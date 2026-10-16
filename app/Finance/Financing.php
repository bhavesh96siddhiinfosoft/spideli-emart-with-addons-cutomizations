<?php

namespace App\Finance;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Phase D3a (spec 6.12, 6.13, 3.11, 3.12, R.12): project financing review,
 * financial resources, and investment opportunities with their interests.
 *
 * Every status change of an application writes a financing_reviews entry
 * (the owner reads them); internal notes go to the staff-only
 * financing_internal_notes. Funding is recorded only: the institution pays
 * the owner outside Spideli (chosen 16 Oct 2026).
 *
 * An opportunity's owner uid is kept in the staff-only
 * opportunity_owners/{id}, never in the published document. Interests are
 * the customers' own documents (their rules refuse extra keys), so a share
 * with the owner is recorded in the staff-only opportunity_shares.
 */
class Financing
{
    public const RESOURCE_TYPES = ['template', 'guide', 'faq', 'regulation'];
    public const OPPORTUNITY_TYPES = ['equity', 'loan', 'revenue_share', 'fund'];

    private $db;

    public function __construct(Firestore $db = null)
    {
        $this->db = $db ?: app(Firestore::class);
    }

    private function admin()
    {
        return 'admin:' . Auth::id();
    }

    private function get(Transaction $t, $path, $what)
    {
        $snap = $t->get($path);
        if (!$snap) {
            throw new FinanceException('missing', "This $what no longer exists.");
        }
        return $snap['data'] + ['id' => $snap['id']];
    }

    private static function needText($text, $what = 'A reason is required.')
    {
        if (trim((string)$text) === '') {
            throw new FinanceException('invalid', $what);
        }
    }

    /* ======================================================= applications */

    /**
     * The documents an application needs: the region's requiredDocuments
     * (a type, or {type, financingTypes} for some types only) for its
     * financing type, with each one's best status among the uploaded ones.
     * [type => 'accepted' | 'received' | 'rejected' | 'missing']
     */
    public static function requiredStatus(array $settings, array $application, array $documents)
    {
        $out = [];
        foreach ((array)($settings['requiredDocuments'] ?? []) as $r) {
            $type = is_array($r) ? ($r['type'] ?? '') : (string)$r;
            $only = is_array($r) ? (array)($r['financingTypes'] ?? []) : [];
            if ($type === '' || ($only && !in_array($application['financingType'] ?? '', $only, true))) {
                continue;
            }
            $best = 'missing';
            foreach ($documents as $d) {
                $d = $d['data'] ?? $d;
                if (($d['type'] ?? '') !== $type) {
                    continue;
                }
                $s = $d['status'] ?? 'received';
                $rank = ['missing' => 0, 'rejected' => 1, 'received' => 2, 'accepted' => 3];
                if (($rank[$s] ?? 0) > $rank[$best]) {
                    $best = $s;
                }
            }
            $out[$type] = $best;
        }
        return $out;
    }

    /**
     * One status change of an application with its public review entry.
     * $from: allowed statuses; $changes: extra fields. Returns the
     * application as it was, merged with the changes.
     */
    private function move($appId, array $from, $to, array $changes, $publicNote, callable $check = null)
    {
        return $this->db->transaction(function (Transaction $t) use ($appId, $from, $to, $changes, $publicNote, $check) {
            $a = $this->get($t, 'financing_applications/' . $appId, 'application');
            if (!in_array($a['status'] ?? '', $from, true)) {
                throw new FinanceException('state_changed', 'This application is now "' . ($a['status'] ?? '?') . '".');
            }
            if (!empty($a['demo'])) {
                throw new FinanceException('demo', 'Demo records are never changed.');
            }
            if ($check) {
                $check($t, $a);
            }
            $t->update('financing_applications/' . $appId, ['status' => $to, 'updatedAt' => Value::serverTimestamp()] + $changes);
            $t->create('financing_reviews/' . Money::random(20), ['ownerId' => $a['ownerId'] ?? '', 'applicationId' => $appId, 'fromStatus' => $a['status'],
                'toStatus' => $to, 'publicNote' => trim((string)$publicNote), 'at' => Value::serverTimestamp()]);
            Audit::add($t, 'financing.' . $to, 'financing_applications', $appId, ['status' => $a['status']], ['status' => $to] + array_diff_key($changes, ['updatedAt' => 1]), $publicNote);
            return array_merge($a, $changes, ['status' => $to]);
        });
    }

    public function startReview($appId, $note = '')
    {
        return $this->move($appId, ['submitted'], 'under_review', ['reviewStartedBy' => $this->admin()], $note ?: __('Your application is being reviewed.'));
    }

    public function requestInfo($appId, $reason)
    {
        self::needText($reason);
        return $this->move($appId, ['submitted', 'under_review'], 'additional_info_required', ['reason' => trim($reason)], $reason);
    }

    /** Approves: every required document must be accepted. */
    public function approve($appId, $amount, $institution, $note, array $settings)
    {
        $amount = (int)$amount;
        if ($amount <= 0) {
            throw new FinanceException('invalid', 'Enter the approved amount.');
        }
        self::needText($institution, 'Enter the financing institution.');
        return $this->move($appId, ['submitted', 'under_review'], 'approved',
            ['approvedAmount' => $amount, 'institutionName' => trim($institution), 'reason' => Value::delete()], $note,
            function (Transaction $t, array $a) use ($settings, $amount) {
                if ($amount > (int)($a['amount'] ?? 0)) {
                    throw new FinanceException('invalid', 'The approved amount cannot exceed the amount asked.');
                }
                $docs = $t->query('financing_documents', [['applicationId', '==', $a['id']]]);
                $missing = array_keys(array_filter(self::requiredStatus($settings, $a, $docs), function ($s) { return $s !== 'accepted'; }));
                if ($missing) {
                    throw new FinanceException('documents_missing', 'Accept every required document first: ' . implode(', ', $missing) . '.');
                }
            });
    }

    public function reject($appId, $reason)
    {
        self::needText($reason);
        return $this->move($appId, ['submitted', 'under_review', 'additional_info_required'], 'rejected', ['reason' => trim($reason)], $reason);
    }

    public function fund($appId, $amount, \DateTimeInterface $at, $note = '')
    {
        $amount = (int)$amount;
        if ($amount <= 0) {
            throw new FinanceException('invalid', 'Enter the amount the institution paid.');
        }
        if ($at > new \DateTimeImmutable('+1 day')) {
            throw new FinanceException('invalid', 'The funding date cannot be in the future.');
        }
        return $this->move($appId, ['approved'], 'funded', ['fundedAmount' => $amount, 'fundedAt' => \DateTimeImmutable::createFromInterface($at)], $note);
    }

    public function close($appId, $note = '')
    {
        return $this->move($appId, ['approved', 'funded'], 'closed', ['closedAt' => Value::serverTimestamp()], $note);
    }

    /** Staff-only note; never shown to the owner. */
    public function internalNote($appId, $text)
    {
        self::needText($text, 'Write the note.');
        $this->db->batch([
            $this->db->write('create', 'financing_internal_notes/' . Money::random(20), ['applicationId' => $appId, 'authorId' => $this->admin(),
                'authorEmail' => (string)(Auth::user()->email ?? ''), 'text' => trim($text), 'at' => Value::serverTimestamp()]),
        ]);
    }

    /** Accepts or rejects one uploaded document (a rejection needs a note for the owner). */
    public function document($docId, $accept, $note = '')
    {
        if (!$accept) {
            self::needText($note, 'Tell the owner what to fix.');
        }
        return $this->db->transaction(function (Transaction $t) use ($docId, $accept, $note) {
            $d = $this->get($t, 'financing_documents/' . $docId, 'document');
            $a = $this->get($t, 'financing_applications/' . ($d['applicationId'] ?? ''), 'application');
            if (!in_array($a['status'] ?? '', ['submitted', 'under_review', 'additional_info_required'], true)) {
                throw new FinanceException('state_changed', 'The application is now "' . ($a['status'] ?? '?') . '".');
            }
            $to = $accept ? 'accepted' : 'rejected';
            $t->update('financing_documents/' . $docId, ['status' => $to, 'note' => trim((string)$note), 'reviewedBy' => $this->admin(), 'reviewedAt' => Value::serverTimestamp()]);
            Audit::add($t, 'financing.document_' . $to, 'financing_documents', $docId, ['status' => $d['status'] ?? 'received'], ['status' => $to], $note);
            return $d + ['projectName' => $a['projectName'] ?? ''];
        });
    }

    /* ========================================================== resources */

    /**
     * Creates or edits a financial resource (template, guide, FAQ,
     * regulation): a text, and a file uploaded to financial_resources/ or an
     * https link.
     */
    public function saveResource($id, array $in, $file = null)
    {
        $type = (string)($in['type'] ?? '');
        if (!in_array($type, self::RESOURCE_TYPES, true)) {
            throw new FinanceException('invalid', 'Choose the type.');
        }
        $r = [
            'type' => $type,
            'title' => trim((string)($in['title'] ?? '')),
            'summary' => trim((string)($in['summary'] ?? '')),
            'body' => trim(str_replace("\r\n", "\n", (string)($in['body'] ?? ''))),
            'country' => trim((string)($in['country'] ?? '')),
            'language' => trim((string)($in['language'] ?? '')),
            'order' => (int)($in['order'] ?? 0),
            'published' => !empty($in['published']),
        ];
        if ($r['title'] === '' || mb_strlen($r['title']) > 200) {
            throw new FinanceException('invalid', 'The resource needs a title (at most 200 characters).');
        }
        $url = trim((string)($in['url'] ?? ''));
        if ($url !== '' && !preg_match('#^https://[^\s/]+#', $url)) {
            throw new FinanceException('invalid', 'A link must start with https://');
        }
        $isNew = !$id;
        $id = $id ?: Money::random(20);
        if ($file) {
            $r['storagePath'] = Storage::upload($file['file'], 'financial_resources/' . $id . '/' . (int)floor(microtime(true) * 1000) . '-' . Storage::safeName($file['name']), $file['contentType'] ?? 'application/pdf');
            $r['url'] = Value::delete();
        } elseif ($url !== '') {
            $r['url'] = $url;
            $r['storagePath'] = Value::delete();
        }
        return $this->db->transaction(function (Transaction $t) use ($id, $isNew, $r) {
            $before = $isNew ? null : $this->get($t, 'financial_resources/' . $id, 'resource');
            $r['updatedAt'] = Value::serverTimestamp();
            if ($isNew) {
                $t->create('financial_resources/' . $id, array_filter($r, function ($v) { return !($v instanceof Value && $v->isDelete()); }) + ['createdAt' => Value::serverTimestamp()]);
            } else {
                $t->update('financial_resources/' . $id, $r);
            }
            Audit::add($t, $isNew ? 'resource.create' : 'resource.update', 'financial_resources', $id, $before ? ['title' => $before['title'] ?? '', 'published' => $before['published'] ?? false] : null,
                ['title' => $r['title'], 'published' => $r['published'], 'type' => $r['type']]);
            return $id;
        });
    }

    /* ====================================================== opportunities */

    /**
     * The opportunity fields of spec 3.12 from the form. $project: the
     * financing project it is for (its owner is kept staff-only).
     */
    public static function opportunityFromInput(array $in, array $project, array $existing = null)
    {
        $title = trim((string)($in['title'] ?? ''));
        if ($title === '' || mb_strlen($title) > 200) {
            throw new FinanceException('invalid', 'The opportunity needs a title (at most 200 characters).');
        }
        $cur = $existing['currency'] ?? (string)($in['currency'] ?? '');
        if (!Money::isCurrency($cur)) {
            throw new FinanceException('invalid', 'Choose the currency.');
        }
        $type = (string)($in['type'] ?? '');
        if (!in_array($type, self::OPPORTUNITY_TYPES, true)) {
            throw new FinanceException('invalid', 'Choose the type.');
        }
        $risk = (int)($in['riskLevel'] ?? 0);
        if ($risk < 1 || $risk > 7) {
            throw new FinanceException('invalid', 'The risk level is 1 to 7.');
        }
        $min = Money::toMinor($in['minTicket'] ?? '', $cur) ?? 0;
        $target = Money::toMinor($in['targetAmount'] ?? '', $cur) ?? 0;
        $raised = Money::toMinor($in['raisedAmount'] ?? '', $cur) ?? 0;
        if ($min <= 0 || $target <= 0 || $min > $target) {
            throw new FinanceException('invalid', 'The minimum ticket and the target must be above 0, the ticket at most the target.');
        }
        $closing = null;
        if (trim((string)($in['closingDate'] ?? '')) !== '') {
            $closing = \DateTimeImmutable::createFromFormat('Y-m-d H:i', trim($in['closingDate']) . ' 23:59', new \DateTimeZone(config('app.timezone')));
            if (!$closing) {
                throw new FinanceException('invalid', 'The closing date is not valid.');
            }
        }
        $return = trim((string)($in['targetReturnPercent'] ?? ''));
        if ($return !== '' && (!is_numeric($return) || (float)$return < 0 || (float)$return > 1000)) {
            throw new FinanceException('invalid', 'The target return is a percentage.');
        }
        $o = [
            'title' => $title,
            'summary' => trim((string)($in['summary'] ?? '')),
            'sector' => (string)($in['sector'] ?? ($project['sector'] ?? '')),
            'country' => trim((string)($in['country'] ?? ($project['country'] ?? ''))),
            'type' => $type,
            'currency' => $cur,
            'minTicket' => $min,
            'targetAmount' => $target,
            'raisedAmount' => $raised,
            'horizonMonths' => max(0, (int)($in['horizonMonths'] ?? 0)),
            'riskLevel' => $risk,
            'targetReturnPercent' => $return === '' ? null : round((float)$return, 2),
            'ownerName' => trim((string)($in['ownerName'] ?? '')) ?: ($project['name'] ?? ''),
            'projectId' => $project['id'] ?? '',
            'closingDate' => $closing,
            'status' => ($in['status'] ?? 'open') === 'closed' ? 'closed' : 'open',
            'published' => !empty($in['published']),
        ];
        if ($o['sector'] === '') {
            throw new FinanceException('invalid', 'Choose the sector.');
        }
        return $o;
    }

    /**
     * Creates or edits an opportunity; the owner's uid goes to the
     * staff-only opportunity_owners/{id}. Documents: financial_resources/
     * opportunities/{id}/ (signed-in customers read them).
     * Returns ['id', 'nowOpen' => published and open after this save].
     */
    public function saveOpportunity($id, array $o, $ownerId, array $newDocuments = [], array $removeDocuments = [])
    {
        $isNew = !$id;
        $id = $id ?: Money::random(20);
        foreach ($newDocuments as $i => $d) {
            $newDocuments[$i] = ['name' => $d['name'], 'storagePath' => Storage::upload($d['file'],
                'financial_resources/opportunities/' . $id . '/' . (int)floor(microtime(true) * 1000) . '-' . $i . '-' . Storage::safeName($d['fileName'] ?? $d['name']))];
        }
        return $this->db->transaction(function (Transaction $t) use ($id, $isNew, $o, $ownerId, $newDocuments, $removeDocuments) {
            $before = $isNew ? null : $this->get($t, 'investment_opportunities/' . $id, 'opportunity');
            $docs = [];
            foreach ((array)($before['documents'] ?? []) as $i => $d) {
                if (!in_array((string)$i, array_map('strval', $removeDocuments), true)) {
                    $docs[] = $d;
                }
            }
            $o['documents'] = array_merge($docs, array_values($newDocuments));
            $o['updatedAt'] = Value::serverTimestamp();
            if ($isNew) {
                $t->create('investment_opportunities/' . $id, $o + ['createdAt' => Value::serverTimestamp()]);
            } else {
                $t->update('investment_opportunities/' . $id, $o);
            }
            $t->set('opportunity_owners/' . $id, ['ownerId' => $ownerId, 'projectId' => $o['projectId'], 'updatedAt' => Value::serverTimestamp()]);
            $diff = $before ? Audit::diff(array_diff_key($before, ['id' => 1]), $o) : [null, $o];
            Audit::add($t, $isNew ? 'opportunity.create' : 'opportunity.update', 'investment_opportunities', $id, $diff[0], $diff[1]);
            return ['id' => $id, 'nowOpen' => $o['published'] && $o['status'] === 'open'];
        });
    }

    /**
     * The match score of the customer spec 6.13: eligible = open, before its
     * closing date, same currency, minTicket ≤ the investor's maxTicket,
     * risk ≤ maxRisk; score = the weighted share of the met preferences
     * (sector, country, type: empty = met; ticket in range; horizon, 0 = no
     * limit). Null when not eligible.
     */
    public static function score(array $opp, array $profile, array $weights, \DateTimeInterface $now)
    {
        $closing = $opp['closingDate'] ?? null;
        if (($opp['status'] ?? '') !== 'open' || ($closing instanceof \DateTimeInterface && $closing <= $now)
            || ($profile['currency'] ?? '') !== ($opp['currency'] ?? '')
            || (int)($opp['minTicket'] ?? 0) > (int)($profile['maxTicket'] ?? 0)
            || (int)($opp['riskLevel'] ?? 7) > (int)($profile['maxRisk'] ?? 4)) {
            return null;
        }
        $in = function ($list, $value) { $list = (array)$list; return !$list || in_array($value, $list, true); };
        $met = [
            'sector' => $in($profile['sectors'] ?? [], $opp['sector'] ?? ''),
            'country' => $in($profile['countries'] ?? [], $opp['country'] ?? ''),
            'type' => $in($profile['types'] ?? [], $opp['type'] ?? ''),
            'ticket' => (int)($opp['minTicket'] ?? 0) >= (int)($profile['minTicket'] ?? 0) && (int)($opp['minTicket'] ?? 0) <= (int)($profile['maxTicket'] ?? 0),
            'horizon' => (int)($profile['maxHorizonMonths'] ?? 0) === 0 || (int)($opp['horizonMonths'] ?? 0) <= (int)$profile['maxHorizonMonths'],
        ];
        $total = 0;
        $got = 0;
        foreach ($met as $k => $ok) {
            $w = (float)($weights[$k] ?? 1);
            $total += $w;
            $got += $ok ? $w : 0;
        }
        return $total > 0 ? (int)round(100 * $got / $total) : 0;
    }

    /**
     * Tells every investor whose preferences match an open, published
     * opportunity (score ≥ their region's minScore, matching enabled), once
     * per investor and opportunity. The owner is never told about their own.
     * Returns [uid => score] of those notified now.
     */
    public function notifyMatches($oppId, Notifier $notifier)
    {
        $opp = $this->db->get('investment_opportunities/' . $oppId);
        if (!$opp || empty($opp['data']['published']) || ($opp['data']['status'] ?? '') !== 'open') {
            return [];
        }
        $o = $opp['data'];
        $owner = $this->db->get('opportunity_owners/' . $oppId);
        $ownerId = $owner['data']['ownerId'] ?? '';
        $regions = new Regions($this->db);
        $now = new \DateTimeImmutable();
        $sent = [];
        foreach ($this->db->query('investor_profiles', [['currency', '==', $o['currency'] ?? '']], [], 5000) as $p) {
            $uid = $p['id'];
            if ($uid === $ownerId) {
                continue;
            }
            $user = $this->db->get('users/' . $uid);
            [$intel] = $regions->moduleSettings('intelligence', (array)($user['data']['regionIds'] ?? []));
            $m = (array)($intel['matching'] ?? []);
            if (empty($m['enabled'])) {
                continue;
            }
            $score = self::score($o, $p['data'], (array)($m['weights'] ?? []), $now);
            if ($score === null || $score < (int)($m['minScore'] ?? 0)) {
                continue;
            }
            if (DB::table('finance_job_marks')->insertOrIgnore(['mark' => substr("opp_match_{$oppId}_$uid", 0, 191), 'created_at' => now()]) !== 1) {
                continue;
            }
            $notifier->send($uid, 'intelligence_new_match', ['title' => $o['title'] ?? '', 'score' => $score], ['opportunityId' => $oppId]);
            $sent[$uid] = $score;
        }
        if ($sent) {
            $this->db->batch([Audit::write($this->db, 'opportunity.match_notify', 'investment_opportunities', $oppId, null, ['notified' => count($sent)])]);
        }
        return $sent;
    }

    /**
     * Shares one interested investor with the owner: recorded in the
     * staff-only opportunity_shares; the owner is notified with the
     * investor's name (and e-mail and message for a template that uses them).
     */
    public function shareInterest($oppId, $uid, Notifier $notifier)
    {
        $interest = $this->db->get('opportunity_interests/' . $oppId . '_' . $uid);
        if (!$interest || empty($interest['data']['interested'])) {
            throw new FinanceException('not_interested', 'This investor is no longer interested.');
        }
        $owner = $this->db->get('opportunity_owners/' . $oppId);
        $ownerId = $owner['data']['ownerId'] ?? '';
        if ($ownerId === '') {
            throw new FinanceException('missing', 'The opportunity has no owner on record.');
        }
        if ($this->db->get('opportunity_shares/' . $oppId . '_' . $uid)) {
            throw new FinanceException('already_shared', 'Already shared with the owner.');
        }
        $opp = $this->db->get('investment_opportunities/' . $oppId);
        $i = $interest['data'];
        $this->db->batch([
            $this->db->write('create', 'opportunity_shares/' . $oppId . '_' . $uid, ['opportunityId' => $oppId, 'userId' => $uid, 'ownerId' => $ownerId,
                'sharedBy' => $this->admin(), 'sharedAt' => Value::serverTimestamp()]),
            Audit::write($this->db, 'opportunity.interest_share', 'opportunity_interests', $oppId . '_' . $uid, null, ['ownerId' => $ownerId]),
        ]);
        return $notifier->send($ownerId, 'intelligence_interest_received', ['investorName' => $i['name'] ?? '', 'title' => $opp['data']['title'] ?? '',
            'investorEmail' => $i['email'] ?? '', 'message' => $i['message'] ?? ''],
            ['opportunityId' => $oppId, 'investorName' => $i['name'] ?? '', 'investorEmail' => $i['email'] ?? '']);
    }
}
