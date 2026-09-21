<?php

namespace App\Http\Controllers;

use App\Helpers\FirestoreHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * OBITSMS, API v2 (https://obitsms.com/api/v2).
 *
 * Two GET endpoints, both authenticated with `key_api` alone:
 *
 *   /bulksms?key_api=&sender=&destination=&message=
 *   /solde?key_api=
 *
 * Both answer with JSON {success, code, message}; /solde adds `solde`.
 * Documented codes: 900 sent, 901 out of credit, 902 bad destination,
 * 903 empty or invalid message.
 *
 * The credentials are read here from Firestore rather than posted by the
 * browser, so the key stays server side even though the settings screen can
 * also see it.
 */
class SmsController extends Controller
{
    private const DEFAULT_BASE_URL = 'https://obitsms.com/api/v2';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'destination' => 'required|string|max:20',
            'message' => 'required|string|max:320',
        ]);

        $settings = FirestoreHelper::getDocument('settings/SMSGateway');

        if (empty($settings) || empty($settings['apiKey'])) {
            return response()->json([
                'success' => false,
                'message' => trans('lang.sms_not_configured'),
            ]);
        }

        $destination = self::normaliseNumber($request->input('destination'));

        if ($destination === '') {
            return response()->json([
                'success' => false,
                'message' => trans('lang.sms_test_number_error'),
            ]);
        }

        $baseUrl = rtrim($settings['apiUrl'] ?? '', '/');

        if ($baseUrl === '') {
            $baseUrl = self::DEFAULT_BASE_URL;
        }

        try {
            $response = Http::timeout(20)->get($baseUrl . '/bulksms', [
                'key_api' => $settings['apiKey'],
                'sender' => $settings['senderId'] ?? '',
                'destination' => $destination,
                'message' => $request->input('message'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => trans('lang.sms_provider_unreachable'),
                'detail' => $e->getMessage(),
            ]);
        }

        $body = $response->json();

        /* A transport failure and a rejected SMS are different things, and the
         * admin needs to be able to tell them apart. */
        if (!is_array($body)) {
            return response()->json([
                'success' => false,
                'message' => trans('lang.sms_provider_unreadable'),
                'detail' => mb_substr((string) $response->body(), 0, 300),
            ]);
        }

        return response()->json([
            'success' => ($body['success'] ?? false) === true,
            'code' => $body['code'] ?? null,
            'message' => $body['message'] ?? '',
            'destination' => $destination,
        ]);
    }

    public function balance()
    {
        $settings = FirestoreHelper::getDocument('settings/SMSGateway');

        if (empty($settings) || empty($settings['apiKey'])) {
            return response()->json(['success' => false]);
        }

        $baseUrl = rtrim($settings['apiUrl'] ?? '', '/');

        if ($baseUrl === '') {
            $baseUrl = self::DEFAULT_BASE_URL;
        }

        try {
            $response = Http::timeout(15)->get($baseUrl . '/solde', [
                'key_api' => $settings['apiKey'],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false]);
        }

        $body = $response->json();

        return response()->json([
            'success' => is_array($body) && ($body['success'] ?? false) === true,
            'solde' => is_array($body) ? ($body['solde'] ?? null) : null,
        ]);
    }

    /**
     * OBITSMS wants the country code with no "+" and no separators:
     * 237674937152, not +237 674 937 152.
     */
    private static function normaliseNumber($number)
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        return $digits === null ? '' : $digits;
    }
}
