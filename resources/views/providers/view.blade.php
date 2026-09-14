@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
                        <div class="row page-titles">
                            <div class="col-md-5 align-self-center">
                                <div class="d-flex top-title-section justify-content-between">
                                    <div class="d-flex top-title-left align-self-center">
                                        <span class="icon mr-3"><img src="{{ asset('images/provider.png') }}"></span>
                                        <h3 class="mb-0">{{ trans('lang.provider_plural') }} - <span
                                                class="itemTitle"></span></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7 align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a
                                            href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                                    <li class="breadcrumb-item"><a
                                            href="{!! route('providers') !!}">{{ trans('lang.provider_plural') }}</a>
                                    </li>
                                    <li class="breadcrumb-item active">{{ trans('lang.provider_details') }}</li>
                                </ol>
                            </div>
                        </div>
                         
            {{-- <div class="admin-top-section pt-4">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-self-center">
                                <span class="icon mr-3"><img src="{{ asset('images/provider.png') }}"></span>
                                <div class="top-title-breadcrumb">
                                    <h3 class="mb-0 restaurantTitle">{{ trans('lang.provider_plural') }}</h3>
                                     <div class="col-md-7 align-self-center">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                                        <li class="breadcrumb-item"><a href="{!! route('providers') !!}">{{ trans('lang.provider_plural') }}</a></li>
                                        <li class="breadcrumb-item active">{{ trans('lang.provider_details') }}</li>
                                    </ol>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
        <div class="container-fluid">
            <div class="resttab-sec mb-4">
                <div class="menu-tab">
                    <ul>
                        <li class="active"><a href="{{ route('providers.view', $id) }}"><img
                                    src="{{ asset('images/provider.png') }}"> {{ trans('lang.tab_basic') }}</a>
                        </li>
                        <li><a href="{{ route('ondemand.services.index', $id) }}"><img
                                    src="{{ asset('images/service.png') }}"> {{ trans('lang.services') }}</a></li>
                        <li>
                        <li><a href="{{ route('ondemand.workers.index', $id) }}"><img
                                    src="{{ asset('images/worker.png') }}"> {{ trans('lang.workers') }}</a></li>
                        <li>
                        <li><a href="{{ route('ondemand.bookings.index', $id) }}"><img
                                    src="{{ asset('images/booking.png') }}"> {{ trans('lang.booking_plural') }}</a></li>
                        <li>
                        <li><a href="{{ route('ondemand.coupons', $id) }}"><img src="{{ asset('images/coupon.png') }}">
                                {{ trans('lang.coupon_plural') }}</a></li>
                        <li>
                            <a href="{{ route('providerPayouts.payout', $id) }}"><img
                                    src="{{ asset('images/payment.png') }}"> {{ trans('lang.tab_payouts') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('payoutRequests.providers', $id) }}"><img
                                    src="{{ asset('images/payment.png') }}"> {{ trans('lang.tab_payout_request') }}</a>
                        </li>
                        <?php if (in_array('wallet-transaction', json_decode(@session('user_permissions'),true))) { ?>

                        <li>
                            <a href="{{ url('walletstransaction/providerID=' . $id) }}" class="wallet_transaction"><img
                                    src="{{ asset('images/wallet.png') }}"> {{ trans('lang.wallet_transaction') }}</a>
                        </li>

                        <?php } ?>
                        <?php
                        
                        $subscription = route('subscription.subscriptionPlanHistory', ':id');
                        $subscription = str_replace(':id', 'providerID=' . $id, $subscription);
                        ?>
                        <li>
                            <a href="{{ $subscription }}"><img src="{{ asset('images/subscription.png') }}">
                                {{ trans('lang.subscription_history') }}</a>
                        </li>
                    </ul>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--1">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 order_count" id="order_count">00</h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.dashboard_total_orders') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/total_orders.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--3">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 wallet_balance" id="wallet_balance">$0.00</h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.wallet_Balance') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/total_payment.png') }}"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="text-dark-2 mb-0 h4">{{ trans('lang.subscription_details') }}</h3>
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#changeSubscriptionModal"
                                class="btn-primary btn rounded-full change-plan"><i
                                    class="mdi mdi-plus mr-2"></i>{{ trans('lang.change_subscription_plan') }}</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--9">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 plan_name"></h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.plan_name') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/basic.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--5">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 number_of_days"></h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.number_of_days') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/countdown.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--14">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 plan_expire_date"></h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.expiry_date') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/calendar.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--6">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 plan_price"></h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.total_price') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/price.png') }}"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="restaurant_info-section">
                <div class="row">
                    <div class="col-md-5">
                        <div class="card border h-100">
                            <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-0 h4">{{ trans('lang.provider_details') }}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="restaurant_info_left">
                                            <div class="d-flex mb-1">
                                                <div class="sis-img profile_image" id="profile_image">
                                                </div>
                                                <div class="sis-content pl-4">
                                                    <ul class="p-0 info-list mb-0">
                                                        <li class="d-flex align-items-center mb-2">
                                                            <label
                                                                class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.first_name') }}</label>
                                                            <span class="user_name" id="user_name"></span>
                                                        </li>
                                                        <li class="d-flex align-items-center mb-2">
                                                            <label
                                                                class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.email') }}</label>
                                                            <span class="email"></span>
                                                        </li>
                                                        <li class="d-flex align-items-center mb-2">
                                                            <label
                                                                class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.user_phone') }}</label>
                                                            <span class="phone"></span>
                                                        </li>
                                                        <li class="d-flex align-items-center mb-2 mr-1">
                                                            <label
                                                                class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.section') }}</label>
                                                            <span class="provider_section"> </span>
                                                        </li>
                                                        <li class="d-flex align-items-center mb-2 mr-1">
                                                            <label
                                                                class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.wallet_Balance') }}</label>
                                                            <span class="wallet_balance"> </span>
                                                        </li>

                                                    </ul>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card border h-100">
                            <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-0 h4">{{ trans('lang.active_subscription_plan') }}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">

                                    <div class="col-md-4">
                                        <div class="active-sub-plan">
                                            <label
                                                class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.plan_name') }}</label>
                                            <p><span class="plan_name"></span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="active-sub-plan">
                                            <label
                                                class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.plan_type') }}</label>
                                            <p><span class="plan_type"></span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="active-sub-plan">
                                            <label
                                                class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.plan_expires_at') }}</label>
                                            <p><span class="plan_expire_at"></span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="active-sub-plan">
                                            <label
                                                class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.booking_limit') }}</label>
                                            <p><span class="order_limit"></span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="active-sub-plan">
                                            <label
                                                class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.service_limit') }}</label>
                                            <p><span class="item_limit"></span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 update-limit-div" style="display:none">
                                        <div class="active-sub-plan">
                                            <a href="javascript:void(0)" data-toggle="modal"
                                                data-target="#updateLimitModal"
                                                class="btn-primary btn rounded-full update-limit">{{ trans('lang.update_plan_limit') }}</a>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="active-sub-plan">
                                            <label
                                                class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.available_booking_limit') }}</label>
                                            <p><span class="available_booking_limit"></span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="active-sub-plan">
                                            <label
                                                class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.available_service_limit') }}</label>
                                            <p><span class="available_service_limit"></span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="active-sub-plan">
                                            <label
                                                class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.available_features') }}</label>
                                            <p><span class="plan_features"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="form-group col-12 text-center btm-btn">

                <a href="{!! route('providers') !!}" class="btn btn-default"><i
                        class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>

            </div>
        </div>
    </div>

    <div class="modal fade" id="changeSubscriptionModal" tabindex="-1" role="dialog" aria-hidden="true"
        style="width: 100%">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="text-dark-2 h5 mb-0">{{ trans('lang.business_plans') }}</h6>
                    <button type="button" id="closeModalButton" class="close" data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-lg-12 ml-lg-auto mr-lg-auto">
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex top-title-section pb-4 mb-2 justify-content-between">
                                        <div class="d-flex top-title-left align-start-center">
                                            <div class="top-title">
                                                <h3 class="mb-0">{{ trans('lang.choose_your_business_plan') }}</h3>
                                                <p class="mb-0 text-dark-2">
                                                    {{ trans('lang.choose_your_business_plan_description') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row sections-div">
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.select_section') }}</label>
                                    <div class="col-7">
                                        <select id="section-input" class="form-control"></select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="row" id="default-plan"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="checkoutSubscriptionModal" tabindex="-1" role="dialog" aria-hidden="true"
        style="width: 100%">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="text-dark-2 h5 mb-0">{{ trans('lang.shift_to_plan') }}</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form class="">
                        <div class="subscription-section">
                            <div class="subscription-section-inner">
                                <div class="card-body">
                                    <div class="row" id="plan-details"></div>

                                </div>
                                <div class="pay-method-section pt-4 manual_pay_div">
                                    <h6 class="text-dark-2 h6 mb-3 pb-3">{{ trans('lang.pay_via_online') }}</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="pay-method-box d-flex align-items-center">
                                                <div class="pay-method-icon">
                                                    <img src="{{ asset('images/wallet_icon_ic.png') }}">
                                                </div>
                                                <div class="form-check">
                                                    <h6 class="text-dark-2 h6 mb-0">{{ trans('lang.manual_pay') }}</h6>
                                                    <input type="radio" id="manual_pay" name="payment_method"
                                                        value="manual_pay" checked="">
                                                    <label class="control-label mb-0" for="manual_pay"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer border-top">
                                    <div class="align-items-center justify-content-between">
                                        <div class="edit-form-group btm-btn text-right">
                                            <div class="card-block-active-plan">
                                                <a href="" class="btn btn-default rounded-full mr-2"
                                                    data-dismiss="modal">{{ trans('lang.cancel_plan') }}</a>
                                                <input type="hidden" id="plan_id" name="plan_id" value="">
                                                <button type="button" class="btn-primary btn rounded-full"
                                                    onclick="finalCheckout()">{{ trans('lang.change_plan') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="updateLimitModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered location_modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title locationModalTitle">{{ trans('lang.update_plan_limit') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="">
                        <div class="form-row">
                            <div class="form-group row">
                                <div class="form-group row width-100">
                                    <label class="control-label">{{ trans('lang.maximum_booking_limit') }}</label>
                                    <div class="form-check width-100">
                                        <input type="radio" id="unlimited_order" name="set_order_limit"
                                            value="unlimited" checked>
                                        <label class="control-label"
                                            for="unlimited_order">{{ trans('lang.unlimited') }}</label>
                                    </div>
                                    <div class="d-flex">
                                        <div class="form-check width-50 limited_order_div">
                                            <input type="radio" id="limited_order" name="set_order_limit"
                                                value="limited">
                                            <label class="control-label"
                                                for="limited_order">{{ trans('lang.limited') }}</label>
                                        </div>
                                        <div class="form-check width-50 d-none order-limit-div">
                                            <input type="number" id="order_limit" class="form-control"
                                                placeholder="{{ trans('lang.ex_1000') }}">
                                        </div>
                                    </div>
                                    <span class="booking_limit_err"></span>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="control-label">{{ trans('lang.maximum_service_limit') }}</label>
                                    <div class="form-check width-100">
                                        <input type="radio" id="unlimited_item" name="set_item_limit"
                                            value="unlimited" checked>
                                        <label class="control-label"
                                            for="unlimited_item">{{ trans('lang.unlimited') }}</label>
                                    </div>
                                    <div class="d-flex ">
                                        <div class="form-check width-50 limited_item_div  ">
                                            <input type="radio" id="limited_item" name="set_item_limit"
                                                value="limited">
                                            <label class="control-label"
                                                for="limited_item">{{ trans('lang.limited') }}</label>
                                        </div>
                                        <div class="form-check width-50 d-none item-limit-div">
                                            <input type="number" id="item_limit" class="form-control"
                                                placeholder="{{ trans('lang.ex_1000') }}">
                                        </div>
                                    </div>
                                    <span class="service_limit_err"></span>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer">
                        <button type="button"
                            class="btn btn-primary update-plan-limit">{{ trans('submit') }}</a></button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                            {{ trans('close') }}</a>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        var id = "{{ $id }}";
        var database = firebase.firestore();
        var ref = database.collection('users').where("id", "==", id);
        var sectionId = null;
        var sectionLength = 0;
        var photo = "";
        var placeholderImage = '';
        var placeholder = database.collection('settings').doc('placeHolderImage');

        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        });
        var currency = database.collection('settings');

        var currentCurrency = '';
        var currencyAtRight = false;
        var decimal_degits = 0;

        var refCurrency = regionCurrencyRef();
        refCurrency.get().then(async function(snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;

            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
            $(".currentCurrency").text(currencyData.symbol);
        });

        var commisionModel = false;
        var AdminCommission = '';
        var vendorSpecificCommission = false;
        var commissionObj = '';
        var refSection = database.collection('sections').where('isActive', '==', true).where('serviceType', '==','On Demand Service');
        refSection.get().then(async function(sectionsSnapshot) {
            sectionsSnapshot.docs.forEach((listval) => {
                var data = listval.data();
                $('#section-input').append(
                    $("<option></option>")
                    .attr("value", data.id)
                    .text(data.name)
                );
            });
        });

        async function getCommissionDataBySection() {
            sectionId = $('#section-input').val();

            var commissionBusinessModel = database.collection('sections').where('id', '==',
                sectionId);
            await commissionBusinessModel.get().then(async function(snapshots) {
                if (snapshots.docs.length > 0) {
                    var data = snapshots.docs[0].data();
                    if (commissionObj == '') {
                        commissionObj = data.adminCommision;
                    }
                    var commissionSetting = data.adminCommision;
                    if (commissionSetting.enable == true) {
                        commisionModel = true;
                    } else {
                        commisionModel = false;
                    }
                    if (vendorSpecificCommission == false) {
                        if (commissionSetting.type == "percentage") {
                            AdminCommission = commissionSetting.commission + '' + '%';
                        } else {
                            if (currencyAtRight) {
                                AdminCommission = commissionSetting.commission.toFixed(decimal_degits) +
                                    currentCurrency;
                            } else {
                                AdminCommission = currentCurrency + commissionSetting.commission.toFixed(
                                    decimal_degits);
                            }
                        }

                    }
                }
            });
            getSubscriptionPlan();

        }

        var subscriptionModel = false;
        database.collection('settings').doc("vendor").get().then(async function(snapshots) {
            var businessModelSettings = snapshots.data();
            if (businessModelSettings.hasOwnProperty('subscription_model') && businessModelSettings
                .subscription_model == true) {
                subscriptionModel = true;
            }
        });


        $(document).ready(async function() {

            $(document).on("click", "#checkoutSubscriptionModal .close", function() {
                $("#checkoutSubscriptionModal").modal("hide");
            });

            jQuery("#data-table_processing").show();

            await ref.get().then(async function(snapshots) {
                if (snapshots.docs.length > 0) {
                    var user = snapshots.docs[0].data();

                    $(".user_name").text(user.firstName + ' ' + user.lastName);
                    $(".itemTitle").text(user.firstName + ' ' + user.lastName);

                    if (user.hasOwnProperty('email') && user.email) {
                        $(".email").text(shortEmail(user.email));

                    } else {
                        $('.email').html("");

                    }

                    if (user.hasOwnProperty('phoneNumber') && user.phoneNumber) {
                        if (user.phoneNumber.includes('+')) {
                            $(".phone").text('+' + EditPhoneNumber(user.phoneNumber.slice(1)));
                        } else {
                            $(".phone").text(EditPhoneNumber(user.phoneNumber));
                        }
                    } else {
                        $('.phone').html("");

                    }

                    if (user.hasOwnProperty('adminCommission') && user.adminCommission != null &&
                        user.adminCommission != '') {
                        commissionObj = user.adminCommission;
                        vendorSpecificCommission = true;
                        if (user.adminCommission.type == "percentage") {
                            AdminCommission = user.adminCommission.commission + '' + '%';
                        } else {
                            if (currencyAtRight) {
                                AdminCommission = user.adminCommission.commission.toFixed(
                                    decimal_degits) + currentCurrency;
                            } else {
                                AdminCommission = currentCurrency + user.adminCommission.commission
                                    .toFixed(decimal_degits);
                            }
                        }
                    }

                    if (user.hasOwnProperty('section_id') && user.section_id != '' && user
                        .section_id != null) {
                        sectionId = user.section_id;
                        $('#section-input').val(sectionId).prop('disabled', true);
                        getCommissionDataBySection();
                    } else {
                        getCommissionDataBySection();
                    }
                    if (user.hasOwnProperty('section_id') && user.section_id) {
                        $(".provider_section").text(await sectionNameById(user.section_id));
                    } else {
                        $('.provider_section').html("-");

                    }

                    var wallet_balance = 0;

                    if (user.hasOwnProperty('wallet_amount') && user.wallet_amount != null && !
                        isNaN(user.wallet_amount)) {
                        wallet_balance = user.wallet_amount;
                    }
                    if (currencyAtRight) {
                        wallet_balance = parseFloat(wallet_balance).toFixed(decimal_degits) + "" +
                            currentCurrency;
                    } else {
                        wallet_balance = currentCurrency + "" + parseFloat(wallet_balance).toFixed(
                            decimal_degits);
                    }

                    $('.wallet_balance').html(wallet_balance);

                    var image = "";
                    if (user.profilePictureURL) {
                        image = '<img width="100px" id="" height="auto" src="' + user
                            .profilePictureURL + '" onerror="this.onerror=null;this.src=\'' +
                            placeholderImage + '\'">';
                    } else {
                        image = '<img width="100px" id="" height="auto" src="' + placeholderImage +
                            '">';
                    }

                    $('.profile_image').html(image);
                } else {
                    $('.provider_detail_div').html(
                        '<h5 class="text-danger text-center font-weight-bold">{{ trans('lang.provider_unknown_deleted') }}</h5>'
                        )
                }
                jQuery("#data-table_processing").hide();

                if (user.hasOwnProperty('subscriptionExpiryDate') && user.hasOwnProperty(
                        'subscriptionPlanId') && user.subscriptionPlanId != '' && user
                    .subscriptionPlanId != null) {
                    $(".update-limit-div").show();
                    $(".plan_name").html(user.subscription_plan.name);
                    $(".plan_type").html(user.subscription_plan.type);
                    if (user.subscriptionExpiryDate != null && user.subscriptionExpiryDate != '') {
                        date = user.subscriptionExpiryDate.toDate().toDateString();
                        time = user.subscriptionExpiryDate.toDate().toLocaleTimeString('en-US');
                        $(".plan_expire_at").html(date + ' ' + time);
                        $(".plan_expire_date").html(date);
                    } else {
                        $(".plan_expire_at").html("{{ trans('lang.unlimited') }}");
                        $(".plan_expire_date").html("{{ trans('lang.unlimited') }}");
                    }
                    var number_of_days = user.subscription_plan.expiryDay == "-1" ? 'Unlimited' :
                        user.subscription_plan.expiryDay + " Days";
                    $(".number_of_days").html(number_of_days);
                    if (currencyAtRight) {
                        $(".plan_price").html(parseFloat(user.subscription_plan.price).toFixed(
                            decimal_degits) + currentCurrency);
                    } else {
                        $(".plan_price").html(currentCurrency + parseFloat(user.subscription_plan
                            .price).toFixed(decimal_degits));
                    }
                    $('.order_limit').html((user.subscription_plan.orderLimit == '-1') ?
                        "{{ trans('lang.unlimited') }}" : user.subscription_plan.orderLimit);
                    $('.item_limit').html((user.subscription_plan.itemLimit == '-1') ?
                        "{{ trans('lang.unlimited') }}" : user.subscription_plan.itemLimit);
                    $('.available_booking_limit').html((user.subscriptionTotalOrders == '-1') ?
                        "{{ trans('lang.unlimited') }}" : user.subscriptionTotalOrders);

                    var snapshot = await database.collection('providers_services').where('author',
                        '==', id).get();
                    var totalProductCount = snapshot.size;
                    if (user.subscription_plan.itemLimit == '-1') {
                        $('.available_service_limit').html("Unlimited");
                    } else {
                        var availableService = parseInt(user.subscription_plan.itemLimit) -
                            parseInt(totalProductCount);
                        if (availableService < 0) {
                            $('.available_service_limit').html(0);
                        } else {
                            $('.available_service_limit').html(availableService);
                        }

                    }



                    if (user.subscription_plan.hasOwnProperty('features')) {
                        const translations = {
                            chatingOption: "{{ trans('lang.chat') }}",
                            mobileAppAccess: "{{ trans('lang.mobile_app') }}"
                        };
                        var features = user.subscription_plan.features;
                        var html = `<ul class="pricing-card-list text-dark-2">
                                            ${features.chat? `<li>${translations.chatingOption}</li>`:''}
                                            ${features.ownerMobileApp? `<li>${translations.mobileAppAccess}</li>`:''}    
                                    </ul>`;
                        $('.plan_features').html(html);
                    }
                } else {
                    $(".plan_name").html('No Active Plan');
                    $(".plan_type").html('N/A');
                    $(".plan_expire_at").html('N/A');
                    $(".plan_expire_date").html('N/A');
                    $(".number_of_days").html('N/A');
                    $(".plan_price").html('N/A');
                    $(".order_limit").html('N/A');
                    $(".item_limit").html('N/A');
                    $(".available_booking_limit").html('N/A');
                    $(".available_service_limit").html('N/A');
                    $(".plan_features").html('N/A');
                }

            });

        });


        $("#changeSubscriptionModal").on('shown.bs.modal', function() {
            getSubscriptionPlan();
        });
        $("#changeSubscriptionModal").on('hide.bs.modal', function() {
            $("#default-plan").html('');
            window.location.reload();
        });
        $("#checkoutSubscriptionModal").on('hide.bs.modal', function() {
            $("#plan-details").html('');
        });
        $('#checkoutSubscriptionModal').on('hidden.bs.modal', function() {
            $('#changeSubscriptionModal').modal('show'); // Reopen the first modal
        });
        async function showPlanDetail(planId) {
            $("#plan_id").val(planId);
            var activePlan = '';
            var snapshots = await database.collection('subscription_history').where('user_id', '==', id).orderBy(
                'createdAt', 'desc').get();
            if (snapshots.docs.length > 0) {
                var data = snapshots.docs[0].data();
                activePlan = data.subscription_plan;
            }
            var choosedPlan = '';
            var snapshot = await database.collection('subscription_plans').doc(planId).get();
            if (snapshot.exists) {
                choosedPlan = snapshot.data();
            }
            let html = '';
            if (parseInt(choosedPlan.price) != 0) {
                $('.manual_pay_div').removeClass('d-none');
            } else {
                $('.manual_pay_div').addClass('d-none');
            }
            let choosedPlan_price = currencyAtRight ? parseFloat(choosedPlan.price).toFixed(decimal_degits) +
                currentCurrency :
                currentCurrency + parseFloat(choosedPlan.price).toFixed(decimal_degits);
            if (activePlan) {
                let activePlan_price = currencyAtRight ? parseFloat(activePlan.price).toFixed(decimal_degits) +
                    currentCurrency :
                    currentCurrency + parseFloat(activePlan.price).toFixed(decimal_degits);
                html += ` 
                    <div class="col-md-8">
                        <div class="subscription-card-left"> 
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <div class="subscription-card text-center">
                                        <div class="d-flex align-items-center pb-3 justify-content-center">
                                            <span class="pricing-card-icon mr-4"><img src="${activePlan.image}"></span>
                                            <h2 class="text-dark-2 mb-0 font-weight-semibold">${activePlan.isCommissionPlan==true ? "{{ trans('lang.commission') }}":activePlan.name}</h2>
                                        </div>
                                        <h3 class="text-dark-2">${activePlan.isCommissionPlan==true ? AdminCommission+" {{ trans('lang.base_plan') }}":activePlan_price}</h3>
                                        <p class="text-center">${activePlan.isCommissionPlan==true ? "Free":activePlan.expiryDay==-1? "{{ trans('lang.unlimited') }}":activePlan.expiryDay+" Days"}</p>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <img src="{{ asset('images/left-right-arrow.png') }}">
                                </div>
                                <div class="col-md-5">
                                    <div class="subscription-card text-center">
                                        <div class="d-flex align-items-center pb-3 justify-content-center">
                                            <span class="pricing-card-icon mr-4"><img src="${choosedPlan.image}"></span>
                                            <h2 class="text-dark-2 mb-0 font-weight-semibold">${choosedPlan.name}
                                            </h2>
                                        </div>`
                if (choosedPlan.type == "paid")
                    html += `<h3 class="text-dark-2">${choosedPlan_price}</h3>`
                else
                    html += `<h3 class="text-dark-2" style="color:red;">Free</h3>`
                html += `<p class="text-center">${choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="subscription-card-right">
                            <div
                                class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                                <span class="font-weight-medium">{{ trans('lang.validity') }}</span>
                                <span class="font-weight-semibold">${choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</span>
                            </div>
                            <div
                                class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                                <span class="font-weight-medium">{{ trans('lang.price') }}</span>`
                if (choosedPlan.type == "paid")
                    html += `<span class="font-weight-semibold">${choosedPlan_price}</span>`
                else

                    html += `<span class="font-weight-semibold" style="color:red;">Free</span>`

                html += `</div>
                            <div
                                class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                                <span class="font-weight-medium">{{ trans('lang.bill_status') }}</span>
                                <span class="font-weight-semibold">{{ trans('lang.migrate_to_new_plan') }}</span>
                            </div>
                        </div>
                    </div>`
            } else {
                html += ` 
                    <div class="col-md-6">
                        <div class="subscription-card-left"> 
                            <div class="row align-items-center">
                                <div class="col-md-12">
                                    <div class="subscription-card text-center">
                                        <div class="d-flex align-items-center pb-3 justify-content-center">
                                            <span class="pricing-card-icon mr-4"><img src="${choosedPlan.image}"></span>
                                            <h2 class="text-dark-2 mb-0 font-weight-semibold">${choosedPlan.name}
                                            </h2>
                                        </div>`
                if (choosedPlan.type == "paid")
                    html += `<h3 class="text-dark-2">${choosedPlan_price}</h3>`
                else

                    html += `<h3 class="text-dark-2" style="color:red;">Free</h3>`

                html += `<p class="text-center">${choosedPlan.isCommissionPlan==true ? "Free":choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="subscription-card-right">
                            <div
                                class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                                <span class="font-weight-medium">{{ trans('lang.validity') }}</span>
                                <span class="font-weight-semibold">${choosedPlan.isCommissionPlan==true? "Unlimited":choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</span>
                            </div>
                            <div
                                class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                                <span class="font-weight-medium">{{ trans('lang.price') }}</span>`
                if (choosedPlan.type == "paid")
                    html += `<span class="font-weight-semibold">${choosedPlan_price}</span>`
                else

                    html += `<span class="font-weight-semibold" style="color:red;">Free</span>
                                
                            </div>
                            <div
                                class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                                <span class="font-weight-medium">{{ trans('lang.bill_status') }}</span>
                                <span class="font-weight-semibold">{{ trans('lang.migrate_to_new_plan') }}</span>
                            </div>
                        </div>
                    </div>`
            }
            $("#plan-details").html(html);
        }

        function chooseSubscriptionPlan(planId) {
            $("#changeSubscriptionModal").modal('hide');
            $("#checkoutSubscriptionModal").modal('show');
            showPlanDetail(planId);
        }

        async function finalCheckout() {
            let planId = $("#plan_id").val();
            if (planId != undefined && planId != '' && planId != null) {
                var userId = id;
                var vendorId = id;
                var id_order = database.collection('tmp').doc().id;
                var plan_type = '';
                var snapshot = await database.collection('subscription_plans').doc(planId).get();
                if (snapshot.exists) {
                    var planData = snapshot.data();
                    var createdAt = firebase.firestore.FieldValue.serverTimestamp();
                    if (planData.type == "paid") {
                        plan_type = "Manual Pay";
                    } else {
                        plan_type = "Free";
                    }
                    if (planData.expiryDay == "-1") {
                        var expiryDay = null
                    } else {
                        var currentDate = new Date();
                        currentDate.setDate(currentDate.getDate() + parseInt(planData.expiryDay));
                        var expiryDay = firebase.firestore.Timestamp.fromDate(currentDate);
                    }
                    await database.collection('users').doc(userId).update({
                        'subscription_plan': planData,
                        'subscriptionPlanId': planId,
                        'subscriptionExpiryDate': expiryDay,
                        'subscriptionTotalOrders': planData.orderLimit,
                        'section_id': sectionId,
                        'adminCommission': commissionObj
                    }).then(async function(result) {
                    var sectionCommissionSetting = {};
                    if(vendorId!=null){
                        const sectionRef = database.collection('sections').where('id', '==', sectionId);
                        const sectionSnap = await sectionRef.get();
                        if (!sectionSnap.empty) {
                            const sectionData = sectionSnap.docs[0].data();
                            sectionCommissionSetting = sectionData.adminCommision;
                        }
                        await database.collection('vendors').doc(vendorId).update({
                           'subscription_plan': planData,
                            'subscriptionPlanId': planId,
                            'subscriptionExpiryDate': expiryDay,
                            'subscriptionTotalOrders':planData.orderLimit,
                            'section_id':sectionId,
                            'adminCommission': sectionCommissionSetting
                        })
                    }

                    var providerServicesSnapshot = await database.collection('providers_services').where('author', '==',
                        userId).get();
                    if (!providerServicesSnapshot.empty) {
                        providerServicesSnapshot.forEach(async (doc) => {
                            // Update each matching document
                            await database.collection('providers_services').doc(doc.id).update({
                                'subscription_plan': planData,
                                'subscriptionPlanId': planId,
                                'subscriptionExpiryDate': expiryDay,
                                'subscriptionTotalOrders': planData.orderLimit
                            }).then(() => {
                                window.location.reload();
                            }).catch((error) => {
                                console.error("Error updating document:", error);
                            });
                        });
                    }

                    await database.collection('subscription_history').doc(id_order).set({
                        'id': id_order,
                        'user_id': userId,
                        'expiry_date': expiryDay,
                        'createdAt': createdAt,
                        'subscription_plan': planData,
                        'payment_type': plan_type
                    }).then(async function(snapshot) {
                        window.location.reload();
                    })
                    })
                }
            }
        }

        async function getSubscriptionPlan() {
            $('#default-plan').html('');
            var activeSubscriptionId = '';

            $('#default-plan').html('');
            var snapshots = await database.collection('subscription_history').where('user_id', '==', id).orderBy(
                'createdAt', 'desc').get();
            if (snapshots.docs.length > 0) {
                var data = snapshots.docs[0].data();
                activeSubscriptionId = data.subscription_plan.id;
            }
            database.collection('subscription_plans').where('isEnable', '==', true).where('sectionId', '==', sectionId)
                .get().then(async function(snapshots) {

                    let plans = [];
                    snapshots.docs.map(doc => {
                        let data = doc.data();
                        plans.push({
                            ...data
                        }); // Include document ID if needed
                    });
                    plans.sort((a, b) => b.isCommissionPlan - a.isCommissionPlan);
                    var html = '';

                    plans.map(async (data) => {
                        var activeClass = (data.id == activeSubscriptionId) ?
                            '<span class="badge badge-success">{{ trans('lang.active') }}</span>' :
                            '';
                        if (data.isCommissionPlan == true) {
                            if (commisionModel) {
                                commissionData = data;
                                planId = data.id;
                                html += `<div class="col-md-3 mb-5 pricing-card pricing-card-commission">
                                            <div class="pricing-card-inner">
                                                <div class="pricing-card-top">
                                                    <div class="d-flex align-items-center pb-4">
                                                        <span class="pricing-card-icon mr-4"><img src="${data.image}"></span>
                                                    </div>
                                                    <div class="pricing-card-price">
                                                        <h3 class="text-dark-2">${data.name} ${activeClass}</h3>
                                                        <span class="price-day">${data.description}</span>
                                                        <div class="pricing-card-price">
                                            <h3 class="text-dark-2">`
                                if (data.type == "paid")
                                    html +=
                                    `${currencyAtRight? parseFloat(data.price).toFixed(decimal_degits)+currentCurrency:currentCurrency+parseFloat(data.price).toFixed(decimal_degits)}</h3>`
                                else

                                    html += `<h3 class="text-dark-2" style="color:red;">Free</h3>`

                                html += `<span class="price-day">${data.expiryDay==-1? "{{ trans('lang.unlimited') }}":data.expiryDay} Days</span></div>
                                                    </div>
                                                </div>
                                                <div class="pricing-card-content pt-3 mt-3 border-top">
                                                    <ul class="pricing-card-list text-dark-2">`;
                                html +=
                                    `<li><span class="mdi mdi-check"></span>{{ trans('lang.pay_commission_of') }} ${AdminCommission} {{ trans('lang.on_each_booking') }} </li>`
                                data.plan_points.map(async (list) => {
                                    html +=
                                        `<li><span class="mdi mdi-check"></span>${list}</li>`
                                });
                                html +=
                                    `<li><span class="mdi mdi-check"></span>{{ trans('lang.unlimited') }} {{ trans('lang.bookings') }}</li>`
                                html +=
                                    `<li><span class="mdi mdi-check"></span>{{ trans('lang.unlimited') }} {{ trans('lang.services') }}</li>`
                                html += `</ul>
                                                </div>`;
                                var buttonText = (activeClass == '') ?
                                    "{{ trans('lang.select_plan') }}" :
                                    "{{ trans('lang.renew_plan') }}";

                                html += `<div class="pricing-card-btm">
                                                    <a href="javascript:void(0)" onClick="chooseSubscriptionPlan('${data.id}')" class="btn rounded-full active-btn btn-primary">${buttonText}</a>
                                                </div>`;

                                html += `</div>
                                </div>`;
                            }
                        } else {
                            if (subscriptionModel) {
                                const translations = {
                                    chatingOption: "{{ trans('lang.chating_option') }}",
                                    mobileAppAccess: "{{ trans('lang.mobile_app_access') }}"
                                };
                                var features = data.features;
                                var buttonText = (activeClass == '') ?
                                    "{{ trans('lang.select_plan') }}" :
                                    "{{ trans('lang.renew_plan') }}";

                                html += `<div class="col-md-3 mt-2 mb-4 pricing-card pricing-card-subscription ${data.name}">
                                    <div class="pricing-card-inner">
                                        <div class="pricing-card-top">
                                        <div class="d-flex align-items-center pb-4">
                                            <span class="pricing-card-icon mr-4"><img src="${data.image}"></span>
                                            <h2 class="text-dark-2">${data.name} ${activeClass}</h2>
                                        </div>
                                        <p class="text-muted">${data.description}</p>
                                        <div class="pricing-card-price">
                                            <h3 class="text-dark-2">`
                                if (data.type == "paid")
                                    html +=
                                    `${currencyAtRight? parseFloat(data.price).toFixed(decimal_degits)+currentCurrency:currentCurrency+parseFloat(data.price).toFixed(decimal_degits)}</h3>`
                                else

                                    html += `<h3 class="text-dark-2" style="color:red;">Free</h3>`

                                html += `<span class="price-day">${data.expiryDay==-1? "{{ trans('lang.unlimited') }}":data.expiryDay} Days</span>
                                        </div>
                                        </div>
                                        <div class="pricing-card-content pt-3 mt-3 border-top">
                                        <ul class="pricing-card-list text-dark-2">
                                            ${features.chat? `<li><span class="mdi mdi-check"></span>${translations.chatingOption}</li>`:`<li><span class="mdi mdi-close" style="color:red;"></span>${translations.chatingOption}</li>`}
                                            ${features.ownerMobileApp? `<li><span class="mdi mdi-check"></span>${translations.mobileAppAccess}</li>`:`<li><span class="mdi mdi-close" style="color:red;"></span>${translations.mobileAppAccess}</li>`}    
                                            <li><span class="mdi mdi-check"></span>${data.orderLimit==-1? "{{ trans('lang.unlimited') }}":data.orderLimit} {{ trans('lang.bookings') }}</li>
                                            <li><span class="mdi mdi-check"></span>${data.itemLimit==-1? "{{ trans('lang.unlimited') }}":data.itemLimit} {{ trans('lang.services') }}</li>
                                        </ul>
                                        </div>`;

                                html += `<div class="pricing-card-btm">
                                                <a href="javascript:void(0)" onClick="chooseSubscriptionPlan('${data.id}')" class="btn rounded-full">${buttonText}</a>
                                            </div>`;

                                html += `</div>
                                </div>`;
                            }
                        }
                    });
                    $('#default-plan').append(html);
                });
        }

        $('input[name="set_item_limit"]').on('change', function() {

            if ($('#limited_item').is(':checked')) {
                $('.item-limit-div').removeClass('d-none');
            } else {
                $('.item-limit-div').addClass('d-none');
            }
        });

        $('input[name="set_order_limit"]').on('change', function() {
            if ($('#limited_order').is(':checked')) {
                $('.order-limit-div').removeClass('d-none');
            } else {
                $('.order-limit-div').addClass('d-none');
            }
        });

        $("#updateLimitModal").on('shown.bs.modal', function() {
            database.collection('users').where('id', '==', id).get().then(async function(snapshot) {
                var data = snapshot.docs[0].data();
                if (data.subscription_plan.itemLimit != '-1') {
                    $("#limited_item").prop('checked', true);
                    $('.item-limit-div').removeClass('d-none');
                    $('#item_limit').val(data.subscription_plan.itemLimit);
                } else {
                    $("#unlimited_item").prop('checked', true);
                }
                if (data.subscription_plan.orderLimit != '-1') {
                    $("#limited_order").prop('checked', true);
                    $('.order-limit-div').removeClass('d-none');
                    $('#order_limit').val(data.subscription_plan.orderLimit);
                } else {
                    $("#unlimited_order").prop('checked', true);
                }
            })
        })

        $('.update-plan-limit').click(async function() {

            var set_item_limit = $('input[name="set_item_limit"]:checked').val();
            var item_limit = (set_item_limit == 'limited') ? $('#item_limit').val() : '-1';
            var set_order_limit = $('input[name="set_order_limit"]:checked').val();
            var order_limit = (set_order_limit == 'limited') ? $('#order_limit').val() : '-1';

            if (set_item_limit == 'limited' && ($('#item_limit').val() == '' || $('#item_limit').val() == 0)) {
                $(".service_limit_err").html(
                    "<p>{{ trans('lang.enter_service_limit_can_not_empty_or_zero') }}</p>");
                return false;
            } else if (set_order_limit == 'limited' && ($('#order_limit').val() == '' || $('#order_limit')
                .val() == 0)) {
                $(".booking_limit_err").html(
                    "<p>{{ trans('lang.enter_booking_limit_can_not_empty_or_zero') }}</p>");
                return false;
            } else {
                await database.collection('users').doc(id).update({
                    'subscription_plan.orderLimit': order_limit,
                    'subscription_plan.itemLimit': item_limit,
                    'subscriptionTotalOrders': order_limit
                }).then(async function(result) {


                    var providerServicesSnapshot = await database.collection('providers_services')
                        .where('author', '==', id).get();
                    if (!providerServicesSnapshot.empty) {
                        providerServicesSnapshot.forEach(async (doc) => {
                            // Update each matching document
                            await database.collection('providers_services').doc(doc.id)
                                .update({
                                    'subscription_plan.orderLimit': order_limit,
                                    'subscription_plan.itemLimit': item_limit,
                                    'subscriptionTotalOrders': order_limit
                                }).then(() => {
                                    window.location.reload();
                                }).catch((error) => {
                                    console.error("Error updating document:",
                                    error);
                                });
                        });
                    }
                });
            }
        })

        database.collection('provider_orders').where('provider.author', '==', id).get().then((snapshot) => {
            jQuery("#order_count").empty();
            jQuery("#order_count").text(snapshot.docs.length);
        });
        async function sectionNameById(id) {
            var sectionSnapShot = await database.collection('sections').doc(id).get();
            var SectionData = sectionSnapShot.data();
            if (SectionData && SectionData.name) {
                return SectionData.name;
            }
            return "";
        }
        $('#section-input').on('change', function() {
            sectionId = $(this).val();
            $('#section-input').val(sectionId);
            getCommissionDataBySection();
        })
    </script>
@endsection
