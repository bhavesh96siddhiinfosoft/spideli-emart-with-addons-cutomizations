@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    use App\Finance\Insurance;
    $tz = new DateTimeZone(config('app.timezone'));
    $day = function ($v) use ($tz) { $d = Insurance::date($v); return $d ? $d->setTimezone($tz)->format('Y-m-d') : ''; };
    $cur = $q['currency'] ?? '';
    $status = $q['status'] ?? '';
    $start = Insurance::date($q['coverStart'] ?? null) ?: (new DateTimeImmutable('tomorrow'));
    $labels = [];
    foreach ((array)($product['rating']['factors'] ?? []) as $f) { $labels[$f['key'] ?? ''] = $f['label'] ?? ($f['key'] ?? ''); }
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Quote') . ': ' . ($q['productName'] ?? ''), 'tab' => 'insurance'])
    <div class="container-fluid">
        @if ($lockedBy)<div class="alert alert-warning">{{ __('Another admin is working on this quote:') }} {{ $lockedBy }}</div>@endif
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
            <fieldset>
                <legend><i class="mr-3 mdi mdi-file-document-edit"></i>{{ __('Quote request') }}</legend>
                @foreach ([
                    __('Customer') => $names[$q['userId'] ?? ''] ?? ($q['userId'] ?? ''),
                    __('Product') => ($q['productName'] ?? '') . (!empty($q['demo']) ? ' (demo)' : ''),
                    __('Insurer') => $q['partnerName'] ?? '',
                    __('Category') => __(ucfirst($q['category'] ?? '')),
                    __('Payment frequency') => $q['frequency'] ?? '',
                    __('Cover starts') => $day($q['coverStart'] ?? null) ?: __('not chosen'),
                    __('Status') => $status . (!empty($q['reason']) ? ' - ' . $q['reason'] : ''),
                    __('Asked on') => $day($q['createdAt'] ?? null),
                ] as $label => $value)
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ $label }}</label><div class="col-7"><span class="form-control-plaintext">{{ $value !== '' ? $value : '—' }}</span></div></div>
                @endforeach
                <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Answers') }}</label><div class="col-7">
                    <table class="table table-bordered table-sm mb-0"><tbody>
                    @forelse ((array)($q['answers'] ?? []) as $k => $a)
                        <tr><td style="width:40%">{{ $labels[$k] ?? $k }}</td><td>{{ is_scalar($a) ? $a : json_encode($a) }}</td></tr>
                    @empty
                        <tr><td class="text-muted">{{ __('No answers.') }}</td></tr>
                    @endforelse
                    </tbody></table>
                </div></div>
            </fieldset>

            @if ($status !== 'requested')
            <fieldset>
                <legend><i class="mr-3 mdi mdi-cash"></i>{{ __('Price sent') }}</legend>
                @foreach ([__('Premium (a year)') => isset($q['premium']) ? Money::format((int)$q['premium'], $cur) : '', __('Instalment') => isset($q['installmentAmount']) ? Money::format((int)$q['installmentAmount'], $cur) : '',
                    __('Deductible') => isset($q['deductible']) ? Money::format((int)$q['deductible'], $cur) : '', __('Cover') => $day($q['coverStart'] ?? null) . ' → ' . $day($q['coverEnd'] ?? null),
                    __('Valid until') => $day($q['validUntil'] ?? null), __('Insurer reference') => $q['insurerReference'] ?? ''] as $label => $value)
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ $label }}</label><div class="col-7"><span class="form-control-plaintext">{{ $value !== '' ? $value : '—' }}</span></div></div>
                @endforeach
                @if (!empty($q['policyId']))<div class="form-group row width-100"><div class="col-7"><a class="btn btn-sm btn-primary" href="{{ route('finance.insurance.policies.show', $q['policyId']) }}">{{ __('Open the policy') }}</a></div></div>@endif
            </fieldset>
            @endif

            @if ($canManage && $status === 'requested')
            <fieldset>
                <legend><i class="mr-3 mdi mdi-calculator"></i>{{ __('Price the quote') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    @if ($suggestion)
                        <div class="alert alert-info mb-0"><strong>{{ __('Suggested from the product\'s rating:') }}</strong> {{ Money::format($suggestion['premium'], $cur) }} {{ __('a year') }} · {{ Money::format($suggestion['installment'], $cur) }} {{ __('per payment') }}
                            <ul class="mb-0 mt-1">@foreach ($suggestion['lines'] as $l)<li>{{ $l }}</li>@endforeach</ul></div>
                    @else
                        <div class="alert alert-warning mb-0">{{ __('The product has no rating: enter the insurer\'s price.') }}</div>
                    @endif
                </div></div>
                <form method="POST" action="{{ route('finance.insurance.quotes.action', [$id, 'price']) }}" onsubmit="return confirm('{{ __('Send this price to the customer?') }}')">@csrf
                    @foreach ([['premium', __('Premium (a year)'), $suggestion['premium'] ?? ''], ['installmentAmount', __('Each payment') . ' (' . ($q['frequency'] ?? '') . ')', $suggestion['installment'] ?? ''],
                               ['deductible', __('Deductible'), $product['deductible'] ?? '']] as [$name, $label, $value])
                        <div class="form-group row width-50"><label class="col-3 control-label" for="q-{{ $name }}">{{ $label }}<span class="required-field"></span></label>
                            <div class="col-7"><div class="finance-amount-box"><span class="finance-symbol">{{ $cur }}</span>
                                <input type="text" inputmode="decimal" id="q-{{ $name }}" name="{{ $name }}" class="form-control" style="padding-left:48px" value="{{ old($name, $value === '' ? '' : Money::toMajor((int)$value, $cur)) }}" required></div></div></div>
                    @endforeach
                    <div class="form-group row width-50"><label class="col-3 control-label" for="q-start">{{ __('Cover starts') }}<span class="required-field"></span></label>
                        <div class="col-7"><input type="date" id="q-start" name="coverStart" class="form-control" value="{{ old('coverStart', $start->setTimezone($tz)->format('Y-m-d')) }}" required>
                            <div class="form-text text-muted">{{ __('The cover ends') }} {{ (int)($q['termMonths'] ?? 0) ?: (int)($product['termMonths'] ?? 12) }} {{ __('months later.') }}</div></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="q-valid">{{ __('Valid until') }}<span class="required-field"></span></label>
                        <div class="col-7"><input type="date" id="q-valid" name="validUntil" class="form-control" value="{{ old('validUntil', (new DateTimeImmutable('+' . $validDays . ' days'))->setTimezone($tz)->format('Y-m-d')) }}" required></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="q-ref">{{ __('Insurer reference') }}</label>
                        <div class="col-7"><input type="text" id="q-ref" name="insurerReference" class="form-control" value="{{ old('insurerReference') }}"></div></div>
                    <div class="form-group row width-100"><div class="col-7">
                        <p class="finance-help">{{ __('The quote also carries the product\'s current terms, coverages and documents.') }}</p>
                        <button class="btn btn-success">{{ __('Send the quote') }}</button></div></div>
                </form>
                <form method="POST" action="{{ route('finance.insurance.quotes.action', [$id, 'refuse']) }}" class="form-group row width-100">@csrf
                    <div class="col-7 d-flex"><input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('Reason for the customer') }}" required>
                        <button class="btn btn-outline-danger text-nowrap">{{ __('Refuse the quote') }}</button></div>
                </form>
            </fieldset>
            @endif
        </div></div>
        </div></div>
    </div>
</div>
@endsection
