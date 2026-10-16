@extends('layouts.app')
@section('content')
@php
    $tz = new DateTimeZone(config('app.timezone'));
    $now = new DateTimeImmutable();
    $fmt = function ($v) use ($tz) { return $v instanceof \DateTimeInterface ? $v->setTimezone($tz)->format('Y-m-d H:i') : ''; };
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Markets and content'), 'tab' => 'content'])
    <div class="container-fluid">
        @include('finance.content._tabs')
        <div class="card border mb-4">
            <div class="card-header border-0">
                <p class="mb-0 text-dark-2">{{ __('Customers convert money between currencies at the newest rate of a pair (or its inverse). A rate older than') }} <strong>{{ $maxAge }} h</strong>
                    {{ __('is refused, so conversions stop until a new one is posted. A pair marked "fixed" (an official peg such as EUR → XAF 655.957) is re-posted every morning.') }}</p>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped mb-0">
                    <thead><tr><th>{{ __('Pair') }}</th><th>{{ __('Rate') }}</th><th>{{ __('Source') }}</th><th>{{ __('Kind') }}</th><th>{{ __('Observed') }}</th><th>{{ __('Fixed') }}</th></tr></thead>
                    <tbody>
                    @forelse ($latest as $pair => $r)
                        @php $old = !($r['observedAt'] ?? null) instanceof \DateTimeInterface || $r['observedAt'] < $now->modify('-' . $maxAge . ' hours'); @endphp
                        <tr>
                            <td>1 {{ $r['from'] ?? '' }} = … {{ $r['to'] ?? '' }}</td>
                            <td class="finance-amount">{{ rtrim(rtrim(number_format((float)($r['rate'] ?? 0), 8, '.', ' '), '0'), '.') }}</td>
                            <td>{{ $r['source'] ?? '' }}</td>
                            <td>{{ $r['kind'] ?? '' }}</td>
                            <td>{{ $fmt($r['observedAt'] ?? null) }} @if ($old)<span class="finance-badge-no">{{ __('too old: conversions refused') }}</span>@else<span class="finance-badge-ok">{{ __('usable') }}</span>@endif</td>
                            <td>@if (isset($fixed[$pair]))<span class="finance-badge-ok">{{ __('Yes') }}</span>
                                @if ($canManage)<form method="POST" action="{{ route('finance.content.rates.unfix', $pair) }}" class="d-inline ml-1" onsubmit="return confirm('{{ __('Stop re-posting this rate every morning?') }}')">@csrf<button class="btn btn-sm btn-link p-0">{{ __('stop') }}</button></form>@endif
                                @else — @endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">{{ __('No rate yet: conversions are refused until one is posted.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($canManage)
        <div class="card mb-4"><div class="card-body">
            <form method="POST" action="{{ route('finance.content.rates.add') }}">@csrf
                <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
                <fieldset>
                    <legend><i class="mr-3 mdi mdi-swap-horizontal"></i>{{ __('Post a rate') }}</legend>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="rt-from">{{ __('1 unit of') }}<span class="required-field"></span></label>
                        <div class="col-7"><input type="text" id="rt-from" name="from" maxlength="3" class="form-control" value="{{ old('from', 'EUR') }}" required></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="rt-to">{{ __('Is worth (currency)') }}<span class="required-field"></span></label>
                        <div class="col-7"><input type="text" id="rt-to" name="to" maxlength="3" class="form-control" value="{{ old('to', 'XAF') }}" required></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="rt-rate">{{ __('Rate') }}<span class="required-field"></span></label>
                        <div class="col-7"><input type="text" inputmode="decimal" id="rt-rate" name="rate" class="form-control" value="{{ old('rate') }}" placeholder="655.957" required>
                            <div class="form-text text-muted">{{ __('In major units: 1 EUR = 655.957 XAF.') }}</div></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="rt-source">{{ __('Source') }}<span class="required-field"></span></label>
                        <div class="col-7"><input type="text" id="rt-source" name="source" class="form-control" value="{{ old('source') }}" placeholder="BEAC" required></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="rt-kind">{{ __('Kind') }}</label>
                        <div class="col-7"><select id="rt-kind" name="kind" class="form-control finance-select2">
                            <option value="official" {{ old('kind', 'official') === 'official' ? 'selected' : '' }}>{{ __('Official') }}</option>
                            <option value="indicative" {{ old('kind') === 'indicative' ? 'selected' : '' }}>{{ __('Indicative') }}</option></select></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="rt-at">{{ __('Observed at') }}</label>
                        <div class="col-7"><input type="datetime-local" id="rt-at" name="observedAt" class="form-control" value="{{ old('observedAt') }}"><div class="form-text text-muted">{{ __('Blank = now.') }}</div></div></div>
                    <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Fixed') }}</label>
                        <div class="col-7"><div class="form-check"><input type="checkbox" id="rt-fixed" name="fixed" value="1" {{ old('fixed') ? 'checked' : '' }}>
                            <label class="control-label" for="rt-fixed">{{ __('An official peg: re-post this rate every morning') }}</label></div></div></div>
                </fieldset>
                </div></div>
                <div class="form-group col-12 text-center btm-btn"><button class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Post the rate') }}</button></div>
            </form>
        </div></div>
        <div class="card border mb-4"><div class="card-body">
            <h4 class="h5">{{ __('Import rates from a CSV file') }}</h4>
            <p class="finance-help">{{ __('Columns: from, to, rate, source, kind (official / indicative), observedAt (YYYY-MM-DD HH:MM, blank = now). One snapshot per row. Every row is checked first: if one is wrong, nothing is imported and the lines are listed.') }}
                <a href="{{ route('finance.content.template', 'rates') }}">{{ __('Download an empty file') }}</a></p>
            <form method="POST" enctype="multipart/form-data" action="{{ route('finance.content.import', 'rates') }}" class="d-flex">@csrf
                <input type="file" name="file" accept=".csv,text/csv" class="form-control mr-2" required>
                <button class="btn btn-outline-primary text-nowrap">{{ __('Import') }}</button></form>
        </div></div>
        @endif
    </div>
</div>
@endsection
@section('scripts')
<script>$(function () { $('.finance-select2').select2({ width: '100%' }); });</script>
@endsection
