@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $fmt = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i') : ''; };
    $cur = $g['currency'] ?? '';
    $saved = (int)($g['savedAmount'] ?? 0);
    $status = $g['status'] ?? '';
    $requested = array_values(array_filter($withdrawals, function ($w) { return ($w['data']['status'] ?? '') === 'requested'; }));
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => $g['name'] ?? __('Savings goal'), 'tab' => 'savings'])
    <div class="container-fluid">
        @if ($lockedBy)<div class="alert alert-warning">{{ __('Another admin is working on this goal:') }} {{ $lockedBy }}</div>@endif
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">

            <fieldset>
                <legend><i class="mr-3 mdi mdi-piggy-bank"></i>{{ __('Savings goal') }}</legend>
                @foreach ([
                    __('Name') => ($g['name'] ?? '') . (!empty($g['demo']) ? ' (demo)' : ''),
                    __('Status') => $status,
                    __('Saved') => Money::format($saved, $cur),
                    __('Target') => Money::format((int)($g['targetAmount'] ?? 0), $cur),
                    __('Owner') => $names[$g['ownerId'] ?? ''] ?? '',
                    __('Deadline') => $fmt($g['deadline'] ?? null),
                    __('Schedule') => !empty($g['schedule']) ? Money::format((int)($g['schedule']['amount'] ?? 0), $cur) . ' · ' . ($g['schedule']['frequency'] ?? '') : '—',
                    __('Purpose') => $g['purpose'] ?? '',
                ] as $label => $value)
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ $label }}</label><div class="col-7"><span class="form-control-plaintext">{{ $value !== '' ? $value : '—' }}</span></div></div>
                @endforeach
                <div class="form-group row width-100"><div class="col-7">
                    @if ($balance === null)
                        <div class="alert alert-warning mb-0">{{ __('The goal\'s pool account (goal_…) does not exist.') }}</div>
                    @elseif ($balance !== $saved)
                        <div class="alert alert-danger mb-0">{{ __('The pool account holds') }} {{ Money::format($balance, $cur) }} {{ __('but the goal says') }} {{ Money::format($saved, $cur) }}. {{ __('They must match: check the ledger.') }}</div>
                    @else
                        <div class="alert alert-success mb-0">{{ __('The pool account matches the saved amount:') }} {{ Money::format($balance, $cur) }}.</div>
                    @endif
                </div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-account-group"></i>{{ __('Members and their shares') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered mb-0">
                        <thead><tr><th>{{ __('Member') }}</th><th>{{ __('Contributed') }}</th><th>{{ __('Withdrawn') }}</th><th>{{ __('Own share') }}</th>
                            @if (in_array($status, ['cancellation_requested', 'active'], true))<th>{{ __('Gets if paid out now') }}</th>@endif</tr></thead>
                        <tbody>
                        @foreach ($shares as $uid => $s)
                            <tr>
                                <td>{{ $names[$uid] ?? $uid }} @if ($uid === ($g['ownerId'] ?? ''))<span class="finance-help">({{ __('owner') }})</span>@endif</td>
                                <td class="finance-amount">{{ Money::format($s['in'], $cur) }}</td>
                                <td class="finance-amount">{{ Money::format($s['out'], $cur) }}</td>
                                <td class="finance-amount">{{ Money::format($s['share'], $cur) }}</td>
                                @if (in_array($status, ['cancellation_requested', 'active'], true))<td class="finance-amount">{{ Money::format((int)($preview[$uid] ?? 0), $cur) }}</td>@endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @if (!empty($g['invitedIds']))<div class="finance-help mt-1">{{ __('Invited, not joined:') }} {{ implode(', ', array_map(function ($u) use ($names) { return $names[$u] ?? $u; }, (array)$g['invitedIds'])) }}</div>@endif
                </div></div>
            </fieldset>

            @if ($canManage && $requested)
            <fieldset>
                <legend><i class="mr-3 mdi mdi-cash-minus"></i>{{ __('Withdrawal requests') }}</legend>
                @foreach ($requested as $w)
                    @php $d = $w['data']; @endphp
                    <div class="form-group row width-100"><div class="col-7">
                        <div class="mb-2"><strong>{{ $names[$d['userId'] ?? ''] ?? '' }}</strong> {{ __('asks') }} <span class="finance-amount">{{ Money::format((int)($d['amount'] ?? 0), $cur) }}</span>
                            · {{ __('own share') }} <span class="finance-amount">{{ Money::format($shares[$d['userId'] ?? '']['share'] ?? 0, $cur) }}</span>
                            @if (!empty($d['reason']))<span class="finance-help">· {{ $d['reason'] }}</span>@endif</div>
                        <form method="POST" action="{{ route('finance.savings.action', [$id, 'pay-withdrawal']) }}" class="d-inline" onsubmit="return confirm('{{ __('Pay this withdrawal to the member\'s wallet?') }}')">@csrf
                            <input type="hidden" name="withdrawalId" value="{{ $w['id'] }}"><button class="btn btn-success btn-sm">{{ __('Pay to their wallet') }}</button></form>
                        <form method="POST" action="{{ route('finance.savings.action', [$id, 'refuse-withdrawal']) }}" class="d-inline-flex ml-2">@csrf
                            <input type="hidden" name="withdrawalId" value="{{ $w['id'] }}">
                            <input type="text" name="reason" class="form-control form-control-sm mr-1" placeholder="{{ __('Reason for the member') }}" required>
                            <button class="btn btn-outline-danger btn-sm">{{ __('Refuse') }}</button></form>
                    </div></div>
                @endforeach
            </fieldset>
            @endif

            @if ($canManage && in_array($status, ['cancellation_requested', 'active'], true))
            <fieldset>
                <legend><i class="mr-3 mdi mdi-gavel"></i>{{ __('Decision') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    @if ($status === 'cancellation_requested')
                        <p>{{ __('The group asked to cancel this goal. Cancelling pays every member their share (the column "Gets if paid out now"), the pool ends at 0 and the goal is closed.') }}</p>
                        <form method="POST" action="{{ route('finance.savings.action', [$id, 'cancel']) }}" class="d-inline" onsubmit="return confirm('{{ __('Cancel the goal and pay every member?') }}')">@csrf
                            <button class="btn btn-danger">{{ __('Cancel and pay the members') }}</button></form>
                        <form method="POST" action="{{ route('finance.savings.action', [$id, 'keep']) }}" class="d-inline ml-2">@csrf
                            <button class="btn btn-primary">{{ __('Keep the goal') }}</button></form>
                    @elseif ($saved >= (int)($g['targetAmount'] ?? 0) && (int)($g['targetAmount'] ?? 0) > 0)
                        <p>{{ __('The target is reached. The goal stays active so members can still withdraw; when the group is done, pay the whole pool out and close it as completed.') }}</p>
                        <form method="POST" action="{{ route('finance.savings.action', [$id, 'complete']) }}" onsubmit="return confirm('{{ __('Pay the whole pool to the members and complete the goal?') }}')">@csrf
                            <button class="btn btn-success">{{ __('Pay out and complete') }}</button></form>
                    @else
                        <p class="finance-help mb-0">{{ __('Nothing to decide: the goal is active and its target is not reached.') }}</p>
                    @endif
                </div></div>
            </fieldset>
            @endif

            @foreach ([['title' => __('Contributions'), 'rows' => $contributions, 'icon' => 'mdi-cash-plus'], ['title' => __('Withdrawals'), 'rows' => $withdrawals, 'icon' => 'mdi-cash-minus']] as $block)
            <fieldset>
                <legend><i class="mr-3 mdi {{ $block['icon'] }}"></i>{{ $block['title'] }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Member') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th><th>{{ __('Reference') }}</th></tr></thead>
                        <tbody>
                        @forelse ($block['rows'] as $r)
                            @php $d = $r['data']; @endphp
                            <tr><td>{{ $fmt($d['createdAt'] ?? null) }}</td><td>{{ $names[$d['userId'] ?? ''] ?? ($d['userId'] ?? '') }}</td>
                                <td class="finance-amount">{{ Money::format((int)($d['amount'] ?? 0), $cur) }}</td>
                                <td>{{ $d['status'] ?? '' }}@if (!empty($d['rejectionReason']))<div class="finance-help">{{ $d['rejectionReason'] }}</div>@endif</td>
                                <td class="finance-mono">@if (!empty($d['txId']))<a href="{{ route('finance.ledger.show', $d['txId']) }}">{{ $d['reference'] ?? $d['txId'] }}</a>@else{{ $d['reference'] ?? '' }}@endif</td></tr>
                        @empty
                            <tr><td colspan="5" class="text-muted">{{ __('Nothing yet.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div></div>
            </fieldset>
            @endforeach

        </div></div>
        <div class="form-group col-12 text-center btm-btn">
            <a href="{{ route('finance.savings') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ __('Back') }}</a>
        </div>
        </div></div>
    </div>
</div>
@endsection
