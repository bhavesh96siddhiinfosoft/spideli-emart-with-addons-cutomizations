@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $fmt = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d') : ''; };
    $tabs = ['review' => __('To review'), 'voting' => __('Voting'), 'funding' => __('Funding'), 'active' => __('Active and overdue'), 'closed' => __('Closed')];
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Member loans'), 'tab' => 'loans'])
    <div class="container-fluid">
        <div class="payment-top-tab mb-3">
            <ul class="nav nav-tabs card-header-tabs align-items-end">
                @foreach ($tabs as $k => $label)
                    <li class="nav-item"><a class="nav-link {{ $tab === $k ? 'active' : '' }}" href="{{ route('finance.loans', ['tab' => $k]) }}">{{ $label }}
                        @if (!empty($counts[$k]))<span class="badge badge-warning ml-1">{{ $counts[$k] }}</span>@endif</a></li>
                @endforeach
            </ul>
        </div>
        <div class="card border mb-4">
            <div class="card-header border-0">
                <h3 class="text-dark-2 mb-1 h4">{{ $tabs[$tab] }}</h3>
                <p class="mb-0 text-dark-2">
                    @switch($tab)
                        @case('review'){{ __('New requests: start the review, add a credit assessment, then open the group vote - or reject.') }}@break
                        @case('voting'){{ __('The group is voting. A vote past its end that nobody closed is closed every hour by the panel.') }}@break
                        @case('funding'){{ __('Approved loans being funded by the group, or waiting for the borrower to accept the agreement. Stuck ones (over') }} {{ $stuckDays }} {{ __('days) can be refunded.') }}@break
                        @case('active'){{ __('Loans being repaid. The daily job marks instalments due and overdue (with the late penalty).') }}@break
                        @case('closed'){{ __('Repaid, rejected, cancelled and defaulted loans.') }}@break
                    @endswitch
                </p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="financeLoansTable">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Borrower') }}</th><th>{{ __('Group') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Progress') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($rows as $r)
                            @php $d = $r['data']; $c = $d['currency'] ?? ''; $stuck = \App\Http\Controllers\FinanceGroupsController::stuck($d); @endphp
                            <tr>
                                <td>{{ $fmt($d['createdAt'] ?? null) }}</td>
                                <td>{{ $names[$d['borrowerId'] ?? ''] ?? '' }} @if (!empty($d['demo']))<span class="badge badge-secondary">demo</span>@endif</td>
                                <td>{{ $d['groupName'] ?? '' }}</td>
                                <td class="finance-amount">{{ Money::format((int)($d['amount'] ?? 0), $c) }}<div class="finance-help">{{ $d['durationMonths'] ?? '' }} {{ __('months') }}</div></td>
                                <td>
                                    @switch($d['status'] ?? '')
                                        @case('voting'){{ __('yes') }} {{ $d['votes']['yes'] ?? 0 }} / {{ __('no') }} {{ $d['votes']['no'] ?? 0 }} / {{ count((array)($d['eligibleVoterIds'] ?? [])) }} · {{ __('ends') }} {{ $fmt($d['votingEndsAt'] ?? null) }}@break
                                        @case('funding')@case('agreement_pending'){{ __('funded') }} {{ Money::format((int)($d['fundedAmount'] ?? 0), $c) }}@break
                                        @case('active')@case('overdue')@case('repaid'){{ __('outstanding') }} {{ Money::format((int)($d['outstandingAmount'] ?? 0), $c) }}@break
                                        @default{{ $d['reason'] ?? '' }}
                                    @endswitch
                                </td>
                                <td>{{ $d['status'] ?? '' }} @if ($stuck)<span class="finance-badge-no">{{ __('stuck') }}</span>@endif</td>
                                <td><a class="btn btn-sm btn-primary" href="{{ route('finance.loans.show', $r['id']) }}">{{ __('Open') }}</a></td>
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
<script>$(function () { $('#financeLoansTable').DataTable({ pageLength: 25, order: [], language: typeof datatableLang !== 'undefined' ? datatableLang : {} }); });</script>
@endsection
