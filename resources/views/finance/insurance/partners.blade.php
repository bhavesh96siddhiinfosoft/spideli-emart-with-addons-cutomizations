@extends('layouts.app')
@section('content')
@php $e = $edit['data'] ?? []; @endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Insurers (partners)'), 'tab' => 'insurance'])
    <div class="container-fluid">
        @if ($canManage)
        <div class="card mb-4"><div class="card-body">
            <form method="POST" action="{{ route('finance.insurance.partners.save') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $edit['id'] ?? '' }}">
                <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-domain"></i>{{ $edit ? __('Edit insurer') : __('Add an insurer') }}</legend>
                        <div class="form-group row width-100 mb-3"><div class="form-text text-muted">{{ __('The insurance companies Spideli sells for. Only the panel sees this list; customers see the insurer\'s name on each product.') }}</div></div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label" for="pt-name">{{ __('Name') }}<span class="required-field"></span></label>
                            <div class="col-7"><input type="text" id="pt-name" name="name" class="form-control" value="{{ old('name', $e['name'] ?? '') }}" required></div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label" for="pt-lic">{{ __('Licence number') }}</label>
                            <div class="col-7"><input type="text" id="pt-lic" name="licenceNumber" class="form-control" value="{{ old('licenceNumber', $e['licenceNumber'] ?? '') }}"></div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label" for="pt-country">{{ __('Country') }}</label>
                            <div class="col-7"><input type="text" id="pt-country" name="country" class="form-control" value="{{ old('country', $e['country'] ?? '') }}"></div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label" for="pt-contact">{{ __('Contact') }}</label>
                            <div class="col-7"><input type="text" id="pt-contact" name="contact" class="form-control" value="{{ old('contact', $e['contact'] ?? '') }}" placeholder="{{ __('Name, phone, e-mail') }}"></div>
                        </div>
                    </fieldset>
                </div></div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Save') }}</button>
                    @if ($edit)<a href="{{ route('finance.insurance.partners') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>@endif
                </div>
            </form>
        </div></div>
        @endif
        <div class="card border mb-4">
            <div class="card-body">
                <table class="table table-bordered table-striped mb-0">
                    <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Licence number') }}</th><th>{{ __('Country') }}</th><th>{{ __('Contact') }}</th>@if ($canManage)<th></th>@endif</tr></thead>
                    <tbody>
                    @forelse ($rows as $r)
                        <tr><td>{{ $r['data']['name'] ?? '' }}</td><td>{{ $r['data']['licenceNumber'] ?? '' }}</td><td>{{ $r['data']['country'] ?? '' }}</td><td>{{ $r['data']['contact'] ?? '' }}</td>
                            @if ($canManage)<td><a class="btn btn-sm btn-outline-primary" href="{{ route('finance.insurance.partners', ['edit' => $r['id']]) }}">{{ __('Edit') }}</a></td>@endif</tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">{{ __('No insurer yet. Add one before creating products.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
