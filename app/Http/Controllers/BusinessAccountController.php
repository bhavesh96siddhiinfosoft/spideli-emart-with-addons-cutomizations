<?php

namespace App\Http\Controllers;

/**
 * Business account requests - the admin half of the customer app's
 * APP-SPEC-CUSTOMER-APP.md section 6.
 *
 * The app writes `users/{uid}.accountType = "business"` and a
 * `businessProfile` map with status "pending". Nothing could approve one
 * before this screen existed, so every request sat pending for ever.
 *
 * Document 2 calls this KYC Management - application validation, document
 * review, validation history.
 */
class BusinessAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('business_accounts.index');
    }
}
