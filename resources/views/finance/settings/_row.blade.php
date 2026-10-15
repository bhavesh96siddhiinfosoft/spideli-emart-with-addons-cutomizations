{{-- One row of a repeatable settings field (see _rows). $key is the row's
     index in the posted array; __ROW__ in the template is replaced on Add. --}}
@php $base = $name . '[' . $key . ']'; $id = 'f-' . $name . '-' . $key; @endphp
<tr>
    @switch($f['type'])
        @case('kyc_documents')
            @php $type = (string)($row['type'] ?? ''); @endphp
            <td>
                <select name="{{ $base }}[type]" class="form-control finance-select2" data-tags="1" data-single="1" {{ $disabled }}>
                    <option value=""></option>
                    @foreach (array_values(array_unique(array_merge($knownDocTypes, $type !== '' ? [$type] : []))) as $o)
                        <option value="{{ $o }}" {{ $type === $o ? 'selected' : '' }}>{{ $o }}</option>
                    @endforeach
                </select>
            </td>
            @foreach (['front' => __('Front side'), 'back' => __('Back side')] as $side => $label)
                <td>
                    <div class="form-check">
                        <input type="hidden" name="{{ $base }}[{{ $side }}]" value="0">
                        <input type="checkbox" id="{{ $id }}-{{ $side }}" name="{{ $base }}[{{ $side }}]" value="1" {{ !empty($row[$side]) ? 'checked' : '' }} {{ $disabled }}>
                        {{-- The column header names the side; the label only draws the theme's tick box. --}}
                        <label class="control-label mb-0" for="{{ $id }}-{{ $side }}" title="{{ $label }}" aria-label="{{ $label }}"></label>
                    </div>
                </td>
            @endforeach
            @break

        @case('kyc_levels')
            @php $groups = (array)($row['groups'] ?? []); if (!$groups) { $groups = [[]]; } @endphp
            <td><input type="text" class="form-control" name="{{ $base }}[name]" value="{{ $row['name'] ?? '' }}" placeholder="basic" {{ $disabled }}></td>
            <td>
                <select name="{{ $base }}[accountTypes][]" multiple class="form-control finance-select2" {{ $disabled }}>
                    @foreach ($accountTypes as $o)
                        <option value="{{ $o }}" {{ in_array($o, (array)($row['accountTypes'] ?? []), true) ? 'selected' : '' }}>{{ $o }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <div class="finance-groups">
                    @foreach ($groups as $g => $types)
                        @include('finance.settings._group', ['gkey' => 'g' . $g, 'types' => (array)$types])
                    @endforeach
                </div>
                @if (!$disabled)
                    <button type="button" class="btn btn-sm btn-link px-0" data-add-group>{{ __('+ Add another requirement') }}</button>
                    <template data-group-template>@include('finance.settings._group', ['gkey' => '__G__', 'types' => []])</template>
                @endif
                <div class="form-text text-muted">{{ __('The customer must meet every requirement; within one requirement, any one of its documents is enough.') }}</div>
            </td>
            @break

        @case('banks')
            <td><input type="text" class="form-control" name="{{ $base }}[code]" value="{{ $row['code'] ?? '' }}" {{ $disabled }}></td>
            <td><input type="text" class="form-control" name="{{ $base }}[name]" value="{{ $row['name'] ?? '' }}" {{ $disabled }}></td>
            @break

        @case('required_documents')
            @php $type = (string)($row['type'] ?? ''); @endphp
            <td>
                <select name="{{ $base }}[type]" class="form-control finance-select2" data-tags="1" data-single="1" {{ $disabled }}>
                    <option value=""></option>
                    @foreach (array_values(array_unique(array_merge($knownDocTypes, $type !== '' ? [$type] : []))) as $o)
                        <option value="{{ $o }}" {{ $type === $o ? 'selected' : '' }}>{{ $o }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="{{ $base }}[financingTypes][]" multiple class="form-control finance-select2" data-placeholder="{{ __('Every financing type') }}" {{ $disabled }}>
                    @foreach ($financingTypes as $o)
                        <option value="{{ $o }}" {{ in_array($o, (array)($row['financingTypes'] ?? []), true) ? 'selected' : '' }}>{{ $o }}</option>
                    @endforeach
                </select>
            </td>
            @break
    @endswitch
    <td class="text-center align-middle">
        @if (!$disabled)
            <button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove-row title="{{ __('Remove') }}"><i class="mdi mdi-delete" style="font-size:20px"></i></button>
        @endif
    </td>
</tr>
