@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $fmt = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i') : ''; };
    $day = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d') : ''; };
    $cur = $l['currency'] ?? '';
    $status = $l['status'] ?? '';
    $eligible = count((array)($l['eligibleVoterIds'] ?? []));
    $yes = (int)($l['votes']['yes'] ?? 0); $no = (int)($l['votes']['no'] ?? 0); $cast = $yes + $no;
    $a = $assessment ?: $suggestion;
    $owedTotal = array_sum(array_filter($owed, function ($v) { return $v > 0; }));
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Member loan'), 'tab' => 'loans'])
    <div class="container-fluid">
        @if ($lockedBy)<div class="alert alert-warning">{{ __('Another admin is working on this loan:') }} {{ $lockedBy }}</div>@endif
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">

            <fieldset>
                <legend><i class="mr-3 mdi mdi-hand-coin"></i>{{ __('Loan') }}</legend>
                @foreach ([
                    __('Borrower') => ($names[$l['borrowerId'] ?? ''] ?? '') . (!empty($l['demo']) ? ' (demo)' : ''),
                    __('Status') => $status . ($stuck ? ' · ' . __('stuck') : ''),
                    __('Group') => $l['groupName'] ?? '',
                    __('Amount') => Money::format((int)($l['amount'] ?? 0), $cur),
                    __('Duration') => ($l['durationMonths'] ?? '') . ' ' . __('months') . ' · ' . ($l['repaymentFrequency'] ?? ''),
                    __('Purpose') => $l['purpose'] ?? '',
                    __('Funded') => Money::format((int)($l['fundedAmount'] ?? 0), $cur),
                    __('Outstanding') => Money::format((int)($l['outstandingAmount'] ?? 0), $cur) . ' · ' . __('repaid') . ' ' . Money::format((int)($l['repaidAmount'] ?? 0), $cur),
                    __('Loan account') => $account ? Money::format((int)($account['balance'] ?? 0), $cur) : '—',
                    __('Rules from') => $lendingFrom,
                ] as $label => $value)
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ $label }}</label><div class="col-7"><span class="form-control-plaintext">{{ $value !== '' ? $value : '—' }}</span></div></div>
                @endforeach
                @if (!empty($l['description']))<div class="form-group row width-100"><label class="col-3 control-label">{{ __('Description') }}</label><div class="col-7"><span class="form-control-plaintext">{{ $l['description'] }}</span></div></div>@endif
                @if (!empty($l['reason']))<div class="form-group row width-100"><label class="col-3 control-label">{{ __('Reason') }}</label><div class="col-7"><span class="form-control-plaintext">{{ $l['reason'] }}</span></div></div>@endif
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-chart-line"></i>{{ __('Credit assessment') }}</legend>
                <div class="form-group row width-100"><div class="col-7 finance-help">
                    {{ $assessment ? __('Saved assessment (the borrower sees it; voters never do).') : __('Not saved yet: the values below are the panel\'s suggestion from the borrower\'s history. Check and edit, then save.') }}
                </div></div>
                <form method="POST" action="{{ route('finance.loans.action', [$id, 'assessment']) }}">@csrf
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Score (300 - 850)') }}</label><div class="col-7"><input type="number" min="300" max="850" name="score" class="form-control" value="{{ old('score', $a['score'] ?? '') }}" {{ $canManage ? '' : 'disabled' }}></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Band') }}</label><div class="col-7">
                        <select name="band" class="form-control" {{ $canManage ? '' : 'disabled' }}>@foreach (['Excellent', 'Good', 'Fair', 'Poor'] as $b)<option {{ ($a['band'] ?? '') === $b ? 'selected' : '' }}>{{ $b }}</option>@endforeach</select></div></div>
                    <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Factors the borrower sees') }}</label><div class="col-7">
                        <table class="table table-bordered table-sm mb-0">
                            <thead><tr><th>{{ __('Factor') }}</th><th style="width:130px">{{ __('Impact') }}</th><th>{{ __('Detail') }}</th></tr></thead>
                            <tbody>
                            @foreach (array_merge((array)($a['factors'] ?? []), [[], []]) as $i => $f)
                                <tr>
                                    <td><input type="text" name="factors[{{ $i }}][label]" class="form-control form-control-sm" value="{{ $f['label'] ?? '' }}" {{ $canManage ? '' : 'disabled' }}></td>
                                    <td><select name="factors[{{ $i }}][impact]" class="form-control form-control-sm" {{ $canManage ? '' : 'disabled' }}>
                                        <option value="positive" {{ ($f['impact'] ?? '') === 'positive' ? 'selected' : '' }}>{{ __('positive') }}</option>
                                        <option value="negative" {{ ($f['impact'] ?? '') === 'negative' ? 'selected' : '' }}>{{ __('negative') }}</option></select></td>
                                    <td><input type="text" name="factors[{{ $i }}][detail]" class="form-control form-control-sm" value="{{ $f['detail'] ?? '' }}" {{ $canManage ? '' : 'disabled' }}></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="finance-help">{{ __('Plain words: the borrower reads these. A row with no factor is ignored.') }}</div>
                    </div></div>
                    @if ($canManage)<div class="form-group row width-100"><div class="col-7"><button class="btn btn-primary">{{ __('Save the assessment') }}</button></div></div>@endif
                </form>
            </fieldset>

            @if (in_array($status, ['voting', 'funding', 'agreement_pending', 'active', 'overdue', 'repaid', 'rejected'], true) && $eligible)
            <fieldset>
                <legend><i class="mr-3 mdi mdi-vote"></i>{{ __('Group vote') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <p class="mb-1">{{ __('Votes cast') }}: <strong>{{ $cast }} / {{ $eligible }}</strong> · {{ __('approve') }} {{ $yes }} · {{ __('decline') }} {{ $no }}
                        · {{ __('needed') }}: {{ __('quorum') }} {{ $lending['voting']['quorumPercent'] ?? '—' }}%, {{ __('approval') }} {{ $lending['voting']['approvalPercent'] ?? '—' }}%</p>
                    <p class="mb-0">{{ __('Ends') }}: {{ $fmt($l['votingEndsAt'] ?? null) }} @if (!empty($l['votingClosedAt']))· {{ __('closed') }} {{ $fmt($l['votingClosedAt']) }}@endif
                        @if ($status === 'voting')· <strong>{{ $passes ? __('would pass now') : __('would not pass now') }}</strong>@endif</p>
                    <div class="finance-help">{{ __('Voters') }}: {{ implode(', ', array_map(function ($v) use ($names) { return $names[$v] ?? $v; }, (array)($l['eligibleVoterIds'] ?? []))) }}</div>
                </div></div>
            </fieldset>
            @endif

            @if ($canManage)
            <fieldset>
                <legend><i class="mr-3 mdi mdi-gavel"></i>{{ __('Decision') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    @if ($status === 'submitted')
                        <form method="POST" action="{{ route('finance.loans.action', [$id, 'review']) }}" class="d-inline">@csrf<button class="btn btn-primary">{{ __('Start review') }}</button></form>
                    @elseif ($status === 'under_review')
                        <form method="POST" action="{{ route('finance.loans.action', [$id, 'open-vote']) }}" class="d-inline" onsubmit="return confirm('{{ __('Open the vote? The group members are notified.') }}')">@csrf
                            <button class="btn btn-success">{{ __('Open the vote') }}</button>
                            <span class="finance-help ml-2">{{ __('for') }} {{ $lending['voting']['durationHours'] ?? '?' }} {{ __('hours') }}</span></form>
                    @elseif ($status === 'voting' && $votingOver)
                        <form method="POST" action="{{ route('finance.loans.action', [$id, 'close-vote']) }}" class="d-inline">@csrf<button class="btn btn-primary">{{ __('Close the vote now') }}</button></form>
                    @endif

                    @if (in_array($status, ['submitted', 'under_review', 'voting'], true))
                        <form method="POST" action="{{ route('finance.loans.action', [$id, 'reject']) }}" class="mt-3" onsubmit="return confirm('{{ __('Reject this loan request?') }}')">@csrf
                            <input type="text" name="reason" class="form-control mb-1" placeholder="{{ __('Reason for the borrower') }}" required>
                            <button class="btn btn-outline-danger">{{ __('Reject') }}</button></form>
                    @endif

                    @if (in_array($status, ['funding', 'agreement_pending'], true))
                        <p class="mb-1">{{ $stuck ? __('This loan is stuck.') : __('Refund only if the loan cannot go on (the group did not fund it, or the borrower never accepted).') }}
                            {{ __('Refunding returns every lender\'s money from the loan account and cancels the loan.') }}</p>
                        <form method="POST" action="{{ route('finance.loans.action', [$id, 'refund']) }}" onsubmit="return confirm('{{ __('Refund every lender and cancel the loan?') }}')">@csrf
                            <input type="text" name="reason" class="form-control mb-1" placeholder="{{ __('Reason (the borrower and the lenders see it)') }}" required>
                            <button class="btn btn-danger">{{ __('Refund the lenders and cancel') }}</button></form>
                    @endif

                    @if (in_array($status, ['active', 'overdue', 'repaid'], true))
                        <p class="mb-1">{{ __('Owed to the lenders now') }}: <strong class="finance-amount">{{ Money::format($owedTotal, $cur) }}</strong> <span class="finance-help">{{ __('(lenders can also collect themselves in the app)') }}</span></p>
                        @if ($owedTotal > 0)
                            <form method="POST" action="{{ route('finance.loans.action', [$id, 'pay-lenders']) }}" class="d-inline" onsubmit="return confirm('{{ __('Pay every lender their share now?') }}')">@csrf<button class="btn btn-success">{{ __('Pay the lenders') }}</button></form>
                        @elseif ($status === 'repaid' && $account && (int)($account['balance'] ?? 0) > 0)
                            <form method="POST" action="{{ route('finance.loans.action', [$id, 'sweep']) }}" class="d-inline">@csrf<button class="btn btn-primary">{{ __('Move the rounding left') }} ({{ Money::format((int)$account['balance'], $cur) }}) {{ __('to the platform') }}</button></form>
                        @endif
                    @endif

                    @if (in_array($status, ['active', 'overdue'], true))
                        <form method="POST" action="{{ route('finance.loans.action', [$id, 'default']) }}" class="mt-3" onsubmit="return confirm('{{ __('Mark this loan defaulted? The borrower is restricted from loans.') }}')">@csrf
                            <input type="text" name="reason" class="form-control mb-1" placeholder="{{ __('After a documented review: why') }}" required>
                            <button class="btn btn-outline-danger">{{ __('Mark defaulted') }}</button></form>
                    @endif
                </div></div>
            </fieldset>
            @endif

            @if ($fundings)
            <fieldset>
                <legend><i class="mr-3 mdi mdi-account-cash"></i>{{ __('Lenders') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>{{ __('Lender') }}</th><th>{{ __('Lent') }}</th><th>{{ __('Collected') }}</th><th>{{ __('Owed now') }}</th><th>{{ __('Status') }}</th></tr></thead>
                        <tbody>
                        @foreach ($fundings as $f)
                            @php $d = $f['data']; @endphp
                            <tr><td>{{ $names[$d['lenderId'] ?? ''] ?? '' }}</td><td class="finance-amount">{{ Money::format((int)($d['amount'] ?? 0), $cur) }}</td>
                                <td class="finance-amount">{{ Money::format((int)($d['collected'] ?? 0), $cur) }}</td>
                                <td class="finance-amount">{{ Money::format(max(0, (int)($owed[$f['id']] ?? 0)), $cur) }}</td><td>{{ $d['status'] ?? '' }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div></div>
            </fieldset>
            @endif

            @if ($schedule)
            <fieldset>
                <legend><i class="mr-3 mdi mdi-calendar-clock"></i>{{ __('Repayment schedule') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>#</th><th>{{ __('Due') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Penalty') }}</th><th>{{ __('Due now') }}</th><th>{{ __('Paid') }}</th><th>{{ __('Status') }}</th></tr></thead>
                        <tbody>
                        @foreach ((array)($schedule['data']['installments'] ?? []) as $it)
                            <tr class="{{ ($it['status'] ?? '') === 'overdue' ? 'table-danger' : '' }}">
                                <td>{{ $it['number'] ?? '' }}</td><td>{{ $day($it['dueDate'] ?? null) }}</td>
                                <td class="finance-amount">{{ Money::format((int)($it['amount'] ?? 0), $cur) }}</td>
                                <td class="finance-amount">{{ Money::format((int)($it['penalty'] ?? 0), $cur) }}</td>
                                <td class="finance-amount">{{ Money::format((int)($it['amountDue'] ?? 0), $cur) }}</td>
                                <td class="finance-amount">{{ Money::format((int)($it['paidAmount'] ?? 0), $cur) }}</td>
                                <td>{{ $it['status'] ?? '' }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="finance-help mt-1">{{ __('Late penalty') }}: {{ $lending['latePenaltyPercent'] ?? 0 }}% {{ __('of what is unpaid, once per instalment, after') }} {{ config('finance.loan_grace_days', 3) }} {{ __('days of grace.') }}</div>
                </div></div>
            </fieldset>
            @endif

            @if ($repayments)
            <fieldset>
                <legend><i class="mr-3 mdi mdi-cash-refund"></i>{{ __('Repayments') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Reference') }}</th></tr></thead>
                        <tbody>
                        @foreach ($repayments as $r)
                            <tr><td>{{ $fmt($r['data']['createdAt'] ?? null) }}</td><td class="finance-amount">{{ Money::format((int)($r['data']['amount'] ?? 0), $cur) }}</td>
                                <td class="finance-mono">@if (!empty($r['data']['txId']))<a href="{{ route('finance.ledger.show', $r['data']['txId']) }}">{{ $r['data']['reference'] ?? '' }}</a>@else{{ $r['data']['reference'] ?? '' }}@endif</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div></div>
            </fieldset>
            @endif

        </div></div>
        <div class="form-group col-12 text-center btm-btn">
            <a href="{{ route('finance.loans') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ __('Back') }}</a>
        </div>
        </div></div>
    </div>
</div>
@endsection
