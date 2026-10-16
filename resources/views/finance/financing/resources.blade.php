@extends('layouts.app')
@section('content')
@php
    $e = $edit['data'] ?? [];
    $types = ['template' => __('Template'), 'guide' => __('Guide'), 'faq' => __('FAQ'), 'regulation' => __('Regulation')];
    $check = function ($key, $stored) { return old('_sent') ? (bool)old($key) : (bool)$stored; };
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Financial resources'), 'tab' => 'financing'])
    <div class="container-fluid">
        @if ($canManage && ($edit || $new))
        <div class="card mb-4"><div class="card-body">
            <form method="POST" enctype="multipart/form-data" action="{{ route('finance.financing.resources.save') }}">
                @csrf
                <input type="hidden" name="_sent" value="1">
                <input type="hidden" name="id" value="{{ $edit['id'] ?? '' }}">
                <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-book-open-variant"></i>{{ $edit ? __('Edit resource') : __('New resource') }}</legend>
                        <div class="form-group row width-50"><label class="col-3 control-label" for="rs-title">{{ __('Title') }}<span class="required-field"></span></label>
                            <div class="col-7"><input type="text" id="rs-title" name="title" class="form-control" maxlength="200" value="{{ old('title', $e['title'] ?? '') }}" required></div></div>
                        <div class="form-group row width-50"><label class="col-3 control-label" for="rs-type">{{ __('Type') }}<span class="required-field"></span></label>
                            <div class="col-7"><select id="rs-type" name="type" class="form-control finance-select2">
                                @foreach ($types as $k => $label)<option value="{{ $k }}" {{ old('type', $e['type'] ?? 'guide') === $k ? 'selected' : '' }}>{{ $label }}</option>@endforeach
                            </select></div></div>
                        <div class="form-group row width-50"><label class="col-3 control-label" for="rs-country">{{ __('Country') }}</label>
                            <div class="col-7"><input type="text" id="rs-country" name="country" class="form-control" value="{{ old('country', $e['country'] ?? '') }}" placeholder="{{ __('Blank = every country') }}"></div></div>
                        <div class="form-group row width-50"><label class="col-3 control-label" for="rs-lang">{{ __('Language') }}</label>
                            <div class="col-7"><input type="text" id="rs-lang" name="language" class="form-control" value="{{ old('language', $e['language'] ?? '') }}" placeholder="en, fr, ar"></div></div>
                        <div class="form-group row width-50"><label class="col-3 control-label" for="rs-order">{{ __('Display order') }}</label>
                            <div class="col-7"><input type="number" id="rs-order" name="order" class="form-control" value="{{ old('order', $e['order'] ?? 0) }}"></div></div>
                        <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Published') }}</label>
                            <div class="col-7"><div class="form-check"><input type="checkbox" id="rs-pub" name="published" value="1" {{ $check('published', $e['published'] ?? false) ? 'checked' : '' }}>
                                <label class="control-label" for="rs-pub">{{ __('Shown to customers in the app') }}</label></div></div></div>
                        <div class="form-group row width-100"><label class="col-3 control-label" for="rs-sum">{{ __('Summary') }}</label>
                            <div class="col-7"><input type="text" id="rs-sum" name="summary" class="form-control" value="{{ old('summary', $e['summary'] ?? '') }}"></div></div>
                        <div class="form-group row width-100"><label class="col-3 control-label" for="rs-body">{{ __('Text') }}</label>
                            <div class="col-7"><textarea id="rs-body" name="body" class="form-control" rows="8">{{ old('body', $e['body'] ?? '') }}</textarea></div></div>
                        <div class="form-group row width-100"><label class="col-3 control-label" for="rs-file">{{ __('File') }}</label>
                            <div class="col-7"><input type="file" id="rs-file" name="file" accept="application/pdf,image/jpeg,image/png" class="form-control">
                                <div class="form-text text-muted">@if (!empty($e['storagePath'])){{ __('A file is attached:') }} <a href="{{ route('finance.financing.resources.file', $edit['id']) }}" target="_blank" rel="noopener">{{ __('open') }}</a>. {{ __('A new file replaces it.') }}@else{{ __('PDF, JPEG or PNG, at most 20 MB. Signed-in customers can open it.') }}@endif</div></div></div>
                        <div class="form-group row width-100"><label class="col-3 control-label" for="rs-url">{{ __('Or a link') }}</label>
                            <div class="col-7"><input type="url" id="rs-url" name="url" class="form-control" value="{{ old('url', $e['url'] ?? '') }}" placeholder="https://"></div></div>
                    </fieldset>
                </div></div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Save') }}</button>
                    <a href="{{ route('finance.financing.resources') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                </div>
            </form>
        </div></div>
        @endif
        <div class="card border mb-4">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <p class="mb-0 text-dark-2">{{ __('Templates, guides, FAQ and regulations shown to business owners in the app (published ones only).') }}</p>
                <div class="text-nowrap ml-3"><a class="btn btn-outline-primary" href="{{ route('finance.financing') }}">{{ __('Back to financing') }}</a>
                    @if ($canManage)<a class="btn btn-primary ml-1" href="{{ route('finance.financing.resources', ['new' => 1]) }}"><i class="mdi mdi-plus mr-1"></i>{{ __('New resource') }}</a>@endif</div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped mb-0">
                    <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Type') }}</th><th>{{ __('Country') }}</th><th>{{ __('Language') }}</th><th>{{ __('File or link') }}</th><th>{{ __('Published') }}</th>@if ($canManage)<th></th>@endif</tr></thead>
                    <tbody>
                    @forelse ($rows as $r)
                        @php $d = $r['data']; @endphp
                        <tr><td>{{ $d['title'] ?? '' }}<div class="finance-help">{{ $d['summary'] ?? '' }}</div></td><td>{{ $types[$d['type'] ?? ''] ?? ($d['type'] ?? '') }}</td>
                            <td>{{ $d['country'] ?? '' }}</td><td>{{ $d['language'] ?? '' }}</td>
                            <td>@if (!empty($d['storagePath']) || !empty($d['url']))<a href="{{ route('finance.financing.resources.file', $r['id']) }}" target="_blank" rel="noopener">{{ __('open') }}</a>@else — @endif</td>
                            <td>@if (!empty($d['published']))<span class="finance-badge-ok">{{ __('Yes') }}</span>@else<span class="finance-badge-no">{{ __('No') }}</span>@endif</td>
                            @if ($canManage)<td><a class="btn btn-sm btn-outline-primary" href="{{ route('finance.financing.resources', ['edit' => $r['id']]) }}">{{ __('Edit') }}</a></td>@endif</tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">{{ __('Nothing yet.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>$(function () { $('.finance-select2').select2({ width: '100%' }); });</script>
@endsection
