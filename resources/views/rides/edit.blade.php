@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.rides')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>

                <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                    <li class="breadcrumb-item"><a href="{{route('drivers.ride',$_GET['eid'])}}">{{trans('lang.order_plural')}}</a>
                    </li>
                <?php } else { ?>
                    <li class="breadcrumb-item"><a href="javascript:window.history.go(-1);">{{trans('lang.rides')}}</a>
                    </li>
                <?php } ?>

                <li class="breadcrumb-item">{{trans('lang.ride_edit')}}</li>
            </ol>
        </div>
    </div>

    <div class="card-body">
    

        <div class="order_detail" id="order_detail">
            <div class="order_detail-top">
                <div class="row">
                    <div class="order_edit-genrl col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-header-title">{{trans('lang.general_details')}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="order_detail-top-box">

                                    <div class="form-group row widt-100 gendetail-col">
                                        <label class="col-12 control-label"><strong>{{trans('lang.date_created')}}
                                                : </strong><span
                                                    id="createdAt"></span></label>
                                    </div>

                                    <div class="form-group row widt-100 gendetail-col">
                                        <label class="col-12 control-label"><strong>{{trans('lang.payment_methods')}}: </strong><span
                                                    id="payment_method"></span></label>
                                    </div>


                                    <div class="form-group row widt-100 gendetail-col">
                                        <label class="col-12 control-label"><strong>{{trans('lang.ridetype')}}:</strong>
                                            <span
                                                    id="rideType"></span></label>
                                    </div>

                                    <div class="form-group row width-100 ">
                                        <label class="col-3 control-label">{{trans('lang.status')}}:</label>
                                        <div class="col-7">
                                            <select id="order_status" class="form-control">
                                                <option value="Order Placed" id="order_placed">{{
                                                    trans('lang.order_placed')}}
                                                </option>
                                                <option value="Order Accepted" id="order_accepted">{{
                                                    trans('lang.order_accepted')}}
                                                </option>
                                                <option value="Order Rejected" id="order_rejected">{{
                                                    trans('lang.order_rejected')}}
                                                </option>
                                                <option value="Driver Pending" id="driver_pending">{{
                                                    trans('lang.driver_pending')}}
                                                </option>
                                                <option value="Driver Rejected" id="driver_rejected">{{
                                                    trans('lang.driver_rejected')}}
                                                </option>
                                                <option value="Order Shipped" id="order_shipped">{{
                                                    trans('lang.order_shipped')}}
                                                </option>
                                                <option value="In Transit" id="in_transit">{{
                                                    trans('lang.in_transit')}}
                                                </option>
                                                <option value="Order Completed" id="order_completed">{{
                                                    trans('lang.order_completed')}}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row width-100">
                                        <label class="col-3 control-label"></label>
                                        <div class="col-7 text-right">
                                            <button type="button" class="btn btn-primary edit-form-btn"><i
                                                        class="fa fa-save"></i>
                                                {{trans('lang.update')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="order-items-list mt-4 ">
                            <div class="card">
                                <div class="card-body">
                                    <table cellpadding="0" cellspacing="0"
                                           class="table table-striped table-valign-middle">

                                        <thead>
                                        <tr>
                                            <th>{{trans('lang.from')}}</th>
                                            <th>{{trans('lang.to')}}</th>
                                            <th>{{trans('lang.price')}}</th>
                                            <th>{{trans('lang.total')}}</th>
                                        </tr>

                                        </thead>

                                        <tbody id="order_products">

                                        </tbody>

                                    </table>

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
                            </div>

                        </div>

                    </div>

                    <div class="order_edit-genrl col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-header-title">{{ trans('lang.billing_details')}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="address order_detail-top-box">
                                    <div class="address order_detail-top-box">
                                        <div class="form-group row widt-100 gendetail-col">
                                            <label class="col-12 control-label"><strong>{{trans('lang.name')}}
                                                    : </strong><span id="billing_name"></span></label>

                                        </div>
                                        <div class="form-group row widt-100 gendetail-col">
                                            <label class="col-12 control-label"><strong>{{trans('lang.address')}}
                                                    : </strong><span id="billing_line1"></span> <span
                                                        id="billing_line2"></span><span
                                                        id="billing_country"></span></label>

                                        </div>

                                        <div class="form-group row widt-100 gendetail-col">
                                            <label class="col-12 control-label"><strong>{{trans('lang.email_address')}}
                                                    : </strong><span id="billing_email"></span></label>

                                        </div>

                                        <p><strong>{{trans('lang.phone')}}:</strong>
                                            <span id="billing_phone"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="order_addre-edit col-md-4 driver_details_hide">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-header-title">{{ trans('lang.driver_detail')}}</h4>
                                </div>
                                <div class="card-body">
                                    <div class="address order_detail-top-box">
                                        <p>
                                            <span id="driver_firstName"></span> <span id="driver_lastName"></span><br>
                                        </p>
                                        <p><strong>{{trans('lang.email_address')}}:</strong>
                                            <span id="driver_email"></span>
                                        </p>
                                        <p><strong>{{trans('lang.phone')}}:</strong>
                                            <span id="driver_phone"></span>
                                        </p>
                                        <p><strong>{{trans('lang.car_name')}}:</strong>
                                            <span id="driver_carName"></span>
                                        </p>
                                        <p><strong>{{trans('lang.car_number')}}:</strong>
                                            <span id="driver_carNumber"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="resturant-detail mt-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-header-title">{{trans('lang.driver_detail')}}</h4>
                                </div>

                                <div class="card-body">
                                    <a href="" class="row redirecttopage" id="resturant-view">
                                        <div class="col-4">
                                            <img src="" class="resturant-img rounded-circle" alt="driver" width="70px"
                                                 height="70px">
                                        </div>
                                        <div class="col-8">
                                            <h4 class="vendor-title"></h4>
                                        </div>
                                    </a>

                                    <h5 class="contact-info">{{trans('lang.contact_info')}}:</h5>

                                    <p><strong id="vendor_phone1">{{trans('lang.phone')}}:</strong>
                                        <span id="vendor_phone"></span>
                                    </p>
                                    <h5 class="contact-info">{{trans('lang.car_info')}}:</h5>
                                    {{-- <a href="" class="row redirecttopage" id="car-view">
                                        <div class="col-4">
                                            <img src="" class="car-img rounded-circle" alt="car" width="70px"
                                                 height="70px">
                                        </div>

                                    </a> --}}
                                    <br>
                                    <p><strong id="driver_carName1" style="width:auto !important;">{{trans('lang.car_name')}}:</strong>
                                        <span id="driver_carName"></span>
                                    </p> <br>
                                    <p><strong id="driver_carNumber1" style="width:auto !important;">{{trans('lang.car_number')}}:</strong>
                                        <span id="driver_carNumber"></span>
                                    </p> <br>
                                    <p><strong id="driver_car_make1" style="width:auto !important;">{{trans('lang.car_make')}}:</strong>
                                        <span id="driver_car_make"></span>
                                    </p>
                                    <p><strong id="driver_car_type1" style="width:auto !important;">{{trans('lang.vehicle_type')}}:</strong>
                                        <span id="driver_car_type"></span>
                                    </p>

                                </div>
                            </div>
                        </div>

                    </div>

                </div>


            </div>

        </div>


        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i>
                {{trans('lang.save')}}
            </button>

            <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                <a href="{{route('vendors.orders',$_GET['eid'])}}" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
            <?php } else { ?>
                <a href="javascript:window.history.go(-1);" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
            <?php } ?>

        </div>

    </div>


</div>
</div>

@endsection

@section('style')

@section('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.js"></script>

<script type="text/javascript">

    var adminCommission = 0;
    var id_rendom = "<?php echo uniqid(); ?>";
    var id = "<?php echo $id; ?>";
    
    var driverId = '';
    var fcmToken = '';
    var old_order_status = '';
    var payment_shared = false;
    var deliveryChargeVal = 0;
    
    var vendorname = '';
    var database = firebase.firestore();
    var ref = database.collection('rides').where("id", "==", id);
    var append_procucts_list = '';
    var append_procucts_total = '';

    var tip_amount = 0;
    var total_price = 0;

    var currentCurrency = '';
    var currencyAtRight = false;
    var refCurrency = regionCurrencyRef();
    var orderPreviousStatus = '';
    
    var manfcmTokenVendor = '';
    var manname = '';
    var decimal_degits = 0;
    var currencyData = '';
    refCurrency.get().then(async function (snapshots) {
        currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;
        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;
    });

    var geoFirestore = new GeoFirestore(database);
    var place_image = '';
    var ref_place = database.collection('settings').doc("placeHolderImage");
    ref_place.get().then(async function (snapshots) {
        var placeHolderImage = snapshots.data();
        place_image = placeHolderImage.image;
    });

    let taxBreakdownGrouped = {
        order: {},
        platform: {}
    };
    
    $(document).ready(function () {

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
        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        jQuery("#data-table_processing").show();

        ref.get().then(async function (snapshots) {

            var ride = snapshots.docs[0].data();

            /* A ride outside the region the admin is working in must not be
             * reachable by editing the url, the same guard the vendor and
             * store screens carry. */
            if (!isInActiveRegion(ride)) {
                window.location.href = '{{ route("rides") }}';
                return;
            }

            append_procucts_list = document.getElementById('order_products');
            append_procucts_list.innerHTML = '';

            append_procucts_total = document.getElementById('order_products_total');
            append_procucts_total.innerHTML = '';


            $("#billing_name").text(ride.author.firstName + " " + ride.author.lastName);


            var billingAddressstring = '';

            $("#trackng_number").text(id);
            
            if (ride.author.hasOwnProperty('phoneNumber')) {
                if(ride.author.phoneNumber.includes('+')){
                    $("#billing_phone").text('+' + EditPhoneNumber(ride.author.phoneNumber.slice(1)));
                }else{
                    $("#billing_phone").text(EditPhoneNumber(ride.author.phoneNumber));
                }
            }

            let localityText = "No address available";

            if (ride.author && ride.author.shippingAddress) {
                const addresses = ride.author.shippingAddress;

                if (Array.isArray(addresses)) {
                    const defaultAddr = addresses.find(addr => addr.isDefault === true);
                    const addrToUse = defaultAddr || addresses[0];
                    localityText = (addrToUse && addrToUse.locality) ? addrToUse.locality : "No locality";
                } else if (addresses && addresses.locality) {
                    localityText = addresses.locality;
                }
            }

            $("#billing_line2").text(localityText);

            if (ride.author && ride.author.shippingAddress) {
                if (ride.author.shippingAddress.hasOwnProperty('country')) {
                    $("#billing_country").text(ride.author.shippingAddress.country);
                }
            }

            if (ride.author.hasOwnProperty('email')) {
                $("#billing_email").html('<a href="mailto:' + ride.author.email + '">' + shortEmail(ride.author.email) + '</a>');
            }

            if (ride.createdAt) {
                var date1 = ride.createdAt.toDate().toDateString();
                var date = new Date(date1);
                var dd = String(date.getDate()).padStart(2, '0');
                var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
                var yyyy = date.getFullYear();
                var createdAt_val = yyyy + '-' + mm + '-' + dd;
                var time = ride.createdAt.toDate().toLocaleTimeString('en-US');

                $('#createdAt').text(createdAt_val + ' ' + time);
            }

            var paymentMethod = '';
            if (ride.paymentMethod) {

                if (ride.paymentMethod == "stripe") {
                    image = '{{asset("images/payment/stripe.png")}}';
                    paymentMethod = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (ride.paymentMethod == "cod") {
                    image = '{{asset("images/payment/cashondelivery.png")}}';
                    paymentMethod = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (ride.paymentMethod == "razorpay") {
                    image = '{{asset("images/payment/razorepay.png")}}';
                    paymentMethod = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (ride.paymentMethod == "paypal") {
                    image = '{{asset("images/payment/paypal.png")}}';
                    paymentMethod = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"  width="30%" height="30%">';

                } else if (ride.paymentMethod == "payfast") {
                    image = '{{asset("images/payfast.png")}}';
                    paymentMethod = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (ride.paymentMethod == "paystack") {
                    image = '{{asset("images/payment/paystack.png")}}';
                    paymentMethod = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"  width="30%" height="30%">';

                } else if (ride.paymentMethod == "flutterwave") {
                    image = '{{asset("images/payment/flutter_wave.png")}}';
                    paymentMethod = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (ride.paymentMethod == "mercadoPago" || ride.paymentMethod == "mercado pago" || ride.paymentMethod == "mercadopago") {
                    image = '{{asset("images/payment/marcado_pago.png")}}';
                    paymentMethod = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (ride.paymentMethod == "wallet") {
                    image = '{{asset("images/payment/emart_wallet.png")}}';
                    paymentMethod = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%" >';

                } else if (ride.paymentMethod == "paytm") {
                    image = '{{asset("images/payment/paytm.png")}}';
                    paymentMethod = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (ride.paymentMethod == "cancelled order payment") {
                    image = '{{asset("images/payment/cancel_order.png")}}';
                    paymentMethod = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (ride.paymentMethod == "refund amount") {
                    image = '{{asset("images/payment/refund_amount.png")}}';
                    paymentMethod = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';
                } else if (ride.paymentMethod == "referral amount") {
                    image = '{{asset("images/payment/reffral_amount.png")}}';
                    paymentMethod = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';
                } else {
                    paymentMethod = ride.paymentMethod;
                }
            }

            $('#payment_method').html('<span>' + paymentMethod + '</span>');

            if (ride.hasOwnProperty('rideType')) {
                $('#rideType').text(ride.rideType);
            } 

            if ((ride.driver != '' && ride.driver != undefined) && (ride.takeAway)) {
                $('#driver_carName').text(ride.driver.carName);
                $('#driver_carNumber').text(ride.driver.carNumber);
                $('#driver_email').html('<a href="mailto:' + ride.driver.email + '">' + shortEmail(ride.driver.email) + '</a>');
                $('#driver_firstName').text(ride.driver.firstName);
                $('#driver_lastName').text(ride.driver.lastName);
                if(ride.driver.phoneNumber.includes('+')){
                    $('#driver_phone').text('+' + EditPhoneNumber(ride.driver.phoneNumber.slice(1)));
                }else{
                    $('#driver_phone').text(EditPhoneNumber(ride.driver.phoneNumber));
                }
            } else {
                $('.order_edit-genrl').removeClass('col-md-4').addClass('col-md-5');
                $('.order_addre-edit').removeClass('col-md-4').addClass('col-md-7');
                $('.driver_details_hide').empty();
            }

            if (ride.driverID != '' && ride.driverID != undefined) {
                driverId = ride.driverID;
            }
            if (ride.vendor && ride.vendor.author != '' && ride.vendor.author != undefined) {
                vendorAuthor = ride.vendor.author;
            }

            fcmToken = ride.author.fcmToken;
            if (ride.driver != undefined) {
                drivername = ride.driver.firstName;
            } else {
                drivername = '';
            }

            customername = ride.author.firstName;

            driverID = ride.driverID;
            old_order_status = ride.status;
            if (ride.payment_shared != undefined) {
                payment_shared = ride.payment_shared;
            }
            var productsListHTML = buildHTMLProductsList(ride);
            var productstotalHTML = buildHTMLProductstotal(ride);

            if (productsListHTML != '') {
                append_procucts_list.innerHTML = productsListHTML;
            }

            if (productstotalHTML != '') {
                append_procucts_total.innerHTML = productstotalHTML;
            }

            orderPreviousStatus = ride.status;
            if (ride.hasOwnProperty('paymentMethod')) {
                orderPaymentMethod = ride.paymentMethod;
            }

            $("#order_status option[value='" + ride.status + "']").attr("selected", "selected");
            if (ride.status == "Order Rejected" || ride.status == "Driver Rejected") {
                $("#order_status").prop("disabled", true);
            }
            var price = 0;

            $('.resturant-img').attr('src', place_image);
            $('.car-img').attr('src', place_image);
           
            var driverId = ride.driver?.id || '';

            if (driverId != '' && driverId != undefined) {

                var driver = database.collection('users').where("id", "==", driverId);

                driver.get().then(async function (snapshotsnew) {
                    if (!snapshotsnew.empty) {
                        var driverdata = snapshotsnew.docs[0].data();


                        if (driverdata.id) {
                            var route_view = '{{route("drivers.view",":id")}}';
                            route_view = route_view.replace(':id', driverdata.id);

                            $('#resturant-view').attr('data-url', route_view);
                        }
                        if (driverdata.profilePictureURL) {
                            $('.resturant-img').attr('src', driverdata.profilePictureURL);
                        } else {
                            $('.resturant-img').attr('src', place_image);
                        }
                        if (driverdata.firstName) {
                            $('.vendor-title').html(driverdata.firstName + ' ' + driverdata.lastName);
                        }

                        if (driverdata.email) {
                            $('#vendor_email').html(shortEmail(driverdata.email));
                        }
                        if (driverdata.phoneNumber) {
                            if(driverdata.phoneNumber.includes('+')){
                                $('#vendor_phone').text('+' + EditPhoneNumber(driverdata.phoneNumber.slice(1)));
                            }else{
                                $('#vendor_phone').text(EditPhoneNumber(driverdata.phoneNumber));
                            }
                        }
                        if (driverdata.id) {
                            var route_view = '{{route("drivers.view",":id")}}';
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
                        if(driverdata.vehicleType){
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

            }else{
                $('.resturant-img').hide();
                $('.vendor-title').text("{{trans('lang.no_driver_assigned')}}");
                $('#vendor_email').hide();
                $('#vendor_phone').hide();
                $('#resturant-car').hide();
                $('#driver_carName').hide();
                $('#driver_carNumber').hide();
                $('#driver_car_make').hide();
                $('#driver_car_type').hide();
                $('#vendor_phone1').hide();
            }

            ref.get().then(async function (snapshotsride) {
                snapshotsride.docs.forEach((listval) => {
                    database.collection('rides').where('id', '==', listval.id).where("status", "in", ["Order Completed"]).get().then(async function (orderSnapshots) {
                        var count_order_complete = orderSnapshots.docs.length;
                    });
                });
            });

            jQuery("#data-table_processing").hide();
        })


        $(".edit-form-btn").click(async function () {

            var clientName = $(".client_name").val();
            var orderStatus = $("#order_status").val();
            if (old_order_status != orderStatus) {
                database.collection('rides').doc(id).update({'status': orderStatus}).then(async function (result) {

                    if (orderStatus == "Order Completed") {
                        manname = customername;
                    } else {
                        manfcmTokenRide = fcmToken;
                        manname = drivername;
                    }
                    if (orderStatus != orderPreviousStatus && payment_shared == false) {

                        if (orderStatus == 'Order Completed') {

                            driverAmount = parseFloat(deliveryChargeVal) - parseFloat(tip_amount);
                            var vendor = database.collection('users').where("driverID", "==", driverID);
                            var vendorWallet = 0;

                            if (driverId && driverAmount) {
                                var driver = database.collection('users').where("id", "==", driverId);
                                await driver.get().then(async function (snapshotsdriver) {
                                    var driverdata = snapshotsdriver.docs[0].data();
                                    if (driverdata) {
                                        if (isNaN(driverdata.wallet_amount) || driverdata.wallet_amount == undefined) {
                                            driverWallet = 0;
                                        } else {
                                            driverWallet = driverdata.wallet_amount;
                                        }
                                        if (orderPaymentMethod == 'cod') {
                                            driverWallet = driverWallet - parseFloat(total_price) - parseFloat(driverAmount);
                                        } else {
                                            driverWallet = driverWallet + driverAmount;
                                        }
                                        if (!isNaN(vendorWallet)) {
                                            await database.collection('users').doc(driverdata.id).update({
                                                'wallet_amount': driverWallet
                                            });
                                        }
                                    }
                                })
                            }
                            await database.collection('rides').doc(id).update({
                                'payment_shared': true
                            });
                        }

                        await $.ajax({
                            type: 'POST',
                            url: "<?php echo route('order-status-notification'); ?>",
                            data: {
                                _token: '<?php echo csrf_token() ?>',
                                'fcm': manfcmTokenVendor,
                                'drivername': manname,
                                'orderStatus': orderStatus
                            },
                            success: function (data) {
                            }
                        });
                    }

                    await $.ajax({
                        type: 'POST',
                        url: "<?php echo route('order-status-notification'); ?>",
                        data: {
                            _token: '<?php echo csrf_token() ?>',
                            'fcm': fcmToken,
                            'drivername': drivername,
                            'orderStatus': orderStatus
                        },
                        success: function (data) {
                            <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                            window.location.href = "{{ route('driver.ride',$_GET['eid']) }}";
                            <?php } else { ?>
                            window.location.href = '{{ route("rides")}}';
                            <?php } ?>
                        }
                    });

                });
            }
        })
    })

    function buildHTMLProductsList(snapshots) {
        var html = '';
        html = html + '<tr>';
        if (snapshots.size) {
            html = html + '<div class="type"><span>{{trans("lang.type")}} :</span><span class="ext-size">' + snapshots.size + '</span></div>';
        }
        let order_subtotal = parseFloat(snapshots.subTotal || 0);
        html = html + '</div></div></td>';
        html = html + '<td>' + snapshots.sourceLocationName + '</td><td>' + snapshots.destinationLocationName + '</td><td>' + formatCurrency(order_subtotal, currencyData) + '</td><td>  ' + formatCurrency(order_subtotal, currencyData) + '</td>';
        html = html + '</tr>';
        return html;
    }

    function buildHTMLProductstotal(snapshotsProducts) {
        
        var html = '';

        let adminCommission =  parseFloat(snapshotsProducts.adminCommission || 0);;
        let adminCommissionType = snapshotsProducts.adminCommissionType;
        let order_subtotal = parseFloat(snapshotsProducts.subTotal || 0);
            tip_amount = parseFloat(snapshotsProducts.tip_amount || 0);

        let notes = snapshotsProducts.notes;

        let total_discount = 0;
        let total_tax_amount = 0;

        // Discount
        let discount = parseFloat(snapshotsProducts.discount || 0);
        total_discount = isNaN(discount) ? 0 : discount;

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
        let order_total = (order_subtotal - total_discount) + tip_amount + platformFee + total_tax_amount;
            total_price = order_total;

        html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.sub_total') }}</span></td></tr>';
        html = html +
            '<tr class="final-rate"><td class="label">{{ trans('lang.sub_total') }}</td><td class="sub_total" style="color:green">(' +
            formatCurrency(order_subtotal, currencyData) + ')</td></tr>';

        html = html +
            '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.discount') }}</span></td></tr>';
        
        let couponCode_html = '';
        if (discount > 0) {
            let discountLabel = "(" + snapshotsProducts.couponCode + "%)";
            couponCode_html = '</br><small>{{ trans('lang.coupon_codes') }} :' + discountLabel + '</small>';
        }
        html = html + '<tr><td class="label">{{ trans('lang.discount') }}' + couponCode_html +
            '</td><td class="discount text-danger">(-' + formatCurrency(total_discount, currencyData) + ')</td></tr>';

        html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.tip') }}</span></td></tr>';
        html = html +'<tr><td class="label">{{ trans('lang.tip_amount') }}</td><td class="tip_amount_val">+' +
                formatCurrency(tip_amount, currencyData) + '</td></tr>';

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

        if (notes) {
            html = html + '<tr><td class="label">{{trans("lang.notes")}}</td><td class="adminCommission_val">' + notes + '</td></tr>';
        }

        return html;
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