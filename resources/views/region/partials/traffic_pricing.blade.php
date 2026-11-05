{{-- Point 103 (5 Nov 2026): prices that follow the traffic, per region.

     Saved as regions/{id}.trafficPricing:
       { enabled, services: [cab, parcel, delivery], capPercent,
         busyHours: [{ days: [1..7], start: "HH:MM", end: "HH:MM", multiplier }],
         busyZones: [{ zoneId, multiplier }] }
     The apps and the website read exactly these keys
     (docs/APP-SPEC-TRAFFIC-PRICING.md). The price per minute itself is set on
     each vehicle type, in the parcel defaults and carriers, and in the
     delivery charge settings.

     Shared by region/create and region/edit. --}}
<fieldset id="region_traffic_pricing">
    <legend><i class="mr-3 mdi mdi-traffic-light"></i>{{ trans('lang.traffic_pricing') }}</legend>

    <div class="form-group row width-100">
        <div class="col-12">
            <div class="form-text text-muted">{{ trans('lang.traffic_pricing_help') }}</div>
        </div>
    </div>

    <div class="form-group row width-100">
        <div class="form-check">
            <input type="checkbox" id="traffic_enabled">
            <label class="col-8 control-label" for="traffic_enabled">{{ trans('lang.traffic_enabled') }}</label>
        </div>
    </div>

    <div class="form-group row width-100">
        <label class="col-3 control-label">{{ trans('lang.traffic_services') }}</label>
        <div class="col-7">
            @foreach (['cab' => 'traffic_service_cab', 'parcel' => 'traffic_service_parcel', 'delivery' => 'traffic_service_delivery'] as $service => $label)
                {{-- The theme draws a checkbox on the label right after it (label for=). --}}
                <span class="d-inline-block mr-4"><input type="checkbox" class="traffic-service" id="traffic_service_{{ $service }}" value="{{ $service }}"><label for="traffic_service_{{ $service }}">{{ trans('lang.' . $label) }}</label></span>
            @endforeach
        </div>
    </div>

    <div class="form-group row width-50">
        <label class="col-3 control-label" for="traffic_cap_percent">{{ trans('lang.traffic_cap_percent') }}</label>
        <div class="col-7">
            <input type="number" step="any" min="0" class="form-control" id="traffic_cap_percent">
            <div class="form-text text-muted">{{ trans('lang.traffic_cap_percent_help') }}</div>
        </div>
    </div>

    <div class="form-group row width-100">
        <label class="col-3 control-label">{{ trans('lang.traffic_busy_hours') }}</label>
        <div class="col-9">
            <div class="form-text text-muted mb-2">{{ trans('lang.traffic_busy_hours_help') }}</div>
            <div id="traffic_busy_hours"></div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="traffic_add_hour"><i class="fa fa-plus"></i> {{ trans('lang.traffic_add_busy_hour') }}</button>
        </div>
    </div>

    <div class="form-group row width-100">
        <label class="col-3 control-label">{{ trans('lang.traffic_busy_zones') }}</label>
        <div class="col-9">
            <div class="form-text text-muted mb-2">{{ trans('lang.traffic_busy_zones_help') }}</div>
            <div id="traffic_busy_zones"></div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="traffic_add_zone"><i class="fa fa-plus"></i> {{ trans('lang.traffic_add_busy_zone') }}</button>
        </div>
    </div>

    <div class="form-group row width-100">
        <div class="col-12"><div id="error_traffic" class="err"></div></div>
    </div>
</fieldset>
