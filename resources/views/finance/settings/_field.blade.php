{{-- One settings field in the panel's form layout: label above the control,
     width-50 for short fields (two per row), width-100 for lists and texts.
     Needs: $path, $f, $data, $hasOld, $fieldErrors, $disabled, $moneyCurrencies, $currencySymbols,
     $currencyCodes, $countryCodes, $availableMethods, $methodProviders, $methodGaps. --}}
@php
    use App\Finance\SettingsSchema;
    use App\Finance\Money;
    $name = SettingsSchema::inputName($path);
    $stored = SettingsSchema::getPath($data, $path);
    $isList = in_array($f['type'], ['options', 'list', 'currencies', 'countries', 'int_list']);
    $current = $hasOld ? old($name, $isList ? [] : null) : SettingsSchema::formValue($f, $stored);
    $wide = $isList || in_array($f['type'], ['textarea', 'kyc_documents', 'kyc_levels', 'banks', 'required_documents']);
@endphp
<div class="form-group row {{ $wide ? 'width-100' : 'width-50' }}">
    @if ($f['type'] === 'bool')
        @php $checked = $hasOld ? (bool)old($name) : (bool)$stored; @endphp
        <div class="form-check">
            <input type="hidden" name="{{ $name }}" value="0">
            <input type="checkbox" id="f-{{ $name }}" name="{{ $name }}" value="1" {{ $checked ? 'checked' : '' }} {{ $disabled }}>
            <label class="col-3 control-label" for="f-{{ $name }}">{{ __($f['label']) }}</label>
        </div>
    @else
        <label class="col-3 control-label" for="f-{{ $name }}">{{ __($f['label']) }}@if (!empty($f['required']))<span class="required-field"></span>@endif</label>
        <div class="col-7">
            @switch($f['type'])
                @case('options')
                    @php
                        $selected = (array)$current;
                        $isMethods = SettingsSchema::isMethodField($f);
                    @endphp
                    <select id="f-{{ $name }}" name="{{ $name }}[]" multiple class="form-control finance-select2" {{ $disabled }}>
                        @foreach (SettingsSchema::optionValues($f) as $o)
                            @if (!$isMethods || in_array($o, $availableMethods, true) || in_array($o, $selected, true))
                                <option value="{{ $o }}" {{ in_array($o, $selected, true) ? 'selected' : '' }}>{{ $o }}{{ $isMethods && !empty($methodProviders[$o]) ? ' (' . implode(', ', $methodProviders[$o]) . ')' : '' }}</option>
                            @endif
                        @endforeach
                    </select>
                    @if ($isMethods)
                        <div class="form-text text-muted">{{ __('Only the methods whose payment gateway serves the selected regions are listed (Settings > Payment methods). Each region keeps only the methods of its own gateways.') }}</div>
                        @foreach ($methodGaps as $m => $names)
                            @if (in_array($m, $selected, true))
                                <div class="form-text text-warning">{{ $m }}: {{ __('no gateway in') }} {{ implode(', ', $names) }}</div>
                            @endif
                        @endforeach
                    @endif
                    @break

                @case('currencies')
                @case('currency')
                    @php $selected = $f['type'] === 'currency' ? [(string)($hasOld ? old($name) : $stored)] : (array)$current; @endphp
                    <select id="f-{{ $name }}" name="{{ $name }}{{ $f['type'] === 'currencies' ? '[]' : '' }}" {{ $f['type'] === 'currencies' ? 'multiple' : '' }} class="form-control finance-select2" {{ $disabled }}>
                        @if ($f['type'] === 'currency')<option value="">—</option>@endif
                        @foreach ($currencyCodes + array_fill_keys(array_diff(array_filter($selected), array_keys($currencyCodes)), null) as $code => $text)
                            <option value="{{ $code }}" {{ in_array($code, $selected, true) ? 'selected' : '' }}>{{ $text ?: $code }}</option>
                        @endforeach
                    </select>
                    <div class="form-text text-muted">{{ __('From the Currencies list (Settings > Currencies).') }}</div>
                    @break

                @case('countries')
                @case('list')
                @case('int_list')
                    @php
                        $selected = (array)$current;
                        $suggest = $f['type'] === 'countries' ? $countryCodes : [];
                    @endphp
                    <select id="f-{{ $name }}" name="{{ $name }}[]" multiple class="form-control finance-select2" data-tags="1" {{ $disabled }}>
                        @foreach (array_values(array_unique(array_merge($selected, $suggest))) as $o)
                            <option value="{{ $o }}" {{ in_array($o, $selected, true) ? 'selected' : '' }}>{{ $o }}</option>
                        @endforeach
                    </select>
                    <div class="form-text text-muted">
                        @if ($f['type'] === 'countries'){{ __('2-letter country codes. Pick or type a new one, then press Enter.') }}
                        @elseif ($f['type'] === 'int_list'){{ __('Numbers. Type one, then press Enter.') }}
                        @else{{ __('Pick or type a new entry, then press Enter.') }}@endif
                    </div>
                    @break

                @case('select')
                    @php $value = $hasOld ? old($name) : ($stored ?? ($f['default'] ?? '')); @endphp
                    <select class="form-control finance-select2" id="f-{{ $name }}" name="{{ $name }}" {{ $disabled }}>
                        <option value="">—</option>
                        @foreach ($f['options'] as $o)
                            <option value="{{ $o }}" {{ $value === $o ? 'selected' : '' }}>{{ $o }}</option>
                        @endforeach
                    </select>
                    @break

                @case('money')
                    @php $amounts = is_array($stored) ? $stored : []; @endphp
                    <div data-money="{{ $name }}">
                        @foreach ($moneyCurrencies as $c)
                            @php $sym = $currencySymbols[$c] ?? $c; @endphp
                            <div class="finance-amount-box" data-currency="{{ $c }}" title="{{ $c }}">
                                <span class="finance-symbol">{{ $sym }}</span>
                                <input type="text" inputmode="decimal" class="form-control" style="padding-left: calc(20px + {{ mb_strlen($sym) }}ch)" name="{{ $name }}[{{ $c }}]"
                                       value="{{ $hasOld ? old($name . '.' . $c) : (isset($amounts[$c]) ? Money::toMajor($amounts[$c], $c) : '') }}" {{ $disabled }}>
                            </div>
                        @endforeach
                    </div>
                    @break

                @case('version')
                    <input type="text" class="form-control" value="{{ is_int($stored) ? $stored : '—' }}" disabled>
                    <div class="form-text text-muted">{{ __('Set by the panel: increases by 1 whenever the text changes.') }}</div>
                    @break

                @case('textarea')
                    <textarea class="form-control" id="f-{{ $name }}" name="{{ $name }}" rows="5" {{ $disabled }}>{{ $hasOld ? old($name) : SettingsSchema::formValue($f, $stored) }}</textarea>
                    @break

                @case('kyc_documents')
                @case('kyc_levels')
                @case('banks')
                @case('required_documents')
                    @php
                        $rows = $hasOld ? SettingsSchema::postedRows($f, old($name, [])) : SettingsSchema::formRows($f, $stored);
                        if (!$rows) { $rows = [[]]; }
                        // Document types offered in the dropdowns: KYC's known types and
                        // the ones already defined; financing's own document types.
                        $docTypeOptions = $path === 'requiredDocuments'
                            ? array_values(array_unique(array_merge((array)SettingsSchema::getPath($data, 'documentTypes'),
                                ['business_plan', 'financial_statements', 'registration', 'tax_certificate', 'id_documents'])))
                            : array_values(array_unique(array_merge(['national_id', 'passport', 'selfie', 'proof_of_address', 'business_registration'],
                                array_keys((array)SettingsSchema::getPath($data, 'documentTypes')))));
                    @endphp
                    @include('finance.settings._rows', ['f' => $f, 'name' => $name, 'rows' => $rows, 'disabled' => $disabled, 'docTypeOptions' => $docTypeOptions])
                    @break

                @default
                    <input type="{{ in_array($f['type'], ['int', 'number']) ? 'number' : 'text' }}" {!! $f['type'] === 'number' ? 'step="any"' : '' !!}
                           {!! isset($f['min']) ? 'min="' . $f['min'] . '"' : '' !!} {!! isset($f['max']) ? 'max="' . $f['max'] . '"' : '' !!}
                           class="form-control" id="f-{{ $name }}" name="{{ $name }}" value="{{ $hasOld ? old($name) : SettingsSchema::formValue($f, $stored) }}" {{ $disabled }}>
            @endswitch
            @if (!empty($f['help']))<div class="form-text text-muted">{{ __($f['help']) }}</div>@endif
            @if (isset($fieldErrors[$path]))<div class="err finance-field-error">{{ __($fieldErrors[$path]) }}</div>@endif
        </div>
    @endif
    @if ($f['type'] === 'bool' && !empty($f['help']))<div class="form-text text-muted finance-check-help">{{ __($f['help']) }}</div>@endif
</div>
