{{--
    Pickup point form fields.

    Shared by create and edit so the two cannot drift apart. The fields are
    exactly those the client listed in Spideli_upgrade.docx #26 - name, quarter,
    phone, town, location - plus the region and status every other collection
    here carries.

    Populated and read back by pickup_points/partials/form_scripts.blade.php.
--}}
<div class="row vendor_payout_create">
    <div class="vendor_payout_create-inner">
        <fieldset>
            <legend><i class="mr-3 mdi mdi-map-marker"></i>{{ trans('lang.pickup_point_plural') }}</legend>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.pickup_point_name') }}</label>
                <div class="col-7">
                    <input type="text" class="form-control" id="name">
                    <div class="form-text text-muted">{{ trans('lang.pickup_point_name_help') }}</div>
                    <div id="error_name" class="err"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.pickup_point_quarter') }}</label>
                <div class="col-7">
                    <input type="text" class="form-control" id="quarter">
                    <div class="form-text text-muted">{{ trans('lang.pickup_point_quarter_help') }}</div>
                    <div id="error_quarter" class="err"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.pickup_point_town') }}</label>
                <div class="col-7">
                    <input type="text" class="form-control" id="town">
                    <div id="error_town" class="err"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.pickup_point_region') }}</label>
                <div class="col-7 pickup-region-box">
                    <select id="region_id" class="form-control"></select>
                    <div class="form-text text-muted">{{ trans('lang.pickup_point_region_help') }}</div>
                    <div id="error_region" class="err"></div>
                </div>
            </div>

            {{-- The .phone-box wrapper is what the theme's global select2 rules
                 are written for: the picker is positioned absolutely inside it,
                 over the left of the input. Same markup as the carrier form. --}}
            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.pickup_point_phone') }}</label>
                <div class="col-7">
                    <div class="phone-box position-relative" id="phone-box">
                        <?php
                        $pickupCountries = (array) json_decode(file_get_contents(public_path('countriesdata.json')));
                        $pickupCountryList = [];
                        $pickupCountryFlags = [];

                        foreach ($pickupCountries as $pickupCountry) {
                            $pickupCountryList[$pickupCountry->phoneCode] = $pickupCountry;
                            $pickupCountryFlags[$pickupCountry->phoneCode] = $pickupCountry->code;
                        }
                        ?>
                        <select name="country" id="country_selector">
                            @foreach ($pickupCountryList as $pickupPhoneCode => $pickupCountry)
                                <option code="{{ $pickupCountry->code }}" value="{{ $pickupPhoneCode }}">
                                    +{{ $pickupCountry->phoneCode }} {{ $pickupCountry->countryName }}</option>
                            @endforeach
                        </select>
                        <input type="text" class="form-control" id="phone">
                        <div id="error_phone" class="err"></div>
                        <div class="form-text text-muted">{{ trans('lang.pickup_point_phone_help') }}</div>
                    </div>
                </div>
            </div>

            {{-- Keyed by dialling code, so the flag can be found from the
                 selected value. --}}
            <script>
                var pickupCountryFlags = <?php echo json_encode($pickupCountryFlags); ?>;
            </script>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.user_latitude') }}</label>
                <div class="col-7">
                    <input type="number" step="any" class="form-control" id="latitude">
                    <div id="error_latitude" class="err"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.user_longitude') }}</label>
                <div class="col-7">
                    <input type="number" step="any" class="form-control" id="longitude">
                    <div class="form-text text-muted">{{ trans('lang.pickup_point_location_help') }}</div>
                    <div id="error_longitude" class="err"></div>
                </div>
            </div>

            <div class="form-group row width-100">
                <div class="form-check width-100">
                    <input type="checkbox" id="publish" checked>
                    <label class="control-label" for="publish">{{ trans('lang.status') }}</label>
                </div>
            </div>
        </fieldset>
    </div>
</div>
