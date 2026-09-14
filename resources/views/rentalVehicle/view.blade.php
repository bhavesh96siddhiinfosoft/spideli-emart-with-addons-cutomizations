@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <div class="d-flex top-title-section justify-content-between">
                <div class="d-flex top-title-left align-self-center">
                    <span class="icon mr-3"><img src="{{ asset('images/car.png') }}"></span>
                    <h3 class="mb-0 page-title">{{trans('lang.vehicle_plural')}}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('rentalvehicle') !!}">{{trans('lang.vehicle_plural')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.vehicle_details')}}</li>
            </ol>
        </div>
    </div>
       
    <div class="container-fluid">

        <div class="resttab-sec mb-4">  
            <div class="menu-tab">
                <ul>
                    <li>
                        <a href="{{route('drivers.view',$id)}}"><i class="ri-list-indefinite"></i> {{trans('lang.tab_basic')}}</a>
                    </li>
                    <li class="active">
                        <a href="{{route('drivers.vehicle',$id)}}"><i class="ri-car-line"></i> {{trans('lang.vehicle')}}</a>
                    </li>
                    <li class="service_type_orders">

                    </li>
                    <li>
                        <a href="{{route('driver.payouts',$id)}}"><i class="ri-bank-card-line"></i> {{trans('lang.tab_payouts')}}</a>
                    </li>
                    <li>
                        <a href="{{route('payoutRequests.drivers.view',$id)}}" class="vendor_payout"><i class="ri-refund-line"></i> {{trans('lang.tab_payout_request')}}</a>
                    </li>
                    <li>
                        <a href="{{route('users.walletstransaction',$id)}}" class="wallet_transaction"><i class="ri-wallet-line"></i> {{trans('lang.wallet_transaction')}}</a>
                    </li>
                </ul>
            </div>  
             
        </div>
        <div class="restaurant_info-section">
            <div class="card border">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                    <div class="card-header-title">
                        <h3 class="text-dark-2 mb-0 h4">{{trans('lang.vehicle_details')}}</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="restaurant_info_left">
                                <div id="vehicle_details" class="row"></div>
                            </div>
                        </div>                    
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <a href="{!! route('users') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        </div>
    </div>    
</div>

@endsection

@section('scripts')

<script type="text/javascript">

    var id = "<?php echo $id;?>";
    var database = firebase.firestore();

    var ref = database.collection('users').where("id", "==", id);

    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(function (snap) {
        placeholderImage = snap.data()?.image || '';
    });

    const serviceLabels = {
        "cab-service": "Cab Service",
        "parcel_delivery": "Parcel Delivery Service",
        "rental-service": "Rental Service",
        "delivery-service": "Delivery Service",
        "ecommerce-service": "Ecommerce Service"
    };
    
    $(document).ready(async function () {

        jQuery("#data-table_processing").show();

        ref.get().then(async function (snapshots) {

            if (!snapshots.docs.length) {
                $('.vehicle_detail_div').html(
                    '<h5 class="text-danger text-center font-weight-bold">{{trans("lang.vehicle_info_not_available")}}</h5>'
                );
                jQuery("#data-table_processing").hide();
                return;
            }

            var dirver = snapshots.docs[0].data();

            let vehicleDetails = dirver.vehicleDetails || {};
            let sectionNames = dirver.sectionNames || {};

            let html = "";
            Object.keys(vehicleDetails).forEach((sectionId) => {
                let v = vehicleDetails[sectionId];
                let sectionName = sectionNames[sectionId] || "Unknown Section";
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="vehicle-block mb-3 p-2 border rounded">
                            <h6 class="font-weight-bold text-dark mb-2">${sectionName}</h6>
                            <div><b>{{trans('lang.car_make')}}:</b> ${v.carBrand || "-"}</div>
                            <div><b>{{trans('lang.car_model')}}:</b> ${v.carModel || "-"}</div>
                            <div><b>{{trans('lang.car_number')}}:</b> ${v.carPlateNumber || "-"}</div>
                            <div><b>{{trans('lang.vehicle_type')}}:</b> ${v.vehicleType || "-"}</div>
                        </div>
                    </div>
                `;
            });

            $("#vehicle_details").html(html);

            $(".driver_name").text(dirver.firstName || "");
            $(".email").text(dirver.email || "");
            $(".phone").text(dirver.phoneNumber || "");
            
            var wallet_route = "{{route('users.walletstransaction','id')}}";
            $(".wallet_transaction").attr("href", wallet_route.replace('id', 'driverID='+dirver.id));

            let serviceTypes = dirver.serviceTypes || (dirver.serviceType ? [dirver.serviceType] : []);
            let primaryService = serviceTypes[0] || null;

            if (dirver.companyName && dirver.companyName.trim() !== "") {
                $(".company_details").show();
                $(".company_address").text(dirver.companyAddress || "");
                $(".company_name").text(dirver.companyName || "");
            }

            let driver_image = dirver.profilePictureURL
            ? `<img width="200px" src="${dirver.profilePictureURL}" onerror="this.src='${placeholderImage}'">`
            : `<img width="200px" src="${placeholderImage}">`;

            $(".profile_image").html(driver_image);
            
            if (serviceTypes.includes("cab-service")) {

                var url = "{{route('drivers.rides','driverId')}}";
                url = url.replace('driverId', dirver.id);

                $('.service_type_orders').html(
                    `<a href="${url}"><i class="ri-shopping-bag-line"></i> {{trans('lang.order_plural')}}</a>`
                );

            } else if (serviceTypes.includes("rental-service")) {

                var url = "{{route('rental_orders.driver','id')}}";
                url = url.replace("id", dirver.id);

                $('.service_type_orders').html(
                    `<a href="${url}"><i class="ri-shopping-bag-line"></i> {{trans('lang.order_plural')}}</a>`
                );

            } else if (
                serviceTypes.includes("delivery-service") ||
                serviceTypes.includes("ecommerce-service")
            ) {

                var url = "{{route('orders','id')}}";
                url = url.replace("id", 'driverId=' + dirver.id);

                $('.service_type_orders').html(
                    `<a href="${url}"><i class="ri-shopping-bag-line"></i> {{trans('lang.order_plural')}}</a>`
                );

            } else if (serviceTypes.includes("parcel_delivery")) {

                var url = "{{route('parcel_orders.driver','id')}}";
                url = url.replace("id", dirver.id);

                $('.service_type_orders').html(
                    `<a href="${url}"><i class="ri-shopping-bag-line"></i> {{trans('lang.order_plural')}}</a>`
                );
            }

            jQuery("#data-table_processing").hide();
        })

    })

</script>

@endsection
