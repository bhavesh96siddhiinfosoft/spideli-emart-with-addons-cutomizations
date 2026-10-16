<?php

namespace App\Http\Controllers;

use App\Finance\Content;
use App\Finance\FinanceException;
use App\Finance\Firestore;
use App\Finance\FirestoreError;
use App\Finance\Regions;
use Illuminate\Http\Request;

/**
 * Spideli Finance, admin Phase D3b (spec 6.13): the content customers read -
 * economic indicators, exchange rates, securities and their prices, IPOs,
 * stock collections, news and billers. One list and one form for every kind
 * (App\Finance\Content::SCHEMAS); exchange rates have their own page, and
 * indicators, rates and prices can be imported from CSV.
 */
class FinanceContentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function db()
    {
        return app(Firestore::class);
    }

    private function can($route)
    {
        return in_array($route, json_decode(session('user_permissions') ?? '[]', true) ?: [], true);
    }

    private function failure(\Throwable $e)
    {
        if ($e instanceof FinanceException) {
            return __($e->getMessage());
        }
        report($e);
        return $e instanceof FirestoreError
            ? 'Firebase refused the request (' . $e->status . '). Nothing was changed.'
            : 'Something went wrong. Nothing was changed.';
    }

    private static function kind($kind)
    {
        abort_unless(isset(Content::SCHEMAS[$kind]), 404);
        return Content::SCHEMAS[$kind];
    }

    /**
     * The strictest convert.maxRateAgeHours of the finance regions (default
     * 48): a rate older than this is refused in at least one region.
     */
    private function maxRateAge()
    {
        $regions = new Regions($this->db());
        $min = null;
        foreach ($regions->visible(true) as $r) {
            [$wallet] = $regions->moduleSettings('wallet', [$r['id']]);
            $h = (int)($wallet['convert']['maxRateAgeHours'] ?? 0);
            $min = min($min ?? PHP_INT_MAX, $h > 0 ? $h : 48);
        }
        return $min ?? 48;
    }

    public function index(Request $request)
    {
        $kind = $request->input('tab', 'indicators');
        $manage = $this->can('finance.content.manage');
        if ($kind === 'rates') {
            $content = new Content($this->db());
            $fixed = [];
            foreach ($this->db()->query('exchange_rate_fixed', [], [], 200) as $f) {
                if (($f['data']['active'] ?? true) !== false) {
                    $fixed[$f['id']] = $f['data'];
                }
            }
            return view('finance.content.rates', ['tab' => 'rates', 'latest' => $content->latestRates(), 'fixed' => $fixed,
                'maxAge' => $this->maxRateAge(), 'canManage' => $manage]);
        }
        $s = self::kind($kind);
        $rows = $this->db()->query($s['collection'], [], [], 1000);
        usort($rows, function ($a, $b) use ($s) {
            return [(int)($a['data']['order'] ?? 0), (string)($a['data'][$s['label']] ?? '')] <=> [(int)($b['data']['order'] ?? 0), (string)($b['data'][$s['label']] ?? '')];
        });
        return view('finance.content.index', ['tab' => $kind, 's' => $s, 'rows' => $rows, 'canManage' => $manage]);
    }

    public function form($kind, $id = null)
    {
        $s = self::kind($kind);
        $record = null;
        if ($id) {
            $doc = $this->db()->get($s['collection'] . '/' . $id);
            abort_unless($doc, 404);
            $record = $doc['data'];
        }
        $options = [];
        foreach ($s['fields'] as $key => $f) {
            if ($f[0] === 'multiselect') {
                $options[$key] = [];
                $from = Content::SCHEMAS[$f['from']];
                foreach ($this->db()->query($from['collection'], [], [], 2000) as $d) {
                    $options[$key][$d['id']] = ($d['data'][$from['label']] ?? $d['id']) . (isset($d['data']['name']) && $from['label'] !== 'name' ? ' - ' . $d['data']['name'] : '');
                }
                asort($options[$key]);
            }
        }
        return view('finance.content.form', ['tab' => $kind, 's' => $s, 'id' => $id, 'r' => $record, 'options' => $options]);
    }

    public function save(Request $request, $kind, $id = null)
    {
        $s = self::kind($kind);
        try {
            $existing = null;
            if ($id) {
                $doc = $this->db()->get($s['collection'] . '/' . $id);
                abort_unless($doc, 404);
                $existing = $doc['data'];
            }
            $data = Content::fromInput($kind, $request->all(), $existing);
            $id = (new Content($this->db()))->save($kind, $id, $data);
            $msg = __('Saved.');
            if ($kind === 'billers' && trim((string)$request->input('samples')) !== '' && is_string($data['accountPattern'] ?? null)) {
                $tests = Content::testPattern($data['accountPattern'], $request->input('samples'));
                $msg .= ' ' . __('Pattern test:') . ' ' . implode(', ', array_map(function ($sample, $ok) { return $sample . ($ok ? ' ✓' : ' ✗'); }, array_keys($tests), $tests));
            }
            return redirect()->route('finance.content.edit', [$kind, $id])->with('success', $msg)->withInput($request->only('samples'));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    public function rateAdd(Request $request)
    {
        try {
            $at = trim((string)$request->input('observedAt')) === '' ? null
                : \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', (string)$request->input('observedAt'), new \DateTimeZone(config('app.timezone')));
            if ($at === false) {
                throw new FinanceException('invalid', 'The observation time is not valid.');
            }
            (new Content($this->db()))->addRate($request->input('from'), $request->input('to'), $request->input('rate'), $request->input('source'),
                (string)$request->input('kind', 'official'), $at, $request->boolean('fixed') ? true : null);
            return back()->with('success', __('Rate posted.') . ($request->boolean('fixed') ? ' ' . __('It will be re-posted every morning.') : ''));
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    public function rateUnfix($pair)
    {
        abort_unless(preg_match('/^[A-Z]{3}_[A-Z]{3}$/', $pair), 404);
        try {
            (new Content($this->db()))->unfix($pair);
            return back()->with('success', __('No longer re-posted every morning.'));
        } catch (\Throwable $e) {
            return back()->with('error', $this->failure($e));
        }
    }

    public function import(Request $request, $what)
    {
        abort_unless(isset(Content::IMPORTS[$what]), 404);
        try {
            $f = $request->file('file');
            if (!$f || !$f->isValid()) {
                throw new FinanceException('invalid', 'Choose the CSV file.');
            }
            if (!in_array(strtolower($f->getClientOriginalExtension()), ['csv', 'txt'], true) || $f->getSize() > 2 * 1024 * 1024) {
                throw new FinanceException('invalid', 'A .csv file of at most 2 MB.');
            }
            $r = (new Content($this->db()))->import($what, $f->getRealPath());
            return back()->with('success', __('Imported rows:') . ' ' . $r['rows'] . '.');
        } catch (\Throwable $e) {
            return back()->with('error', $this->failure($e));
        }
    }

    /** An empty CSV with the expected columns, for the team to fill in. */
    public function template($what)
    {
        abort_unless(isset(Content::IMPORTS[$what]), 404);
        return response(implode(',', Content::IMPORTS[$what]) . "\n", 200, [
            'Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="spideli-' . $what . '.csv"',
        ]);
    }
}
