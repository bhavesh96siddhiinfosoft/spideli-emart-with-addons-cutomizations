@extends('layouts.app')

@section('content')
{{-- #92: MyCoolPay Cameroun (my-coolpay.com). API: merchant API documentation of 14 Oct 2021 and the
     official PHP SDK. The public key and the switches live in settings/mycoolpay_settings (read by the
     apps and the website); the private key and the callback token only in the staff-only
     payment_secrets/mycoolpay, written by this panel's server. --}}
@php
    $webBase = preg_replace('#/admin/?$#', '', rtrim(url('/'), '/'));
    $token = $secret['callbackToken'] ?? '';
@endphp
<div class="page-wrapper">
    <div class="card">
        <div class="payment-top-tab mt-3 mb-3">
            @include('settings.app.partials.payment_tabs', ['activeTab' => 'mycoolpay'])
        </div>

        <div class="card-body">
            <div class="error_top alert alert-danger" style="display:none"></div>
            @if (!empty($secretError))
                <div class="alert alert-warning">{{ trans('lang.mycoolpay_secret_read_error') }}</div>
            @endif
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.app_setting_mycoolpay') }}</legend>

                        <div class="form-check width-100">
                            <input type="checkbox" class="enable_mycoolpay" id="enable_mycoolpay">
                            <label class="col-3 control-label" for="enable_mycoolpay">{{ trans('lang.app_setting_enable_mycoolpay') }}</label>
                            <div class="form-text text-muted">{{ trans('lang.app_setting_enable_mycoolpay_help') }}</div>
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
                                <input type="password" class="form-control mycoolpay_private_key" autocomplete="new-password"
                                    placeholder="{{ !empty($secret['last4']) ? '•••• ' . $secret['last4'] : '' }}">
                                <div class="form-text text-muted">
                                    @if (!empty($secret['last4']))
                                        <strong>{{ trans('lang.mycoolpay_private_key_saved', ['last4' => $secret['last4']]) }}</strong>
                                    @else
                                        <strong>{{ trans('lang.mycoolpay_private_key_none') }}</strong>
                                    @endif
                                    {{ trans('lang.mycoolpay_private_key_help') }}
                                </div>
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
                        @foreach ([
                            'mycoolpay_callback_url' => $token !== '' ? $webBase . '/mycoolpay/callback/' . $token : '',
                            'mycoolpay_success_url' => $webBase . '/mycoolpay/return/success',
                            'mycoolpay_cancel_url' => $webBase . '/mycoolpay/return/cancel',
                            'mycoolpay_failure_url' => $webBase . '/mycoolpay/return/failure',
                        ] as $labelKey => $link)
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.' . $labelKey) }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control mycoolpay_link" readonly value="{{ $link }}"
                                        placeholder="{{ trans('lang.mycoolpay_callback_pending') }}" onclick="this.select()">
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
    var hasPrivateKey = {!! json_encode(!empty($secret['last4'])) !!};

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
                $('.mycoolpay_currency').val(p.currency || 'XAF');
                $('.mycoolpay_mode').val(p.paymentMode || 'paylink');
            }
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
            if (enable && (publicKey === '' || (!hasPrivateKey && privateKey === ''))) {
                showError({!! json_encode(trans('lang.mycoolpay_keys_required')) !!});
                return;
            }
            jQuery("#data-table_processing").show();
            try {
                // 1. The private key goes to the server only (staff-only document).
                if (privateKey !== '') {
                    await $.ajax({
                        url: {!! json_encode(route('payment.mycoolpay.secret')) !!},
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        data: {privateKey: privateKey}
                    });
                }
                // 2. The public part, read by the apps and the website.
                await ref.set({
                    isEnabled: enable,
                    isWithdrawEnabled: $('#mycoolpay_withdraw_enable').is(':checked'),
                    publicKey: publicKey,
                    currency: $('.mycoolpay_currency').val(),
                    paymentMode: $('.mycoolpay_mode').val(),
                    regionIds: getRegionAssignment(),
                    gateway: 'mycoolpay',
                    updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                }, {merge: true});
                window.location.href = {!! json_encode(url('settings/payment/mycoolpay')) !!};
            } catch (e) {
                jQuery("#data-table_processing").hide();
                var msg = (e && e.responseJSON && e.responseJSON.message) ? e.responseJSON.message : (e && e.message ? e.message : String(e));
                showError({!! json_encode(trans('lang.mycoolpay_save_error')) !!} + ' ' + msg);
            }
        });
    });
</script>
@endsection
