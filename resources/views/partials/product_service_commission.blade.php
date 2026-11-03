{{-- Admin Product & Service Commission (client point 87, 3 Nov 2026): a second
     commission, taken from the store's / provider's earnings; the customer's
     price does not change. Filled and read by pscFill() / pscRead() in
     layouts/app. Spec: docs/APP-SPEC-ADMIN-PRODUCT-SERVICE-COMMISSION.md. --}}
<fieldset class="product-service-commission">
    <legend>{{ trans('lang.psc_title') }}</legend>
    <div class="form-check width-100">
        <label style="font-size: 15px;">{{ $note ?? trans('lang.psc_note') }}</label>
    </div>
    <div class="form-check width-100">
        <input type="checkbox" class="form-check-inline" id="psc_enable">
        <label class="col-5 control-label" for="psc_enable">{{ trans('lang.psc_enable') }}</label>
    </div>
    <div class="form-fields" id="psc_fields" style="display:none">
        <div class="form-group row width-50">
            <label class="col-4 control-label">{{ trans('lang.commission_type') }}</label>
            <div class="col-7">
                <select class="form-control" id="psc_type">
                    <option value="percentage">{{ trans('lang.psc_percentage') }}</option>
                    <option value="fixed">{{ trans('lang.psc_fixed') }}</option>
                </select>
            </div>
        </div>
        <div class="form-group row width-50">
            <label class="col-4 control-label">{{ trans('lang.admin_commission_value') }}</label>
            <div class="col-7">
                <input type="number" class="form-control" id="psc_value" min="0" step="any">
            </div>
        </div>
    </div>
</fieldset>
