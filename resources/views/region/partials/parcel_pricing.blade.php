{{-- Point 41 (12 Oct 2026): the region's parcel / mail pricing DEFAULTS.

     Saved as regions/{id}.parcelPricing. Each delivery company (owner) can
     set its own values per region on its carrier; anything it leaves blank
     falls back to these. The two fixed charges are platform charges and are
     set here only. Field names and the calculation are fixed in
     docs/app-spec-parcel-pricing.md - the app reads exactly these keys.

     Shared by region/create and region/edit so the two cannot drift. --}}
@php
    $regionParcelFields = [
        ['parcel_volumetric_divisor',      'region_parcel_volumetric_divisor',      '1'],
        ['parcel_home_pickup_charge',      'region_parcel_home_pickup_charge',      'any'],
        ['parcel_point_dropoff_charge',    'region_parcel_point_dropoff_charge',    'any'],
        ['parcel_home_delivery_charge',    'region_parcel_home_delivery_charge',    'any'],
        ['parcel_point_collection_charge', 'region_parcel_point_collection_charge', 'any'],
        ['parcel_intercity_fixed_tax',     'region_parcel_intercity_fixed_tax',     'any'],
        ['parcel_intercountry_fixed_tax', 'region_parcel_intercountry_fixed_tax', 'any'],
    ];
@endphp
<fieldset id="region_parcel_pricing">
    <legend><i class="mr-3 mdi mdi-package-variant"></i>{{ trans('lang.region_parcel_pricing') }}</legend>

    <div class="form-group row width-100">
        <div class="col-12">
            <div class="form-text text-muted">{{ trans('lang.region_parcel_pricing_help') }}</div>
            {{-- Shown when this region has never had these values saved, so
                 the prefilled numbers are not mistaken for live settings. --}}
            <div id="region_parcel_pricing_unsaved" class="alert alert-info mt-2 mb-0" style="display:none;">
                {{ trans('lang.region_parcel_pricing_unsaved') }}
            </div>
        </div>
    </div>

    @foreach ($regionParcelFields as [$fieldId, $langKey, $step])
        <div class="form-group row width-50">
            <label class="col-3 control-label" for="{{ $fieldId }}">{{ trans('lang.' . $langKey) }}</label>
            <div class="col-7">
                <input type="number" step="{{ $step }}" min="0" class="form-control" id="{{ $fieldId }}">
                <div class="form-text text-muted">{{ trans('lang.' . $langKey . '_help') }}</div>
                <div id="error_{{ $fieldId }}" class="err"></div>
            </div>
        </div>
    @endforeach
</fieldset>
