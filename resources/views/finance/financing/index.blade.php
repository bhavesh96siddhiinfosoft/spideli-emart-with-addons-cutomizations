@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $day = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d') : ''; };
    $tabs = ['review' => __('To review'), 'in_review' => __('Under review'), 'info' => __('Waiting for the owner'), 'approved' => __('Approved'), 'funded' => __('Funded'), 'closed' => __('Closed')];
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Project financing'), 'tab' => 'financing'])
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="mb-0 text-dark-2">{{ __('Business owners ask financing for their projects in the app. Review each application, check its documents, then approve or reject it and record the funding the institution pays.') }}</p>
            <a class="btn btn-outline-primary text-nowrap ml-3" href="{{ route('finance.financing.resources') }}"><i class="mdi mdi-book-open-variant mr-1"></i>{{ __('Resources') }}</a>
        </div>
        <ul class="nav nav-tabs mb-3">
            @foreach ($tabs as $k => $label)
                <li class="nav-item"><a class="nav-link {{ $tab === $k ? 'active' : '' }}" href="{{ route('finance.financing', ['tab' => $k]) }}">{{ $label }}
                    @if (!empty($counts[$k]) && in_array($k, ['review', 'in_review'], true))<span class="badge badge-warning ml-1">{{ $counts[$k] }}</span>@elseif (!empty($counts[$k]))<span class="counter ml-1">{{ $counts[$k] }}</span>@endif</a></li>
            @endforeach
        </ul>
        <div class="card border mb-4"><div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="financeFinancingTable">
                    <thead><tr><th>{{ __('Submitted') }}</th><th>{{ __('Project') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($rows as $r)
                        @php $d = $r['data']; @endphp
                        <tr>
                            <td data-order="{{ ($d['submittedAt'] ?? null) instanceof \DateTimeInterface ? $d['submittedAt']->getTimestamp() : 0 }}">{{ $day($d['submittedAt'] ?? $d['createdAt'] ?? null) }}</td>
                            <td>{{ $d['projectName'] ?? '' }} @if (!empty($d['demo']))<span class="badge badge-secondary">demo</span>@endif</td>
                            <td>{{ $names[$d['ownerId'] ?? ''] ?? ($d['ownerId'] ?? '') }}</td>
                            <td>{{ __(ucfirst($d['financingType'] ?? '')) }}@if (!empty($d['durationMonths'])) · {{ $d['durationMonths'] }} {{ __('months') }}@endif</td>
                            <td class="finance-amount">{{ Money::format((int)($d['amount'] ?? 0), $d['currency'] ?? '') }}</td>
                            <td>{{ $d['status'] ?? '' }}</td>
                            <td><a class="btn btn-sm btn-primary" href="{{ route('finance.financing.show', $r['id']) }}">{{ __('Open') }}</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
</div>
@endsection
@section('scripts')
<script>$(function () { $('#financeFinancingTable').DataTable({ pageLength: 25, order: [], language: typeof datatableLang !== 'undefined' ? datatableLang : {} }); });</script>
@endsection
