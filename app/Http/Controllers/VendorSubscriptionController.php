<?php

namespace App\Http\Controllers;

/**
 * Oversight of the subscriptions a store sells to its own customers.
 *
 * Not to be confused with SubscriptionPlanController, which covers the plans
 * the platform sells to stores and to customers. Those live in
 * `subscription_plans`; these live in their own collections and must never be
 * mixed - see docs/ADMIN-VENDOR-SUBSCRIPTION.md.
 *
 * Read-only by design. Plans are authored by the store, and subscriptions and
 * payments are written by the app when a customer pays; an admin editing what a
 * store earned would corrupt the commission record.
 */
class VendorSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function plans()
    {
        return view('vendor_subscriptions.plans');
    }

    public function subscribers()
    {
        return view('vendor_subscriptions.subscribers');
    }

    public function payments()
    {
        return view('vendor_subscriptions.payments');
    }
}
