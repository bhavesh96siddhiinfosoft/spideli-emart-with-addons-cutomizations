<?php

namespace App\Http\Controllers;

use App\Finance\CustomerAccounts;
use App\Finance\FinanceException;
use App\Finance\Firestore;
use App\Finance\Regions;
use Illuminate\Http\Request;

/**
 * Customers screen: suspend / re-activate and delete, done on the server so
 * Firebase sign-in follows the switch and every change is audited, and a
 * deleted customer is soft-deleted (users/{uid} kept with isDeleted and
 * isDeletedDate, the sign-in account removed) so the history is kept
 * (spec 6.3). Logic in App\Finance\CustomerAccounts. JSON answers for the
 * Customers list and edit form.
 */
class CustomerAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function accounts()
    {
        return new CustomerAccounts(app(Firestore::class));
    }

    /** A region-bound admin may only act on customers of their regions. */
    private function inScope($uid)
    {
        $allowed = Regions::allowedIds();
        if (!$allowed) {
            return true;
        }
        $u = app(Firestore::class)->get('users/' . $uid);
        return $u && array_intersect((array)($u['data']['regionIds'] ?? []), $allowed);
    }

    private function fail(\Throwable $e)
    {
        if (!$e instanceof FinanceException) {
            report($e);
        }
        return response()->json(['ok' => false, 'message' => $e instanceof FinanceException
            ? __($e->getMessage()) : __('Something went wrong. Nothing was changed.')], 422);
    }

    public function setActive(Request $request, $id)
    {
        abort_unless($this->inScope($id), 404);
        try {
            $note = $this->accounts()->setActive($id, $request->boolean('active'), (string)$request->input('reason'));
            return response()->json(['ok' => true, 'message' => __($note)]);
        } catch (\Throwable $e) {
            return $this->fail($e);
        }
    }

    public function deleteCheck($id)
    {
        abort_unless($this->inScope($id), 404);
        try {
            $check = $this->accounts()->deleteCheck($id);
            return response()->json(['ok' => true, 'usesFinance' => $check['usesFinance'],
                'blockers' => array_map(function ($b) { return __($b); }, $check['blockers'])]);
        } catch (\Throwable $e) {
            return $this->fail($e);
        }
    }

    public function softDelete(Request $request, $id)
    {
        abort_unless($this->inScope($id), 404);
        try {
            $note = $this->accounts()->softDelete($id, (string)$request->input('reason'));
            return response()->json(['ok' => true, 'message' => __($note)]);
        } catch (\Throwable $e) {
            return $this->fail($e);
        }
    }
}
