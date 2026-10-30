<?php

namespace App\Http\Controllers;

use App\Finance\AuthAccounts;
use App\Finance\Firestore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Removes a Firebase sign-in when the admin deletes a driver, store, owner,
 * provider, worker, employee or fleet driver (client point 84).
 *
 * Those screens called the `deleteUser` Cloud Function from the browser. It
 * answers 403 to every browser call, so the profile was deleted and the
 * sign-in was not, and the email could never sign up again - 35 such
 * sign-ins on the live project on 30 Oct. The screens now post here, in the
 * same shape ({"data": {"uid"}} in, {"result"} or {"error"} out), and the
 * admin's own server deletes it, as it already does for customers.
 *
 * A customer is refused here: customers are soft-deleted on their own screen
 * (users.soft-delete), which keeps their history.
 */
class FirebaseAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function delete(Request $request)
    {
        $uid = (string)($request->input('data.uid') ?? $request->input('uid') ?? '');
        if ($uid === '' || strlen($uid) > 128 || !preg_match('/^[A-Za-z0-9_\-:.]+$/', $uid)) {
            return response()->json(['error' => ['message' => 'A user id is required.']], 422);
        }

        try {
            $db = app(Firestore::class);
            $profile = $db->get('users/' . $uid);
            if ($profile && (($profile['data']['role'] ?? '') === 'customer')) {
                return response()->json(['error' => ['message' => 'Customers are deleted from the Customers screen.']], 422);
            }

            $result = (new AuthAccounts($db))->delete($uid);
            Log::info('Firebase sign-in deleted by the admin', ['uid' => $uid, 'admin' => Auth::id(), 'result' => $result]);

            return response()->json(['result' => ['uid' => $uid, 'deleted' => $result === true ? true : $result]]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => ['message' => 'The sign-in could not be deleted.']], 500);
        }
    }
}
