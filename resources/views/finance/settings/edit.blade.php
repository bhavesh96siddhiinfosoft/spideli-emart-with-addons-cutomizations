@extends('layouts.app')
@section('content')
@php
    // The fields grouped into the panel's fieldsets: the module's own fields
    // first, then one fieldset per section heading of the schema.
    $groups = [['title' => $title, 'fields' => []]];
    foreach ($fields as $path => $f) {
        if (!is_string($path)) {
            $groups[] = ['title' => $f['section'], 'fields' => []];
            continue;
        }
        $groups[count($groups) - 1]['fields'][$path] = $f;
    }
    $groups = array_values(array_filter($groups, function ($g) { return $g['fields']; }));
    $shared = [
        'data' => $data, 'hasOld' => session()->hasOldInput(), 'fieldErrors' => session('finance_errors', []),
        'disabled' => $canEdit ? '' : 'disabled', 'moneyCurrencies' => $moneyCurrencies, 'currencySymbols' => $currencySymbols, 'currencyCodes' => $currencyCodes,
        'countryCodes' => $countryCodes, 'availableMethods' => $availableMethods, 'methodProviders' => $methodProviders,
        'methodGaps' => $methodGaps,
    ];
    $icons = ['wallet' => 'mdi-wallet', 'kyc' => 'mdi-account-check', 'transfers' => 'mdi-bank-transfer', 'bills' => 'mdi-receipt',
        'cards' => 'mdi-credit-card', 'savings' => 'mdi-piggy-bank', 'tontines' => 'mdi-account-group', 'lending' => 'mdi-cash-multiple',
        'investments' => 'mdi-chart-line', 'insurance' => 'mdi-shield-check', 'financing' => 'mdi-briefcase', 'intelligence' => 'mdi-chart-areaspline'];
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __($title), 'tab' => 'settings'])
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                @if (!$regions)
                    <div class="alert alert-warning mb-0">
                        {{ __('No region is enabled for Finance yet. Tick "Finance" on a region in Region Management.') }}
                        <a href="{{ route('region') }}">{{ __('Region Management') }}</a>
                    </div>
                @else
                <form method="POST" action="{{ route('finance.settings.update', $doc) }}" id="finance-settings-form">
                    @csrf
                    <div class="row vendor_payout_create">
                        <div class="vendor_payout_create-inner">

                            <fieldset>
                                <legend><i class="mr-3 mdi mdi-earth"></i>{{ __('Regions') }}</legend>
                                <div class="form-group row width-100">
                                    <label class="col-3 control-label" for="finance-regions">{{ __('Regions') }}<span class="required-field"></span></label>
                                    <div class="col-7">
                                        <select id="finance-regions" name="regions[]" multiple class="form-control finance-select2" {{ $canEdit ? '' : 'disabled' }}>
                                            @foreach ($regions as $id => $r)
                                                <option value="{{ $id }}" {{ in_array($id, $chosen, true) ? 'selected' : '' }}>{{ $r['name'] }} ({{ $r['currency'] ?: __('no currency') }})</option>
                                            @endforeach
                                        </select>
                                        <div class="form-text text-muted">{{ __('Saving writes these values to every selected region, each in its own currency (set in Region Management). Changing the selection reloads the form.') }}</div>
                                        @if (isset(session('finance_errors', [])['currencies']))<div class="err finance-field-error">{{ __(session('finance_errors')['currencies']) }}</div>@endif
                                    </div>
                                </div>
                                <div class="form-group row width-100 mb-2">
                                    <div class="col-7">
                                    @if ($source === 'region')
                                        @if (!$missing)
                                            <span class="finance-badge-ok">{{ __('Offered') }}</span>
                                        @else
                                            <span class="finance-badge-no">{{ __('Not available') }}</span>
                                            <span class="form-text text-muted d-inline">{{ __('Missing:') }} {{ implode(', ', array_map('__', $missing)) }}</span>
                                        @endif
                                    @elseif ($source === 'app')
                                        <div class="alert alert-info mb-0">{{ __('This region has no settings for this module yet: the form starts from the app\'s current settings. Nothing is saved until you press Save.') }}</div>
                                    @else
                                        <div class="alert alert-info mb-0">{{ __('No settings yet for this module.') }}</div>
                                    @endif
                                    @if ($differs)
                                        <div class="alert alert-warning mt-2 mb-0">{{ __('The selected regions have different settings. The form shows the first region\'s; saving gives every selected region these values.') }}</div>
                                    @endif
                                    @foreach ($warnings as $w)
                                        <div class="form-text text-warning">{{ __($w) }}</div>
                                    @endforeach
                                    <div class="form-text text-muted">{{ __('Amounts are entered in the currency\'s normal unit (1500 XAF, 12.50 EUR) and stored in minor units. Saving writes the whole document and an audit entry.') }}</div>
                                    </div>
                                </div>
                            </fieldset>

                            @foreach ($groups as $i => $g)
                                <fieldset>
                                    <legend><i class="mr-3 mdi {{ $i === 0 ? ($icons[$doc] ?? 'mdi-cog') : 'mdi-tune' }}"></i>{{ __($g['title']) }}</legend>
                                    @foreach ($g['fields'] as $path => $f)
                                        @include('finance.settings._field', $shared + ['path' => $path, 'f' => $f])
                                    @endforeach
                                </fieldset>
                            @endforeach

                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        @if ($canEdit)
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Save') }}</button>
                        @endif
                        <a href="{{ route('finance.settings') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function financeSelect2($scope) {
        $scope.find('.finance-select2').addBack('.finance-select2').each(function () {
            var $s = $(this);
            if ($s.closest('template').length || $s.hasClass('select2-hidden-accessible')) return;
            $s.select2({
                width: '100%',
                tags: $s.data('tags') == 1,
                tokenSeparators: $s.data('tags') == 1 && $s.prop('multiple') ? [','] : [],
                placeholder: $s.data('placeholder') || ($s.prop('multiple') || $s.data('single') == 1 ? '{{ __('Choose…') }}' : ''),
                allowClear: !$s.prop('multiple')
            });
        });
    }

    $(function () {
        financeSelect2($(document));

        // Repeatable fields: each Add gets a fresh index so posted rows never collide.
        var seq = 0;
        function fresh(prefix) { return prefix + Date.now().toString(36) + (seq++); }
        $(document).on('click', '[data-add-row]', function () {
            var name = $(this).data('add-row');
            var html = $('template[data-row-template="' + name + '"]').html().split('__ROW__').join(fresh('n'));
            var $row = $(html);
            $(this).closest('.finance-rows').find('> table > tbody').append($row);
            financeSelect2($row);
        });
        $(document).on('click', '[data-remove-row]', function () {
            var $tbody = $(this).closest('tbody');
            $(this).closest('tr').remove();
            if (!$tbody.children('tr').length) {
                $tbody.closest('.finance-rows').find('[data-add-row]').trigger('click');
            }
        });
        $(document).on('click', '[data-add-group]', function () {
            var $cell = $(this).closest('td');
            var html = $cell.find('template[data-group-template]').html().split('__G__').join(fresh('g'));
            var $g = $(html);
            $cell.find('.finance-groups').append($g);
            financeSelect2($g);
        });
        $(document).on('click', '[data-remove-group]', function () {
            var $groups = $(this).closest('.finance-groups');
            if ($groups.children('.finance-group').length > 1) {
                $(this).closest('.finance-group').remove();
            }
        });

        // A different selection of regions is a different form (currencies,
        // payment methods, stored values): reload it for those regions.
        $('#finance-regions').on('change', function () {
            var ids = $(this).val() || [];
            if (!ids.length) return;
            var url = new URL(window.location.href);
            url.search = '';
            ids.forEach(function (id) { url.searchParams.append('regions[]', id); });
            window.location.href = url.toString();
        });

        // Wallet: a convertible currency also needs its amounts.
        var SYMBOLS = {!! json_encode($currencySymbols, JSON_UNESCAPED_UNICODE) !!};
        var $conv = $('#f-convert__currencies');
        if ($conv.length) {
            $conv.on('change', function () {
                ($conv.val() || []).forEach(function (c) {
                    $('[data-money]').each(function () {
                        var $box = $(this);
                        if ($box.find('[data-currency="' + c + '"]').length) return;
                        var name = $box.data('money');
                        var sym = SYMBOLS[c] || c;
                        var $row = $('<div class="finance-amount-box"><span class="finance-symbol"></span><input type="text" inputmode="decimal" class="form-control"></div>');
                        $row.attr({'data-currency': c, 'title': c});
                        $row.find('.finance-symbol').text(sym);
                        $row.find('input').attr('name', name + '[' + c + ']').css('padding-left', 'calc(20px + ' + sym.length + 'ch)');
                        $box.append($row);
                    });
                });
            });
        }
    });
</script>
@endsection
