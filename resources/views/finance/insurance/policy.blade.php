@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    use App\Finance\Insurance;
    $tz = new DateTimeZone(config('app.timezone'));
    $day = function ($v) use ($tz) { $d = Insurance::date($v); return $d ? $d->setTimezone($tz)->format('Y-m-d') : ''; };
    $cur = $p['currency'] ?? '';
    $status = $p['status'] ?? '';
    $paidRenewal = !empty($p['renewalRequestedAt']) && !empty($p['renewalTxId']);
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Policy') . ': ' . ($p['productName'] ?? ''), 'tab' => 'insurance'])
    <div class="container-fluid">
        @if ($lockedBy)<div class="alert alert-warning">{{ __('Another admin is working on this policy:') }} {{ $lockedBy }}</div>@endif
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
            <fieldset>
                <legend><i class="mr-3 mdi mdi-shield-account"></i>{{ __('Policy') }}</legend>
                @foreach ([
                    __('Customer') => $names[$p['userId'] ?? ''] ?? ($p['userId'] ?? ''),
                    __('Product') => ($p['productName'] ?? '') . (!empty($p['demo']) ? ' (demo)' : '') . ' · ' . ($p['partnerName'] ?? ''),
                    __('Status') => $status . (!empty($p['reason']) ? ' - ' . $p['reason'] : ''),
                    __('Policy number') => $p['policyNumber'] ?? '',
                    __('Cover') => $day($p['coverStart'] ?? null) . ' → ' . $day($p['coverEnd'] ?? null),
                    __('Premium (a year)') => Money::format((int)($p['premium'] ?? 0), $cur),
                    __('Each payment') => Money::format((int)($p['installmentAmount'] ?? 0), $cur) . ' · ' . ($p['frequency'] ?? ''),
                    __('Deductible') => Money::format((int)($p['deductible'] ?? 0), $cur),
                    __('Next premium due') => $day($p['nextPremiumDue'] ?? null),
                    __('Automatic renewal') => !empty($p['autoRenew']) ? __('Yes') : __('No'),
                    __('Signed by') => ($p['signatureName'] ?? '') . ' · ' . __('terms v') . ($p['termsVersion'] ?? '?'),
                    __('Quote') => $p['quoteId'] ?? '',
                ] as $label => $value)
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ $label }}</label><div class="col-7"><span class="form-control-plaintext">{{ trim($value, ' ·→') !== '' ? $value : '—' }}</span></div></div>
                @endforeach
                <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Coverages') }}</label><div class="col-7"><span class="form-control-plaintext">
                    @forelse ((array)($p['coverages'] ?? []) as $c)<span class="mr-3">{{ $c['name'] ?? '' }}: {{ Money::format((int)($c['limit'] ?? 0), $cur) }}</span>@empty — @endforelse
                </span></div></div>
                <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Documents') }}</label><div class="col-7"><span class="form-control-plaintext">
                    @forelse ((array)($p['documents'] ?? []) as $i => $d)<a class="mr-3" href="{{ route('finance.insurance.document', ['policies', $id, $i]) }}" target="_blank" rel="noopener"><i class="mdi mdi-file-pdf-box"></i> {{ $d['name'] ?? __('Document') }}</a>@empty — @endforelse
                </span></div></div>
            </fieldset>

            @if ($canManage && $status === 'pending_activation')
            <fieldset>
                <legend><i class="mr-3 mdi mdi-check-decagram"></i>{{ __('Activate') }}</legend>
                <form method="POST" enctype="multipart/form-data" action="{{ route('finance.insurance.policies.action', [$id, 'activate']) }}" onsubmit="return confirm('{{ __('Activate this policy?') }}')">@csrf
                    <div class="form-group row width-50"><label class="col-3 control-label" for="po-num">{{ __('Policy number') }}<span class="required-field"></span></label>
                        <div class="col-7"><input type="text" id="po-num" name="policyNumber" class="form-control" maxlength="100" value="{{ old('policyNumber') }}" required>
                            <div class="form-text text-muted">{{ __('The number the insurer gave.') }}</div></div></div>
                    <div class="form-group row width-100"><div class="col-7">
                        <table class="table table-bordered table-sm mb-2" id="po-docs"><thead><tr><th>{{ __('Title') }}</th><th>{{ __('PDF file') }}</th></tr></thead>
                            <tbody><tr><td><input type="text" name="documentTitles[]" class="form-control" placeholder="{{ __('e.g. Policy certificate') }}"></td><td><input type="file" name="documents[]" accept="application/pdf,.pdf" class="form-control"></td></tr></tbody></table>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="po-add-doc"><i class="mdi mdi-plus"></i> {{ __('Another document') }}</button>
                        <div class="form-text text-muted">{{ __('Optional. Only this customer can open them in the app.') }}</div>
                        <p class="finance-help mt-2">{{ __('Activating creates the later premiums of the cover (the first one is paid) and tells the customer.') }}</p>
                        <button class="btn btn-success">{{ __('Activate the policy') }}</button>
                    </div></div>
                </form>
                <form method="POST" action="{{ route('finance.insurance.policies.action', [$id, 'refuse']) }}" class="form-group row width-100" onsubmit="return confirm('{{ __('Refuse and refund the first premium?') }}')">@csrf
                    <div class="col-7 d-flex"><input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('Reason for the customer') }}" required>
                        <button class="btn btn-outline-danger text-nowrap">{{ __('Refuse and refund') }}</button></div>
                </form>
            </fieldset>
            @endif

            @if ($canManage && $status === 'active')
            <fieldset>
                <legend><i class="mr-3 mdi mdi-autorenew"></i>{{ __('Renewal') }}</legend>
                <div class="form-group row width-100"><div class="col-7"><p class="finance-help">{{ __('The daily job offers the renewal 30 days before the cover ends, at the current premium. To offer it now or at another premium:') }}</p></div></div>
                <form method="POST" action="{{ route('finance.insurance.policies.action', [$id, 'start-renewal']) }}" class="form-group row width-100" onsubmit="return confirm('{{ __('Offer the renewal to the customer now?') }}')">@csrf
                    <div class="col-7 d-flex"><div class="finance-amount-box mr-2" style="margin-bottom:0"><span class="finance-symbol">{{ $cur }}</span>
                        <input type="text" inputmode="decimal" name="renewalPremium" class="form-control" style="padding-left:48px" value="{{ Money::toMajor((int)($p['premium'] ?? 0), $cur) }}" required></div>
                        <button class="btn btn-outline-primary text-nowrap">{{ __('Offer the renewal now') }}</button></div>
                </form>
            </fieldset>
            @endif

            @if ($status === 'renewal_due')
            <fieldset>
                <legend><i class="mr-3 mdi mdi-autorenew"></i>{{ __('Renewal') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <p>{{ __('Renewal premium') }}: <strong>{{ Money::format((int)($p['renewalPremium'] ?? 0), $cur) }}</strong> · {{ __('by') }} {{ $day($p['renewalDate'] ?? $p['coverEnd'] ?? null) }}</p>
                    @if ($paidRenewal)
                        <div class="alert {{ $renewalTx && ($renewalTx['status'] ?? '') === 'posted' ? 'alert-success' : 'alert-danger' }}">{{ __('The customer paid the renewal') }}: {{ Money::format((int)($p['renewalPaidAmount'] ?? $p['renewalPremium'] ?? 0), $cur) }}
                            · <a href="{{ route('finance.ledger.show', $p['renewalTxId']) }}">{{ $p['renewalReference'] ?? $p['renewalTxId'] }}</a>
                            @if (!$renewalTx || ($renewalTx['status'] ?? '') !== 'posted') - {{ __('the posting is missing or not posted.') }}@endif</div>
                        @if ($canManage)
                        <form method="POST" action="{{ route('finance.insurance.policies.action', [$id, 'renew']) }}">@csrf<button class="btn btn-success">{{ __('Renew now') }}</button>
                            <span class="finance-help ml-1">{{ __('The daily job also does it.') }}</span></form>
                        @endif
                    @elseif ($canManage)
                        <form method="POST" action="{{ route('finance.insurance.policies.action', [$id, 'renewal-premium']) }}" class="d-flex">@csrf
                            <div class="finance-amount-box mr-2" style="margin-bottom:0"><span class="finance-symbol">{{ $cur }}</span>
                                <input type="text" inputmode="decimal" name="renewalPremium" class="form-control" style="padding-left:48px" value="{{ Money::toMajor((int)($p['renewalPremium'] ?? 0), $cur) }}" required></div>
                            <button class="btn btn-outline-primary text-nowrap">{{ __('Change the renewal premium') }}</button></form>
                        <p class="finance-help mt-2">{{ !empty($p['autoRenew']) ? __('Automatic renewal is on: from 3 days before the date, the daily job pays it from the customer\'s wallet if it holds enough.') : __('Waiting for the customer to pay it in the app. Not paid when the cover ends, the policy expires.') }}</p>
                    @endif
                </div></div>
            </fieldset>
            @endif

            @if ($status === 'cancellation_requested')
            <fieldset>
                <legend><i class="mr-3 mdi mdi-cancel"></i>{{ __('Cancellation requested') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <p>{{ __('Asked on') }} {{ $day($p['cancellationRequestedAt'] ?? null) }}@if (!empty($p['cancellationReason'])): <em>{{ $p['cancellationReason'] }}</em>@endif</p>
                    <p>{{ __('Unused part of what was paid for this cover (suggested refund):') }} <strong>{{ Money::format($refundSuggestion, $cur) }}</strong></p>
                    @if ($canManage)
                    <form method="POST" action="{{ route('finance.insurance.policies.action', [$id, 'cancel']) }}" class="d-flex mb-2" onsubmit="return confirm('{{ __('Cancel the policy and refund this amount to the customer\'s wallet?') }}')">@csrf
                        <div class="finance-amount-box mr-2" style="margin-bottom:0"><span class="finance-symbol">{{ $cur }}</span>
                            <input type="text" inputmode="decimal" name="refund" class="form-control" style="padding-left:48px" value="{{ old('refund', Money::toMajor($refundSuggestion, $cur)) }}" required></div>
                        <input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('Note (optional)') }}">
                        <button class="btn btn-danger text-nowrap">{{ __('Cancel with this refund') }}</button></form>
                    <form method="POST" action="{{ route('finance.insurance.policies.action', [$id, 'keep']) }}" class="d-flex">@csrf
                        <input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('Note (optional)') }}">
                        <button class="btn btn-outline-primary text-nowrap">{{ __('Keep the policy') }}</button></form>
                    <p class="finance-help mt-2">{{ __('0 cancels without a refund. A refund is paid from the platform account to the customer\'s Finance wallet.') }}</p>
                    @endif
                </div></div>
            </fieldset>
            @endif

            <fieldset>
                <legend><i class="mr-3 mdi mdi-calendar-clock"></i>{{ __('Premiums') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>#</th><th>{{ __('Due') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th><th>{{ __('Paid') }}</th><th>{{ __('Reference') }}</th></tr></thead>
                        <tbody>
                        @forelse ($premiums as $pr)
                            @php $d = $pr['data']; @endphp
                            <tr><td>{{ substr(strrchr($pr['id'], '_'), 1) }}</td><td>{{ $day($d['dueDate'] ?? null) }}</td><td class="finance-amount">{{ Money::format((int)($d['amount'] ?? 0), $cur) }}</td>
                                <td>{{ $d['status'] ?? '' }}@if (!empty($d['refundedAt'])) · {{ __('refunded') }}@endif</td><td>{{ $day($d['paidAt'] ?? null) }}</td>
                                <td class="finance-mono">@if (!empty($d['txId']))<a href="{{ route('finance.ledger.show', $d['txId']) }}">{{ $d['reference'] ?? $d['txId'] }}</a>@else{{ $d['reference'] ?? '' }}@endif</td></tr>
                        @empty
                            <tr><td colspan="6" class="text-muted">{{ __('Nothing yet.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-medical-bag"></i>{{ __('Claims') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Claimed') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                        <tbody>
                        @forelse ($claims as $c)
                            <tr><td>{{ $day($c['data']['createdAt'] ?? null) }}</td><td class="finance-amount">{{ Money::format((int)($c['data']['amountClaimed'] ?? 0), $cur) }}</td><td>{{ $c['data']['status'] ?? '' }}</td>
                                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('finance.insurance.claims.show', $c['id']) }}">{{ __('Open') }}</a></td></tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">{{ __('No claim.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div></div>
            </fieldset>
        </div></div>
        </div></div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(function () {
        $('#po-add-doc').on('click', function () { var $r = $('#po-docs tbody tr:first').clone(); $r.find('input').val(''); $('#po-docs tbody').append($r); });
    });
</script>
@endsection
