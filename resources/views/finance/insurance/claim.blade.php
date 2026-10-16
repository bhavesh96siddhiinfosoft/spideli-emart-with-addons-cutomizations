@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    use App\Finance\Insurance;
    $tz = new DateTimeZone(config('app.timezone'));
    $day = function ($v, $time = false) use ($tz) { $d = Insurance::date($v); return $d ? $d->setTimezone($tz)->format($time ? 'Y-m-d H:i' : 'Y-m-d') : ''; };
    $cur = $c['currency'] ?? '';
    $status = $c['status'] ?? '';
    $cov = $policy ?? [];
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Claim') . ': ' . ($c['productName'] ?? ''), 'tab' => 'insurance'])
    <div class="container-fluid">
        @if ($lockedBy)<div class="alert alert-warning">{{ __('Another admin is working on this claim:') }} {{ $lockedBy }}</div>@endif
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
            <fieldset>
                <legend><i class="mr-3 mdi mdi-medical-bag"></i>{{ __('Claim') }}</legend>
                @foreach ([
                    __('Customer') => $names[$c['userId'] ?? ''] ?? ($c['userId'] ?? ''),
                    __('Product') => ($c['productName'] ?? '') . (!empty($c['demo']) ? ' (demo)' : ''),
                    __('Policy number') => $c['policyNumber'] ?? '',
                    __('Status') => $status,
                    __('Incident date') => $day($c['incidentDate'] ?? null),
                    __('Sent on') => $day($c['createdAt'] ?? null),
                    __('Amount claimed') => Money::format((int)($c['amountClaimed'] ?? 0), $cur),
                    __('Approved') => isset($c['approvedAmount']) ? Money::format((int)$c['approvedAmount'], $cur) : '',
                    __('Paid') => isset($c['paidAmount']) ? Money::format((int)$c['paidAmount'], $cur) . ' · ' . $day($c['paidAt'] ?? null) : '',
                    __('Deductible of the policy') => isset($cov['deductible']) ? Money::format((int)$cov['deductible'], $cur) : '',
                ] as $label => $value)
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ $label }}</label><div class="col-7"><span class="form-control-plaintext">{{ trim($value, ' ·') !== '' ? $value : '—' }}</span></div></div>
                @endforeach
                <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Description') }}</label><div class="col-7"><span class="form-control-plaintext" style="white-space:pre-line">{{ $c['description'] ?? '' }}</span></div></div>
                <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Coverages') }}</label><div class="col-7"><span class="form-control-plaintext">
                    @forelse ((array)($cov['coverages'] ?? []) as $x)<span class="mr-3">{{ $x['name'] ?? '' }}: {{ Money::format((int)($x['limit'] ?? 0), $cur) }}</span>@empty — @endforelse
                    @if (!empty($c['policyId']))<a class="ml-2" href="{{ route('finance.insurance.policies.show', $c['policyId']) }}">{{ __('Open the policy') }}</a>@endif
                </span></div></div>
                <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Photos and documents') }}</label><div class="col-7"><span class="form-control-plaintext">
                    @forelse ((array)($c['documents'] ?? []) as $i => $d)
                        <a class="mr-3" href="{{ route('finance.insurance.claims.file', [$id, $i]) }}" target="_blank" rel="noopener"><i class="mdi mdi-file-image"></i> {{ is_array($d) ? ($d['name'] ?? __('File') . ' ' . ($i + 1)) : __('File') . ' ' . ($i + 1) }}</a>
                    @empty — @endforelse
                </span><div class="finance-help">{{ __('Each link is valid 5 minutes.') }}</div></div></div>
            </fieldset>

            @if ($canManage && in_array($status, ['submitted', 'under_review', 'info_requested', 'approved'], true))
            <fieldset>
                <legend><i class="mr-3 mdi mdi-gavel"></i>{{ __('Decision') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    @if ($status === 'submitted')
                        <form method="POST" action="{{ route('finance.insurance.claims.action', [$id, 'review']) }}" class="mb-3">@csrf
                            <button class="btn btn-primary">{{ __('Start the review') }}</button> <span class="finance-help">{{ __('Tells the customer the claim was received and sent to the insurer.') }}</span></form>
                    @endif
                    @if (in_array($status, ['submitted', 'under_review'], true))
                        <form method="POST" action="{{ route('finance.insurance.claims.action', [$id, 'request-info']) }}" class="d-flex mb-3">@csrf
                            <input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('Question for the customer') }}" required>
                            <button class="btn btn-outline-primary text-nowrap">{{ __('Ask for more information') }}</button></form>
                        <form method="POST" action="{{ route('finance.insurance.claims.action', [$id, 'approve']) }}" class="d-flex mb-3" onsubmit="return confirm('{{ __('Approve this amount?') }}')">@csrf
                            <div class="finance-amount-box mr-2" style="margin-bottom:0"><span class="finance-symbol">{{ $cur }}</span>
                                <input type="text" inputmode="decimal" name="amount" class="form-control" style="padding-left:48px" value="{{ old('amount') }}" placeholder="{{ __('Approved amount') }}" required></div>
                            <input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('Note (optional)') }}">
                            <button class="btn btn-success text-nowrap">{{ __('Approve') }}</button></form>
                    @endif
                    @if ($status === 'info_requested')
                        <p class="finance-help">{{ __('Waiting for the customer\'s answer:') }} <em>{{ $c['reason'] ?? '' }}</em></p>
                    @endif
                    @if ($status === 'approved')
                        <form method="POST" action="{{ route('finance.insurance.claims.action', [$id, 'pay']) }}" class="d-flex mb-3" onsubmit="return confirm('{{ __('Pay the approved amount to the customer\'s Finance wallet?') }}')">@csrf
                            <input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('Insurer payment reference (optional)') }}">
                            <button class="btn btn-success text-nowrap">{{ __('Pay') }} {{ Money::format((int)($c['approvedAmount'] ?? 0), $cur) }} {{ __('to the wallet') }}</button></form>
                    @endif
                    @if ($status !== 'approved')
                        <form method="POST" action="{{ route('finance.insurance.claims.action', [$id, 'reject']) }}" class="d-flex" onsubmit="return confirm('{{ __('Reject this claim?') }}')">@csrf
                            <input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('Reason for the customer') }}" required>
                            <button class="btn btn-outline-danger text-nowrap">{{ __('Reject') }}</button></form>
                    @endif
                </div></div>
            </fieldset>
            @endif

            <fieldset>
                <legend><i class="mr-3 mdi mdi-history"></i>{{ __('History') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Step') }}</th><th>{{ __('By') }}</th><th>{{ __('Message') }}</th></tr></thead>
                        <tbody>
                        @forelse ($events as $e)
                            @php $d = $e['data']; @endphp
                            <tr><td>{{ $day($d['createdAt'] ?? null, true) }}</td><td>{{ $d['type'] ?? '' }}@if (isset($d['amount'])) · {{ Money::format((int)$d['amount'], $cur) }}@endif</td>
                                <td>{{ strpos($d['authorId'] ?? '', 'admin:') === 0 ? __('Spideli') : __('Customer') }}</td><td>{{ $d['message'] ?? '' }}@if (!empty($d['documents'])) <span class="finance-help">({{ count((array)$d['documents']) }} {{ __('files') }})</span>@endif</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">{{ __('Nothing yet.') }}</td></tr>
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
