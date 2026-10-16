@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $fmt = function ($v) use ($cur) { return $v === null ? '—' : Money::format((int)$v, $cur); };
    $diffClass = function ($v) { return (int)$v === 0 ? 'finance-badge-ok' : 'finance-badge-no'; };
    $kinds = ['withdrawals' => __('Withdrawals'), 'transfers' => __('External transfers'), 'bills' => __('Bill payments')];
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Bank reconciliation'), 'tab' => 'ledger'])
    <div class="container-fluid">
        <div class="card border mb-3"><div class="card-body">
            <form method="GET" class="d-flex align-items-center flex-wrap">
                <label class="mr-2 mb-0">{{ __('Day') }}</label>
                <input type="date" name="date" class="form-control mr-2" style="max-width:180px" value="{{ $day->format('Y-m-d') }}" max="{{ (new DateTimeImmutable('today'))->format('Y-m-d') }}">
                <label class="mr-2 mb-0">{{ __('Currency') }}</label>
                <select name="currency" class="form-control mr-2" style="max-width:140px">@foreach ($currencies as $c)<option value="{{ $c }}" {{ $c === $cur ? 'selected' : '' }}>{{ $c }}</option>@endforeach</select>
                <button class="btn btn-primary">{{ __('Show') }}</button>
            </form>
            <p class="finance-help mt-2 mb-0">{{ __('Compare what the panel recorded for the day with the bank / payment-provider statement of Spideli\'s account. In = customer payments received through the gateways; out = payouts completed (withdrawals, external transfers, bills).') }}</p>
            @if ($missing)<div class="alert alert-warning mt-2 mb-0">{{ __('Not reconciled in the last 14 days:') }}
                @foreach ($missing as $m)<a class="mr-2" href="{{ route('finance.reconciliation', ['currency' => $cur, 'date' => $m]) }}">{{ $m }}</a>@endforeach</div>@endif
        </div></div>

        @if ($expected)
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
            <fieldset>
                <legend><i class="mr-3 mdi mdi-bank"></i>{{ $day->format('Y-m-d') }} · {{ $cur }}
                    @if ($saved)<span class="ml-2 {{ $saved['status'] === 'matched' ? 'finance-badge-ok' : 'finance-badge-no' }}">{{ $saved['status'] === 'matched' ? __('matched') : __('difference') }}</span>@endif</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered mb-0">
                        <thead><tr><th></th><th>{{ __('Recorded by the panel') }}</th><th>{{ __('Statement') }}</th><th>{{ __('Difference') }}</th></tr></thead>
                        <tbody>
                            <tr><td><strong>{{ __('Money in') }}</strong>
                                    <div class="finance-help">{{ $expected['in']['count'] }} {{ __('gateway payments') }}, {{ $expected['in']['verified'] }} {{ __('verified') }}
                                    @foreach ($expected['in']['byMethod'] as $m => $v) · {{ $m }} {{ $fmt($v) }}@endforeach</div></td>
                                <td class="finance-amount">{{ $fmt($expected['in']['total']) }}</td>
                                <td class="finance-amount">{{ $saved ? $fmt($saved['actualIn']) : '—' }}</td>
                                <td>@if ($saved)<span class="{{ $diffClass($saved['actualIn'] - $expected['in']['total']) }}">{{ $fmt($saved['actualIn'] - $expected['in']['total']) }}</span>@endif</td></tr>
                            <tr><td><strong>{{ __('Money out') }}</strong>
                                    <div class="finance-help">{{ $expected['out']['count'] }} {{ __('payouts') }}
                                    @foreach ($expected['out']['byKind'] as $k => $v) · {{ $kinds[$k] ?? $k }} {{ $fmt($v) }}@endforeach</div></td>
                                <td class="finance-amount">{{ $fmt($expected['out']['total']) }}</td>
                                <td class="finance-amount">{{ $saved ? $fmt($saved['actualOut']) : '—' }}</td>
                                <td>@if ($saved)<span class="{{ $diffClass($saved['actualOut'] - $expected['out']['total']) }}">{{ $fmt($saved['actualOut'] - $expected['out']['total']) }}</span>@endif</td></tr>
                            <tr><td><strong>{{ __('Net') }}</strong></td><td class="finance-amount">{{ $fmt($expected['net']) }}</td>
                                <td class="finance-amount">{{ $saved ? $fmt($saved['actual']) : '—' }}</td>
                                <td>@if ($saved)<span class="{{ $diffClass($saved['actual'] - $expected['net']) }}">{{ $fmt($saved['actual'] - $expected['net']) }}</span>@endif</td></tr>
                        </tbody>
                    </table>
                    @if ($saved && ($saved['expectedIn'] !== $expected['in']['total'] || $saved['expectedOut'] !== $expected['out']['total']))
                        <div class="alert alert-warning mt-2 mb-0">{{ __('The panel\'s figures changed since this day was saved (a late record or a verification). Save again to update.') }}</div>
                    @endif
                    @if ($saved)<p class="finance-help mt-2 mb-0">{{ __('Saved by') }} {{ $saved['byEmail'] ?? $saved['by'] ?? '' }}@if (!empty($saved['notes'])) · {{ $saved['notes'] }}@endif</p>@endif
                </div></div>
            </fieldset>
            <fieldset>
                <legend><i class="mr-3 mdi mdi-file-document-edit"></i>{{ __('Enter the statement') }}</legend>
                <form method="POST" action="{{ route('finance.reconciliation.save') }}">@csrf
                    <input type="hidden" name="date" value="{{ $day->format('Y-m-d') }}"><input type="hidden" name="currency" value="{{ $cur }}">
                    @foreach (['actualIn' => __('Money in on the statement'), 'actualOut' => __('Money out on the statement')] as $k => $label)
                        <div class="form-group row width-50"><label class="col-3 control-label" for="rc-{{ $k }}">{{ $label }}<span class="required-field"></span></label>
                            <div class="col-7"><div class="finance-amount-box"><span class="finance-symbol">{{ $cur }}</span>
                                <input type="text" inputmode="decimal" id="rc-{{ $k }}" name="{{ $k }}" class="form-control" style="padding-left:48px" required
                                    value="{{ old($k, $saved ? Money::toMajor((int)$saved[$k], $cur) : '') }}"></div></div></div>
                    @endforeach
                    <div class="form-group row width-100"><label class="col-3 control-label" for="rc-notes">{{ __('Notes') }}</label>
                        <div class="col-7"><textarea id="rc-notes" name="notes" class="form-control" rows="2" placeholder="{{ __('Required when there is a difference: what explains it') }}">{{ old('notes', $saved['notes'] ?? '') }}</textarea></div></div>
                    <div class="form-group row width-100"><div class="col-7"><button class="btn btn-primary">{{ $saved ? __('Save again') : __('Save the reconciliation') }}</button></div></div>
                </form>
            </fieldset>
        </div></div>
        </div></div>
        @endif

        <div class="card border mb-4"><div class="card-body">
            <h4 class="h5">{{ __('Reconciled days') }} ({{ $cur }})</h4>
            <table class="table table-bordered table-striped table-sm mb-0">
                <thead><tr><th>{{ __('Day') }}</th><th>{{ __('In: panel / statement') }}</th><th>{{ __('Out: panel / statement') }}</th><th>{{ __('Difference') }}</th><th>{{ __('Notes') }}</th><th>{{ __('By') }}</th></tr></thead>
                <tbody>
                @forelse ($history as $h)
                    @php $d = $h['data']; @endphp
                    <tr><td><a href="{{ route('finance.reconciliation', ['currency' => $cur, 'date' => $d['date'] ?? '']) }}">{{ $d['date'] ?? '' }}</a></td>
                        <td class="finance-amount">{{ $fmt($d['expectedIn'] ?? null) }} / {{ $fmt($d['actualIn'] ?? null) }}</td>
                        <td class="finance-amount">{{ $fmt($d['expectedOut'] ?? null) }} / {{ $fmt($d['actualOut'] ?? null) }}</td>
                        <td><span class="{{ $diffClass($d['difference'] ?? 0) }}">{{ $fmt($d['difference'] ?? 0) }}</span></td>
                        <td>{{ $d['notes'] ?? '' }}</td><td>{{ $d['byEmail'] ?? '' }}</td></tr>
                @empty
                    <tr><td colspan="6" class="text-muted">{{ __('Nothing yet.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
</div>
@endsection
