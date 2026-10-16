@extends('layouts.app')
@section('content')
@php $q = ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]; @endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Finance reports'), 'tab' => 'reports'])
    <div class="container-fluid">
        <div class="card border mb-4 finance-no-print"><div class="card-body">
            <form method="GET" class="d-flex align-items-center flex-wrap">
                <label class="mr-2 mb-0">{{ __('Period') }}</label>
                <input type="date" name="from" class="form-control mr-2" style="max-width:180px" value="{{ $q['from'] }}">
                <span class="mr-2">→</span>
                <input type="date" name="to" class="form-control mr-2" style="max-width:180px" value="{{ $q['to'] }}">
                <button class="btn btn-primary mr-2">{{ __('Show') }}</button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="mdi mdi-printer"></i> {{ __('Print / PDF') }}</button>
            </form>
            <p class="finance-help mt-2 mb-0">{{ __('Figures are read from Firestore when the page opens. Totals are as of now; flows (new customers, contributions, activity, premiums, payouts) cover the period.') }}
                @if (\App\Finance\Regions::allowedIds()){{ __('Only your regions\' customers and currencies are counted.') }}@endif</p>
            @if ($truncated)<div class="alert alert-warning mt-2 mb-0">{{ __('The period is very large; these lists were cut at 20 000 records:') }} {{ implode(', ', array_unique($truncated)) }}. {{ __('Choose a shorter period.') }}</div>@endif
        </div></div>

        @foreach ($reports as $key => $r)
        <div class="card border mb-4">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <div><h3 class="text-dark-2 mb-1 h4">{{ $r['title'] }}</h3>@if (!empty($r['note']))<p class="mb-0 finance-help">{{ $r['note'] }}</p>@endif</div>
                @if ($r['columns'])<a class="btn btn-sm btn-outline-primary finance-no-print" href="{{ route('finance.reports.export', ['section' => $key] + $q) }}"><i class="mdi mdi-download"></i> CSV</a>@endif
            </div>
            <div class="card-body">
                @if ($r['columns'])
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr>@foreach ($r['columns'] as $c)<th>{{ $c }}</th>@endforeach</tr></thead>
                        <tbody>
                        @forelse ($r['rows'] as $row)
                            <tr>@foreach ($row as $i => $v)<td class="{{ $i > 0 ? 'finance-amount' : '' }}">{{ $v === null ? '—' : $v }}</td>@endforeach</tr>
                        @empty
                            <tr><td colspan="{{ count($r['columns']) }}" class="text-muted">{{ __('Nothing in this period.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
<style>@media print { .finance-no-print, .left-sidebar, .topbar, .finance-tabs, .page-titles .breadcrumb { display: none !important; } .page-wrapper { margin: 0 !important; } }</style>
@endsection
