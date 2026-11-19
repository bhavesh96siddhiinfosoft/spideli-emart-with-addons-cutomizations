<?php

namespace App\Http\Controllers;

/**
 * #114: oversight of the subscriptions a provider sells to its own clients (for example a monthly home cleaning).
 *
 * The provider version of VendorSubscriptionController, in its own collections (the user's choice, 19 Nov):
 * provider_subscription_plans, provider_subscriptions, provider_subscription_payments. Never mixed with the store's
 * vendor_subscription_* collections or the platform's subscription_plans.
 *
 * Read-only by design: plans are written by the provider, and subscriptions and payments by the website / app when a
 * client pays. An admin editing what a provider earned would corrupt the commission record.
 */
class ProviderSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function plans()
    {
        return view('provider_subscriptions.plans');
    }

    public function subscribers()
    {
        return view('provider_subscriptions.subscribers');
    }

    public function payments()
    {
        return view('provider_subscriptions.payments');
    }
}
