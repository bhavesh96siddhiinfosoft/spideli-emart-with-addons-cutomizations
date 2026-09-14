@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{trans('lang.fleet_drivers')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('drivers') !!}">{{trans('lang.fleet_drivers')}}</a>
                    </li>
                    <li class="breadcrumb-item active">{{trans('lang.fleet_driver_edit')}}</li>
                </ol>
            </div>
        </div>
        <div>
            <div class="card-body">
                <div class="error_top"></div>
                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">
                        <fieldset>
                            <legend>{{trans('lang.driver_details')}}</legend>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.first_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_first_name"
                                           onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32)">
                                    <div class="form-text text-muted">{{trans('lang.first_name_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.last_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_last_name"
                                           onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32)">
                                    <div class="form-text text-muted">{{trans('lang.last_name_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.email')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_email">
                                    <div class="form-text text-muted">{{trans('lang.user_email_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.user_phone')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_phone"
                                        onkeypress="return chkAlphabets2(event,'error2')" readonly>
                                    <div id="error2" class="err"></div>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_phone_help") }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.user_latitude')}}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control user_latitude"
                                           onkeypress="return chkAlphabets3(event,'error2')">
                                    <div id="error2" class="err"></div>
                                    <div class="form-text text-muted">{{trans('lang.user_latitude_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.user_longitude')}}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control user_longitude"
                                           onkeypress="return chkAlphabets3(event,'error3')">
                                    <div id="error3" class="err"></div>
                                    <div class="form-text text-muted">{{trans('lang.user_longitude_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.zone') }}<span class="required-field"></span></label>
                                <div class="col-7">
                                    <select id='zone' class="form-control">
                                        <option value="">{{ trans('lang.select_zone') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.profile_image')}}</label>
                                <div class="col-7">
                                    <input type="file" onChange="handleFileSelect(event)" class="">
                                    <div class="form-text text-muted">{{trans('lang.profile_image_help')}}</div>
                                </div>
                                <div class="placeholder_img_thumb user_image">
                                </div>
                                <div id="uploding_image"></div>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="col-7 form-check-inline user_active" id="user_active">
                                <label class="col-3 control-label" for="user_active">{{trans('lang.active')}}</label>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="col-7 form-check-inline" id="reset_password">
                                <label class="col-3 control-label"
                                       for="reset_password">{{trans('lang.reset_driver_password')}}</label>
                            </div>
                            <div class="form-group row width-100">
                                <div class="form-text text-muted w-100 col-12">
                                    {{ trans("lang.note_reset_driver_password_email") }}
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <div class="col-3 control-label" style="margin-top: 16px;">
                                    <button type="button" class="btn btn-primary"
                                            id="send_mail">{{trans('lang.send_mail')}}
                                    </button>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="service_wrapper" style="display:none">
                            <legend>{{trans('lang.section_service_details')}}</legend>
                            <div class="form-group row width-100">
                                <div class="col-12">
                                    <div id="section_wrapper"></div>
                                </div>
                            </div>
                        </fieldset>

                    </div>
                </div>
            </div>
            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i> {{
                trans('lang.save')}}
                </button>
                <a href="{!! route('fleet.drivers') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
                trans('lang.cancel')}}</a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    <script type="text/javascript">

        var id = "{{ $id }}";
        
        var section_id = getCookie('section_id') || '';
        var service_type = getCookie('service_type') || '';
        
        var database = firebase.firestore();
        var ref = database.collection('users').where("id", "==", id);
        var photo = "";
        var fileName = '';
        var oldProfileFile = '';
        
        var storage = firebase.storage();
        var storageRef = firebase.storage().ref('images');
        
        var refZone = database.collection('zone').where('publish', '==', true);
        var refCarMake = database.collection('car_make');
        var refCarModel = database.collection('car_model');
        var refCabVehicle = database.collection('vehicle_type');
        var refRentalVehicle = database.collection('rental_vehicle_type');
        var refSection = database.collection('sections').where('isActive', '==', true);
    
        var placeholderImage = '';
        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function (snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })
        
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
        });
        
        $("#send_mail").click(function () {
            if ($("#reset_password").is(":checked")) {
                var email = $(".user_email").val();
                firebase.auth().sendPasswordResetEmail(email)
                    .then((res) => {
                        alert('{{trans("lang.driver_mail_sent")}}');
                    })
                    .catch((error) => {
                        console.log('Error password reset: ', error);
                    });
            } else {
                alert('{{trans("lang.error_reset_driver_password")}}');
            }
        });

        $(document).ready(async function () {
            
            jQuery("#data-table_processing").show();

            $('#zone').empty().append(
                $("<option></option>").attr("value", "").attr("disabled", true).attr("selected", 'selected').text("{{ trans('lang.select_zone') }}")
            );
            
            refZone.orderBy('name', 'asc').get().then(async function(snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    $('#zone').append($("<option></option>")
                        .attr("value", data.id)
                        .text(data.name));
                })
            });

            let userRef = await database.collection('users').doc(id).get();
            let user = userRef.data();

            /* A driver outside the region the admin is working in must not be
             * reachable by editing the url. */
            if (!isInActiveRegion(user)) {
                window.location.href = '{{ route('fleet.drivers') }}';
                return;
            }

            $(".user_first_name").val(user.firstName);
            $(".user_last_name").val(user.lastName);
            $(".user_email").val(shortEmail(user.email)).prop('disabled',true);
            
            if (user.hasOwnProperty('zoneId') && user.zoneId != '') {
                $("#zone").val(user.zoneId);
            }
            let = phoneNumber = user.countryCode + user.phoneNumber;
            if(phoneNumber){
                $(".user_phone").val('+' + EditPhoneNumber(phoneNumber.slice(1))).prop('disabled',true);
            }else{
                $(".user_phone").val(EditPhoneNumber(phoneNumber)).prop('disabled',true);
            }
            if (user.hasOwnProperty('carColor')) {
                $('.car_color').val(user.carColor);
            }
            if (user.hasOwnProperty('location')) {
                $(".user_latitude").val(user.location.latitude);
                $(".user_longitude").val(user.location.longitude);
            }
            oldProfileFile = user.profilePictureURL;
            
            if (user.active) {
                $(".user_active").prop('checked', true);
            }
            
            if (oldProfileFile != '' && oldProfileFile != null) {
                $(".user_image").append('<img class="rounded" style="width:50px" src="' + oldProfileFile + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image">');
            } else {
                $(".user_image").append('<img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">');
            }

            var wallet = 0;
            if (user.wallet_amount) {
                wallet = user.wallet_amount;
            }
            if (currencyAtRight) {
                wallet = parseFloat(wallet).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                wallet = currentCurrency + "" + parseFloat(wallet).toFixed(decimal_degits);
            }

            $("#wallet_amount").text(wallet);


            let ownerId = user.ownerId;
            let ownerRef = await database.collection('users').doc(ownerId).get();
            let ownerData = ownerRef.data();
            let serviceTypes = ownerData.serviceTypes || [];
            if(serviceTypes.length > 0){
                $(".service_wrapper").show();
            }
            
            refSection.get().then((snapshots) => {

                const groups = {
                    "cab-service": "Cab Service",
                    "parcel_delivery": "Parcel Delivery Service",
                    "rental-service": "Rental Service",
                };
                
                let html = "";
                
                serviceTypes.forEach((serviceType) => {
                    if (groups[serviceType]) {
                        html += `
                            <div class="service-group border-bottom pb-2 mb-2" data-flag="${serviceType}">
                                <h4 class="text-dark mb-2">${groups[serviceType]}</h4>
                                <div class="service-items"></div>
                            </div>
                        `;
                    }
                });
                
                $("#section_wrapper").html(html);
                
                snapshots.docs.forEach((doc) => {
                    
                    const data = doc.data();

                    if (!serviceTypes.includes(data.serviceTypeFlag)) {
                        return;
                    }

                    if (groups[data.serviceTypeFlag]) {

                        let vhtml = '';
                        if(data.serviceTypeFlag == "cab-service" || data.serviceTypeFlag == "rental-service"){

                            vhtml += `<div class="vehicle-details pt-3" id="vehicle_details_${data.id}" style="display:none">
                                
                                <div class="form-group row width-50 mb-0" id="vehicle_type_${data.id}">
                                    <label class="control-label">{{trans('lang.vehicle_type')}}</label>
                                    <select name="vehicle_type" class="form-control vehicle_type">
                                        <option value="">{{trans('lang.select')}} {{trans('lang.vehicle_type')}}
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="form-group row width-50" id="vehicle_make_${data.id}"> 
                                    <label class="control-label">{{trans('lang.car_make')}}</label>
                                    <div class="form-field">
                                        <select name="car_make" class="form-control car_make">
                                            <option value="">{{trans('lang.select')}} {{trans('lang.car_make')}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group row width-50" id="vehicle_model_${data.id}">
                                    <label class="control-label">{{trans('lang.car_model')}}</label>
                                    <div class="form-field">
                                        <select name="car_model" class="form-control car_model">
                                            <option value="">{{trans('lang.select')}} {{trans('lang.car_model')}}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row width-50" id="vehicle_number_${data.id}">
                                    <label class="control-label">{{trans('lang.car_number')}}</label>
                                    <div class="form-field">
                                        <input type="text" class="form-control car_number">
                                    </div>
                                </div>`;
                            
                                if (data.serviceTypeFlag === "cab-service") {
                                    vhtml += `
                                    <div class="form-group row width-50" id="ride_type_wrapper_${data.id}">
                                        <label class="control-label" for="user_active">{{ trans('lang.choose_ride_type') }}</label>
                                        <div class="form-field">
                                            <div id="type_ride" class="radio-form" style="display: none">
                                                <input type="radio" class="form-check-inline" name="ride_type_${data.id}" id="ride_type_${data.id}" value="ride">
                                                <label for="ride_type_${data.id}">{{ trans('lang.ride') }}</label>
                                            </div>
                                            <div id="type_intercity" class="radio-form" style="display: none">
                                                <input type="radio" class="form-check-inline" name="ride_type_${data.id}" id="ride_type_${data.id}"value="intercity">
                                                <label for="ride_type_${data.id}">{{ trans('lang.intercity') }}</label>
                                            </div>
                                            <div id="type_both" class="radio-form" style="display: none">
                                                <input type="radio" class="form-check-inline" name="ride_type_${data.id}" id="ride_type_${data.id}" value="both">
                                                <label for="ride_type_${data.id}"> {{ trans('lang.both') }}</label>
                                            </div>
                                        </div>
                                    </div>`;
                                }
                                
                            vhtml += `</div>`;
                        }
                        
                        $(`div[data-flag='${data.serviceTypeFlag}'] .service-items`).append(`
                            <div class="mb-3 p-3 border" data-section-name="${data.name}">
                                <div class='form-check pl-0'>
                                    <input type="checkbox" class="section-checkbox" value="${data.id}" data-service-type="${data.serviceTypeFlag}">
                                    <label class='mb-0'>${data.name}</label>
                                </div>
                                ${vhtml}
                            </div>
                        `);
                    }
                });
            });

            getTotalOrders(id, user.serviceType);
            
            setTimeout(() => {
                setSelectedService(user);
            }, 1500);

            jQuery("#data-table_processing").hide();
            

            async function setSelectedService(user) {

                let sectionsByService = {};
                user.sectionIds.forEach((sectionId) => {
                    const checkbox = $(`.section-checkbox[value="${sectionId}"]`);
                    const serviceType = checkbox.data("service-type");
                    if (!serviceType) return;
                    if (!sectionsByService[serviceType]) {
                        sectionsByService[serviceType] = [];
                    }
                    sectionsByService[serviceType].push(sectionId);
                    checkbox.prop("checked", true);
                });

                for (const serviceType in sectionsByService) {

                    const sectionIds = sectionsByService[serviceType];

                    for (const sectionId of sectionIds) {

                        const vehicleData = user.vehicleDetails?.[sectionId];
                        const wrapper = $(`#vehicle_details_${sectionId}`);
                        if (!vehicleData) {
                            continue;
                        }
                        
                        wrapper.show();
                        
                        const vehicleTypeSelect = wrapper.find(".vehicle_type");
                        const carMakeSelect = wrapper.find(".car_make");
                        const carModelSelect = wrapper.find(".car_model");
                        const carNumberInput = wrapper.find(".car_number");

                        let vehicleOptions = `<option value="">{{trans('lang.select')}} {{trans('lang.vehicle_type')}}</option>`;
                        if (serviceType === "cab-service") {
                            const snap = await refCabVehicle.where("sectionId", "==", sectionId).get();
                            let sectionRef = await database.collection('sections').doc(sectionId).get();
                            let sectionData = sectionRef.data();
                            snap.forEach((doc) => {
                                const v = doc.data();
                                vehicleOptions += `<option value="${doc.id}" data-ride-type="${sectionData.rideType || ''}">${v.name}</option>`;
                            });
                        }else if (serviceType === "rental-service") {
                            const snap = await refRentalVehicle.where("sectionId", "==", sectionId).get();
                            snap.forEach((doc) => {
                                const v = doc.data();
                                vehicleOptions += `<option value="${doc.id}">${v.name}</option>`;
                            });
                        }
                        vehicleTypeSelect.html(vehicleOptions);
                        vehicleTypeSelect.val(vehicleData.vehicleId);

                        let makeOptions = `<option value="">{{trans('lang.select')}} {{trans('lang.car_make')}}</option>`;
                        const carMakeSnap = await refCarMake.orderBy('name', 'asc').get();
                        carMakeSnap.forEach((doc) => {
                            const v = doc.data();
                            makeOptions += `<option value="${v.name}">${v.name}</option>`;
                        });
                        carMakeSelect.html(makeOptions);
                        carMakeSelect.val(vehicleData.carBrand);

                        let modelOptions = `<option value="">{{trans('lang.select')}} {{trans('lang.car_model')}}</option>`;
                        const modelSnap = await refCarModel.where('car_make_name', '==', vehicleData.carBrand).orderBy('name', 'asc').get();
                        modelSnap.forEach((doc) => {
                            const v = doc.data();
                            modelOptions += `<option value="${v.name}">${v.name}</option>`;
                        });
                        carModelSelect.html(modelOptions);
                        carModelSelect.val(vehicleData.carModel);
                        carNumberInput.val(vehicleData.carPlateNumber || '');

                        if (serviceType === "cab-service" && vehicleData.rideType) {
                            const rideWrapper = $(`#ride_type_wrapper_${sectionId}`);
                            if (vehicleData.rideType === "ride") {
                                rideWrapper.find("#type_ride").show();
                            } else if (vehicleData.rideType === "intercity") {
                                rideWrapper.find("#type_intercity").show();
                            } else if (vehicleData.rideType === "both") {
                                rideWrapper.find("#type_ride").show();
                                rideWrapper.find("#type_intercity").show();
                                rideWrapper.find("#type_both").show();
                            }
                            rideWrapper.find(`input[name="ride_type_${sectionId}"][value="${vehicleData.rideType}"]`).prop("checked", true);
                        }
                    }
                }
            }

            function getSelectedSections() {
                
                let sectionIds = [];
                let serviceTypes = [];
                let sectionNames = {};
                let vehicleDetails = {};
                let isValid = true;    

                $(".section-checkbox:checked").each(function () {

                    let sectionId = $(this).val();
                    let serviceType = $(this).data("service-type");
                    let sectionName = $(this).closest("[data-section-name]").data("section-name");

                    sectionIds.push(sectionId);
                    if (!serviceTypes.includes(serviceType)) {
                        serviceTypes.push(serviceType);
                    }
                    sectionNames[sectionId] = sectionName;

                    // Check if this section needs vehicle data
                    if (serviceType === "cab-service" || serviceType === "rental-service") {

                        const wrapper = $(`#vehicle_details_${sectionId}`);
                        const vehicleSelect = wrapper.find(".vehicle_type");
                        const carMake = wrapper.find(".car_make").val();
                        const carModelSelect = wrapper.find(".car_model option:selected");
                        const carModel = carModelSelect.val();
                        const carNumber = wrapper.find(".car_number").val();

                        const vehicleId = vehicleSelect.val();
                        const vehicleType = vehicleSelect.find("option:selected").text();
                        
                        if (!vehicleId) {
                            $(".error_top").show().html("<p>" + sectionName + " : {{trans('lang.vehicle_type_error')}}</p>");
                            window.scrollTo(0, 0);
                            isValid = false;
                            return false;
                        }
                        
                        if (!carMake) {
                            $(".error_top").show().html("<p>" + sectionName + " : {{trans('lang.car_make_error')}}</p>");
                            window.scrollTo(0, 0);
                            isValid = false;
                            return false;
                        }

                        if (!carModel) {
                            $(".error_top").show().html("<p>" + sectionName + " : {{trans('lang.car_model_error')}}</p>");
                            window.scrollTo(0, 0);
                            isValid = false;
                            return false;
                        }

                        if (!carNumber) {
                            $(".error_top").show().html("<p>" + sectionName + " : {{trans('lang.car_number_error')}}</p>");
                            window.scrollTo(0, 0);
                            isValid = false;
                            return false;
                        }

                        vehicleDetails[sectionId] = {
                            vehicleId: vehicleId,
                            vehicleType: vehicleType,
                            carBrand: carMake,
                            carModel: carModel,
                            carPlateNumber: carNumber
                        };

                        // Optional: If you have rideType:
                        let rideType = $(`input[name="ride_type_${sectionId}"]:checked`).val();
                        if (rideType) {
                            vehicleDetails[sectionId].rideType = rideType;
                        }
                    }
                });

                if (!isValid) {
                    return false;
                }
            
                if (sectionIds.length === 0) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.section_atleast_one_section')}}</p>");
                    window.scrollTo(0, 0);
                    return false;
                }

                return {
                    sectionIds,
                    serviceTypes,
                    sectionNames,
                    vehicleDetails
                };
            }

            $(document).on("change", ".section-checkbox", async function () {

                const sectionId = $(this).val();
                const serviceType = $(this).data("service-type");
                const wrapper = $(`#vehicle_details_${sectionId}`);
                
                if (serviceType === "cab-service" || serviceType === "rental-service") {

                    if (this.checked) {
                        
                        wrapper.show();

                        const vehicle_type = wrapper.find(".vehicle_type");
                        vehicle_type.html(`<option value="">{{trans('lang.select')}} {{trans('lang.vehicle_type')}}</option>`);
                        
                        if (serviceType === "cab-service") {
                            let sectionRef = await database.collection('sections').doc(sectionId).get();
                            let sectionData = sectionRef.data();
                            const snap = await refCabVehicle.where("sectionId", "==", sectionId).get();
                            snap.docs.forEach((doc) => {
                                const v = doc.data();
                                vehicle_type.append(
                                    `<option value="${doc.id}" data-ride-type="${sectionData.rideType || ''}">${v.name}</option>`
                                );
                            });

                            const rideWrapper = $(`#ride_type_wrapper_${sectionId}`);
                            if (sectionData.rideType === "ride") {
                                rideWrapper.find("#type_ride").show();
                            } else if (sectionData.rideType === "intercity") {
                                rideWrapper.find("#type_intercity").show();
                            } else if (sectionData.rideType === "both") {
                                rideWrapper.find("#type_ride").show();
                                rideWrapper.find("#type_intercity").show();
                                rideWrapper.find("#type_both").show();
                            }
                            rideWrapper.find(`input[name="ride_type_${sectionId}"][value="${sectionData.rideType}"]`).prop("checked", true);

                        } else if (serviceType === "rental-service") {
                            const snap = await refRentalVehicle.where("sectionId", "==", sectionId).get();
                            snap.docs.forEach((doc) => {
                                const v = doc.data();
                                vehicle_type.append(
                                    `<option value="${doc.id}">${v.name}</option>`
                                );
                            });
                        }

                        const car_make = wrapper.find(".car_make");
                        car_make.html(`<option value="">{{trans('lang.select')}} {{trans('lang.car_make')}}</option>`);
                        const carmMakeRef = await refCarMake.orderBy('name', 'asc').get();
                        carmMakeRef.docs.forEach((doc) => {
                            const v = doc.data();
                            car_make.append(
                                `<option value="${v.name}">${v.name}</option>`
                            );
                        });

                    } else {
                        wrapper.hide();
                    }
                }
            });

            
            $(".edit-form-btn").click(function () {

                var userFirstName = $(".user_first_name").val();
                var userLastName = $(".user_last_name").val();
                var email = $(".user_email").val();
                var userPhone = $(".user_phone").val();
                var active = $(".user_active").is(":checked");
                var zoneId = $('#zone option:selected').val();
                
                var latitude = parseFloat($(".user_latitude").val());
                var longitude = parseFloat($(".user_longitude").val());
                var location = { 'latitude': latitude, 'longitude': longitude };
                
                var selectedSections = getSelectedSections();
                
                if (userFirstName == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.user_firstname_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if (userLastName == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.user_lastname_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if(isNaN(latitude)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.driver_lattitude_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if (latitude < -90 || latitude > 90) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.driver_lattitude_limit_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if (isNaN(longitude)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.driver_longitude_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if (longitude < -180 || longitude > 180) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.driver_longitude_limit_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if (zoneId == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.select_zone_help') }}</p>");
                    window.scrollTo(0, 0);
                } else if(!selectedSections){
                    return false;
                } else {

                    jQuery("#data-table_processing").show();

                    storeImageData().then(IMG => {
                        database.collection('users').doc(id).update({
                            'firstName': userFirstName,
                            'lastName': userLastName,
                            'active': active,
                            'profilePictureURL': IMG.profile,
                            'location': location,
                            'zoneId': zoneId,
                            'sectionIds': selectedSections.sectionIds,
                            'serviceTypes': selectedSections.serviceTypes,
                            'sectionNames': selectedSections.sectionNames,
                            'vehicleDetails': selectedSections.vehicleDetails
                        }).then(function (result) {
                            window.location.href = '{{ route("fleet.drivers")}}';
                        });
                    }).catch(function (error) {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + error + "</p>");
                        window.scrollTo(0, 0);
                    });
                }
            })
        })

        $(document).on('change', '.car_make', function () {
            const cab_make_name = $(this).val();
            const wrapper = $(this).closest('.vehicle-details');
            const modelDropdown = wrapper.find('.car_model');
            let options = `<option value="">{{trans("lang.select")}} {{trans("lang.car_model")}}</option>`;
            refCarModel.where('car_make_name', '==', cab_make_name)
            .orderBy('name', 'asc')
            .get()
            .then(function (snapshots) {
                snapshots.docs.forEach((doc) => {
                    const data = doc.data();
                    options += `
                        <option value="${data.name}" data-id="${data.id}">
                            ${data.name}
                        </option>
                    `;
                });
                modelDropdown.html(options);
            });
        });
        
        async function getTotalOrders(id, type) {
            var count_order_complete = 0;
            var url = "Javascript:void(0)";
            var order_text = '';
            if (type == "cab-service") {
                url = "{{route('drivers.rides','driverId')}}";
                url = url.replace('driverId', id);
                await database.collection('rides').where('driverID', '==', id).get().then(async function (orderSnapshots) {
                    count_order_complete = orderSnapshots.docs.length;
                });
                order_text = "{{trans('lang.rides')}}";
            } else if (type == "rental-service") {
                url = "{{route('rental_orders.driver','id')}}";
                url = url.replace("id", id);
                await database.collection('rental_orders').where('driverID', '==', id).get().then(async function (orderSnapshots) {
                    count_order_complete = orderSnapshots.docs.length;
                });
                order_text = "{{trans('lang.rental_orders')}}";
            } else if (type == "delivery-service" || type == "ecommerce-service") {
                url = "{{route('orders','id')}}";
                url = url.replace("id", 'driverId=' + id);
                await database.collection('vendor_orders').where('driverID', '==', id).get().then(async function (orderSnapshots) {
                    count_order_complete = orderSnapshots.docs.length;
                });
                order_text = "{{trans('lang.order_plural')}}";
            } else if (type == "parcel_delivery") {
                url = "{{route('parcel_orders.driver','id')}}";
                url = url.replace("id", id);
                await database.collection('parcel_orders').where('driverID', '==', id).get().then(async function (orderSnapshots) {
                    count_order_complete = orderSnapshots.docs.length;
                });
                order_text = "{{trans('lang.parcel_orders')}}";
            }
            $("#total_orders").text(count_order_complete);
            $('.driver_order_text').html(order_text);
            $('.driver_orders_url').attr('href', url);
        }

        function handleFileSelect(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function (theFile) {
                return function (e) {
                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var timestamp = Number(new Date());
                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                    photo = filePayload;
                    fileName = filename;
                    $(".user_image").empty();
                    $(".user_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">');
                };
            })(f);
            reader.readAsDataURL(f);
        }

        async function storeImageData() {
            var newPhoto = [];
            newPhoto['profile'] = '';
            if (photo != '' && photo != oldProfileFile) {
                    if (oldProfileFile != "" && oldProfileFile != null) {
                        var oldImageUrlRef = await storage.refFromURL(oldProfileFile);
                        imageBucket = oldImageUrlRef.bucket;
                        var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                        if (imageBucket == envBucket) {
                            if (oldImageUrlRef) {
                                await oldImageUrlRef.delete().then(() => {
                                    console.log("Old file deleted!")
                                }).catch((error) => {
                                    console.log("ERR File delete ===", error);
                                });
                            }
                        } else {
                            console.log('Bucket not matched');
                        }
                    }
                    try {
                        photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")
                        var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', {contentType: 'image/jpg'});
                        var downloadURL = await uploadTask.ref.getDownloadURL();
                        newPhoto['profile'] = downloadURL;
                        photo = downloadURL;
                    } catch (error) {
                        console.log("ERR ===", error);
                    }
            } else {
                newPhoto['profile'] = oldProfileFile;
            }
            return newPhoto;
        }

        function chkAlphabets3(event, msg) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                document.getElementById(msg).innerHTML = "Accept only Number and Dot(.)";
                return false;
            } else {
                document.getElementById(msg).innerHTML = "";
                return true;
            }
        }

    </script>

@endsection
