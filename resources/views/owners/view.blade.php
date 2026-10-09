@extends('layouts.app')
@section('content')
<div class="page-wrapper">

    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <div class="d-flex top-title-section justify-content-between">
                <div class="d-flex top-title-left align-self-center">
                    <span class="icon mr-3"><img src="{{ asset('images/users.png') }}"></span>
                    <h3 class="mb-0 page-title">{{trans('lang.owner_details')}}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('owners') !!}">{{trans('lang.owners')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.owner_details')}}</li>
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
                                <div id="add_wallet_btn_wrap">
                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#addWalletModal" class="btn-primary btn rounded-full add-wallate"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.add_wallet_amount')}}</a>
                                </div>
                                <div id="create_carrier_top_btn_wrap" style="display:none;">
                                    <a href="{{route('carriers.create')}}?fromCompany={{$id}}&back=owner_view" class="btn-primary btn rounded-full"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.carrier_create_for_owner')}}</a>
                                </div>
                                <div id="edit_carrier_top_btn_wrap" style="display:none;">
                                    <a href="javascript:void(0)" id="top_edit_carrier_link" class="btn-primary btn rounded-full"><i class="mdi mdi-lead-pencil mr-2"></i>{{trans('lang.carrier_edit_for_owner')}}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
        <div class="resttab-sec mb-4">  
            <div class="menu-tab">
                <ul>
                    <li class="active basic_tab_li">
                        <a href="{{route('owners.view',$id)}}" id="owner_basic_tab_btn" class="basic"><i class="ri-list-indefinite"></i>{{trans('lang.tab_basic')}}</a>
                    </li>
                    <li>
                        <a href="{{route('owner.driver.list',$id)}}"><i class="ri-group-3-fill"></i>{{trans('lang.driver_plural')}}</a>
                    </li>
                    <li>
                        <a href="{{route('orders.owner',$id)}}"><i class="ri-group-3-fill"></i>{{trans('lang.order_plural')}}</a>
                    </li>
                    <li>
                        <a href="{{route('owners.payouts',$id)}}"><i class="ri-bank-card-line"></i>{{trans('lang.tab_payouts')}}</a>
                    </li>
                    <li>
                        <a href="{{route('payoutRequests.owners.view',$id)}}" class="vendor_payout"><i class="ri-refund-line"></i>{{trans('lang.tab_payout_request')}}</a>
                    </li>
                    <li>
                        <a href="{{route('owners.walletTransaction',$id)}}"
                            class="wallet_transaction"><i class="ri-wallet-line"></i>{{trans('lang.wallet_transaction')}}</a>
                    </li>
                    <li class="carrier_tab_li">
                        <a href="{{route('owners.carrier',$id)}}" id="owner_carrier_tab_btn" class="carrier_tab"><i class="ri-truck-line"></i>{{trans('lang.carrier_plural')}}</a>
                    </li>
                </ul>
            </div>  
            <div class="row" id="owner_stats_row">
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--1">
                        <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="card-box-with-content">
                            <h4 class="text-dark-2 mb-1 h4 total_orders" id="total_orders">00</h4>
                            <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_orders')}}</p>
                        </div>
                            <span class="box-icon ab"><img src="{{ asset('images/total_order.png') }}"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--10">
                        <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="card-box-with-content">
                            <h4 class="text-dark-2 mb-1 h4 total_drivers" id="total_drivers">00</h4>
                            <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_drivers')}}</p>
                        </div>
                            <span class="box-icon ab"><img src="{{ asset('images/total_drivers.png') }}"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--3">
                        <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="card-box-with-content">
                            <h4 class="text-dark-2 mb-1 h4 wallet_balance" id="wallet_balance">$0.00</h4>
                            <p class="mb-0 small text-dark-2">{{trans('lang.wallet_Balance')}}</p>
                        </div>
                            <span class="box-icon ab"><img src="{{ asset('images/total_payment.png') }}"></span>
                        </div>
                    </div>
                </div>
            </div>   
        </div>
        <div id="owner_basic_details_sections">
        <div class="restaurant_info-section">
            <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                <div class="card-header-title">
                    <h3 class="text-dark-2 mb-0 h4">{{trans('lang.owner_details')}}</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="restaurant_info_left">
                            <div class="d-flex mb-1">
                                <div class="sis-img profile_image" id="profile_image">
                                </div>
                                <div class="sis-content pl-4">
                                    <ul class="p-0 info-list mb-0">
                                        <li class="d-flex align-items-center mb-2">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.name')}}</label>
                                            <span class="driver_name" id="driver_name"></span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.email')}}</label>
                                            <span class="email"></span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.user_phone')}}</label>
                                            <span class="phone"></span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2 mr-1">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.wallet_Balance')}}</label>
                                            <span class="wallet_balance"> </span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2 mr-1">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.service_type')}}</label>
                                            <span class="service_type"> </span>
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
        {{-- Report 03 point 39: a delivery company registers with company
             details and documents, and NOTHING IN THIS PANEL EVER SHOWED THEM.
             Hidden entirely unless the record is a company, so an ordinary
             owner's page is unchanged. --}}
        <div class="restaurant_info-section" id="company_details_section" style="display:none;">
            <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                <div class="card-header-title">
                    <h3 class="text-dark-2 mb-0 h4">{{trans('lang.company_details')}}</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="restaurant_info_left">
                            <ul class="p-0 info-list mb-0">
                                <li class="d-flex align-items-center mb-2">
                                    <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.company_name')}}</label>
                                    <span id="company_name"></span>
                                </li>
                                <li class="d-flex align-items-center mb-2">
                                    <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.company_address')}}</label>
                                    <span id="company_address"></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="restaurant_info_left">
                            <ul class="p-0 info-list mb-0">
                                <li class="d-flex align-items-center mb-2">
                                    <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_registration_number')}}</label>
                                    <span id="company_commercial_register"></span>
                                </li>
                                <li class="d-flex align-items-center mb-2">
                                    <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_operating_licence')}}</label>
                                    <span id="company_operating_licence"></span>
                                </li>
                                <li class="d-flex align-items-center mb-2">
                                    <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_unique_id')}}</label>
                                    <span id="company_unique_id"></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <label class="mb-2 font-wi font-semibold text-dark-2 d-block">{{trans('lang.company_documents')}}</label>
                        <div id="company_documents"></div>
                    </div>
                </div>
            </div>
            </div>
        </div>

        <!-- Bank detail -->
        <div class="restaurant_info-section">
            <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                <div class="card-header-title">
                    <h3 class="text-dark-2 mb-0 h4">{{trans('lang.bankdetails')}}</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div id="noBankData" class="text-muted font-weight-bold py-3" style="display:none;">
                            {{trans("lang.bank_details_not_found")}}
                        </div>
                        <div class="restaurant_info_left" id="bankDetailsBox" style="display:none;">
                            <div class="d-flex mb-1">                              
                                <div class="sis-content pl-4">
                                    <ul class="p-0 info-list mb-0">
                                        <li class="d-flex align-items-center mb-2">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.bank_name')}}</label>
                                            <span class="bank_name" id="bank_name"></span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.branch_name')}}</label>
                                            <span class="branch_name" id="branch_name"></span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.holer_name')}}</label>
                                            <span class="holer_name" id="holer_name"></span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2 mr-1">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.account_number')}}</label>
                                            <span class="account_number" id="account_number"> </span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2 mr-1">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.other_information')}}</label>
                                            <span class="other_information" id="other_information"> </span>
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
        <div class="form-group col-12 text-center btm-btn">
            <a href="{!! route('owners') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        </div>
        </div>{{-- close owner_basic_details_sections --}}

        <div id="owner_carrier_tab_section" style="display:none;">
            {{-- Loading spinner --}}
            <div id="owner_carrier_loading" class="text-center py-5">
                <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="text-muted mt-2">{{trans('lang.loading') ?? 'Loading...'}}</p>
            </div>

            {{-- Empty State: when no carrier is created for this owner --}}
            <div id="owner_carrier_empty_state" class="card border text-center py-5" style="display:none;">
                <div class="card-body">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background-color: #f1f5f9;">
                            <i class="mdi mdi-truck" style="font-size: 40px; color: #64748b;"></i>
                        </span>
                    </div>
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.carrier_not_found')}}</h3>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 500px;">
                        {{trans('lang.carrier_not_created_yet_desc')}}
                    </p>
                    <div>
                        <a href="{{route('carriers.create')}}?fromCompany={{$id}}&back=owner_view" class="btn-primary btn rounded-full px-4 py-2">
                            <i class="mdi mdi-plus mr-2"></i>{{trans('lang.carrier_create_for_owner')}}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Carrier Details: when carrier exists --}}
            <div id="owner_carrier_details_card" style="display:none;">
                <!-- General Carrier Info -->
                <div class="restaurant_info-section">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-0 h4"><i class="mdi mdi-truck mr-2"></i>{{trans('lang.carrier_info')}}</h3>
                            </div>
                            <div class="card-header-right">
                                <span id="carrier_view_status_badge" class="badge mr-2"></span>
                                <a href="javascript:void(0)" id="carrier_inline_edit_btn" class="btn btn-sm btn-primary">
                                    <i class="mdi mdi-lead-pencil mr-1"></i>{{trans('lang.carrier_edit_for_owner')}}
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="restaurant_info_left">
                                        <div class="d-flex mb-1">
                                            <div class="sis-img" id="carrier_view_logo"></div>
                                            <div class="sis-content pl-4">
                                                <ul class="p-0 info-list mb-0">
                                                    <li class="d-flex align-items-center mb-2">
                                                        <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_name')}}</label>
                                                        <span id="carrier_view_name"></span>
                                                    </li>
                                                    <li class="d-flex align-items-center mb-2">
                                                        <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_code')}}</label>
                                                        <span id="carrier_view_code"></span>
                                                    </li>
                                                    <li class="d-flex align-items-center mb-2">
                                                        <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_phone')}}</label>
                                                        <span id="carrier_view_phone"></span>
                                                    </li>
                                                    <li class="d-flex align-items-center mb-2">
                                                        <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_email')}}</label>
                                                        <span id="carrier_view_email"></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="restaurant_info_left">
                                        <ul class="p-0 info-list mb-0">
                                            <li class="d-flex align-items-center mb-2">
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_regions')}}</label>
                                                <span id="carrier_view_regions"></span>
                                            </li>
                                            <li class="d-flex align-items-center mb-2">
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_delivery_time_conditions')}}</label>
                                                <span id="carrier_view_delivery_time"></span>
                                            </li>
                                            <li class="d-flex align-items-center mb-2">
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_max_weight') ?? 'Max Weight'}}</label>
                                                <span id="carrier_view_max_weight"></span>
                                            </li>
                                            <li class="d-flex align-items-center mb-2">
                                                <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_documents_verified')}}</label>
                                                <span id="carrier_view_verified_badge"></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carrier Identification & Documents -->
                <div class="restaurant_info-section">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-0 h4"><i class="mdi mdi-file-document mr-2"></i>{{trans('lang.carrier_identification')}}</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="p-0 info-list mb-0">
                                        <li class="d-flex align-items-center mb-2">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_commercial_register')}}</label>
                                            <span id="carrier_view_cr"></span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_operating_licence')}}</label>
                                            <span id="carrier_view_licence"></span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.carrier_unique_id')}}</label>
                                            <span id="carrier_view_uid"></span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <label class="mb-2 font-wi font-semibold text-dark-2 d-block">{{trans('lang.company_documents')}}</label>
                                    <div id="carrier_view_documents"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carrier Delivery Pricing / Charges -->
                <div class="restaurant_info-section">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-0 h4"><i class="mdi mdi-cash mr-2"></i>{{trans('lang.carrier_pricing')}}</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="carrier_pricing_table">
                                    <thead>
                                        <tr>
                                            <th>{{trans('lang.region') ?? 'Region'}}</th>
                                            <th>{{trans('lang.carrier_base_charge')}}</th>
                                            <th>{{trans('lang.carrier_per_km_charge')}}</th>
                                            <th>{{trans('lang.carrier_per_kg_charge')}}</th>
                                            <th>{{trans('lang.carrier_minimum_charge')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="carrier_pricing_tbody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Optional Conditions -->
                <div class="restaurant_info-section" id="carrier_conditions_section" style="display:none;">
                    <div class="card border">
                        <div class="card-header border-bottom pb-3">
                            <h3 class="text-dark-2 mb-0 h4"><i class="mdi mdi-alert-circle-outline mr-2"></i>{{trans('lang.carrier_conditions') ?? 'Conditions'}}</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-dark-2" id="carrier_view_conditions"></p>
                        </div>
                    </div>
                </div>

                <div class="form-group col-12 text-center btm-btn my-4">
                    <a href="javascript:void(0)" id="carrier_bottom_edit_btn" class="btn btn-primary mr-2">
                        <i class="mdi mdi-lead-pencil mr-1"></i>{{trans('lang.carrier_edit_for_owner')}}
                    </a>
                    <a href="{!! route('owners') !!}" class="btn btn-default">
                        <i class="fa fa-undo"></i>{{trans('lang.cancel')}}
                    </a>
                </div>
            </div>
        </div>
    </div>    
</div>
<div class="modal fade" id="addWalletModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered location_modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title locationModalTitle">{{trans('lang.add_wallet_amount')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="">
                    <div class="form-row">
                        <div class="form-group row">
                            <div class="form-group row width-100">
                                <label class="col-12 control-label">{{trans('lang.amount')}}</label>
                                <div class="col-12">
                                    <input type="number" name="amount" class="form-control" id="amount">
                                    <div id="wallet_error" style="color:red"></div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-12 control-label">{{trans('lang.note')}}</label>
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
                    <button type="button" class="btn btn-primary save-form-btn" id="add-wallet-btn">{{trans('submit')}}</a>
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal"
                            aria-label="Close">{{trans('close')}}</a>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')

    <script type="text/javascript">

        var id = "{{$id}}";        
        var database = firebase.firestore();
        var ref = database.collection('users').where("id", "==", id);
        
        var photo = "";
        var vendorOwnerId = "";
        var vendorOwnerOnline = false;
        var type = '';
        var placeholderImage = '';
        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function (snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        });
        
        var currency = database.collection('settings');
        var currentCurrency = '';
        var currencyAtRight = false;
        var decimal_degits = 0;
        var refCurrency = regionCurrencyRef();
        refCurrency.get().then(async function (snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
            $(".currentCurrency").text(currencyData.symbol);
        });
        
        var email_templates = database.collection('email_templates').where('type', '==', 'wallet_topup');
        var emailTemplatesData = null;

        const serviceLabels = {
            "cab-service": "Cab Service",
            "parcel_delivery": "Parcel Delivery Service",
            "rental-service": "Rental Service",
            "delivery-service": "Multivendor Delivery Service",
            "ecommerce-service": "Ecommerce Service",
            "ondemand-service": "On Demand Service"
        };

        $(document).ready(async function () {

            jQuery("#data-table_processing").show();

            await email_templates.get().then(async function (snapshots) {
                emailTemplatesData = snapshots.docs[0].data();
            });

            ref.get().then(async function (snapshots) {

                if(snapshots.docs.length>0){
                    
                    var dirver = snapshots.docs[0].data();
                        type = dirver.serviceType;
                    
                    $('.page-title').html("{{trans('lang.owner_details')}} - " + dirver.firstName + ' ' + dirver.lastName);
                    $(".driver_name").text(dirver.firstName + ' ' + dirver.lastName);

                    /* Report 03 point 39 - see the card in the markup. */
                    renderCompanyDetails(dirver);
                    loadPublishedRegionsMap();
                    fetchOwnerCarrier(dirver);
                    $(".email").text(shortEmail(dirver.email));

                    if(dirver.phoneNumber.includes('+')){
                        $(".phone").text('+' + EditPhoneNumber(dirver.phoneNumber.slice(1)));
                    }else{
                        $(".phone").text(EditPhoneNumber(dirver.phoneNumber));
                    }
                    
                    var wallet_route = "{{route('owners.walletTransaction','id')}}";
                    $(".wallet_transaction").attr("href",  spideliRouteWithId(wallet_route, dirver.id) );

                    let serviceTypes = dirver.serviceTypes || (dirver.serviceType ? [dirver.serviceType] : []);
                    let serviceTypeText = serviceTypes.map(type => serviceLabels[type] || type).join(", ");
                    $(".service_type").text(serviceTypeText);
                    
                    if (serviceTypes) {

                        let ownedDriversSnapshot = await database.collection('users').where('role','==',"driver").where('ownerId', '==', dirver.id).get();
                        $('.total_drivers').html(ownedDriversSnapshot.docs.length);

                        let totalOrders = 0;
                        for (const driverSnap of ownedDriversSnapshot.docs){

                            var driverdata = driverSnap.data();
                            let driverServiceTypes = driverdata.serviceTypes || (driverdata.serviceType ? [driverdata.serviceType] : []);

                            if (driverServiceTypes.includes("cab-service")) {
                                const ordersSnapshot = await database.collection('rides').where('driverId', '==', driverdata.id).get();
                                totalOrders += ordersSnapshot.docs.length;
                            }
                            
                            if (driverServiceTypes.includes("rental-service")) {
                                const ordersSnapshot = await database.collection('rental_orders').where('driverId', '==', driverdata.id).get();
                                totalOrders += ordersSnapshot.docs.length;
                            } 
                            
                            if (driverServiceTypes.includes("delivery-service") || driverServiceTypes.includes("ecommerce-service")) {
                                const ordersSnapshot = await database.collection('vendor_orders').where('driverID', '==', driverdata.id).get();
                                totalOrders += ordersSnapshot.docs.length;
                            } 
                            
                            if (driverServiceTypes.includes("parcel_delivery")) {
                                const ordersSnapshot = await database.collection('parcel_orders').where('driverId', '==', driverdata.id).get();
                                totalOrders += ordersSnapshot.docs.length;
                            }
                        }

                        $('.total_orders').html(totalOrders);
                    }

                    var wallet_balance = 0;
                    if (dirver.hasOwnProperty('wallet_amount') && dirver.wallet_amount != null && !isNaN(dirver.wallet_amount)) {
                        wallet_balance = dirver.wallet_amount;
                    }
                    if (currencyAtRight) {
                        wallet_balance = parseFloat(wallet_balance).toFixed(decimal_degits) + "" + currentCurrency;
                    } else {
                        wallet_balance = currentCurrency + "" + parseFloat(wallet_balance).toFixed(decimal_degits);
                    }
                    $('.wallet_balance').html(wallet_balance);
                    var image = "";
                    if (dirver.profilePictureURL) {
                        if(dirver.profilePictureURL){
                            photo=dirver.profilePictureURL;
                        }else{
                            photo=placeholderImage;
                        }
                        image = '<img width="200px" id="" height="auto" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                    } else {
                        image = '<img width="200px" id="" height="auto" src="' + placeholderImage + '">';
                    }
                    $(".profile_image").html(image);
                    var vehicle_profile_image = "";
                    if (dirver.carPictureURL)
                            vehicle_profile_image = '<img width="200px" id="" height="auto" src="' + dirver.carPictureURL + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                    else
                        vehicle_profile_image = '<img width="200px" id="" height="auto" src="' + placeholderImage + '">';
                    $(".vehicle_profile_image").html(vehicle_profile_image);
                    var driver_proof_image = "";
                    if (dirver.driverProofPictureURL)
                        driver_proof_image = '<img width="200px" id="" height="auto" src="' + dirver.driverProofPictureURL + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                    else
                        driver_proof_image = '<img width="200px" id="" height="auto" src="' + placeholderImage + '">';
                    $(".driver_proof_image").html(driver_proof_image);
                
                    if (dirver.hasOwnProperty('userBankDetails')) {
                        if (dirver.userBankDetails.hasOwnProperty('bankName')) {
                            $(".bank_name").text(dirver.userBankDetails.bankName);
                        }
                        if (dirver.userBankDetails.hasOwnProperty('branchName')) {
                            $(".branch_name").text(dirver.userBankDetails.branchName);
                        }
                        if (dirver.userBankDetails.hasOwnProperty('holderName')) {
                            $(".holer_name").text(dirver.userBankDetails.holderName);
                        }
                        if (dirver.userBankDetails.hasOwnProperty('accountNumber')) {
                            $(".account_number").text(dirver.userBankDetails.accountNumber);
                        }
                        if (dirver.userBankDetails.hasOwnProperty('otherDetails')) {
                            $(".other_information").text(dirver.userBankDetails.otherDetails);
                        }
                        if (
                            !dirver.userBankDetails.bankName &&
                            !dirver.userBankDetails.branchName &&
                            !dirver.userBankDetails.holder_naholderNameme &&
                            !dirver.userBankDetails.accountNumber &&
                            !dirver.userBankDetails.otherDetails
                        ) {
                            $("#bankDetailsBox").hide();
                            $("#noBankData").show();
                        } else {
                            $("#bankDetailsBox").show();
                            $("#noBankData").hide();
                        }
                    } else {
                        $("#bankDetailsBox").hide();
                        $("#noBankData").show();
                    }
                    
                } else{
                    $('.driver_detail_div').html('<h5 class="font-weight-bold align text-danger text-center">{{trans('lang.driver_unknown_deleted')}}</h5>')
                }
                jQuery("#data-table_processing").hide();
            })
        });
        // });
        /* ---- Report 03 point 39 ---------------------------------------------
         *
         * *"When we create a delivery company, we have to specify Company
         * information and Company documents. But … we do not see this informations
         * in the admin web panel."*
         *
         * Correct. `owners/view`, `owners/edit` and `drivers/view` between them
         * carried NOT ONE reference to companyName, commercialRegister,
         * operatingLicence or uniqueIdNumber. A company registered from the phone,
         * the details were stored, and there was nowhere to look at them.
         *
         * All six companies carry the three reference numbers. Only two carry the
         * document files, and NONE carries companyAddress - so every field has to
         * cope with being absent rather than assume it is there.
         * ------------------------------------------------------------------- */
        function renderCompanyDetails(owner) {
            if (!owner) {
                return;
            }

            var isCompany = owner.driverType === 'company' || owner.isCompany === true;

            if (!isCompany) {
                return;
            }

            $('#company_details_section').show();

            function orDash(value) {
                var text = (value === null || value === undefined) ? '' : String(value).trim();
                return text === '' ? '-' : text;
            }

            $('#company_name').text(orDash(owner.companyName));
            $('#company_address').text(orDash(owner.companyAddress));
            $('#company_commercial_register').text(orDash(owner.commercialRegister));
            $('#company_operating_licence').text(orDash(owner.operatingLicence));
            $('#company_unique_id').text(orDash(owner.uniqueIdNumber));

            /* Four of the six companies uploaded no files at all, so saying so
             * plainly beats three dead links. */
            var files = [
                { url: owner.commercialRegisterFile, label: "{{trans('lang.carrier_registration_number')}}" },
                { url: owner.operatingLicenceFile,   label: "{{trans('lang.carrier_operating_licence')}}" },
                { url: owner.uniqueIdNumberFile,     label: "{{trans('lang.carrier_unique_id')}}" }
            ].filter(function (f) {
                return typeof f.url === 'string' && f.url.trim() !== '';
            });

            if (files.length === 0) {
                $('#company_documents').html('<span class="text-muted">' +
                    "{{trans('lang.company_documents_none')}}" + '</span>');
                return;
            }

            var html = files.map(function (f) {
                /* NOT encodeURI'd - a Firebase Storage url is already encoded and
                 * encoding it again breaks the token. */
                return '<a href="' + escapeHtmlAttribute(f.url) + '" target="_blank" rel="noopener" ' +
                       'class="badge badge-info mr-2 mb-2"><i class="mdi mdi-file-document mr-1"></i>' +
                       escapeHtmlText(f.label) + '</a>';
            }).join('');

            $('#company_documents').html(html);
        }

        function escapeHtmlText(value) {
            return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
        }

        function escapeHtmlAttribute(value) {
            return escapeHtmlText(value).replace(/"/g, '&quot;');
        }

            $("#add-wallet-btn").click(function () {
            var date = firebase.firestore.FieldValue.serverTimestamp();
            var amount = $('#amount').val();
            if (amount == '' || amount <= 0) {
                $('#wallet_error').text('{{trans("lang.add_wallet_amount_error")}}');
                return false;
            }
            var note = $('#note').val();
            database.collection('users').where('id', '==', id).get().then(async function (snapshot) {
                if (snapshot.docs.length > 0) {
                    var data = snapshot.docs[0].data();
                    var walletAmount = 0;
                    if (data.hasOwnProperty('wallet_amount') && !isNaN(data.wallet_amount) && data.wallet_amount != null) {
                        walletAmount = data.wallet_amount;
                    }
                    var user_id = data.id;
                    var newWalletAmount = parseFloat(walletAmount) + parseFloat(amount);
                    database.collection('users').doc(id).update({
                        'wallet_amount': newWalletAmount
                    }).then(function (result) {
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
                            'transactionUser': "driver",
                        }).then(async function (result) {
                            if (currencyAtRight) {
                                amount = parseInt(amount).toFixed(decimal_degits) + "" + currentCurrency;
                                newWalletAmount = newWalletAmount.toFixed(decimal_degits) + "" + currentCurrency;
                            } else {
                                amount = currentCurrency + "" + parseInt(amount).toFixed(decimal_degits);
                                newWalletAmount = currentCurrency + "" + newWalletAmount.toFixed(decimal_degits);
                            }
                            var formattedDate = new Date();
                            var month = formattedDate.getMonth() + 1;
                            var day = formattedDate.getDate();
                            var year = formattedDate.getFullYear();
                            month = month < 10 ? '0' + month : month;
                            day = day < 10 ? '0' + day : day;
                            formattedDate = day + '-' + month + '-' + year;
                            var message = emailTemplatesData.message;
                            message = message.replace(/{username}/g, data.firstName + ' ' + data.lastName);
                            message = message.replace(/{date}/g, formattedDate);
                            message = message.replace(/{amount}/g, amount);
                            message = message.replace(/{paymentmethod}/g, 'Wallet');
                            message = message.replace(/{transactionid}/g, tempId);
                            message = message.replace(/{newwalletbalance}/g, newWalletAmount);
                            emailTemplatesData.message = message;
                            var url = "{{url('send-email')}}";
                            if(data.email != '' && data.email != null){
                            var sendEmailStatus = await sendEmail(url, emailTemplatesData.subject, emailTemplatesData.message, [data.email]);
                            if (sendEmailStatus) {
                                window.location.reload();
                            }
                        }else{
                            window.location.reload();
                        }
                        })
                    })
                } else {
                    $('#user_account_not_found_error').text('{{trans("lang.user_detail_not_found")}}');
                }
            });
        });

        var ownerCarrierData = null;
        var ownerCarrierId = null;
        var publishedRegionsMap = {};

        async function loadPublishedRegionsMap() {
            try {
                var snap = await database.collection('regions').get();
                snap.forEach(function (doc) {
                    var r = doc.data();
                    publishedRegionsMap[doc.id] = r.name || r.title || doc.id;
                });
            } catch (e) {
                console.error('Error fetching regions map', e);
            }
        }

        async function fetchOwnerCarrier(owner) {
            $('#owner_carrier_loading').show();
            $('#owner_carrier_empty_state').hide();
            $('#owner_carrier_details_card').hide();

            try {
                if (owner && owner.carrierId) {
                    var cDoc = await database.collection('delivery_carriers').doc(owner.carrierId).get();
                    if (cDoc.exists) {
                        ownerCarrierId = cDoc.id;
                        ownerCarrierData = cDoc.data();
                    }
                }

                if (!ownerCarrierData) {
                    var cQuery = await database.collection('delivery_carriers').where('ownerId', '==', id).limit(1).get();
                    if (!cQuery.empty) {
                        ownerCarrierId = cQuery.docs[0].id;
                        ownerCarrierData = cQuery.docs[0].data();
                        if (owner && !owner.carrierId) {
                            database.collection('users').doc(id).update({ carrierId: ownerCarrierId }).catch(console.error);
                        }
                    }
                }
            } catch (err) {
                console.error('Error fetching carrier for owner', err);
            }

            $('#owner_carrier_loading').hide();
            renderCarrierTabContent();
        }

        function renderCarrierTabContent() {
            if (!ownerCarrierData) {
                $('#owner_carrier_empty_state').show();
                $('#owner_carrier_details_card').hide();
                if ($('.carrier_tab_li').hasClass('active')) {
                    $('#create_carrier_top_btn_wrap').show();
                    $('#edit_carrier_top_btn_wrap').hide();
                    $('#add_wallet_btn_wrap').hide();
                }
                return;
            }

            var editUrl = '{{ route("carriers.edit", ":cid") }}'.replace(':cid', ownerCarrierId) + '?fromCompany=' + encodeURIComponent(id) + '&back=owner_view';
            $('#carrier_inline_edit_btn').attr('href', editUrl);
            $('#carrier_bottom_edit_btn').attr('href', editUrl);
            $('#top_edit_carrier_link').attr('href', editUrl);

            if ($('.carrier_tab_li').hasClass('active')) {
                $('#create_carrier_top_btn_wrap').hide();
                $('#edit_carrier_top_btn_wrap').show();
                $('#add_wallet_btn_wrap').hide();
            }

            // Logo
            var logoImg = (ownerCarrierData.photo && ownerCarrierData.photo.trim() !== '') ? ownerCarrierData.photo : placeholderImage;
            $('#carrier_view_logo').html('<img width="120px" height="auto" class="rounded" src="' + escapeHtmlAttribute(logoImg) + '" onerror="this.onerror=null;this.src=\'' + escapeHtmlAttribute(placeholderImage) + '\'">');

            // General Info
            $('#carrier_view_name').text(ownerCarrierData.name || '-');
            $('#carrier_view_code').text(ownerCarrierData.code || '-');
            $('#carrier_view_phone').text(ownerCarrierData.phone || '-');
            $('#carrier_view_email').text(ownerCarrierData.email || '-');

            // Status Badge
            var isActive = ownerCarrierData.publish === true || ownerCarrierData.is_active === true;
            if (isActive) {
                $('#carrier_view_status_badge').attr('class', 'badge badge-success').text("{{ trans('lang.active') ?? 'Active' }}");
            } else {
                $('#carrier_view_status_badge').attr('class', 'badge badge-danger').text("{{ trans('lang.inactive') ?? 'Inactive' }}");
            }

            // Verified Badge
            if (ownerCarrierData.isVerified === true) {
                $('#carrier_view_verified_badge').html('<span class="badge badge-success"><i class="mdi mdi-check-circle mr-1"></i>' + "{{ trans('lang.carrier_documents_verified') }}" + '</span>');
            } else {
                $('#carrier_view_verified_badge').html('<span class="badge badge-warning">' + "{{ trans('lang.unverified') ?? 'Unverified' }}" + '</span>');
            }

            // Regions
            var regionIds = ownerCarrierData.regionIds || [];
            if (regionIds.length === 0) {
                $('#carrier_view_regions').html('<span class="badge badge-primary mr-1">' + "{{ trans('lang.carrier_pricing_all_regions') ?? 'All Regions' }}" + '</span>');
            } else {
                var rBadges = regionIds.map(function(rid) {
                    var rName = publishedRegionsMap[rid] || rid;
                    return '<span class="badge badge-info mr-1 mb-1">' + escapeHtmlText(rName) + '</span>';
                }).join('');
                $('#carrier_view_regions').html(rBadges);
            }

            // Delivery time
            var minT = ownerCarrierData.minDeliveryTime || '-';
            var maxT = ownerCarrierData.maxDeliveryTime || '-';
            var unit = ownerCarrierData.deliveryTimeUnit || '';
            $('#carrier_view_delivery_time').text(minT + ' - ' + maxT + ' ' + unit);

            // Max weight
            $('#carrier_view_max_weight').text(ownerCarrierData.maxWeight ? (ownerCarrierData.maxWeight + ' kg') : '-');

            // Identification
            $('#carrier_view_cr').text(ownerCarrierData.commercialRegister || '-');
            $('#carrier_view_licence').text(ownerCarrierData.operatingLicence || '-');
            $('#carrier_view_uid').text(ownerCarrierData.uniqueIdNumber || '-');

            // Documents
            var docFiles = [
                { url: ownerCarrierData.commercialRegisterFile, label: "{{ trans('lang.carrier_commercial_register') }}" },
                { url: ownerCarrierData.operatingLicenceFile,   label: "{{ trans('lang.carrier_operating_licence') }}" },
                { url: ownerCarrierData.uniqueIdNumberFile,     label: "{{ trans('lang.carrier_unique_id') }}" }
            ].filter(function(f) {
                return typeof f.url === 'string' && f.url.trim() !== '';
            });

            if (docFiles.length === 0) {
                $('#carrier_view_documents').html('<span class="text-muted">' + "{{ trans('lang.company_documents_none') }}" + '</span>');
            } else {
                var docHtml = docFiles.map(function(f) {
                    return '<a href="' + escapeHtmlAttribute(f.url) + '" target="_blank" rel="noopener" class="badge badge-info mr-2 mb-2 p-2">' +
                           '<i class="mdi mdi-file-document mr-1"></i>' + escapeHtmlText(f.label) + '</a>';
                }).join('');
                $('#carrier_view_documents').html(docHtml);
            }

            // Pricing Table
            var pricingTbody = $('#carrier_pricing_tbody');
            pricingTbody.empty();

            function formatPrice(val) {
                if (val === null || val === undefined || val === '' || isNaN(val)) {
                    return '-';
                }
                var num = parseFloat(val).toFixed(decimal_degits);
                return currencyAtRight ? (num + ' ' + currentCurrency) : (currentCurrency + ' ' + num);
            }

            var regPricing = ownerCarrierData.regionPricing || {};
            var hasRegionPricing = Object.keys(regPricing).length > 0;

            if (hasRegionPricing && regionIds.length > 0) {
                regionIds.forEach(function(rid) {
                    var p = regPricing[rid] || {};
                    var rName = publishedRegionsMap[rid] || rid;
                    var rowHtml = '<tr>' +
                        '<td><strong>' + escapeHtmlText(rName) + '</strong></td>' +
                        '<td>' + formatPrice(p.baseCharge) + '</td>' +
                        '<td>' + formatPrice(p.perKmCharge) + '</td>' +
                        '<td>' + formatPrice(p.perKgCharge) + '</td>' +
                        '<td>' + formatPrice(p.minimumCharge) + '</td>' +
                    '</tr>';
                    pricingTbody.append(rowHtml);
                });
            } else {
                var flatBase = ownerCarrierData.baseCharge;
                var flatKm = ownerCarrierData.perKmCharge;
                var flatKg = ownerCarrierData.perKgCharge;
                var flatMin = ownerCarrierData.minimumCharge;
                var rowHtml = '<tr>' +
                    '<td><strong>' + "{{ trans('lang.carrier_pricing_all_regions') ?? 'All Regions (Flat Rate)' }}" + '</strong></td>' +
                    '<td>' + formatPrice(flatBase) + '</td>' +
                    '<td>' + formatPrice(flatKm) + '</td>' +
                    '<td>' + formatPrice(flatKg) + '</td>' +
                    '<td>' + formatPrice(flatMin) + '</td>' +
                '</tr>';
                pricingTbody.append(rowHtml);
            }

            // Conditions
            if (ownerCarrierData.conditions && ownerCarrierData.conditions.trim() !== '') {
                $('#carrier_view_conditions').text(ownerCarrierData.conditions);
                $('#carrier_conditions_section').show();
            } else {
                $('#carrier_conditions_section').hide();
            }

            $('#owner_carrier_details_card').show();
        }

        function switchTab(target) {
            if (target === 'carrier') {
                $('.menu-tab ul li').removeClass('active');
                $('.carrier_tab_li').addClass('active');
                $('#owner_basic_details_sections').hide();
                $('#owner_stats_row').hide();
                $('#owner_carrier_tab_section').show();
                $('#add_wallet_btn_wrap').hide();
                if (ownerCarrierData) {
                    $('#create_carrier_top_btn_wrap').hide();
                    $('#edit_carrier_top_btn_wrap').show();
                } else {
                    $('#create_carrier_top_btn_wrap').show();
                    $('#edit_carrier_top_btn_wrap').hide();
                }
                window.location.hash = 'carrier';
            } else {
                $('.menu-tab ul li').removeClass('active');
                $('.basic_tab_li').addClass('active');
                $('#owner_carrier_tab_section').hide();
                $('#owner_stats_row').show();
                $('#owner_basic_details_sections').show();
                $('#create_carrier_top_btn_wrap').hide();
                $('#edit_carrier_top_btn_wrap').hide();
                $('#add_wallet_btn_wrap').show();
                if (window.location.hash === '#carrier') {
                    history.replaceState(null, null, window.location.pathname + window.location.search);
                }
            }
        }

        $(document).on('click', '#owner_carrier_tab_btn', function (e) {
            e.preventDefault();
            switchTab('carrier');
        });

        $(document).on('click', '#owner_basic_tab_btn', function (e) {
            e.preventDefault();
            switchTab('basic');
        });

        var requestedTab = "<?php echo addslashes($tab ?? ''); ?>";
        if (requestedTab === 'carrier' || window.location.hash === '#carrier' || window.location.search.indexOf('tab=carrier') !== -1) {
            switchTab('carrier');
        }
    </script>
@endsection