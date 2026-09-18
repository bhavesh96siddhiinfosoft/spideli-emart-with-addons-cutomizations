@extends('layouts.app')
@section('content')
<?php
$countries = file_get_contents(public_path('countriesdata.json'));
$countries = json_decode($countries);
$countries = (array)$countries;
$newcountries = array();
$newcountriesjs = array();
foreach ($countries as $keycountry => $valuecountry) {
    $newcountries[$valuecountry->phoneCode] = $valuecountry;
    $newcountriesjs[$valuecountry->phoneCode] = $valuecountry->code;
}
?>
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.driver_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('drivers') !!}">{{trans('lang.driver_plural')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.driver_edit')}}</li>
            </ol>
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
                                    <input type="email" class="form-control user_email">
                                    <div class="form-text text-muted">{{trans('lang.user_email_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.password')}}</label>
                                <div class="col-7">
                                    <input type="password" class="form-control user_password">
                                    <div class="form-text text-muted">{{trans('lang.user_password_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row"> 
                                <label class="col-3 control-label">{{trans('lang.user_phone')}}</label>
                                <div class="col-md-6">
                                    <div class="phone-box position-relative" id="phone-box">
                                        <select name="country" id="country_selector">
                                            <?php foreach ($newcountries as $keycy => $valuecy) { ?>
                                                <?php $selected = ""; ?>
                                                <option <?php echo $selected; ?> code="<?php echo $valuecy->code; ?>" value="<?php echo $keycy; ?>">+<?php echo $valuecy->phoneCode; ?> {{$valuecy->countryName}}</option>
                                            <?php } ?>
                                        </select>
                                        <input type="text" class="form-control user_phone"  onkeypress="return chkAlphabets2(event,'error2')">
                                        <div id="error2" class="err"></div>
                                    </div>
                                </div>
                                <div class="form-text text-muted">
                                    {{trans('lang.user_phone_help')}}
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
                                <div class="placeholder_img_thumb user_image"></div>
                                <div id="uploding_image"></div>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="col-7 form-check-inline user_active" id="user_active">
                                <label class="col-3 control-label" for="user_active">{{trans('lang.active')}}</label>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>{{trans('lang.section_service_details')}}</legend>
                            <div class="form-group row width-100">
                                <div class="col-12">
                                    <div id="section_wrapper"></div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>

                            <legend>{{trans('lang.bankdetails')}}</legend>
                            
                            <div class="form-group row width-100" style="display: none;" id="companyDriverShowDiv">
                                <div class="col-12">
                                    <h6><a href="#">{{ trans("lang.driver_add_by_company_info") }}</a>
                                    </h6>
                                </div>
                            </div>
                            
                            <div class="form-group row" id="companyDriverHideDiv">
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.bank_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="bank_name" class="form-control" id="bankName"
                                            onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32)">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.branch_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="branch_name" class="form-control" id="branchName"
                                            onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32)">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.holer_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="holer_name" class="form-control" id="holderName"
                                            onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32)">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.account_number')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="account_number" class="form-control" id="accountNumber"
                                            onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.other_information')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="other_information" class="form-control"
                                            id="otherDetails">
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i> {{
                    trans('lang.save')}}</button>
                <a href="{!! route('drivers') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
                    trans('lang.cancel')}}</a>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">

    var section_id = getCookie('section_id') || '';
    var service_type = getCookie('service_type') || '';

    var database = firebase.firestore();
    var geoFirestore = new GeoFirestore(database);
    var createdAt = firebase.firestore.FieldValue.serverTimestamp();
    var photo = "";
    var fileName = '';
    
    var refZone = database.collection('zone').where('publish', '==', true);
    var refCarMake = database.collection('car_make');
    var refCarModel = database.collection('car_model');
    var refCabVehicle = database.collection('vehicle_type');
    var refRentalVehicle = database.collection('rental_vehicle_type');
    var refSection = database.collection('sections').where('isActive', '==', true).orderBy('order');;

    let isAutoVerify = false;
    
    $(document).ready(async function () {

        jQuery("#data-table_processing").show();

        let sectionRef = await database.collection('sections').doc(section_id).get();
        let sectionData = sectionRef.data();
        
        jQuery("#country_selector").select2({
			templateResult: formatState,
			templateSelection: formatState2,
			placeholder: "Select Country",
			allowClear: true
		});

        // --- ADD THIS BLOCK TO SET DEFAULT COUNTRY CODE ---
        var globalSettingsRef = database.collection('settings').doc('globalSettings');
        globalSettingsRef.get().then(async function (snapshot) {
            var globalSettings = snapshot.data();
            if (globalSettings && globalSettings.defaultCountryCode) {
                var defaultPhoneCode = globalSettings.defaultCountryCode.replace('+', '').trim();
                // Find the option with matching phoneCode
                var $option = $("#country_selector option").filter(function() {
                    return $(this).val() === defaultPhoneCode;
                });
                if ($option.length > 0) {
                    $("#country_selector").val(defaultPhoneCode).trigger('change');
                } else {
                    console.warn("Default country code not found in list:", defaultPhoneCode);
                }
            }
        }).catch(function (error) {
            console.error("Error fetching global settings: ", error);
        });
        // --- END OF DEFAULT COUNTRY LOGIC ---

        let documentVerify = await database.collection('settings').doc('document_verification_settings').get();
        let documentSettings = documentVerify.data();
        if(documentSettings.isDriverVerification === false){
            isAutoVerify = true;
        }
    
        $('#zone').empty().append(
            $("<option></option>").attr("value", "").attr("disabled", true).attr("selected", 'selected').text("{{ trans('lang.select_zone') }}")
        );

        refZone.orderBy('name', 'asc').get().then(async function(snapshots) {
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                /* Only zones serving the region being worked in can be picked,
                 * so a driver can never be placed outside it. A zone may serve
                 * several regions, so its whole list is checked. */
                var zoneRegions = zoneRegionIds(data);
                var active = getActiveRegionId();

                if (active && zoneRegions.indexOf(active) === -1) {
                    return;
                }
                $('#zone').append($("<option></option>")
                    .attr("value", data.id)
                    .attr("data-region", regionForZone(data))
                    .text(data.name));
            })
        });
        
        refSection.get().then((snapshots) => {
            const groups = {
                "cab-service": "Cab Service",
                "delivery-service": "Multivendor Delivery Service",
                "parcel_delivery": "Parcel Delivery Service",
                "rental-service": "Rental Service",
            };
            let html = "";
            for (const key in groups) {
                html += `
                    <div class="service-group border-bottom pb-2 mb-2" data-flag="${key}">
                        <h4 class="text-dark mb-2">${groups[key]}</h4>
                        <div class="service-items"></div>
                    </div>
                `;
            }
            $("#section_wrapper").html(html);
            snapshots.docs.forEach((doc) => {
                const data = doc.data();
                if (groups[data.serviceTypeFlag]) {

                    let vhtml = '';
                    if(data.serviceTypeFlag == "cab-service" || data.serviceTypeFlag == "rental-service"){
                        vhtml += `
                        <div class="vehicle-details pt-3" id="vehicle_details_${data.id}" style="display:none"> 
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
                            </div>
                        </div>
                        `;
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
        
        jQuery("#data-table_processing").hide();
    });

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
                const rideType = vehicleSelect.find("option:selected").data("ride-type") || "";

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

    $(".save-form-btn").click(function () {

        var userFirstName = $(".user_first_name").val();
        var userLastName = $(".user_last_name").val();
        var email = $(".user_email").val();
        var password = $(".user_password").val();
        var country_code = '+' + $("#country_selector").val();
        var userPhone = $(".user_phone").val();
        var active = $(".user_active").is(":checked");
        var zoneId = $('#zone option:selected').val();
        /* The zone decides the region. A zone serving several regions cannot do
         * that alone, so regionForZone() settled it when the list was built:
         * the region being worked in wins, and a zone serving exactly one
         * region still decides by itself. */
        var zoneRegion = $('#zone option:selected').data('region');
        var driverRegionId = zoneRegion ? zoneRegion : getActiveRegionId();

        var latitude = parseFloat($(".user_latitude").val());
        var longitude = parseFloat($(".user_longitude").val());
        var location = { 'latitude': latitude, 'longitude': longitude };
        
        var selectedSections = getSelectedSections();
        var id = database.collection("tmp").doc().id;

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
        } else if (email == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.user_email_error')}}</p>");
            window.scrollTo(0, 0);
        } else if (password == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.user_password_error')}}</p>");
            window.scrollTo(0, 0);
        }else if(!country_code) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.select_country_code')}}</p>");
            window.scrollTo(0,0);
        } else if (userPhone == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.user_phone_error')}}</p>");
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

            var bankName = $("#bankName").val();
            var branchName = $("#branchName").val();
            var holderName = $("#holderName").val();
            var accountNumber = $("#accountNumber").val();
            var otherDetails = $("#otherDetails").val();
            var userBankDetails = {
                'bankName': bankName,
                'branchName': branchName,
                'holderName': holderName,
                'accountNumber': accountNumber,
                'accountNumber': accountNumber,
                'otherDetails': otherDetails,
            };
            
            firebase.auth().fetchSignInMethodsForEmail(email).then(function (methods) {

                if (methods.length > 0) {
                    jQuery("#data-table_processing").hide();
                    $(".error_top").show().html("<p>{{trans('lang.email_exist')}}</p>");
                    window.scrollTo(0, 0);
                    return false;
                }

                firebase.auth().createUserWithEmailAndPassword(email, password).then(function (firebaseUser) {
                        
                    id = firebaseUser.user.uid;
                    coordinates = new firebase.firestore.GeoPoint(latitude, longitude);

                    storeImageData().then(IMG => {
                        geoFirestore.collection('users').doc(id).set({
                            'appIdentifier': 'web',
                            'id': id,
                            'firstName': userFirstName,
                            'lastName': userLastName,
                            'email': email,
                            'phoneNumber': country_code+userPhone,
                            'active': active,
                            'profilePictureURL': IMG.profile,
                            'carColor': null,
                            'carProofPictureURL': null,
                            'driverProofPictureURL': null,
                            'location': location,
                            'carPictureURL': null,
                            'role': 'driver',
                            'carRate': null,
                            'carInfo': null,
                            'userBankDetails': userBankDetails,
                            'coordinates': coordinates,
                            'createdAt': createdAt,
                            'isDocumentVerify':false,
                            'isAutoVerify':isAutoVerify,
                            'isOwner': false,
                            'ownerId': null,
                            'vendorID': '',
                            'zoneId': zoneId,
                            'regionId': driverRegionId,
                            'provider': 'email',
                            'wallet_amount' : 0,
                            'sectionIds': selectedSections.sectionIds,
                            'serviceTypes': selectedSections.serviceTypes,
                            'sectionNames': selectedSections.sectionNames,
                            'vehicleDetails': selectedSections.vehicleDetails
                        }).then(function (result) {
                            window.location.href = '{{ route("drivers")}}';
                        });

                    }).catch(error => {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show().html("<p>" + error.message + "</p>");
                        window.scrollTo(0, 0);
                    });
                    
                }).catch(function (error) {
                    jQuery("#data-table_processing").hide();
                    $(".error_top").show().html("<p>" + error.message + "</p>");
                    window.scrollTo(0, 0);
                });

            }).catch(function (error) {
                jQuery("#data-table_processing").hide();
                $(".error_top").show().html("<p>" + error.message + "</p>");
                window.scrollTo(0, 0);
            });
        }
    });

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

    var storageRef = firebase.storage().ref('images');
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
                $(".user_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
            };
        })(f);
        reader.readAsDataURL(f);
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
    async function storeImageData() {
        var newPhoto = [];
        newPhoto['profile'] = '';
        try {
            if (photo != "") {
                photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")
                var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', { contentType: 'image/jpg' });
                var downloadURL = await uploadTask.ref.getDownloadURL();
                newPhoto['profile'] = downloadURL;
                photo = downloadURL;
            }
        } catch (error) {
            console.log("ERR ===", error);
        }
        return newPhoto;
    }
    
    var newcountriesjs = '<?php echo json_encode($newcountriesjs); ?>';
    var newcountriesjs = JSON.parse(newcountriesjs);
    function formatState(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/');?>/scss/icons/flag-icon-css/flags";
        var $state = $(
            '<span><img src="' + baseUrl + '/' + newcountriesjs[state.element.value].toLowerCase() + '.svg" class="img-flag" /> ' + state.text + '</span>'
        );
        return $state;
    }

    function formatState2(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/');?>/scss/icons/flag-icon-css/flags"
        var $state = $(
            '<span><img class="img-flag" /> <span></span></span>'
        );
        $state.find("span").text(state.text);
        $state.find("img").attr("src", baseUrl + "/" + newcountriesjs[state.element.value].toLowerCase() + ".svg");
        return $state;
    }

    function chkAlphabets2(event,msg)
    {
        if(!(event.which>=48  && event.which<=57)){
            document.getElementById(msg).innerHTML="Accept only Number";
            return false;
        }else{
            document.getElementById(msg).innerHTML="";
            return true;
        }
    }

</script>
@endsection
