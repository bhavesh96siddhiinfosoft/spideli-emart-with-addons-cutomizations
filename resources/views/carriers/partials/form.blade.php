{{-- Shared carrier form, used by both create and edit. --}}
<div class="row vendor_payout_create">
    <div class="vendor_payout_create-inner">

        <fieldset>
            <legend><i class="mr-3 mdi mdi-truck"></i>{{ trans('lang.carrier_info') }}</legend>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_name') }}<span class="required-field"></span></label>
                <div class="col-7">
                    <input type="text" class="form-control" id="name">
                    <div class="form-text text-muted">{{ trans('lang.carrier_name_help') }}</div>
                    <div id="error_name" class="err"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_code') }}<span class="required-field"></span></label>
                <div class="col-7">
                    <input type="text" class="form-control" id="code">
                    <div class="form-text text-muted">{{ trans('lang.carrier_code_help') }}</div>
                    <div id="error_code" class="err"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_phone') }}</label>
                <div class="col-7">
                    <div class="phone-box position-relative" id="phone-box">
                        <?php
                        $carrierCountries = (array) json_decode(file_get_contents(public_path('countriesdata.json')));
                        $carrierCountryList = [];
                        $carrierCountryFlags = [];
                        foreach ($carrierCountries as $carrierCountry) {
                            $carrierCountryList[$carrierCountry->phoneCode] = $carrierCountry;
                            $carrierCountryFlags[$carrierCountry->phoneCode] = $carrierCountry->code;
                        }
                        ?>
                        <select name="country" id="country_selector">
                            @foreach ($carrierCountryList as $carrierPhoneCode => $carrierCountry)
                                <option code="{{ $carrierCountry->code }}" value="{{ $carrierPhoneCode }}">
                                    +{{ $carrierCountry->phoneCode }} {{ $carrierCountry->countryName }}</option>
                            @endforeach
                        </select>
                        <input type="text" class="form-control" id="phone">
                        <div id="error_phone" class="err"></div>
                        <div class="form-text text-muted">{{ trans('lang.carrier_phone_help') }}</div>
                    </div>
                </div>
            </div>

            {{-- Keyed by dialling code, matching the store and driver forms, so
                 the flag lookup in formatState works the same way. --}}
            <script type="text/javascript">
                var carrierCountryFlags = JSON.parse('<?php echo json_encode($carrierCountryFlags); ?>');
            </script>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_email') }}</label>
                <div class="col-7">
                    <input type="text" class="form-control" id="email">
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_logo') }}</label>
                <div class="col-7">
                    <input type="file" id="carrierLogo" accept="image/*" onChange="handleCarrierLogoSelect(event)">
                    <div class="placeholder_img_thumb carrier_logo_thumb"></div>
                    <div id="uploading_logo" class="text-muted"></div>
                    <div class="form-text text-muted">{{ trans('lang.carrier_logo_help') }}</div>
                </div>
            </div>

            <div class="form-group row width-100">
                <label class="col-3 control-label">{{ trans('lang.carrier_regions') }}</label>
                <div class="col-7">
                    <select id="region_ids" class="form-control chosen-select" multiple="multiple"></select>
                    <div class="form-text text-muted">{{ trans('lang.carrier_regions_help') }}</div>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend><i class="mr-3 mdi mdi-file-document"></i>{{ trans('lang.carrier_identification') }}</legend>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_operating_licence') }}</label>
                <div class="col-7">
                    <input type="text" class="form-control" id="operating_licence">
                    <div class="form-text text-muted">{{ trans('lang.carrier_operating_licence_help') }}</div>
                    <input type="file" class="pb-2 pt-2" onChange="handleCarrierDocUpload(event, 'operatingLicenceFile')">
                    <div id="uploading_operatingLicenceFile" class="text-muted"></div>
                    <div id="uploaded_operatingLicenceFile"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_commercial_register') }}</label>
                <div class="col-7">
                    <input type="text" class="form-control" id="commercial_register">
                    <div class="form-text text-muted">{{ trans('lang.carrier_document_file_help') }}</div>
                    <input type="file" class="pb-2 pt-2" onChange="handleCarrierDocUpload(event, 'commercialRegisterFile')">
                    <div id="uploading_commercialRegisterFile" class="text-muted"></div>
                    <div id="uploaded_commercialRegisterFile"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_unique_id') }}</label>
                <div class="col-7">
                    <input type="text" class="form-control" id="unique_id_number">
                    <div class="form-text text-muted">{{ trans('lang.carrier_document_file_help') }}</div>
                    <input type="file" class="pb-2 pt-2" onChange="handleCarrierDocUpload(event, 'uniqueIdNumberFile')">
                    <div id="uploading_uniqueIdNumberFile" class="text-muted"></div>
                    <div id="uploaded_uniqueIdNumberFile"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <div class="form-check">
                    <input type="checkbox" id="is_verified">
                    <label class="col-6 control-label" for="is_verified">{{ trans('lang.carrier_documents_verified') }}</label>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend><i class="mr-3 mdi mdi-cash"></i>{{ trans('lang.carrier_pricing') }}</legend>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_base_charge') }}</label>
                <div class="col-7">
                    <input type="number" step="any" class="form-control" id="base_charge">
                    <div class="form-text text-muted">{{ trans('lang.carrier_base_charge_help') }}</div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_per_km_charge') }}</label>
                <div class="col-7">
                    <input type="number" step="any" class="form-control" id="per_km_charge">
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_per_kg_charge') }}</label>
                <div class="col-7">
                    <input type="number" step="any" class="form-control" id="per_kg_charge">
                    <div class="form-text text-muted">{{ trans('lang.carrier_per_kg_charge_help') }}</div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_minimum_charge') }}</label>
                <div class="col-7">
                    <input type="number" step="any" class="form-control" id="minimum_charge">
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend><i class="mr-3 mdi mdi-clock"></i>{{ trans('lang.carrier_delivery_time_conditions') }}</legend>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_min_delivery_time') }}</label>
                <div class="col-7">
                    <input type="number" class="form-control" id="min_delivery_time">
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_max_delivery_time') }}</label>
                <div class="col-7">
                    <input type="number" class="form-control" id="max_delivery_time">
                    <div id="error_delivery_time" class="err"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_delivery_time_unit') }}</label>
                <div class="col-7">
                    <select class="form-control" id="delivery_time_unit">
                        <option value="hours">{{ trans('lang.carrier_unit_hours') }}</option>
                        <option value="days">{{ trans('lang.carrier_unit_days') }}</option>
                    </select>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.carrier_max_weight') }}</label>
                <div class="col-7">
                    <input type="number" step="any" class="form-control" id="max_weight">
                    <div class="form-text text-muted">{{ trans('lang.carrier_max_weight_help') }}</div>
                </div>
            </div>

            <div class="form-group row width-100">
                <label class="col-3 control-label">{{ trans('lang.carrier_conditions') }}</label>
                <div class="col-7">
                    <textarea class="form-control" id="conditions" rows="3"></textarea>
                    <div class="form-text text-muted">{{ trans('lang.carrier_conditions_help') }}</div>
                </div>
            </div>

            <div class="form-group row width-100">
                <div class="form-check">
                    <input type="checkbox" id="publish" checked>
                    <label class="col-3 control-label" for="publish">{{ trans('lang.status') }}</label>
                </div>
            </div>
        </fieldset>

    </div>
</div>
