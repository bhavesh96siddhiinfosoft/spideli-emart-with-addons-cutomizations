@extends('layouts.app')

@section('content')
{{-- #92: PI-SPI (BCEAO instant payments, UEMOA, XOF). API Business of the client's participant
     (developer.pispi.bceao.int): OAuth2 client credentials (token server URL, client ID, client secret),
     an x-api-key header, and in production an mTLS client certificate issued through the BCEAO
     (the sandbox has no mTLS). A payment is a payment request (RTP, category 521 e-commerce) sent from
     our account alias (payeAlias) to the customer's alias; the website then reads its status.
     Everything is saved in settings/pispi_settings, like the other gateways. --}}
<div class="page-wrapper">
    <div class="card">
        <div class="payment-top-tab mt-3 mb-3">
            @include('settings.app.partials.payment_tabs', ['activeTab' => 'pispi'])
        </div>

        <div class="card-body">
            <div class="error_top alert alert-danger" style="display:none"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.pispi') }}</legend>

                        <div class="form-check width-100">
                            <input type="checkbox" id="enable_pispi">
                            <label class="col-3 control-label" for="enable_pispi">{{ trans('lang.app_setting_enable_pispi') }}</label>
                        </div>
                        <div class="form-check width-100">
                            <input type="checkbox" id="pispi_sandbox">
                            <label class="col-3 control-label" for="pispi_sandbox">{{ trans('lang.app_setting_enable_sandbox_mode') }}</label>
                            <div class="form-text text-muted">{{ trans('lang.pispi_sandbox_help') }}</div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.pispi_api_url') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control pispi_api_url" autocomplete="off" placeholder="{{ \App\Http\Controllers\SettingsController::PISPI_SANDBOX_API }}">
                                <div class="form-text text-muted">{{ trans('lang.pispi_api_url_help', ['url' => \App\Http\Controllers\SettingsController::PISPI_SANDBOX_API]) }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.pispi_token_url') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control pispi_token_url" autocomplete="off" placeholder="https://….auth.eu-west-1.amazoncognito.com/oauth2/token">
                                <div class="form-text text-muted">{{ trans('lang.pispi_credentials_help') }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.pispi_client_id') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control pispi_client_id" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.pispi_client_secret') }}</label>
                            <div class="col-7">
                                <input type="password" class="form-control pispi_client_secret" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.pispi_api_key') }}</label>
                            <div class="col-7">
                                <input type="password" class="form-control pispi_api_key" autocomplete="new-password">
                                <div class="form-text text-muted">{{ trans('lang.pispi_credentials_help') }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.pispi_paye_alias') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control pispi_paye_alias" autocomplete="off" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                                <div class="form-text text-muted">{{ trans('lang.pispi_paye_alias_help') }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.mycoolpay_currency') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" value="XOF" readonly>
                                <div class="form-text text-muted">{{ trans('lang.pispi_currency_help') }}</div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="pispi_mtls">
                        <legend>{{ trans('lang.pispi_mtls') }}</legend>
                        <div class="form-group row width-100">
                            <div class="col-12 form-text text-muted mb-2">{{ trans('lang.pispi_mtls_help') }}</div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.pispi_mtls_cert') }}</label>
                            <div class="col-7">
                                <textarea class="form-control pispi_mtls_cert" rows="4" spellcheck="false" placeholder="-----BEGIN CERTIFICATE-----"></textarea>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.pispi_mtls_key') }}</label>
                            <div class="col-7">
                                <textarea class="form-control pispi_mtls_key" rows="4" spellcheck="false" placeholder="-----BEGIN PRIVATE KEY-----" style="-webkit-text-security:disc"></textarea>
                            </div>
                        </div>
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
    var ref = database.collection('settings').doc('pispi_settings');
    var CURRENCY = 'XOF';
    var SANDBOX_API = {!! json_encode(\App\Http\Controllers\SettingsController::PISPI_SANDBOX_API) !!};
    var regionCurrencies = {!! json_encode((object)($regionCurrencies ?? [])) !!};
    var ALIAS = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;

    function showError(msg) {
        $('.error_top').text(msg).show();
        window.scrollTo(0, 0);
    }
    function isHttps(u) {
        return /^https:\/\/[^\s\/]+\.[^\s]+$/i.test(u);
    }

    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        ref.get().then(async function (snap) {
            var p = snap.exists ? snap.data() : null;
            await loadRegionAssignment(p);
            if (p) {
                $('#enable_pispi').prop('checked', p.isEnabled === true || p.enable === true);
                $('#pispi_sandbox').prop('checked', p.isSandbox !== false);
                $('.pispi_api_url').val(p.apiUrl || '');
                $('.pispi_token_url').val(p.tokenUrl || '');
                $('.pispi_client_id').val(p.clientId || '');
                $('.pispi_client_secret').val(p.clientSecret || '');
                $('.pispi_api_key').val(p.apiKey || '');
                $('.pispi_paye_alias').val(p.payeAlias || '');
                $('.pispi_mtls_cert').val(p.mtlsCert || '');
                $('.pispi_mtls_key').val(p.mtlsKey || '');
            } else {
                $('#pispi_sandbox').prop('checked', true);
            }
            jQuery("#data-table_processing").hide();
        }).catch(function (e) {
            jQuery("#data-table_processing").hide();
            showError(e.message || String(e));
        });

        $('.edit-setting-btn').click(async function () {
            $('.error_top').hide();
            var enable = $('#enable_pispi').is(':checked');
            var sandbox = $('#pispi_sandbox').is(':checked');
            var v = {
                apiUrl: $.trim($('.pispi_api_url').val()).replace(/\/+$/, ''),
                tokenUrl: $.trim($('.pispi_token_url').val()),
                clientId: $.trim($('.pispi_client_id').val()),
                clientSecret: $.trim($('.pispi_client_secret').val()),
                apiKey: $.trim($('.pispi_api_key').val()),
                payeAlias: $.trim($('.pispi_paye_alias').val()).toLowerCase(),
                mtlsCert: $.trim($('.pispi_mtls_cert').val()),
                mtlsKey: $.trim($('.pispi_mtls_key').val())
            };
            if (sandbox && v.apiUrl === '') {
                v.apiUrl = SANDBOX_API;
            }
            if (enable && (v.apiUrl === '' || v.tokenUrl === '' || v.clientId === '' || v.clientSecret === '' || v.apiKey === '' || v.payeAlias === '')) {
                showError({!! json_encode(trans('lang.pispi_fields_required')) !!});
                return;
            }
            if ((v.apiUrl !== '' && !isHttps(v.apiUrl)) || (v.tokenUrl !== '' && !isHttps(v.tokenUrl))) {
                showError({!! json_encode(trans('lang.pispi_url_https')) !!});
                return;
            }
            if (v.payeAlias !== '' && !ALIAS.test(v.payeAlias)) {
                showError({!! json_encode(trans('lang.pispi_alias_invalid')) !!});
                return;
            }
            // Production needs the BCEAO mTLS certificate and its private key (PEM).
            if (enable && !sandbox && (!/-----BEGIN CERTIFICATE-----/.test(v.mtlsCert) || !/-----BEGIN (RSA |EC |ENCRYPTED )?PRIVATE KEY-----/.test(v.mtlsKey))) {
                showError({!! json_encode(trans('lang.pispi_mtls_required')) !!});
                return;
            }
            var wrong = getRegionAssignment().filter(function (id) {
                var r = regionCurrencies[id];
                return r && r.currency !== '' && r.currency !== CURRENCY;
            }).map(function (id) { return regionCurrencies[id].name + ' (' + regionCurrencies[id].currency + ')'; });
            if (wrong.length) {
                showError({!! json_encode(trans('lang.pispi_region_currency')) !!}.replace(':regions', wrong.join(', ')));
                return;
            }
            jQuery("#data-table_processing").show();
            try {
                await ref.set(Object.assign({
                    gateway: 'pispi',
                    name: 'PI-SPI',
                    currency: CURRENCY,
                    isEnabled: enable,
                    enable: enable,
                    isSandbox: sandbox,
                    regionIds: getRegionAssignment(),
                    updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                }, v), {merge: true});
                window.location.href = {!! json_encode(url('settings/payment/pispi')) !!};
            } catch (e) {
                jQuery("#data-table_processing").hide();
                showError({!! json_encode(trans('lang.mycoolpay_save_error')) !!} + ' ' + (e && e.message ? e.message : String(e)));
            }
        });
    });
</script>
@endsection
