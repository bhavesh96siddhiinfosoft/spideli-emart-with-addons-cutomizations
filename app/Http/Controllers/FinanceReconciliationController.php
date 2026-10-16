<?php

namespace App\Http\Controllers;

use App\Finance\FinanceException;
use App\Finance\Firestore;
use App\Finance\FirestoreError;
use App\Finance\Money;
use App\Finance\Reconciliation;
use App\Finance\Regions;
use Illuminate\Http\Request;

/**
 * Daily bank reconciliation (spec 6.5.3): the day's expected money in and
 * out from the ledger records, the statement totals the admin enters, and
 * the history of reconciled days. Logic in App\Finance\Reconciliation.
 */
class FinanceReconciliationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function db()
    {
        return app(Firestore::class);
    }

    private static function tz()
    {
        return new \DateTimeZone(config('app.timezone'));
    }

    /** The chosen day (default yesterday) and currency (default the first finance currency). */
    private function choice(Request $request)
    {
        $currencies = (new Regions($this->db()))->financeCurrencies();
        $cur = in_array($request->input('currency'), $currencies, true) ? $request->input('currency') : ($currencies[0] ?? '');
        $day = \DateTimeImmutable::createFromFormat('!Y-m-d', (string)$request->input('date'), self::tz()) ?: new \DateTimeImmutable('yesterday', self::tz());
        return [$day, $cur, $currencies];
    }

    public function index(Request $request)
    {
        [$day, $cur, $currencies] = $this->choice($request);
        $rec = new Reconciliation($this->db());
        $history = $this->db()->query('reconciliations', [['currency', '==', $cur]], [], 400);
        usort($history, function ($a, $b) { return strcmp($b['data']['date'] ?? '', $a['data']['date'] ?? ''); });
        $saved = $cur ? $this->db()->get('reconciliations/' . Reconciliation::id($day, $cur)) : null;
        return view('finance.reconciliation', [
            'day' => $day, 'cur' => $cur, 'currencies' => $currencies,
            'expected' => $cur ? $rec->expected($day, $cur) : null,
            'saved' => $saved ? $saved['data'] : null,
            'history' => array_slice($history, 0, 90),
            'missing' => $cur ? ($rec->missing([$cur], 14, self::tz())[$cur] ?? []) : [],
        ]);
    }

    public function save(Request $request)
    {
        [$day, $cur] = $this->choice($request);
        try {
            $r = (new Reconciliation($this->db()))->save($day, $cur, Money::toMinor($request->input('actualIn'), $cur), Money::toMinor($request->input('actualOut'), $cur), $request->input('notes'));
            return redirect()->route('finance.reconciliation', ['currency' => $cur, 'date' => $day->format('Y-m-d')])
                ->with('success', $r['status'] === 'matched' ? __('Reconciled: the statement matches.') : __('Saved with a difference to investigate.'));
        } catch (\Throwable $e) {
            if (!$e instanceof FinanceException) {
                report($e);
            }
            return back()->withInput()->with('error', $e instanceof FinanceException ? __($e->getMessage())
                : ($e instanceof FirestoreError ? 'Firebase refused the request (' . $e->status . '). Nothing was changed.' : 'Something went wrong. Nothing was changed.'));
        }
    }
}
