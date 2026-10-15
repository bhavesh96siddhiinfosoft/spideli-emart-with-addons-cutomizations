@extends('layouts.app')
@section('content')
@php
    $fmt = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i') : ''; };
    $status = $a['status'] ?? '';
    $part = ($a['accountType'] ?? '') === 'business' ? ($a['business'] ?? []) : ($a['personal'] ?? []);
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('KYC review'), 'tab' => 'kyc'])
    <div class="container-fluid">
        @if ($lockedBy)<div class="alert alert-warning">{{ __('Another admin is working on this application:') }} {{ $lockedBy }}</div>@endif
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">

            <fieldset>
                <legend><i class="mr-3 mdi mdi-account-check"></i>{{ __('Application') }}</legend>
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Customer') }}</label><div class="col-7"><span class="form-control-plaintext">
                    <a href="{{ route('finance.customers.show', $a['userId'] ?? '') }}">{{ trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?: ($a['userId'] ?? '') }}</a>
                    <span class="finance-help">{{ $user['email'] ?? '' }}</span></span></div></div>
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Status') }}</label><div class="col-7"><span class="form-control-plaintext"><strong>{{ $status }}</strong>
                    @if (!empty($a['reason']))<span class="finance-help">· {{ $a['reason'] }}</span>@endif</span></div></div>
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Level') }}</label><div class="col-7"><span class="form-control-plaintext">{{ $a['level'] ?? '' }} · {{ $a['accountType'] ?? '' }} · {{ __('revision') }} {{ $a['revision'] ?? 1 }}</span></div></div>
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Submitted') }}</label><div class="col-7"><span class="form-control-plaintext">{{ $fmt($a['submittedAt'] ?? null) }}</span></div></div>
                @foreach ($part as $k => $v)
                    @if (is_scalar($v))
                        <div class="form-group row width-50"><label class="col-3 control-label">{{ __(ucfirst(trim(preg_replace('/([A-Z])/', ' $1', $k)))) }}</label><div class="col-7"><span class="form-control-plaintext">{{ $v }}</span></div></div>
                    @endif
                @endforeach
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-file-document"></i>{{ __('Documents') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <div class="finance-help mb-2">{{ __('Links open the file for 5 minutes; nothing is saved on the panel. Requirements come from') }} {{ $kycFrom }}.</div>
                    <table class="table table-bordered mb-2">
                        <thead><tr><th>{{ __('Document') }}</th><th>{{ __('Side') }}</th><th>{{ __('File') }}</th><th>{{ __('Status') }}</th><th style="width:38%"></th></tr></thead>
                        <tbody>
                        @forelse ($docs as $doc)
                            @php $d = $doc['data']; @endphp
                            <tr>
                                <td>{{ $d['type'] ?? '' }}</td>
                                <td>{{ $d['side'] ?? '' }}</td>
                                <td>@if (isset($links[$doc['id']]))<a href="{{ $links[$doc['id']] }}" target="_blank" rel="noopener noreferrer">{{ __('Open') }}</a>@else<span class="finance-help">{{ __('no file') }}</span>@endif</td>
                                <td>{{ $d['status'] ?? '' }}@if (!empty($d['note']))<div class="finance-help">{{ $d['note'] }}</div>@endif</td>
                                <td>
                                    @if ($canDecide && $open && ($d['status'] ?? '') === 'uploaded')
                                        <form method="POST" action="{{ route('finance.kyc.document', [$id, $doc['id']]) }}" class="d-flex">
                                            @csrf
                                            <input type="text" name="note" class="form-control form-control-sm mr-1" placeholder="{{ __('Note (optional)') }}">
                                            <button name="decision" value="accept" class="btn btn-sm btn-success mr-1">{{ __('Accept') }}</button>
                                            <button name="decision" value="reject" class="btn btn-sm btn-outline-danger">{{ __('Reject') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted">{{ __('No document uploaded.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    @if ($missing)
                        <div class="alert alert-warning mb-0">{{ __('Not met yet (needs accepted documents):') }} {{ implode('; ', $missing) }}</div>
                    @else
                        <div class="alert alert-success mb-0">{{ __('Every requirement of the level is met by accepted documents.') }}</div>
                    @endif
                </div></div>
            </fieldset>

            @if ($open && $canDecide)
            <fieldset>
                <legend><i class="mr-3 mdi mdi-gavel"></i>{{ __('Decision') }}</legend>
                @if ($status === 'submitted')
                    <div class="form-group row width-100"><div class="col-7">
                        <form method="POST" action="{{ route('finance.kyc.action', [$id, 'start']) }}">@csrf
                            <button class="btn btn-primary">{{ __('Start review') }}</button>
                            <span class="finance-help ml-2">{{ __('The customer sees "Under review". Starting is needed before approving.') }}</span>
                        </form>
                    </div></div>
                @endif
                <form method="POST" id="kyc-decision">
                    @csrf
                    <div class="form-group row width-100">
                        <label class="col-3 control-label">{{ __('Reason for the customer') }}</label>
                        <div class="col-7">
                            <textarea name="reason" class="form-control" rows="2">{{ old('reason') }}</textarea>
                            <div class="form-text text-muted">{{ __('Required to ask for more information or to reject; the customer sees it.') }}</div>
                        </div>
                    </div>
                    <div class="form-group row width-100"><div class="col-7">
                        @if ($status === 'under_review')
                            <button formaction="{{ route('finance.kyc.action', [$id, 'approve']) }}" class="btn btn-success mr-2" {{ $missing ? 'disabled' : '' }}
                                    onclick="return confirm('{{ __('Approve: the customer becomes verified at this level?') }}')">{{ __('Approve') }}</button>
                        @endif
                        <button formaction="{{ route('finance.kyc.action', [$id, 'info']) }}" class="btn btn-warning mr-2">{{ __('Ask for more information') }}</button>
                        <button formaction="{{ route('finance.kyc.action', [$id, 'reject']) }}" class="btn btn-danger"
                                onclick="return confirm('{{ __('Reject this application?') }}')">{{ __('Reject') }}</button>
                    </div></div>
                </form>
            </fieldset>
            @endif

            <fieldset>
                <legend><i class="mr-3 mdi mdi-history"></i>{{ __('History') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Action') }}</th><th>{{ __('Reason') }}</th><th>{{ __('By') }}</th></tr></thead>
                        <tbody>
                        @forelse ($reviews as $rv)
                            <tr><td>{{ $fmt($rv['data']['at'] ?? null) }}</td><td>{{ $rv['data']['action'] ?? '' }}</td><td>{{ $rv['data']['reason'] ?? '' }}</td><td class="finance-mono">{{ $rv['data']['by'] ?? '' }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">{{ __('Nothing yet.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div></div>
            </fieldset>

        </div></div>
        <div class="form-group col-12 text-center btm-btn">
            <a href="{{ route('finance.kyc') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ __('Back') }}</a>
        </div>
        </div></div>
    </div>
</div>
@endsection
