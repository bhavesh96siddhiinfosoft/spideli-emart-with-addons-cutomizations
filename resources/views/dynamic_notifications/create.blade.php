@extends('layouts.app')

@section('content')
    <div class="page-wrapper">

        <div class="row page-titles">

            <div class="col-md-5 align-self-center">

                @if ($id == '')
                    <h3 class="text-themecolor">{{ trans('lang.create_notification') }}</h3>
                @else
                    <h3 class="text-themecolor">{{ trans('lang.edit_notification') }}</h3>
                @endif

            </div>

            <div class="col-md-7 align-self-center">

                <ol class="breadcrumb">

                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>

                    <li class="breadcrumb-item"><a href="{{ route('dynamic-notification.index') }}">{{ trans('lang.dynamic_notification') }}</a></li>

                    @if ($id == '')
                        <li class="breadcrumb-item active">{{ trans('lang.create_notification') }}</li>
                    @else
                        <li class="breadcrumb-item active">{{ trans('lang.edit_notification') }}</li>
                    @endif

                </ol>

            </div>

        </div>

        <div>

            <div class="card-body">

                <div class="error_top" style="display:none"></div>

                <div class="row vendor_payout_create">

                    <div class="vendor_payout_create-inner">

                        <fieldset>

                            <legend>{{ trans('lang.notification') }}</legend>

                            <div class="form-group row width-100">

                                <label class="col-3 control-label">{{ trans('lang.type') }}</label>

                                <div class="col-7">

                                    <input type="text" class="form-control" id="type" disabled>

                                </div>

                            </div>

                            <div class="form-group row width-100">

                                <label class="col-3 control-label">{{ trans('lang.subject') }}</label>

                                <div class="col-7">

                                    <input type="text" class="form-control" id="subject">

                                </div>

                            </div>

                            <div class="form-group row width-100">

                                <label class="col-3 control-label">{{ trans('lang.message') }}</label>

                                <div class="col-7">

                                    <textarea class="form-control" id="message"></textarea>

                                </div>

                            </div>

                        </fieldset>

                    </div>

                </div>

            </div>

            <div class="form-group col-12 text-center btm-btn">

                <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}

                </button>

                <a href="{{ url('/dynamic-notification') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>

            </div>

        </div>
    @endsection

    @section('scripts')
        <script>
            var requestId = "<?php echo $id; ?>";

            var database = firebase.firestore();

            var createdAt = firebase.firestore.FieldValue.serverTimestamp();

            var id = (requestId == '') ? database.collection("tmp").doc().id : requestId;



            var pagesize = 20;

            var start = '';

            $(document).ready(function() {

                if (requestId != '') {

                    var ref = database.collection('dynamic_notification').where('id', '==', id);

                    jQuery("#data-table_processing").show();

                    ref.get().then(async function(snapshots) {

                        if (snapshots.docs.length) {

                            var val = snapshots.docs[0].data();

                            $("#message").val(val.message);

                            $("#subject").val(val.subject);



                            var type = '';

                            var title = '';



                            if (val.type == "restaurant_rejected") {



                                type = "{{ trans('lang.order_rejected_by_restaurant') }}";

                                title = "{{ trans('lang.order_reject_notification') }}";

                            } else if (val.type == "restaurant_accepted") {

                                type = "{{ trans('lang.order_accepted_by_restaurant') }}";

                                title = "{{ trans('lang.order_accept_notification') }}";

                            } else if (val.type == "takeaway_completed") {

                                type = "{{ trans('lang.takeaway_order_completed') }}";

                                title = "{{ trans('lang.takeaway_order_complete_notification') }}";

                            } else if (val.type == "store_accepted") {

                                type = "{{ trans('lang.order_accepted_by_restaurant') }}";

                                title = "{{ trans('lang.order_accept_notification') }}";

                            } else if (val.type == "store_intransit") {

                                type = "{{ trans('lang.order_intransit_by_restaurant') }}";

                                title = "{{ trans('lang.order_intransit_notification') }}";

                            } else if (val.type == "store_completed") {

                                type = "{{ trans('lang.order_completed_by_restaurant') }}";

                                title = "{{ trans('lang.order_complete_notification') }}";

                            } else if (val.type == "cab_accepted") {

                                type = "{{ trans('lang.cab_accepted_by_driver') }}";

                                title = "{{ trans('lang.cab_accepted_order_notification') }}";

                            } else if (val.type == "cab_completed") {

                                type = "{{ trans('lang.cab_completed_by_driver') }}";

                                title = "{{ trans('lang.cab_completed_order_notification') }}";

                            } else if (val.type == "driver_completed") {

                                type = "{{ trans('lang.driver_completed_order') }}";

                                title = "{{ trans('lang.order_complete_notification') }}";



                            } else if (val.type == "driver_accepted") {

                                type = "{{ trans('lang.driver_accepted_order') }}";

                                title = "{{ trans('lang.driver_accept_order_notification') }}";

                            } else if (val.type == "dinein_canceled") {

                                type = "{{ trans('lang.dine_order_book_canceled_by_restaurant') }}";

                                title = "{{ trans('lang.dinein_cancel_notification') }}";

                            } else if (val.type == "dinein_accepted") {

                                type = "{{ trans('lang.dine_order_book_accepted_by_restaurant') }}";

                                title = "{{ trans('lang.dinein_accept_notification') }}";

                            } else if (val.type == "order_placed") {

                                type = "{{ trans('lang.new_order_place') }}";

                                title = "{{ trans('lang.order_placed_notification') }}";

                            } else if (val.type == "dinein_placed") {

                                type = "{{ trans('lang.new_dine_booking') }}";

                                title = "{{ trans('lang.dinein_order_place_notification') }}";



                            } else if (val.type == "schedule_order") {

                                type = "{{ trans('lang.shedule_order') }}";

                                title = "{{ trans('lang.schedule_order_notification') }}";

                            } else if (val.type == "payment_received") {

                                type = "{{ trans('lang.pament_received') }}";

                                title = "{{ trans('lang.payment_receive_notification') }}";

                            } else if (val.type == "parcel_accepted") {



                                type = "{{ trans('lang.parcel_accepted_by_driver') }}";

                                title = "{{ trans('lang.parcel_accept_notification') }}";

                            } else if (val.type == "parcel_rejected") {

                                type = "{{ trans('lang.parcel_rejected_by_driver') }}";

                                title = "{{ trans('lang.parcel_reject_notification') }}";

                            } else if (val.type == "rental_booked") {

                                type = "{{ trans('lang.rental_booked_by_customer') }}";

                                title = "{{ trans('lang.rental_book_notification') }}";

                            } else if (val.type == "rental_rejected") {

                                type = "{{ trans('lang.rental_rejected_by_driver') }}";

                                title = "{{ trans('lang.rental_reject_notification') }}";

                            } else if (val.type == "rental_accepted") {

                                type = "{{ trans('lang.rental_accepted_by_driver') }}";

                                title = "{{ trans('lang.rental_accept_notification') }}";

                            } else if (val.type == "start_ride") {

                                type = "{{ trans('lang.start_ride_by_driver') }}";

                                title = "{{ trans('lang.start_ride_notification') }}";

                            } else if (val.type == "rental_completed") {

                                type = "{{ trans('lang.rental_completed_by_driver') }}";

                                title = "{{ trans('lang.rental_complete_notification') }}";

                            } else if (val.type == "parcel_completed") {



                                type = "{{ trans('lang.parcel_completed_by_driver') }}";

                                title = "{{ trans('lang.parcel_complete_notification') }}";

                            } else if (val.type == "provider_accepted") {

                                type = "{{ trans('lang.booking_accepted_by_provider') }}";

                                title = "{{ trans('lang.booking_accepted_notification') }}";

                            } else if (val.type == "booking_placed") {

                                type = "{{ trans('lang.service_booked_by_customer') }}";

                                title = "{{ trans('lang.service_booked_notification') }}";

                            } else if (val.type == "provider_accepted") {

                                type = "{{ trans('lang.service_accepted_by_provider') }}";

                                title = "{{ trans('lang.service_accepted_notification') }}";

                            } else if (val.type == "service_intransit") {

                                type = "{{ trans('lang.service_intransit_by_provider') }}";

                                title = "{{ trans('lang.service_intransit_notification') }}";

                            } else if (val.type == "provider_rejected") {

                                type = "{{ trans('lang.service_rejected_by_provider') }}";

                                title = "{{ trans('lang.service_rejected_notification') }}";

                            } else if (val.type == "service_completed") {

                                type = "{{ trans('lang.service_completed_by_provider') }}";

                                title = "{{ trans('lang.service_completed_notification') }}";

                            } else if (val.type == "stop_time") {

                                type = "{{ trans('lang.service_stop_by_provider') }}";

                                title = "{{ trans('lang.service_stop_notification') }}";

                            } else if (val.type == "service_cancelled") {

                                type = "{{ trans('lang.service_cancelled_by_customer') }}";

                                title = "{{ trans('lang.service_cancelled_notification') }}";

                            } else if (val.type == "service_charges") {

                                type = "{{ trans('lang.service_add_extra_charges') }}";

                                title = "{{ trans('lang.extra_charge_notification') }}";

                            } else if (val.type == "worker_assigned") {

                                type = "{{ trans('lang.service_assigned_to_worker') }}";

                                title = "{{ trans('lang.service_assigned_to_worker_notification') }}";

                            } else if (val.type == "advertisement_approved") {
                                type = "{{ trans('lang.advertisement_approved') }}";
                            } else if (val.type == "advertisement_cancelled") {
                                type = "{{ trans('lang.advertisement_cancelled') }}";
                            } else if (val.type == "advertisement_paused") {
                                type = "{{ trans('lang.advertisement_paused') }}";
                            } else if (val.type == "advertisement_resumed") {
                                type = "{{ trans('lang.advertisement_resumed') }}";
                            }else if (val.type == "assign_order") {
                                type = "{{ trans('lang.assign_order') }}";
                            } else if (val.type == "restaurant_cancelled") {
                                type = "{{ trans('lang.restaurant_cancelled') }}";
                            } else if (val.type == "driver_cancelled") {
                                type = "{{ trans('lang.driver_cancelled') }}";
                            } else if (val.type == "new_delivery_order") {
                                type = "{{ trans('lang.new_delivery_order') }}";
                            } else if (np.type == "customer_cancelled") {
                                type = "{{ trans('lang.customer_cancelled') }}";
                            } else if (np.type == "dinein_rejected") {
                                type = "{{ trans('lang.dinein_rejected') }}";
                            }






                            $('#type').val(type);



                        }

                        jQuery("#data-table_processing").hide();



                    });

                }

            });



            $(".edit-setting-btn").click(async function() {



                $(".error_top").hide();

                var message = $("#message").val();

                var subject = $("#subject").val();

                var type = $('#type').val();



                if (subject == "") {

                    $(".error_top").show();

                    $(".error_top").html("");

                    $(".error_top").append("<p>{{ trans('lang.please_enter_subject') }}</p>");

                    window.scrollTo(0, 0);

                    return false;

                } else if (message == "") {

                    $(".error_top").show();

                    $(".error_top").html("");

                    $(".error_top").append("<p>{{ trans('lang.please_enter_message') }}</p>");

                    window.scrollTo(0, 0);

                    return false;

                } else {

                    jQuery("#data-table_processing").show();

                    requestId == '' ? (database.collection('dynamic_notification').doc(id).set({

                            'id': id,

                            'subject': subject,

                            'message': message,

                            'type': type,

                            'createdAt': createdAt



                        }).then(function(result) {

                            jQuery("#data-table_processing").hide();

                            $(".error_top").show();

                            $(".error_top").html("");

                            $(".error_top").append("<p style='color:green;border-left:4px solid green'>{{ trans('lang.notification_created_success') }}</p>");

                            window.scrollTo(0, 0);

                            window.location.href = '{{ route('dynamic-notification.index') }}';

                        }).catch(function(error) {

                            $(".error_top").show();

                            $(".error_top").html("");

                            $(".error_top").append("<p>" + error + "</p>");

                        })) :

                        (database.collection('dynamic_notification').doc(id).update({



                            'subject': subject,

                            'message': message,





                        }).then(function(result) {

                            jQuery("#data-table_processing").hide();

                            $(".error_top").show();

                            $(".error_top").html("");

                            $(".error_top").append("<p style='color:green;border-left:4px solid green'>{{ trans('lang.notification_updated_success') }}</p>");

                            window.scrollTo(0, 0);



                            window.location.href = '{{ route('dynamic-notification.index') }}';

                        }).catch(function(error) {

                            $(".error_top").show();

                            $(".error_top").html("");

                            $(".error_top").append("<p>" + error + "</p>");

                        }));





                }





            });
        </script>
    @endsection
