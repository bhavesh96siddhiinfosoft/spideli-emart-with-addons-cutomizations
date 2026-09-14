@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">

        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.parcel_plural')}} {{trans('lang.order_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{{route('parcel_orders')}}">{{trans('lang.parcel_plural')}}
                        {{trans('lang.order_plural')}}</a></li>
                <li class="breadcrumb-item">{{trans('lang.order_edit')}}</li>
            </ol>
        </div>
    </div>

    <div class="card-body">

        <?php if (in_array('parcel.orders.print', json_decode(@session('user_permissions'),true))) { ?>

            <div class="text-right print-btn non-printable">
                <button type="button" class="fa fa-print" onclick="PrintElem('printableArea')"></button>
            </div>
        <?php } ?>

        <div class="order_detail printableArea" id="order_detail">
            <div class="order_detail-top">
                <div class="row">
                    <div class="order_edit-genrl col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-header-title">{{trans('lang.general_details')}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="order_detail-top-box">

                                    <div class="form-group row widt-100 gendetail-col " id="div_parcel_category">
                                        <label class="col-12 control-label"><strong>{{trans('lang.parcel_category')}}:
                                            </strong><span id="parcel_type"></span></label>
                                    </div>

                                    <div class="form-group row widt-100 gendetail-col">
                                        <label class="col-12 control-label"><strong>{{trans('lang.date_created')}}
                                                : </strong><span id="createdAt"></span></label>

                                    </div>

                                    <div class="form-group row widt-100 gendetail-col payment_method">
                                        <label class="col-12 control-label"><strong>{{trans('lang.payment_methods')}}
                                                : </strong>
                                            <span id="payment_method"></span></label>

                                    </div>

                                    <div class="form-group row width-100 non-printable">
                                        <label class="col-3 control-label"><strong></strong>{{trans('lang.status')}}
                                            :</strong></label>
                                        <div class="col-7">
                                            <select id="order_status" class="form-control">
                                                <option value="Order Placed" id="order_placed">{{ trans('lang.order_placed')}}
                                                </option>
                                                <option value="Order Accepted" id="order_accepted">{{ trans('lang.order_accepted')}}
                                                </option>
                                                <option value="Order Rejected" id="order_rejected">{{ trans('lang.order_rejected')}}
                                                </option>
                                                <option value="Driver Pending" id="driver_pending">{{ trans('lang.driver_pending')}}
                                                </option>
                                                <option value="Driver Rejected" id="driver_rejected">{{ trans('lang.driver_rejected')}}
                                                </option>
                                                <option value="Order Shipped" id="order_shipped">{{ trans('lang.order_shipped')}}
                                                </option>
                                                <option value="In Transit" id="in_transit">{{ trans('lang.in_transit')}}
                                                </option>
                                                <option value="Order Completed" id="order_completed">{{ trans('lang.order_completed')}}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row width-100 non-printable">
                                        <label class="col-3 control-label"></label>
                                        <div class="col-7 text-right">
                                            <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i> {{trans('lang.update')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="order-items-list mt-4">
                            <div class="card">
                                <div class="card-body">
                                    <table cellpadding="0" cellspacing="0" class="table table-striped table-valign-middle">

                                        <thead>
                                            <tr>

                                                <th>{{trans('lang.parcel_weight')}}</th>
                                                <th>{{trans('lang.item_review_rate')}}</th>
                                                <th>{{trans('lang.parcel_distance')}}</th>
                                                <th>{{trans('lang.total')}}</th>
                                            </tr>

                                        </thead>

                                        <tbody id="order_products">

                                        </tbody>
                                    </table>
                                    <div class="form-group row width-50">

                                       <label class="col-12 control-label"><strong>{{ trans('lang.parcel_image') }}</strong></label>

                                        <div class="placeholder_img_thumb user_image">
                                            <img class="rounded" style="width:50px" src="" alt="Parcel_image" id="parcel_image">
                                            <span id="no-image-text" style="display:none; color:gray;">{{ trans('lang.parcel_images_not_found') }}</span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="order-data-row order-totals-items">
                            <div class="card">
                                <div class="card-body">
                                    <table class="order-totals">

                                        <tbody id="order_products_total">

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>



                    </div>

                    <div class="order_edit-genrl col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-header-title">{{ trans('lang.sender_details')}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="address order_detail-top-box">

                                    <div class="form-group row widt-100 gendetail-col ">
                                        <label class="col-12 control-label"><strong>{{trans('lang.sender_name')}}:</strong>
                                            <span id="sender_name"></span></label>

                                    </div>
                                    <div class="form-group row widt-100 gendetail-col ">
                                        <label class="col-12 control-label"><strong>{{trans('lang.sender_address')}}
                                                :</strong>
                                            <span id="sender_address"></span></label>

                                    </div>
                                    <div class="form-group row widt-100 gendetail-col ">
                                        <label class="col-12 control-label"><strong>{{trans('lang.date')}}:</strong>
                                            <span id="sender_datetime"></span></label>

                                    </div>

                                    <p><strong>{{trans('lang.phone')}}:</strong>
                                        <span id="sender_phone"></span>
                                    </p>
                                    <p><strong>{{trans('lang.note')}}:</strong>
                                        <span id="sender_note"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h4 class="card-header-title">{{ trans('lang.receiver_details')}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="address order_detail-top-box">
                                    <div class="form-group row widt-100 gendetail-col ">
                                        <label class="col-12 control-label"><strong>{{trans('lang.receiver_name')}}
                                                :</strong>
                                            <span id="receiver_name"></span></label>

                                    </div>
                                    <div class="form-group row widt-100 gendetail-col ">
                                        <label class="col-12 control-label"><strong>{{trans('lang.receiver_address')}}
                                                :</strong>
                                            <span id="receiver_address"></span></label>

                                    </div>
                                    <div class="form-group row widt-100 gendetail-col ">
                                        <label class="col-12 control-label"><strong>{{trans('lang.date')}}:</strong>
                                            <span id="receiver_datetime"></span></label>

                                    </div>


                                    <p><strong>{{trans('lang.phone')}}:</strong>
                                        <span id="receiver_phone"></span>
                                    </p>
                                    <p><strong>{{trans('lang.note')}}:</strong>
                                        <span id="receiver_note"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="order-deta-btm-right driver_details_hide mt-4">

                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-header-title">{{ trans('lang.driver_detail')}}</h4>
                                </div>

                                <div class="card-body">

                                    <div class="address order_detail-top-box">
                                        <p><strong>{{trans('lang.driver_name')}}:</strong>
                                            <span id="driver_firstName"></span> <span id="driver_lastName"></span><br>
                                        </p>
                                        <p><strong>{{trans('lang.email_address')}}:</strong>
                                            <span id="driver_email"></span>
                                        </p>
                                        <p><strong>{{trans('lang.phone')}}:</strong>
                                            <span id="driver_phone"></span>
                                        </p>
                                        {{-- <p id="para_carName"><strong>{{trans('lang.car_name')}}:</strong>
                                            <span id="driver_carName"></span>
                                        </p>
                                        <p><strong>{{trans('lang.car_number')}}:</strong>
                                            <span id="driver_carNumber"></span>
                                        </p> --}}
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="order_detail-review col-md-12 mt-4 non-printable">
                        <div class="row">
                            <div class="rental-review col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-header-title">{{trans('lang.customer_reviews')}}</h4>
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

</div>

</div>

</div>

</div>

@endsection

@section('style')

@section('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.js"></script>

<script type="text/javascript">
    var id_rendom = "<?php echo uniqid(); ?>";
    var adminCommission = 0;
    var id = "<?php echo $id; ?>";
    var fcmToken = '';
    var old_order_status = '';
    var payment_shared = false;
    var vendorname = '';
    var vendorId = '';
    
    var deliveryChargeVal = 0;
    var tip_amount_val = 0;
    var tip_amount = 0;
    var total_price_val = 0;
    var adminCommission_val = 0;
    var database = firebase.firestore();
    var ref = database.collection('parcel_orders').where("id", "==", id);
    var page_size = 5;
    var refUserReview = database.collection('items_review').where('orderid', '==', id);
    var append_procucts_list = '';
    var append_procucts_total = '';
    var total_price = 0;
    var currentCurrency = '';
    var currencyAtRight = false;
    var refCurrency = regionCurrencyRef();
    var orderPreviousStatus = '';
    var orderPaymentMethod = '';
    var orderCustomerId = '';
    var orderPaytableAmount = 0;
    var orderTakeAwayOption = false;
    var manfcmTokenVendor = '';
    var manname = '';
    var decimal_degits = 0;
    var vendorAuthor = '';
    var currencyData = '';
    refCurrency.get().then(async function(snapshots) {
        currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;
        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });


    var geoFirestore = new GeoFirestore(database);
    var place_image = '';
    var ref_place = database.collection('settings').doc("placeHolderImage");
    ref_place.get().then(async function(snapshots) {
        var placeHolderImage = snapshots.data();
        place_image = placeHolderImage.image;
    });

    var user_permissions = '<?php echo @session('user_permissions') ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkPrintPermission = false;
    if ($.inArray('parcel.orders.print', user_permissions) >= 0) {
        checkPrintPermission = true;
    }

    let taxBreakdownGrouped = {
        order: {},
        platform: {}
    };

    $(document).ready(function() {

        //hide this status for admin
        $('#order_placed').hide();
        $('#driver_pending').hide();
        $('#driver_rejected').hide();
        $('#order_shipped').hide();
        $('#in_transit').hide();
        $('#order_completed').hide();

        var alovelaceDocumentRef = database.collection('parcel_orders').doc();
        if (alovelaceDocumentRef.id) {
            id_rendom = alovelaceDocumentRef.id;
        }

        $(document.body).on('click', '.redirecttopage', function() {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        jQuery("#data-table_processing").show();

        ref.get().then(async function(snapshots) {
            var order = snapshots.docs[0].data();
            getUserReview(order);
            append_procucts_list = document.getElementById('order_products');
            append_procucts_list.innerHTML = '';


            append_procucts_total = document.getElementById('order_products_total');
            append_procucts_total.innerHTML = '';

            $("#sender_name").text(order.sender.name);
            $("#sender_address").text(order.sender.address);
            $("#sender_note").text(order.note ? order.note : '');
            $("#receiver_note").text(order.receiverNote ? order.receiverNote : '');
          if (Array.isArray(order.parcelImages) && order.parcelImages.length > 0 && order.parcelImages[0].trim() !== '') {
                $("#parcel_image").attr("src", order.parcelImages[0]).show();
                $("#no-image-text").hide();
            } else {
                $("#parcel_image").hide();
                $("#no-image-text").text("Parcel Images Not Found").show();
            }
           
            var date = "";
            var time = "";
            if (order.senderPickupDateTime) {
                date = order.senderPickupDateTime.toDate().toDateString();
                time = order.senderPickupDateTime.toDate().toLocaleTimeString();
            }
            $("#sender_datetime").text(date + " " + time);


            let cleanedPhone = order.sender.phone.replace(/[()\s]/g, '').replace(/^(\+)?/, '+');


            if (cleanedPhone.startsWith('+')) {
                $("#sender_phone").text(EditPhoneNumber(cleanedPhone));
            } else {
                $("#sender_phone").text(EditPhoneNumber('+' + cleanedPhone));
            }

            $("#receiver_name").text(order.receiver.name);

            $("#receiver_address").text(order.receiver.address);
            var date = "";
            var time = "";

            if (order.receiverPickupDateTime) {
                date = order.receiverPickupDateTime.toDate().toDateString();
                time = order.receiverPickupDateTime.toDate().toLocaleTimeString();

            }
            $("#receiver_datetime").text(date + " " + time);

            let cleanedReceiverPhone = order.receiver.phone.replace(/[()\s]/g, '').replace(/^(\+)?/, '+');

            if (cleanedReceiverPhone.startsWith('+')) {
                $("#receiver_phone").text(EditPhoneNumber(cleanedReceiverPhone));
            } else {
                $("#receiver_phone").text(EditPhoneNumber('+' + cleanedReceiverPhone));
            }



            if (order.createdAt) {
                var date1 = order.createdAt.toDate().toDateString();
                var date = new Date(date1);
                var dd = String(date.getDate()).padStart(2, '0');
                var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
                var yyyy = date.getFullYear();
                var createdAt_val = yyyy + '-' + mm + '-' + dd;
                var time = order.createdAt.toDate().toLocaleTimeString('en-US');

                $('#createdAt').text(createdAt_val + ' ' + time);
            }

            var payment_method = '';
            if (order.payment_method) {

                if (order.payment_method == "stripe") {
                    image = '{{asset("images/payment/stripe.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "xendit") {
                    image = '{{asset("images/payment/xendit.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "midtrans") {
                    image = '{{asset("images/payment/midtrans.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"  width="30%" height="30%">';

                } else if (order.payment_method == "orangepay") {
                    image = '{{asset("images/payment/orangepay.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "cod") {
                    image = '{{asset("images/payment/cashondelivery.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"  width="30%" height="30%">';

                } else if (order.payment_method == "razorpay") {
                    image = '{{asset("images/payment/razorepay.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"  width="30%" height="30%">';

                } else if (order.payment_method == "paypal") {
                    image = '{{asset("images/payment/paypal.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "payfast") {
                    image = '{{asset("images/payment/payfast.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "paystack") {
                    image = '{{asset("images/payment/paystack.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "flutterwave") {
                    image = '{{asset("images/payment/flutter_wave.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "mercadoPago" || order.payment_method == "mercado pago" || order.payment_method == "mercadopago") {
                    image = '{{asset("images/payment/marcado_pago.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "wallet") {
                    image = '{{asset("images/payment/foodie_wallet.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%" >';

                } else if (order.payment_method == "paytm") {
                    image = '{{asset("images/payment/paytm.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "cancelled order payment") {
                    image = '{{asset("images/payment/cancel_order.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "refund amount") {
                    image = '{{asset("images/payment/refund_amount.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';
                } else if (order.payment_method == "referral amount") {
                    image = '{{asset("images/payment/reffral_amount.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';
                } else {
                    payment_method = order.payment_method;
                }
            }
            $('#payment_method').html('<span>' + payment_method + '</span>');

            if (order.driver != '' && order.driver != undefined) {
              
                $('#driver_email').html('<a href="mailto:' + order.driver.email + '">' + shortEmail(order.driver.email) + '</a>');
                $('#driver_firstName').text(order.driver.firstName);
                $('#driver_lastName').text(order.driver.lastName);
                if (order.driver.phoneNumber.includes('+')) {
                    $('#driver_phone').text('+' + EditPhoneNumber(order.driver.phoneNumber.slice(1)));
                } else {
                    $('#driver_phone').text(EditPhoneNumber(order.driver.phoneNumber));
                }

            } else {

                $('.driver_details_hide').empty();
            }

            fcmToken = order.author.fcmToken;
            customername = order.author.firstName;
            old_order_status = order.status;

            if (order.payment_shared != undefined) {
                payment_shared = order.payment_shared;
            }
            var productsListHTML = buildHTMLParcelList(order);
            var productstotalHTML = buildParcelTotal(order);

            if (productsListHTML != '') {
                append_procucts_list.innerHTML = productsListHTML;
            }
            if (productstotalHTML != '') {
                append_procucts_total.innerHTML = productstotalHTML;
            }
            orderPreviousStatus = order.status;
            if (order.hasOwnProperty('payment_method')) {
                orderPaymentMethod = order.payment_method;
            }

            $("#order_status option[value='" + order.status + "']").attr("selected", "selected");
            if (order.status == "Order Rejected" || order.status == "Driver Rejected") {
                $("#order_status").prop("disabled", true);
            }

            jQuery("#data-table_processing").hide();
        })

        $(".edit-form-btn").click(async function() {


            var clientName = $(".client_name").val();
            var orderStatus = $("#order_status").val();
            if (old_order_status != orderStatus) {

                if (orderStatus == "Order Completed") {
                    manfcmTokenVendor = fcmToken;
                    manname = customername;
                } else {
                    manfcmTokenVendor = fcmToken;
                    manname = vendorname;
                }

                database.collection('parcel_orders').doc(id).update({
                    'status': orderStatus
                }).then(async function(result) {
                    if (orderStatus != orderPreviousStatus && payment_shared == false) {
                        if (orderStatus == 'Order Completed') {
                            await database.collection('parcel_orders').doc(id).update({
                                'payment_shared': true
                            }).then(async function(result) {});
                        }


                        await $.ajax({
                            type: 'POST',
                            url: "<?php echo route('order-status-notification'); ?>",
                            data: {
                                _token: '<?php echo csrf_token() ?>',
                                'fcm': manfcmTokenVendor,
                                'vendorname': manname,
                                'orderStatus': orderStatus
                            },
                            success: function(data) {

                                if (orderPreviousStatus != 'Order Rejected' && orderPreviousStatus != 'Driver Rejected' && orderPaymentMethod != 'cod' && orderTakeAwayOption == false) {
                                    if (orderStatus == 'Order Rejected' || orderStatus == 'Driver Rejected') {

                                    } else {

                                        window.location.href = '{{ route("parcel_orders")}}';
                                    }
                                } else {
                                    window.location.href = '{{ route("parcel_orders")}}';
                                }

                            }
                        });

                    } else {

                        $.ajax({
                            type: 'POST',
                            url: "<?php echo route('order-status-notification'); ?>",
                            data: {
                                _token: '<?php echo csrf_token() ?>',
                                'fcm': manfcmTokenVendor,
                                'vendorname': manname,
                                'orderStatus': orderStatus
                            },
                            success: function(data) {

                                if (orderPreviousStatus != 'Order Rejected' && orderPreviousStatus != 'Driver Rejected' && orderPaymentMethod != 'cod' && orderTakeAwayOption == false) {
                                    if (orderStatus == 'Order Rejected' || orderStatus == 'Driver Rejected') {
                                        var walletId = "<?php echo uniqid(); ?>";
                                        var canceldateNew = new Date();
                                        var orderCancelDate = new Date(canceldateNew.setHours(23, 59, 59, 999));
                                        database.collection('wallet').doc(walletId).set({
                                            'amount': parseFloat(orderPaytableAmount),
                                            'date': orderStatus,
                                            'id': walletId,
                                            'payment_status': 'success',
                                            'user_id': orderCustomerId,
                                            'payment_method': 'Cancelled Order Payment'
                                        }).then(function(result) {
                                            window.location.href = '{{ route("orders")}}';
                                        })
                                    } else {

                                        window.location.href = '{{ route("orders")}}';
                                    }
                                } else {

                                    window.location.href = '{{ route("orders")}}';
                                }

                            }
                        });

                    }


                });
            }
        })

    })

    function buildHTMLParcelList(snapshotsParcel) {
        var html = '';
        
        html = html + '<tr>';

        html = html + '<td class="order-product"><div class="order-product-box">';

        html = html + '</div><div class="orders-tracking"><h6>' + snapshotsParcel.parcelWeight + '</h6><div class="orders-tracking-item-details">';

        html = html + '</div></div></td>';
        
        var parcelWeightCharge = "";
        var subTotal = "";
        if (currencyAtRight) {
            parcelWeightCharge = parseFloat(snapshotsParcel.parcelWeightCharge).toFixed(decimal_degits) + "" + currentCurrency;
            subTotal = parseFloat(snapshotsParcel.subTotal).toFixed(decimal_degits) + "" + currentCurrency;
        } else {
            parcelWeightCharge = currentCurrency + "" + parseFloat(snapshotsParcel.parcelWeightCharge).toFixed(decimal_degits);
            subTotal = currentCurrency + "" + parseFloat(snapshotsParcel.subTotal).toFixed(decimal_degits);
        }

        if (snapshotsParcel.hasOwnProperty('parcelType') && snapshotsParcel.parcelType != '') {
            $('#parcel_type').text(snapshotsParcel.parcelType);
        } else {
            $('#div_parcel_category').hide();
        }

        html = html + '<td>' + parcelWeightCharge + '</td><td> ' + parseFloat(snapshotsParcel.distance).toFixed(decimal_degits) + ' Km' + '</td><td>  ' + subTotal + '</td>';

        html = html + '</tr>';

        return html;
    }

    function buildParcelTotal(orderData) {

        var html = '';
    
        let adminCommission =  parseFloat(orderData.adminCommission || 0);;
        let adminCommissionType = orderData.adminCommissionType;
        let order_subtotal = parseFloat(orderData.subTotal || 0);
        let total_discount = 0;
        let total_tax_amount = 0;

        // Discount
        let discount = parseFloat(orderData.discount || 0);
        total_discount = isNaN(discount) ? 0 : discount;

        // Order tax
        let orderTaxable = Math.max(0, order_subtotal - total_discount);
        let orderCombinedTax = 0;
        (orderData.taxSetting || []).forEach(tax => {
            if (tax.enable) {
                let taxAmount = tax.type === "percentage"
                    ? (parseFloat(tax.tax) / 100) * orderTaxable
                    : parseFloat(tax.tax);

                total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                orderCombinedTax += parseFloat(taxAmount);
            }
        });
        taxBreakdownGrouped.order[''] = orderCombinedTax;

        // Extra charges
        let platformFee = parseFloat(orderData.platformFee || 0);

        // Extra taxes
        [
            {key: 'platform', amount: platformFee, taxes: orderData.platformTax || []},
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

        // Final price
        let order_total = (order_subtotal - total_discount) + platformFee + total_tax_amount;
        
        html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.sub_total') }}</span></td></tr>';
        html = html +
            '<tr class="final-rate"><td class="label">{{ trans('lang.sub_total') }}</td><td class="sub_total" style="color:green">(' +
            formatCurrency(order_subtotal, currencyData) + ')</td></tr>';

        html = html +
            '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.discount') }}</span></td></tr>';
        
        let couponCode_html = '';
        if (discount > 0) {
            let discountLabel = "(" + orderData.discountLabel + "%)";
            couponCode_html = '</br><small>{{ trans('lang.coupon_codes') }} :' + discountLabel + '</small>';
        }
        html = html + '<tr><td class="label">{{ trans('lang.discount') }}' + couponCode_html +
            '</td><td class="discount text-danger">(-' + formatCurrency(total_discount, currencyData) + ')</td></tr>';

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

        if (adminCommission > 0) {
            let adminCommHtml = "";
            let adminCommission_val = 0;
            if (adminCommissionType === "percentage") {
                adminCommHtml = "(" + adminCommission + "%)";
            }
            let commissionBase = (discount != 0 && discount != '') ? orderTaxable : order_subtotal;
            if (adminCommissionType === "percentage") {
                adminCommission_val = (commissionBase * adminCommission) / 100;
            } else {
                adminCommission_val = parseFloat(adminCommission);
            }
            html = html + '<tr><td class="label"><small>{{ trans('lang.admin_commission') }} ' + adminCommHtml +
            '</small> </td><td style="color:red"><small>( ' +  formatCurrency(adminCommission_val, currencyData) + ' )</small></td></tr>';
        }

        return html;
    }


    function PrintElem(elem) {
        var css = '@page { size: portrait; }',
            head = document.head || document.getElementsByTagName('head')[0],
            style = document.createElement('style');

        style.type = 'text/css';
        style.media = 'print';

        if (style.styleSheet) {
            style.styleSheet.cssText = css;
        } else {
            style.appendChild(document.createTextNode(css));
        }

        head.appendChild(style);
        var printContents = $('.printableArea').html();
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;

    }



    function getUserReview(Order) {
        refUserReview.limit(page_size).get().then(async function(userreviewsnapshot) {
            var reviewHTML = '';
            reviewHTML = buildRatingsAndReviewsHTML(Order, userreviewsnapshot);
            if (userreviewsnapshot.docs.length > 0) {
                jQuery("#customers_rating_and_review").append(reviewHTML);
            } else {
                jQuery("#customers_rating_and_review").html('<h4>No Reviews Found</h4>');
            }
        });
    }

    function buildRatingsAndReviewsHTML(Order, userreviewsnapshot) {
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
            var review_user_view = '{{route("users.view",":id")}}';
            review_user_view = review_user_view.replace(':id', val.CustomerId);
            rating = val.rating;
            reviewhtml = reviewhtml + '<div class="reviews-members py-3 border mb-3"><div class="media">';
            reviewhtml = reviewhtml + '<a href="' + review_user_view + '"><img alt="#" src="' + val.profile + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" class=" img-circle img-size-32 mr-2" style="width:60px;height:60px"></a>';
            reviewhtml = reviewhtml + '<div class="media-body d-flex"><div class="reviews-members-header"><h6 class="mb-0"><a class="text-dark" href="' + review_user_view + '">' + val.uname + '</a></h6><div class="star-rating"><div class="d-inline-block" style="font-size: 14px;">';
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
            reviewhtml = reviewhtml + '</div></div><div class="reviews-members-body w-100"><p class="mb-2">' + val.comment + '</p></div>';
            reviewhtml += '</div>';
            reviewhtml += '</div>';


        });

        reviewhtml += '</div>';

        return reviewhtml;
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