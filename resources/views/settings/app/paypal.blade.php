@extends('layouts.app')



@section('content')

<div class="page-wrapper">

    <div class="card">

        <div class="payment-top-tab mt-3 mb-3">

            @include('settings.app.partials.payment_tabs', ['activeTab' => 'paypal'])

        </div>

        <div class="card-body">


            <div class="row vendor_payout_create">

                <div class="vendor_payout_create-inner">

                    <fieldset>

                        <legend>{{trans('lang.app_setting_paypal')}}</legend>

                        <div class="form-check width-100">

                            <input type="checkbox" class=" enable_paypal" id="enable_paypal">

                            <label class="col-3 control-label"

                                for="enable_paypal">{{trans('lang.app_setting_enable_paypal')}}</label>

                        </div>

                        <div class="form-check width-100">

                            <input type="checkbox" class=" paypal_live_mode" id="paypal_live_mode">

                            <label class="col-3 control-label"

                                for="paypal_live_mode">{{trans('lang.app_setting_paypal_mode')}}</label>

                        </div>

                        <div class="form-group row width-100">

                            <label class="col-3 control-label">{{trans('lang.app_setting_paypal_app_id')}}</label>

                            <div class="col-7">

                                <input type="password" class=" form-control paypal_app_id">

                                <div class="form-text text-muted">

                                    {!! trans('lang.app_setting_paypal_app_id_help') !!}

                                </div>

                            </div>

                        </div>

                        <div class="form-group row width-100">

                            <label class="col-3 control-label">{{trans('lang.app_setting_paypal_secret')}}</label>

                            <div class="col-7">

                                <input type="password" class="form-control paypal_secret">

                                <div class="form-text text-muted">

                                    {!! trans('lang.app_setting_paypal_secret_help') !!}

                                </div>

                            </div>

                        </div>



                    </fieldset>

                    <fieldset>

                        <legend><i class="mr-3 fa fa-cc-stripe"></i>{{trans('lang.withdraw_setting')}}</legend>

                       <div class="form-check width-100">

                            <input type="checkbox" class="withdraw_enable" id="withdraw_enable">

                            <label class="col-3 control-label"

                                for="withdraw_enable">{{trans('lang.app_setting_enable_paypal')}}</label>

                            <div class="form-text text-muted">

                                {!! trans('lang.withdraw_setting_enable_paypal_help') !!}

                            </div>

                        </div>

                    </fieldset>

                </div>

            </div>

        </div>



        @include('settings.app.partials.region_assignment')

        <div class="form-group col-12 text-center btm-btn" style="margin-bottom: inherit;">

            <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i>

                {{trans('lang.save')}}

            </button>

            <a href="{{url('/dashboard')}}" class="btn btn-default"><i

                    class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>

        </div>

    </div>

</div>



@endsection



@section('scripts')



<script type="text/javascript">

var database = firebase.firestore();

var ref = database.collection('settings').doc('paypalSettings');

var codData = database.collection('settings').doc('CODSettings');

var razorpayData = database.collection('settings').doc('razorpaySettings');

var stripeData = database.collection('settings').doc('stripeSettings');

var walletData = database.collection('settings').doc('walletSettings');

var payFastSettings = database.collection('settings').doc('payFastSettings');

var payStackSettings = database.collection('settings').doc('payStack');

var flutterWaveSettings = database.collection('settings').doc('flutterWave');

var MercadopagoSettings = database.collection('settings').doc('MercadoPago');

var orangePay = database.collection('settings').doc('orange_money_settings');

var xenditSettings = database.collection('settings').doc('xendit_settings');

var midTrans = database.collection('settings').doc('midtrans_settings');



$(document).ready(function() {

    jQuery("#data-table_processing").show();

    ref.get().then(async function(snapshots) {

        var paypal = snapshots.data();

        await loadRegionAssignment(paypal);



        if (paypal.isEnabled) {

            $(".enable_paypal").prop('checked', true);

            jQuery(".paypal_active_label span").addClass('badge-success');

            jQuery(".paypal_active_label span").text('Active');

        }

        if (paypal.isLive) {

            $(".paypal_live_mode").prop('checked', true);

        }



        if (paypal.isWithdrawEnabled) {

            $(".withdraw_enable").prop('checked', true);

        }



        $(".paypal_app_id").val(paypal.paypalClient);

        $(".paypal_secret").val(paypal.paypalSecret);



        codData.get().then(async function(codSnapshots) {

            var cod = codSnapshots.data();

            if (cod.isEnabled) {

                jQuery(".cod_active_label span").addClass('badge-success');

                jQuery(".cod_active_label span").text('Active');

            }



        })



        razorpayData.get().then(async function(razorpaySnapshots) {

            var razorPay = razorpaySnapshots.data();

            if (razorPay.isEnabled) {

                jQuery(".razorpay_active_label span").addClass('badge-success');

                jQuery(".razorpay_active_label span").text('Active');

            }

        })



        stripeData.get().then(async function(stripeSnapshots) {

            var stripe = stripeSnapshots.data();

            if (stripe.isEnabled) {

                jQuery(".stripe_active_label span").addClass('badge-success');

                jQuery(".stripe_active_label span").text('Active');

            }

        })



        walletData.get().then(async function(walletSnapshots) {

            var wallet = walletSnapshots.data();

            if (wallet.isEnabled) {

                jQuery(".wallet_active_label span").addClass('badge-success');

                jQuery(".wallet_active_label span").text('Active');

            }

        })



        payFastSettings.get().then(async function(payFastSnaShots) {

            var payFast = payFastSnaShots.data();

            if (payFast.isEnable) {

                jQuery(".payfast_active_label span").addClass('badge-success');

                jQuery(".payfast_active_label span").text('Active');

            }

        })



        payStackSettings.get().then(async function(payStackSnapShots) {

            var payStack = payStackSnapShots.data();

            if (payStack.isEnable) {

                jQuery(".paystack_active_label span").addClass('badge-success');

                jQuery(".paystack_active_label span").text('Active');

            }

        })





        flutterWaveSettings.get().then(async function(flutterWaveSnapShots) {

            var flutterWave = flutterWaveSnapShots.data();

            if (flutterWave.isEnable) {

                jQuery(".flutterWave_active_label span").addClass('badge-success');

                jQuery(".flutterWave_active_label span").text('Active');

            }

        })



        MercadopagoSettings.get().then(async function(mercadopagoSnapshots) {

            var mercadopago = mercadopagoSnapshots.data();

            if (mercadopago.isEnabled) {

                jQuery(".mercadopago_active_label span").addClass('badge-success');

                jQuery(".mercadopago_active_label span").text('Active');

            }

        })



        orangePay.get().then(async function(orangePay) {

            var orangePay = orangePay.data();

            if (orangePay.enable) {

                jQuery(".orangePay_active_label span").addClass('badge-success');

                jQuery(".orangePay_active_label span").text('Active');

            }

        })



        xenditSettings.get().then(async function(xenditSettings) {

            var xenditSettings = xenditSettings.data();

            if (xenditSettings.enable) {

                jQuery(".xendit_active_label span").addClass('badge-success');

                jQuery(".xendit_active_label span").text('Active');

            }

        })



        midTrans.get().then(async function(midTrans) {

            var midTrans = midTrans.data();

            if (midTrans.enable) {

                jQuery(".midTrans_active_label span").addClass('badge-success');

                jQuery(".midTrans_active_label span").text('Active');

            }

        })



        jQuery("#data-table_processing").hide();

    })



    $(".edit-setting-btn").click(function() {



        var isenabled = $(".enable_paypal").is(":checked");

        var paypalLive = $(".paypal_live_mode").is(":checked");

        var paypalAppId = $(".paypal_app_id").val();

        var paypalAppSecret = $(".paypal_secret").val();

        var paypalUsername = $(".paypal_username").val();

        var paypalPassword = $(".paypal_password").val();

        var isWithdrawEnabled = $(".withdraw_enable").is(":checked");



        database.collection('settings').doc("paypalSettings").update({




            'regionIds': getRegionAssignment(),

            'isEnabled': isenabled,

            'isLive': paypalLive,

            'paypalAppId': paypalAppId,

            'paypalSecret': paypalAppSecret,

            'isWithdrawEnabled': isWithdrawEnabled



        }).then(function(result) {

            window.location.href = '{{ url("settings/payment/paypal")}}';

        });



    })

})

</script>



@endsection

