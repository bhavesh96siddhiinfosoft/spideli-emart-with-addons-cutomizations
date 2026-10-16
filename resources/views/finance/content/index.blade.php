@extends('layouts.app')
@section('content')
@php
    $tz = new DateTimeZone(config('app.timezone'));
    $show = function ($v, $key) use ($s, $tz) {
        if ($v instanceof \DateTimeInterface) { return $v->setTimezone($tz)->format(($s['fields'][$key][0] ?? '') === 'datetime' ? 'Y-m-d H:i' : 'Y-m-d'); }
        if (is_bool($v)) { return $v ? __('Yes') : __('No'); }
        if (is_float($v)) { return rtrim(rtrim(number_format($v, 6, '.', ' '), '0'), '.'); }
        if (is_array($v)) { return count($v); }
        $f = $s['fields'][$key] ?? null;
        if ($f && $f[0] === 'select') { return __($f['options'][$v] ?? $v); }
        return (string)$v;
    };
    $import = ['indicators' => 'indicators', 'securities' => 'prices'][$tab] ?? null;
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Markets and content'), 'tab' => 'content'])
    <div class="container-fluid">
        @include('finance.content._tabs')
        <div class="card border mb-4">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <p class="mb-0 text-dark-2">{{ __($s['help']) }} {{ __('Customers see only the published ones.') }}</p>
                @if ($canManage)<a class="btn btn-primary text-nowrap ml-3" href="{{ route('finance.content.create', $tab) }}"><i class="mdi mdi-plus mr-1"></i>{{ __('Add') }}</a>@endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="financeContentTable">
                        <thead><tr>@foreach ($s['columns'] as $c)<th>{{ __($s['fields'][$c][1] ?? $c) }}</th>@endforeach<th>{{ __('Published') }}</th><th>{{ __('Updated') }}</th>@if ($canManage)<th></th>@endif</tr></thead>
                        <tbody>
                        @foreach ($rows as $r)
                            @php $d = $r['data']; @endphp
                            <tr>
                                @foreach ($s['columns'] as $c)
                                    <td>{{ $show($d[$c] ?? '', $c) }}@if ($c === 'value' && !empty($d['unit'])) {{ $d['unit'] }}@endif @if ($loop->first && !empty($d['demo']))<span class="badge badge-secondary">demo</span>@endif</td>
                                @endforeach
                                <td>@if (!empty($d['published']))<span class="finance-badge-ok">{{ __('Yes') }}</span>@else<span class="finance-badge-no">{{ __('No') }}</span>@endif</td>
                                <td>{{ $show($d['updatedAt'] ?? '', 'updatedAt') }}</td>
                                @if ($canManage)<td><a class="btn btn-sm btn-outline-primary" href="{{ route('finance.content.edit', [$tab, $r['id']]) }}">{{ __('Edit') }}</a></td>@endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if ($canManage && $import)
        <div class="card border mb-4"><div class="card-body">
            <h4 class="h5">{{ $import === 'prices' ? __('Import prices from a CSV file') : __('Import indicators from a CSV file') }}</h4>
            <p class="finance-help">
                @if ($import === 'prices'){{ __('Columns: symbol, date (YYYY-MM-DD), close. Each row is kept in the price history; the newest close becomes the security\'s price. The securities must exist.') }}
                @else{{ __('Columns: country, code, name, value, unit, currency, period, periodEnd, source, sourceUrl. An indicator with the same country and code is updated (its value kept in the history); a new one is created unpublished.') }}@endif
                {{ __('Every row is checked first: if one is wrong, nothing is imported and the lines are listed.') }}
                <a href="{{ route('finance.content.template', $import) }}">{{ __('Download an empty file') }}</a></p>
            <form method="POST" enctype="multipart/form-data" action="{{ route('finance.content.import', $import) }}" class="d-flex">@csrf
                <input type="file" name="file" accept=".csv,text/csv" class="form-control mr-2" required>
                <button class="btn btn-outline-primary text-nowrap">{{ __('Import') }}</button></form>
        </div></div>
        @endif
    </div>
</div>
@endsection
@section('scripts')
<script>$(function () { $('#financeContentTable').DataTable({ pageLength: 25, order: [], language: typeof datatableLang !== 'undefined' ? datatableLang : {} }); });</script>
@endsection
