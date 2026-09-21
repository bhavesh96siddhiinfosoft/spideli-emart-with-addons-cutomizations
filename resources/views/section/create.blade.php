@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.section_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('section') !!}">{{ trans('lang.section_plural') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ trans('lang.section_create') }}</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.section_create') }}</legend>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.section_name') }}</label>
                            <div class="col-7">
                                <input type="text" name="name" class="form-control name" id="name">
                                <div class="form-text text-muted">{{ trans('lang.section_name_help') }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label ">{{ trans('lang.section_color') }}</label>
                            <div class="col-7">
                                <input type="color" id="color" class="color" value="#0000ff">
                                <div class="form-text text-muted">{{ trans('lang.section_color_help') }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.section_image') }}</label>
                            <div class="col-7">
                                <input type="file" id="sectionImage" onChange="handleFileSelect(event)">
                                <div class="placeholder_img_thumb cat_image"></div>
                                <div id="uploding_image"></div>
                                <div class="form-text text-muted w-50">{{ trans('lang.section_image_help') }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label ">{{ trans('lang.service_type') }}</label>
                            <div class="col-12">
                                <select name="service_type" id="service_type" class="form-control service_type">
                                    <option value="">{{ trans('lang.select') }} {{ trans('lang.service_type') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        @include('section.partials.service_group')
                        <div class="form-group row width-100 marker-icon-div" style="display:none">
                            <label class="col-3 control-label">{{trans('lang.marker_icon')}}</label>
                            <div class="col-7">
                                <div class="map-markers">
                                    <ul>
                                        <li>
                                            <input type="radio" name="marker_icon" value="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fsedan.png?alt=media&token=50c63470-65e4-45fa-bda1-cecfce83cb47" id="sedan" title="Seden" checked>
                                            <img src="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fsedan.png?alt=media&token=50c63470-65e4-45fa-bda1-cecfce83cb47">
                                        </li>
                                        <li>
                                            <input type="radio" name="marker_icon" value="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fsuv.png?alt=media&token=e741359a-2f69-4e17-b731-a3f52282176b" id="suv" title="SUV">
                                            <img src="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fsuv.png?alt=media&token=e741359a-2f69-4e17-b731-a3f52282176b">
                                        </li>
                                        <li>
                                            <input type="radio" name="marker_icon" value="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fhatchback.png?alt=media&token=be339f9a-b483-4662-9965-b60e187e2824" id="hatchback" title="Hatchback">
                                            <img src="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fhatchback.png?alt=media&token=be339f9a-b483-4662-9965-b60e187e2824">
                                        </li>
                                        <li>
                                            <input type="radio" name="marker_icon" value="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fminivan.png?alt=media&token=298e9508-e925-49ce-9597-0a64b8d56e1e" id="minivan" title="Minivan">
                                            <img src="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fminivan.png?alt=media&token=298e9508-e925-49ce-9597-0a64b8d56e1e">
                                        </li>
                                        <li>
                                            <input type="radio" name="marker_icon" value="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fbike.png?alt=media&token=c8747106-ac49-436d-9355-3c274ca8bd76" id="bike" title="Bike">
                                            <img src="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fbike.png?alt=media&token=c8747106-ac49-436d-9355-3c274ca8bd76">
                                        </li>
                                        <li>
                                            <input type="radio" name="marker_icon" value="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fscooter.png?alt=media&token=0dd48daa-5d24-4166-bb49-f01f3afea683" id="scooter" title="Scooter">
                                            <img src="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fscooter.png?alt=media&token=0dd48daa-5d24-4166-bb49-f01f3afea683">
                                        </li>
                                        <li>
                                            <input type="radio" name="marker_icon" value="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fautorickshaw.png?alt=media&token=a3d607f8-fa02-46ca-855a-1b2da56ac9c1" id="autorickshaw" title="Auto Rickshaw">
                                            <img src="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fautorickshaw.png?alt=media&token=a3d607f8-fa02-46ca-855a-1b2da56ac9c1">
                                        </li>
                                        <li>
                                            <input type="radio" name="marker_icon" value="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fminibus.png?alt=media&token=f0e342a2-2c62-4a1c-be2b-9e78eb64db6c" id="minibus" title="Minibus">
                                            <img src="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fminibus.png?alt=media&token=f0e342a2-2c62-4a1c-be2b-9e78eb64db6c">
                                        </li>
                                        <li>
                                            <input type="radio" name="marker_icon" value="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fbus.png?alt=media&token=da016c81-6317-45ba-bf1e-e13192138c2b" id="bus" title="Bus">
                                            <img src="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Fbus.png?alt=media&token=da016c81-6317-45ba-bf1e-e13192138c2b">
                                        </li>
                                        <li>
                                            <input type="radio" name="marker_icon" value="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Ftruck.png?alt=media&token=4807adfe-458d-4659-b8d6-13d4125cd456" id="truck" title="Truck">
                                            <img src="https://firebasestorage.googleapis.com/v0/b/emart-8d99f.appspot.com/o/marker%2Ftruck.png?alt=media&token=4807adfe-458d-4659-b8d6-13d4125cd456">
                                        </li>
                                    </ul>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.marker_icon_help') }} 
                                    </div>   
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-100" id="div_ride_type" style="display: none;">
                            <label class="col-3 control-label" for="user_active">{{ trans('lang.choose_ride_type') }}</label>
                            <div class="col-7">
                                <input type="radio" class="form-check-inline" name="ride_type" id="ride" value="ride" checked>
                                <label for="ride">{{ trans('lang.ride') }}</label>
                                <input type="radio" class="form-check-inline" name="ride_type" id="intercity" value="intercity">
                                <label for="intercity">{{ trans('lang.intercity') }}</label>
                                <input type="radio" class="form-check-inline" name="ride_type" id="both" value="both">
                                <label for="both">{{ trans('lang.both') }}</label>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <div class="form-check">
                                <input type="checkbox" class="section_active" id="section_active">
                                <label class="col-3 control-label" for="section_active">{{ trans('lang.active') }}</label>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset id="" class="diliverychargeDiv" style="display: none">
                        <legend>{{ trans('lang.deliveryCharge') }}</legend>
                        <div class="form-group row width-50">
                            <label class="col-4 control-label">{{ trans('lang.deliveryCharge') }}</label>
                            <div class="col-7">
                                <input type="number" id="deliveryCharge" class="form-control ">
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-share"></i>{{ trans('lang.referral_settings') }}</legend>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.referral_amount') }}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control" id="referral_amount">
                                    <span class="currentCurrency"></span>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.referral_amount_help') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset id="food_delivery_set" style="display:none">
                        <legend>{{ trans('lang.food_delivery_feature') }}</legend>
                        <div class="form-group row width-100">
                            <div class="form-check">
                                <input type="checkbox" class="section_dine_in_active" id="section_dine_in_active">
                                <label class="col-3 control-label" for="section_dine_in_active">{{ trans('lang.dine_in_feature') }}</label>
                                <span style="font-size: 15px;">{{ trans('lang.dine_in_feature_note') }}</span>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <div class="form-check">
                                <input type="checkbox" class="is_product_details" id="is_product_details">
                                <label class="col-3 control-label" for="is_product_details">{{ trans('lang.product_detail_feature') }}</label>
                                <span style="font-size: 15px;">{{ trans('lang.product_detail_feature_note') }}</span>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset id="radios_set" style="display:none">
                        <legend>{{ trans('lang.radios_configuration') }}</legend>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label"><span id="stype"></span>
                                {{ trans('lang.nearby_radios') }}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control" id="vendor_nearby_radius" min="1">
                                    <span id="set_distance_type"></span>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="adminCommisitionDiv">
                        <legend>{{ trans('lang.admin_commission') }}</legend>
                        <div class="form-check width-100">
                            <label style="font-size: 15px;">{{ trans('lang.admin_commision_note_section') }}</label>
                        </div>
                        <div class="form-check width-100">
                            <input type="checkbox" class="form-check-inline" id="enable_commission">
                            <label class="col-5 control-label" for="enable_commission">{{ trans('lang.enable_adminCommission') }}</label>
                        </div>
                        <div class="form-fields" id="show_admin_commision_type_value" style="display:none">
                            <div class="form-group row width-50">
                                <label class="col-4 control-label">{{ trans('lang.commission_type') }}</label>
                                <div class="col-7">
                                    <select class="form-control" id="commission_type">
                                        <option value="percentage">{{ trans('lang.admin_commission_percentage') }}
                                        </option>
                                        <option value="fixed">{{ trans('lang.admin_commission_fixed') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-4 control-label">{{ trans('lang.admin_commission_value') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control" id="commission_value" min="0">
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-cash-100"></i>{{ trans('lang.platform_fee_setting') }}</legend>
                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="checkbox" class="form-check-inline" id="enable_platform_fee">
                                <label class="col-5 control-label" for="enable_platform_fee">{{ trans('lang.enable_platform_fee') }}</label>
                                <div class="form-text text-muted">
                                    {{ trans('lang.enable_platform_fee_help') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50" id="platform_fee_div" style="display:none;">
                            <label class="col-5 control-label">{{ trans('lang.platform_fee') }}</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="platform_fee" value="" min="0">
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="packingChargeDiv" style="display: none">
                        <legend><i class="mr-3 mdi mdi-cash-100"></i>{{ trans('lang.packaging_charge_setting') }}</legend>
                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="checkbox" class="form-check-inline" id="packagingChargeEnable">
                                <label class="col-5 control-label" for="packagingChargeEnable">{{ trans('lang.packagingChargeEnable') }}</label>
                                <div class="form-text text-muted">
                                    {{ trans('lang.packagingChargeEnable_help') }}
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset id="homepage_theme" class="d-none">
                        <legend>{{ trans('lang.app_homepage_theme') }}</legend>
                        <div class="form-group width-100 choose-theme">
                            <div class="col-12">
                                <div class="select-theme-radio">
                                    <label class="form-check-label" for="app_homepage_theme_1">
                                        <input type="radio" class="btn-check" name="app_homepage_theme" id="app_homepage_theme_1" value="theme_1" checked>
                                        <img src="{{ url('images/app_homepage_theme_1.png') }}" height="150">
                                    </label>
                                    <label class="form-check-label" for="app_homepage_theme_2">
                                        <input type="radio" class="btn-check" name="app_homepage_theme" id="app_homepage_theme_2" value="theme_2">
                                        <img src="{{ url('images/app_homepage_theme_2.png') }}" height="150">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="htmlTemplateDiv" style="display:none">
                        <legend>{{ trans('lang.html_template') }}</legend>
                        <div class="form-group width-100">
                            <textarea class="form-control col-7" name="html_template" id="html_template"></textarea>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        @include('settings.app.partials.region_assignment', [
            'regionFieldLabel' => trans('lang.section_regions'),
            'regionFieldHelp' => trans('lang.section_regions_help'),
        ])

        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary save-setting-btn"><i class="fa fa-save"></i>
                {{ trans('lang.save') }}
            </button>
            <a href="{!! route('section') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
        </div>
    </div>
    </div>
    <div class="modal fade" id="themeModal" tabindex="-1" role="dialog" aria-labelledby="themeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 50%;">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-group">
                        <img id="themeImage" src="" width="630">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        var database = firebase.firestore();
        var ref = database.collection('sections');
        var services = database.collection('services');
        var photo = "";
        var fileName = "";
        var photo_parcel = "";
        var id_section = "<?php echo uniqid(); ?>";
        var category_length = 1;
        var placeholderImage = '';
        var placeholder = database.collection('settings').doc('placeHolderImage');
        var htmlTemplate = "";
        var storageRef = firebase.storage().ref('images');
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })
        var theme_1_url = '{!! url('images/app_homepage_theme_1.png') !!}';
        var theme_2_url = '{!! url('images/app_homepage_theme_2.png') !!}';
        var refDriver = database.collection('settings').doc("DriverNearBy");
        refDriver.get().then(async function(snapshots) {
            var radios = snapshots.data();
            if (radios.hasOwnProperty('distanceType')) {
                $("#set_distance_type").text(radios.distanceType);
            }
        });
        $('#html_template').summernote({
            height: 400,
            width: 1000,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['forecolor', ['forecolor']],
                ['backcolor', ['backcolor']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['view', ['fullscreen', 'codeview', 'help']],
            ]
        });
        $(document).ready(function() {
            jQuery("#data-table_processing").hide();
            loadRegionAssignment(null);
            loadServiceGroups('');
            services.get().then(async function(snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    $('#service_type').append($("<option></option>")
                        .attr("value", data.name).attr("flag", data.flag)
                        .text(data.name));
                })
            });
            $(".save-setting-btn").click(async function() {
                var name = $("#name").val();
                var color = $("#color").val();
                var active = $("#section_active").is(":checked");
                var section_dine_in_active = $("#section_dine_in_active").is(":checked");
                var is_product_details = $("#is_product_details").is(":checked");
                var service_type = $('#service_type').val();
                var service_type_flag = $('#service_type option:selected').attr('flag');
                var referralAmount = $("#referral_amount").val();
                var enable_commission = $("#enable_commission").is(":checked");
                var commission_type = $("#commission_type").val();
                var commission_value = parseInt($("#commission_value").val());
                var markerIcon = $("input[name='marker_icon']:checked").val();
                // var vendor_nearby_radius = parseInt($("#vendor_nearby_radius").val());
                var vendor_nearby_radius = null;
                if (
                    service_type === "Multivendor Delivery Service" ||
                    service_type === "On Demand Service" ||
                    service_type === "Ecommerce Service"
                ) {
                    var inputVal = $("#vendor_nearby_radius").val().trim();
                    vendor_nearby_radius = (inputVal === '' || isNaN(parseInt(inputVal))) ? null : parseInt(inputVal);
                }
                var app_homepage_theme=null;
                if(service_type=='Multivendor Delivery Service'){
                     app_homepage_theme = $(".form-group input[name='app_homepage_theme']:checked").val();
                }
                if (service_type == "Ecommerce Service") {
                    var delivery_charge = $('#deliveryCharge').val();
                } else {
                    var delivery_charge = '';
                }
                if (service_type == "Cab Service") {
                    var htmlTemplate = $('#html_template').summernote('code');
                    var rideType = $('input[name="ride_type"]:checked').val();
                } else {
                    var htmlTemplate = '';
                    var rideType = '';
                }
                if (enable_commission == true) {
                    var adminCommision = {
                        'commission': commission_value,
                        'enable': true,
                        'type': commission_type,
                    };
                } else {
                    var adminCommision = {
                        'commission': 0,
                        'enable': false,
                        'type': null,
                    };
                }
                
                var enable_platform_fee = $('#enable_platform_fee').is(":checked");
                var platform_fee = $('#platform_fee').val();
                var platformFee = enable_platform_fee ? { enable: true, fee: platform_fee }  : { enable: false, fee: "0" };
                var packagingChargeEnable = $("#packagingChargeEnable").is(":checked");

                $(".error_top").hide().html("");
                if (name == '') {
                    $(".error_top").show().append("<p>{{ trans('lang.enter_section_name_error') }}</p>");
                    window.scrollTo(0, 0);
                    return;
                }
                if (service_type == "") {
                    $(".error_top").show().append("<p>{{ trans('lang.service_type_error') }}</p>");
                    window.scrollTo(0, 0);
                    return;
                }
                if (referralAmount == '') {
                    $(".error_top").show().append("<p>{{ trans('lang.enter_referral_amount_error') }}</p>");
                    window.scrollTo(0, 0);
                    return;
                }
                if (enable_commission && (isNaN(commission_value) || commission_value <= 0)) {
                    $(".error_top").show().append("<p>{{ trans('lang.commission_fix_error') }}</p>");
                    window.scrollTo(0, 0);
                    return;
                }
                if (
                    (service_type === "Multivendor Delivery Service" ||
                    service_type === "On Demand Service" ||
                    service_type === "Ecommerce Service") &&
                    (vendor_nearby_radius === null || vendor_nearby_radius <= 0)
                ) {
                    $(".error_top").show().append("<p>{{ trans('lang.enter_vendor_nearby_error') }}</p>");
                    window.scrollTo(0, 0);
                    return;
                }
                const IMG = await storeImageData();

                jQuery("#data-table_processing").show();
                let sectionData = {
                    'id': id_section,
                    'name': name,
                    'color': color,
                    'sectionImage': IMG,
                    'isActive': active,
                    'dine_in_active': section_dine_in_active,
                    'is_product_details': is_product_details,
                    'rideType': rideType,
                    'serviceType': service_type,
                    'serviceTypeFlag': service_type_flag,
                    'serviceGroup': $('#service_group').val() || '',
                    'delivery_charge': delivery_charge,
                    'cab_service_template': htmlTemplate,
                    'referralAmount': referralAmount,
                    'adminCommision': adminCommision,
                    'nearByRadius': vendor_nearby_radius,
                    'theme': app_homepage_theme,
                    'order': 10,
                    'platformFee': platformFee,
                    'packagingChargeEnable': packagingChargeEnable,
                    /* Regions this service is offered in. Empty means every
                     * region, which keeps existing sections working unchanged. */
                    'regionIds': getRegionAssignment(),
                };
                //add this new code 
                if (
                    service_type === "Multivendor Delivery Service" ||
                    service_type === "On Demand Service" ||
                    service_type === "Ecommerce Service"
                ) {
                    sectionData.nearByRadius = vendor_nearby_radius;  // will be number or null
                }
                if (
                    service_type == "Cab Service" ||
                    service_type == "Parcel Delivery Service" ||
                    service_type == "Rental Service"
                ) {
                    sectionData.markerIcon = markerIcon;
                }
                await database.collection('sections').doc(id_section).set(sectionData);
                if (service_type == 'Multivendor Delivery Service' ||
                    service_type == 'On Demand Service' ||
                    service_type == 'Ecommerce Service') {
                    await addCommissionModel(id_section);
                }
                jQuery("#data-table_processing").hide();
                window.location.href = '{{ route('section') }}';
            });
        });
        async function addCommissionModel(sectionId) {
            var tempId = database.collection("tmp").doc().id;
            var features = {
                'chat': true,
                'qrCodeGenerate': true,
                'ownerMobileApp': true
            };
            await database.collection('subscription_plans').doc(tempId).set({
                'createdAt': firebase.firestore.FieldValue.serverTimestamp(),
                'description': 'Commission apply per order',
                'expiryDay': '-1',
                'features': features,
                'id': tempId,
                'image': placeholderImage,
                'isEnable': true,
                'itemLimit': '-1',
                'name': 'Commission Base Plan',
                'orderLimit': '-1',
                'place': '0',
                'plan_points': ['Access to all features easily.'],
                'price': '0',
                'type': 'free',
                'sectionId': sectionId,
                'isCommissionPlan': true
            })
        }
        function handleFileSelect(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = $('#sectionImage').val().toLowerCase();
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = $('#sectionImage').val().replace(/C:\\fakepath\\/i, '')
                    var timestamp = Number(new Date());
                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                    photo = filePayload;
                    fileName = filename;
                    $(".cat_image").empty();
                    $(".cat_image").append(
                        '<span class="image-item" id="photo_user"><span class="remove-btn" data-id="user-remove" data-img="' +
                        photo +
                        '"><i class="fa fa-remove"></i></span><img class="rounded" style="width:50px" src="' +
                        photo + '" alt="image"></span>');
                };
            })(f);
            reader.readAsDataURL(f);
        }
        async function storeImageData() {
            let newPhoto = '';
            try {
                if (!photo || photo.trim() === '') {
                    throw "{{ trans('lang.vendor_image_help') }}"; // show image validation message
                }
                if (photo.startsWith('https://')) {
                    return photo;
                }
                const mimeMatch = photo.match(/^data:(image\/[a-zA-Z0-9+.-]+);base64,/);
                const contentType = mimeMatch ? mimeMatch[1] : 'image/jpeg';
                const allowedTypes = [
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                    'image/gif'
                ];
                if (!allowedTypes.includes(contentType)) {
                    throw "Please upload only JPG, JPEG, PNG, or GIF images.";
                }
                const base64Data = photo.replace(/^data:image\/[a-zA-Z0-9+.-]+;base64,/, "");
                const uploadTask = await storageRef.child(fileName).putString(base64Data, 'base64', {
                    contentType: contentType
                });
                const downloadURL = await uploadTask.ref.getDownloadURL();
                newPhoto = downloadURL;
                photo = downloadURL;
            } catch (err) {
                $(".error_top").show().html("<p>" + err + "</p>");
                window.scrollTo(0, 0);
                throw err; 
            }
            return newPhoto;
        }
        $(document).on("click", ".remove-btn", function() {
            $(".image-item").remove();
            $('#sectionImage').val('');
        });
        $('#enable_commission').click(function() {
            var checkboxValue = $(this).is(":checked");
            if (checkboxValue) {
                $("#show_admin_commision_type_value").show();
            } else {
                $("#show_admin_commision_type_value").hide();
            }
        });
        $("#enable_platform_fee").click(function() {
            if ($(this).is(':checked')) {
                $("#platform_fee_div").show();
            } else {
                $("#platform_fee_div").hide();
            }
        });
        $('.service_type').change(function() {
            var serviceType = $(this).val();
            if (serviceType == "Cab Service") {
                $('.diliverychargeDiv').hide();
                $('.packingChargeDiv').hide();
                $('.htmlTemplateDiv').show();
                $('#div_ride_type').show();
                $('#food_delivery_set').hide();
                $('#radios_set').hide();
                $('#homepage_theme').addClass('d-none');
                $('.marker-icon-div').show();
            } else if (serviceType == "Parcel Delivery Service") {
                $('.diliverychargeDiv').hide();
                $('.packingChargeDiv').hide();
                $('.htmlTemplateDiv').hide();
                $('#div_ride_type').hide();
                $('#food_delivery_set').hide();
                $('#radios_set').hide();
                $('#homepage_theme').addClass('d-none');
                $('.marker-icon-div').show();
            } else if (serviceType == "Ecommerce Service") {
                $('.diliverychargeDiv').show();
                $('.packingChargeDiv').show();
                $('.htmlTemplateDiv').hide();
                $('#div_ride_type').hide();
                $('#food_delivery_set').hide();
                $('#radios_set').show();
                $('#stype').text("{{ trans('lang.store') }}");
                $('#homepage_theme').addClass('d-none');
                $('.marker-icon-div').hide();
            } else if (serviceType == "Rental Service") {
                $('.diliverychargeDiv').hide();
                $('.packingChargeDiv').hide();
                $('.htmlTemplateDiv').hide();
                $('#div_ride_type').hide();
                $('#food_delivery_set').hide();
                $('#radios_set').hide();
                $('#homepage_theme').addClass('d-none');
                $('.marker-icon-div').show();
            } else if (serviceType == "On Demand Service") {
                $('.diliverychargeDiv').hide();
                $('.packingChargeDiv').hide();
                $('.htmlTemplateDiv').hide();
                $('#div_ride_type').hide();
                $('#food_delivery_set').hide();
                $('#radios_set').show();
                $('#stype').text("{{ trans('lang.provider_services') }}");
                $('#homepage_theme').addClass('d-none');
                $('.marker-icon-div').hide();
            } else if (serviceType == "Multivendor Delivery Service") {
                $('.diliverychargeDiv').hide();
                $('.packingChargeDiv').show();
                $('.htmlTemplateDiv').hide();
                $('#div_ride_type').hide();
                $('#food_delivery_set').show();
                $('#radios_set').show();
                $('#stype').text("{{ trans('lang.store') }}");
                $('#homepage_theme').removeClass('d-none');
                $('.marker-icon-div').hide();
            } else {
                $('.diliverychargeDiv').hide();
                $('.packingChargeDiv').hide();
                $('.htmlTemplateDiv').hide();
                $('#div_ride_type').hide();
                $('#food_delivery_set').hide();
                $('#radios_set').hide();
                $('#homepage_theme').addClass('d-none');
                $('.marker-icon-div').hide();
            }
        })
        $(".form-group input[name='app_homepage_theme']").click(function() {
            if ($(this).is(':checked')) {
                var modal = $('#themeModal');
                if ($(this).val() == "theme_1") {
                    modal.find('#themeImage').attr('src', theme_1_url);
                } else {
                    modal.find('#themeImage').attr('src', theme_2_url);
                }
                $('#themeModal').modal('show');
            }
        });
        $('#themeModal').on('hide.bs.modal', function(event) {
            var modal = $(this);
            modal.find('#themeImage').attr('src', '');
        });
    </script>
@endsection
