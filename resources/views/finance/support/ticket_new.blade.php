@extends('layouts.app')
@section('content')
@php use App\Finance\Support; @endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Open a ticket'), 'tab' => 'tickets'])
    <div class="container-fluid">
        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('finance.tickets.store') }}">
                @csrf
                <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
                <fieldset>
                    <legend><i class="mr-3 mdi mdi-lifebuoy"></i>{{ __('Open a ticket') }}</legend>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="tk-user">{{ __('Customer') }}<span class="required-field"></span></label>
                        <div class="col-7"><input type="text" id="tk-user" name="user" class="form-control" value="{{ old('user', $prefill['user']) }}" placeholder="{{ __('id, e-mail or phone') }}" required>
                            @if ($customer)<div class="form-text text-muted">{{ trim(($customer['data']['firstName'] ?? '') . ' ' . ($customer['data']['lastName'] ?? '')) }} · {{ $customer['data']['email'] ?? '' }}</div>@endif</div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="tk-source">{{ __('Came from') }}</label>
                        <div class="col-7"><select id="tk-source" name="source" class="form-control finance-select2">
                            @foreach (['chat' => __('Support chat'), 'complaint' => __('A complaint'), 'operation' => __('A failed operation'), 'phone' => __('Phone call'), 'other' => __('Other')] as $k => $l)
                                <option value="{{ $k }}" {{ old('source', $prefill['relatedRef'] ? 'operation' : 'chat') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach
                        </select></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="tk-cat">{{ __('Category') }}<span class="required-field"></span></label>
                        <div class="col-7"><select id="tk-cat" name="category" class="form-control finance-select2">
                            @foreach (Support::CATEGORIES as $c)<option value="{{ $c }}" {{ old('category', $prefill['category'] ?: 'other') === $c ? 'selected' : '' }}>{{ __(ucfirst($c)) }}</option>@endforeach
                        </select></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="tk-prio">{{ __('Priority') }}<span class="required-field"></span></label>
                        <div class="col-7"><select id="tk-prio" name="priority" class="form-control finance-select2">
                            @foreach (Support::PRIORITIES as $p)<option value="{{ $p }}" {{ old('priority', 'normal') === $p ? 'selected' : '' }}>{{ __(\App\Finance\Support::priorityLabel($p)) }} ({{ Support::slaHours($p) }} h)</option>@endforeach
                        </select></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="tk-ref">{{ __('Related reference') }}</label>
                        <div class="col-7"><input type="text" id="tk-ref" name="relatedRef" class="form-control" value="{{ old('relatedRef', $prefill['relatedRef']) }}" placeholder="SPF-…">
                            <div class="form-text text-muted">{{ __('A posting reference or a request id, if the problem is about one.') }}</div></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="tk-assign">{{ __('Assign to') }}</label>
                        <div class="col-7"><select id="tk-assign" name="assignedTo" class="form-control finance-select2"><option value="">{{ __('Nobody yet') }}</option>
                            @foreach ($admins as $aid => $email)<option value="{{ $aid }}" {{ old('assignedTo', 'admin:' . auth()->id()) === $aid ? 'selected' : '' }}>{{ $email }}</option>@endforeach
                        </select></div></div>
                    <div class="form-group row width-100"><label class="col-3 control-label" for="tk-subject">{{ __('Subject') }}<span class="required-field"></span></label>
                        <div class="col-7"><input type="text" id="tk-subject" name="subject" class="form-control" maxlength="200" value="{{ old('subject') }}" required></div></div>
                    <div class="form-group row width-100"><label class="col-3 control-label" for="tk-msg">{{ __('What happened') }}<span class="required-field"></span></label>
                        <div class="col-7"><textarea id="tk-msg" name="message" class="form-control" rows="5" required>{{ old('message') }}</textarea></div></div>
                </fieldset>
                </div></div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Open the ticket') }}</button>
                    <a href="{{ route('finance.tickets') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                </div>
            </form>
        </div></div>
    </div>
</div>
@endsection
@section('scripts')
<script>$(function () { $('.finance-select2').select2({ width: '100%' }); });</script>
@endsection
