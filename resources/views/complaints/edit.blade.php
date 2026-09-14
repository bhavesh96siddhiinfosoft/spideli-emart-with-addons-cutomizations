@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">

        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.complaints')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>

                <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                    <li class="breadcrumb-item"><a href="{{route('drivers.ride',$_GET['eid'])}}">{{trans('lang.order_plural')}}</a>
                    </li>
                <?php } else { ?>
                    <li class="breadcrumb-item"><a href="{!! route('complaints') !!}">{{trans('lang.complaints')}}</a>
                    </li>
                <?php } ?>

                <li class="breadcrumb-item">{{trans('lang.edit_complaints')}}</li>
            </ol>
        </div>
    </div>

    <div class="card-body">

        <div class="order_detail" id="order_detail">
            <div class="order_detail-top">
                <div class="row">
                    <div class="order_edit-genrl col-md-4">

                        <h3>{{trans('lang.general_details')}}</h3>
                        <div class="order_detail-top-box">

                            <div class="form-group row widt-100 gendetail-col">
                                <label class="col-12 control-label"><strong>{{trans('lang.date_created')}}
                                        : </strong><span id="createdAt"></span></label>
                            </div>

                            <div class="form-group row widt-100 gendetail-col payment_method">
                                <label class="col-12 control-label"><strong>{{trans('lang.item_title')}}: </strong><span
                                            id="title"></span></label>
                            </div>

                            <div class="form-group row widt-100 gendetail-col">
                                <label class="col-12 control-label"><strong>{{trans('lang.vendor_description')}}:</strong>
                                    <span id="description"></span></label>
                            </div>
                            <div class="form-group row widt-100 gendetail-col">
                                <label class="col-12 control-label"><strong>{{trans('lang.rider_name')}}:</strong> <span
                                            id="rider"></span></label>
                            </div>
                            <div class="form-group row width-100 ">
                                <label class="col-3 control-label">{{trans('lang.status')}}:</label>
                                <div class="col-7">
                                    <select id="order_status" class="form-control">
                                        <option value="Initiated" id="Initiated">{{ trans('lang.initiated')}}</option>
                                        <option value="Resolved" id="Resolved">{{ trans('lang.resolved')}}</option>
                                        <option value="Under Investigation" id="Under_investigation">{{
                                            trans('lang.Under_investigation')}}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label"></label>
                                <div class="col-7 text-right">
                                    <button type="button" class="btn btn-primary edit-form-btn"><i
                                                class="fa fa-save"></i> {{trans('lang.update')}}
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="order_addre-edit col-md-4 ">
                        <h3>{{ trans('lang.driver_detail')}}</h3>

                        <div class="address order_detail-top-box">
                            <a href="#" class="row redirecttopage" id="resturant-view">
                                <div class="col-4">
                                </div>

                            </a>
                            <p>
                                <span class="vendor-title" id="driver_firstName"></span> <br>
                            </p>
                            <p><strong>{{trans('lang.email_address')}}:</strong>
                                <span id="driver_email"></span>
                            </p>
                            <p><strong>{{trans('lang.phone')}}:</strong>
                                <span id="driver_phone"></span>
                            </p>

                        </div>
                    </div>
                    <div class="order_addre-edit col-md-4 ">
                        <h3>{{ trans('lang.customer_details')}}</h3>

                        <div class="address order_detail-top-box">
                            <a href="#" class="row redirecttopage" id="resturant-view">
                                <div class="col-4">
                                </div>

                            </a>
                            <p>
                                <span class="vendor-title-customer" id="customer_firstName"></span> <br>
                            </p>
                            <p><strong>{{trans('lang.email_address')}}:</strong>
                                <span id="customer_email"></span>
                            </p>
                            <p><strong>{{trans('lang.phone')}}:</strong>
                                <span id="customer_phone"></span>
                            </p>

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

var adminCommission = 0;
var id_rendom = "<?php echo uniqid();?>";
var id = "<?php echo $id;?>";
var driverId = '';
var fcmToken = '';
var customerName = '';
var old_order_status = '';
var payment_shared = false;
var deliveryChargeVal = 0;
var tip_amount_val = 0;
var tip_amount = 0;
var vendorname = '';
var database = firebase.firestore();
var ref = database.collection('complaints').where("id", "==", id);
var append_procucts_list = '';
var append_procucts_total = '';
var total_price = 0;
var currentCurrency = '';
var currencyAtRight = false;
var refCurrency = regionCurrencyRef();
var orderPreviousStatus = '';
var orderTakeAwayOption = false;
var manfcmTokenVendor = '';
var manname = '';
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

$(document.body).on('click', '.redirecttopage', function () {
    var url = $(this).attr('data-url');
    window.location.href = url;
});

$(document).ready(function () {

    var alovelaceDocumentRef = database.collection('vendor_orders').doc();
    if (alovelaceDocumentRef.id) {
        id_rendom = alovelaceDocumentRef.id;
    }

    jQuery("#data-table_processing").show();

    ref.get().then(async function (snapshots) {
        var ride = snapshots.docs[0].data();

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

        if (ride.title) {
            $('#title').text(ride.title);
        }
        if (ride.description) {
            $('#description').text(ride.description);
        }
        if (ride.riderName) {
            $('#rider').text(ride.riderName);
        }
        driverID = ride.driverId;
        old_order_status = ride.status;

        orderPreviousStatus = ride.status;
        if (ride.status) {
            orderPaymentMethod = ride.status;
        }

        $("#order_status option[value='" + ride.status + "']").attr("selected", "selected");

        var price = 0;

        var customerId = ride.customerId;

        $('#customer_firstName').text(ride.customerName);
        $('#driver_firstName').text(ride.driverName);
        customerName = ride.customerName;
        driverName = ride.driverName;

        var user = await database.collection('users').where("id", "==", customerId).get().then(async function (usersnapshots) {
            if(!usersnapshots.empty){
                var userData = usersnapshots.docs[0].data();

                if (userData.profilePictureURL) {
                    $('.resturant-img-customer').attr('src', userData.profilePictureURL);
                } else {
                    $('.resturant-img-customer').attr('src', place_image);
                }

                if (userData.email) {
                    $('#customer_email').html(userData.email);
                }
                if (userData.phoneNumber) {
                    $('#customer_phone').text(userData.phoneNumber);
                }

                fcmToken = userData.fcmToken;
            } 
            else {              

                $('.resturant-img-customer').attr('src', place_image);
                $('.vendor-title-customer').text(customerName || '{{trans("lang.unknown_user")}}');

                $('#customer_email').text('N/A');
                $('#customer_phone').text('N/A');
            }
        });

        if (ride.driverId) {
            var driver = database.collection('users').where("id", "==", ride.driverId);
            driver.get().then(async function (snapshotsnew) {
                if(!snapshotsnew.empty){
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
                        $('.vendor-title').text(driverdata.firstName + ' ' + driverdata.lastName);
                    }

                    if (driverdata.email) {
                        $('#driver_email').html(driverdata.email);
                    }
                    if (driverdata.phoneNumber) {
                        $('#driver_phone').text(driverdata.phoneNumber);
                    }
                }else {              

                    $('.resturant-img').attr('src', place_image);
                    $('.vendor-title').text(driverName || '{{trans("lang.unknown_user")}}');

                    $('#driver_email').text('N/A');
                    $('#driver_phone').text('N/A');
                }
            });

        }
        if (ride.riderId) {
           
            var driver = database.collection('users').where("id", '==', ride.riderId);
            driver.get().then(async function (snapshotsnew) {
                if(!snapshotsnew.empty){
                var driverdata = snapshotsnew.docs[0].data();


                if (driverdata.profilePictureURL) {
                    $('.resturant-img-customer').attr('src', driverdata.profilePictureURL);
                } else {
                    $('.resturant-img-customer').attr('src', place_image);
                }
                if (driverdata.firstName) {
                    $('.vendor-title-customer').text(driverdata.firstName + ' ' + driverdata.lastName);
                }else{
                    $('.vendor-title-customer').text(' ');
                }

                if (driverdata.email) {
                    $('#customer_email').html(driverdata.email);
                }else{
                    $('#customer_email').text(' ');
                }
                if (driverdata.phoneNumber) {
                    $('#customer_phone').text(driverdata.phoneNumber);
                }else{
                    $('#customer_phone').text(' ');
                }
            }
            });

        }

        jQuery("#data-table_processing").hide();
    })

})

$(".edit-form-btn").click(async function () {

    var orderStatus = $("#order_status").val();
    if (old_order_status != orderStatus) {
        jQuery("#data-table_processing").show();

        database.collection('complaints').doc(id).update({'status': orderStatus}).then(async function (result) {

            if (orderStatus != 'Initiated') {

                await $.ajax({
                    type: 'POST',
                    url: "<?php echo route('complaint_notification'); ?>",
                    data: {
                        _token: '<?php echo csrf_token() ?>',
                        'fcm': fcmToken,
                        'orderStatus': orderStatus
                    },
                    success: function (data) {

                        window.location.href = '{{ route("complaints")}}';

                    }
                });
            }

             jQuery("#data-table_processing").hide();

             window.location.reload();

        });
    }

})

</script>

@endsection