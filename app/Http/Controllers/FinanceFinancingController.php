<?php

namespace App\Http\Controllers;

use App\Finance\FinanceException;
use App\Finance\Financing;
use App\Finance\Firestore;
use App\Finance\FirestoreError;
use App\Finance\Locks;
use App\Finance\Money;
use App\Finance\Notifier;
use App\Finance\Regions;
use App\Finance\Scope;
use App\Finance\Storage;
use Illuminate\Http\Request;

/**
 * Spideli Finance, admin Phase D3a (spec 6.12, 6.13): project financing
 * review, financial resources, and investment opportunities with the
 * interests investors send. The work is in App\Finance\Financing; this
 * controller reads, scopes to the admin's regions, locks the application
 * being reviewed and notifies the customers.
 */
class FinanceFinancingController extends Controller
{
    private const MAX_FILE_KB = 20480;

    public const TABS = [
        'review' => ['submitted'],
        'in_review' => ['under_review'],
        'info' => ['additional_info_required'],
        'approved' => ['approved'],
        'funded' => ['funded'],
        'closed' => ['closed', 'rejected', 'withdrawn'],
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

    /** One uploaded file of the allowed types: ['name', 'file', 'contentType'], null when none, or an error message. */
    private static function file($f, array $types = ['application/pdf'])
    {
        if (!$f) {
            return null;
        }
        if (!$f->isValid()) {
            return __('A file could not be received. Try again.');
        }
        $head = (string)@file_get_contents($f->getRealPath(), false, null, 0, 8);
        $kind = strpos($head, '%PDF-') === 0 ? 'application/pdf' : (strpos($head, "\xFF\xD8\xFF") === 0 ? 'image/jpeg' : (strpos($head, "\x89PNG") === 0 ? 'image/png' : ''));
        if (!in_array($kind, $types, true)) {
            return __('This kind of file cannot be added.') . ' (' . $f->getClientOriginalName() . ')';
        }
        if ($f->getSize() > self::MAX_FILE_KB * 1024) {
            return __('A file can be at most 20 MB.') . ' (' . $f->getClientOriginalName() . ')';
        }
        return ['name' => $f->getClientOriginalName(), 'file' => $f->getRealPath(), 'contentType' => $kind];
    }

    private function settingsFor($ownerId)
    {
        $u = $ownerId ? $this->db()->get('users/' . $ownerId) : null;
        return (new Regions($this->db()))->moduleSettings('financing', (array)($u['data']['regionIds'] ?? []))[0];
    }

    /* ======================================================= applications */

    public function index(Request $request)
    {
        $db = $this->db();
        $tab = $request->input('tab', 'review');
        abort_unless(isset(self::TABS[$tab]), 404);
        $counts = [];
        $rows = [];
        foreach (self::TABS as $k => $statuses) {
            $found = Scope::filter($db, $db->query('financing_applications', [['status', 'in', $statuses]], [], 1000), 'ownerId');
            $counts[$k] = count($found);
            if ($k === $tab) {
                $rows = $found;
            }
        }
        return view('finance.financing.index', [
            'tab' => $tab, 'counts' => $counts, 'rows' => self::sortBy($rows, 'submittedAt', $tab !== 'closed'),
            'names' => $this->names(array_map(function ($r) { return $r['data']['ownerId'] ?? null; }, $rows)),
        ]);
    }

    public function application($id)
    {
        $db = $this->db();
        $doc = $db->get('financing_applications/' . $id);
        abort_unless($doc && Scope::allows($db, $doc, 'ownerId'), 404);
        $a = $doc['data'] + ['id' => $id];
        $documents = self::sortBy($db->query('financing_documents', [['applicationId', '==', $id]], [], 300), 'uploadedAt', true);
        $settings = $this->settingsFor($a['ownerId'] ?? '');
        $manage = $this->can('finance.financing.manage');
        $open = in_array($a['status'] ?? '', ['submitted', 'under_review', 'additional_info_required', 'approved', 'funded'], true);
        $lockedBy = $manage && $open ? Locks::take('financing_applications/' . $id) : null;
        $project = $db->get('financing_projects/' . ($a['projectId'] ?? ''));
        return view('finance.financing.show', [
            'id' => $id, 'a' => $a, 'project' => $project ? $project['data'] : null, 'documents' => $documents,
            'required' => Financing::requiredStatus($settings, $a, $documents),
            'reviews' => self::sortBy($db->query('financing_reviews', [['applicationId', '==', $id]], [], 300), 'at', true),
            'notes' => self::sortBy($db->query('financing_internal_notes', [['applicationId', '==', $id]], [], 300), 'at', true),
            'names' => $this->names([$a['ownerId'] ?? null]), 'lockedBy' => $lockedBy, 'canManage' => $manage && $lockedBy === null,
        ]);
    }

    public function applicationAction(Request $request, $id, $action)
    {
        $db = $this->db();
        $fin = new Financing($db);
        $notifier = new Notifier($db);
        $reason = trim((string)$request->input('reason'));
        try {
            Locks::assertMine('financing_applications/' . $id);
            $doc = $db->get('financing_applications/' . $id);
            abort_unless($doc && Scope::allows($db, $doc, 'ownerId'), 404);
            $a = $doc['data'];
            $cur = $a['currency'] ?? '';
            $owner = $a['ownerId'] ?? '';
            $project = ['projectName' => $a['projectName'] ?? ''];
            $data = ['applicationId' => $id];
            switch ($action) {
                case 'review':
                    $fin->startReview($id, $reason);
                    return $this->done(__('Review started.'), [$notifier->send($owner, 'financing_under_review', $project, $data)]);
                case 'request-info':
                    $fin->requestInfo($id, $reason);
                    return $this->done(__('Information requested from the owner.'), [$notifier->send($owner, 'financing_info_required', $project + ['reason' => $reason], $data)]);
                case 'approve':
                    $r = $fin->approve($id, Money::toMinor($request->input('approvedAmount'), $cur), $request->input('institutionName'), $reason, $this->settingsFor($owner));
                    return $this->done(__('Application approved.'), [$notifier->send($owner, 'financing_approved', $project + ['amount' => Money::format((int)$r['approvedAmount'], $cur)], $data)]);
                case 'reject':
                    $fin->reject($id, $reason);
                    Locks::release('financing_applications/' . $id);
                    return $this->done(__('Application rejected.'), [$notifier->send($owner, 'financing_rejected', $project + ['reason' => $reason], $data)]);
                case 'fund':
                    $at = \DateTimeImmutable::createFromFormat('Y-m-d H:i', trim((string)$request->input('fundedAt')) . ' 12:00', new \DateTimeZone(config('app.timezone')));
                    if (!$at) {
                        throw new FinanceException('invalid', 'Enter the date the institution paid.');
                    }
                    $r = $fin->fund($id, Money::toMinor($request->input('fundedAmount'), $cur), $at, $reason);
                    return $this->done(__('Funding recorded.'), [$notifier->send($owner, 'financing_funded', $project + ['amount' => Money::format((int)$r['fundedAmount'], $cur)], $data)]);
                case 'close':
                    $fin->close($id, $reason);
                    Locks::release('financing_applications/' . $id);
                    return $this->done(__('Application closed.'), [$notifier->send($owner, 'financing_closed', $project, $data)]);
                case 'note':
                    $fin->internalNote($id, $reason);
                    return $this->done(__('Internal note saved.'));
                case 'document':
                    $accept = $request->input('decision') === 'accept';
                    $d = $fin->document((string)$request->input('documentId'), $accept, $reason);
                    return $accept ? $this->done(__('Document accepted.'))
                        : $this->done(__('Document rejected.'), [$notifier->send($owner, 'financing_document_rejected',
                            ['documentName' => $d['name'] ?? '', 'projectName' => $d['projectName'], 'note' => $reason], $data)]);
            }
            abort(404);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    /** An uploaded application document: a signed link valid 5 minutes. */
    public function documentFile($docId)
    {
        $db = $this->db();
        $doc = $db->get('financing_documents/' . $docId);
        abort_unless($doc && Scope::allows($db, $doc, 'ownerId'), 404);
        $path = (string)($doc['data']['storagePath'] ?? '');
        abort_unless(strpos($path, 'financing_documents/') === 0, 404);
        return redirect()->away(Storage::signedUrl($path));
    }

    /* ========================================================== resources */

    public function resources(Request $request)
    {
        $rows = $this->db()->query('financial_resources', [], [], 500);
        usort($rows, function ($a, $b) { return [(int)($a['data']['order'] ?? 0), $a['data']['title'] ?? ''] <=> [(int)($b['data']['order'] ?? 0), $b['data']['title'] ?? '']; });
        $edit = null;
        foreach ($rows as $r) {
            if ($r['id'] === $request->input('edit')) {
                $edit = $r;
            }
        }
        return view('finance.financing.resources', ['rows' => $rows, 'edit' => $edit, 'new' => $request->has('new'), 'canManage' => $this->can('finance.financing.manage')]);
    }

    public function resourceSave(Request $request)
    {
        try {
            $file = self::file($request->file('file'), ['application/pdf', 'image/jpeg', 'image/png']);
            if (is_string($file)) {
                throw new FinanceException('invalid', $file);
            }
            $id = (new Financing($this->db()))->saveResource((string)$request->input('id') ?: null, $request->all(), $file);
            return redirect()->route('finance.financing.resources', ['edit' => $id])->with('success', __('Resource saved.'));
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    public function resourceFile($id)
    {
        $doc = $this->db()->get('financial_resources/' . $id);
        abort_unless($doc, 404);
        $d = $doc['data'];
        if (!empty($d['storagePath'])) {
            return redirect()->away(Storage::signedUrl($d['storagePath']));
        }
        abort_unless(!empty($d['url']) && preg_match('#^https://#', $d['url']), 404);
        return redirect()->away($d['url']);
    }

    /* ====================================================== opportunities */

    /** Projects with an application approved, funded or closed: the ones an opportunity may be published for. */
    private function reviewedProjects()
    {
        $db = $this->db();
        $apps = Scope::filter($db, $db->query('financing_applications', [['status', 'in', ['approved', 'funded', 'closed']]], [], 1000), 'ownerId');
        $ids = array_values(array_unique(array_filter(array_map(function ($a) { return empty($a['data']['demo']) ? ($a['data']['projectId'] ?? null) : null; }, $apps))));
        $out = [];
        foreach ($ids ? $db->getMany(array_map(function ($i) { return 'financing_projects/' . $i; }, $ids)) : [] as $doc) {
            if ($doc) {
                $out[$doc['id']] = $doc['data'] + ['id' => $doc['id']];
            }
        }
        return $out;
    }

    /** Sectors, currencies and countries offered on the opportunity form, from the regions' settings. */
    private function opportunityLists()
    {
        $regions = new Regions($this->db());
        $sectors = [];
        $currencies = [];
        foreach ($regions->visible(true) as $r) {
            [$intel] = $regions->moduleSettings('intelligence', [$r['id']]);
            [$fin] = $regions->moduleSettings('financing', [$r['id']]);
            foreach (array_merge((array)($intel['matching']['sectors'] ?? []), (array)($fin['sectors'] ?? [])) as $s) {
                $sectors[$s] = true;
            }
            foreach (array_merge((array)($intel['matching']['currencies'] ?? []), (array)($fin['currencies'] ?? [])) as $c) {
                if (Money::isCurrency($c)) {
                    $currencies[$c] = true;
                }
            }
        }
        ksort($sectors);
        ksort($currencies);
        return ['sectors' => array_keys($sectors), 'currencies' => array_keys($currencies)];
    }

    public function opportunities()
    {
        $db = $this->db();
        $rows = self::sortBy($db->query('investment_opportunities', [], [], 500), 'createdAt');
        $interested = [];
        foreach ($db->query('opportunity_interests', [['interested', '==', true]], [], 5000) as $i) {
            $interested[$i['data']['opportunityId'] ?? ''] = ($interested[$i['data']['opportunityId'] ?? ''] ?? 0) + 1;
        }
        return view('finance.financing.opportunities', ['rows' => $rows, 'interested' => $interested, 'canManage' => $this->can('finance.financing.manage')]);
    }

    public function opportunityForm($id = null)
    {
        $db = $this->db();
        $o = null;
        $ownerId = '';
        if ($id) {
            $doc = $db->get('investment_opportunities/' . $id);
            abort_unless($doc, 404);
            $o = $doc['data'];
            $owner = $db->get('opportunity_owners/' . $id);
            $ownerId = $owner['data']['ownerId'] ?? '';
        }
        $projects = $this->reviewedProjects();
        if ($o && !empty($o['projectId']) && !isset($projects[$o['projectId']])) {
            $p = $db->get('financing_projects/' . $o['projectId']);
            if ($p) {
                $projects[$p['id']] = $p['data'] + ['id' => $p['id']];
            }
        }
        return view('finance.financing.opportunity', $this->opportunityLists() + [
            'id' => $id, 'o' => $o, 'projects' => $projects, 'ownerId' => $ownerId, 'symbols' => (new Regions($db))->currencySymbols(),
            'names' => $this->names(array_map(function ($p) { return $p['ownerId'] ?? null; }, $projects)),
        ]);
    }

    public function opportunitySave(Request $request, $id = null)
    {
        $db = $this->db();
        $fin = new Financing($db);
        try {
            $existing = null;
            if ($id) {
                $doc = $db->get('investment_opportunities/' . $id);
                abort_unless($doc, 404);
                $existing = $doc['data'];
            }
            $projects = $this->reviewedProjects();
            $pid = (string)$request->input('projectId');
            if (!isset($projects[$pid]) && !($existing && ($existing['projectId'] ?? '') === $pid && $pid !== '')) {
                throw new FinanceException('invalid', 'Choose the project: an opportunity is published only for a project whose financing was reviewed and approved.');
            }
            if (!isset($projects[$pid])) {
                $p = $db->get('financing_projects/' . $pid);
                abort_unless($p, 404);
                $projects[$pid] = $p['data'] + ['id' => $pid];
            }
            $project = $projects[$pid];
            $o = Financing::opportunityFromInput($request->all(), $project, $existing);
            if (!$existing && !in_array($o['currency'], $this->opportunityLists()['currencies'], true)) {
                throw new FinanceException('invalid', 'This currency is not one of the regions\' financing or matching currencies.');
            }
            $docs = [];
            $titles = (array)$request->input('documentTitles', []);
            foreach (array_filter((array)$request->file('documents', [])) as $i => $f) {
                $file = self::file($f);
                if (is_string($file)) {
                    throw new FinanceException('invalid', $file);
                }
                $docs[$i] = ['name' => trim((string)($titles[$i] ?? '')) ?: pathinfo($file['name'], PATHINFO_FILENAME), 'fileName' => $file['name'], 'file' => $file['file']];
            }
            $r = $fin->saveOpportunity($id, $o, $project['ownerId'] ?? '', $docs, (array)$request->input('removeDocuments', []));
            $sent = $r['nowOpen'] ? $fin->notifyMatches($r['id'], new Notifier($db)) : [];
            return redirect()->route('finance.financing.opportunities.show', $r['id'])->with('success', __('Opportunity saved.')
                . ($sent ? ' ' . __('Matching investors notified:') . ' ' . count($sent) . '.' : ''));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->failure($e));
        }
    }

    public function opportunity($id)
    {
        $db = $this->db();
        $doc = $db->get('investment_opportunities/' . $id);
        abort_unless($doc, 404);
        $owner = $db->get('opportunity_owners/' . $id);
        $interests = self::sortBy($db->query('opportunity_interests', [['opportunityId', '==', $id]], [], 2000), 'updatedAt');
        $shares = [];
        foreach ($db->query('opportunity_shares', [['opportunityId', '==', $id]], [], 2000) as $s) {
            $shares[$s['data']['userId'] ?? ''] = $s['data'];
        }
        $ownerId = $owner['data']['ownerId'] ?? '';
        return view('finance.financing.opportunity_show', [
            'id' => $id, 'o' => $doc['data'], 'ownerId' => $ownerId, 'interests' => $interests, 'shares' => $shares,
            'names' => $this->names([$ownerId]), 'canManage' => $this->can('finance.financing.manage'),
        ]);
    }

    public function opportunityAction(Request $request, $id, $action)
    {
        $db = $this->db();
        $fin = new Financing($db);
        try {
            if ($action === 'share') {
                $note = $fin->shareInterest($id, (string)$request->input('userId'), new Notifier($db));
                return $this->done(__('Shared with the project owner.'), [$note]);
            }
            if ($action === 'notify-matches') {
                $sent = $fin->notifyMatches($id, new Notifier($db));
                return $this->done(__('Matching investors notified:') . ' ' . count($sent) . '.');
            }
            abort(404);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->failure($e));
        }
    }

    public function opportunityDocument($id, $index)
    {
        $doc = $this->db()->get('investment_opportunities/' . $id);
        $d = $doc ? (array)($doc['data']['documents'][(int)$index] ?? []) : [];
        abort_unless($d, 404);
        if (!empty($d['storagePath'])) {
            return redirect()->away(Storage::signedUrl($d['storagePath']));
        }
        abort_unless(!empty($d['url']) && preg_match('#^https://#', $d['url']), 404);
        return redirect()->away($d['url']);
    }
}
