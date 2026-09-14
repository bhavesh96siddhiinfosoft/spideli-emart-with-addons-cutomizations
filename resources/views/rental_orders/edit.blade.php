@extends('layouts.app')

@section('content')

    <div class="page-wrapper">
        <div class="row page-titles">

            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.rental_plural') }} {{ trans('lang.order_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('rental_orders') }}">{{ trans('lang.rental_plural') }}
                            {{ trans('lang.order_plural') }}</a>
                    </li>
                    <li class="breadcrumb-item">{{ trans('lang.order_edit') }}</li>
                </ol>
            </div>
        </div>

        <div class="card-body">
            <?php if (in_array('rental-orders.print', json_decode(@session('user_permissions'),true))) { ?>

            <div class="text-right print-btn">
                <button type="button" class="fa fa-print" onclick="PrintElem('order_detail')"></button>
            </div>
            <?php } ?>

            <div class="row vendor_payout_create" style="max-width:100%;" role="tabpanel">

                <div class="vendor_payout_create-inner tab-content">

                    <div role="tabpanel" class="tab-pane active">
                        <div class="order_detail printableArea" id="order_detail">
                            <div class="order_detail-top">
                                <div class="row">
                                    <div class="order_edit-genrl col-md-7">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-header-title">{{ trans('lang.general_details') }}</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="order_detail-top-box">

                                                    <div class="form-group row widt-100 gendetail-col">
                                                        <label class="col-12 control-label"><strong>{{ trans('lang.date_created') }}
                                                                : </strong><span id="createdAt"></span></label>

                                                    </div>

                                                    <div class="form-group row widt-100 gendetail-col payment_method">
                                                        <label class="col-12 control-label"><strong>{{ trans('lang.payment_methods') }}
                                                                : </strong><span id="payment_method"></span></label>

                                                    </div>
                                                    <div id="statusDiv">
                                                        <div class="form-group row width-100 ">
                                                            <label
                                                                class="col-3 control-label">{{ trans('lang.status') }}:</label>
                                                            <div class="col-7">
                                                                <select id="order_status" class="form-control">
                                                                    <option value="Order Placed" id="order_placed">
                                                                        {{ trans('lang.order_placed') }}
                                                                    </option>
                                                                    <option value="Order Accepted" id="order_accepted">
                                                                        {{ trans('lang.order_accepted') }}
                                                                    </option>
                                                                    <option value="Order Rejected" id="order_rejected">
                                                                        {{ trans('lang.order_rejected') }}
                                                                    </option>
                                                                    <option value="Driver Pending" id="driver_pending">
                                                                        {{ trans('lang.driver_pending') }}
                                                                    </option>
                                                                    <option value="Driver Rejected" id="driver_rejected">
                                                                        {{ trans('lang.driver_rejected') }}
                                                                    </option>
                                                                    <option value="Order Shipped" id="order_shipped">
                                                                        {{ trans('lang.order_shipped') }}
                                                                    </option>
                                                                    <option value="In Transit" id="in_transit">
                                                                        {{ trans('lang.in_transit') }}
                                                                    </option>
                                                                    <option value="Order Completed" id="order_completed">
                                                                        {{ trans('lang.order_completed') }}
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="form-group row width-100">
                                                            <label class="col-3 control-label"></label>
                                                            <div class="col-7 text-right">
                                                                <button type="button"
                                                                    class="btn btn-primary edit-form-btn"><i
                                                                        class="fa fa-save"></i> {{ trans('lang.update') }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>
                                        </div>

                                        <div class="order-data-row order-totals-items mt-4">
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

                                    <div class="order_addre-edit col-md-5">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-header-title">{{ trans('lang.pick_up_details') }}</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="address order_detail-top-box">


                                                    <p>
                                                        <strong>{{ trans('lang.date') }}:</strong>
                                                        <span id="pickUpTime"></span><br>
                                                    </p>

                                                    <p><strong>{{ trans('lang.address') }}:</strong>
                                                        <span id="pickUpAddress"></span>
                                                    </p>

                                                </div>
                                            </div>
                                        </div>


                                        <div class="order-deta-btm-right mt-4">

                                            <div class="card">
                                                <div class="card-header">
                                                    <h4 class="card-header-title">{{ trans('lang.user_details') }}</h4>
                                                </div>

                                                <div class="card-body">
                                                    <div class="address order_detail-top-box">
                                                        <p>
                                                            <strong>{{ trans('lang.name') }}:</strong><span
                                                                id="user_firstName"></span>
                                                            <span id="user_lastName"></span><br>
                                                        </p>
                                                        <p><strong>{{ trans('lang.email_address') }}:</strong>
                                                            <span id="user_email"></span>
                                                        </p>
                                                        <p><strong>{{ trans('lang.phone') }}:</strong>
                                                            <span id="user_phone"></span>
                                                        </p>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="order-deta-btm-right mt-4">

                                            <div class="card">
                                                <div class="card-header">
                                                    <h4 class="card-header-title">{{ trans('lang.rental_details') }}</h4>
                                                </div>

                                                <div class="card-body">
                                                    <div class="address rental_detail-top-box">
                                                        <p>
                                                            <strong>{{ trans('lang.rental_package') }}:</strong><span
                                                                id="rental_pack_name"></span>
                                                            <!-- <span id="user_lastName"></span> --><br>
                                                        </p>
                                                        <p><strong>{{ trans('lang.rental_package_price') }}:</strong>
                                                            <span id="rental_pack_price"></span><br>
                                                        </p>
                                                        <p><strong>{{ trans('lang.rental_package_hours') }}:</strong>
                                                            <span id="rental_pack_hours"></span><br>
                                                        </p>
                                                        <p><strong>{{ trans('lang.rental_package_km') }}:</strong>
                                                            <span id="rental_pack_km"></span><br>
                                                        </p>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                          <div class="resturant-detail mt-4">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h4 class="card-header-title">{{ trans('lang.driver_detail') }}</h4>
                                                </div>

                                                <div class="card-body">
                                                    <a href="" class="row redirecttopage" id="resturant-view">
                                                        <div class="col-4">
                                                            <img src="" class="resturant-img rounded-circle"
                                                                alt="driver" width="70px" height="70px">
                                                        </div>
                                                        <div class="col-8">
                                                            <h4 class="vendor-title"></h4>
                                                        </div>
                                                    </a>

                                                    <h5 class="contact-info">{{ trans('lang.contact_info') }}:</h5>

                                                    <p><strong id="vendor_phone1">{{ trans('lang.phone') }}:</strong>
                                                        <span id="vendor_phone"></span>
                                                    </p>
                                                    <h5 class="contact-info">{{ trans('lang.car_info') }}:</h5>
                                               
                                                    <br>
                                                    <p><strong id="driver_carName1"
                                                            style="width:auto !important;">{{ trans('lang.car_name') }}:</strong>
                                                        <span id="driver_carName"></span>
                                                    </p> <br>
                                                    <p><strong id="driver_carNumber1"
                                                            style="width:auto !important;">{{ trans('lang.car_number') }}:</strong>
                                                        <span id="driver_carNumber"></span>
                                                    </p> <br>
                                                    <p><strong id="driver_car_make1"
                                                            style="width:auto !important;">{{ trans('lang.car_make') }}:</strong>
                                                        <span id="driver_car_make"></span>
                                                    </p>
                                                    <p><strong id="driver_car_type1"
                                                            style="width:auto !important;">{{ trans('lang.vehicle_type') }}:</strong>
                                                        <span id="driver_car_type"></span>
                                                    </p>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="order_detail-review col-md-12 mt-4 non-printable">
                                        <div class="row">
                                            <div class="rental-review col-md-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h4 class="card-header-title">{{ trans('lang.customer_reviews') }}
                                                        </h4>
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


                            <div class="order-deta-btm mt-4">
                                <div class="row">


                                    <div class="col-md-4 order-deta-btm-right driver_details_hide">
                                      

                                    </div>
                                </div>

                            </div>


                        </div>
                    </div>


                </div>
            </div>

        </div>


        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary edit-form-btn d-none"><i class="fa fa-save"></i>
                {{ trans('lang.save') }}
            </button>
            <a href="{!! route('rental_orders') !!}" class="btn btn-default"><i
                    class="fa fa-undo"></i>{{ trans('lang.cancel') }}
            </a>
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
        var driverId = '';
        var deliveryChargeVal = 0;
        var tip_amount_val = 0;
        var tip_amount = 0;
        var total_price_val = 0;
        var adminCommission_val = 0;
        var database = firebase.firestore();
        var ref = database.collection('rental_orders').where("id", "==", id);
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
        var page_size = 5;
        var refUserReview = database.collection('items_review').where('orderid', '==', id);
        var currencyData = '';
        refCurrency.get().then(async function(snapshots) {
            currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        });

        var user_permissions = '<?php echo @session('user_permissions'); ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));

        var checkPrintPermission = false;
        if ($.inArray('rental-orders.print', user_permissions) >= 0) {
            checkPrintPermission = true;
        }

        var geoFirestore = new GeoFirestore(database);
        var place_image = '';
        var ref_place = database.collection('settings').doc("placeHolderImage");
        ref_place.get().then(async function(snapshots) {
            var placeHolderImage = snapshots.data();
            place_image = placeHolderImage.image;
        });

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

            var alovelaceDocumentRef = database.collection('vendor_orders').doc();
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
                
                append_procucts_total = document.getElementById('order_products_total');
                append_procucts_total.innerHTML = '';

                $('.resturant-img').attr('src', place_image);
                $('.car-img').attr('src', place_image);

                if (order?.driver?.id) {

                    var driver = database.collection('users').where("id", "==", order.driver.id);

                    driver.get().then(async function(snapshotsnew) {
                        if (!snapshotsnew.empty) {
                            var driverdata = snapshotsnew.docs[0].data();


                            if (driverdata.id) {
                                var route_view = '{{ route('drivers.view', ':id') }}';
                                route_view = route_view.replace(':id', driverdata.id);

                                $('#resturant-view').attr('data-url', route_view);
                            }
                            if (driverdata.profilePictureURL) {
                                $('.resturant-img').attr('src', driverdata
                                    .profilePictureURL);
                            } else {
                                $('.resturant-img').attr('src', place_image);
                            }
                            if (driverdata.firstName) {
                                $('.vendor-title').html(driverdata.firstName + ' ' +
                                    driverdata.lastName);
                            }

                            if (driverdata.email) {
                                $('#vendor_email').html(shortEmail(driverdata.email));
                            }
                            if (driverdata.phoneNumber) {
                                if (driverdata.phoneNumber.includes('+')) {
                                    $('#vendor_phone').text('+' + EditPhoneNumber(driverdata
                                        .phoneNumber.slice(1)));
                                } else {
                                    $('#vendor_phone').text(EditPhoneNumber(driverdata
                                        .phoneNumber));
                                }
                            }
                            if (driverdata.id) {
                                var route_view = '{{ route('drivers.view', ':id') }}';
                                route_view = route_view.replace(':id', driverdata.id);

                                $('#resturant-car').attr('data-url', route_view);
                            }
                         
                            if (driverdata.carName) {
                                $('#driver_carName').html(driverdata.carName);
                            }

                            if (driverdata.carNumber) {
                                $('#driver_carNumber').html(driverdata.carNumber);
                            }
                            if (driverdata.carMakes) {
                                $('#driver_car_make').text(driverdata.carMakes);
                            }
                            if (driverdata.vehicleType) {
                                $('#driver_car_type').text(driverdata.vehicleType);

                            }
                        } else {

                            $('.resturant-img').hide();
                            $('.vendor-title').text('Driver deleted');
                            $('#vendor_email').hide();
                            $('#vendor_phone').hide();

                            $('#resturant-car').hide();
                            $('#driver_carName').hide();
                            $('#driver_carNumber').hide();
                            $('#driver_car_make').hide();
                            $('#driver_car_type').hide();

                            $('#vendor_phone1').hide();

                            $('#driver_carName1').hide();
                            $('#driver_carNumber1').hide();
                            $('#driver_car_make1').hide();
                            $('#driver_car_type1').hide();

                            $('.contact-info').hide();
                        }
                    });

                }

                if (order.bookingDateTime) {
                    var date1 = order.bookingDateTime.toDate().toDateString();
                    var date = new Date(date1);
                    var dd = String(date.getDate()).padStart(2, '0');
                    var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
                    var yyyy = date.getFullYear();
                    var pickUpTimeVal = yyyy + '-' + mm + '-' + dd;
                    var time = order.bookingDateTime.toDate().toLocaleTimeString('en-US');

                    $('#pickUpTime').text(pickUpTimeVal + ' ' + time);
                }


                $("#pickUpAddress").text(order.sourceLocationName);
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
                if (order.paymentMethod) {

                    if (order.paymentMethod == "stripe") {
                        image = '{{ asset('images/payment/stripe.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'" width="30%" height="30%">';

                    } else if (order.paymentMethod == "xendit") {
                        image = '{{ asset('images/payment/xendit.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'"  width="30%" height="30%">';

                    } else if (order.paymentMethod == "midtrans") {
                        image = '{{ asset('images/payment/midtrans.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'" width="30%" height="30%">';

                    } else if (order.paymentMethod == "orangepay") {
                        image = '{{ asset('images/payment/orangepay.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'" width="30%" height="30%">';

                    } else if (order.paymentMethod == "cod") {
                        image = '{{ asset('images/payment/cashondelivery.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'" width="30%" height="30%">';

                    } else if (order.paymentMethod == "razorpay") {
                        image = '{{ asset('images/payment/razorepay.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'" width="30%" height="30%">';

                    } else if (order.paymentMethod == "paypal") {
                        image = '{{ asset('images/payment/paypal.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'" width="30%" height="30%">';

                    } else if (order.paymentMethod == "payfast") {
                        image = '{{ asset('images/payfast.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'"  width="30%" height="30%">';

                    } else if (order.paymentMethod == "paystack") {
                        image = '{{ asset('images/payment/paystack.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'" width="30%" height="30%">';

                    } else if (order.paymentMethod == "flutterwave") {
                        image = '{{ asset('images/payment/flutter_wave.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'" width="30%" height="30%">';

                    } else if (order.paymentMethod == "mercadoPago" || order.paymentMethod ==
                        "mercado pago" || order.paymentMethod == "mercadopago") {
                        image = '{{ asset('images/payment/marcado_pago.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'" width="30%" height="30%">';

                    } else if (order.paymentMethod == "wallet") {
                        image = '{{ asset('images/payment/emart_wallet.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'" width="30%" height="30%" >';

                    } else if (order.paymentMethod == "paytm") {
                        image = '{{ asset('images/payment/paytm.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'"  width="30%" height="30%">';

                    } else if (order.paymentMethod == "cancelled order payment") {
                        image = '{{ asset('images/payment/cancel_order.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'" width="30%" height="30%">';

                    } else if (order.paymentMethod == "refund amount") {
                        image = '{{ asset('images/payment/refund_amount.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'"  width="30%" height="30%">';
                    } else if (order.paymentMethod == "referral amount") {
                        image = '{{ asset('images/payment/reffral_amount.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" onerror="this.onerror=null;this.src=\'' + place_image +
                            '\'" width="30%" height="30%">';
                    } else {
                        payment_method = order.paymentMethod;
                    }
                }
                $('#payment_method').html(payment_method);



                $('#user_email').html('<a href="mailto:' + order.author.email + '">' + shortEmail(order
                    .author.email) + '</a>');
                $('#user_firstName').text(order.author.firstName);
                $('#user_lastName').text(order.author.lastName);

                if (order.author.phoneNumber.includes('+')) {
                    $('#user_phone').text('+' + EditPhoneNumber(order.author.phoneNumber.slice(1)));
                } else {
                    $('#user_phone').text(EditPhoneNumber(order.author.phoneNumber));
                }

                if (order.driverID != '' && order.driverID != undefined) {
                    driverId = order.driverID;
                }

                if (order.rentalPackageModel && order.rentalPackageModel != '' && order
                    .rentalPackageModel != undefined) {
                    $('#rental_pack_name').text(order.rentalPackageModel.name);
                    let baseFare = 0;
                    if (currencyAtRight) {
                        baseFare = parseFloat(order.rentalPackageModel.baseFare).toFixed(
                            decimal_degits) + "" + currentCurrency;
                    } else {
                        baseFare = currentCurrency + "" + parseFloat(order.rentalPackageModel.baseFare)
                            .toFixed(decimal_degits);
                    }
                    $('#rental_pack_price').text(baseFare);
                    $('#rental_pack_hours').text(order.rentalPackageModel.includedHours + ' ' + 'Hr');
                    $('#rental_pack_km').text(order.rentalPackageModel.includedDistance + ' ' + 'Km');
                }

                fcmToken = order.author.fcmToken;

                customername = order.author.firstName;

                old_order_status = order.status;
                if (order.payment_shared != undefined) {
                    payment_shared = order.payment_shared;
                }
                
                var productstotalHTML = buildRentalTotal(order);
                if (productstotalHTML != '') {
                    append_procucts_total.innerHTML = productstotalHTML;
                }

                orderPreviousStatus = order.status;
                if (order.hasOwnProperty('payment_method')) {
                    orderPaymentMethod = order.paymentMethod;
                }

                $("#order_status option[value='" + order.status + "']").attr("selected", "selected");
                if (order.status == "Order Rejected" || order.status == "Driver Rejected") {
                    $("#order_status").prop("disabled", true);
                }
                var price = 0;

                jQuery("#data-table_processing").hide();
            })

            $(".edit-form-btn").click(async function() {

                var orderStatus = $("#order_status").val();
                if (old_order_status != orderStatus) {

                    database.collection('rental_orders').doc(id).update({
                        'status': orderStatus
                    }).then(async function(result) {
                        if (orderStatus != orderPreviousStatus && payment_shared == false) {
                            if (orderStatus == 'Order Completed') {

                                await database.collection('rental_orders').doc(id).update({
                                    'payment_shared': true
                                }).then(async function(result) {
                                    window.location.href =
                                        '{{ route('rental_orders') }}';
                                });
                            } else {
                                window.location.href = '{{ route('rental_orders') }}';
                            }

                        } else {
                            window.location.href = '{{ route('rental_orders') }}';
                        }
                    });
                } else {
                    window.location.href = '{{ route('rental_orders') }}';

                }
            })

        });

        function buildRentalTotal(snapshotsProducts) {

            var intRegex = /^\d+$/;
            var floatRegex = /^((\d+(\.\d*)?)|((\d*\.)?\d+))$/;

            var adminCommission = snapshotsProducts.adminCommission;
            var adminCommissionType = snapshotsProducts.adminCommissionType;
            var discount = parseFloat(snapshotsProducts.discount || 0);
            var couponCode = snapshotsProducts.couponCode;
            var discountType = snapshotsProducts.discountType;
            var discountLabel = "";
            let subTotal = parseFloat(snapshotsProducts.subTotal || 0);
            let driverRate = parseFloat(snapshotsProducts.driverRate || 0);
            var notes = snapshotsProducts.note;

            var startKm = parseFloat(snapshotsProducts.startKitoMetersReading) || 0;
            var endKm = parseFloat(snapshotsProducts.endKitoMetersReading) || 0;
            var includedDistance = parseFloat(snapshotsProducts?.rentalPackageModel?.includedDistance) || 0;
            var extraKmFare = parseFloat(snapshotsProducts?.rentalPackageModel?.extraKmFare) || 0;
            var extraMinuteFare = parseFloat(snapshotsProducts?.rentalPackageModel?.extraMinuteFare) || 0;
            var totalMinutesUsed = parseFloat(snapshotsProducts.totalMinutesUsed) || 0;
            var includedMinutes = parseFloat(snapshotsProducts?.rentalPackageModel?.includedMinutes) || 0;

            var extraKm = 0;
            var extraKilometerCharge = 0;
            var extraMinutesCharge = 0;

            // Extra Kilometer Calculation
            if (endKm > startKm) {
                let totalKm = endKm - startKm;
                if (totalKm > includedDistance) {
                    totalKm = totalKm - includedDistance;
                    extraKm = totalKm;
                    extraKilometerCharge = totalKm * extraKmFare;
                }
            }
          
            // Convert Firestore timestamps to JS Date
            var startTime = snapshotsProducts.startTime ? snapshotsProducts.startTime.toDate() : null;
            var endTime = snapshotsProducts.endTime ? snapshotsProducts.endTime.toDate() : null;

            var totalMinutesUsed = 0;

            // Total minutes difference
            if (startTime && endTime) {
                totalMinutesUsed = Math.floor((endTime - startTime) / (1000 * 60));
            }

            // Get included hours from rental package and convert to minutes
            var includedHours = parseFloat(snapshotsProducts?.rentalPackageModel?.includedHours) || 0;
            var includedMinutes = includedHours * 60;

            // Extra Minutes Calculation
            var extraMinutesCharge = 0;
            var extraMinutes = 0;

            if (totalMinutesUsed > includedMinutes) {
                extraMinutes = totalMinutesUsed - includedMinutes;
                extraMinutesCharge = extraMinutes * extraMinuteFare;
            }

            // Add extra cost before discount
            var order_subtotal = parseFloat(subTotal) + parseFloat(driverRate) + parseFloat(extraKilometerCharge) + parseFloat(extraMinutesCharge);
            var total_discount = discount;

            let total_tax_amount = 0;
            // Order tax
            let orderTaxable = Math.max(0, order_subtotal - total_discount);
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

            // Extra charges
            let platformFee = parseFloat(snapshotsProducts.platformFee || 0);

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

            // Final price
            let order_total = (order_subtotal - total_discount) + platformFee + total_tax_amount;

            var html = "";
            
            html += '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.sub_total') }}</span></td></tr>';
            html +=
                '<tr class="final-rate"><td class="label">{{ trans('lang.sub_total') }}</td><td class="sub_total" style="color:green">(' +
                formatCurrency(order_subtotal, currencyData)  + ')</td></tr>';

            // Show extra charges in table
            html += '<tr><td class="label">{{ trans('lang.extra_km') }}</td><td ">' + extraKm + ' Km</td></tr>';
            if (extraKilometerCharge > 0) {
                let extraKmDisplay = currencyAtRight ?
                    parseFloat(extraKilometerCharge).toFixed(decimal_degits) + currentCurrency :
                    currentCurrency + parseFloat(extraKilometerCharge).toFixed(decimal_degits);
                html += '<tr><td class="label">{{ trans('lang.extra_distance_charge') }}</td><td style="color:green">+' +
                    extraKmDisplay + '</td></tr>';
            }
            
            html += '<tr><td class="label">{{ trans('lang.extra_minutes') }}</td><td>' + extraMinutes + ' Min</td></tr>';
            if (extraMinutesCharge > 0) {
                let extraMinDisplay = currencyAtRight ?
                    parseFloat(extraMinutesCharge).toFixed(decimal_degits) + currentCurrency :
                    currentCurrency + parseFloat(extraMinutesCharge).toFixed(decimal_degits);
                html += '<tr><td class="label">{{ trans('lang.extra_time_charge') }}</td><td style="color:green">+' +
                    extraMinDisplay + '</td></tr>';
            }

            if (intRegex.test(discount) || floatRegex.test(discount)) {
                html += '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.discount') }}</span></td></tr>';
                discount = parseFloat(discount).toFixed(decimal_degits);
                total_price -= parseFloat(discount);

                if (currencyAtRight) {
                    discount_val = parseFloat(discount).toFixed(decimal_degits) + "" + currentCurrency;
                } else {
                    discount_val = currentCurrency + "" + parseFloat(discount).toFixed(decimal_degits);
                }
                couponCode_html = '';
                if (couponCode) {
                    couponCode_html = '</br><small>{{ trans('lang.coupon_codes') }} :' + couponCode + '</small>';
                }
                html += '<tr><td class="label">{{ trans('lang.discount') }}' + couponCode_html +
                    '</td><td class="discount" style="color:red">(-' + discount_val + ')</td></tr>';
            }

            html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.platform_charge') }}</span></td></tr>';
            html = html +'<tr><td class="label">{{ trans('lang.platform_charge') }}</td><td class="platform_charge " id="greenColor">+' +
                    formatCurrency(platformFee, currencyData) + '</td></tr>';

            html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.tax_calculation') }}</span></td></tr>';
            html = html + renderTaxSection('order', 'Tax on Order Total');
            html = html + renderTaxSection('platform', 'Tax on Platform Fee');
            html = html +'<tr><td class="label"><strong>{{ trans('lang.total_tax') }}</strong></td><td class="total_tax " id="greenColor"><strong>+' +
            formatCurrency(total_tax_amount, currencyData) + '</strong></td></tr>';
            
            html += '<tr><td class="seprater" colspan="2"><hr></td></tr>';
            html +=
                '<tr class="grand-total"><td class="label">{{ trans('lang.total_amount') }}</td><td class="total_price_val">' +
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

            if (notes) {
                html += '<tr><td class="label">{{ trans('lang.notes') }}</td><td class="adminCommission_val">' + notes +
                    '</td></tr>';
            }

            return html;
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
                var review_user_view = '{{ route('users.view', ':id') }}';
                review_user_view = review_user_view.replace(':id', val.CustomerId);
                rating = val.rating;
                reviewhtml = reviewhtml + '<div class="reviews-members py-3 border mb-3"><div class="media">';
                reviewhtml = reviewhtml + '<a href="' + review_user_view + '"><img alt="#" src="' + val.profile +
                    '" onerror="this.onerror=null;this.src=\'' + place_image +
                    '\'" class=" img-circle img-size-32 mr-2" style="width:60px;height:60px"></a>';
                reviewhtml = reviewhtml +
                    '<div class="media-body d-flex"><div class="reviews-members-header"><h6 class="mb-0"><a class="text-dark" href="' +
                    review_user_view + '">' + val.uname +
                    '</a></h6><div class="star-rating"><div class="d-inline-block" style="font-size: 14px;">';
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
                reviewhtml = reviewhtml + '</div></div><div class="reviews-members-body w-100"><p class="mb-2">' +
                    val.comment + '</p></div>';
                reviewhtml += '</div>';
                reviewhtml += '</div>';


            });

            reviewhtml += '</div>';

            return reviewhtml;
        }

        function PrintElem(elem) {
            // Clone the original element
            var elemClone = $('#' + elem).clone();

            // Remove the statusDiv portion from the clone
            elemClone.find('#statusDiv').remove();

            elemClone.printThis({
                debug: false,
                importStyle: true,
                loadCSS: [
                    '<?php echo asset('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>',
                    '<?php echo asset('css/style.css'); ?>',
                    '<?php echo asset('css/colors/blue.css'); ?>',
                    '<?php echo asset('css/icons/font-awesome/css/font-awesome.css'); ?>',
                    '<?php echo asset('assets/plugins/toast-master/css/jquery.toast.css'); ?>',
                ],

            });


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
