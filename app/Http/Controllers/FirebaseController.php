<?php

namespace App\Http\Controllers;


class FirebaseController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function config()
    {
        $data = array(
            'apiKey' => base64_encode(env('FIREBASE_APIKEY')),
            'authDomain' => base64_encode(env('FIREBASE_AUTH_DOMAIN')),
            'databaseURL' => base64_encode(env('FIREBASE_DATABASE_URL')),
            'projectId' => base64_encode(env('FIREBASE_PROJECT_ID')),
            'storageBucket' => base64_encode(env('FIREBASE_STORAGE_BUCKET')),
            'messagingSenderId' => base64_encode(env('FIREBASE_MESSAAGING_SENDER_ID')),
            'appId' => base64_encode(env('FIREBASE_APP_ID')),
            'measurementId' => base64_encode(env('FIREBASE_MEASUREMENT_ID')),
        );

        return response()->json($data);
    }

}
