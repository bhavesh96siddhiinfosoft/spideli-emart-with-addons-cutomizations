{{--
    Service group selector.

    The group a service appears under on the customer home screen, from
    "Document 1 details.pdf" page 9. Shared by section create and edit so the
    two can never drift apart.

    The options come from the `service_groups` collection, managed in
    Settings > Service Groups. Populated by loadServiceGroups(), defined in
    layouts/app.blade.php. Stored on the section as `serviceGroup`, holding the
    group's id - never its name, so renaming a group keeps its services.

    The wrapper class is load-bearing: the theme's global select2 rules pull
    any select2 out of flow, and public/css/admin.css undoes them for
    .service-group-box only.
--}}
<div class="form-group row width-50">
    <label class="col-3 control-label">{{ trans('lang.service_group') }}</label>
    <div class="col-12 service-group-box">
        <select name="service_group" id="service_group" class="form-control">
            <option value="">{{ trans('lang.service_group_none') }}</option>
        </select>
        <div class="form-text text-muted">{{ trans('lang.service_group_help') }}</div>
    </div>
</div>
