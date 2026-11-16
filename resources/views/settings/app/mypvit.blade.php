@extends('layouts.app')

@section('content')
{{-- #92: MyPVIT Gabon (PVit, docs.mypvit.pro). Everything is saved in settings/mypvit_settings, like the
     other gateways. Field names are those already used in that document. Each PVit API has its own
     "URL code" (merchant space > APIs); the callback and redirect pages are registered in the merchant
     space > Urls, which gives the codes entered here. --}}
@php
    $webBase = preg_replace('#/admin/?$#', '', rtrim(url('/'), '/'));
@endphp
<div class="page-wrapper">
    <div class="card">
        <div class="payment-top-tab mt-3 mb-3">
            @include('settings.app.partials.payment_tabs', ['activeTab' => 'mypvit'])
        </div>

        <div class="card-body">
            <div class="error_top alert alert-danger" style="display:none"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.app_setting_mypvit') }}</legend>

                        <div class="form-check width-100">
                            <input type="checkbox" class="enable_mypvit" id="enable_mypvit">
                            <label class="col-3 control-label" for="enable_mypvit">{{ trans('lang.app_setting_enable_mypvit') }}</label>
                        </div>
                        <div class="form-check width-100">
                            <input type="checkbox" class="mypvit_sandbox" id="mypvit_sandbox">
                            <label class="col-3 control-label" for="mypvit_sandbox">{{ trans('lang.app_setting_enable_sandbox_mode') }}</label>
                            <div class="form-text text-muted">{{ trans('lang.mypvit_sandbox_help') }}</div>
                        </div>

                        @foreach ([
                            'accountCode' => ['text', 'mypvit_account_code', 'mypvit_account_code_help'],
                            'password' => ['password', 'mypvit_password', 'mypvit_password_help'],
                            'secretCode' => ['text', 'mypvit_secret_code', 'mypvit_url_code_help'],
                            'linkCode' => ['text', 'mypvit_link_code', 'mypvit_url_code_help'],
                            'statusCode' => ['text', 'mypvit_status_code', 'mypvit_url_code_help'],
                        ] as $field => [$type, $label, $help])
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.' . $label) }}</label>
                                <div class="col-7">
                                    <input type="{{ $type }}" class="form-control mypvit_field" data-field="{{ $field }}" autocomplete="{{ $type === 'password' ? 'new-password' : 'off' }}">
                                    <div class="form-text text-muted">{{ trans('lang.' . $help) }}</div>
                                </div>
                            </div>
                        @endforeach

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.mypvit_owner_charge') }}</label>
                            <div class="col-7">
                                <select class="form-control mypvit_owner_charge">
                                    <option value="CUSTOMER">{{ trans('lang.mypvit_charge_customer') }}</option>
                                    <option value="MERCHANT">{{ trans('lang.mypvit_charge_merchant') }}</option>
                                </select>
                                <div class="form-text text-muted">{{ trans('lang.mypvit_owner_charge_help') }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.mypvit_operator_owner_charge') }}</label>
                            <div class="col-7">
                                <select class="form-control mypvit_operator_owner_charge">
                                    <option value="CUSTOMER">{{ trans('lang.mypvit_charge_customer') }}</option>
                                    <option value="MERCHANT">{{ trans('lang.mypvit_charge_merchant') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.mycoolpay_currency') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" value="XAF" readonly>
                                <div class="form-text text-muted">{{ trans('lang.mypvit_currency_help') }}</div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>{{ trans('lang.mypvit_urls') }}</legend>
                        <div class="form-group row width-100">
                            <div class="col-12 form-text text-muted mb-2">{{ trans('lang.mypvit_urls_help') }}</div>
                        </div>
                        @foreach ([
                            ['callbackUrl', 'mycoolpay_callback_url', 'callbackCode', 'mypvit_callback_code'],
                            ['successUrl', 'mycoolpay_success_url', 'successRedirectCode', 'mypvit_success_code'],
                            ['failureUrl', 'mycoolpay_failure_url', 'failedRedirectCode', 'mypvit_failed_code'],
                        ] as [$urlField, $urlLabel, $codeField, $codeLabel])
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.' . $urlLabel) }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control" id="mypvit_{{ $urlField }}" readonly onclick="this.select()">
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.' . $codeLabel) }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control mypvit_field" data-field="{{ $codeField }}" autocomplete="off">
                                </div>
                            </div>
                        @endforeach
                    </fieldset>

                    <fieldset>
                        <legend>{{ trans('lang.withdraw_setting') }}</legend>
                        <div class="form-check width-100">
                            <div class="form-text text-muted">{!! trans('lang.withdraw_setting_not_available_help') !!}</div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>

        @include('settings.app.partials.region_assignment')

        <div class="form-group col-12 text-center btm-btn" style="margin-bottom:inherit;">
            <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
            <a href="{{ url('/dashboard') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();
    var ref = database.collection('settings').doc('mypvit_settings');
    var webBase = {!! json_encode($webBase) !!};
    /* regionId => {name, currency}: PVit pays in XAF. */
    var regionCurrencies = {!! json_encode((object)($regionCurrencies ?? [])) !!};
    var callbackToken = '';

    function newToken() {
        var a = new Uint8Array(20);
        window.crypto.getRandomValues(a);
        return Array.prototype.map.call(a, function (b) { return ('0' + b.toString(16)).slice(-2); }).join('');
    }
    function links(token) {
        return {
            callbackUrl: webBase + '/mypvit/callback/' + token,
            successUrl: webBase + '/mypvit/return/success',
            failureUrl: webBase + '/mypvit/return/failure'
        };
    }
    function showLinks() {
        var l = links(callbackToken);
        Object.keys(l).forEach(function (k) { $('#mypvit_' + k).val(l[k]); });
    }
    function showError(msg) {
        $('.error_top').text(msg).show();
        window.scrollTo(0, 0);
    }

    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        ref.get().then(async function (snap) {
            var p = snap.exists ? snap.data() : null;
            await loadRegionAssignment(p);
            if (p) {
                $('#enable_mypvit').prop('checked', p.isEnabled === true || p.enable === true);
                $('#mypvit_sandbox').prop('checked', p.isSandbox === true);
                $('.mypvit_field').each(function () { $(this).val(p[$(this).data('field')] || ''); });
                $('.mypvit_owner_charge').val(p.ownerCharge || 'CUSTOMER');
                $('.mypvit_operator_owner_charge').val(p.operatorOwnerCharge || p.ownerCharge || 'CUSTOMER');
            }
            callbackToken = (p && /^[a-f0-9]{40}$/.test(p.callbackToken || '')) ? p.callbackToken : newToken();
            showLinks();
            jQuery("#data-table_processing").hide();
        }).catch(function (e) {
            jQuery("#data-table_processing").hide();
            showError(e.message || String(e));
        });

        $('.edit-setting-btn').click(async function () {
            $('.error_top').hide();
            var enable = $('#enable_mypvit').is(':checked');
            var data = {};
            $('.mypvit_field').each(function () { data[$(this).data('field')] = $.trim($(this).val()); });
            if (enable) {
                var missing = ['accountCode', 'password', 'secretCode', 'linkCode', 'statusCode', 'callbackCode', 'successRedirectCode', 'failedRedirectCode']
                    .filter(function (k) { return !data[k]; });
                if (missing.length) {
                    showError({!! json_encode(trans('lang.mypvit_fields_required')) !!});
                    return;
                }
                if (!/^ACC_/i.test(data.accountCode)) {
                    showError({!! json_encode(trans('lang.mypvit_account_code_invalid')) !!});
                    return;
                }
            }
            var wrong = getRegionAssignment().filter(function (id) {
                var r = regionCurrencies[id];
                return r && r.currency !== '' && r.currency !== 'XAF';
            }).map(function (id) { return regionCurrencies[id].name + ' (' + regionCurrencies[id].currency + ')'; });
            if (wrong.length) {
                showError({!! json_encode(trans('lang.mypvit_region_currency')) !!}.replace(':regions', wrong.join(', ')));
                return;
            }
            if (!callbackToken) {
                callbackToken = newToken();
            }
            jQuery("#data-table_processing").show();
            try {
                await ref.set(Object.assign(data, {
                    gateway: 'mypvit',
                    name: 'MyPVIT',
                    countryCode: 'GA',
                    currency: 'XAF',
                    isEnabled: enable,
                    enable: enable,
                    isSandbox: $('#mypvit_sandbox').is(':checked'),
                    ownerCharge: $('.mypvit_owner_charge').val(),
                    operatorOwnerCharge: $('.mypvit_operator_owner_charge').val(),
                    regionIds: getRegionAssignment(),
                    callbackToken: callbackToken,
                    updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                }, links(callbackToken)), {merge: true});
                window.location.href = {!! json_encode(url('settings/payment/mypvit')) !!};
            } catch (e) {
                jQuery("#data-table_processing").hide();
                showError({!! json_encode(trans('lang.mycoolpay_save_error')) !!} + ' ' + (e && e.message ? e.message : String(e)));
            }
        });
    });
</script>
@endsection
