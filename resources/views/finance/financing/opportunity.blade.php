@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $o = $o ?? [];
    $cur = $o['currency'] ?? old('currency', $currencies[0] ?? '');
    $v = function ($key, $default = '') use ($o) { return old($key, $o[$key] ?? $default); };
    $money = function ($key) use ($o, $cur) { return old($key, isset($o[$key]) ? Money::toMajor((int)$o[$key], $cur) : ''); };
    $check = function ($key, $stored) { return old('_sent') ? (bool)old($key) : (bool)$stored; };
    $types = ['equity' => __('Equity (shares)'), 'loan' => __('Loan'), 'revenue_share' => __('Revenue share'), 'fund' => __('Fund')];
    $closing = ($o['closingDate'] ?? null) instanceof \DateTimeInterface ? $o['closingDate']->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d') : '';
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => $id ? ($o['title'] ?? __('Opportunity')) : __('New opportunity'), 'tab' => 'opportunities'])
    <div class="container-fluid">
        <div class="card"><div class="card-body">
            @if (!$projects)
                <div class="alert alert-warning">{{ __('No project has an approved financing application yet. An opportunity is published only for a reviewed project.') }}</div>
            @else
            <form method="POST" enctype="multipart/form-data" action="{{ $id ? route('finance.financing.opportunities.update', $id) : route('finance.financing.opportunities.store') }}">
                @csrf
                <input type="hidden" name="_sent" value="1">
                <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
                <fieldset>
                    <legend><i class="mr-3 mdi mdi-lightbulb"></i>{{ __('Opportunity') }}</legend>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="op-project">{{ __('Project') }}<span class="required-field"></span></label>
                        <div class="col-7"><select id="op-project" name="projectId" class="form-control finance-select2" required>
                            @foreach ($projects as $pid => $p)<option value="{{ $pid }}" {{ $v('projectId') === $pid ? 'selected' : '' }}>{{ $p['name'] ?? $pid }} - {{ $names[$p['ownerId'] ?? ''] ?? '' }}</option>@endforeach
                        </select><div class="form-text text-muted">{{ __('Projects whose financing was approved. The owner is kept private: investors see only the name below.') }}</div></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="op-owner">{{ __('Owner name shown') }}</label>
                        <div class="col-7"><input type="text" id="op-owner" name="ownerName" class="form-control" value="{{ $v('ownerName') }}" placeholder="{{ __('Blank = the project name') }}"></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="op-title">{{ __('Title') }}<span class="required-field"></span></label>
                        <div class="col-7"><input type="text" id="op-title" name="title" class="form-control" maxlength="200" value="{{ $v('title') }}" required></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="op-type">{{ __('Type') }}<span class="required-field"></span></label>
                        <div class="col-7"><select id="op-type" name="type" class="form-control finance-select2">@foreach ($types as $k => $label)<option value="{{ $k }}" {{ $v('type') === $k ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="op-sector">{{ __('Sector') }}<span class="required-field"></span></label>
                        <div class="col-7"><select id="op-sector" name="sector" class="form-control finance-select2">@foreach ($sectors as $s)<option value="{{ $s }}" {{ $v('sector') === $s ? 'selected' : '' }}>{{ __(ucfirst(str_replace('_', ' ', $s))) }}</option>@endforeach</select></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="op-country">{{ __('Country') }}</label>
                        <div class="col-7"><input type="text" id="op-country" name="country" class="form-control" value="{{ $v('country') }}" placeholder="{{ __('Blank = the project\'s') }}">
                            <div class="form-text text-muted">{{ __('As investors choose it in their preferences (e.g. CM).') }}</div></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="op-cur">{{ __('Currency') }}<span class="required-field"></span></label>
                        <div class="col-7">@if ($id)<input type="text" class="form-control" value="{{ $cur }}" disabled>@else<select id="op-cur" name="currency" class="form-control finance-select2">@foreach ($currencies as $c)<option value="{{ $c }}" data-symbol="{{ $symbols[$c] ?? $c }}" {{ $cur === $c ? 'selected' : '' }}>{{ $c }}</option>@endforeach</select>@endif</div></div>
                    @foreach (['minTicket' => __('Minimum ticket'), 'targetAmount' => __('Target amount'), 'raisedAmount' => __('Raised so far')] as $k => $label)
                        <div class="form-group row width-50"><label class="col-3 control-label" for="op-{{ $k }}">{{ $label }}@if ($k !== 'raisedAmount')<span class="required-field"></span>@endif</label>
                            <div class="col-7"><div class="finance-amount-box"><span class="finance-symbol op-symbol">{{ $symbols[$cur] ?? $cur }}</span>
                                <input type="text" inputmode="decimal" id="op-{{ $k }}" name="{{ $k }}" class="form-control" value="{{ $money($k) }}" {{ $k !== 'raisedAmount' ? 'required' : '' }}></div></div></div>
                    @endforeach
                    <div class="form-group row width-50"><label class="col-3 control-label" for="op-horizon">{{ __('Horizon (months)') }}</label>
                        <div class="col-7"><input type="number" min="0" id="op-horizon" name="horizonMonths" class="form-control" value="{{ $v('horizonMonths', 0) }}"></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="op-risk">{{ __('Risk level') }}<span class="required-field"></span></label>
                        <div class="col-7"><select id="op-risk" name="riskLevel" class="form-control finance-select2">@for ($i = 1; $i <= 7; $i++)<option value="{{ $i }}" {{ (int)$v('riskLevel', 5) === $i ? 'selected' : '' }}>{{ $i }} / 7</option>@endfor</select></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="op-return">{{ __('Target return') }}</label>
                        <div class="col-7"><div class="finance-amount-box"><span class="finance-symbol">%</span><input type="text" inputmode="decimal" id="op-return" name="targetReturnPercent" class="form-control" value="{{ $v('targetReturnPercent') }}"></div>
                            <div class="form-text text-muted">{{ __('Shown as "not guaranteed".') }}</div></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="op-close">{{ __('Closing date') }}</label>
                        <div class="col-7"><input type="date" id="op-close" name="closingDate" class="form-control" value="{{ old('closingDate', $closing) }}"></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label" for="op-status">{{ __('Status') }}</label>
                        <div class="col-7"><select id="op-status" name="status" class="form-control finance-select2">
                            <option value="open" {{ $v('status', 'open') === 'open' ? 'selected' : '' }}>{{ __('Open: investors can show interest') }}</option>
                            <option value="closed" {{ $v('status') === 'closed' ? 'selected' : '' }}>{{ __('Closed') }}</option></select></div></div>
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Published') }}</label>
                        <div class="col-7"><div class="form-check"><input type="checkbox" id="op-pub" name="published" value="1" {{ $check('published', $o['published'] ?? false) ? 'checked' : '' }}>
                            <label class="control-label" for="op-pub">{{ __('Shown in the app; matching investors are told once') }}</label></div></div></div>
                    <div class="form-group row width-100"><label class="col-3 control-label" for="op-sum">{{ __('Summary') }}</label>
                        <div class="col-7"><textarea id="op-sum" name="summary" class="form-control" rows="4">{{ $v('summary') }}</textarea></div></div>
                </fieldset>
                <fieldset>
                    <legend><i class="mr-3 mdi mdi-file-pdf-box"></i>{{ __('Documents') }}</legend>
                    <div class="form-group row width-100"><div class="col-7">
                        @if (!empty($o['documents']))
                            <table class="table table-bordered table-sm mb-3"><thead><tr><th>{{ __('Document') }}</th><th style="width:160px">{{ __('Remove') }}</th></tr></thead><tbody>
                            @foreach ((array)$o['documents'] as $i => $d)
                                <tr><td><a href="{{ route('finance.financing.opportunities.document', [$id, $i]) }}" target="_blank" rel="noopener">{{ $d['name'] ?? __('Document') }}</a></td>
                                    <td><div class="form-check"><input type="checkbox" id="doc-rm-{{ $i }}" name="removeDocuments[]" value="{{ $i }}"><label class="control-label" for="doc-rm-{{ $i }}">{{ __('Remove') }}</label></div></td></tr>
                            @endforeach
                            </tbody></table>
                        @endif
                        <table class="table table-bordered table-sm mb-2" id="op-docs"><thead><tr><th>{{ __('Title') }}</th><th>{{ __('PDF file') }}</th></tr></thead>
                            <tbody><tr><td><input type="text" name="documentTitles[]" class="form-control" placeholder="{{ __('e.g. Investor presentation') }}"></td><td><input type="file" name="documents[]" accept="application/pdf,.pdf" class="form-control"></td></tr></tbody></table>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="op-add-doc"><i class="mdi mdi-plus"></i> {{ __('Another document') }}</button>
                        <div class="form-text text-muted">{{ __('PDF only, at most 20 MB each. Signed-in customers can read them.') }}</div>
                    </div></div>
                </fieldset>
                </div></div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Save') }}</button>
                    <a href="{{ $id ? route('finance.financing.opportunities.show', $id) : route('finance.financing.opportunities') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                </div>
            </form>
            @endif
        </div></div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(function () {
        $('.finance-select2').select2({ width: '100%' });
        $('#op-cur').on('change', function () { $('.op-symbol').text($(this).find(':selected').data('symbol') || $(this).val()); });
        $('#op-add-doc').on('click', function () { var $r = $('#op-docs tbody tr:first').clone(); $r.find('input').val(''); $('#op-docs tbody').append($r); });
    });
</script>
@endsection
