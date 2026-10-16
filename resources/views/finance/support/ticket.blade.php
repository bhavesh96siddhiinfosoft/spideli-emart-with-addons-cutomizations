@extends('layouts.app')
@section('content')
@php
    use App\Finance\Support;
    $tz = new DateTimeZone(config('app.timezone'));
    $fmt = function ($v) use ($tz) { return $v instanceof \DateTimeInterface ? $v->setTimezone($tz)->format('Y-m-d H:i') : ''; };
    $status = $t['status'] ?? '';
    $open = in_array($status, ['open', 'in_progress'], true);
    $uid = $t['userId'] ?? '';
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Ticket') . ': ' . ($t['subject'] ?? ''), 'tab' => 'tickets'])
    <div class="container-fluid">
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
            <fieldset>
                <legend><i class="mr-3 mdi mdi-lifebuoy"></i>{{ __('Ticket') }}</legend>
                @foreach ([
                    __('Subject') => $t['subject'] ?? '',
                    __('Status') => $status,
                    __('Opened') => $fmt($t['createdAt'] ?? null) . ' · ' . __('from') . ' ' . ($t['source'] ?? ''),
                    __('Time to resolve') => $sla ? ($sla['met'] !== null ? ($sla['met'] ? __('met') : __('late')) : ($sla['late'] ? __('late since') : __('due by')) . ' ' . $fmt($sla['due'])) : '',
                    __('Related reference') => $t['relatedRef'] ?? '',
                    __('Resolution') => $t['resolution'] ?? '',
                ] as $label => $value)
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ $label }}</label><div class="col-7"><span class="form-control-plaintext">{{ $value !== '' ? $value : '—' }}</span></div></div>
                @endforeach
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Customer') }}</label><div class="col-7"><span class="form-control-plaintext">
                    <a href="{{ route('finance.customers.show', $uid) }}">{{ $names[$uid] ?? $uid }}</a> · {{ $customer['data']['email'] ?? '' }} · <span class="finance-amount">{{ $customer['data']['phoneNumber'] ?? '' }}</span></span></div></div>
                @if ($canManage)
                <form method="POST" action="{{ route('finance.tickets.action', [$id, 'update']) }}" class="form-group row width-100">@csrf
                    <div class="col-7 d-flex">
                        <select name="category" class="form-control mr-2">@foreach (Support::CATEGORIES as $c)<option value="{{ $c }}" {{ ($t['category'] ?? '') === $c ? 'selected' : '' }}>{{ __(ucfirst($c)) }}</option>@endforeach</select>
                        <select name="priority" class="form-control mr-2">@foreach (Support::PRIORITIES as $p)<option value="{{ $p }}" {{ ($t['priority'] ?? '') === $p ? 'selected' : '' }}>{{ __(\App\Finance\Support::priorityLabel($p)) }}</option>@endforeach</select>
                        <select name="assignedTo" class="form-control mr-2"><option value="">{{ __('Nobody') }}</option>@foreach ($admins as $aid => $email)<option value="{{ $aid }}" {{ ($t['assignedTo'] ?? '') === $aid ? 'selected' : '' }}>{{ $email }}</option>@endforeach</select>
                        <button class="btn btn-outline-primary text-nowrap">{{ __('Save') }}</button>
                    </div>
                </form>
                @else
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Assigned to') }}</label><div class="col-7"><span class="form-control-plaintext">{{ $t['assignedToEmail'] ?? '—' }}</span></div></div>
                @endif
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-forum"></i>{{ __('Messages (staff only)') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    @foreach ($messages as $m)
                        @php $d = $m['data']; $cust = ($d['authorType'] ?? '') === 'customer'; @endphp
                        <div class="mb-3 p-2" style="border-left:3px solid {{ $cust ? '#f0ad4e' : '#2a9d8f' }}">
                            <strong>{{ $cust ? __('Customer') . ' (' . __('recorded by staff') . ')' : ($d['authorEmail'] ?? $d['authorId'] ?? '') }}</strong>
                            <span class="finance-help">{{ $fmt($d['createdAt'] ?? null) }}</span>
                            <div style="white-space:pre-line">{{ $d['text'] ?? '' }}</div>
                        </div>
                    @endforeach
                    @if ($canManage && $status !== 'closed')
                        <form method="POST" action="{{ route('finance.tickets.action', [$id, 'message']) }}">@csrf
                            <textarea name="text" class="form-control mb-2" rows="3" required></textarea>
                            <div class="d-flex align-items-center">
                                <select name="author" class="form-control mr-2" style="max-width:320px"><option value="admin">{{ __('My note') }}</option><option value="customer">{{ __('What the customer said (chat, phone)') }}</option></select>
                                <button class="btn btn-outline-primary">{{ __('Add') }}</button></div></form>
                    @endif
                </div></div>
            </fieldset>

            @if ($canManage)
            <fieldset>
                <legend><i class="mr-3 mdi mdi-check-circle"></i>{{ __('Status') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    @if ($open)
                        <form method="POST" action="{{ route('finance.tickets.action', [$id, 'resolve']) }}" class="mb-3" onsubmit="return confirm('{{ __('Resolve the ticket and tell the customer?') }}')">@csrf
                            <textarea name="text" class="form-control mb-2" rows="3" placeholder="{{ __('What was done - the customer receives this') }}" required></textarea>
                            <button class="btn btn-success">{{ __('Resolve and tell the customer') }}</button></form>
                    @endif
                    @if ($status !== 'closed')
                        <form method="POST" action="{{ route('finance.tickets.action', [$id, 'close']) }}" class="d-flex mb-2">@csrf
                            <input type="text" name="text" class="form-control mr-2" placeholder="{{ __('Note (optional, not sent)') }}">
                            <button class="btn btn-outline-secondary text-nowrap">{{ __('Close') }}</button></form>
                    @endif
                    @if (in_array($status, ['resolved', 'closed'], true))
                        <form method="POST" action="{{ route('finance.tickets.action', [$id, 'reopen']) }}" class="d-flex">@csrf
                            <input type="text" name="text" class="form-control mr-2" placeholder="{{ __('Why (optional)') }}">
                            <button class="btn btn-outline-primary text-nowrap">{{ __('Reopen') }}</button></form>
                    @endif
                </div></div>
            </fieldset>
            @endif
        </div></div>
        </div></div>
    </div>
</div>
@endsection
