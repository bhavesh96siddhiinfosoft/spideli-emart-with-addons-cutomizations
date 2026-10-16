@extends('layouts.app')
@section('content')
@php
    $fmt = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i') : ''; };
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Customer reports'), 'tab' => 'investments'])
    <div class="container-fluid">
        @if ($canManage)
        <div class="card mb-4"><div class="card-body">
            <form method="POST" enctype="multipart/form-data" action="{{ route('finance.investments.reports.store') }}">
                @csrf
                <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-file-upload"></i>{{ __('Add a report') }}</legend>
                        <div class="form-group row width-100 mb-3"><div class="form-text text-muted">{{ __('A PDF statement for one investor. It is stored privately; only that customer can open it in the app, and they are notified.') }}</div></div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label" for="rep-user">{{ __('Customer') }}<span class="required-field"></span></label>
                            <div class="col-7">
                                <select id="rep-user" name="userId" class="form-control finance-select2" required>
                                    <option value="">{{ __('Choose') }}</option>
                                    @foreach ($investors as $uid)
                                        <option value="{{ $uid }}" {{ old('userId') === $uid ? 'selected' : '' }}>{{ $names[$uid] ?? $uid }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted">{{ __('Customers who hold or held units in a fund.') }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label" for="rep-period">{{ __('Period') }}<span class="required-field"></span></label>
                            <div class="col-7"><input type="text" id="rep-period" name="period" class="form-control" maxlength="60" value="{{ old('period') }}" placeholder="{{ __('e.g. Q3 2026') }}" required></div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label" for="rep-title">{{ __('Title') }}<span class="required-field"></span></label>
                            <div class="col-7"><input type="text" id="rep-title" name="title" class="form-control" maxlength="120" value="{{ old('title') }}" placeholder="{{ __('e.g. Quarterly statement') }}" required></div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label" for="rep-file">{{ __('PDF file') }}<span class="required-field"></span></label>
                            <div class="col-7"><input type="file" id="rep-file" name="report" accept="application/pdf,.pdf" class="form-control" required>
                                <div class="form-text text-muted">{{ __('PDF only, at most 20 MB.') }}</div></div>
                        </div>
                    </fieldset>
                </div></div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> {{ __('Upload and notify') }}</button>
                    <a href="{{ route('finance.investments') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ __('Back to the funds') }}</a>
                </div>
            </form>
        </div></div>
        @endif

        <div class="card border mb-4">
            <div class="card-header border-0">
                <h3 class="text-dark-2 mb-1 h4">{{ __('Reports sent') }}</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="financeReportsTable">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Title') }}</th><th>{{ __('Period') }}</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($reports as $r)
                            @php $d = $r['data']; @endphp
                            <tr>
                                <td data-order="{{ ($d['createdAt'] ?? null) instanceof \DateTimeInterface ? $d['createdAt']->getTimestamp() : 0 }}">{{ $fmt($d['createdAt'] ?? null) }}</td>
                                <td>{{ $names[$d['userId'] ?? ''] ?? ($d['userId'] ?? '') }}</td>
                                <td>{{ $d['title'] ?? '' }}</td>
                                <td>{{ $d['period'] ?? '' }}</td>
                                <td>@if (!empty($d['storagePath']))<a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="{{ route('finance.investments.reports.file', $r['id']) }}"><i class="mdi mdi-file-pdf-box"></i> {{ __('Open') }}</a>@endif</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(function () {
        $('.finance-select2').select2({ width: '100%' });
        $('#financeReportsTable').DataTable({ pageLength: 25, order: [[0, 'desc']], language: typeof datatableLang !== 'undefined' ? datatableLang : {} });
    });
</script>
@endsection
