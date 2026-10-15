{{-- A repeatable settings field (KYC document types, KYC levels, banks,
     financing required documents): one row of normal fields per entry, with
     Add / remove, like the panel's other screens - no typed line formats.
     Needs: $f, $name, $rows, $disabled, and for the dropdowns $docTypeOptions. --}}
@php
    $knownDocTypes = $docTypeOptions;
    $financingTypes = ['loan', 'equity', 'grant', 'guarantee', 'mixed'];
    $accountTypes = ['individual', 'business'];
    $rowKeys = array_keys($rows);
@endphp

<div class="finance-rows" data-field="{{ $name }}">
    <table class="table table-bordered mb-2">
        <thead>
            <tr>
                @switch($f['type'])
                    @case('kyc_documents')
                        <th>{{ __('Document type') }}</th><th style="width:110px">{{ __('Front side') }}</th><th style="width:110px">{{ __('Back side') }}</th>
                        @break
                    @case('kyc_levels')
                        <th style="width:22%">{{ __('Level') }}</th><th style="width:22%">{{ __('Account types') }}</th><th>{{ __('Required documents') }}</th>
                        @break
                    @case('banks')
                        <th style="width:30%">{{ __('Bank code') }}</th><th>{{ __('Bank name') }}</th>
                        @break
                    @case('required_documents')
                        <th style="width:40%">{{ __('Document type') }}</th><th>{{ __('Only for these financing types') }}</th>
                        @break
                @endswitch
                <th style="width:60px"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                @include('finance.settings._row', ['row' => $row, 'key' => 'r' . $i])
            @endforeach
        </tbody>
    </table>
    @if (!$disabled)
        <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="{{ $name }}"><i class="mdi mdi-plus mr-1"></i>{{ __('Add') }}</button>
        <template data-row-template="{{ $name }}">
            @include('finance.settings._row', ['row' => [], 'key' => '__ROW__'])
        </template>
    @endif
</div>
