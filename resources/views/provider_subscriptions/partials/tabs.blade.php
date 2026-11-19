{{-- #114: the three provider subscription screens share one nav: plans, subscribers and payments. --}}
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $active === 'plans' ? 'active' : '' }}"
            href="{!! route('provider-subscriptions.plans') !!}">
            {{ trans('lang.provider_subscription_plans') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $active === 'subscribers' ? 'active' : '' }}"
            href="{!! route('provider-subscriptions.subscribers') !!}">
            {{ trans('lang.provider_subscription_subscribers') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $active === 'payments' ? 'active' : '' }}"
            href="{!! route('provider-subscriptions.payments') !!}">
            {{ trans('lang.provider_subscription_payments') }}
        </a>
    </li>
</ul>
