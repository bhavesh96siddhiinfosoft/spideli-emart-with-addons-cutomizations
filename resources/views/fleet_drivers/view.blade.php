@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <div class="d-flex top-title-section justify-content-between">
                <div class="d-flex top-title-left align-self-center">
                    <span class="icon mr-3"><img src="{{ asset('images/driver.png') }}"></span>
                    <h3 class="mb-0 page-title">{{trans('lang.fleet_drivers')}} - <span class="driver_name"></span></h3>
                </div>
            </div>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('fleet.drivers') !!}">{{trans('lang.fleet_drivers')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.driver_details')}}</li>
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
                            
                        </div>
                    </div>
                </div>
            </div> 
        </div>
        <div class="resttab-sec mb-4">  
            <div class="menu-tab">
                <ul>
                    <li class="active">
                        <a href="{{route('drivers.view',$id)}}" class="basic"><i class="ri-list-indefinite"></i> {{trans('lang.tab_basic')}}</a>
                    </li>
                    <li class="vehicle_tab" style="display:none">
                        <a href="{{route('drivers.vehicle',$id)}}" class="vehicle"><i class="ri-car-line"></i> {{trans('lang.vehicle')}}</a>
                    </li>
                    <li class="service_type_orders">
                    </li>
                    
                </ul>
            </div>  
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-box-with-icon bg--1">
                        <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="card-box-with-content">
                            <h4 class="text-dark-2 mb-1 h4 total_orders" id="total_orders">00</h4>
                            <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_rides')}}</p>
                        </div>
                            <span class="box-icon ab"><img src="{{ asset('images/total_rides.png') }}"></span>
                        </div>
                    </div>
                </div>
                
            </div>   
        </div>
        <div class="restaurant_info-section">
          <div class="row">
            <div class="col-md-6">
                <div class="card border h-100">
                    <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                        <div class="card-header-title">
                            <h3 class="text-dark-2 mb-0 h4">{{trans('lang.driver_details')}}
                                <i class="mdi mdi-verified verified-icon verified-icon-heading ml-2" 
       data-toggle="tooltip" data-bs-original-title="Verified" 
       style="display:none;color: green;"></i>
                            </h3>
                        </div>
                    </div>
            <div class="card-body">
                        <div class="restaurant_info_left">
                            <div class="d-flex mb-1">
                                <div class="sis-img profile_image" id="profile_image">
                                </div>
                                <div class="sis-content pl-4">
                                    <ul class="p-0 info-list mb-0">                                    
                                        <li class="d-flex align-items-center mb-2">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.first_name')}}</label>
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
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.service_type')}}</label>
                                            <span class="service_type"> </span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2 mr-1">
                                            <label class="mb-0 font-wi font-semibold text-dark-2">{{trans('lang.zone_name')}}</label>
                                            <span class="zone_name"> </span>
                                        </li>
                                    </ul>
                                </div>  
                            </div>                            
                        </div>
                </div>
            </div>
            </div>
           <div class="col-md-6">
                <div class="card border h-100">
                    <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3">
                        <div class="card-header-title">
                            <h3 class="text-dark-2 mb-0 h4">{{trans('lang.bankdetails')}}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="noBankData" class="text-muted font-weight-bold py-3" style="display:none;">
                            {{trans("lang.bank_details_not_found")}}
                        </div>
                        <div class="restaurant_info_left" id="bankDetailsBox" style="display:none;">
                            <div class="d-flex mb-1">                              
                                <div class="sis-content">
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
        <!-- Bank detail -->
        <div class="restaurant_info-section">
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <a href="{!! route('drivers') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
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
<div class="modal fade"id="imagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered location_modal modal-sm">
        <div class="modal-content">
            <div class="modal-header">
<h4 class="modal-title text-dark font-medium" id="modalImageTitle"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
               <img id="previewImage" src="" alt="Image Preview" style="max-width: 100%; height: auto;">
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
    <script type="text/javascript">
        var id = "{{$id}}";  
        var serviceType = getCookie('service_type');    
        
        var database = firebase.firestore();
        var ref = database.collection('users').where("id", "==", id);
        var photo = "";
        var vendorOwnerId = "";
        var vendorOwnerOnline = false;
        
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
            
            if(serviceType !== 'delivery-service' && serviceType !== 'parcel_delivery'){
                $('.vehicle_tab').show();
            }else{
                $('.vehicle_tab').hide();
            }
            await email_templates.get().then(async function (snapshots) {
                emailTemplatesData = snapshots.docs[0].data();
            });
            
            ref.get().then(async function (snapshots) {

                if(snapshots.docs.length>0){

                    var dirver = snapshots.docs[0].data();

                    /* A driver outside the region the admin is working in must
                     * not be reachable by editing the url. */
                    if (!isInActiveRegion(dirver)) {
                        window.location.href = '{{ route('fleet.drivers') }}';
                        return;
                    }

                    if (dirver.isDocumentVerify === true) {
                        $('.verified-icon-heading').show();
                    } else {
                        $('.verified-icon-heading').hide();
                    }
                
                    $(".driver_name").text(dirver.firstName);
                    $(".email").text(shortEmail(dirver.email));
                    if(dirver.phoneNumber.includes('+')){
                        $(".phone").text('+' + EditPhoneNumber(dirver.phoneNumber.slice(1)));
                    }else{
                        $(".phone").text(EditPhoneNumber(dirver.phoneNumber));
                    }
                    var wallet_route = "{{route('users.walletstransaction','id')}}";
                    $(".wallet_transaction").attr("href", wallet_route.replace('id', 'driverID='+dirver.id));


                    let serviceTypes = dirver.serviceTypes || (dirver.serviceType ? [dirver.serviceType] : []);
                    let serviceTypeText = serviceTypes.map(type => serviceLabels[type] || type).join(", ");
                    $(".service_type").text(serviceTypeText);

                    if (serviceTypes) {

                        if (serviceTypes.includes("cab-service") && serviceType == "cab-service") {

                            var url = "{{route('drivers.rides','driverId')}}";
                            url = url.replace('driverId', dirver.id);
                            $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i> {{trans('lang.order_plural')}}</a>');
                            await database.collection('rides').where('driverId', '==', dirver.id).get().then(async function (orderSnapshots) {
                                $('.total_orders').html(orderSnapshots.docs.length);
                            });

                        }else if (serviceTypes.includes("rental-service") && serviceType == "rental-service") {
                            
                            var url = "{{route('rental_orders.driver','id')}}";
                            url = url.replace("id", dirver.id);
                            $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i> {{trans('lang.order_plural')}}</a>');

                            await database.collection('rental_orders').where('driverId', '==', dirver.id).get().then(async function (orderSnapshots) {
                                $('.total_orders').html(orderSnapshots.docs.length);
                            });

                        } else if (serviceTypes.includes("parcel_delivery") && serviceType == "parcel_delivery") {
                            
                            var url = "{{route('parcel_orders.driver','id')}}";
                            url = url.replace("id", dirver.id);
                            $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i> {{trans('lang.order_plural')}}</a>');
                            await database.collection('parcel_orders').where('driverId', '==', dirver.id).get().then(async function (orderSnapshots) {
                                $('.total_orders').html(orderSnapshots.docs.length);
                            });
                        }
                    }

                    if (dirver.zoneId){
                        database.collection('zone').doc(dirver.zoneId).get().then((zoneSnap) => {
                            if (zoneSnap.exists) {
                                const zoneName = zoneSnap.data().name || '';
                                $(".zone_name").text(zoneName);
                            } else {
                                $(".zone_name").text('-');
                            }
                        });

                    }else {
                        $(".zone_name").text('-');
                    }
                
                    var image = "";
                    if (dirver.profilePictureURL) {
                        if(dirver.profilePictureURL){
                            photo=dirver.profilePictureURL;
                        }else{
                            photo=placeholderImage;
                        }
                        image = '<img width="200px" id="" height="auto" src="' + photo + '" class="clickable-image profile-image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                    } else {
                        image = '<img width="200px" id="" height="auto" src="' + placeholderImage + '" class="clickable-image profile-image">';
                    }
                    $(".profile_image").html(image);
              
                    $('.clickable-image').on('click', function () { 
                        var imageSrc = $(this).attr('src');
                        var $parentBox = $(this).closest('.driver-detail-box');
                        var headingText;
                        if ($(this).hasClass('profile-image')) {
                            headingText = 'Profile Image'; 
                        } else if ($(this).hasClass('vehicle-profile-image')) {
                            headingText = $parentBox.find('h4').text() || 'Vehicle Profile Image';
                        } else if ($(this).hasClass('driver-proof-image')) {
                            headingText = $parentBox.find('h4').text() || 'Driver Proof Image';
                        } else if ($(this).hasClass('vehicle-proof-image')) {
                            headingText = $parentBox.find('h4').text() || 'Vehicle Proof Image';
                        } else {
                            headingText = 'Image Preview';
                        }
                        $('#previewImage').attr('src', imageSrc);
                        $('#modalImageTitle').text(headingText);
                        $('#imagePreviewModal').modal('show');
                    });

                    $('.close, [data-dismiss="modal"]').on('click', function () {
                        $('#imagePreviewModal').modal('hide');
                    });

                    if (dirver.hasOwnProperty('userBankDetails') && dirver.userBankDetails != null) {
                        $(".bank_name").text(dirver.userBankDetails.bankName || '');
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
    </script>
@endsection