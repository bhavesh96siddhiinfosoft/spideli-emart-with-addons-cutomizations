@extends('layouts.app')
@section('content')
<style type="text/css">
    .cus_name {
        font-style: normal;
        font-weight: 600;
        font-size: 16px;
        line-height: 24px;
        letter-spacing: 0.03em;
        color: #333333;
    }
    .fstar{
        padding-right: 5px;
    }

</style>
<div class="page-wrapper ">
    <div class="row page-titles non-printable">

        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.booking_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item">{{trans('lang.booking_edit')}}</li>
            </ol>
        </div>
    </div>

    <div class="card-body">
        <div class="col-md-12">
            <div class="print-top mt-3">
                <div class="text-right print-btn">
                    <a href="{{route('ondemand.bookings.print',$id)}}"><i class="fa fa-print"
                                                                          style="font-size:20px;"></i></a>
                </div>
            </div>

            <hr>
        </div>
        <div class="order_detail " id="order_detail">
            <div class="order_detail-top">
                <div class="row">
                    <div class="order_edit-genrl col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>{{trans('lang.general_details')}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="order_detail-top-box">

                                    <div class="form-group row widt-100 gendetail-col">
                                        <label class="col-12 control-label"><strong>{{trans('lang.booking_date_time')}}
                                                : </strong><span id="createdAt"></span></label>
                                    </div>

                                    <div class="form-group row widt-100 gendetail-col payment_method">
                                        <label class="col-12 control-label"><strong>{{trans('lang.payment_methods')}}
                                                : </strong><span id="payment_method"></span></label>
                                    </div>
                                    <div class="form-group row widt-100 gendetail-col old_order_status">
                                        <label class="col-12 control-label"><strong>{{trans('lang.order_status')}}
                                                : </strong><span id="old_order_status"></span></label>
                                    </div>
                                    <div class="form-group row widt-100 gendetail-col" id="reason_div"
                                         style="display:none">
                                        <label class="col-12 control-label"><strong>{{trans('lang.reason')}}
                                                : </strong><span class="reason"></span></label>
                                    </div>
                                    <div class="form-group row widt-100 gendetail-col" id="new_schdeule_date_div"
                                         style="display:none">
                                        <label class="col-12 control-label"><strong>{{trans('lang.new_schedule_date')}}
                                                : </strong><span class="new_schedule_date"></span></label>
                                    </div>
                                    <div class="form-group row widt-100 gendetail-col" id="start_time_div" style="display:none">
                                        <label class="col-12 control-label"><strong>{{trans('lang.start_time')}}
                                                : </strong><span class="start_time"></span></label>
                                    </div>
                                    <div class="form-group row widt-100 gendetail-col " id="end_time_div" style="display:none">
                                        <label class="col-12 control-label"><strong>{{trans('lang.end_time')}}
                                                : </strong><span class="end_time"></span></label>
                                    </div>
                                    <div class="d-flex mb-2 d-none total_time_div">
                                        <div class="row widt-100 gendetail-col d-none" id="total_time">
                                            <label class="col-12 control-label"><strong class="font-weight-bold mr-5">{{trans('lang.total_time')}}
                                                    : </strong><span id="timer" class="text-danger font-weight-bold">00:00:00</span>
                                            </label>
                                        </div>
                                        <div class="ml-auto stop_time_div d-none">
                                            <button class="btn btn-sm btn-danger stop_timer_btn">
                                                {{trans('lang.stop_time')}}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group row width-100 " id="order_status_div">
                                        <label class="col-3 control-label">{{trans('lang.update_status')}}:</label>
                                        <div class="col-7">
                                            <select id="order_status" class="form-control">
                                                <option value="" disabled selected>{{ trans('lang.select_status')}}
                                                </option>
                                                <option value="Order Accepted" id="order_accepted">{{
                                                    trans('lang.order_accepted')}}
                                                </option>
                                                <option value="Order Rejected" id="order_rejected">{{
                                                    trans('lang.order_rejected')}}
                                                </option>
                                                <option value="Order Assigned" id="order_assigned">{{
                                                    trans('lang.order_assigned')}}
                                                </option>
                                                <option value="Order Ongoing" id="order_ongoing">{{
                                                    trans('lang.order_ongoing')}}
                                                </option>
                                                <option value="Order Completed" id="order_completed">{{
                                                    trans('lang.order_completed')}}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row width-100 mt-3">

                                        <div class="d-flex">
                                            <div id="extra_charge_div" style="display:none">
                                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                                        data-target="#extraChargeModal">
                                                    <i class="fa fa-money"></i> {{trans('lang.add_extra_charges')}}
                                                </button>
                                            </div>
                                            <button type="button"
                                                    class="btn btn-primary edit-form-btn show_popup ml-3">
                                                <i class="fa fa-save"></i> {{trans('lang.update')}}
                                            </button>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="order-items-list mt-4 order-data-row order-totals-items">
                            <div class="card">
                                <div class="card-body">

                                    <div class="table-responsive bk-summary-table">
                                        <table class="order-totals">

                                            <tbody id="order_products_total">

                                            </tbody>
                                        </table>
                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="order_edit-genrl col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>{{ trans('lang.billing_details')}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="address order_detail-top-box">
                                    <div class="form-group row widt-100 gendetail-col">
                                        <label class="col-12 control-label"><strong>{{trans('lang.name')}}:</strong>
                                            <span id="billing_name"></span></label>
                                    </div>
                                    <div class="form-group row widt-100 gendetail-col">
                                        <label class="col-12 control-label"><strong>{{trans('lang.address')}}
                                                :</strong>
                                            <span id="billing_line1"></span>
                                            <span id="billing_line2"></span>

                                    </div>

                                    <p><strong>{{trans('lang.email_address')}}:</strong>
                                        <span id="billing_email"></span>
                                    </p>
                                    <p><strong>{{trans('lang.phone')}}:</strong>
                                        <span id="billing_phone"></span>
                                    </p>
                                </div>
                            </div>
                        </div>


                        <div class="resturant-detail mt-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-header-title">{{trans('lang.service_details')}}</h4>
                                </div>

                                <div class="card-body">
                                    <a class="row redirecttopage service_details" id="resturant-view">
                                        <div class="col-3">
                                            <img src="" class="service-img rounded-circle" alt="vendor" width="70px"
                                                 height="70px">
                                        </div>
                                        <div class="col-9">
                                            <h4 class="service-title"></h4>
                                        </div>
                                    </a>
                                    <h5 class="contact-info">{{trans('lang.ondemand_service_category')}}:</h5>
                                    <p class="mt-2"><strong>{{trans('lang.name')}}:</strong><span
                                                id="service_category"></span></p>
                                    <h5 class="contact-info">{{trans('lang.contact_info')}}:</h5>
                                    <p><strong>{{trans('lang.address')}}:</strong>
                                        <span id="service_address"></span>
                                    </p>

                                </div>
                            </div>
                        </div>
                        <div class="resturant-detail mt-4" id="worker_name_div" style="display:none">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-header-title">{{trans('lang.worker_details')}}</h4>
                                </div>

                                <div class="card-body">
                                    <a class="row redirecttopage worker_details" id="resturant-view">
                                        <div class="col-3">
                                            <img src="" class="worker-img rounded-circle" alt="vendor" width="70px"
                                                 height="70px">
                                        </div>
                                        <div class="col-9">
                                            <h4 class="worker_name"></h4>
                                        </div>
                                    </a>

                                    <h5 class="contact-info">{{trans('lang.contact_info')}}:</h5>
                                    <p><strong>{{trans('lang.email')}}:</strong>
                                        <span id="worker_email"></span>
                                    </p>
                                    <p><strong>{{trans('lang.phone')}}:</strong>
                                        <span id="worker_phone"></span>
                                    </p>
                                    <p><strong>{{trans('lang.address')}}:</strong>
                                        <span id="worker_address"></span>
                                    </p>

                                </div>
                            </div>
                        </div>
                        <div class="resturant-detail mt-4" id="provider_name_div" style="display:none">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-header-title">{{trans('lang.provider_details')}}</h4>
                                </div>

                                <div class="card-body">
                                    <a class="row redirecttopage provider_details" id="resturant-view">
                                        <div class="col-3">
                                            <img src="" class="provider-img rounded-circle" alt="vendor" width="70px"
                                                 height="70px">
                                        </div>
                                        <div class="col-9">
                                            <h4 class="provider_name"></h4>
                                        </div>
                                    </a>

                                    <h5 class="contact-info">{{trans('lang.contact_info')}}:</h5>
                                    <p><strong>{{trans('lang.email')}}:</strong>
                                        <span id="provider_email"></span>
                                    </p>
                                    <p><strong>{{trans('lang.phone')}}:</strong>
                                        <span id="provider_phone"></span>
                                    </p>


                                </div>
                            </div>
                        </div>
                        <div class="order_detail-review mt-4">

                            <div class="rental-review ">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>{{trans("lang.customer_reviews")}}</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="review-inner">

                                            <div id="customers_rating_and_review">

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

    </div>




    <div class="form-group col-12 text-center btm-btn">
        <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i> {{trans('lang.save')}}
        </button>

        <a href="javascript:window.history.go(-1);" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}
        </a>
    </div>

</div>

<div class="modal fade" id="orderAcceptModal" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered location_modal">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title locationModalTitle">{{trans('lang.accept_order')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

            </div>

            <div class="modal-body">

                <form class="">

                    <div class="form-row">

                        <div class="form-group row">

                            <div class="form-group row width-100">
                                <div class="col-12">
                                    <input type="datetime-local" name="new_schedule_date" class="form-control"
                                           id="new_schedule_date">
                                    <div id="select_date_time_err" style="color:red"></div>
                                </div>
                            </div>
                        </div>

                    </div>

                </form>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="order-accept-btn">{{trans('lang.accept')}}
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                        {{trans('close')}}
                    </button>

                </div>


            </div>
        </div>

    </div>

</div>
<div class="modal fade" id="orderAssignModal" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered location_modal">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title locationModalTitle">{{trans('lang.assign_order')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

            </div>

            <div class="modal-body">

                <form class="">

                    <div class="form-row">

                        <div class="form-group row">

                            <div class="form-group form-check row width-100">
                                <input type="radio" name="assign_order_radio" class="form-check-input"
                                       id="assign_order_me" value="my_self">
                                <label for="assign_order_me"
                                       class="form-check-label">{{trans("lang.assign_to_me")}}</label>
                            </div>
                            <div class="form-group form-check row width-100">
                                <input type="radio" name="assign_order_radio" class="form-check-input"
                                       id="assign_order_worker" value="worker">
                                <label for="assign_order_worker" class="form-check-label">{{trans("lang.assign_to_worker")}}</label>
                                <div id="select_radio_err" style="color:red"></div>
                            </div>
                            <div class="form-group row width-100" id="worker_list_div" style="display:none">
                                <div class="col-12">
                                    <label>{{trans("lang.select_worker_err")}}</label>
                                    <select name="worker" class="form-control" id="worker_list">
                                        <option value="" disabled selected>{{trans("lang.select_worker")}}</option>
                                    </select>
                                    <div id="select_worker_err" style="color:red"></div>
                                </div>
                            </div>
                        </div>

                    </div>

                </form>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="order-assign-btn">{{trans('lang.assign')}}
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                        {{trans('close')}}
                    </button>

                </div>


            </div>
        </div>

    </div>

</div>
<div class="modal fade" id="extraChargeModal" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered location_modal">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title locationModalTitle">{{trans('lang.add_extra_charges')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

            </div>

            <div class="modal-body">

                <form class="">

                    <div class="form-row">

                        <div class="form-group row">

                            <div class="form-group row width-100">
                                <div class="col-12">
                                    <input type="number" name="extra_charge"
                                           placeholder="{{trans('lang.extra_charge_amount')}}" class="form-control"
                                           id="extra_charge_amount">
                                    <div id="add_extra_charge_err" style="color:red"></div>
                                </div>
                                <div class="col-12 mt-5">
                                    <textarea rows="3" name="extra_charge_desc"
                                              placeholder="{{trans('lang.add_description')}}" class="form-control"
                                              id="extra_charge_desc"></textarea>
                                </div>
                            </div>
                        </div>

                    </div>

                </form>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="extra-charge-btn">{{trans('lang.add')}}
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                        {{trans('close')}}
                    </button>

                </div>


            </div>
        </div>

    </div>

</div>

<div class="modal fade" id="addOtpModal" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered location_modal">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title locationModalTitle">{{trans('lang.enter_otp')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

            </div>

            <div class="modal-body">

                <form class="">

                    <div class="form-row">

                        <div class="form-group row">

                            <div class="form-group row width-100">
                                <div class="col-12">
                                    <input type="number" name="otp" class="form-control" id="otp">
                                    <div id="enter_otp" style="color:red"></div>
                                </div>
                            </div>
                        </div>

                    </div>

                </form>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="add-otp-btn">{{trans('lang.submit')}}
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal"
                            aria-label="Close">{{trans('close')}}
                    </button>

                </div>


            </div>
        </div>

    </div>

</div>

@endsection

@section('style')

@section('scripts')
<script>
    var id_rendom = "<?php echo uniqid(); ?>";
    var adminCommission = 0;
    var id = "<?php echo $id; ?>";
    var userFcmToken = '';
    var fcmTokenProvider = '';
    var workerFcmToken = '';
    var old_order_status = '';
    var payment_shared = false;
    var serviceName = '';
    var serviceId = '';
    var userId = '';
    var order_sectionId = '';
    var total_price_val = 0;
    var adminCommission_val = 0;
    var providerAuthor = '';
    var paymentMethod = '';
    var paymentStatus = '';
    var extraChargePaymentStatus = '';
    var bookingDate = '';
    var newScheduleBookingDate = '';
    var database = firebase.firestore();

    var ref = database.collection('provider_orders').where("id", "==", id);

    var append_procucts_list = '';
    var append_procucts_total = '';
    var total_price = 0;
    var currentCurrency = '';
    var currencyAtRight = false;
    var refCurrency = regionCurrencyRef();
    var orderPreviousStatus = '';
    var orderPaymentMethod = '';
    var orderCustomerId = '';
    var orderPayableAmount = 0;
    var extraCharges = '';
    var manname = '';
    var decimal_degits = 0;
    var service_type = '';
    var otpToVerify = '';
    var priceUnit = '';
    let timerInterval;
    let startTime;
    var storedEndTime = '';
    var storedStartTime = '';

    var currencyData = '';
    refCurrency.get().then(async function (snapshots) {
        currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;
        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });

    var ProviderRejectedSubject = '';
    var ProviderRejectedMessage = '';
    var ProviderAcceptedSubject = '';
    var ProviderAcceptedMessage = '';
    var ProviderCompletedSubject = '';
    var ProviderCompletedMessage = '';
    var ProviderOngoingSubject = "";
    var ProviderOngoingMsg = "";
    var ProviderAssignedSubject = "";
    var ProviderAssignedMsg = "";
    var extraChargesSubject = "";
    var extraChargesMsg = "";
    var bookingTimeStopSubject = "";
    var bookingTimeStopMsg = "";

    var subscriptionTotalOrders=-1;
    var subscriptionModel=false;
    var commissionBusinessModel = '';
    var commisionModel=false;
     var subscriptionBusinessModel = database.collection('settings').doc("vendor");
        subscriptionBusinessModel.get().then(async function(snapshots) {
            var subscriptionSetting = snapshots.data();
            if (subscriptionSetting.subscription_model == true) {
                subscriptionModel = true;
            }
        });
    database.collection('dynamic_notification').get().then(async function (snapshot) {
        if (snapshot.docs.length > 0) {
            snapshot.docs.map(async (listval) => {
                val = listval.data();

                if (val.type == "provider_rejected") {
                    ProviderRejectedSubject = val.subject;
                    ProviderRejectedMessage = val.message;
                } else if (val.type == "provider_accepted") {
                    ProviderAcceptedSubject = val.subject;
                    ProviderAcceptedMessage = val.message;
                } else if (val.type == "worker_assigned") {
                    ProviderAssignedSubject = val.subject;
                    ProviderAssignedMsg = val.message;
                } else if (val.type == "service_intransit") {
                    ProviderOngoingSubject = val.subject;
                    ProviderOngoingMsg = val.message;
                } else if (val.type == "service_completed") {
                    ProviderCompletedSubject = val.subject;
                    ProviderCompletedMessage = val.message;
                } else if (val.type == "service_charges") {
                    extraChargesSubject = val.subject;
                    extraChargesMsg = val.message;
                } else if (val.type == "stop_time") {
                    bookingTimeStopSubject = val.subject;
                    bookingTimeStopMsg = val.message;
                }
            });
        }
    });


    var geoFirestore = new GeoFirestore(database);
    var place_image = '';
    var ref_place = database.collection('settings').doc("placeHolderImage");
    ref_place.get().then(async function (snapshots) {
        var placeHolderImage = snapshots.data();
        place_image = placeHolderImage.image;
    });

    async function getProvidersWorkerList(providerId) {
        database.collection('providers_workers').where('providerId', '==', providerId).where('online', '==', true).get().then(async function (snapshots) {
            if (snapshots.docs.length > 0) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    $('#worker_list').append($("<option></option>")
                        .attr("value", data.id)
                        .attr("fcm", data.fcmToken)
                        .text(data.firstName + ' ' + data.lastName));
                });
            }

        })
    }

    async function getMinDate() {
        var currentDate = new Date();
        var year = currentDate.getFullYear();
        var month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
        var day = currentDate.getDate().toString().padStart(2, '0');
        var hours = currentDate.getHours().toString().padStart(2, '0');
        var minutes = currentDate.getMinutes().toString().padStart(2, '0');

        var minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        $('#new_schedule_date').attr('min', minDateTime);
        $('#new_schedule_date').val(minDateTime);
    }

    let taxBreakdownGrouped = {
        order: {},
        platform: {}
    };

    $(document).ready(function () {

        $('[data-dismiss="modal"]').click(function() {
            $('#orderAcceptModal').modal('hide');
        });

        $('[data-dismiss="modal"]').click(function() {
            $('#orderAssignModal').modal('hide');
        });

        $('[data-dismiss="modal"]').click(function() {
            $('#extraChargeModal').modal('hide');
        });

        $('[data-dismiss="modal"]').click(function() {
            $('#addOtpModal').modal('hide');
        });

        $('.time-picker').timepicker({
            timeFormat: "HH:mm",
            showMeridian: false,
            format24: true,
            dropdown: false
        });
        $('.time-picker').timepicker().on('changeTime.timepicker', function (e) {
            var hours = e.time.hours,
                min = e.time.minutes;
            if (hours < 10) {
                $(e.currentTarget).val('0' + hours + ':' + min);
            }

        });

        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        getMinDate(); // set the minimum date

        $('#new_schedule_date').on('input', function () {

            var currentDateTime = new Date();
            var selectedDateTime = new Date($(this).val());
            selectedDateTime.setSeconds(60);
            if (currentDateTime.getDate() == selectedDateTime.getDate() && currentDateTime.getTime() > selectedDateTime.getTime()) {
                alert('{{trans("lang.can_not_select_time_less_than_current_time")}}');
                getMinDate();
            }
        });


        jQuery("#data-table_processing").show();

        ref.get().then(async function (snapshots) {
            var order = snapshots.docs[0].data();
             if (order.sectionId != undefined && order.sectionId != '' && order.sectionId!=null) {
                    await database.collection('sections').doc(order.sectionId).get().then(
                        async function(snapshot) {           
                            if (snapshot.data().adminCommision != null && snapshot.data()
                                .adminCommision != '') {
                                if (snapshot.data().adminCommision.enable) {
                                    commissionModel = true;
                                }
                            }
                        });
                }
            priceUnit = order.provider.priceUnit;
            getUserReview(order);
            append_procucts_total = document.getElementById('order_products_total');
            append_procucts_total.innerHTML = '';

            var id = order.authorID;
            var user_view = '{{route("users.view",":id")}}';
            user_view = user_view.replace(':id', id);

            var billing_name = order.author.firstName + ' ' + order.author.lastName;
            $('#billing_name').text(billing_name);
            $('#billing_name').html('<a href="' + user_view + '">' + billing_name + '</a>');

            var billingAddressstring = '';
            if (order.address.hasOwnProperty('address')) {
                $("#billing_line1").text(order.address.address);
            }
            if (order.address.hasOwnProperty('locality')) {
                billingAddressstring = billingAddressstring + order.address.locality;
            }
            if (order.address.hasOwnProperty('landmark') && order.address.landmark != null) {
                billingAddressstring = billingAddressstring + " " + order.address.landmark;
            }
            $("#billing_line2").text(billingAddressstring);
            if (order.author.hasOwnProperty('phoneNumber')) {
                if(order.author.phoneNumber.includes('+')){
                    $("#billing_phone").text('+' + EditPhoneNumber(order.author.phoneNumber.slice(1)));
                }else{
                    $("#billing_phone").text(EditPhoneNumber(order.author.phoneNumber));
                }
            }
            if (order.author.hasOwnProperty('email')) {
                $("#billing_email").html('<a href="mailto:' + order.author.email + '">' + shortEmail(order.author.email) + '</a>');
            }
            if (order.scheduleDateTime) {
                var date1 = order.scheduleDateTime.toDate().toDateString();
                var date = new Date(date1);
                var dd = String(date.getDate()).padStart(2, '0');
                var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
                var yyyy = date.getFullYear();
                var createdAt_val = yyyy + '-' + mm + '-' + dd;
                var time = order.scheduleDateTime.toDate().toLocaleTimeString('en-US');

                $('#createdAt').text(createdAt_val + ' ' + time);

                var now = new Date(order.scheduleDateTime.seconds * 1000);
                const year = now.getFullYear();
                let month = (now.getMonth() + 1).toString().padStart(2, '0');
                let day = now.getDate().toString().padStart(2, '0');
                let hours = now.getHours().toString().padStart(2, '0');
                let minutes = now.getMinutes().toString().padStart(2, '0');

                const formattedDatetime = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;

                $('#new_schedule_date').val(formattedDatetime);
                bookingDate = order.scheduleDateTime;
            }
            if (order.hasOwnProperty('newScheduleDateTime') && order.newScheduleDateTime != '' && order.newScheduleDateTime != null) {
                $('#new_schdeule_date_div').show();
                var date1 = order.newScheduleDateTime.toDate().toDateString();
                var date = new Date(date1);
                var dd = String(date.getDate()).padStart(2, '0');
                var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
                var yyyy = date.getFullYear();
                var createdAt_val = yyyy + '-' + mm + '-' + dd;
                var time = order.newScheduleDateTime.toDate().toLocaleTimeString('en-US');
                $('.new_schedule_date').text(createdAt_val + ' ' + time);
                newScheduleBookingDate = order.newScheduleDateTime;
            }
            if (order.reason != '' && order.reason != null) {
                $('#reason_div').show();
                $('.reason').html(order.reason);
            }
            var payment_method = '';
            if (order.payment_method) {
                paymentMethod = order.payment_method;
                if (order.payment_method == "stripe") {
                    image = '{{asset("images/payment/stripe.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" style="height: auto;">';

                } else if (order.payment_method == "xendit") {
                        image = '{{asset("images/payment/xendit.png")}}';
                        payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"  width="30%" height="30%">';

                    } else if (order.payment_method == "midtrans") {
                        image = '{{asset("images/payment/midtrans.png")}}';
                        payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                    } else if (order.payment_method == "orangepay") {
                        image = '{{asset("images/payment/orangepay.png")}}';
                        payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"  width="30%" height="30%">';

                    } else if (order.payment_method == "cod") {
                    image = '{{asset("images/payment/cashondelivery.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" style="height: auto;">';

                } else if (order.payment_method == "razorpay") {
                    image = '{{asset("images/payment/razorepay.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" style="height: auto;">';

                } else if (order.payment_method == "paypal") {
                    image = '{{asset("images/payment/paypal.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" style="height: auto;">';

                } else if (order.payment_method == "payfast") {
                    image = '{{asset("images/payment/payfast.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" style="height: auto;">';

                } else if (order.payment_method == "paystack") {
                    image = '{{asset("images/payment/paystack.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" style="height: auto;">';

                } else if (order.payment_method == "flutterwave") {
                    image = '{{asset("images/payment/flutter_wave.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" style="height: auto;">';

                } else if (order.payment_method == "mercadoPago" || order.payment_method == "mercado pago" || order.payment_method == "mercadopago") {
                    image = '{{asset("images/payment/marcado_pago.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" style="height: auto;">';

                } else if (order.payment_method == "wallet") {
                    image = '{{asset("images/payment/emart_wallet.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" style="height: auto;" >';

                } else if (order.payment_method == "paytm") {
                    image = '{{asset("images/payment/paytm.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" style="height: auto;">';

                } else if (order.payment_method == "cancelled order payment") {
                    image = '{{asset("images/payment/cancel_order.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" style="height: auto;">';

                } else if (order.payment_method == "refund amount") {
                    image = '{{asset("images/payment/refund_amount.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" style="height: auto;">';
                } else if (order.payment_method == "referral amount") {
                    image = '{{asset("images/payment/reffral_amount.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"  width="30%" style="height: auto;">';
                } else {
                    payment_method = order.payment_method;
                }
            }
            $('#payment_method').html('<span>' + payment_method + '</span>');

            if (order.provider && order.provider.author != '' && order.provider.author != undefined) {
                providerAuthor = order.provider.author;
                fcmTokenProvider = await getProviderFcm(order.provider.author);
                await getProvidersWorkerList(order.provider.author);
            }
            if (order.workerId != '' && order.workerId != null && order.workerId != undefined) {

                $('#worker_name_div').show();
                var workerDetail = await getWorkerDetail(order.workerId);
                if (workerDetail == '' || workerDetail == undefined) {
                    $('.worker_name').html("{{trans('lang.unknown')}}");
                    $('.worker_details').removeClass('redirecttopage');
                    $('.worker-img').attr('src', place_image);
                } else {

                    var rating = 0;

                    if (workerDetail.hasOwnProperty('reviewsCount') && workerDetail.reviewsCount && workerDetail.reviewsCount != "0.0" && workerDetail.reviewsCount != null && workerDetail.hasOwnProperty('reviewsSum') && workerDetail.reviewsSum && workerDetail.reviewsSum != "0.0" && workerDetail.reviewsSum != null) {

                        rating = (parseFloat(workerDetail.reviewsSum) / parseFloat(workerDetail.reviewsCount));

                        rating = (rating * 10) / 10;

                    }
                    var ratingHtml = '<span class="badge badge-success text-white ml-auto" ><i class="fstar fa fa-star" ></i>' + (rating).toFixed(1) + '</span>';


                    $('.worker_name').html('<span class="worker-title">' + workerDetail.firstName + workerDetail.lastName + '</span>' + ratingHtml);
                    $('#worker_email').html(shortEmail(workerDetail.email));
                    $('#worker_address').html(workerDetail.address);
                    if(workerDetail.phoneNumber.includes('+')){
                        $('#worker_phone').html('+' + EditPhoneNumber(workerDetail.phoneNumber.slice(1)));
                    }else{
                        $('#worker_phone').html(EditPhoneNumber(workerDetail.phoneNumber));
                    }
                    if (workerDetail.profilePictureURL != '') {

                        $('.worker-img').attr('src', workerDetail.profilePictureURL);
                    } else {
                        $('.worker-img').attr('src', place_image);

                    }
                    var workerDetailRoute = '{{route("ondemand.workers.edit", ":id")}}';
                    workerDetailRoute = workerDetailRoute.replace(':id', order.workerId);
                    $('.worker_details').attr('href', workerDetailRoute);
                }
            }
            orderCustomerId = order.author.id;
            userFcmToken = await getCusotmerFcm(order.author.id);
            serviceName = order.provider.title;
            customername = order.author.firstName;
            serviceId = order.provider.id;

            old_order_status = order.status;
            storedStartTime = '';
            storedEndTime = '';
            if (order.hasOwnProperty('startTime') && order.startTime != null && order.startTime != '') {
                storedStartTime = order.startTime.toDate();
                var date1 = order.startTime.toDate().toDateString();
                    var date = new Date(date1);
                    var dd = String(date.getDate()).padStart(2, '0');
                    var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
                    var yyyy = date.getFullYear();
                    var startDate = yyyy + '-' + mm + '-' + dd;
                    var time = order.startTime.toDate().toLocaleTimeString('en-US');
                    $('.start_time').text(startDate+' '+time);
                    $('#start_time_div').show();
            }
            if (order.hasOwnProperty('endTime') && order.endTime != null && order.endTime != '') {
                storedEndTime = order.endTime.toDate();
                 var date1 = order.endTime.toDate().toDateString();
                    var date = new Date(date1);
                    var dd = String(date.getDate()).padStart(2, '0');
                    var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
                    var yyyy = date.getFullYear();
                    var endDate = yyyy + '-' + mm + '-' + dd;
                    var time = order.endTime.toDate().toLocaleTimeString('en-US');
                    $('.end_time').text(endDate+' '+time);
                    $('#end_time_div').show();
            }

            if (storedStartTime != '' && priceUnit == 'Hourly' && order.status == 'Order Ongoing' && storedEndTime == '') {
                $('.total_time_div').removeClass('d-none');
                $('#total_time').removeClass('d-none');
                $('.stop_time_div').removeClass('d-none');
                startTime = parseInt(storedStartTime.getTime());
                timerInterval = setInterval(updateTimer, 1000);
            }

            if (order.status == 'Order Placed') {
                $('#old_order_status').html('<span class="order_placed py-2 px-3">' + order.status + '</span>');
            } else if (order.status == 'Order Assigned') {
                $('#old_order_status').html('<span class="order_assigned py-2 px-3">' + order.status + '</span>');
            } else if (order.status == 'Order Ongoing') {
                $('#old_order_status').html('<span class="order_ongoing py-2 px-3">' + order.status + '</span>');
            } else if (order.status == 'Order Accepted') {
                $('#old_order_status').html('<span class="order_accept py-2 px-3">' + order.status + '</span>');
            } else if (order.status == 'Order Rejected') {
                $('#old_order_status').html('<span class="order_rejected py-2 px-3">' + order.status + '</span>');
            } else if (order.status == 'Order Completed') {
                $('#old_order_status').html('<span class="order_completed py-2 px-3">' + order.status + '</span>');
            } else if (order.status == 'Order Cancelled') {
                $('#old_order_status').html('<span class="order_rejected py-2 px-3">' + order.status + '</span>');
            } else {
                $('#old_order_status').html('<span class="order_completed py-2 px-3">' + order.status + '</span>');
            }

            var productstotalHTML = buildHTMLProductstotal(order);
            if (productstotalHTML != '') {
                append_procucts_total.innerHTML = productstotalHTML;
            }

            orderPreviousStatus = order.status;
            if (order.hasOwnProperty('payment_method')) {
                orderPaymentMethod = order.payment_method;
            }


            if (order.status == "Order Placed") {
                $('#order_assigned').hide();
                $('#order_ongoing').hide();
                $('#order_completed').hide();
            } else if (order.status == "Order Accepted") {
                $('#order_accepted').hide();
                $('#order_ongoing').hide();
                $('#order_completed').hide();
                $('#order_rejected').hide();
            } else if (order.status == "Order Assigned") {
                $('#order_accepted').hide();
                $('#order_assigned').hide();
                $('#order_completed').hide();
                $('#order_rejected').hide();
            } else if (order.status == "Order Ongoing") {

                $("#extra_charge_div").show();

                $('#order_accepted').hide();
                $('#order_assigned').hide();
                $('#order_rejected').hide();
                $('#order_ongoing').hide();
            }

            if (order.status == "Order Completed" || order.status == "Order Cancelled" || order.status == "Order Rejected") {

                $('#order_status_div').hide();
                $('.edit-form-btn').hide();

            }
            if(order.extraCharges!=''){
                    $("#extra_charge_div").hide();
                }
            var price = 0;

            if (order.provider.photos && order.provider.photos.length) {
                $('.service-img').attr('src', order.provider.photos[0]);
            } else {
                $('.service-img').attr('src', place_image);
            }

            if (order.hasOwnProperty('provider') && order.provider.hasOwnProperty('id')) {


                var providerServiceDetail = await getProviderServiceDetail(order.provider.id);


                    var rating = 0;

                    if (providerServiceDetail.hasOwnProperty('reviewsCount') && providerServiceDetail.reviewsCount && providerServiceDetail.reviewsCount != "0.0" && providerServiceDetail.reviewsCount != null && providerServiceDetail.hasOwnProperty('reviewsSum') && providerServiceDetail.reviewsSum && providerServiceDetail.reviewsSum != "0.0" && providerServiceDetail.reviewsSum != null) {

                        rating = (parseFloat(providerServiceDetail.reviewsSum) / parseFloat(providerServiceDetail.reviewsCount));

                        rating = (rating * 10) / 10;

                    }
                    var ratingHtml = '<span class="badge badge-success text-white ml-auto" ><i class="fstar fa fa-star" ></i>' + (rating).toFixed(1) + '</span>';

                    if (order.provider.title) {
                        $('.service-title').html('<span class="provider-service-title">' + order.provider.title + '</span>' + ratingHtml);
                    }
                    var route1 = '{{route("ondemand.services.edit", ":id")}}';
                    route1 = route1.replace(':id', order.provider.id);


                    $('.service_details').attr('href', route1);
                    if (order.provider.address) {
                        $('#service_address').text(order.provider.address);
                    }

            }
            paymentStatus = order.paymentStatus;

            if (order.provider.categoryId != '' && order.provider.categoryId != null && order.provider.categoryId != undefined) {
                var CategoryDetail = await getServiceCategory(order.provider.categoryId);
                if (CategoryDetail == '' || CategoryDetail == undefined) {
                    $('#service_category').html("{{trans('lang.unknown')}}");
                } else {
                    $('#service_category').html(CategoryDetail.title);
                }
            }

            if (order.provider.subCategoryId != '' && order.provider.subCategoryId != null && order.provider.subCategoryId != undefined) {
                var SubCategoryDetail = await getServiceSubCategory(order.provider.subCategoryId);
                if (SubCategoryDetail == '' || SubCategoryDetail == undefined) {
                    $('#service_category').append("");
                } else {
                    $('#service_category').append("/" + SubCategoryDetail.title);
                }
            }


            if (order.provider.author != '' && order.provider.author != null && order.provider.author != undefined) {

                $('#provider_name_div').show();
                var providerDetail = await getProviderDetail(order.provider.author);
                if (providerDetail == '' || providerDetail == undefined) {
                    $('.provider_name').html("{{trans('lang.unknown')}}");
                    $('.provider_details').removeClass('redirecttopage');
                    $('.provider-img').attr('src', place_image);

                } else {
                    var rating = 0;

                    if (providerDetail.hasOwnProperty('reviewsCount') && providerDetail.reviewsCount && providerDetail.reviewsCount != "0.0" && providerDetail.reviewsCount != null && providerDetail.hasOwnProperty('reviewsSum') && providerDetail.reviewsSum && providerDetail.reviewsSum != "0.0" && providerDetail.reviewsSum != null) {

                        rating = (parseFloat(providerDetail.reviewsSum) / parseFloat(providerDetail.reviewsCount));

                        rating = (rating * 10) / 10;

                    }
                    var ratingHtml = '<span class="badge badge-success text-white ml-auto" ><i class="fstar fa fa-star" ></i>' + (rating).toFixed(1) + '</span>';

                    $('.provider_name').html('<span class="provider-title">' + providerDetail.firstName + ' ' + providerDetail.lastName + '</span>' + ratingHtml);
                    $('#provider_email').html(shortEmail(providerDetail.email));
                    if(providerDetail.phoneNumber.includes('+')){
                        $('#provider_phone').html('+' + EditPhoneNumber(providerDetail.phoneNumber.slice(1)));
                    }else{
                        $('#provider_phone').html(EditPhoneNumber(providerDetail.phoneNumber));
                    }
                    if (providerDetail.profilePictureURL != '') {

                        $('.provider-img').attr('src', providerDetail.profilePictureURL);
                    } else {
                        $('.provider-img').attr('src', place_image);

                    }
                    var providerDetailRoute = '{{route("providers.view", ":id")}}';
                    providerDetailRoute = providerDetailRoute.replace(':id', providerDetail.id);
                    $('.provider_details').attr('href', providerDetailRoute);
                }
            }
            otpToVerify = order.otp;
            extraChargePaymentStatus = order.extraPaymentStatus;
            if (order.hasOwnProperty('startTime') && order.hasOwnProperty('endTime')) {
                if (order.startTime != '' && order.endTime != '' && order.startTime != null && order.endTime != null) {
                    var orderStartTime = order.startTime.toDate();
                    var orderEndTime = order.endTime.toDate();
                    var timeDiff = Math.abs(orderEndTime - orderStartTime);
                    var hours = Math.floor(timeDiff / (1000 * 60 * 60));
                    var minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
                    const totalOrderTime = `${padZero(hours)}:${padZero(minutes)}:${padZero(seconds)}`;
                    $('.total_time_div').removeClass('d-none');
                    $('#total_time').removeClass('d-none');
                    $('.stop_time_div').addClass('d-none');
                    $('#timer').html(totalOrderTime);
                }
            }

            jQuery("#data-table_processing").hide();
        })
    });

    function getTwentyFourFormat(h, timeslot) {
        if (h < 10 && timeslot == "PM") {
            h = parseInt(h) + 12;
        } else if (h < 10 && timeslot == "AM") {
            h = '0' + h;
        }
        return h;
    }

    async function callWalletTransaction(status) {
        var orderStatus = status;

        var date = firebase.firestore.FieldValue.serverTimestamp();
        var wId = database.collection('temp').doc().id;

        database.collection('wallet').doc(wId).set({
            'amount': parseFloat(adminCommission_val),
            'date': date,
            'id': wId,
            'isTopUp': false,
            'note': 'Admin Commission debit',
            'order_id': "<?php echo $id; ?>",
            'payment_method': 'Wallet',
            'payment_status': 'success',
            'transactionUser': 'provider',
            'user_id': providerAuthor,
            'serviceType': 'ondemand-service',
        }).then(async function (result) {
            if (paymentMethod != 'cod') {
                var wId = database.collection('temp').doc().id;
                database.collection('wallet').doc(wId).set({
                    'amount': parseFloat(orderPayableAmount),
                    'date': date,
                    'id': wId,
                    'isTopUp': true,
                    'note': 'Booking Amount',
                    'order_id': "<?php echo $id; ?>",
                    'payment_method': 'Wallet',
                    'payment_status': 'success',
                    'transactionUser': 'provider',
                    'user_id': providerAuthor,
                    'serviceType': 'ondemand-service',
                }).then(async function (result) {
                 
                    var walletAmountToUpdate = parseFloat(orderPayableAmount) - parseFloat(adminCommission_val);
                    database.collection('users').where('id', '==', providerAuthor).get().then(async function (snapshotsnew) {
                        var providerData = snapshotsnew.docs[0].data();                       

                        if (providerData) {
                           
                            if (isNaN(providerData.wallet_amount) || providerData.wallet_amount == undefined) {
                                providerWallet = 0;
                            } else {
                                providerWallet = parseFloat(providerData.wallet_amount);
                            }
                            newProviderWallet = providerWallet + walletAmountToUpdate;
                            database.collection('users').doc(providerAuthor).update({
                                'wallet_amount': parseFloat(newProviderWallet)
                            }).then(async function (result) {
                                callAjax(orderStatus);
                            })
                        } else {
                            callAjax(orderStatus);
                        }

                    });

                })
            } else {
              
                var walletAmountToUpdate = parseFloat(adminCommission_val);
                database.collection('users').where('id', '==', providerAuthor).get().then(async function (snapshotsnew) {
                    var providerData = snapshotsnew.docs[0].data();

                   
                    if (providerData) {
                      
                        if (isNaN(providerData.wallet_amount) || providerData.wallet_amount == undefined) {
                            providerWallet = 0;
                        } else {
                            providerWallet = parseFloat(providerData.wallet_amount);
                        }
                        newProviderWallet = providerWallet - walletAmountToUpdate;
                        database.collection('users').doc(providerAuthor).update({
                            'wallet_amount': parseFloat(newProviderWallet)
                        }).then(async function (result) {
                            callAjax(orderStatus);
                        })
                    } else {
                        callAjax(orderStatus);
                    }

                });
            }
        })

    }

    async function callAjax(orderStatus) {

        var subject = '';
        var message = '';
        var fcm = '';
        if (orderStatus == "Order Rejected") {
            fcm = userFcmToken;
            subject = ProviderRejectedSubject;
            message = ProviderRejectedMessage;
        } else if (orderStatus == "Order Accepted") {
            fcm = userFcmToken;
            subject = ProviderAcceptedSubject;
            message = ProviderAcceptedMessage;
            if (parseInt(subscriptionTotalOrders) != -1) {
                        subscriptionTotalOrders = parseInt(subscriptionTotalOrders) - 1;
                }
                await database.collection('users').doc(providerAuthor).update({'subscriptionTotalOrders':subscriptionTotalOrders.toString()});
                let providerSnapshot = await database.collection('providers_services').where('author', '==', providerAuthor).get();
                let updatePromises = [];
                providerSnapshot.forEach(doc => {
                    updatePromises.push(
                        doc.ref.update({
                            'subscriptionTotalOrders': subscriptionTotalOrders.toString()
                        })
                    );
                });
                await Promise.all(updatePromises);
        } else if (orderStatus == "Order Assigned") {
            selectedWorker = $('#worker_list').val();
            if (selectedWorker != '') {
                fcm = $("#worker_list option[value='" + selectedWorker + "']").attr('fcm');
            }
            subject = ProviderAssignedSubject;
            message = ProviderAssignedMsg;
        } else if (orderStatus == "Order Ongoing") {
            fcm = userFcmToken;
            subject = ProviderOngoingSubject;
            message = ProviderOngoingMsg;
        } else if (orderStatus == "Order Completed") {
            fcm = userFcmToken;
            subject = ProviderCompletedSubject;
            message = ProviderCompletedMessage;
        } else if (orderStatus == "Extra Charges") {
            fcm = userFcmToken;
            subject = extraChargesSubject;
            message = extraChargesMsg;
        } else if (orderStatus == "Stop Time") {
            fcm = userFcmToken;
            subject = bookingTimeStopSubject;
            message = bookingTimeStopMsg;
        }

        await $.ajax({
            type: 'POST',
            url: "<?php echo route('order-status-notification'); ?>",
            data: {
                _token: '<?php echo csrf_token() ?>',
                'fcm': fcm,
                'serviceName': manname,
                'orderStatus': orderStatus,
                'subject': subject,
                'message': message
            },
            success: function (data) {
                if (orderStatus == "Extra Charges") {
                   window.location.reload();
                } else if (orderStatus == "Stop Time") {
                    window.location.reload();
                } else {
                    window.location.href = '{{ route("ondemand.bookings.index")}}';
                }


            }
        });
    }

    async function refundAmount() {
        var date = firebase.firestore.FieldValue.serverTimestamp();
        var wId = database.collection('temp').doc().id;
        if (paymentMethod != 'cod') {
            database.collection('wallet').doc(wId).set({
                'amount': parseFloat(orderPayableAmount),
                'date': date,
                'id': wId,
                'isTopUp': true,
                'note': 'Booking amount Refund',
                'order_id': "<?php echo $id; ?>",
                'payment_method': 'Wallet',
                'payment_status': 'success',
                'transactionUser': 'customer',
                'user_id': orderCustomerId,
                'serviceType': 'ondemand-service',
            }).then(async function (result) {
                var walletAmountToUpdate = parseFloat(orderPayableAmount);
                database.collection('users').where('id', '==', orderCustomerId).get().then(async function (snapshotsnew) {
                    var customerData = snapshotsnew.docs[0].data();
                    if (customerData) {
                        if (isNaN(customerData.wallet_amount) || customerData.wallet_amount == undefined) {
                            customerWallet = 0;
                        } else {
                            customerWallet = parseFloat(customerData.wallet_amount);
                        }
                        newCustomerWallet = customerWallet + walletAmountToUpdate;
                        database.collection('users').doc(orderCustomerId).update({
                            'wallet_amount': parseFloat(newCustomerWallet)
                        }).then(async function (result) {
                            callAjax('Order Rejected');
                        })
                    } else {
                        callAjax('Order Rejected');
                    }

                });

            })
        } else {
            callAjax('Order Rejected')
        }
    }

    $('#order-accept-btn').click(async function () {
     if (parseInt(subscriptionTotalOrders) == 0) {

                alert('{{ trans('lang.can_not_accept_more_orders') }}');

                return false;

            }
        var newScheduleDate = $('#new_schedule_date').val();
        if (newScheduleDate == '') {
            $('#select_date_time_err').html('{{trans("lang.select_date_time")}}');
            return false;
        }
        newScheduleDate = new Date($('#new_schedule_date').val());
        database.collection('provider_orders').doc(id).update({
            'status': 'Order Accepted',
            'newScheduleDateTime': newScheduleDate
        }).then(async function (result) {
            if (priceUnit != 'Hourly') {
                callWalletTransaction('Order Accepted');
            } else {
                callAjax('Order Accepted');
            }

        })

        

    });
    $('input[name="assign_order_radio"]').on('click', function () {
        var AssignTo = $("input[name='assign_order_radio']:checked").val();
        if (AssignTo == 'worker') {
            $('#worker_list_div').show();
        } else {
            $('#worker_list').val('');
            $('#worker_list_div').hide();
        }
    });
    $('#extra-charge-btn').on('click', function () {
        extraCharges = $('#extra_charge_amount').val(); // do not add var keyword before extraCharges it was defined globally
        var extraChargeDesc = $('#extra_charge_desc').val();
        if (extraCharges == '') {
            $('#add_extra_charge_err').html('{{trans("lang.add_extra_charge_err")}}');
            return false;
        }

        extraChargePaymentStatus = false; // do not add var keyword before extraChargePaymentStatus it was defined globally

        database.collection('provider_orders').doc(id).update({
            'extraCharges': extraCharges,
            'extraPaymentStatus': extraChargePaymentStatus,
            'extraChargesDescription': extraChargeDesc
        }).then(function (result) {
            callAjax('Extra Charges');
        })
    });
    $('#add-otp-btn').on('click', function () {
        otp = $('#otp').val();
        if (otp == '') {
            $('#enter_otp').html('{{trans("lang.enter_otp")}}');
            return false;
        }
        if (otp == otpToVerify) {
            database.collection('provider_orders').doc(id).update({
                'status': 'Order Completed',
                'extraPaymentStatus': true ,
                'paymentStatus':true
            }).then(async function (result) {
                callAjax('Order Completed');
            })
        } else {
            $('#enter_otp').html('{{trans("lang.invalid_otp")}}');
            return false;
        }

    });
    $('.stop_timer_btn').on('click', function () {
        stopTimer();
        $('.stop_time_div').addClass('d-none');
        storedEndTime = firebase.firestore.FieldValue.serverTimestamp();
        currentTime = new Date();

        var timeDiff = Math.abs(currentTime - storedStartTime);
        var totalTimeInHours = timeDiff / (1000 * 60 * 60);
        totalTimeInHours = parseFloat(totalTimeInHours).toFixed(2);

        database.collection('provider_orders').doc(id).update({
            'endTime': (priceUnit == 'Hourly') ? firebase.firestore.FieldValue.serverTimestamp() : null,
            'quantity': (totalTimeInHours < 1) ? parseInt(1) : parseFloat(totalTimeInHours)
        }).then(async function (result) {
            callAjax('Stop Time');
        })
    })
    $('#order-assign-btn').on('click', function () {
        var AssignTo = $("input[name='assign_order_radio']:checked").val();
        if (AssignTo == '' || AssignTo == null) {
            $('#select_radio_err').html('{{trans("lang.select_one_radio_option")}}');
            return false;
        }
        var worker = '';
        if (AssignTo == 'worker') {
            worker = $('#worker_list').val();
            if (worker == '' || worker == null) {
                $('#select_worker_err').html('{{trans("lang.select_worker")}}');
                return false;
            }
        }
        database.collection('provider_orders').doc(id).update({
            'status': 'Order Assigned',
            'workerId': worker
        }).then(async function (result) {
            callAjax('Order Assigned');
        })
    });

    async function checkBookingDateTime() {
        var dateToCheck = bookingDate;
        if (newScheduleBookingDate != '') {
            dateToCheck = newScheduleBookingDate;
        }
        var currentTime = new Date();
        if (dateToCheck.toDate() > currentTime) {
            var date1 = dateToCheck.toDate().toDateString();
            var date = new Date(date1);
            var dd = String(date.getDate()).padStart(2, '0');
            var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
            var yyyy = date.getFullYear();
            var date = yyyy + '-' + mm + '-' + dd;
            var time = dateToCheck.toDate().toLocaleTimeString('en-US');
            alert('{{trans("lang.you_can_start_booking_on")}} ' + date + ', ' + time);
        } else {
            database.collection('provider_orders').doc(id).update({
                'status': "Order Ongoing",
                'startTime': (priceUnit == 'Hourly') ? firebase.firestore.FieldValue.serverTimestamp() : null
            }).then(async function (result) {
                if (priceUnit == 'Hourly') {
                    startTimer();
                }
                callAjax('Order Ongoing');
            })
        }
    }

    $(".edit-form-btn").click(async function () {
        var clientName = $(".client_name").val();
        var orderStatus = $("#order_status").val();

        if (orderStatus == '' || orderStatus == null) {

            alert('{{trans("lang.select_status")}}');
            return false;
        }
        if (orderStatus == "Order Accepted") {
            $('#orderAcceptModal').modal('show');
        } else if (orderStatus == "Order Rejected") {
            database.collection('provider_orders').doc(id).update({
                'status': orderStatus
            }).then(async function (result) {
                refundAmount();
            })
        } else if (orderStatus == "Order Assigned") {
            $('#orderAssignModal').modal('show');
        } else if (orderStatus == "Order Ongoing") {
            checkBookingDateTime();

        } else if (orderStatus == "Order Completed") {
            if (priceUnit == 'Hourly') {
                if (storedEndTime != '') {
                    if (orderPaymentMethod != '' && orderPaymentMethod != null &&  extraChargePaymentStatus == true) {
                        $('#addOtpModal').modal('show');
                    } else {
                        alert('{{trans("lang.payment_pending")}}');
                    }
                } else {
                    alert('{{trans("lang.stop_the_timer")}}');
                }
            } else {
                if (extraCharges != '' && extraCharges != null && extraChargePaymentStatus == false) {
                    alert('{{trans("lang.extra_charges_payment_pending")}}');
                    return false;
                } else {
                    $('#addOtpModal').modal('show');
                }
            }
        }

    });


    function getUserReview(bookingOrder, reviewAttr) {
        var refUserReview = database.collection('items_review').where('orderid', "==", bookingOrder.id);
        refUserReview.get().then(async function (userreviewsnapshot) {
            var reviewHTML = '';
            reviewHTML = buildRatingsAndReviewsHTML(bookingOrder, userreviewsnapshot);
            if (userreviewsnapshot.docs.length > 0) {
                jQuery("#customers_rating_and_review").append(reviewHTML);
            } else {
                jQuery("#customers_rating_and_review").html('<h4>{{ trans('lang.no_reviews_found') }}</h4>');
            }
        });
    }

    function buildRatingsAndReviewsHTML(bookingOrder, userreviewsnapshot) {
        var allreviewdata = [];
        var reviewhtml = '';
        userreviewsnapshot.docs.forEach((listval) => {
            var reviewDatas = listval.data();
            reviewDatas.id = listval.id;
            allreviewdata.push(reviewDatas);
        });
        reviewhtml += '<div class="user-ratings">';
        allreviewdata.forEach((listval) => {
            var val = listval;

            var cus_name = '';

            if (val.VendorId == undefined ) {
                cus_name = "Review for worker";
            } else {
                cus_name = "Review for provider";
            }

            var provider = bookingOrder.provider;
            if (provider.id == val.productId) {

                rating = val.rating;
                reviewhtml = reviewhtml + '<div class="reviews-members py-3 border mb-3"><h6 class="cus_name text-dark">' + cus_name + '</h6><div class="media">';
                reviewhtml = reviewhtml + '<a href="javascript:void(0);"><img alt="#" src="' + provider.photos[0] + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" class=" img-circle img-size-32 mr-2" style="width:60px;height:60px"></a>';
                reviewhtml = reviewhtml + '<div class="media-body d-flex"><div class="reviews-members-header"><h6 class="mb-0"><a class="text-dark" href="javascript:void(0);">' + provider.title + '</a></h6><div class="star-rating"><div class="d-inline-block" style="font-size: 14px;">';
                reviewhtml = reviewhtml + ' <ul class="rating" data-rating="' + rating + '">';
                reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                reviewhtml = reviewhtml + '</ul>';
                reviewhtml = reviewhtml + '</div></div>';
                reviewhtml = reviewhtml + '</div>';
                reviewhtml = reviewhtml + '<div class="review-date ml-auto">';
                if (val.createdAt != null && val.createdAt != "") {
                    var review_date = val.createdAt.toDate().toLocaleDateString('en', {
                        year: "numeric",
                        month: "short",
                        day: "numeric"
                    });
                    reviewhtml = reviewhtml + '<span>' + review_date + '</span>';
                }
                reviewhtml = reviewhtml + '</div>';
                var photos = '';
                if (val.photos.length > 0) {
                    photos += '<div class="photos"><ul>';
                    $.each(val.photos, function (key, img) {
                        photos += '<li><img src="' + img + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"  width="100"></li>';
                    });
                    photos += '</ul></div>';
                }
                reviewhtml = reviewhtml + '</div></div><div class="reviews-members-body w-100"><p class="mb-2">' + val.comment + '</p>' + photos + '</div>';
                reviewhtml += '</div>';
            }
            reviewhtml += '</div>';


        });

        reviewhtml += '</div>';

        return reviewhtml;
    }


    function checkIsDownloadedItem(productId) {
        database.collection('vendor_products').doc(productId).get().then(async function (snapshots) {
            var productInfo = snapshots.data();
            if (productInfo != undefined) {
                if (productInfo.hasOwnProperty('isDigitalProduct') && productInfo.hasOwnProperty('digitalProduct') && productInfo.isDigitalProduct == true && productInfo.digitalProduct) {
                    $(".d-head").show();
                    $(".d-btn").show();
                    $(".d-btn[data-pid='" + productId + "']").html('<a href="' + productInfo.digitalProduct + '" class="btn btn-primary"><i class="fa fa-download"></i></a>');
                }
            }
        });
    }

    function buildHTMLProductstotal(snapshotsProducts) {

        let html = '';
        const intRegex = /^\d+$/;
        const floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;

        let adminCommission = snapshotsProducts.adminCommission || 0;
        let adminCommissionType = snapshotsProducts.adminCommissionType || 'fixed';
        let couponCode = snapshotsProducts.couponCode;
        let notes = snapshotsProducts.notes || '';
        let extraCharges = parseFloat(snapshotsProducts.extraCharges || 0);
        let platformFee = parseFloat(snapshotsProducts.platformFee || 0);

        // Base subtotal
        let quantity = parseFloat(snapshotsProducts.quantity || 1);
        let basePrice = parseFloat(snapshotsProducts.provider.price || 0);
        if (snapshotsProducts.provider.disPrice && parseFloat(snapshotsProducts.provider.disPrice) > 0) {
            basePrice = parseFloat(snapshotsProducts.provider.disPrice);
        }
        let subtotal = basePrice * quantity;
        let order_subtotal = subtotal;
        let totalDiscount = parseFloat(snapshotsProducts.discount || 0);

        html += '<tr><td class="seprater" colspan="2"><hr><span>{{trans("lang.price_detail")}}</span></td></tr>';

        let subtotalDisplay = currencyAtRight
            ? subtotal.toFixed(decimal_degits) + currentCurrency
            : currentCurrency + subtotal.toFixed(decimal_degits);

        let basePriceDisplay = currencyAtRight
            ? basePrice.toFixed(decimal_degits) + currentCurrency
            : currentCurrency + basePrice.toFixed(decimal_degits);


        // Price row
        if (snapshotsProducts.provider.priceUnit != 'Hourly') {
            html += `<tr class="final-rate"><td class="label">{{trans("lang.price")}}</td>
                    <td style="color:green">${basePriceDisplay} x ${quantity} (${subtotalDisplay})</td></tr>`;
        } else {
            html += `<tr class="final-rate"><td class="label">{{trans("lang.price")}}</td>
                    <td style="color:green">${basePriceDisplay}/hr</td></tr>`;
        }

        // Discount
        if ((intRegex.test(totalDiscount) || floatRegex.test(totalDiscount))) { 
            html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{trans("lang.discount")}}</span></td></tr>';
            subtotal -= totalDiscount;
            let discountDisplay = currencyAtRight
                ? totalDiscount.toFixed(decimal_degits) + currentCurrency
                : currentCurrency + totalDiscount.toFixed(decimal_degits);

            let couponHtml = couponCode ? `<br><small>{{trans("lang.coupon_codes")}} : ${couponCode}</small>` : '';
            html += `<tr><td class="label">{{trans("lang.discount")}} ${couponHtml}</td>
                    <td style="color:red">(-${discountDisplay})</td></tr>`;
        }

        // Subtotal after discount
        let subtotalDisplay2 = currencyAtRight
            ? subtotal.toFixed(decimal_degits) + currentCurrency
            : currentCurrency + subtotal.toFixed(decimal_degits);

        html += '<tr><td class="seprater" colspan="2"><hr><span>{{trans("lang.sub_total")}}</span></td></tr>';
        html += `<tr><td class="label">{{trans("lang.sub_total")}}</td><td>${subtotalDisplay2}</td></tr>`;

        // TAX CALCULATION
        let total_tax_amount = 0;
        let orderTaxable = Math.max(0, subtotal - totalDiscount);
        let orderCombinedTax = 0;
        (snapshotsProducts.taxSetting || []).forEach(tax => {
            if (tax.enable) {
                let taxAmount = tax.type === "percentage"
                    ? (parseFloat(tax.tax) / 100) * orderTaxable
                    : parseFloat(tax.tax);

                total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                orderCombinedTax += parseFloat(taxAmount);
            }
        });
        taxBreakdownGrouped.order[''] = orderCombinedTax;

        // Extra taxes
        [
            {key: 'platform', amount: platformFee, taxes: snapshotsProducts.platformTax || []},
        ].forEach(scope => {
            scope.taxes?.forEach(tax => {
                if (tax.enable) {
                    let taxAmount = 0;
                    if(scope.amount > 0){
                        taxAmount = tax.type === "percentage"
                        ? (parseFloat(tax.tax) / 100) * scope.amount
                        : parseFloat(tax.tax);
                    }
                    total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                    taxBreakdownGrouped[scope.key][tax.title] = (taxBreakdownGrouped[scope.key][tax.title] || 0) + parseFloat(taxAmount);
                }
            });
        });

        // Extra charges
        if (extraCharges > 0) {
            let extraDisplay = currencyAtRight
                ? extraCharges.toFixed(decimal_degits) + currentCurrency
                : currentCurrency + extraCharges.toFixed(decimal_degits);
            html += `<tr><td class="label">{{trans("lang.extra_charges")}}</td><td style="color:green">+${extraDisplay}</td></tr>`;
        }

        // FINAL TOTAL
        let order_total = subtotal + extraCharges + platformFee + total_tax_amount;

        html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.platform_charge') }}</span></td></tr>';
        html = html +'<tr><td class="label">{{ trans('lang.platform_charge') }}</td><td class="platform_charge " id="greenColor">+' +
                formatCurrency(platformFee, currencyData) + '</td></tr>';

        html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.tax_calculation') }}</span></td></tr>';
        html = html + renderTaxSection('order', 'Tax on Order Total');
        html = html + renderTaxSection('platform', 'Tax on Platform Fee');
        html = html +'<tr><td class="label"><strong>{{ trans('lang.total_tax') }}</strong></td><td class="total_tax " id="greenColor"><strong>+' +
        formatCurrency(total_tax_amount, currencyData) + '</strong></td></tr>';

        html += '<tr><td class="seprater" colspan="2"><hr></td></tr>';        
        html = html +
            '<tr class="grand-total"><td class="label">{{ trans('lang.total_amount') }}</td><td class="total_price_val " id="greenColor">' +
            formatCurrency(order_total, currencyData) + '</td></tr>';

        // Admin commission
        if (adminCommission > 0) {
            let adminCommHtml = "";
            let adminCommission_val = 0;
            if (adminCommissionType === "percentage") {
                adminCommHtml = "(" + adminCommission + "%)";
            }
            let commissionBase = (totalDiscount != 0 && totalDiscount != '') ? orderTaxable : order_subtotal;
            if (adminCommissionType === "percentage") {
                adminCommission_val = (commissionBase * adminCommission) / 100;
            } else {
                adminCommission_val = parseFloat(adminCommission);
            }
            html = html + '<tr><td class="label"><small>{{ trans('lang.admin_commission') }} ' + adminCommHtml +
            '</small> </td><td style="color:red"><small>( ' +  formatCurrency(adminCommission_val, currencyData) + ' )</small></td></tr>';
        }

        // Notes
        if (notes) {
            html += '<tr><td class="seprater" colspan="2"><hr></td></tr>';
            html += `<tr><td class="label">{{trans("lang.notes")}}</td><td>${notes}</td></tr>`;
        }

        return html;
    }

    async function getProviderFcm(providerId) {
        var providerFcm = '';
        await database.collection('users').where('id', '==', providerId).get().then(async function (snapshots) {
            if (snapshots.docs.length > 0) {
                providerData = snapshots.docs[0].data();
                 if (subscriptionModel || commissionModel) {
                        if (providerData.hasOwnProperty('subscriptionTotalOrders') &&
                            providerData.subscriptionTotalOrders != null && providerData.subscriptionTotalOrders != '') {
                            subscriptionTotalOrders = providerData.subscriptionTotalOrders;
                        }
                    }
                providerFcm = providerData.fcmToken;
            }
        })
        return providerFcm;
    }

    async function getCusotmerFcm(custId) {
        var customerFcm = '';
        await database.collection('users').where('id', '==', custId).get().then(async function (snapshots) {
            if (snapshots.docs.length > 0) {
                customerData = snapshots.docs[0].data();
                customerFcm = customerData.fcmToken;
            }
        })
        return customerFcm;
    }

    async function getWorkerDetail(workerId) {
        var workerDetail = '';
        await database.collection('providers_workers').where('id', '==', workerId).get().then(async function (snapshots) {
            if (snapshots.docs.length > 0) {
                workerDetail = snapshots.docs[0].data();

            }
        })
        return workerDetail;
    }

    async function getProviderServiceDetail(serviceId) {
        var serviceDetail = '';
        await database.collection('providers_services').where('id', '==', serviceId).get().then(async function (snapshots) {
            if (snapshots.docs.length > 0) {
                serviceDetail = snapshots.docs[0].data();

            }
        });
        return serviceDetail;
    }

    async function getProviderDetail(author) {
        var providerDetail = '';
        await database.collection('users').where('id', '==', author).get().then(async function (snapshots) {
            if (snapshots.docs.length > 0) {
                providerDetail = snapshots.docs[0].data();

            }
        });
        return providerDetail;
    }

    async function getServiceCategory(catId) {
        var categoryDetail = '';
        await database.collection('provider_categories').where('id', '==', catId).get().then(async function (snapshots) {
            if (snapshots.docs.length > 0) {
                categoryDetail = snapshots.docs[0].data();

            }
        })
        return categoryDetail;
    }

    async function getServiceSubCategory(subcatId) {
        var subcategoryDetail = '';
        await database.collection('provider_categories').where('id', '==', subcatId).get().then(async function (snapshots) {
            if (snapshots.docs.length > 0) {
                subcategoryDetail = snapshots.docs[0].data();

            }
        })
        return subcategoryDetail;
    }

    const timerDisplay = document.getElementById('timer');

    function startTimer() {
        startTime = Date.now();
        timerInterval = setInterval(updateTimer, 1000); // Update timer every second
    }

    function updateTimer() {
        const currentTime = Date.now();
        const elapsedTime = currentTime - startTime;
        const formattedTime = formatTime(elapsedTime);
        timerDisplay.textContent = formattedTime;
    }

    function formatTime(milliseconds) {
        const totalSeconds = Math.floor(milliseconds / 1000);
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        return `${padZero(hours)}:${padZero(minutes)}:${padZero(seconds)}`;
    }

    function padZero(num) {
        return num < 10 ? '0' + num : num;
    }

    function stopTimer() {
        clearInterval(timerInterval);
    }

    function renderTaxSection(section, labelSuffix) {
        let html = '';
        if (!taxBreakdownGrouped[section]) return '';
        for (let title in taxBreakdownGrouped[section]) {
            let taxlabel = title;
            let taxAmount = parseFloat(taxBreakdownGrouped[section][title]);
            html = html + '<tr><td class="label">' + taxlabel + " " + labelSuffix + '</td><td class="tax_amount" id="greenColor">+' + formatCurrency(taxAmount, currencyData) + '</td></tr>';
        }
        return html;
    }

</script>

@endsection
