@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $fmt = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i') : ''; };
    $titles = ['withdrawals' => __('Wallet withdrawal'), 'transfers' => __('External transfer'), 'bills' => __('Bill payment'), 'receipts' => __('Gateway receipt')];
    $cur = $d['currency'] ?? '';
    $money = function ($v) use ($cur) { return $v === null || $v === '' ? '—' : Money::format((int)$v, $cur); };
    $rows = [];
    $rows[__('Reference')] = $d['reference'] ?? $id;
    $rows[__('Customer')] = $customer ?: $uid;
    $rows[__('Date')] = $fmt($d['createdAt'] ?? null);
    $rows[__('Status')] = $kind === 'receipts' ? (!empty($d['disputed']) ? __('disputed') : (($d['verified'] ?? false) ? __('checked') : __('to check'))) : ($d['status'] ?? '');
    switch ($kind) {
        case 'withdrawals':
            $dest = $d['destination'] ?? [];
            $rows[__('Pay to the customer')] = $money($d['amount'] ?? null);
            $rows[__('Taken from the wallet')] = $money($d['total'] ?? null) . ' (' . __('fee') . ' ' . $money($d['fee'] ?? 0) . ')';
            $rows[__('Destination')] = trim(($dest['method'] ?? '') . ' · ' . ($dest['operator'] ?? $dest['bankName'] ?? '') . ' · ' . ($dest['number'] ?? $dest['accountNumber'] ?? '') . ' ' . ($dest['holderName'] ?? ''), ' ·');
            break;
        case 'transfers':
            $rec = $d['recipient'] ?? [];
            $rows[__('Pay to the recipient')] = $money($d['receiveAmount'] ?? $d['amount'] ?? null);
            $rows[__('Taken from the sender')] = $money($d['total'] ?? null) . ' (' . __('fee') . ' ' . $money($d['fee'] ?? 0) . ')';
            $rows[__('Recipient')] = trim(($d['recipientName'] ?? '') . ' · ' . ($d['channel'] ?? '') . ' · ' . ($rec['operator'] ?? $rec['bankName'] ?? '') . ' · ' . ($rec['number'] ?? $rec['accountNumber'] ?? ''), ' ·');
            $rows[__('Note')] = $d['note'] ?? '';
            if (!empty($d['cancelRequested'])) { $rows[__('Cancel requested')] = $fmt($d['cancelRequestedAt'] ?? null) ?: __('yes'); }
            if (!empty($d['adminLockedAt'])) { $rows[__('Payout started')] = $fmt($d['adminLockedAt']); }
            break;
        case 'bills':
            $rows[__('Biller')] = ($d['billerName'] ?? '') . ' · ' . ($d['category'] ?? '');
            $rows[__('Account')] = $d['account'] ?? '';
            if (!empty($d['packageName'])) { $rows[__('Package')] = $d['packageName']; }
            $rows[__('Pay to the biller')] = $money($d['amount'] ?? null);
            $rows[__('Taken from the customer')] = $money($d['total'] ?? null);
            break;
        case 'receipts':
            $rows[__('Gateway')] = $d['method'] ?? '';
            $rows[__('Gateway reference')] = $d['reference'] ?? '';
            $rows[__('Charged by the gateway')] = $money($d['amount'] ?? null);
            if (!empty($d['note'])) { $rows[__('Note')] = $d['note']; }
            break;
    }
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => $titles[$kind], 'tab' => 'payments'])
    <div class="container-fluid">
        @if ($lockedBy)<div class="alert alert-warning">{{ __('Another admin is working on this item:') }} {{ $lockedBy }}</div>@endif
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">

            <fieldset>
                <legend><i class="mr-3 mdi mdi-information"></i>{{ $titles[$kind] }}</legend>
                @foreach ($rows as $label => $value)
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ $label }}</label>
                        <div class="col-7"><span class="form-control-plaintext">{{ $value !== '' ? $value : '—' }}</span></div></div>
                @endforeach
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Posting') }}</label>
                    <div class="col-7"><span class="form-control-plaintext">
                        @if ($tx)<a class="finance-mono" href="{{ route('finance.ledger.show', $tx['id']) }}">{{ $tx['data']['reference'] ?? $tx['id'] }}</a> · {{ $tx['data']['status'] ?? '' }}@else—@endif
                    </span></div></div>
            </fieldset>

            @if ($open && $canProcess)
            <fieldset>
                <legend><i class="mr-3 mdi mdi-gavel"></i>{{ __('Decision') }}</legend>

                @if ($kind === 'withdrawals')
                    <form method="POST" action="{{ route('finance.payments.action', [$kind, $id, 'complete']) }}" onsubmit="return confirm('{{ __('Mark as paid?') }}')">@csrf
                        <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Payout reference') }}<span class="required-field"></span></label>
                            <div class="col-7"><input type="text" name="payoutReference" class="form-control" required>
                                <div class="form-text text-muted">{{ __('The reference of the mobile money / bank payment you made.') }}</div></div></div>
                        <div class="form-group row width-50"><div class="col-7 pt-4"><button class="btn btn-success">{{ __('Paid - complete') }}</button></div></div>
                    </form>
                @elseif ($kind === 'transfers')
                    @if (!empty($d['cancelRequested']) && empty($d['adminLockedAt']))
                        <form method="POST" action="{{ route('finance.payments.action', [$kind, $id, 'cancel']) }}" onsubmit="return confirm('{{ __('Cancel and refund the sender?') }}')">@csrf
                            <div class="form-group row width-100"><div class="col-7">
                                <div class="alert alert-warning">{{ __('The sender asked to cancel. Cancel it: the money goes back to their wallet.') }}</div>
                                <button class="btn btn-warning">{{ __('Cancel and refund') }}</button></div></div>
                        </form>
                    @elseif (empty($d['adminLockedAt']))
                        <form method="POST" action="{{ route('finance.payments.action', [$kind, $id, 'start']) }}">@csrf
                            <div class="form-group row width-100"><div class="col-7">
                                <button class="btn btn-primary">{{ __('Start the payout') }}</button>
                                <span class="finance-help ml-2">{{ __('From now on the sender can no longer cancel.') }}</span></div></div>
                        </form>
                    @else
                        <form method="POST" action="{{ route('finance.payments.action', [$kind, $id, 'complete']) }}" onsubmit="return confirm('{{ __('Mark as paid?') }}')">@csrf
                            <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Provider reference') }}<span class="required-field"></span></label>
                                <div class="col-7"><input type="text" name="providerReference" class="form-control" required></div></div>
                            <div class="form-group row width-50"><div class="form-check">
                                <input type="checkbox" id="recipientVerified" name="recipientVerified" value="1">
                                <label class="col-3 control-label" for="recipientVerified">{{ __('The provider confirmed the recipient\'s name') }}</label></div></div>
                            <div class="form-group row width-100"><div class="col-7"><button class="btn btn-success">{{ __('Paid - complete') }}</button></div></div>
                        </form>
                    @endif
                @elseif ($kind === 'bills')
                    <form method="POST" action="{{ route('finance.payments.action', [$kind, $id, 'complete']) }}" onsubmit="return confirm('{{ __('Mark as paid?') }}')">@csrf
                        <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Biller reference') }}<span class="required-field"></span></label><div class="col-7"><input type="text" name="billerReference" class="form-control" required></div></div>
                        <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Customer name at the biller') }}</label><div class="col-7"><input type="text" name="customerName" class="form-control"></div></div>
                        <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Code / token') }}</label><div class="col-7"><input type="text" name="token" class="form-control">
                            <div class="form-text text-muted">{{ __('For prepaid electricity and similar: sent to the customer.') }}</div></div></div>
                        <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Code label') }}</label><div class="col-7"><input type="text" name="tokenLabel" class="form-control" placeholder="{{ __('e.g. Electricity token') }}"></div></div>
                        <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Units') }}</label><div class="col-7"><input type="text" name="units" class="form-control" placeholder="{{ __('e.g. 98.4 kWh') }}"></div></div>
                        <div class="form-group row width-100"><div class="col-7"><button class="btn btn-success">{{ __('Paid - complete') }}</button></div></div>
                    </form>
                @elseif ($kind === 'receipts')
                    <form method="POST" action="{{ route('finance.payments.action', [$kind, $id, 'verify']) }}">@csrf
                        <div class="form-group row width-100"><div class="col-7">
                            <button class="btn btn-success" onclick="return confirm('{{ __('The provider confirms this payment?') }}')">{{ __('Matches the provider - mark checked') }}</button></div></div>
                    </form>
                @endif

                @if (!($kind === 'transfers' && !empty($d['cancelRequested']) && empty($d['adminLockedAt'])))
                    <form method="POST" action="{{ route('finance.payments.action', [$kind, $id, $kind === 'receipts' ? 'dispute' : 'fail']) }}"
                          onsubmit="return confirm('{{ $kind === 'receipts' ? __('Reverse the deposit of this payment?') : __('Refund the customer? The posting is reversed.') }}')">@csrf
                        <div class="form-group row width-50"><label class="col-3 control-label">{{ $kind === 'receipts' ? __('What the provider reported') : __('Reason for the customer') }}<span class="required-field"></span></label>
                            <div class="col-7"><input type="text" name="reason" class="form-control" required></div></div>
                        <div class="form-group row width-50"><div class="col-7 pt-4">
                            <button class="btn btn-danger">{{ $kind === 'receipts' ? __('Not received - reverse') : __('Could not pay - refund') }}</button></div></div>
                    </form>
                @endif
            </fieldset>
            @endif

        </div></div>
        <div class="form-group col-12 text-center btm-btn">
            <a href="{{ route('finance.payments', ['kind' => $kind]) }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ __('Back') }}</a>
        </div>
        </div></div>
    </div>
</div>
@endsection
