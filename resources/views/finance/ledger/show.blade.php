@extends('layouts.app')
@section('content')
@php use App\Finance\Money; use App\Finance\Value; $cur = $d['currency'] ?? ''; @endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Posting') . ' ' . ($d['reference'] ?? $tx['id']), 'tab' => 'ledger'])
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-7">
                <div class="card border mb-4">
                    <div class="card-header border-0 d-flex justify-content-between">
                        <h3 class="text-dark-2 mb-0 h4">{{ $d['type'] ?? '' }} <span class="finance-mono text-muted">{{ $d['reference'] ?? '' }}</span></h3>
                        <a href="{{ route('finance.ledger') }}" class="btn btn-light">{{ __('Back') }}</a>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr><th style="width:30%">{{ __('Id') }}</th><td class="finance-mono">{{ $tx['id'] }}</td></tr>
                            <tr><th>{{ __('Status') }}</th><td>{{ $d['status'] ?? '' }} @if (!empty($d['demo']))<span class="badge badge-secondary">demo</span>@endif</td></tr>
                            <tr><th>{{ __('Date') }}</th><td class="finance-mono">{{ ($d['createdAt'] ?? null) instanceof \DateTimeInterface ? $d['createdAt']->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i:s') : '' }}</td></tr>
                            <tr><th>{{ __('Currency') }}</th><td class="finance-mono">{{ $cur }}</td></tr>
                            <tr><th>{{ __('Customers') }}</th><td>@foreach ($d['userIds'] ?? [] as $u)<div><span class="finance-mono">{{ $u }}</span> {{ $users[$u] ?? '' }}</div>@endforeach</td></tr>
                            <tr><th>{{ __('Source') }}</th><td class="finance-mono">{{ isset($d['source']) ? ($d['source']['kind'] ?? '') . '/' . ($d['source']['id'] ?? '') : '' }}</td></tr>
                            <tr><th>{{ __('Funding') }}</th><td>{{ $d['funding'] ?? '' }} @if (!empty($d['gateway']))<span class="finance-mono">{{ ($d['gateway']['method'] ?? '') . ' ' . ($d['gateway']['reference'] ?? '') }}</span>@endif</td></tr>
                            <tr><th>{{ __('Description') }}</th><td>{{ $d['description'] ?? '' }}</td></tr>
                            <tr><th>{{ __('Created by') }}</th><td class="finance-mono">{{ $d['createdBy'] ?? '' }}</td></tr>
                            @if (!empty($d['reversalOf']))
                                <tr><th>{{ __('Reverses') }}</th><td><a class="finance-mono" href="{{ route('finance.ledger.show', $d['reversalOf']) }}">{{ $d['reversalOf'] }}</a></td></tr>
                            @endif
                            @if (!empty($d['reversedBy']))
                                <tr><th>{{ __('Reversed by') }}</th><td>@foreach ($d['reversedBy'] as $r)<div><a class="finance-mono" href="{{ route('finance.ledger.show', $r) }}">{{ $r }}</a></div>@endforeach
                                    <span class="finance-mono">{{ ($d['reversedAt'] ?? null) instanceof \DateTimeInterface ? $d['reversedAt']->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i') : '' }}</span></td></tr>
                            @endif
                        </table>

                        <h4 class="h5 mt-4">{{ __('Lines') }}</h4>
                        <table class="table table-bordered">
                            <thead><tr><th>{{ __('Account') }}</th><th>{{ __('Debit') }}</th><th>{{ __('Credit') }}</th><th>{{ __('Balance now') }}</th></tr></thead>
                            <tbody>
                            @foreach ($d['lines'] ?? [] as $l)
                                @php $acc = $accounts[$l['accountId'] ?? ''] ?? null; @endphp
                                <tr>
                                    <td class="finance-mono">{{ $l['accountId'] ?? '' }} @if (!$acc)<span class="text-danger">({{ __('missing') }})</span>@endif</td>
                                    <td class="finance-amount">{{ ($l['direction'] ?? '') === 'debit' ? Money::format((int)$l['amount'], $cur) : '' }}</td>
                                    <td class="finance-amount">{{ ($l['direction'] ?? '') === 'credit' ? Money::format((int)$l['amount'], $cur) : '' }}</td>
                                    <td class="finance-amount">{{ $acc ? Money::format((int)($acc['balance'] ?? 0), $acc['currency'] ?? $cur) : '' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border mb-4">
                    <div class="card-header border-0"><h3 class="text-dark-2 mb-0 h4">{{ __('Reverse') }}</h3></div>
                    <div class="card-body">
                        @if (($d['status'] ?? '') === 'reversed')
                            <p class="mb-0">{{ __('Already reversed.') }}</p>
                        @elseif (($d['type'] ?? '') === 'reversal')
                            <p class="mb-0">{{ __('A reversal cannot be reversed. Make a manual adjustment instead.') }}</p>
                        @elseif (!$wellFormed)
                            <p class="mb-0">{{ __('This record is not a well-formed posting (2 or 3 balanced lines) and cannot be reversed.') }}</p>
                        @elseif ($pendingReversal)
                            <p class="mb-0">{{ __('A reversal of this posting is waiting for a second admin.') }} <a href="{{ route('finance.approvals') }}">{{ __('Approvals') }}</a></p>
                        @elseif (!$canReverse)
                            <p class="mb-0 text-muted">{{ __('Your role cannot reverse postings.') }}</p>
                        @else
                            <p>{{ __('Gives the money back: the credited account pays the debited one, and a fee comes back from the platform account. The credited account must still hold the money.') }}</p>
                            @if ($needsApproval)
                                <div class="alert alert-info">{{ __('This amount needs a second admin: your request will wait in Approvals.') }}</div>
                            @endif
                            <form method="POST" action="{{ route('finance.ledger.reverse', $tx['id']) }}" onsubmit="return confirm('{{ __('Reverse this posting?') }}')">
                                @csrf
                                <div class="form-group">
                                    <label>{{ __('Reason') }} *</label>
                                    <textarea name="reason" class="form-control" rows="3" required></textarea>
                                </div>
                                <button class="btn btn-danger">{{ $needsApproval ? __('Ask for reversal') : __('Reverse') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
