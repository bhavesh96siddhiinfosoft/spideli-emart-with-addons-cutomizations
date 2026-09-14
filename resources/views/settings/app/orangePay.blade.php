@extends('layouts.app')



@section('content')

<div class="page-wrapper">

    <div class="card">

        <div class="payment-top-tab mt-3 mb-3">

            <ul class="nav nav-tabs card-header-tabs align-items-end">

                <li class="nav-item">

                    <a class="nav-link stripe_active_label" href="{!! url('settings/payment/stripe') !!}"><i

                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_stripe')}}<span

                            class="badge ml-2"></span>

                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link cod_active_label" href="{!! url('settings/payment/cod') !!}"><i

                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_cod_short')}}<span

                            class="badge ml-2"></span>

                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link razorpay_active_label" href="{!! url('settings/payment/razorpay') !!}"><i

                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_razorpay')}}<span

                            class="badge ml-2"></span>

                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link paypal_active_label" href="{!! url('settings/payment/paypal') !!}"><i

                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_paypal')}}<span

                            class="badge ml-2"></span>

                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link wallet_active_label" href="{!! url('settings/payment/wallet') !!}"><i

                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_wallet')}}<span

                            class="badge ml-2"></span>

                    </a>

                </li>



                <li class="nav-item">

                    <a class="nav-link payfast_active_label" href="{!! url('settings/payment/payfast') !!}"><i

                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.payfast')}}<span class="badge ml-2"></span>

                    </a>

                </li>





                <li class="nav-item">

                    <a class="nav-link paystack_active_label" href="{!! url('settings/payment/paystack') !!}"><i

                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_paystack_lable')}}<span

                            class="badge ml-2"></span>

                    </a>

                </li>



                <li class="nav-item">

                    <a class="nav-link flutterWave_active_label" href="{!! url('settings/payment/flutterwave') !!}"><i

                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.flutterWave')}}<span

                            class="badge ml-2"></span>

                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link mercadopago_active_label" href="{!! url('settings/payment/mercadopago') !!}"><i

                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.mercadopago')}}<span

                            class="badge ml-2"></span></a>

                </li>

                <li class="nav-item">

                    <a class="nav-link xendit_active_label" href="{!! url('settings/payment/xendit') !!}"><i

                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.xendit')}}<span class="badge ml-2"></span>

                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link midTrans_active_label" href="{!! url('settings/payment/midtrans') !!}"><i

                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.midtrans')}}<span class="badge ml-2"></span>

                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link active orangePay_active_label"

                        href="{!! url('settings/payment/orangepay') !!}"><i

                            class="fa fa-envelope-o mr-2"></i>{{trans('lang.orangePay')}}<span

                            class="badge ml-2"></span>

                    </a>

                </li>



            </ul>

        </div>



        <div class="card-body">

            <div class="row vendor_payout_create">

                <div class="vendor_payout_create-inner">

                    <fieldset>

                        <legend>{{trans('lang.app_setting_orangepay')}}</legend>

                        <div class="form-check width-100">

                            <input type="checkbox" class=" enable_orangePay" id="enable_orangePay">

                            <label class="col-3 control-label"

                                for="enable_orangePay">{{trans('lang.app_setting_enable_orangepay')}}</label>

                        </div>



                        <div class="form-check width-100">

                            <input type="checkbox" class="sand_box_mode" id="sand_box_mode">

                            <label class="col-3 control-label"

                                for="sand_box_mode">{{trans('lang.app_setting_enable_sandbox_mode')}}</label>

                        </div>

                        <div class="form-group row width-100">

                            <label

                                class="col-3 control-label">{{trans('lang.app_setting_orangepay_merchantKey')}}</label>

                            <div class="col-7">

                                <input type="text" class="form-control orangePay_merchantKey">

                                <div class="form-text text-muted">

                                    {!! trans('lang.app_setting_orangepay_merchantKey_help') !!}

                                </div>

                            </div>

                        </div>

                        <div class="form-group row width-100">

                            <label class="col-3 control-label">{{trans('lang.app_setting_orangepay_auth')}}</label>

                            <div class="col-7">

                                <input type="text" class="form-control orangePay_auth">

                                <div class="form-text text-muted">

                                    {!! trans('lang.app_setting_orangepay_auth_help') !!}

                                </div>

                            </div>

                        </div>

                        <div class="form-group row width-100">

                            <label class="col-3 control-label">{{trans('lang.app_setting_orangepay_clientid')}}</label>

                            <div class="col-7">

                                <input type="password" class="form-control orangePay_clientId">

                                <div class="form-text text-muted">

                                    {!! trans('lang.app_setting_orangepay_clientid_help') !!}

                                </div>

                            </div>

                        </div>



                        <div class="form-group row width-100">

                            <label class="col-3 control-label">{{trans('lang.app_setting_orangepay_secret')}}</label>

                            <div class="col-7">

                                <input type="password" class=" form-control orangePay_secret">

                                <div class="form-text text-muted">

                                    {!! trans('lang.app_setting_orangepay_secret_help') !!}

                                </div>

                            </div>

                        </div>

                        <div class="form-group row width-100">

                            <label class="col-3 control-label">{{trans('lang.app_setting_orangepay_cancelurl')}}</label>

                            <div class="col-7">

                                <input type="text" class=" form-control orangePay_cancelUrl">

                                <div class="form-text text-muted">

                                    {!! trans('lang.app_setting_orangepay_cancelurl_help') !!}

                                </div>

                            </div>

                        </div>

                        <div class="form-group row width-100">

                            <label class="col-3 control-label">{{trans('lang.app_setting_orangepay_notifyurl')}}</label>

                            <div class="col-7">

                                <input type="text" class=" form-control orangePay_notifyUrl">

                                <div class="form-text text-muted">

                                    {!! trans('lang.app_setting_orangepay_notifyurl_help') !!}

                                </div>

                            </div>

                        </div>

                        <div class="form-group row width-100">

                            <label class="col-3 control-label">{{trans('lang.app_setting_orangepay_returnurl')}}</label>

                            <div class="col-7">

                                <input type="text" class=" form-control orangePay_returnUrl">

                                <div class="form-text text-muted">

                                    {!! trans('lang.app_setting_orangepay_returnurl_help') !!}

                                </div>

                            </div>

                        </div>

                    </fieldset>

                    <fieldset>

                        <legend>{{trans('lang.withdraw_setting')}}</legend>

                        <div class="form-check width-100">

                            <div class="form-text text-muted">

                                {!! trans('lang.withdraw_setting_not_available_help') !!}

                            </div>

                        </div>

                    </fieldset>

                </div>

            </div>

        </div>

        @include('settings.app.partials.region_assignment')

        <div class="form-group col-12 text-center btm-btn" style="margin-bottom:inherit;">

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

var ref = database.collection('settings').doc('orange_money_settings');

var codData = database.collection('settings').doc('CODSettings');

var razorpayData = database.collection('settings').doc('razorpaySettings');

var paypalData = database.collection('settings').doc('paypalSettings');

var walletData = database.collection('settings').doc('walletSettings');

var payFastSettings = database.collection('settings').doc('payFastSettings');

var payStackSettings = database.collection('settings').doc('payStack');

var flutterWaveSettings = database.collection('settings').doc('flutterWave');

var MercadopagoSettings = database.collection('settings').doc('MercadoPago');

var stripe = database.collection('settings').doc('stripeSettings');

var xenditSettings = database.collection('settings').doc('xendit_settings');

var midTrans = database.collection('settings').doc('midtrans_settings');



$(document).ready(function() {

    jQuery("#data-table_processing").show();

    ref.get().then(async function(paymentSnapshots) {

        var payment = paymentSnapshots.data();

        await loadRegionAssignment(payment);



        if (payment) {

            if (payment.enable) {

                $("#enable_orangePay").prop('checked', true);

                jQuery(".orangePay_active_label span").addClass('badge-success');

                jQuery(".orangePay_active_label span").text('Active');

            }

            if (payment.isSandbox) {

                $("#sand_box_mode").prop('checked', true);

            }

            $('.orangePay_clientId').val(payment.clientId);

            $('.orangePay_secret').val(payment.clientSecret);

            $('.orangePay_merchantKey').val(payment.merchantKey);

            $('.orangePay_auth').val(payment.auth);

            $('.orangePay_cancelUrl').val(payment.cancelUrl);

            $('.orangePay_notifyUrl').val(payment.notifyUrl);

            $('.orangePay_returnUrl').val(payment.returnUrl);

        }



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



        paypalData.get().then(async function(paypalSnapshots) {

            var paypal = paypalSnapshots.data();

            if (paypal.isEnabled) {

                jQuery(".paypal_active_label span").addClass('badge-success');

                jQuery(".paypal_active_label span").text('Active');

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



        stripe.get().then(async function(stripe) {

            var stripe = stripe.data();

            if (stripe.isEnabled) {

                jQuery(".stripe_active_label span").addClass('badge-success');

                jQuery(".stripe_active_label span").text('Active');

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



        var returnUrl = $(".orangePay_returnUrl").val();

        var notifyUrl = $(".orangePay_notifyUrl").val();

        var cancelUrl = $(".orangePay_cancelUrl").val();

        var auth = $(".orangePay_auth").val();

        var merchantKey = $(".orangePay_merchantKey").val();

        var clientId = $(".orangePay_clientId").val();

        var clientSecret = $(".orangePay_secret").val();

        var enable = $("#enable_orangePay").is(":checked");

        var isSandBox = $("#sand_box_mode").is(":checked");

        ref.update({


            'regionIds': getRegionAssignment(),

            'enable': enable,

            'isSandbox': isSandBox,

            'merchantKey': merchantKey,

            'auth': auth,

            'clientId': clientId,

            'clientSecret': clientSecret,

            'cancelUrl': cancelUrl,

            'notifyUrl': notifyUrl,

            'returnUrl': returnUrl

        }).then(function(result) {

            window.location.href = '{{ url("settings/payment/orangepay")}}';

        });



    })



})

</script>





@endsection

