@extends('layouts.app')

@section('content')
{{-- #92: MyCoolPay Cameroun (my-coolpay.com). API: merchant API documentation of 14 Oct 2021 and the
     official PHP SDK. Everything is saved in settings/mycoolpay_settings, like the other gateways:
     keys, switches, regions, the callback token and the four links set in the MyCoolPay merchant space. --}}
@php
    $webBase = preg_replace('#/admin/?$#', '', rtrim(url('/'), '/'));
@endphp
<div class="page-wrapper">
    <div class="card">
        <div class="payment-top-tab mt-3 mb-3">
            @include('settings.app.partials.payment_tabs', ['activeTab' => 'mycoolpay'])
        </div>

        <div class="card-body">
            <div class="error_top alert alert-danger" style="display:none"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.app_setting_mycoolpay') }}</legend>

                        <div class="form-check width-100">
                            <input type="checkbox" class="enable_mycoolpay" id="enable_mycoolpay">
                            <label class="col-3 control-label" for="enable_mycoolpay">{{ trans('lang.app_setting_enable_mycoolpay') }}</label>
                        </div>

                        <div class="form-group row width-100">
                            <div class="col-12">
                                <div class="alert alert-info mb-0">{{ trans('lang.mycoolpay_no_sandbox') }}</div>
                            </div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.mycoolpay_public_key') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control mycoolpay_public_key" autocomplete="off">
                                <div class="form-text text-muted">{{ trans('lang.mycoolpay_public_key_help') }}</div>
                            </div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.mycoolpay_private_key') }}</label>
                            <div class="col-7">
                                <input type="password" class="form-control mycoolpay_private_key" autocomplete="new-password">
                                <div class="form-text text-muted">{{ trans('lang.mycoolpay_public_key_help') }}</div>
                            </div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.mycoolpay_currency') }}</label>
                            <div class="col-7">
                                <select class="form-control mycoolpay_currency">
                                    <option value="XAF">XAF</option>
                                    <option value="EUR">EUR</option>
                                </select>
                                <div class="form-text text-muted">{{ trans('lang.mycoolpay_currency_help') }}</div>
                            </div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.mycoolpay_mode') }}</label>
                            <div class="col-7">
                                <select class="form-control mycoolpay_mode">
                                    <option value="paylink">{{ trans('lang.mycoolpay_mode_paylink') }}</option>
                                    <option value="payin">{{ trans('lang.mycoolpay_mode_payin') }}</option>
                                </select>
                                <div class="form-text text-muted">{{ trans('lang.mycoolpay_mode_help') }}</div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>{{ trans('lang.mycoolpay_urls') }}</legend>
                        <div class="form-group row width-100">
                            <div class="col-12 form-text text-muted mb-2">{{ trans('lang.mycoolpay_urls_help') }}</div>
                        </div>
                        @foreach (['callbackUrl' => 'mycoolpay_callback_url', 'successUrl' => 'mycoolpay_success_url',
                                   'cancelUrl' => 'mycoolpay_cancel_url', 'failureUrl' => 'mycoolpay_failure_url'] as $field => $labelKey)
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.' . $labelKey) }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control mycoolpay_link" id="mycoolpay_{{ $field }}" readonly onclick="this.select()">
                                </div>
                            </div>
                        @endforeach
                    </fieldset>

                    <fieldset>
                        <legend>{{ trans('lang.withdraw_setting') }}</legend>
                        <div class="form-check width-100">
                            <input type="checkbox" class="mycoolpay_withdraw_enable" id="mycoolpay_withdraw_enable">
                            <label class="col-3 control-label" for="mycoolpay_withdraw_enable">{{ trans('lang.app_setting_enable_mycoolpay') }}</label>
                            <div class="form-text text-muted">{{ trans('lang.mycoolpay_withdraw_help') }}</div>
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
    var ref = database.collection('settings').doc('mycoolpay_settings');
    var webBase = {!! json_encode($webBase) !!};
    /* The callback token already set in the MyCoolPay merchant space (kept, so that link stays valid). */
    var existingToken = {!! json_encode($existingToken ?? '') !!};
    var callbackToken = '';
    /* regionId => {name, currency}: MyCoolPay takes XAF only (and EUR when chosen below). */
    var regionCurrencies = {!! json_encode((object)($regionCurrencies ?? [])) !!};

    function newToken() {
        var a = new Uint8Array(20);
        window.crypto.getRandomValues(a);
        return Array.prototype.map.call(a, function (b) { return ('0' + b.toString(16)).slice(-2); }).join('');
    }

    function links(token) {
        return {
            callbackUrl: webBase + '/mycoolpay/callback/' + token,
            successUrl: webBase + '/mycoolpay/return/success',
            cancelUrl: webBase + '/mycoolpay/return/cancel',
            failureUrl: webBase + '/mycoolpay/return/failure'
        };
    }

    function showLinks() {
        var l = links(callbackToken);
        Object.keys(l).forEach(function (k) { $('#mycoolpay_' + k).val(l[k]); });
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
                $('#enable_mycoolpay').prop('checked', p.isEnabled === true);
                $('#mycoolpay_withdraw_enable').prop('checked', p.isWithdrawEnabled === true);
                $('.mycoolpay_public_key').val(p.publicKey || '');
                $('.mycoolpay_private_key').val(p.privateKey || '');
                $('.mycoolpay_currency').val(p.currency || 'XAF');
                $('.mycoolpay_mode').val(p.paymentMode || 'paylink');
            }
            callbackToken = (p && /^[a-f0-9]{40}$/.test(p.callbackToken || '')) ? p.callbackToken
                : (/^[a-f0-9]{40}$/.test(existingToken) ? existingToken : newToken());
            showLinks();
            jQuery("#data-table_processing").hide();
        }).catch(function (e) {
            jQuery("#data-table_processing").hide();
            showError(e.message || String(e));
        });

        $('.edit-setting-btn').click(async function () {
            $('.error_top').hide();
            var enable = $('#enable_mycoolpay').is(':checked');
            var publicKey = $.trim($('.mycoolpay_public_key').val());
            var privateKey = $.trim($('.mycoolpay_private_key').val());
            if (enable && (publicKey === '' || privateKey === '')) {
                showError({!! json_encode(trans('lang.mycoolpay_keys_required')) !!});
                return;
            }
            var allowedCurrencies = $('.mycoolpay_currency').val() === 'EUR' ? ['XAF', 'EUR'] : ['XAF'];
            var wrong = getRegionAssignment().filter(function (id) {
                var r = regionCurrencies[id];
                return r && r.currency !== '' && allowedCurrencies.indexOf(r.currency) === -1;
            }).map(function (id) { return regionCurrencies[id].name + ' (' + regionCurrencies[id].currency + ')'; });
            if (wrong.length) {
                showError({!! json_encode(trans('lang.mycoolpay_region_currency')) !!}.replace(':regions', wrong.join(', ')));
                return;
            }
            if (!callbackToken) {
                callbackToken = newToken();
            }
            jQuery("#data-table_processing").show();
            try {
                await ref.set(Object.assign({
                    gateway: 'mycoolpay',
                    name: 'MyCoolPay',
                    isEnabled: enable,
                    enable: enable,   // the field an earlier app version of this document used
                    isSandbox: false, // MyCoolPay has no sandbox
                    isWithdrawEnabled: $('#mycoolpay_withdraw_enable').is(':checked'),
                    publicKey: publicKey,
                    privateKey: privateKey,
                    currency: $('.mycoolpay_currency').val(),
                    paymentMode: $('.mycoolpay_mode').val(),
                    regionIds: getRegionAssignment(),
                    callbackToken: callbackToken,
                    updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                }, links(callbackToken)), {merge: true});
                window.location.href = {!! json_encode(url('settings/payment/mycoolpay')) !!};
            } catch (e) {
                jQuery("#data-table_processing").hide();
                showError({!! json_encode(trans('lang.mycoolpay_save_error')) !!} + ' ' + (e && e.message ? e.message : String(e)));
            }
        });
    });
</script>
@endsection
