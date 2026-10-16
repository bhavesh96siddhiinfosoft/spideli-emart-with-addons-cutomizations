@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    use App\Finance\Investments;
    $fmt = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d') : ''; };
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Investments'), 'tab' => 'investments'])
    <div class="container-fluid">
        <div class="card border mb-4">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="text-dark-2 mb-1 h4">{{ __('Investment funds') }}</h3>
                    <p class="mb-0 text-dark-2">{{ __('Customers subscribe to published, open funds in the app. Open a fund to add its unit price and run its waiting orders at that price.') }}</p>
                </div>
                <div class="text-nowrap">
                    <a class="btn btn-outline-primary" href="{{ route('finance.investments.reports') }}"><i class="mdi mdi-file-pdf-box mr-1"></i>{{ __('Customer reports') }}</a>
                    @if ($canManage)<a class="btn btn-primary ml-1" href="{{ route('finance.investments.create') }}"><i class="mdi mdi-plus mr-1"></i>{{ __('New fund') }}</a>@endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="financeFundsTable">
                        <thead><tr><th>{{ __('Fund') }}</th><th>{{ __('Asset class') }}</th><th>{{ __('Risk') }}</th><th>{{ __('Unit price') }}</th><th>{{ __('Price date') }}</th>
                            <th>{{ __('Status') }}</th><th>{{ __('Published') }}</th><th>{{ __('Waiting') }}</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($funds as $r)
                            @php $d = $r['data']; $w = $waiting[$r['id']] ?? []; @endphp
                            <tr>
                                <td>{{ $d['name'] ?? '' }} @if (!empty($d['demo']))<span class="badge badge-secondary">demo</span>@endif<div class="finance-help">{{ $d['manager'] ?? '' }}</div></td>
                                <td>{{ __(ucfirst(str_replace('_', ' ', $d['assetClass'] ?? ''))) }}</td>
                                <td>{{ $d['riskLevel'] ?? '' }}/7</td>
                                <td class="finance-amount">{{ isset($d['navPerUnit']) ? Investments::navText($d['navPerUnit'], $d['currency'] ?? '') : '—' }}</td>
                                <td>{{ $fmt($d['navDate'] ?? null) }}</td>
                                <td>{{ $d['status'] ?? '' }}</td>
                                <td>@if (!empty($d['published']))<span class="finance-badge-ok">{{ __('Yes') }}</span>@else<span class="finance-badge-no">{{ __('No') }}</span>@endif</td>
                                <td>
                                    @if (!empty($w['subscriptions']))<div>{{ __('Subscriptions') }}: <strong>{{ $w['subscriptions'] }}</strong></div>@endif
                                    @if (!empty($w['redemptions']))<div>{{ __('Redemptions') }}: <strong>{{ $w['redemptions'] }}</strong></div>@endif
                                    @if (!empty($w['settling']))<div>{{ __('To settle') }}: <strong>{{ $w['settling'] }}</strong></div>@endif
                                    @if (!$w)<span class="text-muted">—</span>@endif
                                </td>
                                <td class="text-nowrap"><a class="btn btn-sm btn-primary" href="{{ route('finance.investments.show', $r['id']) }}">{{ __('Open') }}</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>$(function () { $('#financeFundsTable').DataTable({ pageLength: 25, order: [], language: typeof datatableLang !== 'undefined' ? datatableLang : {} }); });</script>
@endsection
