{{-- Spideli Finance: page title, section tabs and messages. Needs $pageTitle and $tab. --}}
@php
    $financePerms = json_decode(session('user_permissions') ?? '[]', true) ?: [];
    $financePending = \App\Finance\FourEyes::pendingCount();
@endphp
<div class="row page-titles">
    <div class="col-md-5 align-self-center">
        <h3 class="text-themecolor">{{ $pageTitle }}</h3>
    </div>
    <div class="col-md-7 align-self-center">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
            <li class="breadcrumb-item">{{ __('Spideli Finance') }}</li>
            <li class="breadcrumb-item active">{{ $pageTitle }}</li>
        </ol>
    </div>
</div>
<div class="container-fluid">
    <div class="payment-top-tab mb-3">
    <ul class="nav nav-tabs card-header-tabs align-items-end finance-tabs">
        @if (in_array('finance.settings', $financePerms))
            <li class="nav-item"><a class="nav-link {{ $tab == 'settings' ? 'active' : '' }}" href="{{ route('finance.settings') }}">{{ __('Settings') }}</a></li>
        @endif
        @if (in_array('finance.customers', $financePerms))
            <li class="nav-item"><a class="nav-link {{ $tab == 'customers' ? 'active' : '' }}" href="{{ route('finance.customers') }}">{{ __('Customers') }}</a></li>
        @endif
        @if (in_array('finance.kyc', $financePerms))
            <li class="nav-item"><a class="nav-link {{ $tab == 'kyc' ? 'active' : '' }}" href="{{ route('finance.kyc') }}">{{ __('KYC') }}</a></li>
        @endif
        @if (in_array('finance.payments', $financePerms))
            <li class="nav-item"><a class="nav-link {{ $tab == 'payments' ? 'active' : '' }}" href="{{ route('finance.payments') }}">{{ __('Payouts') }}</a></li>
        @endif
        @if (in_array('finance.groups', $financePerms))
            <li class="nav-item"><a class="nav-link {{ $tab == 'savings' ? 'active' : '' }}" href="{{ route('finance.savings') }}">{{ __('Savings') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ $tab == 'tontines' ? 'active' : '' }}" href="{{ route('finance.tontines') }}">{{ __('Tontines') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ $tab == 'loans' ? 'active' : '' }}" href="{{ route('finance.loans') }}">{{ __('Loans') }}</a></li>
        @endif
        @if (in_array('finance.ledger', $financePerms))
            <li class="nav-item"><a class="nav-link {{ $tab == 'ledger' ? 'active' : '' }}" href="{{ route('finance.ledger') }}">{{ __('Ledger') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ $tab == 'approvals' ? 'active' : '' }}" href="{{ route('finance.approvals') }}">{{ __('Approvals') }}
                @if ($financePending)<span class="badge badge-warning ml-1">{{ $financePending }}</span>@endif</a></li>
        @endif
        @if (in_array('finance.audit', $financePerms))
            <li class="nav-item"><a class="nav-link {{ $tab == 'audit' ? 'active' : '' }}" href="{{ route('finance.audit') }}">{{ __('Audit log') }}</a></li>
        @endif
    </ul>
    </div>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
</div>
<style>
    .finance-amount { direction: ltr; unicode-bidi: embed; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .finance-mono { direction: ltr; unicode-bidi: embed; font-family: SFMono-Regular, Consolas, monospace; font-size: 12px; }
    .finance-field-error { color: #dc3545; font-size: 13px; margin-top: 4px; }
    .finance-help { color: #6c757d; font-size: 12px; margin-top: 4px; }
    .finance-section { font-weight: 700; margin: 24px 0 8px; padding-bottom: 6px; border-bottom: 1px solid #e9ecef; }
    .finance-option { display: inline-block; margin: 0 20px 6px 0; }
    .finance-option label { font-weight: normal; }
    .finance-money td { padding: 4px 8px 4px 0; border: 0; }
    .finance-money input { max-width: 180px; }
    .finance-badge-ok { background: #e6f4ea; color: #1e7e34; padding: 2px 8px; border-radius: 10px; font-size: 12px; }
    /* style.css pins every .select2 container to its parent's corner (written for
       the phone country picker); undo it inside Finance pages. */
    .finance-page .select2.select2-container { position: relative !important; left: 0 !important; top: 0 !important; bottom: auto !important; width: 100% !important; }
    .finance-page .select2-container--default .select2-selection--multiple,
    .finance-page .select2-container--default .select2-selection--single { min-height: 38px; border: 1px solid #b1b8bb; border-radius: 4px; }
    .finance-page .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px; max-width: none !important; }
    .finance-page .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    /* An amount box with its currency symbol inside it on the left - the
       panel's own pattern (control-inner + currentCurrency). The input keeps
       the theme's full width, so nothing sticks out of the column. */
    .finance-page .finance-amount-box { position: relative; margin-bottom: 8px; }
    .finance-page .finance-amount-box:last-child { margin-bottom: 0; }
    .finance-page .finance-amount-box .finance-symbol { position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
        color: #67757c; font-weight: 600; font-size: 14px; pointer-events: none; direction: ltr; }
    .finance-page .finance-check-help { padding-left: 15px; padding-right: 15px; }
    /* Repeatable fields (KYC document types, levels, banks, required documents). */
    .finance-page .finance-rows > table { table-layout: fixed; width: 100%; }
    .finance-page .finance-rows > table td { vertical-align: middle; }
    .finance-page .finance-rows > table th { font-weight: 500; color: #000; }
    .finance-page .finance-rows .form-check { margin: 0; }
    .finance-page .finance-group .btn-link { font-size: 18px; line-height: 1; }
    /* KYC level requirements: "Requirement 1", "AND", "Requirement 2"... */
    .finance-page .finance-groups { counter-reset: finance-req; }
    .finance-page .finance-group { counter-increment: finance-req; }
    .finance-page .finance-req-label { font-weight: 600; color: #000; font-size: 13px; }
    .finance-page .finance-req-label::after { content: counter(finance-req); }
    .finance-page .finance-and { font-size: 11px; font-weight: 700; color: #6c757d; letter-spacing: .05em; margin: 4px 0 6px; }
    .finance-page .finance-group:first-child .finance-and { display: none; }
    .finance-matrix td, .finance-matrix th { vertical-align: top; min-width: 130px; }
    .finance-cell-link { text-decoration: none; }
    .finance-badge-no { background: #fdecea; color: #b02a37; padding: 2px 8px; border-radius: 10px; font-size: 12px; }
</style>
