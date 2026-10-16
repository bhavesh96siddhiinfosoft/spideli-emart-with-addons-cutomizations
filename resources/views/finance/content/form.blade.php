@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $tz = new DateTimeZone(config('app.timezone'));
    $r = $r ?? [];
    $cur = $r['currency'] ?? old('currency', 'XAF');
    $val = function ($key, $f) use ($r, $tz, $cur) {
        if (old('_sent')) { return old($key); }
        $v = $r[$key] ?? null;
        if ($v === null) { return $f[0] === 'bool' ? false : ''; }
        if ($v instanceof \DateTimeInterface) { return $v->setTimezone($tz)->format($f[0] === 'datetime' ? 'Y-m-d\TH:i' : 'Y-m-d'); }
        if ($f[0] === 'money') { return Money::toMajor((int)$v, $cur); }
        if (is_float($v)) { return rtrim(rtrim(number_format($v, 6, '.', ''), '0'), '.'); }
        return $v;
    };
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __($s['title']), 'tab' => 'content'])
    <div class="container-fluid">
        @include('finance.content._tabs')
        <div class="card"><div class="card-body">
            <form method="POST" action="{{ $id ? route('finance.content.update', [$tab, $id]) : route('finance.content.store', $tab) }}">
                @csrf
                <input type="hidden" name="_sent" value="1">
                <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
                <fieldset>
                    <legend><i class="mr-3 mdi mdi-chart-box"></i>{{ $id ? __('Edit') : __('Add') }}: {{ __($s['title']) }}</legend>
                    <div class="form-group row width-100 mb-3"><div class="form-text text-muted">{{ __($s['help']) }}</div></div>
                    @foreach ($s['fields'] as $key => $f)
                        @php $v = $val($key, $f); $wide = in_array($f[0], ['textarea', 'packages', 'multiselect'], true); @endphp
                        <div class="form-group row {{ $wide ? 'width-100' : 'width-50' }}">
                            <label class="col-3 control-label" for="ct-{{ $key }}">{{ __($f[1]) }}@if (!empty($f['req']))<span class="required-field"></span>@endif</label>
                            <div class="col-7">
                                @switch($f[0])
                                    @case('textarea')
                                        <textarea id="ct-{{ $key }}" name="{{ $key }}" class="form-control" rows="4">{{ $v }}</textarea>
                                        @break
                                    @case('bool')
                                        <div class="form-check"><input type="checkbox" id="ct-{{ $key }}" name="{{ $key }}" value="1" {{ $v ? 'checked' : '' }}><label class="control-label" for="ct-{{ $key }}">{{ __('Yes') }}</label></div>
                                        @break
                                    @case('select')
                                        <select id="ct-{{ $key }}" name="{{ $key }}" class="form-control finance-select2">
                                            @if (empty($f['req']))<option value="">—</option>@endif
                                            @foreach ($f['options'] as $ok => $ol)<option value="{{ $ok }}" {{ (string)$v === (string)$ok ? 'selected' : '' }}>{{ __($ol) }}</option>@endforeach
                                        </select>
                                        @break
                                    @case('multiselect')
                                        <select id="ct-{{ $key }}" name="{{ $key }}[]" class="form-control finance-select2" multiple>
                                            @foreach ($options[$key] ?? [] as $ok => $ol)<option value="{{ $ok }}" {{ in_array($ok, (array)$v, true) ? 'selected' : '' }}>{{ $ol }}</option>@endforeach
                                        </select>
                                        @break
                                    @case('packages')
                                        @php $rows = (array)$v ?: [['code' => '', 'name' => '', 'amount' => '']]; @endphp
                                        <table class="table table-bordered table-sm mb-2" id="ct-packages"><thead><tr><th>{{ __('Code') }}</th><th>{{ __('Name') }}</th><th style="width:180px">{{ __('Amount') }}</th></tr></thead><tbody>
                                        @foreach (array_values($rows) as $i => $p)
                                            <tr><td><input type="text" name="packages[{{ $i }}][code]" class="form-control" value="{{ $p['code'] ?? '' }}"></td>
                                                <td><input type="text" name="packages[{{ $i }}][name]" class="form-control" value="{{ $p['name'] ?? '' }}"></td>
                                                <td><input type="text" inputmode="decimal" name="packages[{{ $i }}][amount]" class="form-control" value="{{ is_int($p['amount'] ?? null) ? Money::toMajor($p['amount'], $cur) : ($p['amount'] ?? '') }}"></td></tr>
                                        @endforeach
                                        </tbody></table>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="ct-add-package"><i class="mdi mdi-plus"></i> {{ __('Another package') }}</button>
                                        @break
                                    @default
                                        <input type="{{ ['date' => 'date', 'datetime' => 'datetime-local', 'url' => 'url', 'int' => 'number'][$f[0]] ?? 'text' }}" id="ct-{{ $key }}" name="{{ $key }}" class="form-control"
                                            value="{{ is_array($v) ? '' : $v }}" {{ in_array($f[0], ['number', 'money'], true) ? 'inputmode=decimal' : '' }} {{ !empty($f['req']) ? 'required' : '' }}
                                            @if (isset($f['max']) && $f[0] === 'text') maxlength="{{ $f['max'] }}" @endif>
                                @endswitch
                                @if (!empty($f['help']))<div class="form-text text-muted">{{ __($f['help']) }}</div>@endif
                                @if ($f[0] === 'money')<div class="form-text text-muted">{{ __('In the currency above.') }}</div>@endif
                            </div>
                        </div>
                    @endforeach
                    @if ($tab === 'billers')
                        <div class="form-group row width-100"><label class="col-3 control-label" for="ct-samples">{{ __('Test the pattern') }}</label>
                            <div class="col-7"><textarea id="ct-samples" name="samples" class="form-control" rows="3" placeholder="{{ __('Real account numbers, one per line') }}">{{ old('samples') }}</textarea>
                                <div class="form-text text-muted">{{ __('On saving, each line is shown as matching (✓) or not (✗).') }}</div></div></div>
                    @endif
                    @if ($tab === 'indicators' && isset($r['previousValue']))
                        <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Previous value') }}</label><div class="col-7"><span class="form-control-plaintext">{{ $r['previousValue'] }}</span></div></div>
                    @endif
                </fieldset>
                </div></div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Save') }}</button>
                    <a href="{{ route('finance.content', ['tab' => $tab]) }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                </div>
            </form>
        </div></div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(function () {
        $('.finance-select2').select2({ width: '100%' });
        $('#ct-add-package').on('click', function () {
            var $body = $('#ct-packages tbody'), n = $body.find('tr').length, $row = $body.find('tr:first').clone();
            $row.find('input').each(function () { this.name = this.name.replace(/\[\d+\]/, '[' + n + ']'); $(this).val(''); });
            $body.append($row);
        });
    });
</script>
@endsection
