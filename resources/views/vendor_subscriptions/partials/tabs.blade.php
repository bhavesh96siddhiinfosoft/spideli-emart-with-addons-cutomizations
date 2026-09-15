{{--
    The three oversight screens share one nav: plans, subscribers and payments.
    Kept in a partial so a fourth never has to be added in three places.
--}}
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $active === 'plans' ? 'active' : '' }}"
            href="{!! route('vendor-subscriptions.plans') !!}">
            {{ trans('lang.vendor_subscription_plans') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $active === 'subscribers' ? 'active' : '' }}"
            href="{!! route('vendor-subscriptions.subscribers') !!}">
            {{ trans('lang.vendor_subscription_subscribers') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $active === 'payments' ? 'active' : '' }}"
            href="{!! route('vendor-subscriptions.payments') !!}">
            {{ trans('lang.vendor_subscription_payments') }}
        </a>
    </li>
</ul>
