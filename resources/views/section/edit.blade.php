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
                    <li class="breadcrumb-item active">{{ trans('lang.section_edit') }}</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.section_edit') }}</legend>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.section_name') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="name">
                                <div class="form-text text-muted">{{ trans('lang.section_name_help') }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label ">{{ trans('lang.section_color') }}</label>
                            <div class="col-7">
                                <input type="color" id="color" value="#0000ff">
                                <div class="form-text text-muted">{{ trans('lang.section_color_help') }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
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
                                    <option value="">{{ trans('lang.select') }} {{ trans('lang.service_type') }}</option>
                                </select>
                            </div>
                        </div>
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
                                <input type="radio" class="form-check-inline" name="ride_type" id="ride" value="ride">
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
                            <label class="col-4 control-label"><span id="stype"></span> {{ trans('lang.nearby_radios') }}</label>
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
                                        <option value="percentage">{{ trans('lang.admin_commission_percentage') }}</option>
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
            <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}
            </button>
            <a href="{!! route('section') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
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
        var id = "<?php echo $id; ?>";
        var database = firebase.firestore();
        var ref = database.collection('sections').where("id", "==", id);
        var services = database.collection('services');
        var theme_1_url = '{!! url('images/app_homepage_theme_1.png') !!}';
        var theme_2_url = '{!! url('images/app_homepage_theme_2.png') !!}';
        var htmlTemplate = ""
        var sectionImage = "";
        var placeholderImage = '';
        var placeholder = database.collection('settings').doc('placeHolderImage');
        var storageRef = firebase.storage().ref('images');
        var storage = firebase.storage();
        var photo = "";
        var fileName = "";
        var oldImageFile = '';
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })
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
            jQuery("#data-table_processing").show();
            services.get().then(async function(snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    $('#service_type').append($("<option></option>")
                        .attr("value", data.name).attr("flag", data.flag)
                        .text(data.name));
                })
            })
            ref.get().then(async function(snapshots) {
                if (snapshots.docs) {
                    var section = snapshots.docs[0].data();
                    loadRegionAssignment(section);
                    $("#name").val(section.name);
                    $("#color").val(section.color);
                    if (section.isActive) {
                        $("#section_active").prop('checked', true);
                    }
                    if (section.markerIcon) {
                        $("input[name='marker_icon'][value='" + section.markerIcon + "']").prop("checked", true);
                    }else{
                        $("#sedan").prop("checked", true);
                    }
                    if (section.hasOwnProperty('nearByRadius')) {
                        $("#vendor_nearby_radius").val(section.nearByRadius);
                    }
                    if (section.hasOwnProperty('adminCommision') && section.adminCommision != null && section.adminCommision.enable == true) {
                        $("#enable_commission").prop('checked', true);
                        $("#show_admin_commision_type_value").show();
                        $("#commission_type").val(section.adminCommision.type);
                        $("#commission_value").val(section.adminCommision.commission);
                    }
                    if (section.hasOwnProperty('platformFee') && section.platformFee != null) {
                        if(section.platformFee.enable == true){
                            $("#enable_platform_fee").prop('checked', true);
                            $("#platform_fee_div").show();
                        }
                        $("#platform_fee").val(section.platformFee.fee);
                    }
                    if (section.packagingChargeEnable) {
                        $("#packagingChargeEnable").prop('checked', true);
                    }
                    if (section.dine_in_active) {
                        $("#section_dine_in_active").prop('checked', true);
                    }
                    if (section.is_product_details) {
                        $("#is_product_details").prop('checked', true);
                    }
                    if (section.serviceType) {
                        $('#service_type').val(section.serviceType).trigger('change');
                        if (section.serviceType == "Cab Service") {
                            $('.diliverychargeDiv').hide();
                            if (section.hasOwnProperty('rideType')) {
                                if (section.rideType == "ride") {
                                    $("#ride").prop('checked', true);
                                } else if (section.rideType == "intercity") {
                                    $("#intercity").prop('checked', true);
                                } else if (section.rideType == "both") {
                                    $("#both").prop('checked', true);
                                }
                            }
                            if (section.hasOwnProperty('cab_service_template')) {
                                $('#html_template').summernote("code", section.cab_service_template);
                            }
                        }
                        if (section.serviceType == "Ecommerce Service") {
                            $('.diliverychargeDiv').show();
                            if (section.delivery_charge != '') {
                                $('#deliveryCharge').val(section.delivery_charge);
                            }
                        }
                    }
                    $("#referral_amount").val(section.referralAmount);
                    if (section.theme == "theme_1") {
                        $("#app_homepage_theme_1").prop('checked', true);
                    } else if (section.theme == "theme_2") {
                        $("#app_homepage_theme_2").prop('checked', true);
                    }
                    sectionImage = section.sectionImage;
                    if (sectionImage != '' && sectionImage != null) {
                        photo = sectionImage;
                        oldImageFile = sectionImage;
                        if (sectionImage) {
                            photo = sectionImage;
                        } else {
                            photo = placeholderImage;
                        }
                        $(".cat_image").append('<span class="image-item"><span class="remove-btn"><i class="fa fa-remove"></i></span><img class="rounded" style="width:50px" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image"></span>');
                    } else {
                        $(".cat_image").append('<span class="image-item"><span class="remove-btn"><i class="fa fa-remove"></i></span><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image"></span>');
                    }
                    $("#data-table_processing").hide();
                }
            })
            $(".edit-setting-btn").click(async function() {
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
                var app_homepage_theme = null;
                if (service_type == 'Multivendor Delivery Service') {
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
                
                if (name == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_cat_name_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (service_type == "") {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.service_type_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (referralAmount == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_referral_amount_error') }}</p>");
                    window.scrollTo(0, 0);
                    window.scrollTo(0, 0);
                } else if (enable_commission == true && (isNaN(commission_value) || commission_value <= 0)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.commission_fix_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (
                    (service_type === "Multivendor Delivery Service" ||
                    service_type === "On Demand Service" ||
                    service_type === "Ecommerce Service") &&
                    (vendor_nearby_radius === null || vendor_nearby_radius <= 0)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_vendor_nearby_error') }}</p>");
                    window.scrollTo(0, 0);
                } else {
                    jQuery("#data-table_processing").show();
                    const IMG = await storeImageData();
                    let sectionData = {
                        'name': name,
                        'color': color,
                        'sectionImage': IMG,
                        'isActive': active,
                        'dine_in_active': section_dine_in_active,
                        'is_product_details': is_product_details,
                        'rideType': rideType,
                        'serviceType': service_type,
                        'serviceTypeFlag': service_type_flag,
                        'delivery_charge': delivery_charge,
                        'cab_service_template': htmlTemplate,
                        'referralAmount': referralAmount,
                        'adminCommision': adminCommision,
                        'nearByRadius': vendor_nearby_radius,
                        'theme': app_homepage_theme,
                        'platformFee': platformFee,
                        'packagingChargeEnable': packagingChargeEnable,
                        /* Regions this service is offered in. Empty means every
                         * region, which keeps existing sections working. */
                        'regionIds': getRegionAssignment(),
                    };
                    //add this new code 
                    const radiusServices = ["Multivendor Delivery Service", "On Demand Service", "Ecommerce Service"];
                    const inputVal = $("#vendor_nearby_radius").val().trim();
                    const radiusValue = (inputVal === '' || isNaN(parseInt(inputVal))) ? null : parseInt(inputVal);
                    if (radiusServices.includes(service_type)) {
                        sectionData.nearByRadius = radiusValue;
                    } else {
                        sectionData.nearByRadius = null;
                    }
                    if (
                        service_type == "Cab Service" ||
                        service_type == "Parcel Delivery Service" ||
                        service_type == "Rental Service"
                    ) {
                        sectionData.markerIcon = markerIcon;
                    }
                    database.collection('sections').doc(id).update(sectionData).then(async function(result) {
                        jQuery("#data-table_processing").hide();
                        window.location.href = '{{ route('section') }}';
                    })
                }
            });
        });
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
                    if (filePayload) {
                        photo1 = filePayload;
                    } else {
                        photo1 = placeholderImage;
                    }
                    $(".cat_image").append('<span class="image-item" id="photo_user"><span class="remove-btn" data-id="user-remove" data-img="' + photo + '"><i class="fa fa-remove"></i></span><img class="rounded" style="width:50px" src="' + photo1 + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image"></span>');
                };
            })(f);
            reader.readAsDataURL(f);
        }
        async function storeImageData() {
            var newPhoto = '';
            try {
                if (oldImageFile != "" && photo != oldImageFile) {
                    var oldImageUrl = await storage.refFromURL(oldImageFile);
                    imageBucket = oldImageUrl.bucket;
                    var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                    if (imageBucket == envBucket) {
                        await oldImageUrl.delete().then(() => {
                            console.log("Old file deleted!")
                        }).catch((error) => {
                            console.log("ERR File delete ===", error);
                        });
                    } else {
                        console.log('Bucket not matched');
                    }
                }
                if (photo != oldImageFile) {
                    photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")
                    var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', {
                        contentType: 'image/jpg'
                    });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto = downloadURL;
                    photo = downloadURL;
                } else {
                    newPhoto = photo;
                }
            } catch (error) {
                console.log("ERR ===", error);
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
