<?php

namespace App\Http\Controllers;

use App\Finance\FinanceException;
use App\Finance\Firestore;
use App\Finance\FirestoreError;
use App\Finance\FourEyes;
use App\Finance\Ledger;
use App\Finance\Money;
use App\Finance\Operations;
use App\Finance\Regions;
use App\Finance\Value;
use App\Finance\Audit;
use App\Finance\SettingsSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Spideli Finance, admin Phase A (SPIDELI_FINANCE_ADMIN_WEB_SPEC.md):
 * settings (6.2), platform accounts, ledger history with reversal and
 * manual adjustment (6.5.3), four-eyes approvals (5.3) and the audit log (6.18).
 *
 * Everything is read and written on the server with the service account;
 * the browser never talks to Firestore for finance.
 */
class FinanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function can($route)
    {
        return in_array($route, json_decode(session('user_permissions') ?? '[]', true) ?: [], true);
    }

    private function failure(\Throwable $e)
    {
        if ($e instanceof FinanceException) {
            return $e->getMessage();
        }
        if ($e instanceof FirestoreError) {
            report($e);
            return 'Firebase refused the request (' . $e->status . '). Nothing was changed.';
        }
        report($e);
        return 'Something went wrong. Nothing was changed.';
    }

    /* ============================================================ settings */

    /**
     * Overview: every finance-enabled region (that this admin may see) against
     * every module, the platform accounts its currencies need, the go-live
     * checklist and the second-approver thresholds.
     */
    public function settings()
    {
        $db = app(Firestore::class);
        $regions = new Regions($db);
        $visible = $regions->visible(true);

        $docs = [];
        foreach ($visible as $id => $r) {
            foreach (SettingsSchema::docs() as $module) {
                $docs[] = 'finance_settings/' . Regions::docId($id, $module);
            }
        }
        $stored = $docs ? $db->getMany($docs) : [];

        $matrix = [];
        foreach ($visible as $id => $r) {
            foreach (SettingsSchema::docs() as $module) {
                $doc = $stored['finance_settings/' . Regions::docId($id, $module)] ?? null;
                $data = $doc ? $doc['data'] : null;
                $matrix[$id][$module] = [
                    'exists' => $data !== null,
                    'missing' => SettingsSchema::missing($module, $data),
                    'warnings' => SettingsSchema::warnings($module, $data),
                ];
            }
        }

        $platform = $db->query('financial_accounts', [['kind', '==', 'platform']]);
        $platformIds = array_column($platform, 'id');
        $currencies = $regions->financeCurrencies();
        $template = $db->query('dynamic_notification', [['type', '==', 'finance_chat_message']], [], 1);

        $goLive = [];
        foreach ($visible as $id => $r) {
            $goLive[] = ['label' => $r['name'] . ': ' . __('currency') . ' ' . ($r['currency'] ?: '-'), 'ok' => $r['currency'] !== ''];
            $goLive[] = ['label' => $r['name'] . ': ' . __('wallet settings'), 'ok' => empty($matrix[$id]['wallet']['missing'])];
        }
        foreach ($currencies as $c) {
            $goLive[] = ['label' => 'financial_accounts/platform_' . $c, 'ok' => in_array('platform_' . $c, $platformIds, true)];
        }
        $goLive[] = ['label' => 'dynamic_notification type finance_chat_message', 'ok' => (bool)$template];

        return view('finance.settings.index', [
            'regions' => $visible,
            'modules' => SettingsSchema::titles(),
            'matrix' => $matrix,
            'platform' => $platform,
            'missingAccounts' => array_values(array_diff($currencies, array_map(function ($id) {
                return substr($id, strlen('platform_'));
            }, $platformIds))),
            'goLive' => $goLive,
            'thresholds' => FourEyes::thresholds(),
            'canEdit' => $this->can('finance.settings.edit'),
            'canCreateAccount' => $this->can('finance.platform-accounts.create'),
        ]);
    }

    /**
     * One module's form for the chosen regions (?regions[]=...). The values
     * shown are the first chosen region's; a region with no settings yet
     * starts from the app's current platform_settings values.
     */
    public function settingsEdit(Request $request, $doc)
    {
        abort_unless(in_array($doc, SettingsSchema::docs(), true), 404);
        $db = app(Firestore::class);
        $regions = new Regions($db);
        $visible = $regions->visible(true);

        $chosen = $regions->filterIds((array)$request->input('regions', []));
        if (!$chosen) {
            $active = $_COOKIE['region_id'] ?? '';
            $chosen = isset($visible[$active]) ? [$active] : array_slice(array_keys($visible), 0, 1);
        }

        $data = null;
        $source = null;
        $differs = false;
        if ($chosen) {
            $docs = $db->getMany(array_map(function ($id) use ($doc) {
                return 'finance_settings/' . Regions::docId($id, $doc);
            }, $chosen));
            $first = $docs['finance_settings/' . Regions::docId($chosen[0], $doc)] ?? null;
            if ($first) {
                $data = $first['data'];
                $source = 'region';
            } else {
                $fallback = $db->get('platform_settings/' . $doc);
                $data = $fallback ? $fallback['data'] : null;
                $source = $fallback ? 'app' : null;
            }
            $plain = function ($d) {
                if (!$d) {
                    return null;
                }
                $d = $d['data'];
                unset($d['regionId'], $d['currencies'], $d['createdAt'], $d['updatedAt'], $d['updatedBy']);
                return json_encode(Value::plain($d));
            };
            foreach (array_slice($chosen, 1) as $id) {
                if ($plain($docs['finance_settings/' . Regions::docId($id, $doc)] ?? null) !== $plain($first)) {
                    $differs = true;
                }
            }
        }

        // Money rows: every currency the chosen regions' documents can hold.
        $moneyCurrencies = [];
        foreach ($chosen as $id) {
            $moneyCurrencies[] = $regions->currencyOf($id);
        }
        if ($doc === 'wallet') {
            $moneyCurrencies = array_merge($moneyCurrencies, (array)SettingsSchema::getPath($data ?: [], 'convert.currencies'));
        }
        $moneyCurrencies = array_values(array_unique(array_filter($moneyCurrencies, [Money::class, 'isCurrency'])));

        // Payment methods: those of the chosen regions' gateways (Settings >
        // Payment Methods), each labelled with the gateways that provide it.
        $methods = [];
        $providers = [];
        foreach ($chosen as $id) {
            $methods = array_merge($methods, $regions->methodsFor($id));
            foreach ($regions->providersFor($id) as $m => $names) {
                $providers[$m] = array_values(array_unique(array_merge($providers[$m] ?? [], $names)));
            }
        }

        return view('finance.settings.edit', [
            'doc' => $doc,
            'title' => SettingsSchema::titles()[$doc],
            'fields' => SettingsSchema::fields($doc),
            'data' => $data ?: [],
            'source' => $source,
            'differs' => $differs,
            'regions' => $visible,
            'chosen' => $chosen,
            'regionHelper' => $regions,
            'moneyCurrencies' => $moneyCurrencies,
            'currencyCodes' => $regions->currencyCodes(),
            'currencySymbols' => $regions->currencySymbols(),
            'countryCodes' => $regions->countryCodes(),
            'availableMethods' => array_values(array_unique($methods)),
            'methodProviders' => $providers,
            'methodGaps' => $regions->methodGaps($chosen),
            'missing' => SettingsSchema::missing($doc, $source === 'region' ? $data : null),
            'warnings' => SettingsSchema::warnings($doc, $data),
            'canEdit' => $this->can('finance.settings.edit'),
        ]);
    }

    public function settingsUpdate(Request $request, $doc)
    {
        abort_unless(in_array($doc, SettingsSchema::docs(), true), 404);
        $db = app(Firestore::class);
        $regions = new Regions($db);
        $chosen = $regions->filterIds((array)$request->input('regions', []));
        $back = route('finance.settings.edit', ['doc' => $doc, 'regions' => $chosen]);
        if (!$chosen) {
            return redirect($back)->withInput()->with('error', __('Choose at least one region enabled for Finance.'));
        }
        $targets = [];
        $methods = [];
        foreach ($chosen as $id) {
            $targets[$id] = $regions->currencyOf($id);
            $methods[$id] = $regions->methodsFor($id);
        }
        try {
            $changed = (new Operations($db))->saveSettings($doc, $request->except(['_token', 'regions']), $targets, $methods);
        } catch (FinanceException $e) {
            if ($e->reason === 'invalid_settings') {
                return redirect($back)->withInput()->with('finance_errors', $e->context)->with('error', __($e->getMessage()));
            }
            return redirect($back)->withInput()->with('error', $this->failure($e));
        } catch (\Throwable $e) {
            return redirect($back)->withInput()->with('error', $this->failure($e));
        }
        if (!$changed) {
            return redirect($back)->with('success', __('Nothing changed.'));
        }
        $lines = [];
        foreach ($changed as $id => $fields) {
            $lines[] = $regions->name($id) . ': ' . implode(', ', $fields);
        }
        return redirect($back)->with('success', __('Saved.') . ' ' . implode(' · ', $lines));
    }

    /**
     * Copies the app's current settings into a finance-enabled region that has
     * none yet. Region Management calls it when Finance is switched on.
     */
    public function seedRegion(Request $request, $regionId)
    {
        $db = app(Firestore::class);
        $regions = new Regions($db);
        $allowed = Regions::allowedIds();
        $region = $regions->all()[$regionId] ?? null;
        $fail = function ($message, $status) use ($request) {
            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => $message], $status)
                : back()->with('error', $message);
        };
        if (!$region || ($allowed && !in_array($regionId, $allowed, true))) {
            return $fail(__('Unknown region.'), 404);
        }
        if (empty($region['financeEnabled'])) {
            return $fail(__('Finance is not enabled for this region.'), 422);
        }
        try {
            $created = (new Operations($db))->seedRegion($regionId, $region['currency'], $regions->methodsFor($regionId));
        } catch (\Throwable $e) {
            return $fail($this->failure($e), 422);
        }
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'created' => $created]);
        }
        return back()->with('success', $created
            ? $region['name'] . ': ' . __('created from the app\'s current settings:') . ' ' . implode(', ', $created)
            : $region['name'] . ': ' . __('already has every module.'));
    }

    public function platformAccountStore(Request $request)
    {
        $cur = strtoupper(trim((string)$request->input('currency')));
        $regions = new Regions(app(Firestore::class));
        if (!in_array($cur, $regions->financeCurrencies(), true)) {
            return back()->with('error', __('Only the currency of a region enabled for Finance can have a platform account.'));
        }
        try {
            $id = (new Operations())->createPlatformAccount($cur);
        } catch (\Throwable $e) {
            return back()->with('error', $this->failure($e));
        }
        return back()->with('success', __('Created') . ' ' . $id . '.');
    }

    public function fourEyesUpdate(Request $request)
    {
        $regions = new Regions(app(Firestore::class));
        $before = FourEyes::thresholds();
        $after = [];
        try {
            foreach ((array)$request->input('threshold', []) as $regionId => $amount) {
                if (!$regions->filterIds([$regionId])) {
                    continue;
                }
                $cur = $regions->currencyOf($regionId);
                $minor = Money::isCurrency($cur) ? Money::toMinor($amount, $cur) : null;
                $after[$regionId] = $minor === null ? [] : [$cur => $minor];
            }
        } catch (FinanceException $e) {
            return back()->with('error', $e->getMessage());
        }
        foreach ($after as $regionId => $map) {
            FourEyes::saveThreshold($regionId, $map);
        }
        try {
            $db = app(Firestore::class);
            $db->batch([Audit::write($db, 'four_eyes.threshold', 'finance_panel_settings', 'four_eyes_threshold',
                array_intersect_key($before, $after) ?: null, $after ?: null)]);
        } catch (\Throwable $e) {
            report($e);
        }
        return back()->with('success', __('Second-approver thresholds saved.'));
    }

    /* ============================================================== ledger */

    /** Reads the filters, resolving a user given by e-mail or phone to a uid. */
    /**
     * Every posting matching the filters (up to 20 000) as CSV: one line per
     * posting with raw amounts (minor units) and the major-unit amount, fee,
     * accounts, customers, source and reversal links. UTF-8 with a byte-order
     * mark, so it opens in Excel; the export is audited.
     */
    public function ledgerExport(Request $request)
    {
        $db = app(Firestore::class);
        $f = $this->ledgerFilters($request, $db);
        try {
            [$rows, $capped] = $this->ledgerRows($f, $db, 20000);
        } catch (\Exception $e) {
            return back()->with('error', 'Invalid dates.');
        }
        $users = $this->userNames($db, array_merge([], ...array_map(function ($r) { return $r['data']['userIds'] ?? []; }, $rows)));
        $tz = new \DateTimeZone(config('app.timezone'));
        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF");   // byte-order mark: Excel reads it as UTF-8
        fputcsv($out, ['Date', 'Reference', 'Type', 'Status', 'Currency', 'Amount', 'Amount (minor units)', 'Fee (minor units)', 'From account', 'To account',
            'Customers', 'Source', 'Description', 'Created by', 'Reversal of', 'Reversed by', 'Transaction id']);
        foreach ($rows as $r) {
            $d = $r['data'];
            $lines = (array)($d['lines'] ?? []);
            $debit = $lines[0] ?? [];
            $amount = (int)($lines[1]['amount'] ?? ($debit['amount'] ?? 0));
            $fee = (int)($lines[2]['amount'] ?? 0);
            $at = $d['createdAt'] ?? null;
            $cur = $d['currency'] ?? '';
            fputcsv($out, [
                $at instanceof \DateTimeInterface ? $at->setTimezone($tz)->format('Y-m-d H:i:s') : '',
                $d['reference'] ?? '', $d['type'] ?? '', $d['status'] ?? '', $cur,
                Money::toMajor($amount, $cur), $amount, $fee,
                $debit['accountId'] ?? '', $lines[1]['accountId'] ?? '',
                implode('; ', array_map(function ($u) use ($users) { return ($users[$u] ?? '') !== '' ? $users[$u] . ' (' . $u . ')' : $u; }, (array)($d['userIds'] ?? []))),
                trim(($d['source']['kind'] ?? '') . ' ' . ($d['source']['id'] ?? '')),
                $d['description'] ?? '', $d['createdBy'] ?? '', $d['reversalOf'] ?? '', implode('; ', (array)($d['reversedBy'] ?? [])), $r['id'],
            ]);
        }
        if ($capped) {
            fputcsv($out, ['Cut at 20 000 postings: narrow the dates for the rest.']);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        $db->batch([Audit::write($db, 'ledger.export', 'ledger_transactions', 'csv', null,
            array_intersect_key($f, array_flip(['from', 'to', 'type', 'reference', 'currency', 'status', 'uid'])) + ['rows' => count($rows)])]);
        return response($csv, 200, ['Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="spideli-ledger-' . $f['from'] . '-' . $f['to'] . '.csv"']);
    }

    private function ledgerFilters(Request $request, Firestore $db)
    {
        $f = [
            'from' => $request->input('from') ?: now()->subDays(30)->format('Y-m-d'),
            'to' => $request->input('to') ?: now()->format('Y-m-d'),
            'type' => trim((string)$request->input('type')),
            'user' => trim((string)$request->input('user')),
            'reference' => strtoupper(trim((string)$request->input('reference'))),
            'currency' => strtoupper(trim((string)$request->input('currency'))),
            'status' => trim((string)$request->input('status')),
            'uid' => null,
            'userNote' => null,
        ];
        if ($f['user'] !== '') {
            $f['uid'] = $this->resolveUser($db, $f['user'], $f['userNote']);
        }
        return $f;
    }

    private function resolveUser(Firestore $db, $value, &$note)
    {
        if (strpos($value, '@') !== false) {
            $found = $db->query('users', [['email', '==', $value]], [], 2);
        } elseif (preg_match('/^\+?[0-9 ]{6,}$/', $value)) {
            $phone = str_replace(' ', '', $value);
            $found = $db->query('users', [['phoneNumber', '==', $phone]], [], 2);
            if (!$found && $phone[0] !== '+') {
                $found = $db->query('users', [['phoneNumber', '==', '+' . $phone]], [], 2);
            }
        } else {
            return $value;   // a uid
        }
        if (count($found) > 1) {
            $note = 'Several customers match; showing the first.';
        }
        if (!$found) {
            $note = 'No customer matches "' . $value . '".';
            return '__none__';
        }
        return $found[0]['id'];
    }

    /**
     * The postings matching the ledger filters, newest first: by reference,
     * by customer, else by date. Returns [rows, capped].
     */
    private function ledgerRows(array $f, Firestore $db, $limit)
    {
        $from = new \DateTimeImmutable($f['from'] . ' 00:00:00');
        $to = (new \DateTimeImmutable($f['to'] . ' 00:00:00'))->modify('+1 day');

        if ($f['reference'] !== '') {
            $rows = $db->query('ledger_transactions', [['reference', '==', $f['reference']]], [], 20);
        } elseif ($f['uid'] === '__none__') {
            $rows = [];
        } elseif ($f['uid']) {
            $rows = $db->query('ledger_transactions', [['userIds', 'array-contains', $f['uid']]], [], $limit);
        } else {
            $rows = $db->query('ledger_transactions', [['createdAt', '>=', $from], ['createdAt', '<', $to]], [['createdAt', 'desc']], $limit);
        }
        $capped = count($rows) >= $limit;

        $rows = array_values(array_filter($rows, function ($r) use ($f, $from, $to) {
            $d = $r['data'];
            $at = $d['createdAt'] ?? null;
            if ($f['reference'] === '' && $at instanceof \DateTimeInterface && ($at < $from || $at >= $to)) {
                return false;
            }
            if ($f['type'] !== '' && ($d['type'] ?? '') !== $f['type']) {
                return false;
            }
            if ($f['currency'] !== '' && ($d['currency'] ?? '') !== $f['currency']) {
                return false;
            }
            if ($f['status'] !== '' && ($d['status'] ?? '') !== $f['status']) {
                return false;
            }
            return true;
        }));
        usort($rows, function ($a, $b) {
            $x = $a['data']['createdAt'] ?? null;
            $y = $b['data']['createdAt'] ?? null;
            return ($y instanceof \DateTimeInterface ? $y->getTimestamp() : 0) <=> ($x instanceof \DateTimeInterface ? $x->getTimestamp() : 0);
        });
        return [$rows, $capped];
    }

    public function ledger(Request $request)
    {
        $db = app(Firestore::class);
        $f = $this->ledgerFilters($request, $db);
        try {
            [$rows, $capped] = $this->ledgerRows($f, $db, 1000);
        } catch (\Exception $e) {
            return back()->with('error', 'Invalid dates.');
        }

        $users = $this->userNames($db, array_merge(...array_map(function ($r) {
            return $r['data']['userIds'] ?? [];
        }, $rows ?: [['data' => []]])));

        return view('finance.ledger.index', [
            'rows' => $rows,
            'filters' => $f,
            'capped' => $capped,
            'users' => $users,
            'types' => ['deposit', 'withdrawal', 'transfer', 'transfer_out', 'bill_payment', 'card_funding', 'card_fee',
                'card_withdrawal', 'savings_contribution', 'savings_withdrawal', 'tontine_contribution', 'tontine_payout',
                'loan_funding', 'loan_disbursement', 'loan_repayment', 'loan_lender_payout', 'investment_subscription',
                'investment_redemption', 'insurance_premium', 'insurance_claim_payout', 'conversion', 'fee', 'reversal',
                'manual_adjustment'],
            'canAdjust' => $this->can('finance.ledger.adjust'),
            'currencyCodes' => (new Regions($db))->currencyCodes(),
            'pending' => FourEyes::pendingCount(),
        ]);
    }

    /** [uid => "Name (email)"] for display. */
    private function userNames(Firestore $db, array $uids)
    {
        $uids = array_values(array_unique(array_filter($uids, 'is_string')));
        if (!$uids) {
            return [];
        }
        $out = [];
        try {
            foreach ($db->getMany(array_map(function ($u) { return 'users/' . $u; }, array_slice($uids, 0, 300))) as $path => $doc) {
                if ($doc) {
                    $d = $doc['data'];
                    $name = trim(($d['firstName'] ?? '') . ' ' . ($d['lastName'] ?? ''));
                    $out[$doc['id']] = trim($name . ' ' . (!empty($d['email']) ? '(' . $d['email'] . ')' : ''));
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
        return $out;
    }

    public function ledgerShow($txId)
    {
        $db = app(Firestore::class);
        $tx = $db->get('ledger_transactions/' . $txId);
        abort_unless($tx, 404);
        $d = $tx['data'];

        $accounts = [];
        foreach ($db->getMany(array_map(function ($l) { return 'financial_accounts/' . $l['accountId']; },
            array_filter($d['lines'] ?? [], function ($l) { return isset($l['accountId']); }))) as $doc) {
            if ($doc) {
                $accounts[$doc['id']] = $doc['data'];
            }
        }
        $related = [];
        foreach (array_merge($d['reversedBy'] ?? [], !empty($d['reversalOf']) ? [$d['reversalOf']] : []) as $id) {
            $related[] = $id;
        }
        $pendingReversal = null;
        try {
            $pendingReversal = DB::table('finance_approvals')->where('action', 'reversal')->where('status', 'pending')
                ->where('payload', 'like', '%"' . $txId . '"%')->first();
        } catch (\Throwable $e) {
        }

        return view('finance.ledger.show', [
            'tx' => $tx,
            'd' => $d,
            'accounts' => $accounts,
            'related' => $related,
            'users' => $this->userNames($db, $d['userIds'] ?? []),
            'wellFormed' => Ledger::isWellFormed($d),
            'canReverse' => $this->can('finance.ledger.reverse'),
            'needsApproval' => isset($d['lines'][0]['amount'], $d['currency']) && FourEyes::needsSecondAdmin($d['currency'], (int)$d['lines'][0]['amount']),
            'pendingReversal' => $pendingReversal,
        ]);
    }

    public function ledgerReverse(Request $request, $txId)
    {
        $reason = trim((string)$request->input('reason'));
        if ($reason === '') {
            return back()->with('error', 'A reason is required.');
        }
        try {
            $tx = (app(Firestore::class))->get('ledger_transactions/' . $txId);
            if (!$tx) {
                throw new FinanceException('posting_missing', 'This posting does not exist.');
            }
            $d = $tx['data'];
            if (!Ledger::isWellFormed($d) || ($d['status'] ?? '') === 'reversed' || ($d['type'] ?? '') === 'reversal') {
                throw new FinanceException('not_reversible', 'This posting cannot be reversed.');
            }
            $amount = (int)$d['lines'][0]['amount'];
            if (FourEyes::needsSecondAdmin($d['currency'], $amount)) {
                FourEyes::request('reversal', ['txId' => $txId, 'reference' => $d['reference'] ?? ''], $d['currency'], $amount, $reason);
                return back()->with('success', 'Sent for a second admin\'s approval (Finance > Approvals).');
            }
            $written = (new Operations())->reverse($txId, $reason);
        } catch (\Throwable $e) {
            return back()->with('error', $this->failure($e));
        }
        return redirect()->route('finance.ledger.show', $written[0]['txId'])
            ->with('success', 'Reversed. ' . implode(', ', array_column($written, 'reference')));
    }

    public function adjustCreate()
    {
        $regions = new Regions(app(Firestore::class));
        return view('finance.ledger.adjust', [
            'currencySymbols' => $regions->currencySymbols(),
            'regions' => array_filter($regions->visible(true), function ($r) {
                return Money::isCurrency($r['currency']);
            }),
        ]);
    }

    /**
     * Manual adjustment in a region: the currency is the region's own, and the
     * customer must belong to that region.
     */
    public function adjustStore(Request $request)
    {
        $reason = trim((string)$request->input('reason'));
        $direction = (string)$request->input('direction');
        try {
            $db = app(Firestore::class);
            $regions = new Regions($db);
            $regionId = (string)$request->input('region');
            if (!$regions->filterIds([$regionId])) {
                throw new FinanceException('no_region', __('Choose a region enabled for Finance.'));
            }
            $cur = $regions->currencyOf($regionId);
            if (!Money::isCurrency($cur)) {
                throw new FinanceException('no_currency', __('This region has no currency. Set it in Region Management.'));
            }
            if ($reason === '') {
                throw new FinanceException('invalid', __('A reason is required.'));
            }
            $amount = Money::toMinor($request->input('amount'), $cur);
            if (!$amount) {
                throw new FinanceException('invalid_amount', __('Enter an amount above 0.'));
            }
            $note = null;
            $uid = $this->resolveUser($db, trim((string)$request->input('user')), $note);
            $user = ($uid === '__none__' || $uid === '') ? null : $db->get('users/' . $uid);
            if (!$user) {
                throw new FinanceException('user_missing', $note ?: __('No customer has this id.'));
            }
            $userRegions = (array)($user['data']['regionIds'] ?? []);
            if ($userRegions && !in_array($regionId, $userRegions, true)) {
                throw new FinanceException('wrong_region', __('This customer does not belong to the chosen region.'));
            }
            if (FourEyes::needsSecondAdmin($cur, $amount, [$regionId])) {
                FourEyes::request('manual_adjustment', ['userId' => $uid, 'currency' => $cur, 'direction' => $direction,
                    'amount' => $amount, 'regionId' => $regionId], $cur, $amount, $reason, $regionId);
                return redirect()->route('finance.approvals')->with('success', __('Sent for a second admin\'s approval.'));
            }
            $written = (new Operations($db))->adjust($uid, $cur, $direction, $amount, $reason, ['regionId' => $regionId]);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
        /* Opened from the customer's page (point 93): back there, where the new balance shows. */
        if ($request->input('from') === 'customer') {
            return redirect()->route('finance.customers.show', $uid)->with('success', __('Adjustment posted:') . ' ' . $written['reference']);
        }
        return redirect()->route('finance.ledger.show', $written['txId'])->with('success', __('Adjustment posted:') . ' ' . $written['reference']);
    }

    /* =========================================================== approvals */

    /** A region-bound admin sees only requests of their regions (and reversals, which name none). */
    private function approvalsQuery()
    {
        $q = DB::table('finance_approvals');
        $allowed = Regions::allowedIds();
        if ($allowed) {
            $q->where(function ($w) use ($allowed) {
                $w->whereNull('region_id')->orWhereIn('region_id', $allowed);
            });
        }
        return $q;
    }

    public function approvals()
    {
        $rows = $this->approvalsQuery()->orderByRaw("status = 'pending' desc")->orderBy('id', 'desc')->limit(200)->get();
        $regions = new Regions(app(Firestore::class));
        return view('finance.approvals.index', [
            'rows' => $rows,
            'regionNames' => array_map(function ($r) { return $r['name'] ?? ''; }, $regions->all()),
            'canApprove' => $this->can('finance.ledger.approve'),
            'me' => Auth::id(),
        ]);
    }

    public function approve(Request $request, $id)
    {
        abort_unless($this->approvalsQuery()->where('id', $id)->exists(), 404);
        try {
            $row = FourEyes::claim($id);
        } catch (FinanceException $e) {
            return back()->with('error', $e->getMessage());
        }
        $p = json_decode($row->payload, true);
        $context = ['requestedBy' => $row->requested_by_email, 'approvedBy' => (string)Auth::user()->email, 'approvalId' => (int)$row->id];
        try {
            $ops = new Operations();
            if ($row->action === 'reversal') {
                $written = $ops->reverse($p['txId'], $row->reason, $context);
                $result = ['txIds' => array_column($written, 'txId'), 'references' => array_column($written, 'reference')];
            } else {
                $written = $ops->adjust($p['userId'], $p['currency'], $p['direction'], (int)$p['amount'], $row->reason,
                    $context + ['regionId' => $p['regionId'] ?? null]);
                $result = ['txIds' => [$written['txId']], 'references' => [$written['reference']]];
            }
            FourEyes::finish($id, 'executed', $result, $request->input('note'));
        } catch (\Throwable $e) {
            FourEyes::finish($id, 'failed', ['error' => $this->failure($e)], $request->input('note'));
            return back()->with('error', 'Approved, but it could not be executed: ' . $this->failure($e));
        }
        return back()->with('success', 'Approved and executed: ' . implode(', ', $result['references']));
    }

    public function reject(Request $request, $id)
    {
        abort_unless($this->approvalsQuery()->where('id', $id)->exists(), 404);
        try {
            FourEyes::claim($id);
        } catch (FinanceException $e) {
            return back()->with('error', $e->getMessage());
        }
        FourEyes::finish($id, 'rejected', null, $request->input('note'));
        return back()->with('success', 'Rejected.');
    }

    /* =============================================================== audit */

    public function audit(Request $request)
    {
        $from = $request->input('from') ?: now()->subDays(7)->format('Y-m-d');
        $to = $request->input('to') ?: now()->format('Y-m-d');
        $q = trim((string)$request->input('q'));
        $rows = [];
        $error = null;
        try {
            $rows = (app(Firestore::class))->query('admin_audit_logs', [
                ['at', '>=', new \DateTimeImmutable($from . ' 00:00:00')],
                ['at', '<', (new \DateTimeImmutable($to . ' 00:00:00'))->modify('+1 day')],
            ], [['at', 'desc']], 1000);
        } catch (\Throwable $e) {
            $error = $this->failure($e);
        }
        if ($q !== '') {
            $rows = array_values(array_filter($rows, function ($r) use ($q) {
                $d = $r['data'];
                $hay = strtolower(implode(' ', [$d['adminEmail'] ?? '', $d['action'] ?? '', $d['collection'] ?? '', $d['docId'] ?? '',
                    $d['reason'] ?? '', json_encode(\App\Finance\Value::plain($d['after'] ?? null))]));
                return strpos($hay, strtolower($q)) !== false;
            }));
        }
        return view('finance.audit.index', compact('rows', 'from', 'to', 'q', 'error'));
    }
}
