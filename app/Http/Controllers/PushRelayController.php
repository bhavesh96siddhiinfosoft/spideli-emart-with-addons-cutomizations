<?php

namespace App\Http\Controllers;

use App\Finance\Firestore;
use App\Finance\PushRelay;
use Illuminate\Http\Request;

/**
 * POST /api/push - the push relay of spec 1.6 for the customer apps and
 * website (settings/notification_setting.serverPushUrl). Stateless: no
 * session, no CSRF; the caller is the Firebase ID token in Authorization.
 * Logic in App\Finance\PushRelay.
 */
class PushRelayController extends Controller
{
    public function send(Request $request)
    {
        try {
            [$status, $body, $headers] = (new PushRelay(app(Firestore::class)))
                ->handle($request->header('Authorization'), $request->getContent());
        } catch (\Throwable $e) {
            report($e);
            [$status, $body, $headers] = [500, ['ok' => false, 'error' => 'internal', 'message' => 'The push could not be sent.'], []];
        }
        return response()->json($body, $status, $headers + ['Cache-Control' => 'no-store']);
    }
}
