{{--
    Region assignment control.

    Shared by any record that can belong to several regions at once and stores
    them in a `regionIds` array. Populated by loadRegionAssignment() and read
    back with getRegionAssignment(), both defined in layouts/app.blade.php.

    Callers may override the wording:
      @include('settings.app.partials.region_assignment', [
          'regionFieldLabel' => trans('lang.section_regions'),
          'regionFieldHelp'  => trans('lang.section_regions_help'),
      ])
--}}
@php
    $regionFieldLabel = $regionFieldLabel ?? trans('lang.payment_regions');
    $regionFieldHelp = $regionFieldHelp ?? trans('lang.payment_regions_help');
@endphp
<div class="row vendor_payout_create">

    <div class="vendor_payout_create-inner">

        <fieldset>

            <legend><i class="mr-3 mdi mdi-earth"></i>{{ $regionFieldLabel }}</legend>

            <div class="form-group row width-100">

                <label class="col-3 control-label">{{ $regionFieldLabel }}</label>

                <div class="col-7">

                    <select id="region_assignment" class="form-control chosen-select" multiple="multiple">
                    </select>

                    <div class="form-text text-muted">

                        {{ $regionFieldHelp }}

                    </div>

                </div>

            </div>

        </fieldset>

    </div>

</div>
