<?php

namespace App\Http\Controllers;

use App\Finance\FinanceException;
use App\Finance\Firestore;
use App\Finance\FirestoreError;
use App\Finance\Insurance;
use App\Finance\Locks;
use App\Finance\Money;
use App\Finance\Notifier;
use App\Finance\Regions;
use App\Finance\Scope;
use App\Finance\Storage;
use Illuminate\Http\Request;

/**
 * Spideli Finance, admin Phase D2 (spec 6.11): insurance partners, the
 * product catalog, the work queues (quotes to price, policies to activate,
 * cancellations, renewals, claims) and each quote, policy and claim. The
 * work is in App\Finance\Insurance; this controller reads, scopes the
 * customers to the admin's regions, locks the item being worked on and
 * notifies the customers.
 */
class FinanceInsuranceController extends Controller
{
    private const MAX_PDF_KB = 20480;

    public const TABS = [
        'quotes' => ['insurance_quotes', ['requested']],
        'activate' => ['insurance_policies', ['pending_activation']],
        'claims' => ['insurance_claims', ['submitted', 'under_review', 'info_requested', 'approved']],
        'cancellations' => ['insurance_policies', ['cancellation_requested']],
        'renewals' => ['insurance_policies', ['renewal_due']],
        'policies' => ['insurance_policies', ['active', 'lapsed', 'cancelled', 'expired', 'rejected']],
    ];

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
            $x = Insurance::date($a['data'][$field] ?? null);
            $y = Insurance::date($b['data'][$field] ?? null);
            $x = $x ? $x->getTimestamp() : 0;
            $y = $y ? $y->getTimestamp() : 0;
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

    private static function day($ts)
    {
        $d = Insurance::date($ts);
        return $d ? $d->setTimezone(new \DateTimeZone(config('app.timezone')))->format('Y-m-d') : '';
    }

    /** A form date (Y-m-d, the panel's timezone) → DateTime at noon, or null. */
    private static function formDate($value)
    {
        $d = \DateTimeImmutable::createFromFormat('Y-m-d H:i', trim((string)$value) . ' 12:00', new \DateTimeZone(config('app.timezone')));
        return $d ?: null;
    }

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
            $out[$k] = ['name' => $f->getClientOriginalName(), 'file' => $f->getRealPath()];
        }
        return $out;
    }

    /** Uploaded PDFs with their typed titles: [['name', 'fileName', 'file'], ...]. */
    private static function titledPdfs(Request $request)
    {
        $files = self::pdfs($request->file('documents'));
        if (is_string($files)) {
            throw new FinanceException('invalid', $files);
        }
        $titles = (array)$request->input('documentTitles', []);
        foreach ($files as $i => $d) {
            $files[$i] = ['name' => trim((string)($titles[$i] ?? '')) ?: pathinfo($d['name'], PATHINFO_FILENAME), 'fileName' => $d['name'], 'file' => $d['file']];
        }
        return $files;
    }

    private function scoped($path, $userField = 'userId')
    {
        $doc = $this->db()->get($path);
        abort_unless($doc && Scope::allows($this->db(), $doc, $userField), 404);
        return $doc;
    }

    /* ============================================================= queues */

    public function index(Request $request)
    {
        $db = $this->db();
        $tab = $request->input('tab', 'quotes');
        abort_unless(isset(self::TABS[$tab]), 404);
        $counts = [];
        $rows = [];
        foreach (self::TABS as $k => [$col, $statuses]) {
            if ($k === 'policies') {
                continue;
            }
            $found = Scope::filter($db, $db->query($col, [['status', 'in', $statuses]], [], 1000), 'userId');
            $counts[$k] = count($found);
            if ($k === $tab) {
                $rows = $found;
            }
        }
        if ($tab === 'policies') {
            $status = $request->input('status', 'active');
            $rows = Scope::filter($db, $db->query('insurance_policies', [['status', 'in', $status === 'all' ? self::TABS['policies'][1] : [$status]]], [], 2000), 'userId');
        }
        $rows = self::sortBy($rows, 'createdAt', $tab !== 'policies');
        return view('finance.insurance.index', [
            'tab' => $tab, 'rows' => $rows, 'counts' => $counts, 'status' => $request->input('status', 'active'),
            'names' => $this->names(array_map(function ($r) { return $r['data']['userId'] ?? null; }, $rows)),
        ]);
    }

    /* ========================================================== partners */

    public function partners(Request $request)
    {
        $rows = $this->db()->query('insurance_partners', [], [], 500);
        usort($rows, function ($a, $b) { return strcasecmp($a['data']['name'] ?? '', $b['data']['name'] ?? ''); });
        $edit = null;
        foreach ($rows as $r) {
            if ($r['id'] === $request->input('edit')) {
                $edit = $r;
            }
        }
        return view('finance.insurance.partners', ['rows' => $rows, 'edit' => $edit, 'canManage' => $this->can('finance.insurance.manage')]);
    }

    public function partnerSave(Request $request)
    {
        try {
            (new Insurance($this->db()))->savePartner((string)$request->input('id') ?: null, $request->all());
            return redirect()->route('finance.insurance.partners')->with('success', __('Partner saved.'));
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    /* ========================================================== products */

    public function products()
    {
        $rows = $this->db()->query('insurance_products', [], [], 500);
        usort($rows, function ($a, $b) { return [(int)($a['data']['order'] ?? 0), $a['data']['name'] ?? ''] <=> [(int)($b['data']['order'] ?? 0), $b['data']['name'] ?? '']; });
        return view('finance.insurance.products', ['rows' => $rows, 'canManage' => $this->can('finance.insurance.manage')]);
    }

    private function productForm($product = null, $id = null)
    {
        $db = $this->db();
        $regions = new Regions($db);
        $currencies = [];
        $categories = [];
        foreach ($regions->visible(true) as $r) {
            [$settings] = $regions->moduleSettings('insurance', [$r['id']]);
            foreach ((array)($settings['currencies'] ?? [$r['currency']]) as $c) {
                if (Money::isCurrency($c)) {
                    $currencies[$c] = true;
                }
            }
            foreach ((array)($settings['categories'] ?? []) as $c) {
                $categories[$c] = true;
            }
        }
        $currencies = array_keys($currencies);
        sort($currencies);
        $partners = [];
        foreach ($db->query('insurance_partners', [], [], 500) as $p) {
            $partners[$p['id']] = $p['data'];
        }
        $categories = array_values(array_intersect(Insurance::CATEGORIES, array_keys($categories))) ?: Insurance::CATEGORIES;
        return ['currencies' => $currencies, 'categories' => $categories, 'partners' => $partners, 'symbols' => $regions->currencySymbols(), 'product' => $product, 'id' => $id];
    }

    public function productCreate()
    {
        return view('finance.insurance.product', $this->productForm());
    }

    public function productEdit($id)
    {
        $doc = $this->db()->get('insurance_products/' . $id);
        abort_unless($doc, 404);
        return view('finance.insurance.product', $this->productForm($doc['data'], $id));
    }

    public function productSave(Request $request, $id = null)
    {
        $db = $this->db();
        try {
            $existing = null;
            if ($id !== null) {
                $doc = $db->get('insurance_products/' . $id);
                abort_unless($doc, 404);
                $existing = $doc['data'];
            }
            $form = $this->productForm();
            $p = Insurance::productFromInput($request->all(), $form['partners'], $existing);
            if (!$existing && !in_array($p['currency'], $form['currencies'], true)) {
                throw new FinanceException('invalid', 'This currency is not one of the insurance currencies of a finance region.');
            }
            $id = (new Insurance($db))->saveProduct($id, $p, self::titledPdfs($request), (array)$request->input('removeDocuments', []));
            return redirect()->route('finance.insurance.products.edit', $id)->with('success', __('Product saved.'));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    /** A product, quote or policy document: a signed link valid 5 minutes. */
    public function document($kind, $id, $index)
    {
        $col = ['products' => 'insurance_products', 'quotes' => 'insurance_quotes', 'policies' => 'insurance_policies', 'claims' => 'insurance_claims'][$kind] ?? null;
        abort_unless($col, 404);
        $doc = $kind === 'products' ? $this->db()->get($col . '/' . $id) : $this->scoped($col . '/' . $id);
        $d = $doc ? (array)($doc['data']['documents'][(int)$index] ?? []) : [];
        abort_unless($d, 404);
        if (!empty($d['storagePath'])) {
            return redirect()->away(Storage::signedUrl($d['storagePath']));
        }
        abort_unless(!empty($d['url']) && preg_match('#^https://#', $d['url']), 404);
        return redirect()->away($d['url']);
    }

    /* ============================================================ quotes */

    public function quote($id)
    {
        $db = $this->db();
        $doc = $this->scoped('insurance_quotes/' . $id);
        $q = $doc['data'];
        $product = $db->get('insurance_products/' . ($q['productId'] ?? ''));
        $manage = $this->can('finance.insurance.manage');
        $lockedBy = $manage && ($q['status'] ?? '') === 'requested' ? Locks::take('insurance_quotes/' . $id) : null;
        return view('finance.insurance.quote', [
            'id' => $id, 'q' => $q, 'product' => $product ? $product['data'] : null,
            'suggestion' => $product ? Insurance::suggest($product['data'], (array)($q['answers'] ?? []), $q['frequency'] ?? 'annual') : null,
            'names' => $this->names([$q['userId'] ?? null]), 'validDays' => (int)config('finance.insurance_quote_valid_days', 14),
            'lockedBy' => $lockedBy, 'canManage' => $manage && $lockedBy === null,
        ]);
    }

    public function quoteAction(Request $request, $id, $action)
    {
        $db = $this->db();
        $ins = new Insurance($db);
        $notifier = new Notifier($db);
        $reason = trim((string)$request->input('reason'));
        try {
            Locks::assertMine('insurance_quotes/' . $id);
            $doc = $this->scoped('insurance_quotes/' . $id);
            $cur = $doc['data']['currency'] ?? '';
            if ($action === 'price') {
                $q = $ins->priceQuote($id, [
                    'premium' => Money::toMinor($request->input('premium'), $cur) ?? 0,
                    'installmentAmount' => Money::toMinor($request->input('installmentAmount'), $cur) ?? 0,
                    'deductible' => Money::toMinor($request->input('deductible'), $cur) ?? 0,
                    'coverStart' => self::formDate($request->input('coverStart')),
                    'validUntil' => ($v = self::formDate($request->input('validUntil'))) ? $v->setTime(23, 59) : null,
                    'insurerReference' => $request->input('insurerReference'),
                ]);
                Locks::release('insurance_quotes/' . $id);
                return $this->done(__('Quote sent to the customer.'), [$notifier->send($q['userId'] ?? '', 'insurance_quote_ready',
                    ['productName' => $q['productName'] ?? '', 'amount' => Money::format((int)$q['installmentAmount'], $cur) . ' (' . ($q['frequency'] ?? '') . ')',
                     'validUntil' => self::day($q['validUntil'])], ['quoteId' => $id])]);
            }
            if ($action === 'refuse') {
                $q = $ins->refuseQuote($id, $reason);
                Locks::release('insurance_quotes/' . $id);
                return $this->done(__('Quote refused.'), [$notifier->send($q['userId'] ?? '', 'insurance_quote_rejected', ['productName' => $q['productName'] ?? '', 'reason' => $reason], ['quoteId' => $id])]);
            }
            abort(404);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    /* ========================================================== policies */

    public function policy($id)
    {
        $db = $this->db();
        $doc = $this->scoped('insurance_policies/' . $id);
        $p = $doc['data'];
        $premiums = $db->query('insurance_premiums', [['policyId', '==', $id]], [], 500);
        usort($premiums, function ($a, $b) { return (int)substr(strrchr($a['id'], '_'), 1) <=> (int)substr(strrchr($b['id'], '_'), 1); });
        $claims = self::sortBy($db->query('insurance_claims', [['policyId', '==', $id]], [], 300), 'createdAt');
        $manage = $this->can('finance.insurance.manage');
        $actionable = in_array($p['status'] ?? '', ['pending_activation', 'renewal_due', 'cancellation_requested', 'active'], true);
        $lockedBy = $manage && $actionable ? Locks::take('insurance_policies/' . $id) : null;
        $renewalTx = !empty($p['renewalTxId']) ? $db->get('ledger_transactions/' . $p['renewalTxId']) : null;
        return view('finance.insurance.policy', [
            'id' => $id, 'p' => $p, 'premiums' => $premiums, 'claims' => $claims, 'renewalTx' => $renewalTx ? $renewalTx['data'] : null,
            'refundSuggestion' => Insurance::refundSuggestion($p, $premiums, new \DateTimeImmutable()),
            'names' => $this->names([$p['userId'] ?? null]), 'lockedBy' => $lockedBy, 'canManage' => $manage && $lockedBy === null,
        ]);
    }

    public function policyAction(Request $request, $id, $action)
    {
        $db = $this->db();
        $ins = new Insurance($db);
        $notifier = new Notifier($db);
        $reason = trim((string)$request->input('reason'));
        try {
            Locks::assertMine('insurance_policies/' . $id);
            $doc = $this->scoped('insurance_policies/' . $id);
            $p = $doc['data'];
            $cur = $p['currency'] ?? '';
            $uid = $p['userId'] ?? '';
            $name = $p['productName'] ?? '';
            switch ($action) {
                case 'activate':
                    if (($p['status'] ?? '') !== 'pending_activation') {
                        throw new FinanceException('state_changed', 'This policy is now "' . ($p['status'] ?? '?') . '".');
                    }
                    $docs = [];
                    foreach (self::titledPdfs($request) as $i => $d) {
                        $docs[] = ['name' => $d['name'], 'storagePath' => Storage::upload($d['file'],
                            'insurance_policies/' . $uid . '/' . $id . '/' . (int)floor(microtime(true) * 1000) . '-' . $i . '-' . Storage::safeName($d['fileName']))];
                    }
                    $r = $ins->activate($id, $request->input('policyNumber'), $docs);
                    Locks::release('insurance_policies/' . $id);
                    return $this->done(__('Policy activated.'), [$notifier->send($uid, 'insurance_policy_active',
                        ['productName' => $name, 'policyNumber' => $r['policyNumber'], 'coverStart' => self::day($r['coverStart'])], ['policyId' => $id])]);
                case 'refuse':
                    $r = $ins->refusePolicy($id, $reason);
                    Locks::release('insurance_policies/' . $id);
                    return $this->done(__('Policy refused; the first premium is back in the customer\'s wallet.'),
                        [$notifier->send($uid, 'insurance_policy_rejected', ['productName' => $name, 'reason' => $reason], ['policyId' => $id])]);
                case 'start-renewal':
                    $r = $ins->startRenewal($id, Money::toMinor($request->input('renewalPremium'), $cur));
                    return $this->done(__('Renewal offered to the customer.'), [$notifier->send($uid, 'insurance_renewal_due',
                        ['productName' => $name, 'amount' => Money::format($r['renewalPremium'], $cur), 'renewalDate' => self::day($r['renewalDate'])], ['policyId' => $id])]);
                case 'renewal-premium':
                    $ins->setRenewalPremium($id, Money::toMinor($request->input('renewalPremium'), $cur) ?? 0);
                    $amount = Money::toMinor($request->input('renewalPremium'), $cur);
                    return $this->done(__('Renewal premium changed.'), [$notifier->send($uid, 'insurance_renewal_due',
                        ['productName' => $name, 'amount' => Money::format((int)$amount, $cur), 'renewalDate' => self::day($p['renewalDate'] ?? $p['coverEnd'] ?? null)], ['policyId' => $id])]);
                case 'renew':
                    $r = $ins->renewPaid($id);
                    return $this->done(__('Policy renewed.'), [$notifier->send($uid, 'insurance_policy_renewed', ['productName' => $name, 'coverEnd' => self::day($r['coverEnd'])], ['policyId' => $id])]);
                case 'cancel':
                case 'keep':
                    $refund = $action === 'cancel' ? (Money::toMinor($request->input('refund'), $cur) ?? 0) : 0;
                    $r = $ins->cancellation($id, $action === 'cancel', $refund, $reason);
                    Locks::release('insurance_policies/' . $id);
                    if ($action === 'keep') {
                        return $this->done(__('The policy is kept:') . ' ' . $r['status'] . '.');
                    }
                    return $this->done(__('Policy cancelled.') . ($refund ? ' ' . __('Refunded:') . ' ' . Money::format($refund, $cur) . '.' : ''),
                        [$notifier->send($uid, 'insurance_policy_cancelled', ['productName' => $name], ['policyId' => $id])]);
            }
            abort(404);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    /* ============================================================ claims */

    public function claim($id)
    {
        $db = $this->db();
        $doc = $this->scoped('insurance_claims/' . $id);
        $c = $doc['data'];
        $policy = $db->get('insurance_policies/' . ($c['policyId'] ?? ''));
        $events = self::sortBy($db->query('insurance_claims/' . $id . '/events', [], [], 200), 'createdAt', true);
        $manage = $this->can('finance.insurance.manage');
        $open = in_array($c['status'] ?? '', ['submitted', 'under_review', 'info_requested', 'approved'], true);
        $lockedBy = $manage && $open ? Locks::take('insurance_claims/' . $id) : null;
        return view('finance.insurance.claim', [
            'id' => $id, 'c' => $c, 'policy' => $policy ? $policy['data'] : null, 'events' => $events,
            'names' => $this->names([$c['userId'] ?? null]), 'lockedBy' => $lockedBy, 'canManage' => $manage && $lockedBy === null,
        ]);
    }

    /** A claim photo or document the customer sent: a signed link valid 5 minutes. */
    public function claimFile($id, $index)
    {
        $doc = $this->scoped('insurance_claims/' . $id);
        $d = (array)($doc['data']['documents'][(int)$index] ?? []);
        $path = $d['storagePath'] ?? (is_string($doc['data']['documents'][(int)$index] ?? null) ? $doc['data']['documents'][(int)$index] : '');
        abort_unless($path !== '' && strpos($path, 'insurance_claims/') === 0, 404);
        return redirect()->away(Storage::signedUrl($path));
    }

    public function claimAction(Request $request, $id, $action)
    {
        $db = $this->db();
        $notifier = new Notifier($db);
        $text = trim((string)$request->input('reason'));
        $map = ['review' => 'review', 'request-info' => 'info', 'approve' => 'approve', 'pay' => 'pay', 'reject' => 'reject'];
        abort_unless(isset($map[$action]), 404);
        try {
            Locks::assertMine('insurance_claims/' . $id);
            $doc = $this->scoped('insurance_claims/' . $id);
            $cur = $doc['data']['currency'] ?? '';
            $c = (new Insurance($db))->claim($id, $map[$action], $text, Money::toMinor($request->input('amount'), $cur));
            $uid = $c['userId'] ?? '';
            $name = $c['productName'] ?? '';
            $data = ['claimId' => $id, 'policyId' => $c['policyId'] ?? ''];
            switch ($map[$action]) {
                case 'review':
                    return $this->done(__('Claim under review.'), [$notifier->send($uid, 'insurance_claim_received', ['productName' => $name, 'amount' => Money::format((int)($c['amountClaimed'] ?? 0), $cur)], $data)]);
                case 'info':
                    return $this->done(__('Question sent to the customer.'), [$notifier->send($uid, 'insurance_claim_info_requested', ['productName' => $name, 'reason' => $text], $data)]);
                case 'approve':
                    return $this->done(__('Claim approved. Pay it when the insurer has paid.'), [$notifier->send($uid, 'insurance_claim_approved', ['productName' => $name, 'amount' => Money::format((int)$c['approvedAmount'], $cur)], $data)]);
                case 'pay':
                    Locks::release('insurance_claims/' . $id);
                    return $this->done(__('Claim paid to the customer\'s Finance wallet.'), [$notifier->send($uid, 'insurance_claim_paid', ['productName' => $name, 'amount' => Money::format((int)$c['paidAmount'], $cur)], $data)]);
                case 'reject':
                    Locks::release('insurance_claims/' . $id);
                    return $this->done(__('Claim rejected.'), [$notifier->send($uid, 'insurance_claim_rejected', ['productName' => $name, 'reason' => $text], $data)]);
            }
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }
}
