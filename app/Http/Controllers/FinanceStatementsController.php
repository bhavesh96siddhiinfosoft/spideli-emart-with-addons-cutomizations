<?php

namespace App\Http\Controllers;

use App\Finance\Firestore;
use App\Finance\Regions;
use App\Finance\Scope;
use Illuminate\Http\Request;

/**
 * F22: Finance > Statement payments. The older months of history customers paid to unlock: the app writes, in one
 * Firestore transaction, a ledger posting `statement_fee` (customer wallet -> platform_{CUR}) and one
 * statement_unlocks/{uid}_{CUR}_{YYYY-MM} per month unlocked. This page lists them grouped by payment, newest first,
 * filtered to the admin's regions; same permission as the ledger (finance-ledger / finance.ledger).
 */
class FinanceStatementsController extends Controller
{
    const ID = '/^[A-Za-z0-9_-]{1,64}$/';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $db = app(Firestore::class);
        $region = (string)$request->query('region', '');
        $region = preg_match(self::ID, $region) ? $region : '';
        $error = null;
        try {
            $rows = $db->query('statement_unlocks', $region !== '' ? [['regionId', '==', $region]] : [], [], 1000);
            $rows = Scope::filter($db, $rows, 'userId');
        } catch (\Throwable $e) {
            report($e);
            $rows = [];
            $error = __('The statement payments could not be read. Please try again.');
        }

        // one row per payment (one ledger posting may unlock several months)
        $payments = [];
        foreach ($rows as $r) {
            $d = $r['data'];
            $key = (string)($d['ledgerTxId'] ?? '') !== '' ? (string)$d['ledgerTxId'] : 'unlock_' . $r['id'];
            if (!isset($payments[$key])) {
                $payments[$key] = ['ledgerTxId' => (string)($d['ledgerTxId'] ?? ''), 'reference' => (string)($d['reference'] ?? ''),
                    'userId' => (string)($d['userId'] ?? ''), 'regionId' => (string)($d['regionId'] ?? ''),
                    'currency' => (string)($d['currency'] ?? ''), 'months' => [], 'amount' => 0, 'createdAt' => null];
            }
            $p = &$payments[$key];
            $p['months'][] = (string)($d['month'] ?? '');
            $p['amount'] += is_int($d['amount'] ?? null) ? $d['amount'] : 0;
            $at = $d['createdAt'] ?? null;
            if ($at instanceof \DateTimeInterface && (!$p['createdAt'] || $at > $p['createdAt'])) {
                $p['createdAt'] = $at;
            }
            unset($p);
        }
        foreach ($payments as &$p) {
            sort($p['months']);
        }
        unset($p);
        usort($payments, function ($a, $b) {
            return ($b['createdAt'] ? $b['createdAt']->getTimestamp() : 0) <=> ($a['createdAt'] ? $a['createdAt']->getTimestamp() : 0);
        });

        $totals = [];   // per currency (added up here: a filtered sum() needs an index the project does not have)
        foreach ($payments as $p) {
            $totals[$p['currency']] = ($totals[$p['currency']] ?? 0) + $p['amount'];
        }

        $regions = [];
        try {
            $allowed = Regions::allowedIds();
            foreach ((new Regions($db))->all() as $id => $r) {
                if (!empty($r['financeEnabled']) && (!$allowed || in_array($id, $allowed, true))) {
                    $regions[$id] = (string)($r['name'] ?? $id);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return view('finance.statements.index', [
            'payments' => $payments,
            'totals' => $totals,
            'names' => $this->names($db, array_column($payments, 'userId')),
            'regions' => $regions,
            'region' => $region,
            'error' => $error,
        ]);
    }

    /** [uid => "Name (email)"]. */
    private function names(Firestore $db, array $uids)
    {
        $uids = array_values(array_unique(array_filter($uids, function ($u) { return is_string($u) && preg_match(self::ID, $u); })));
        $out = [];
        if (!$uids) {
            return $out;
        }
        try {
            foreach ($db->getMany(array_map(function ($u) { return 'users/' . $u; }, array_slice($uids, 0, 300))) as $doc) {
                if ($doc) {
                    $d = $doc['data'];
                    $name = trim(($d['firstName'] ?? '') . ' ' . ($d['lastName'] ?? ''));
                    $out[$doc['id']] = trim($name . (!empty($d['email']) ? ' (' . $d['email'] . ')' : ''));
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
        return $out;
    }
}
