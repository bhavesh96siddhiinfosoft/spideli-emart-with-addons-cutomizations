@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    use App\Finance\Insurance;
    $p = $product ?? [];
    $cur = $p['currency'] ?? old('currency', $currencies[0] ?? '');
    $v = function ($key, $default = '') use ($p) { return old($key, $p[$key] ?? $default); };
    $money = function ($value) use ($cur) { return $value === null || $value === '' ? '' : Money::toMajor((int)$value, $cur); };
    $check = function ($key, $stored) { return old('_sent') ? (bool)old($key) : (bool)$stored; };
    $coverages = old('coverages', array_map(function ($c) use ($money) { return ['name' => $c['name'] ?? '', 'description' => $c['description'] ?? '', 'limit' => $money($c['limit'] ?? '')]; }, (array)($p['coverages'] ?? []))) ?: [['name' => '', 'description' => '', 'limit' => '']];
    $factorValues = function ($f) {
        if (($f['type'] ?? '') === 'select') {
            return implode("\n", array_map(function ($o) { return ($o['value'] ?? '') . ' | ' . ($o['label'] ?? '') . ' | ' . ($o['multiplier'] ?? 1); }, (array)($f['options'] ?? [])));
        }
        return implode("\n", array_map(function ($b) { return ($b['upTo'] ?? '') . ' = ' . ($b['multiplier'] ?? 1); }, (array)($f['bands'] ?? [])));
    };
    $factors = old('factors', array_map(function ($f) use ($factorValues) { return ['key' => $f['key'] ?? '', 'label' => $f['label'] ?? '', 'type' => $f['type'] ?? 'number', 'unit' => $f['unit'] ?? '',
        'min' => $f['min'] ?? '', 'max' => $f['max'] ?? '', 'values' => $factorValues($f)]; }, (array)($p['rating']['factors'] ?? []))) ?: [['key' => '', 'label' => '', 'type' => 'number', 'unit' => '', 'min' => '', 'max' => '', 'values' => '']];
    $loadings = (array)($p['rating']['frequencies'] ?? ['annual' => 0, 'monthly' => 5]);
    $freqLabels = ['annual' => __('Once a year'), 'semiannual' => __('Twice a year'), 'quarterly' => __('Every 3 months'), 'monthly' => __('Every month')];
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => $id ? ($p['name'] ?? __('Product')) : __('New product'), 'tab' => 'insurance'])
    <div class="container-fluid">
        <div class="card"><div class="card-body">
            @if (!$partners)
                <div class="alert alert-warning">{{ __('Add the insurer first:') }} <a href="{{ route('finance.insurance.partners') }}">{{ __('Insurers (partners)') }}</a></div>
            @elseif (!$id && !$currencies)
                <div class="alert alert-warning">{{ __('No region is enabled for Finance yet. Tick "Finance" on a region in Region Management.') }}</div>
            @else
            <form method="POST" enctype="multipart/form-data" action="{{ $id ? route('finance.insurance.products.update', $id) : route('finance.insurance.products.store') }}">
                @csrf
                <input type="hidden" name="_sent" value="1">
                <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">

                <fieldset>
                    <legend><i class="mr-3 mdi mdi-shield-check"></i>{{ __('Product') }}</legend>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label" for="pr-name">{{ __('Name') }}<span class="required-field"></span></label>
                        <div class="col-7"><input type="text" id="pr-name" name="name" class="form-control" maxlength="200" value="{{ $v('name') }}" required></div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label" for="pr-partner">{{ __('Insurer') }}<span class="required-field"></span></label>
                        <div class="col-7"><select id="pr-partner" name="partnerId" class="form-control finance-select2" required>
                            @foreach ($partners as $pid => $pt)<option value="{{ $pid }}" {{ $v('partnerId') === $pid ? 'selected' : '' }}>{{ $pt['name'] ?? $pid }}</option>@endforeach
                        </select></div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label" for="pr-cat">{{ __('Category') }}<span class="required-field"></span></label>
                        <div class="col-7"><select id="pr-cat" name="category" class="form-control finance-select2" required>
                            @foreach ($categories as $c)<option value="{{ $c }}" {{ $v('category') === $c ? 'selected' : '' }}>{{ __(ucfirst($c)) }}</option>@endforeach
                        </select><div class="form-text text-muted">{{ __('The categories of the regions\' insurance settings.') }}</div></div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label" for="pr-cur">{{ __('Currency') }}<span class="required-field"></span></label>
                        <div class="col-7">
                            @if ($id)<input type="text" class="form-control" value="{{ $cur }}" disabled><div class="form-text text-muted">{{ __('A product\'s currency cannot change once it exists.') }}</div>
                            @else<select id="pr-cur" name="currency" class="form-control finance-select2" required>
                                @foreach ($currencies as $c)<option value="{{ $c }}" data-symbol="{{ $symbols[$c] ?? $c }}" {{ $cur === $c ? 'selected' : '' }}>{{ $c }}</option>@endforeach
                            </select>@endif
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label" for="pr-term">{{ __('Term (months)') }}<span class="required-field"></span></label>
                        <div class="col-7"><input type="number" min="1" max="120" id="pr-term" name="termMonths" class="form-control" value="{{ $v('termMonths', 12) }}" required></div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label" for="pr-wait">{{ __('Waiting period (days)') }}</label>
                        <div class="col-7"><input type="number" min="0" id="pr-wait" name="waitingPeriodDays" class="form-control" value="{{ $v('waitingPeriodDays', 0) }}">
                            <div class="form-text text-muted">{{ __('Days after the cover starts before a claim can be made.') }}</div></div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label" for="pr-ded">{{ __('Deductible') }}</label>
                        <div class="col-7"><div class="finance-amount-box"><span class="finance-symbol pr-symbol">{{ $symbols[$cur] ?? $cur }}</span>
                            <input type="text" inputmode="decimal" id="pr-ded" name="deductible" class="form-control" value="{{ old('deductible', $money($p['deductible'] ?? '')) }}"></div>
                            <div class="form-text text-muted">{{ __('The part of each claim the customer pays.') }}</div></div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label" for="pr-order">{{ __('Display order') }}</label>
                        <div class="col-7"><input type="number" id="pr-order" name="order" class="form-control" value="{{ $v('order', 0) }}"></div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label" for="pr-status">{{ __('Status') }}</label>
                        <div class="col-7"><select id="pr-status" name="status" class="form-control finance-select2">
                            <option value="open" {{ $v('status', 'open') === 'open' ? 'selected' : '' }}>{{ __('Open: customers can ask a quote') }}</option>
                            <option value="closed" {{ $v('status') === 'closed' ? 'selected' : '' }}>{{ __('Closed: no new quotes') }}</option>
                        </select></div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{ __('Published') }}</label>
                        <div class="col-7"><div class="form-check"><input type="checkbox" id="pr-pub" name="published" value="1" {{ $check('published', $p['published'] ?? false) ? 'checked' : '' }}>
                            <label class="control-label" for="pr-pub">{{ __('Shown to customers in the app') }}</label></div></div>
                    </div>
                    <div class="form-group row width-100">
                        <label class="col-3 control-label" for="pr-sum">{{ __('Summary') }}</label>
                        <div class="col-7"><input type="text" id="pr-sum" name="summary" class="form-control" value="{{ $v('summary') }}"></div>
                    </div>
                    <div class="form-group row width-100">
                        <label class="col-3 control-label" for="pr-desc">{{ __('Description') }}</label>
                        <div class="col-7"><textarea id="pr-desc" name="description" class="form-control" rows="3">{{ $v('description') }}</textarea></div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend><i class="mr-3 mdi mdi-umbrella"></i>{{ __('Coverages and exclusions') }}</legend>
                    <div class="form-group row width-100"><div class="col-7">
                        <table class="table table-bordered table-sm mb-2" id="pr-coverages">
                            <thead><tr><th>{{ __('Coverage') }}</th><th>{{ __('Description') }}</th><th style="width:180px">{{ __('Limit') }}</th></tr></thead>
                            <tbody>
                            @foreach (array_values($coverages) as $i => $c)
                                <tr><td><input type="text" name="coverages[{{ $i }}][name]" class="form-control" value="{{ $c['name'] ?? '' }}"></td>
                                    <td><input type="text" name="coverages[{{ $i }}][description]" class="form-control" value="{{ $c['description'] ?? '' }}"></td>
                                    <td><input type="text" inputmode="decimal" name="coverages[{{ $i }}][limit]" class="form-control" value="{{ $c['limit'] ?? '' }}"></td></tr>
                            @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-primary finance-add-row" data-table="#pr-coverages"><i class="mdi mdi-plus"></i> {{ __('Another coverage') }}</button>
                        <div class="form-text text-muted">{{ __('A row without a name is ignored. Limits are in the product\'s currency.') }}</div>
                    </div></div>
                    <div class="form-group row width-100">
                        <label class="col-3 control-label" for="pr-excl">{{ __('Exclusions') }}</label>
                        <div class="col-7"><textarea id="pr-excl" name="exclusions" class="form-control" rows="3" placeholder="{{ __('One per line') }}">{{ old('exclusions', implode("\n", (array)($p['exclusions'] ?? []))) }}</textarea></div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend><i class="mr-3 mdi mdi-calculator"></i>{{ __('Pricing') }}</legend>
                    <div class="form-group row width-100 mb-3"><div class="form-text text-muted">{{ __('The premium for a year = base premium × the multiplier of each answer. Paying more often adds the frequency\'s loading. The panel uses this to suggest each quote\'s price; you confirm it.') }}</div></div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label" for="pr-base">{{ __('Base premium (a year)') }}</label>
                        <div class="col-7"><div class="finance-amount-box"><span class="finance-symbol pr-symbol">{{ $symbols[$cur] ?? $cur }}</span>
                            <input type="text" inputmode="decimal" id="pr-base" name="basePremium" class="form-control" value="{{ old('basePremium', $money($p['rating']['basePremium'] ?? '')) }}"></div></div>
                    </div>
                    <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Payment frequencies') }}</label><div class="col-7">
                        <table class="table table-bordered table-sm mb-0"><thead><tr><th>{{ __('Offered') }}</th><th style="width:200px">{{ __('Loading') }}</th></tr></thead><tbody>
                        @foreach ($freqLabels as $k => $label)
                            <tr><td><div class="form-check"><input type="checkbox" id="fq-{{ $k }}" name="frequencyOn[{{ $k }}]" value="1" {{ (old('_sent') ? old('frequencyOn.' . $k) : array_key_exists($k, $loadings)) ? 'checked' : '' }}>
                                <label class="control-label" for="fq-{{ $k }}">{{ $label }}</label></div></td>
                                <td><div class="finance-amount-box"><span class="finance-symbol">%</span><input type="text" inputmode="decimal" name="loading[{{ $k }}]" class="form-control" value="{{ old('loading.' . $k, $loadings[$k] ?? '') }}"></div></td></tr>
                        @endforeach
                        </tbody></table>
                    </div></div>
                    <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Rating factors') }}</label><div class="col-7">
                        <table class="table table-bordered table-sm mb-2" id="pr-factors">
                            <thead><tr><th style="width:120px">{{ __('Key') }}</th><th>{{ __('Question') }}</th><th style="width:120px">{{ __('Type') }}</th><th style="width:90px">{{ __('Unit') }}</th><th style="width:80px">{{ __('Min') }}</th><th style="width:80px">{{ __('Max') }}</th><th>{{ __('Multipliers') }}</th></tr></thead>
                            <tbody>
                            @foreach (array_values($factors) as $i => $f)
                                <tr><td><input type="text" name="factors[{{ $i }}][key]" class="form-control" value="{{ $f['key'] ?? '' }}" placeholder="age"></td>
                                    <td><input type="text" name="factors[{{ $i }}][label]" class="form-control" value="{{ $f['label'] ?? '' }}" placeholder="{{ __('e.g. Your age') }}"></td>
                                    <td><select name="factors[{{ $i }}][type]" class="form-control">
                                        <option value="number" {{ ($f['type'] ?? '') !== 'select' ? 'selected' : '' }}>{{ __('Number') }}</option>
                                        <option value="select" {{ ($f['type'] ?? '') === 'select' ? 'selected' : '' }}>{{ __('List') }}</option></select></td>
                                    <td><input type="text" name="factors[{{ $i }}][unit]" class="form-control" value="{{ $f['unit'] ?? '' }}"></td>
                                    <td><input type="text" name="factors[{{ $i }}][min]" class="form-control" value="{{ $f['min'] ?? '' }}"></td>
                                    <td><input type="text" name="factors[{{ $i }}][max]" class="form-control" value="{{ $f['max'] ?? '' }}"></td>
                                    <td><textarea name="factors[{{ $i }}][values]" class="form-control" rows="3">{{ $f['values'] ?? '' }}</textarea></td></tr>
                            @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-primary finance-add-row" data-table="#pr-factors"><i class="mdi mdi-plus"></i> {{ __('Another factor') }}</button>
                        <div class="form-text text-muted">{{ __('Number: one line per band, "up to = multiplier" (30 = 1, 50 = 1.3, 120 = 1.8). List: one line per choice, "value | label | multiplier" (car | Car | 1.1). A row without a key is ignored.') }}</div>
                    </div></div>
                </fieldset>

                <fieldset>
                    <legend><i class="mr-3 mdi mdi-file-sign"></i>{{ __('Terms and documents') }}</legend>
                    <div class="form-group row width-100">
                        <label class="col-3 control-label" for="pr-terms">{{ __('Terms') }}</label>
                        <div class="col-7"><textarea id="pr-terms" name="terms" class="form-control" rows="8">{{ $v('terms') }}</textarea>
                            <div class="form-text text-muted">@if ($id){{ __('Version') }} {{ (int)($p['termsVersion'] ?? 1) }}. {{ __('Changing the text makes a new version; quotes priced from now on carry it.') }}@else{{ __('Each quote carries these terms; the customer signs them on acceptance. Required before publishing.') }}@endif</div></div>
                    </div>
                    <div class="form-group row width-100"><div class="col-7">
                        @if (!empty($p['documents']))
                            <table class="table table-bordered table-sm mb-3">
                                <thead><tr><th>{{ __('Document') }}</th><th style="width:160px">{{ __('Remove') }}</th></tr></thead>
                                <tbody>
                                @foreach ((array)$p['documents'] as $i => $d)
                                    <tr><td><a href="{{ route('finance.insurance.document', ['products', $id, $i]) }}" target="_blank" rel="noopener">{{ $d['name'] ?? __('Document') }}</a></td>
                                        <td><div class="form-check"><input type="checkbox" id="doc-rm-{{ $i }}" name="removeDocuments[]" value="{{ $i }}"><label class="control-label" for="doc-rm-{{ $i }}">{{ __('Remove') }}</label></div></td></tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                        <table class="table table-bordered table-sm mb-2" id="pr-new-docs">
                            <thead><tr><th>{{ __('Title') }}</th><th>{{ __('PDF file') }}</th></tr></thead>
                            <tbody><tr><td><input type="text" name="documentTitles[]" class="form-control" placeholder="{{ __('e.g. Policy wording') }}"></td>
                                <td><input type="file" name="documents[]" accept="application/pdf,.pdf" class="form-control"></td></tr></tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="pr-add-doc"><i class="mdi mdi-plus"></i> {{ __('Another document') }}</button>
                        <div class="form-text text-muted">{{ __('PDF only, at most 20 MB each. Signed-in customers can read them; each quote carries them.') }}</div>
                    </div></div>
                </fieldset>
                </div></div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Save') }}</button>
                    <a href="{{ route('finance.insurance.products') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
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
        $('#pr-cur').on('change', function () { $('.pr-symbol').text($(this).find(':selected').data('symbol') || $(this).val()); });
        // A new row: a copy of the first, emptied, its [n] index moved past the last one.
        $('.finance-add-row').on('click', function () {
            var $body = $($(this).data('table')).find('tbody');
            var n = $body.find('tr').length;
            var $row = $body.find('tr:first').clone();
            $row.find('input, textarea, select').each(function () {
                this.name = this.name.replace(/\[\d+\]/, '[' + n + ']');
                if (this.tagName === 'SELECT') { this.selectedIndex = 0; } else { $(this).val(''); }
            });
            $body.append($row);
        });
        $('#pr-add-doc').on('click', function () {
            var $row = $('#pr-new-docs tbody tr:first').clone();
            $row.find('input').val('');
            $('#pr-new-docs tbody').append($row);
        });
    });
</script>
@endsection
