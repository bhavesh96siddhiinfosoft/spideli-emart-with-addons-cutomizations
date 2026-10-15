@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $fmt = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i') : ''; };
    $tabs = ['withdrawals' => __('Wallet withdrawals'), 'transfers' => __('External transfers'), 'bills' => __('Bill payments'), 'receipts' => __('Gateway receipts')];
    $collection = ['withdrawals' => 'wallet_operations', 'transfers' => 'money_transfers', 'bills' => 'utility_payment_requests', 'receipts' => 'payment_receipts'][$kind];
    $userField = $kind === 'transfers' ? 'senderId' : 'userId';
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Payouts and receipts'), 'tab' => 'payments'])
    <div class="container-fluid">
        <div class="payment-top-tab mb-3">
            <ul class="nav nav-tabs card-header-tabs align-items-end">
                @foreach ($tabs as $k => $label)
                    <li class="nav-item"><a class="nav-link {{ $kind === $k ? 'active' : '' }}" href="{{ route('finance.payments', ['kind' => $k]) }}">{{ $label }}
                        @if (!empty($counts[$k]))<span class="badge badge-warning ml-1">{{ $counts[$k] }}</span>@endif</a></li>
                @endforeach
            </ul>
        </div>
        <div class="card border mb-4">
            <div class="card-header border-0">
                <h3 class="text-dark-2 mb-1 h4">{{ $tabs[$kind] }}</h3>
                <p class="mb-0 text-dark-2">
                    @switch($kind)
                        @case('withdrawals'){{ __('The money left the wallet when the customer asked. Pay the destination and complete, or refund it (the withdrawal is reversed).') }}@break
                        @case('transfers'){{ __('Transfers to mobile money, Wave or a bank. Start one (the sender can no longer cancel), pay the recipient, then complete it - or refund it.') }}@break
                        @case('bills'){{ __('Pay the biller, then complete with the biller reference (and the code or token, if any) - or refund it.') }}@break
                        @case('receipts'){{ __('Each payment a customer made through a gateway. Check it with the provider: if it matches, mark it checked; if the provider did not receive it, the deposit is reversed.') }}@break
                    @endswitch
                </p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead><tr>
                            <th>{{ __('Date') }}</th><th>{{ __('Reference') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Amount') }}</th>
                            <th>{{ __('Details') }}</th><th>{{ __('Status') }}</th><th></th>
                        </tr></thead>
                        <tbody>
                        @forelse ($rows as $r)
                            @php
                                $d = $r['data'];
                                $amount = $kind === 'withdrawals' ? ($d['amount'] ?? 0) : ($kind === 'receipts' ? ($d['amount'] ?? 0) : ($d['amount'] ?? 0));
                            @endphp
                            <tr>
                                <td>{{ $fmt($d['createdAt'] ?? null) }}</td>
                                <td class="finance-mono">{{ $d['reference'] ?? $r['id'] }}</td>
                                <td>{{ $names[$d[$userField] ?? ''] ?? ($d[$userField] ?? '') }}</td>
                                <td class="finance-amount">{{ Money::format((int)$amount, $d['currency'] ?? '') }}</td>
                                <td>
                                    @switch($kind)
                                        @case('withdrawals'){{ $d['destination']['method'] ?? '' }} {{ $d['destination']['operator'] ?? ($d['destination']['bankName'] ?? '') }}@break
                                        @case('transfers'){{ $d['channel'] ?? '' }} → {{ $d['recipientName'] ?? '' }}
                                            @if (!empty($d['cancelRequested']))<span class="finance-badge-no">{{ __('cancel requested') }}</span>@endif
                                            @if (!empty($d['adminLockedAt']))<span class="finance-badge-ok">{{ __('started') }}</span>@endif
                                            @break
                                        @case('bills'){{ $d['billerName'] ?? '' }} · {{ $d['account'] ?? '' }}@break
                                        @case('receipts'){{ $d['method'] ?? '' }}@break
                                    @endswitch
                                </td>
                                <td>{{ $kind === 'receipts' ? __('to check') : ($d['status'] ?? '') }}
                                    @if (isset($locks[$collection . '/' . $r['id']]))<div class="finance-help">{{ __('with') }} {{ $locks[$collection . '/' . $r['id']] }}</div>@endif</td>
                                <td><a class="btn btn-sm btn-primary" href="{{ route('finance.payments.show', [$kind, $r['id']]) }}">{{ __('Open') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-muted">{{ __('Nothing waiting.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
