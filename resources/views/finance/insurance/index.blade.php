@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    use App\Finance\Insurance;
    $day = function ($v) { $d = Insurance::date($v); return $d ? $d->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d') : ''; };
    $tabs = ['quotes' => __('Quotes to price'), 'activate' => __('Policies to activate'), 'claims' => __('Claims'),
        'cancellations' => __('Cancellations'), 'renewals' => __('Renewals'), 'policies' => __('All policies')];
    $help = [
        'quotes' => __('Customers asked for a price. Open one: the panel suggests the premium from the product\'s rating; check it and send the quote.'),
        'activate' => __('Customers accepted a quote and paid the first premium. Activate the policy with the insurer\'s policy number, or refuse it (the premium is refunded).'),
        'claims' => __('Claims to review, waiting for the customer\'s answer, or approved and waiting to be paid.'),
        'cancellations' => __('Customers asked to cancel. Cancel with a refund you decide (the unused part is suggested), or keep the policy.'),
        'renewals' => __('Policies in their renewal window. The customer pays in the app (or the wallet pays it if they chose automatic renewal); you can change the renewal premium until it is paid.'),
        'policies' => __('Every policy by status.'),
    ];
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Insurance'), 'tab' => 'insurance'])
    <div class="container-fluid">
        <div class="d-flex justify-content-end mb-3">
            <a class="btn btn-outline-primary" href="{{ route('finance.insurance.products') }}"><i class="mdi mdi-shield-check mr-1"></i>{{ __('Products') }}</a>
            <a class="btn btn-outline-primary ml-2" href="{{ route('finance.insurance.partners') }}"><i class="mdi mdi-domain mr-1"></i>{{ __('Insurers (partners)') }}</a>
        </div>
        <ul class="nav nav-tabs mb-3">
            @foreach ($tabs as $k => $label)
                <li class="nav-item"><a class="nav-link {{ $tab === $k ? 'active' : '' }}" href="{{ route('finance.insurance', ['tab' => $k]) }}">{{ $label }}
                    @if (!empty($counts[$k]))<span class="badge badge-warning ml-1">{{ $counts[$k] }}</span>@endif</a></li>
            @endforeach
        </ul>
        <div class="card border mb-4">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <p class="mb-0 text-dark-2">{{ $help[$tab] }}</p>
                @if ($tab === 'policies')
                <form method="GET" class="form-inline"><input type="hidden" name="tab" value="policies">
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        @foreach (['active' => __('Active'), 'lapsed' => __('Lapsed'), 'cancelled' => __('Cancelled'), 'expired' => __('Expired'), 'rejected' => __('Refused'), 'all' => __('All')] as $k => $label)
                            <option value="{{ $k }}" {{ $status === $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="financeInsuranceTable">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Product') }}</th>
                            @if ($tab === 'claims')<th>{{ __('Claimed') }}</th>@else<th>{{ __('Premium') }}</th>@endif
                            <th>{{ __('Status') }}</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($rows as $r)
                            @php
                                $d = $r['data'];
                                $cur = $d['currency'] ?? '';
                                $route = $tab === 'quotes' ? route('finance.insurance.quotes.show', $r['id']) : ($tab === 'claims' ? route('finance.insurance.claims.show', $r['id']) : route('finance.insurance.policies.show', $r['id']));
                            @endphp
                            <tr>
                                <td data-order="{{ ($x = Insurance::date($d['createdAt'] ?? null)) ? $x->getTimestamp() : 0 }}">{{ $day($d['createdAt'] ?? null) }}</td>
                                <td>{{ $names[$d['userId'] ?? ''] ?? ($d['userId'] ?? '') }}</td>
                                <td>{{ $d['productName'] ?? '' }} @if (!empty($d['demo']))<span class="badge badge-secondary">demo</span>@endif
                                    <div class="finance-help">{{ $d['partnerName'] ?? '' }}{{ !empty($d['policyNumber']) ? ' · ' . $d['policyNumber'] : '' }}</div></td>
                                <td class="finance-amount">
                                    @if ($tab === 'claims'){{ Money::format((int)($d['amountClaimed'] ?? 0), $cur) }}
                                    @elseif ($tab === 'renewals'){{ Money::format((int)($d['renewalPremium'] ?? 0), $cur) }}<div class="finance-help">{{ __('due by') }} {{ $day($d['renewalDate'] ?? $d['coverEnd'] ?? null) }}@if (!empty($d['renewalRequestedAt'])) · <strong>{{ __('paid') }}</strong>@endif</div>
                                    @elseif (isset($d['installmentAmount'])){{ Money::format((int)$d['installmentAmount'], $cur) }} <span class="finance-help">{{ $d['frequency'] ?? '' }}</span>
                                    @else <span class="finance-help">{{ $d['frequency'] ?? '' }}</span>@endif
                                </td>
                                <td>{{ $d['status'] ?? '' }}</td>
                                <td><a class="btn btn-sm btn-primary" href="{{ $route }}">{{ __('Open') }}</a></td>
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
<script>$(function () { $('#financeInsuranceTable').DataTable({ pageLength: 25, order: [], language: typeof datatableLang !== 'undefined' ? datatableLang : {} }); });</script>
@endsection
