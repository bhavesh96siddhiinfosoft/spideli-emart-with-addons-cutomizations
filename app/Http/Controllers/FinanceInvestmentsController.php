<?php

namespace App\Http\Controllers;

use App\Finance\FinanceException;
use App\Finance\Firestore;
use App\Finance\FirestoreError;
use App\Finance\Investments;
use App\Finance\Locks;
use App\Finance\Money;
use App\Finance\Notifier;
use App\Finance\Regions;
use App\Finance\Scope;
use App\Finance\Storage;
use Illuminate\Http\Request;

/**
 * Spideli Finance, admin Phase D1 (spec 6.10): investment funds, unit
 * prices, the waiting subscriptions and redemptions of each fund, and the
 * customers' PDF reports. The work is in App\Finance\Investments; this
 * controller reads, scopes the customers to the admin's regions, locks the
 * fund being worked on and notifies the customers.
 */
class FinanceInvestmentsController extends Controller
{
    private const MAX_PDF_KB = 20480;

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

    private static function sortBy(array $rows, $field, $oldestFirst = false)
    {
        usort($rows, function ($a, $b) use ($field, $oldestFirst) {
            $x = $a['data'][$field] ?? null;
            $y = $b['data'][$field] ?? null;
            $x = $x instanceof \DateTimeInterface ? $x->getTimestamp() : 0;
            $y = $y instanceof \DateTimeInterface ? $y->getTimestamp() : 0;
            return $oldestFirst ? $x <=> $y : $y <=> $x;
        });
        return $rows;
    }

    private function names(array $uids)
    {
        $uids = array_values(array_unique(array_filter($uids, 'is_string')));
        $out = [];
        if ($uids) {
            foreach ($this->db()->getMany(array_map(function ($u) { return 'users/' . $u; }, $uids)) as $doc) {
                if ($doc) {
                    $out[$doc['id']] = trim(($doc['data']['firstName'] ?? '') . ' ' . ($doc['data']['lastName'] ?? '')) ?: ($doc['data']['email'] ?? $doc['id']);
                }
            }
        }
        return $out;
    }

    private function done($msg, array $notes = [])
    {
        $notes = array_values(array_filter($notes));
        return back()->with('success', trim($msg . ($notes ? ' ' . __('Notification:') . ' ' . implode(' / ', array_unique($notes)) : '')));
    }

    private static function date($ts)
    {
        return $ts instanceof \DateTimeInterface ? $ts->setTimezone(new \DateTimeZone(config('app.timezone')))->format('Y-m-d') : '';
    }

    /** A PDF the admin chose, checked: [['name' => ..., 'file' => local path], ...] or an error message. */
    private static function pdfs($files)
    {
        $out = [];
        foreach (array_filter(is_array($files) ? $files : [$files]) as $k => $f) {
            if (!$f->isValid()) {
                return __('A file could not be received. Try again.');
            }
            $head = (string)@file_get_contents($f->getRealPath(), false, null, 0, 5);
            if (strtolower($f->getClientOriginalExtension()) !== 'pdf' || $head !== '%PDF-') {
                return __('Only PDF files can be added.') . ' (' . $f->getClientOriginalName() . ')';
            }
            if ($f->getSize() > self::MAX_PDF_KB * 1024) {
                return __('A PDF can be at most 20 MB.') . ' (' . $f->getClientOriginalName() . ')';
            }
            $out[$k] = ['name' => $f->getClientOriginalName(), 'file' => $f->getRealPath()];   // keyed as the form rows, for their titles
        }
        return $out;
    }

    /* =============================================================== funds */

    public function index()
    {
        $db = $this->db();
        $funds = $db->query('investment_funds', [], [], 500);
        usort($funds, function ($a, $b) { return [(int)($a['data']['order'] ?? 0), $a['data']['name'] ?? ''] <=> [(int)($b['data']['order'] ?? 0), $b['data']['name'] ?? '']; });
        $waiting = [];
        foreach ([['investment_subscriptions', 'paid', 'subscriptions'], ['investment_redemptions', 'submitted', 'redemptions'], ['investment_redemptions', 'settling', 'settling']] as [$col, $status, $key]) {
            foreach (Scope::filter($db, $db->query($col, [['status', '==', $status]], [], 1000), 'userId') as $r) {
                if (empty($r['data']['demo'])) {
                    $waiting[$r['data']['fundId'] ?? ''][$key] = ($waiting[$r['data']['fundId'] ?? ''][$key] ?? 0) + 1;
                }
            }
        }
        return view('finance.investments.index', ['funds' => $funds, 'waiting' => $waiting, 'canManage' => $this->can('finance.investments.manage')]);
    }

    private function formData($fund = null)
    {
        $regions = new Regions($this->db());
        $currencies = [];
        foreach ($regions->visible(true) as $r) {
            [$settings] = $regions->moduleSettings('investments', [$r['id']]);
            foreach ((array)($settings['currencies'] ?? [$r['currency']]) as $c) {
                if (Money::isCurrency($c)) {
                    $currencies[$c] = true;
                }
            }
        }
        $currencies = array_keys($currencies);
        sort($currencies);
        return ['currencies' => $currencies, 'symbols' => $regions->currencySymbols(), 'fund' => $fund];
    }

    public function create()
    {
        return view('finance.investments.edit', $this->formData() + ['id' => null]);
    }

    public function edit($id)
    {
        $doc = $this->db()->get('investment_funds/' . $id);
        abort_unless($doc, 404);
        return view('finance.investments.edit', $this->formData($doc['data']) + ['id' => $id]);
    }

    public function save(Request $request, $id = null)
    {
        $db = $this->db();
        try {
            $existing = null;
            if ($id !== null) {
                $doc = $db->get('investment_funds/' . $id);
                abort_unless($doc, 404);
                $existing = $doc['data'];
            }
            $f = Investments::fundFromInput($request->all(), $existing);
            if (!$existing && !in_array($f['currency'], $this->formData()['currencies'], true)) {
                throw new FinanceException('invalid', 'This currency is not one of the investment currencies of a finance region.');
            }
            $docs = self::pdfs($request->file('documents'));
            if (is_string($docs)) {
                throw new FinanceException('invalid', $docs);
            }
            $titles = (array)$request->input('documentTitles', []);
            foreach ($docs as $i => $d) {
                $docs[$i] = ['name' => trim((string)($titles[$i] ?? '')) ?: pathinfo($d['name'], PATHINFO_FILENAME), 'fileName' => $d['name'], 'file' => $d['file']];
            }
            $firstNav = $existing ? null : Investments::navToMinor($request->input('navPerUnit'), $f['currency']);
            $r = (new Investments($db))->saveFund($id, $f, $docs, (array)$request->input('removeDocuments', []), $firstNav);
            $notes = [];
            if ($r['termsChanged']) {
                $notifier = new Notifier($db);
                foreach ($r['holders'] as $uid) {
                    $notes[] = $notifier->send($uid, 'investment_terms_updated', ['fundName' => $f['name']], ['fundId' => $r['id']]);
                }
            }
            return redirect()->route('finance.investments.show', $r['id'])->with('success', trim(($id ? __('Fund saved.') : __('Fund created.'))
                . ($r['termsChanged'] ? ' ' . __('The terms changed: version') . ' ' . ((int)($existing['termsVersion'] ?? 1) + 1) . ', ' . count($r['holders']) . ' ' . __('holders notified.') : '')
                . ($notes ? ' ' . __('Notification:') . ' ' . implode(' / ', array_unique($notes)) : '')));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    public function show($id)
    {
        $db = $this->db();
        $doc = $db->get('investment_funds/' . $id);
        abort_unless($doc, 404);
        $f = $doc['data'];
        $prices = $db->query('investment_valuations', [['fundId', '==', $id]], [], 1000);
        usort($prices, function ($a, $b) { return ($b['data']['date'] ?? null) <=> ($a['data']['date'] ?? null); });
        $subs = self::sortBy(Scope::filter($db, $db->query('investment_subscriptions', [['fundId', '==', $id]], [], 1000), 'userId'), 'createdAt');
        $reds = self::sortBy(Scope::filter($db, $db->query('investment_redemptions', [['fundId', '==', $id]], [], 1000), 'userId'), 'createdAt');
        $holdings = Scope::filter($db, $db->query('investment_holdings', [['fundId', '==', $id]], [], 2000), 'userId');
        usort($holdings, function ($a, $b) { return (int)($b['data']['currentValue'] ?? 0) <=> (int)($a['data']['currentValue'] ?? 0); });
        $pick = function ($rows, $status) {
            return array_values(array_filter($rows, function ($r) use ($status) { return ($r['data']['status'] ?? '') === $status && empty($r['data']['demo']); }));
        };
        $waitingSubs = $pick($subs, 'paid');
        $waitingReds = $pick($reds, 'submitted');
        $settling = $pick($reds, 'settling');
        $manage = $this->can('finance.investments.manage');
        $lockedBy = $manage ? Locks::take('investment_funds/' . $id) : null;
        $uids = array_map(function ($r) { return $r['data']['userId'] ?? null; }, array_merge($subs, $reds, $holdings));
        return view('finance.investments.show', [
            'id' => $id, 'f' => $f, 'prices' => $prices, 'subs' => $subs, 'reds' => $reds, 'holdings' => $holdings,
            'waitingSubs' => $waitingSubs, 'waitingReds' => $waitingReds, 'settling' => $settling,
            'names' => $this->names($uids), 'lockedBy' => $lockedBy, 'canManage' => $manage && $lockedBy === null,
        ]);
    }

    /** A fund document: a signed link valid 5 minutes. */
    public function document($id, $index)
    {
        $doc = $this->db()->get('investment_funds/' . $id);
        $d = $doc ? (array)($doc['data']['documents'][(int)$index] ?? []) : [];
        abort_unless($d, 404);
        if (!empty($d['storagePath'])) {
            return redirect()->away(Storage::signedUrl($d['storagePath']));
        }
        abort_unless(!empty($d['url']) && preg_match('#^https://#', $d['url']), 404);
        return redirect()->away($d['url']);
    }

    public function action(Request $request, $id, $action)
    {
        $db = $this->db();
        $inv = new Investments($db);
        $notifier = new Notifier($db);
        $reason = trim((string)$request->input('reason'));
        try {
            Locks::assertMine('investment_funds/' . $id);
            $fund = $db->get('investment_funds/' . $id);
            abort_unless($fund, 404);
            $f = $fund['data'];
            $cur = $f['currency'] ?? '';
            switch ($action) {
                case 'add-price':
                    $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i', trim((string)$request->input('date')) . ' ' . (trim((string)$request->input('time')) ?: '12:00'),
                        new \DateTimeZone(config('app.timezone')));
                    if (!$date) {
                        throw new FinanceException('invalid', 'Choose the date of the price.');
                    }
                    $n = $inv->addPrice($id, $date, Investments::navToMinor($request->input('navPerUnit'), $cur));
                    return $this->done(__('Unit price added.') . ' ' . __('Holdings revalued:') . ' ' . $n . '.');

                case 'revalue':
                    return $this->done(__('Holdings revalued:') . ' ' . $inv->revalue($id) . '.');

                case 'execute-subscriptions':
                    $ok = 0;
                    $errors = [];
                    $notes = [];
                    foreach ($this->waiting($db, 'investment_subscriptions', $id, 'paid', (array)$request->input('ids', [])) as $sid) {
                        try {
                            $r = $inv->executeSubscription($sid, $id);
                            $ok++;
                            $notes[] = $notifier->send($r['userId'], 'investment_payment_confirmed', ['amount' => Money::format($r['amount'], $r['currency']), 'fundName' => $r['fundName']], ['fundId' => $id, 'subscriptionId' => $sid]);
                            $notes[] = $notifier->send($r['userId'], 'investment_units_allocated', ['units' => Investments::unitsText($r['units']), 'fundName' => $r['fundName'],
                                'navPerUnit' => Investments::navText($r['nav'], $r['currency'])], ['fundId' => $id, 'subscriptionId' => $sid]);
                        } catch (\Throwable $e) {
                            $errors[] = $sid . ': ' . $this->failure($e);
                        }
                    }
                    return $this->summary(__('Subscriptions executed:') . ' ' . $ok . '.', $errors, $notes);

                case 'refuse-subscription':
                    $sid = (string)$request->input('subscriptionId');
                    $s = $inv->refuseSubscription($sid, $reason);
                    return $this->done(__('Subscription refused; the money is back in the customer\'s wallet.'), [$notifier->send($s['userId'] ?? '', 'investment_subscription_rejected',
                        ['amount' => Money::format((int)($s['amount'] ?? 0), $s['currency'] ?? $cur), 'fundName' => $s['fundName'] ?? ($f['name'] ?? ''), 'reason' => $reason], ['fundId' => $id, 'subscriptionId' => $sid])]);

                case 'execute-redemptions':
                    $ok = 0;
                    $errors = [];
                    $notes = [];
                    foreach ($this->waiting($db, 'investment_redemptions', $id, 'submitted', (array)$request->input('ids', [])) as $rid) {
                        try {
                            $r = $inv->executeRedemption($rid, $id);
                            $ok++;
                            $notes[] = $notifier->send($r['userId'], 'investment_redemption_processed', ['fundName' => $r['fundName'], 'amount' => Money::format($r['net'], $r['currency']),
                                'settlementDate' => self::date($r['settleOn'])], ['fundId' => $id, 'redemptionId' => $rid]);
                        } catch (\Throwable $e) {
                            $errors[] = $rid . ': ' . $this->failure($e);
                        }
                    }
                    return $this->summary(__('Redemptions executed:') . ' ' . $ok . '. ' . __('Settle them to pay the customers.'), $errors, $notes);

                case 'refuse-redemption':
                    $rid = (string)$request->input('redemptionId');
                    $r = $inv->refuseRedemption($rid, $reason);
                    return $this->done(__('Redemption refused.'), [$notifier->send($r['userId'] ?? '', 'investment_redemption_rejected',
                        ['fundName' => $r['fundName'] ?? ($f['name'] ?? ''), 'reason' => $reason], ['fundId' => $id, 'redemptionId' => $rid])]);

                case 'settle':
                case 'settle-due':
                    $ids = $action === 'settle' ? [(string)$request->input('redemptionId')] : $this->due($db, $id);
                    $ok = 0;
                    $errors = [];
                    $notes = [];
                    foreach ($ids as $rid) {
                        try {
                            $r = $inv->settle($rid);
                            $ok++;
                            $notes[] = $notifier->send($r['userId'] ?? '', 'investment_redemption_settled', ['amount' => Money::format((int)($r['netAmount'] ?? 0), $r['currency'] ?? $cur),
                                'fundName' => $r['fundName'] ?? ($f['name'] ?? '')], ['fundId' => $id, 'redemptionId' => $rid]);
                        } catch (\Throwable $e) {
                            $errors[] = $rid . ': ' . $this->failure($e);
                        }
                    }
                    return $this->summary(__('Redemptions paid to the Finance wallet:') . ' ' . $ok . '.', $errors, $notes);
            }
            abort(404);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    /** The waiting orders of a fund this admin may see (their regions), optionally only the ticked ones; never demo records. */
    private function waiting(Firestore $db, $collection, $fundId, $status, array $only)
    {
        $rows = Scope::filter($db, $db->query($collection, [['fundId', '==', $fundId], ['status', '==', $status]], [], 1000), 'userId');
        $rows = self::sortBy(array_values(array_filter($rows, function ($r) { return empty($r['data']['demo']); })), 'createdAt', true);
        $ids = array_column($rows, 'id');
        return $only ? array_values(array_intersect($ids, $only)) : $ids;
    }

    /** Settling redemptions whose expected settlement date has come. */
    private function due(Firestore $db, $fundId)
    {
        $now = new \DateTimeImmutable();
        $rows = Scope::filter($db, $db->query('investment_redemptions', [['fundId', '==', $fundId], ['status', '==', 'settling']], [], 1000), 'userId');
        return array_values(array_column(array_filter($rows, function ($r) use ($now) {
            $at = $r['data']['expectedSettlementDate'] ?? null;
            return empty($r['data']['demo']) && (!($at instanceof \DateTimeInterface) || $at <= $now);
        }), 'id'));
    }

    private function summary($msg, array $errors, array $notes)
    {
        $back = $this->done($msg, $notes);
        return $errors ? $back->with('error', __('Not done:') . ' ' . implode(' | ', $errors)) : $back;
    }

    /* ============================================================= reports */

    public function reports()
    {
        $db = $this->db();
        $reports = self::sortBy(Scope::filter($db, $db->query('investment_reports', [], [], 1000), 'userId'), 'createdAt');
        $investors = [];
        foreach (Scope::filter($db, $db->query('investment_holdings', [], [], 3000), 'userId') as $h) {
            $investors[$h['data']['userId'] ?? ''] = true;
        }
        unset($investors['']);
        $uids = array_merge(array_keys($investors), array_map(function ($r) { return $r['data']['userId'] ?? null; }, $reports));
        return view('finance.investments.reports', [
            'reports' => $reports, 'investors' => array_keys($investors), 'names' => $this->names($uids),
            'canManage' => $this->can('finance.investments.manage'),
        ]);
    }

    public function reportStore(Request $request)
    {
        $db = $this->db();
        try {
            $uid = (string)$request->input('userId');
            $user = $db->get('users/' . $uid);
            if (!$user || !Scope::allows($db, ['id' => $uid, 'data' => ['userId' => $uid]], 'userId')) {
                throw new FinanceException('invalid', 'Choose the customer.');
            }
            $files = self::pdfs($request->file('report'));
            if (is_string($files)) {
                throw new FinanceException('invalid', $files);
            }
            $files = array_values($files);
            if (count($files) !== 1) {
                throw new FinanceException('invalid', 'Choose the PDF report.');
            }
            $period = trim((string)$request->input('period'));
            (new Investments($db))->addReport($uid, $request->input('title'), $period, $files[0]['file'], $files[0]['name']);
            return $this->done(__('Report added.'), [(new Notifier($db))->send($uid, 'investment_report_available', ['period' => $period], [])]);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    public function reportFile($id)
    {
        $db = $this->db();
        $doc = $db->get('investment_reports/' . $id);
        abort_unless($doc && !empty($doc['data']['storagePath']) && Scope::allows($db, $doc, 'userId'), 404);
        return redirect()->away(Storage::signedUrl($doc['data']['storagePath']));
    }
}
