@extends('layouts.app')
@section('content')
@php use App\Finance\Money; @endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Insurance products'), 'tab' => 'insurance'])
    <div class="container-fluid">
        <div class="card border mb-4">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="text-dark-2 mb-1 h4">{{ __('Insurance products') }}</h3>
                    <p class="mb-0 text-dark-2">{{ __('The covers customers can ask a quote for in the app (published and open).') }}</p>
                </div>
                <div class="text-nowrap">
                    <a class="btn btn-outline-primary" href="{{ route('finance.insurance') }}">{{ __('Back to insurance') }}</a>
                    @if ($canManage)<a class="btn btn-primary ml-1" href="{{ route('finance.insurance.products.create') }}"><i class="mdi mdi-plus mr-1"></i>{{ __('New product') }}</a>@endif
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped mb-0" id="financeProductsTable">
                    <thead><tr><th>{{ __('Product') }}</th><th>{{ __('Insurer') }}</th><th>{{ __('Category') }}</th><th>{{ __('Base premium (a year)') }}</th><th>{{ __('Term') }}</th><th>{{ __('Status') }}</th><th>{{ __('Published') }}</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($rows as $r)
                        @php $d = $r['data']; @endphp
                        <tr>
                            <td>{{ $d['name'] ?? '' }} @if (!empty($d['demo']))<span class="badge badge-secondary">demo</span>@endif<div class="finance-help">{{ $d['summary'] ?? '' }}</div></td>
                            <td>{{ $d['partnerName'] ?? '' }}</td>
                            <td>{{ __(ucfirst($d['category'] ?? '')) }}</td>
                            <td class="finance-amount">{{ isset($d['rating']['basePremium']) ? Money::format((int)$d['rating']['basePremium'], $d['currency'] ?? '') : '—' }}</td>
                            <td>{{ (int)($d['termMonths'] ?? 12) }} {{ __('months') }}</td>
                            <td>{{ $d['status'] ?? 'open' }}</td>
                            <td>@if (!empty($d['published']))<span class="finance-badge-ok">{{ __('Yes') }}</span>@else<span class="finance-badge-no">{{ __('No') }}</span>@endif</td>
                            <td>@if ($canManage)<a class="btn btn-sm btn-primary" href="{{ route('finance.insurance.products.edit', $r['id']) }}">{{ __('Edit') }}</a>@endif</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>$(function () { $('#financeProductsTable').DataTable({ pageLength: 25, order: [], language: typeof datatableLang !== 'undefined' ? datatableLang : {} }); });</script>
@endsection
