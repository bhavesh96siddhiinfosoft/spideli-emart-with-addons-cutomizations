{{--
    Point 104 - a driver bonus rule (driver_bonus_rules/{id}).
    Shared by create and edit; filled and read by partials/form_scripts.
    Field names: docs/APP-SPEC-DRIVER-BONUSES.md section 3.1.
    Checkboxes follow the theme rule: <input id=X> then <label for=X>.
--}}
<div class="row vendor_payout_create">
    <div class="vendor_payout_create-inner">
        <fieldset>
            <legend><i class="mr-3 mdi mdi-trophy"></i>{{ trans('lang.driver_bonus_rule') }}</legend>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.driver_bonus_name') }}</label>
                <div class="col-7">
                    <input type="text" class="form-control" id="rule_name">
                    <div class="form-text text-muted">{{ trans('lang.driver_bonus_name_help') }}</div>
                    <div id="error_name" class="err"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.region') }}</label>
                <div class="col-7">
                    <select id="region_id" class="form-control"></select>
                    <div class="form-text text-muted">{{ trans('lang.driver_bonus_region_help') }}</div>
                    <div id="error_region" class="err"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.driver_bonus_period') }}</label>
                <div class="col-7">
                    <select id="period" class="form-control">
                        <option value="weekly">{{ trans('lang.driver_bonus_weekly') }}</option>
                        <option value="monthly">{{ trans('lang.driver_bonus_monthly') }}</option>
                    </select>
                    <div class="form-text text-muted">{{ trans('lang.driver_bonus_period_help') }}</div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.driver_bonus_measure') }}</label>
                <div class="col-7">
                    <select id="measure" class="form-control">
                        <option value="trips">{{ trans('lang.driver_bonus_measure_trips') }}</option>
                        <option value="value">{{ trans('lang.driver_bonus_measure_value') }}</option>
                    </select>
                    <div class="form-text text-muted">{{ trans('lang.driver_bonus_measure_help') }}</div>
                </div>
            </div>

            <div class="form-group row width-100">
                <label class="col-3 control-label">{{ trans('lang.driver_bonus_services') }}</label>
                <div class="col-7">
                    <div class="driver-bonus-services">
                        @foreach (['cab' => 'driver_bonus_service_cab', 'delivery' => 'driver_bonus_service_delivery', 'parcel' => 'driver_bonus_service_parcel', 'rental' => 'driver_bonus_service_rental'] as $svc => $svcLabel)
                            <span class="form-check d-inline-block mr-4">
                                <input type="checkbox" class="bonus-service" id="svc_{{ $svc }}" value="{{ $svc }}">
                                <label class="control-label" for="svc_{{ $svc }}">{{ trans('lang.' . $svcLabel) }}</label>
                            </span>
                        @endforeach
                    </div>
                    <div class="form-text text-muted">{{ trans('lang.driver_bonus_services_help') }}</div>
                    <div id="error_services" class="err"></div>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend><i class="mr-3 mdi mdi-stairs"></i>{{ trans('lang.driver_bonus_tiers') }}</legend>
            <p class="text-muted">{{ trans('lang.driver_bonus_tiers_help') }}</p>
            <table class="table table-bordered driver-bonus-tiers">
                <thead>
                    <tr>
                        <th id="tier_target_head">{{ trans('lang.driver_bonus_target_trips') }}</th>
                        <th>{{ trans('lang.driver_bonus_amount') }} <span class="bonus-currency"></span></th>
                        <th style="width:90px"></th>
                    </tr>
                </thead>
                <tbody id="tier_rows"></tbody>
            </table>
            <button type="button" class="btn btn-outline-primary btn-sm" id="add_tier"><i class="mdi mdi-plus"></i> {{ trans('lang.driver_bonus_add_tier') }}</button>
            <div id="error_tiers" class="err mt-2"></div>
        </fieldset>

        <fieldset>
            <legend><i class="mr-3 mdi mdi-shield-check"></i>{{ trans('lang.driver_bonus_conditions') }}</legend>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.driver_bonus_min_rating') }}</label>
                <div class="col-7">
                    <input type="number" step="0.1" min="0" max="5" class="form-control" id="min_rating">
                    <div class="form-text text-muted">{{ trans('lang.driver_bonus_min_rating_help') }}</div>
                    <div id="error_min_rating" class="err"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.driver_bonus_max_cancellation') }}</label>
                <div class="col-7">
                    <input type="number" step="any" min="0" max="100" class="form-control" id="max_cancellation">
                    <div class="form-text text-muted">{{ trans('lang.driver_bonus_max_cancellation_help') }}</div>
                    <div id="error_max_cancellation" class="err"></div>
                </div>
            </div>

            <div class="form-group row width-50">
                <label class="col-3 control-label">{{ trans('lang.driver_bonus_min_level') }}</label>
                <div class="col-7">
                    <select id="min_level" class="form-control">
                        <option value="">{{ trans('lang.driver_bonus_level_all') }}</option>
                        <option value="silver">{{ trans('lang.driver_level_silver') }}</option>
                        <option value="gold">{{ trans('lang.driver_level_gold') }}</option>
                    </select>
                    <div class="form-text text-muted">{{ trans('lang.driver_bonus_min_level_help') }}</div>
                </div>
            </div>

            <div class="form-group row width-100">
                <div class="form-check width-100">
                    <input type="checkbox" id="publish" checked>
                    <label class="control-label" for="publish">{{ trans('lang.driver_bonus_publish') }}</label>
                </div>
            </div>
        </fieldset>
    </div>
</div>
