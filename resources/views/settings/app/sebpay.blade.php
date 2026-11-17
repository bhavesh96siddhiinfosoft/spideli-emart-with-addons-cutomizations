@extends('layouts.app')

@section('content')
{{-- #92: Sebpay, one page per country (settings/sebpay_{country}_settings). API: the official Sebpay SDK
     (github.com/mjims/sebpay-sdk-py): X-Public-Key / X-Secret-Key; pk_test_ keys go to the sandbox,
     pk_live_ keys to live; hosted checkout sessions; webhooks signed with HMAC-SHA256 of the secret key.
     Everything is saved in the settings document, like the other gateways, with its existing field names.
     The return and callback links are sent with each payment, so nothing has to be set in the Sebpay
     dashboard. --}}
@php
    $webBase = preg_replace('#/admin/?$#', '', rtrim(url('/'), '/'));
@endphp
<div class="page-wrapper">
    <div class="card">
        <div class="payment-top-tab mt-3 mb-3">
            @include('settings.app.partials.payment_tabs', ['activeTab' => 'sebpay_' . $country])
        </div>

        <div class="card-body">
            <div class="error_top alert alert-danger" style="display:none"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{ $title }}</legend>

                        <div class="form-check width-100">
                            <input type="checkbox" id="enable_sebpay">
                            <label class="col-3 control-label" for="enable_sebpay">{{ trans('lang.app_setting_enable_sebpay') }}</label>
                        </div>
                        <div class="form-check width-100">
                            <input type="checkbox" id="sebpay_sandbox">
                            <label class="col-3 control-label" for="sebpay_sandbox">{{ trans('lang.app_setting_enable_sandbox_mode') }}</label>
                            <div class="form-text text-muted">{{ trans('lang.sebpay_sandbox_help') }}</div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.sebpay_public_key') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control sebpay_public_key" autocomplete="off">
                                <div class="form-text text-muted">{{ trans('lang.sebpay_keys_help') }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.sebpay_secret_key') }}</label>
                            <div class="col-7">
                                <input type="password" class="form-control sebpay_secret_key" autocomplete="new-password">
                                <div class="form-text text-muted">{{ trans('lang.sebpay_keys_help') }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.mycoolpay_currency') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" value="{{ $currency }}" readonly>
                                <div class="form-text text-muted">{{ trans('lang.sebpay_currency_help', ['currency' => $currency]) }}</div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>{{ trans('lang.sebpay_urls') }}</legend>
                        <div class="form-group row width-100">
                            <div class="col-12 form-text text-muted mb-2">{{ trans('lang.sebpay_urls_help') }}</div>
                        </div>
                        @foreach (['callbackUrl' => 'mycoolpay_callback_url', 'returnUrl' => 'sebpay_return_url'] as $field => $labelKey)
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.' . $labelKey) }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control" id="sebpay_{{ $field }}" readonly onclick="this.select()">
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
    var COUNTRY = {!! json_encode($country) !!};
    var CURRENCY = {!! json_encode($currency) !!};
    var ref = database.collection('settings').doc('sebpay_' + COUNTRY + '_settings');
    var webBase = {!! json_encode($webBase) !!};
    var regionCurrencies = {!! json_encode((object)($regionCurrencies ?? [])) !!};
    var callbackToken = '';

    function newToken() {
        var a = new Uint8Array(20);
        window.crypto.getRandomValues(a);
        return Array.prototype.map.call(a, function (b) { return ('0' + b.toString(16)).slice(-2); }).join('');
    }
    function links(token) {
        return {
            callbackUrl: webBase + '/sebpay/' + COUNTRY + '/callback/' + token,
            returnUrl: webBase + '/sebpay/' + COUNTRY + '/return'
        };
    }
    function showLinks() {
        var l = links(callbackToken);
        Object.keys(l).forEach(function (k) { $('#sebpay_' + k).val(l[k]); });
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
                $('#enable_sebpay').prop('checked', p.isEnabled === true || p.enable === true);
                $('#sebpay_sandbox').prop('checked', p.isSandbox === true);
                $('.sebpay_public_key').val(p.publicKey || '');
                $('.sebpay_secret_key').val(p.secretKey || '');
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
            var enable = $('#enable_sebpay').is(':checked');
            var sandbox = $('#sebpay_sandbox').is(':checked');
            var publicKey = $.trim($('.sebpay_public_key').val());
            var secretKey = $.trim($('.sebpay_secret_key').val());
            if (enable && (publicKey === '' || secretKey === '')) {
                showError({!! json_encode(trans('lang.sebpay_keys_required')) !!});
                return;
            }
            // Sebpay sends pk_test_ keys to its sandbox and pk_live_ keys to live: the switch must match.
            if (publicKey !== '' && ((sandbox && !/^pk_test_/.test(publicKey)) || (!sandbox && !/^pk_live_/.test(publicKey)))) {
                showError(sandbox ? {!! json_encode(trans('lang.sebpay_key_not_test')) !!} : {!! json_encode(trans('lang.sebpay_key_not_live')) !!});
                return;
            }
            var wrong = getRegionAssignment().filter(function (id) {
                var r = regionCurrencies[id];
                return r && r.currency !== '' && r.currency !== CURRENCY;
            }).map(function (id) { return regionCurrencies[id].name + ' (' + regionCurrencies[id].currency + ')'; });
            if (wrong.length) {
                showError({!! json_encode(trans('lang.sebpay_region_currency')) !!}.replace(':currency', CURRENCY).replace(':regions', wrong.join(', ')));
                return;
            }
            if (!callbackToken) {
                callbackToken = newToken();
            }
            jQuery("#data-table_processing").show();
            try {
                await ref.set(Object.assign({
                    gateway: 'sebpay',
                    name: {!! json_encode($title) !!},
                    countryCode: {!! json_encode(strtoupper($country)) !!},
                    currency: CURRENCY,
                    isEnabled: enable,
                    enable: enable,
                    isSandbox: sandbox,
                    publicKey: publicKey,
                    secretKey: secretKey,
                    regionIds: getRegionAssignment(),
                    callbackToken: callbackToken,
                    updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                }, links(callbackToken)), {merge: true});
                window.location.href = {!! json_encode(url('settings/payment/sebpay-' . $country)) !!};
            } catch (e) {
                jQuery("#data-table_processing").hide();
                showError({!! json_encode(trans('lang.mycoolpay_save_error')) !!} + ' ' + (e && e.message ? e.message : String(e)));
            }
        });
    });
</script>
@endsection
