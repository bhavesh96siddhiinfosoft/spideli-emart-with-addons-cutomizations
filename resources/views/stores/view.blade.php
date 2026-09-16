@extends('layouts.app')

@section('content')
    <div class="page-wrapper">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <div class="d-flex top-title-section justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/building-four.png') }}"></span>
                        <h3 class="mb-0">{{ trans('lang.vendor_details') }} <span class="itemTitle"></span></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('stores') !!}">{{ trans('lang.store_plural') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ trans('lang.vendor_details') }}</li>
                </ol>
            </div>
        </div>

        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section justify-content-between">
                            <div class="d-flex top-title-left align-self-center">
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="card-header-right">
                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#addWalletModal"
                                        class="btn-primary btn rounded-full add-wallate"><i
                                            class="mdi mdi-plus mr-2"></i>{{ trans('lang.add_wallet_amount') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="resttab-sec mb-4">
                <div class="menu-tab">
                    <ul>
                        <li class="active">
                            <a href="{{ route('stores.view', $id) }}"><i
                                    class="ri-list-indefinite"></i>{{ trans('lang.tab_basic') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('vendors.items', $id) }}"><i
                                    class="ri-shopping-basket-fill"></i>{{ trans('lang.tab_items') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('vendors.orders', $id) }}"><i class="ri-shopping-bag-line"></i>
                                {{ trans('lang.tab_orders') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('vendors.reviews', $id) }}"><i
                                    class="ri-shield-star-fill"></i>{{ trans('lang.tab_reviews') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('vendors.coupons', $id) }}"><i
                                    class="ri-discount-percent-fill"></i>{{ trans('lang.tab_promos') }}</a>
                        <li>
                            <a href="{{ route('vendors.payout', $id) }}"><i
                                    class="ri-bank-card-line"></i>{{ trans('lang.tab_payouts') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('payoutRequests.vendor.view', $id) }}"><i
                                    class="ri-refund-line"></i>{{ trans('lang.tab_payout_request') }}</a>
                        </li>
                        <li class="dine_in_future" style="display:none;">
                            <a href="{{ route('vendors.booktable', $id) }}"><i
                                    class="ri-restaurant-line"></i>{{ trans('lang.dine_in_booking_history') }}</a>
                        </li>
                        <?php if (in_array('wallet-transaction', json_decode(@session('user_permissions'),true))) { ?>
                        <li>
                            <a class="wallet_transaction"><i
                                    class="ri-wallet-line"></i>{{ trans('lang.wallet_transaction') }}</a>
                        </li>
                        <?php }?>
                        <?php
                        $subscription = route('subscription.subscriptionPlanHistory', ':id');
                        $subscription = str_replace(':id', 'storeID=' . $id, $subscription);
                        ?>
                        <li>
                            <a href="{{ $subscription }}"><i
                                    class="ri-chat-history-fill"></i>{{ trans('lang.subscription_history') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('restaurants.advertisements', $id) }}"><i
                                    class="mdi mdi-newspaper"></i>{{ trans('lang.advertisement_plural') }}</a>
                        </li>
                        @php
                                    $sectionType = $_COOKIE['service_type'] ?? ''; 
                                    
                                @endphp
                                <?php if($sectionType == 'ecommerce-service'){ ?>
                               
                                <?php }else{ ?>
                                <li class="">
                                    <a href="{{ route('restaurants.deliveryman', $id) }}" value="<?php echo $sectionType;?>"><i class="ri-riding-fill"></i>{{ trans('lang.deliveryman') }}</a>
                                </li>
                                    <?php }?>

                    </ul>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--1">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 rest_count" id="total_orders">0</h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.dashboard_total_orders') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/total_order.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--2">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 rest_active_count" id="total_items">0</h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.dashboard_total_items') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/active_restaurant.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--2">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 rest_active_count" id="total_earnings">$0.00</h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.dashboard_total_earnings') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/total_earning.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--5">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 commission_earned" id="total_payment">$0.00</h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.dashboard_total_payment') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/total_payment.png') }}"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-box-with-icon bg--5">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="card-box-with-content">
                                    <h4 class="text-dark-2 mb-1 h4 commission_earned" id="remaining_amount">$0.00</h4>
                                    <p class="mb-0 small text-dark-2">{{ trans('lang.remaining_payment') }}</p>
                                </div>
                                <span class="box-icon ab"><img src="{{ asset('images/remaining_payment.png') }}"></span>
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
                <div class="card border">
                    <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                        <div class="card-header-title">
                            <h3 class="text-dark-2 mb-0 h4">{{ trans('lang.vendor_details') }}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="restaurant_info_left">
                                    <div class="d-flex mb-1">
                                        <div class="sis-img vendor_image" id="vendor_image">
                                        </div>
                                        <div class="sis-content pl-4">
                                            <div class="sis-content-title d-flex align-items-center mb-1">
                                                <h5 class="text-dark-2 mb-0 font-18 font-semibold"><span
                                                        class="vendor_name"></span></h5><span class="sis-review"
                                                    id="vendor_reviewcount">0</span>
                                                <div class="reviewhtml"></div>
                                            </div>
                                            <ul class="p-0 info-list mb-0">
                                                <li class="d-flex align-items-center mb-2">
                                                    <label
                                                        class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.vendor_phone') }}</label>
                                                    <span class="vendor_phone"></span>
                                                </li>
                                                <li class="d-flex align-items-center mb-2">
                                                    <label
                                                        class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.vendor_address') }}</label>
                                                    <span class="vendor_address"></span>
                                                </li>
                                                <li class="d-flex align-items-center mb-2">
                                                    <label
                                                        class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.vendor_cuisines') }}</label>
                                                    <span class="vendor_cuisines"></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label
                                                class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.vendor_description') }}</label>
                                            <p><span class="vendor_description"></span></p>
                                        </div>
                                        <div class="col-md-3">
                                            <label
                                                class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.zone_plural') }}</label>
                                            <p><span class="zone_name"></span></p>
                                        </div>
                                        <div class="col-md-3">
                                            <label
                                                class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.section') }}</label>
                                            <p><span class="vendor_section"></span></p>
                                        </div>
                                        <div class="col-md-3">
                                            <label
                                                class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.wallet_Balance') }}</label>
                                            <p><span class="wallet"></span></p>
                                        </div>
                                    </div>
                                    <div class="row store-status" style="display:none">
                                        <div class="col-md-9">
                                            <label
                                                class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.vendor_status') }}</label>
                                            <p><span class="vendor_avtive"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="map-box">
                                    <div class="mapouter">
                                        <div class="gmap_canvas"><iframe class="gmap_iframe" width="100%"
                                                frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                                                src="https://maps.google.com/maps?width=600&amp;height=225&amp;hl=en&amp;q=University of Oxford&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe><a
                                                href="https://sprunkiplay.com/">Sprunki Game</a></div>
                                        <style>
                                            .mapouter {
                                                position: relative;
                                                text-align: right;
                                                width: 100%;
                                                height: 225px;
                                            }

                                            .gmap_canvas {
                                                overflow: hidden;
                                                background: none !important;
                                                width: 100%;
                                                height: 225px;
                                            }

                                            .gmap_iframe {
                                                height: 225px !important;
                                            }
                                        </style>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="restaurant_info-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-0 h4">{{ trans('lang.vendor_details') }}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.name') }}</label>
                                        <p><span class="vendor_name"></span></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.vendor_phone') }}</label>
                                        <p><span class="vendor_phoneNumber"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.email') }}</label>
                                        <p><span class="vendor_email"></span></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label
                                            class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.vendor_image') }}</label>
                                        <p><img src="" class="vendor_owner_image" alt="Vendor Image" style="width: 50px"></p> 
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-0 h4">{{ trans('lang.gallery') }}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="restaurant_gallery">
                                    <div id="photos"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="restaurant_info-section">
                <div class="row">
                    <div class="col-md-6" id="services_feature">
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-0 h4">{{ trans('lang.services') }}</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="restaurant_service">
                                    <ul class="p-0 " id="filtershtml">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-0 h4">{{ trans('lang.active_subscription_plan') }}</h3>
                                </div>
                                <div class="update-limit-div" style="display:none">
                                    <div class="active-sub-plan" style="margin-bottom:0px;">
                                        <a href="javascript:void(0)" data-toggle="modal" data-target="#updateLimitModal"
                                            class="btn-primary btn rounded-full update-limit">{{ trans('lang.update_plan_limit') }}</a>
                                    </div>
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
                                                class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.order_limit') }}</label>
                                            <p><span class="order_limit"></span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="active-sub-plan">
                                            <label
                                                class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.item_limit') }}</label>
                                            <p><span class="item_limit"></span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="active-sub-plan">
                                            <label
                                                class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.available_order_limit') }}</label>
                                            <p><span class="available_order_limit"></span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="active-sub-plan">
                                            <label
                                                class="mb-1 font-wi font-semibold text-dark-2">{{ trans('lang.available_item_limit') }}</label>
                                            <p><span class="available_item_limit"></span></p>
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
        </div>
    </div>

    <div class="modal fade" id="addWalletModal" tabindex="-1" role="dialog" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered location_modal">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title locationModalTitle">{{ trans('lang.add_wallet_amount') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>

                <div class="modal-body">

                    <form class="">

                        <div class="form-row">

                            <div class="form-group row">

                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.amount') }}</label>
                                    <div class="col-12">
                                        <input type="number" name="amount" class="form-control" id="amount">
                                        <div id="wallet_error" style="color:red"></div>
                                    </div>
                                </div>

                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.note') }}</label>
                                    <div class="col-12">
                                        <input type="text" name="note" class="form-control" id="note">
                                    </div>
                                </div>

                                <div class="form-group row width-100">

                                    <div id="user_account_not_found_error" class="align-items-center" style="color:red">
                                    </div>
                                </div>

                            </div>

                        </div>

                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="add-wallet-btn">{{ trans('submit') }}
                        </button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                            {{ trans('close') }}
                        </button>

                    </div>

                </div>
            </div>

        </div>

    </div>

    <div class="modal fade" id="changeSubscriptionModal" tabindex="-1" role="dialog" aria-hidden="true"
        style="width: 100%">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="text-dark-2 h5 mb-0">{{ trans('lang.business_plans') }}</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form class="">
                        <div class="subscription-section">
                            <div class="subscription-section-inner">
                                <div class="card-body">
                                    <div class="row" id="plan-details"></div>
                                    <div class="pay-method-section pt-4 manual_pay_div">
                                        <h6 class="text-dark-2 h6 mb-3 pb-3">{{ trans('lang.pay_via_online') }}</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="pay-method-box d-flex align-items-center">
                                                    <div class="pay-method-icon">
                                                        <img src="{{ asset('images/wallet_icon_ic.png') }}">
                                                    </div>
                                                    <div class="form-check">
                                                        <h6 class="text-dark-2 h6 mb-0">{{ trans('lang.manual_pay') }}
                                                        </h6>
                                                        <input type="radio" id="manual_pay" name="payment_method"
                                                            value="manual_pay" checked="">
                                                        <label class="control-label mb-0" for="manual_pay"></label>
                                                    </div>
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
                                    <label class="control-label">{{ trans('lang.maximum_order_limit') }}</label>
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
                                    <span class="order_limit_err"></span>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="control-label">{{ trans('lang.maximum_item_limit') }}</label>
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
                                    <span class="item_limit_err"></span>
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
        var serviceType = getCookie('service_type');
        var database = firebase.firestore();
        var ref = database.collection('vendors').where("id", "==", id);
        var photo = "";
        var vendorOwnerId = "";
        var vendorOwnerOnline = false;

        var placeholderImage = '';
        var placeholder = database.collection('settings').doc('placeHolderImage');
        var sectionId = '';
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })

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
        });
        var commisionModel = false;
        var AdminCommission = '';
        var subscriptionModel = false;
        ref.get().then(async function(snapshots) {
            if (snapshots.docs.length > 0) {

                var vendor = snapshots.docs[0].data();

                /* A store outside the region the admin is working in must not
                 * be reachable by editing the url. */
                if (!isInActiveRegion(vendor)) {
                    window.location.href = '{{ route('stores') }}';
                    return;
                }

                sectionId = vendor.section_id;
                if (vendor.hasOwnProperty('adminCommission') && vendor.adminCommission != null && vendor
                    .adminCommission != '') {
                    if (vendor.adminCommission.type == "percentage") {
                        AdminCommission = vendor.adminCommission.commission + '' + '%';
                    } else {
                        if (currencyAtRight) {
                            AdminCommission = vendor.adminCommission.commission.toFixed(decimal_degits) +
                                currentCurrency;
                        } else {
                            AdminCommission = currentCurrency + vendor.adminCommission.commission.toFixed(
                                decimal_degits);
                        }
                    }

                }
                database.collection('sections').where('id', '==', vendor.section_id).get().then((
                    querySnapshot) => {
                        var commissionSetting = querySnapshot.docs[0].data();
                        if (commissionSetting.adminCommision.enable == true) {
                            commisionModel = true;
                        }
                        if (AdminCommission == '') {
                            if (commissionSetting.adminCommision.type == "percentage") {
                                AdminCommission = commissionSetting.adminCommision.commission + '' + '%';
                            } else {
                                if (currencyAtRight) {
                                    AdminCommission = commissionSetting.adminCommision.commission.toFixed(
                                        decimal_degits) + currentCurrency;
                                } else {
                                    AdminCommission = currentCurrency + commissionSetting.adminCommision
                                        .commission.toFixed(decimal_degits);
                                }
                            }
                        }
                        if (commissionSetting.dine_in_active == true) {                            
                            $("#services_feature").show();
                        }else{
                            $("#services_feature").hide();                             
                        }
                    });

                database.collection('settings').doc("vendor").get().then(async function(snapshots) {
                    var businessModelSettings = snapshots.data();
                    if (businessModelSettings.hasOwnProperty('subscription_model') &&
                        businessModelSettings.subscription_model == true) {
                        subscriptionModel = true;
                    }
                });
            }
        });



        var email_templates = database.collection('email_templates').where('type', '==', 'wallet_topup');

        var emailTemplatesData = null;

        $(document).ready(async function() {
            jQuery("#data-table_processing").show();

            await email_templates.get().then(async function(snapshots) {
                emailTemplatesData = snapshots.docs[0].data();
            });

            if(serviceType !== 'ecommerce-service'){
                $('.store-status').show();
            }else{
                $('.store-status').hide();
            }

            var orders = await getTotalOrders();
            var items = await getTotalItems();
            var earnings = await getTotalEarnings();
            var payment = await getTotalpayment();

            var remaining = earnings - payment;

            if (currencyAtRight) {
                remaining_with_currency = parseFloat(remaining).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                remaining_with_currency = currentCurrency + "" + parseFloat(remaining).toFixed(decimal_degits);
            }

            $("#remaining_amount").text(remaining_with_currency);

            ref.get().then(async function(snapshots) {
                if (snapshots.docs.length > 0) {

                    var vendor = snapshots.docs[0].data();

                    var categoryTitles = vendor.categoryTitle;
                    var categoryTitleString = Array.isArray(categoryTitles) ?
                        (categoryTitles.length > 0 ? categoryTitles.join(', ') :
                            'No categories available') :
                        (typeof categoryTitles === 'string' && categoryTitles.trim() !== '' ?
                            categoryTitles : 'No categories available');

                    // Set the category title in the appropriate HTML element
                    $(".vendor_cuisines").text(categoryTitleString);

                    var wallet_route = "{{ route('users.walletstransaction', 'id') }}";
                    wallet_route = wallet_route.replace('id', 'storeID=' + vendor.author);

                    $(".wallet_transaction").attr("href", wallet_route.replace('id', 'storeID=' +
                        vendor.author));

                    $(".vendor_name").text(vendor.title);
                    $(".itemTitle").text(' - ' + vendor.title);

                    var rating = 0;
                    if (vendor.hasOwnProperty('reviewsCount') && vendor.reviewsCount != 0) {
                        rating = Math.round(parseFloat(vendor.reviewsSum) / parseInt(vendor
                            .reviewsCount));
                    } else {
                        rating = 0;
                    }

                    const walletBalance = getWalletBalance(vendor.author);

                    var review = '<ul class="rating" data-rating="' + rating + '">';
                    review = review + '<li class="rating__item"></li>';
                    review = review + '<li class="rating__item"></li>';
                    review = review + '<li class="rating__item"></li>';
                    review = review + '<li class="rating__item"></li>';
                    review = review + '<li class="rating__item"></li>';
                    review = review + '</ul>';
                    $("#vendor_reviewcount").text(vendor.reviewsCount);


                    $(".vendor_avtive").html("{{ trans('lang.closed') }}");
                    $(".vendor_avtive").addClass('closed');

                    var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday',
                        'Saturday'
                    ];

                    var currentdate = new Date();

                    var currentDay = days[currentdate.getDay()];

                    var hour = currentdate.getHours();

                    var minute = currentdate.getMinutes();
                    if (hour < 10) {
                        hour = '0' + hour
                    }
                    if (minute < 10) {
                        minute = '0' + minute
                    }
                    var currentHours = hour + ':' + minute;

                    if (vendor.hasOwnProperty('subscriptionExpiryDate') && vendor.hasOwnProperty(
                            'subscriptionPlanId') && vendor.subscriptionPlanId != '' && vendor
                        .subscriptionPlanId != null) {
                        $(".update-limit-div").show();
                        $(".plan_name").html(vendor.subscription_plan.name);
                        $(".plan_type").html(vendor.subscription_plan.type);
                        if (vendor.subscriptionExpiryDate != null && vendor
                            .subscriptionExpiryDate != '') {
                            date = vendor.subscriptionExpiryDate.toDate().toDateString();
                            time = vendor.subscriptionExpiryDate.toDate().toLocaleTimeString(
                                'en-US');
                            $(".plan_expire_at").html(date + ' ' + time);
                            $(".plan_expire_date").html(date);
                        } else {
                            $(".plan_expire_at").html("{{ trans('lang.unlimited') }}");
                            $(".plan_expire_date").html("{{ trans('lang.unlimited') }}");
                        }
                        var number_of_days = vendor.subscription_plan.expiryDay == "-1" ?
                            'Unlimited' : vendor.subscription_plan.expiryDay + " Days";
                        $(".number_of_days").html(number_of_days);
                        if (currencyAtRight) {
                            $(".plan_price").html(parseFloat(vendor.subscription_plan.price)
                                .toFixed(decimal_degits) + currentCurrency);
                        } else {
                            $(".plan_price").html(currentCurrency + parseFloat(vendor
                                .subscription_plan.price).toFixed(decimal_degits));
                        }
                        $('.order_limit').html((vendor.subscription_plan.orderLimit == '-1') ?
                            "{{ trans('lang.unlimited') }}" : vendor.subscription_plan
                            .orderLimit);
                        $('.item_limit').html((vendor.subscription_plan.itemLimit == '-1') ?
                            "{{ trans('lang.unlimited') }}" : vendor.subscription_plan
                            .itemLimit);

                        $('.available_order_limit').html((vendor.subscriptionTotalOrders == '-1') ?
                            "{{ trans('lang.unlimited') }}" : vendor.subscriptionTotalOrders);

                        var snapshot = await database.collection('vendor_products').where(
                            'vendorID', '==', id).get();
                        var totalProductCount = snapshot.size;
                        if (vendor.subscription_plan.itemLimit == '-1') {
                            $('.available_item_limit').html("Unlimited");
                        } else {
                            availableItemLimit = parseInt(vendor.subscription_plan.itemLimit) -
                                parseInt(totalProductCount);
                            if (availableItemLimit < 0) {
                                $('.available_item_limit').html(0);
                            } else {
                                $('.available_item_limit').html(availableItemLimit);
                            }
                        }
                        if (vendor.subscription_plan.hasOwnProperty('features')) {
                            const translations = {
                                chatingOption: "{{ trans('lang.chat') }}",
                                generateQrCode: "{{ trans('lang.generate_qr_code') }}",
                                mobileAppAccess: "{{ trans('lang.mobile_app') }}"
                            };
                            var features = vendor.subscription_plan.features;
                            var html = `<ul class="pricing-card-list text-dark-2">
                                                ${features.chat? `<li>${translations.chatingOption}</li>`:''}
                                                
                                                ${features.qrCodeGenerate? `<li>${translations.generateQrCode}</li>`:''}
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
                        $(".available_order_limit").html('N/A');
                        $(".available_item_limit").html('N/A');
                        $(".plan_features").html('N/A');
                    }

                    if (vendor.hasOwnProperty('workingHours')) {
                        for (i = 0; i < vendor.workingHours.length; i++) {
                            var day = vendor.workingHours[i]['day'];
                            if (vendor.workingHours[i]['day'] == currentDay) {
                                if (vendor.workingHours[i]['timeslot'].length != 0) {
                                    for (j = 0; j < vendor.workingHours[i]['timeslot']
                                        .length; j++) {
                                        var timeslot = vendor.workingHours[i]['timeslot'][j];
                                        var TimeslotHourVar = {
                                            'from': timeslot[`from`],
                                            'to': timeslot[`to`],
                                            'closeingType': timeslot[`closeingType`]
                                        };
                                        var [h, m] = timeslot[`from`].split(":");
                                        var from = ((h % 12 ? h % 12 : 12) + ":" + m, h >= 12 ?
                                            'PM' : 'AM');

                                        var [h2, m2] = timeslot[`to`].split(":");
                                        var to = ((h2 % 12 ? h2 % 12 : 12) + ":" + m2, h2 >= 12 ?
                                            'PM' : 'AM');

                                        if (currentHours >= timeslot[`from`] && currentHours <=
                                            timeslot[`to`]) {
                                            $(".vendor_avtive").html("{{ trans('lang.open') }}");
                                            $(".vendor_avtive").removeClass('close');
                                            $(".vendor_avtive").addClass('open');
                                            $(".vendor_avtive").append(" (" + timeslot[`from`] +
                                                ' ' + from + ' - ' + timeslot[`to`] + ' ' + to +
                                                ")");

                                        }

                                    }
                                }
                            }
                        }

                    }

                    var photos = '';
                    if (vendor.photos.length > 0) {
                        vendor.photos.forEach((photo) => {
                            photos = photos +
                                '<span class="image-item"><img width="100px" id="" height="auto" src="' +
                                photo + '" onerror="this.onerror=null;this.src=\'' +
                                placeholderImage + '\'"></span>';
                        })
                    }
                    if (photos) {
                        $("#photos").html(photos);
                    } else {
                        $("#photos").html('<p>photos not available.</p>');
                    }

                    var image = "";
                    if (vendor.photo) {
                        if (vendor.photo) {
                            photo = vendor.photo;
                        } else {
                            photo = placeholderImage;
                        }
                        image = '<img width="200px" id="" height="auto" src="' + photo +
                            '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                    } else {
                        image = '<img width="200px" id="" height="auto" src="' + placeholderImage +
                            '">';
                    }
                    $("#vendor_image").html(image);
                    $(".reviewhtml").html(review);

                    filtershtml = '';
                    for (var key in vendor.filters) {
                        filtershtml = filtershtml + '<li>' + key + ': ' + vendor.filters[key] +
                            '</li>';
                    }
                    if (filtershtml != '') {
                        $("#filtershtml").html(filtershtml);
                    } else {
                        $("#filtershtml").html('{{ trans('lang.service_not_available') }}')
                    }



                    await database.collection('vendor_categories').get().then(async function(
                        snapshots) {
                        snapshots.docs.forEach((listval) => {
                            var data = listval.data();
                            if (data.id == vendor.categoryID) {
                                $(".vendor_cuisines").text(data.title);
                            }
                        })

                        var categoryTitleString = Array.isArray(categoryTitles) ?
                            (categoryTitles.length > 0 ? categoryTitles.join(', ') :
                                'No categories available') :
                            (typeof categoryTitles === 'string' && categoryTitles
                            .trim() !== '' ? categoryTitles : 'No categories available'
                                );

                        $(".vendor_cuisines").text(categoryTitleString);
                    });


                    await database.collection('sections').where('isActive', '==', true).orderBy('order').get().then(async function(snapshots) {
                        snapshots.docs.forEach((listval) => {
                            var data = listval.data();
                            if (data.id == vendor.section_id) {
                                $(".vendor_section").text(data.name);
                                if (data.serviceTypeFlag ==
                                    "delivery-service") {
                                    $('.multivendor_status_div').removeClass(
                                        'd-none');
                                } else {
                                    $('.multivendor_status_div').html('');
                                }

                                if (data.dine_in_active === true) {
                                    $(".dine_in_future").show();
                                }
                            }

                        })
                    });

                    $(".vendor_address").text(vendor.location);
                    $(".vendor_latitude").text(vendor.latitude);
                    $(".vendor_longitude").text(vendor.longitude);
                    $(".vendor_description").text(vendor.description);
                   
                    let profilePic = vendor.authorProfilePic;

                    if (!profilePic || profilePic.trim() === "" || profilePic === "null") {
                        profilePic = placeholderImage;
                    }

                    $(".vendor_owner_image").attr("src", profilePic)
                        .on("error", function () {                           
                            $(this).attr("src", placeholderImage);
                        });


                    if (vendor.hasOwnProperty('zoneId') && vendor.zoneId) {
                        database.collection('zone').where('id', '==', vendor.zoneId).get().then(
                            async function(zoneSnapshots) {
                                if (!zoneSnapshots.empty) {
                                    var zoneData = zoneSnapshots.docs[0].data();
                                    var zoneTitle = zoneData.name || 'Unknown Zone';
                                    $(".zone_name").text(zoneTitle);
                                } else {
                                    $(".zone_name").text("Not found Zone");
                                }
                            }).catch(function(error) {
                            console.log("Error fetching zone:", error);
                            $(".zone_name").text("Error loading zone");
                        });
                    } else {
                        $(".zone_name").text("No zone assigned");
                    }

                    if (
                        vendor.hasOwnProperty('latitude') &&
                        vendor.hasOwnProperty('longitude') &&
                        !isNaN(vendor.latitude) &&
                        !isNaN(vendor.longitude) &&
                        vendor.latitude !== null &&
                        vendor.longitude !== null
                    ) {
                        var lat = vendor.latitude;
                        var lng = vendor.longitude;
                        var mapSrc =
                            `https://maps.google.com/maps?width=600&height=225&hl=en&q=${lat},${lng}&t=&z=14&ie=UTF8&iwloc=B&output=embed`;
                        $(".gmap_iframe").attr("src", mapSrc);
                    } else {
                        $(".mapouter").html("<p>Location data not available for this vendor.</p>");
                    }
                    vendorOwnerOnline = vendor.isActive;
                    photo = vendor.photo;
                    vendorOwnerId = vendor.author;
                    await database.collection('users').where("id", "==", vendor.author).get().then(
                        async function(snapshots) {
                            snapshots.docs.forEach((listval) => {
                                var user = listval.data();

                                $(".vendor_email").html(shortEmail(user.email));
                                if (user.phoneNumber.includes('+')) {
                                    $(".vendor_phoneNumber").html('+' +
                                        EditPhoneNumber(user.phoneNumber.slice(
                                            1)));
                                } else {
                                    $(".vendor_phoneNumber").html(EditPhoneNumber(
                                        user.phoneNumber));
                                }

                            })
                        });

                    await database.collection('vendor_categories').get().then(async function(
                        snapshots) {
                        snapshots.docs.forEach((listval) => {
                            var data = listval.data();
                            if (data.id == vendor.categoryID) {
                                $('#vendor_cuisines').append($(
                                        "<option selected></option>")
                                    .attr("value", data.id)
                                    .text(data.title));
                            } else {
                                $('#vendor_cuisines').append($(
                                        "<option></option>")
                                    .attr("value", data.id)
                                    .text(data.title));
                            }
                        })

                    });

                    if (vendor.hasOwnProperty('phonenumber')) {
                        if (vendor.phonenumber.includes('+')) {
                            $(".vendor_phone").text('+' + EditPhoneNumber(vendor.phonenumber.slice(
                                1)));
                        } else {
                            $(".vendor_phone").text(EditPhoneNumber(vendor.phonenumber));
                        }
                    }
                } else {
                    $('.vendor_detail_div').html(
                        '<h5 class="text-danger text-center font-weight-bold">{{ trans('lang.vendor_unknown_deleted') }}</h5>'
                        )
                }
                jQuery("#data-table_processing").hide();
            })


            $(".save_vendor_btn").click(function() {
                var vendorname = $(".vendor_name").val();
                var cuisines = $("#vendor_cuisines option:selected").val();
                var address = $(".vendor_address").val();
                var latitude = parseFloat($(".vendor_latitude").val());
                var longitude = parseFloat($(".vendor_longitude").val());
                var description = $(".vendor_description").val();
                var zoneName = $(".zone_name").val();
                var phonenumber = $(".vendor_phone").val();
                var categoryTitle = $("#vendor_cuisines option:selected").text();

                database.collection('vendors').doc(id).update({
                    'title': vendorname,
                    'description': description,
                    'latitude': latitude,
                    'longitude': longitude,
                    'location': address,
                    'photo': photo,
                    'categoryID': cuisines,
                    'phonenumber': phonenumber,
                    'categoryTitle': categoryTitle
                }).then(function(result) {
                    window.location.href = '{{ route('stores') }}';
                });
            })

        })

        async function getWalletBalance(vendorId) {
            database.collection('users').where('id', '==', vendorId).get().then(async function(snapshot) {
                if (snapshot.docs.length > 0) {
                    restaurant = snapshot.docs[0].data();
                    var wallet_balance = 0;

                    if (restaurant.hasOwnProperty('wallet_amount') && restaurant.wallet_amount != null && !
                        isNaN(restaurant.wallet_amount)) {
                        wallet_balance = restaurant.wallet_amount;
                    }
                    if (currencyAtRight) {
                        wallet_balance = parseFloat(wallet_balance).toFixed(decimal_degits) + "" +
                            currentCurrency;
                    } else {
                        wallet_balance = currentCurrency + "" + parseFloat(wallet_balance).toFixed(
                            decimal_degits);
                    }

                    $('.wallet').html(wallet_balance);

                }
            });

        }

        var storageRef = firebase.storage().ref('images');

        function handleFileSelect(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {

                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')

                    var timestamp = Number(new Date());
                    var uploadTask = storageRef.child(filename).put(theFile);
                    uploadTask.on('state_changed', function(snapshot) {

                        var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                        console.log('Upload is ' + progress + '% done');
                        jQuery("#uploding_image").text("Image is uploading...");
                    }, function(error) {}, function() {
                        uploadTask.snapshot.ref.getDownloadURL().then(function(downloadURL) {
                            jQuery("#uploding_image").text("Upload is completed");
                            photo = downloadURL;

                        });
                    });

                };
            })(f);
            reader.readAsDataURL(f);
        }

        async function getTotalOrders() {

            await database.collection('vendor_orders').where('vendorID', '==', '<?php echo $id; ?>').get().then(
            async function(orderSnapshots) {
                    var paymentData = orderSnapshots.docs;
                    $("#total_orders").text(paymentData.length);
                })
        }

        async function getTotalItems() {

            await database.collection('vendor_products').where('vendorID', '==', '<?php echo $id; ?>').get().then(
            async function(orderSnapshots) {
                    var itemsData = orderSnapshots.docs;
                    $("#total_items").text(itemsData.length);
                })
        }

        async function getTotalEarnings() {
            var totalEarning = 0;
            var adminCommission = 0;
            await database.collection('vendor_orders').where('vendorID', '==', '<?php echo $id; ?>').where('status',
                'in', ["Order Completed"]).get().then(async function(orderSnapshots) {
                var paymentData = orderSnapshots.docs;
                paymentData.forEach((order) => {
                    var orderData = order.data();
                    var price = 0;
                    if (orderData.adminCommission != undefined) {
                        var commission = parseInt(orderData.adminCommission);
                        adminCommission = commission + adminCommission;
                    }
                    orderData.products.forEach((product) => {

                        if (product.price && product.quantity != 0) {
                            var productTotal = parseInt(product.price) * parseInt(product
                                .quantity);
                            price = price + productTotal;
                        }
                    })
                    totalEarning = totalEarning + price;
                })

                if (currencyAtRight) {
                    totalEarningwithCurrency = parseFloat(totalEarning).toFixed(decimal_degits) + "" +
                        currentCurrency;
                } else {
                    totalEarningwithCurrency = currentCurrency + "" + parseFloat(totalEarning).toFixed(
                        decimal_degits);
                }

                $("#total_earnings").text(totalEarningwithCurrency);

            })
            return totalEarning;
        }


        async function getTotalpayment(driverID) {
            var paid_price = 0;
            var total_price = 0;
            var remaining = 0;
            await database.collection('payouts').where('vendorID', '==', '<?php echo $id; ?>').get().then(
            async function(payoutSnapshots) {
                    payoutSnapshots.docs.forEach((payout) => {
                        var payoutData = payout.data();
                        if (payoutData.amount && parseFloat(payoutData.amount) != undefined &&
                            parseFloat(payoutData.amount) != '' && parseFloat(payoutData.amount) != NaN
                            ) {
                            paid_price = parseFloat(paid_price) + parseFloat(payoutData.amount);
                        }

                    })
                });

            if (currencyAtRight) {
                paid_price_with_currency = parseFloat(paid_price).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                paid_price_with_currency = currentCurrency + "" + parseFloat(paid_price).toFixed(decimal_degits);
            }

            $("#total_payment").text(paid_price_with_currency);
            return paid_price;
        }

        $("#add-wallet-btn").click(function() {
            var date = firebase.firestore.FieldValue.serverTimestamp();
            var amount = $('#amount').val();
            if (amount == '' || amount <= 0) {
                $('#wallet_error').text('{{ trans('lang.add_wallet_amount_error') }}');
                return false;
            }
            var note = $('#note').val();
            database.collection('users').where('id', '==', vendorOwnerId).get().then(async function(snapshot) {

                if (snapshot.docs.length > 0) {
                    var data = snapshot.docs[0].data();

                    user_id = data.id;

                    /* This screen shows the STORE's balance, so a top-up here has
                     * to reach the store as well as the account - otherwise the
                     * figure above would not move when an admin adds money. */
                    var newWalletAmount = (await storeWalletAmount(id)) + parseFloat(amount);

                    applyVendorWalletDelta(id, vendorOwnerId, parseFloat(amount)).then(function(result) {
                        var tempId = database.collection("tmp").doc().id;
                        database.collection('wallet').doc(tempId).set({
                            'amount': parseFloat(amount),
                            'date': date,
                            'isTopUp': true,
                            'id': tempId,
                            'order_id': '',
                            'payment_method': 'Wallet',
                            'payment_status': 'success',
                            'user_id': user_id,
                            'note': note,
                            'transactionUser': "vendor",

                        }).then(async function(result) {
                            if (currencyAtRight) {
                                amount = parseInt(amount).toFixed(decimal_degits) +
                                    "" + currentCurrency;
                                newWalletAmount = newWalletAmount.toFixed(
                                    decimal_degits) + "" + currentCurrency;
                            } else {
                                amount = currentCurrency + "" + parseInt(amount)
                                    .toFixed(decimal_degits);
                                newWalletAmount = currentCurrency + "" +
                                    newWalletAmount.toFixed(decimal_degits);
                            }

                            var formattedDate = new Date();
                            var month = formattedDate.getMonth() + 1;
                            var day = formattedDate.getDate();
                            var year = formattedDate.getFullYear();

                            month = month < 10 ? '0' + month : month;
                            day = day < 10 ? '0' + day : day;

                            formattedDate = day + '-' + month + '-' + year;

                            var message = emailTemplatesData.message;
                            message = message.replace(/{username}/g, data
                                .firstName + ' ' + data.lastName);
                            message = message.replace(/{date}/g, formattedDate);
                            message = message.replace(/{amount}/g, amount);
                            message = message.replace(/{paymentmethod}/g, 'Wallet');
                            message = message.replace(/{transactionid}/g, tempId);
                            message = message.replace(/{newwalletbalance}/g,
                                newWalletAmount);

                            emailTemplatesData.message = message;

                            var url = "{{ url('send-email') }}";
                            if (data.email != '' && data.email != null) {
                                var sendEmailStatus = await sendEmail(url,
                                    emailTemplatesData.subject,
                                    emailTemplatesData.message, [data.email]);

                                if (sendEmailStatus) {
                                    window.location.reload();
                                }
                            } else {
                                window.location.reload();
                            }
                        })
                    })
                } else {
                    $('#user_account_not_found_error').text(
                        '{{ trans('lang.user_detail_not_found') }}');
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
        async function getSubscriptionPlan() {
            $('#default-plan').html('');
            var activeSubscriptionId = '';
            var snapshots = await database.collection('subscription_history').where('user_id', '==', vendorOwnerId)
                .orderBy('createdAt', 'desc').get();
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
                                html += `<div class="col-md-3 mb-3 pricing-card pricing-card-commission">
                                    <div class="pricing-card-inner">
                                        <div class="pricing-card-top">
                                            <div class="d-flex align-items-center pb-4">
                                                <span class="pricing-card-icon mr-4"><img src="${data.image}"></span>
                                            </div>
                                            <div class="pricing-card-price">
                                                <h3 class="text-dark-2">${data.name} ${activeClass}</h3>
                                                <span class="price-day">${data.description}</span>
                                                <h3 class="text-dark-2">`
                                if (data.type == "paid")
                                    html +=
                                    `${currencyAtRight? parseFloat(data.price).toFixed(decimal_degits)+currentCurrency:currentCurrency+parseFloat(data.price).toFixed(decimal_degits)}</h3>`
                                else
                                    html += `<h3 class="text-dark-2" style="color:red;">Free</h3>`
                                html += `<span class="price-day">${data.expiryDay==-1? "{{ trans('lang.unlimited') }}":data.expiryDay} Days</span></div>
                                            
                                        </div>
                                        <div class="pricing-card-content pt-3 mt-3 border-top">
                                            <ul class="pricing-card-list text-dark-2">`;
                                html +=
                                    `<li><span class="mdi mdi-check"></span>{{ trans('lang.pay_commission_of') }} ${AdminCommission} {{ trans('lang.on_each_order') }} </li>`
                                data.plan_points.map(async (list) => {
                                    html +=
                                        `<li><span class="mdi mdi-check"></span>${list}</li>`
                                });
                                html +=
                                    `<li><span class="mdi mdi-check"></span>{{ trans('lang.unlimited') }} {{ trans('lang.orders') }}</li>`
                                html +=
                                    `<li><span class="mdi mdi-check"></span>{{ trans('lang.unlimited') }} {{ trans('lang.products') }}</li>`
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
                                    generateQrCode: "{{ trans('lang.generate_qr_code') }}",
                                    mobileAppAccess: "{{ trans('lang.mobile_app_access') }}"
                                };
                                var features = data.features;
                                var buttonText = (activeClass == '') ?
                                    "{{ trans('lang.select_plan') }}" :
                                    "{{ trans('lang.renew_plan') }}";

                                html += `<div class="col-md-3 mt-2 pricing-card pricing-card-subscription ${data.name}">
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
                                    ${features.qrCodeGenerate? `<li><span class="mdi mdi-check"></span>${translations.generateQrCode}</li>`:`<li><span class="mdi mdi-close" style="color:red;"></span>${translations.generateQrCode}</li>`}
                                    ${features.ownerMobileApp? `<li><span class="mdi mdi-check"></span>${translations.mobileAppAccess}</li>`:`<li><span class="mdi mdi-close" style="color:red;"></span>${translations.mobileAppAccess}</li>`}     
                                    <li><span class="mdi mdi-check"></span>${data.orderLimit==-1? "{{ trans('lang.unlimited') }}":data.orderLimit} {{ trans('lang.orders') }}</li>
                                    <li><span class="mdi mdi-check"></span>${data.itemLimit==-1? "{{ trans('lang.unlimited') }}":data.itemLimit} {{ trans('lang.products') }}</li>
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
        async function showPlanDetail(planId) {
            $("#plan_id").val(planId);
            var activePlan = '';
            var snapshots = await database.collection('subscription_history').where('user_id', '==', vendorOwnerId)
                .orderBy('createdAt', 'desc').get();
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
            let choosedPlan_price;
            if (parseInt(choosedPlan.price) != 0) {
                choosedPlan_price = currencyAtRight ? parseFloat(choosedPlan.price).toFixed(decimal_degits) +
                    currentCurrency :
                    currentCurrency + parseFloat(choosedPlan.price).toFixed(decimal_degits);
                $('.manual_pay_div').removeClass('d-none');
            } else {
                choosedPlan_price = 'Free';
                $('.manual_pay_div').addClass('d-none');
            }

            if (activePlan) {
                let activePlan_price;
                if (parseInt(activePlan.price) != 0) {

                    activePlan_price = currencyAtRight ? parseFloat(activePlan.price).toFixed(decimal_degits) +
                        currentCurrency :
                        currentCurrency + parseFloat(activePlan.price).toFixed(decimal_degits);

                } else {

                    activePlan_price = 'Free';

                }

                html += ` 
            <div class="col-md-8">
                <div class="subscription-card-left"> 
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="subscription-card text-center">
                                <div class="d-flex align-items-center pb-3 justify-content-center">
                                    <span class="pricing-card-icon mr-4"><img src="${activePlan.image}"></span>
                                    <h2 class="text-dark-2 mb-0 font-weight-semibold">${activePlan.isCommissionPlan==true? "{{ trans('lang.commission') }}":activePlan.name}</h2>
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
                                    </h2></div>
                                <h3 class="text-dark-2">${choosedPlan_price}</h3>
                                <p class="text-center">${choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</p>
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
                        <span class="font-weight-medium">{{ trans('lang.price') }}</span>
                        <span class="font-weight-semibold">${choosedPlan_price} </span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.bill_status') }}</span>
                        <span class="font-weight-semibold">{{ trans('lang.migrate_to_new_plan') }}</span>
                    </div>
                </div>
            </div>`;
            } else {
                html += ` 
            <div class="col-md-6">
                <div class="subscription-card-left"> 
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="subscription-card text-center">
                                <div class="d-flex align-items-center pb-3 justify-content-center">
                                    <span class="pricing-card-icon mr-4"><img src="${choosedPlan.image}"></span>
                                    <h2 class="text-dark-2 mb-0 font-weight-semibold">${choosedPlan.isCommissionPlan==true? "{{ trans('lang.commission') }}":choosedPlan.name}</h2>
                                </div>
                                <h3 class="text-dark-2">${activePlan.isCommissionPlan==true ? AdminCommission+" {{ trans('lang.base_plan') }}":choosedPlan_price}</h3>
                                <p class="text-center">${choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</p>
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
                        <span class="font-weight-semibold">${choosedPlan.expiryDay=="-1"? "{{ trans('lang.unlimited') }}":choosedPlan.expiryDay+" {{ trans('lang.days') }}"}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.price') }}</span>
                        <span class="font-weight-semibold">${choosedPlan_price}</span>
                    </div>
                    <div
                        class="d-flex justify-content-between align-items-center py-3 px-3 text-dark-2">
                        <span class="font-weight-medium">{{ trans('lang.bill_status') }}</span>
                        <span class="font-weight-semibold">{{ trans('lang.migrate_to_new_plan') }}</span>
                    </div>
                </div>
            </div>`;
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
                var snapshot = await database.collection('subscription_plans').doc(planId).get();
                if (snapshot.exists) {
                    var planData = snapshot.data();
                    var createdAt = firebase.firestore.FieldValue.serverTimestamp();
                    if (planData.expiryDay == "-1") {
                        var expiryDay = null
                    } else {
                        var currentDate = new Date();
                        currentDate.setDate(currentDate.getDate() + parseInt(planData.expiryDay));
                        var expiryDay = firebase.firestore.Timestamp.fromDate(currentDate);
                    }
                    await database.collection('users').doc(vendorOwnerId).update({
                        'subscription_plan': planData,
                        'subscriptionPlanId': planId,
                        'subscriptionExpiryDate': expiryDay
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

                    await database.collection('vendors').doc(vendorId).update({
                        'subscription_plan': planData,
                        'subscriptionPlanId': planId,
                        'subscriptionExpiryDate': expiryDay,
                        'subscriptionTotalOrders': planData.orderLimit
                    });
                    let paymentType = "Manual Pay";
                    if (planData.type === 'free') {
                        paymentType = "Free";
                    }
                    await database.collection('subscription_history').doc(id_order).set({
                        'id': id_order,
                        'user_id': vendorOwnerId,
                        'expiry_date': expiryDay,
                        'createdAt': createdAt,
                        'subscription_plan': planData,
                        'payment_type': paymentType,
                    }).then(async function(snapshot) {
                        window.location.reload();
                    })

                    })

                }
            }
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

            database.collection('vendors').where('id', '==', id).get().then(async function(snapshot) {
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
                $(".item_limit_err").html(
                "<p>{{ trans('lang.enter_item_limit_can_not_empty_or_zero') }}</p>");
                return false;
            } else if (set_order_limit == 'limited' && ($('#order_limit').val() == '' || $('#order_limit')
                .val() == 0)) {
                $(".order_limit_err").html(
                    "<p>{{ trans('lang.enter_order_limit_can_not_empty_or_zero') }}</p>");
                return false;
            } else {

                await database.collection('users').doc(vendorOwnerId).update({
                    'subscription_plan.orderLimit': order_limit,
                    'subscription_plan.itemLimit': item_limit,
                }).then(async function(result) {
                    await database.collection('vendors').doc("{{ $id }}").update({
                        'subscription_plan.orderLimit': order_limit,
                        'subscription_plan.itemLimit': item_limit,
                        'subscriptionTotalOrders': order_limit
                    }).then(async function(result) {
                        window.location.reload();
                    })
                });

            }
        })
    </script>
@endsection
