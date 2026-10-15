{{-- One requirement of a KYC level: the customer uploads any one of these
     documents. A level's requirements are all needed (AND); the numbering and
     the AND between them are drawn by CSS, so they stay right on Add / remove. --}}
<div class="finance-group mb-2">
    <div class="finance-and">{{ __('AND') }}</div>
    <div class="d-flex align-items-center mb-1">
        <span class="finance-req-label">{{ __('Requirement') }} </span>
        <span class="finance-help ml-2">{{ __('any one of these') }}</span>
        @if (!$disabled)
            <button type="button" class="btn btn-sm btn-link text-danger px-2 ml-auto" data-remove-group title="{{ __('Remove') }}"><i class="mdi mdi-close"></i></button>
        @endif
    </div>
    <select name="{{ $base }}[groups][{{ $gkey }}][]" multiple class="form-control finance-select2" data-tags="1" data-placeholder="{{ __('Choose the documents…') }}" {{ $disabled }}>
        @foreach (array_values(array_unique(array_merge($knownDocTypes, $types))) as $o)
            <option value="{{ $o }}" {{ in_array($o, $types, true) ? 'selected' : '' }}>{{ $o }}</option>
        @endforeach
    </select>
</div>
