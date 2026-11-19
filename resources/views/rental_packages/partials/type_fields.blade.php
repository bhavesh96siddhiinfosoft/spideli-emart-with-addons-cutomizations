{{-- #112: package type. "By the day" packages are booked for one or more days; the price per day,
     the km per day and 24 hours are multiplied by the number of days the customer books. --}}
<div class="form-group row width-50">
    <label class="col-3 control-label">{{ trans('lang.rental_package_type') }}</label>
    <div class="col-7">
        <select class="form-control" id="packageType" name="packageType">
            <option value="hourly">{{ trans('lang.rental_package_type_hourly') }}</option>
            <option value="daily">{{ trans('lang.rental_package_type_daily') }}</option>
        </select>
        <div class="form-text text-muted">{{ trans('lang.rental_package_type_help') }}</div>
    </div>
</div>
<div class="form-group row width-50 spd-rental-daily-only" style="display:none">
    <label class="col-3 control-label">{{ trans('lang.rental_min_days') }}</label>
    <div class="col-7">
        <input type="number" class="form-control" id="minDays" name="minDays" min="1" max="365" step="1" value="1">
        <div class="form-text text-muted">{{ trans('lang.rental_min_days_help') }}</div>
    </div>
</div>
<div class="form-group row width-50 spd-rental-daily-only" style="display:none">
    <label class="col-3 control-label">{{ trans('lang.rental_max_days') }}</label>
    <div class="col-7">
        <input type="number" class="form-control" id="maxDays" name="maxDays" min="1" max="365" step="1" value="30">
        <div class="form-text text-muted">{{ trans('lang.rental_max_days_help') }}</div>
    </div>
</div>
