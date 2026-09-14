@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.app_setting_global') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.app_setting_global') }}</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend><i class="mr-3 fa fa-cog"></i>  {{ trans('lang.app_setting_global') }}</legend>
                        <div class="form-group row width-100">
                            <label class="col-5 control-label">{{ trans('lang.app_setting_app_name') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control application_name">
                                <div class="form-text text-muted">
                                    {{ trans('lang.app_setting_app_name_help') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.upload_app_logo') }}</label>
                            <input type="file" class="col-7" onChange="handleFileSelect(event)">
                            <div id="uploding_image"></div>
                            <div class="logo_img_thumb"></div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.menu_placeholder_image') }}</label>
                            <input type="file" class="col-7" onChange="handleFileSelectplaceholder(event)">
                            <div id="uploading_placeholder" class="pl-3"></div>
                            <div class="placeholder_img_thumb">
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.upload_provider_logo') }}</label>
                            <input type="file" class="col-7" onChange="handleFileSelectProviderLogo(event)">
                            <div id="provider_uploding_image" class="pl-3"></div>
                            <div class="provider_logo_img_thumb"></div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.upload_worker_logo') }}</label>
                            <input type="file" class="col-7" onChange="handleFileSelectWorkerLogo(event)">
                            <div id="worker_uploding_image" class="pl-3"></div>
                            <div class="worker_logo_img_thumb"></div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 fa fa-dashboard text-white"></i> {{ trans('lang.panel_color_settings') }}</legend>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.admin_panel_color_settings') }}</label>
                            <input type="color" class="ml-3" name="admin_color" id="admin_color">
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.website_color_settings') }}</label>
                            <input type="color" class="ml-3" name="website_color" id="website_color">
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.store_panel_color_settings') }}</label>
                            <input type="color" class="ml-3" name="store_color" id="store_color">
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.provider_panel_color_settings') }}</label>
                            <input type="color" class="ml-3" name="provider_panel_color" id="provider_panel_color">
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 fa fa-paint-brush text-white"></i> {{ trans('lang.app_color_settings') }}</legend>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.customer_app_color_settings') }}</label>
                            <input type="color" class="ml-3" name="customer_app_color" id="customer_app_color">
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.driver_app_color_settings') }}</label>
                            <input type="color" class="ml-3" name="driver_app_color" id="driver_app_color">
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.store_app_color_settings') }}</label>
                            <input type="color" class="ml-3" name="store_app_color" id="store_app_color">
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.provider_app_color_settings') }}</label>
                            <input type="color" class="ml-3" name="provider_app_color" id="provider_app_color">
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.worker_app_color_settings') }}</label>
                            <input type="color" class="ml-3" name="worker_app_color" id="worker_app_color">
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 fa fa-location-arrow text-white"></i> {{ trans('lang.google_map_api_key_title') }}</legend>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.google_map_api_key') }}</label>
                            <div class="col-7">
                                <input type="password" class="form-control address_line1" name="map_key" id="map_key">
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 fa fa-solid fa-address-book"></i> {{ trans('lang.contact_us') }}</legend>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.contact_us_address') }}</label>
                            <div class="col-7">
                                <textarea class="form-control contact_us_address" rows="3"></textarea>
                                <div class="form-text text-muted">
                                    {{ trans('lang.contact_us_address_help') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.contact_us_email') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control contact_us_email">
                                <div class="form-text text-muted">
                                    {{ trans('lang.contact_us_email_help') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.contact_us_phone') }}</label>
                            <div class="col-7">
                                <input type="number" class="form-control contact_us_phone">
                                <div class="form-text text-muted">
                                    {{ trans('lang.contact_us_phone_help') }}
                                </div>
                            </div>
                        </div>
                        <!-- prachi upadhyay -->
                     <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.default_country')}}<span
                                                class="required-field"></span></label>
                                    <div class="col-7">
                                    <div id="phone-box" class="country-box position-relative">  
                                        <?php
                                        $countries = file_get_contents(public_path('countriesdata.json'));
                                        $countries = json_decode($countries);
                                        $countries = (array) $countries;
                                        $newcountries = array();
                                        $newcountriesjs = array();
                                        foreach ($countries as $keycountry => $valuecountry) {
                                            $newcountries[$valuecountry->code] = $valuecountry;
                                            $newcountriesjs[$valuecountry->countryName] = $valuecountry->code;
                                        }
                                        ?>
                                        <select name="country" id="country" class="form-control defaultCountryCode">
                                            @foreach($newcountries as $code => $country)
                                                <option value="{{ $country->countryName }}" data-code="{{ $code }}" data-phonecode="+{{ $country->phoneCode }}">
                                                    {{ $country->countryName }} +({{ $country->phoneCode }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text text-muted">
                                            {{ trans("lang.default_country_help") }}
                                        </div>
                                    </div>
                                    </div>
                                </div>
                    </fieldset>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 fa fa-map-marker text-white"></i> {{ trans('lang.map_redirection') }}</legend>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.select_map_type_for_application') }}</label>
                            <div class="col-7">
                                <select name="selectedMapType" id="selectedMapType" class="form-control selectedMapType">
                                    <option value="google">{{ trans('lang.google_maps') }}</option>
                                    <option value="osm">{{ trans('lang.open_street_map') }}</option>
                                </select>
                            </div>
                            <div class="form-text pl-3 text-muted">
                                <span><strong>{{ trans('lang.note') }}:</strong>
                                    {{ trans('lang.google_map_note') }}<br>
                                    {{ trans('lang.open_street_map_note') }}<br>
                                    <strong>{{ trans('lang.recommended_note') }}</strong>
                                </span>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.select_map_type') }}</label>
                            <div class="col-7">
                                <select name="map_type" id="map_type" class="form-control map_type">
                                    <option value="">{{ trans('lang.select_type') }}</option>
                                    <option value="google">{{ trans('lang.google_map') }}</option>
                                    <option value="googleGo">{{ trans('lang.google_go_map') }}</option>
                                    <option value="waze">{{ trans('lang.waze_map') }}</option>
                                    <option value="mapswithme">{{ trans('lang.mapswithme_map') }}</option>
                                    <option value="yandexNavi">{{ trans('lang.vandexnavi_map') }}</option>
                                    <option value="yandexMaps">{{ trans('lang.vandex_map') }}</option>
                                    <option value="inappmap">{{ trans('lang.inapp_map') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.driver_location_update') }}</label>
                            <div class="col-7">
                                <input name="radius" id="driver_location_update" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="checkbox" class="form-check-inline" id="single_order_receive">
                                <label class="col-5 control-label" for="single_order_receive">{{ trans('lang.single_order_receive') }}</label>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-cash-100"></i>{{ trans('lang.wallet_settings') }}</legend>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.minimum_deposit_amount') }}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control minimum_deposit_amount">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.minimum_deposit_amount_owner') }}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control minimum_deposit_amount_owner">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.minimum_withdrawal_amount') }}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control minimum_withdrawal_amount">
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-share"></i>{{ trans('lang.digital_product_settings') }}</legend>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.digital_product_max_fileSize') }}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control fileSize">
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-cash"></i>{{ trans('lang.tax_settings') }}</legend>
                         <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.tax_scope') }}</label>
                            <div class="col-7">
                                <select name="selectedMapType" id="tax_scope" class="form-control">
                                    <option value="order">{{ trans('lang.order_wise_tax') }}</option>
                                    <option value="product">{{ trans('lang.product_wise_tax') }}</option>
                                </select>
                                <div class="form-text text-muted">
                                    {{ trans('lang.tax_scope_help') }}
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-shopping"></i>{{ trans('lang.store_settings') }}</legend>
                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="checkbox" class="form-check-inline" id="store_can_upload_story">
                                <label class="col-5 control-label" for="store_can_upload_story">{{ trans('lang.store_can_upload_story') }}</label>
                                <input type="checkbox" class="form-check-inline" id="auto_approve_vendor">
                                <label class="col-5 control-label" for="auto_approve_vendor">{{ trans('lang.auto_approve_vendor') }}</label>
                                <input type="checkbox" class="form-check-inline" id="emaployee_detail_management">
                                <label class="col-5 control-label" for="emaployee_detail_management">{{ trans('lang.employee_management') }}</label>

                            </div>
                        </div>
                        <div class="form-group row width-50" id="story_upload_time_div" style="display:none;">
                            <label class="col-5 control-label">{{ trans('lang.story_upload_time') }}</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="story_upload_time" value="30" min="0">
                                <div class="form-text text-muted">
                                    {{ trans('lang.story_upload_time_help') }}
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-shopping"></i>{{ trans('lang.provider_settings') }}</legend>
                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="checkbox" class="form-check-inline" id="auto_approve_provider">
                                <label class="col-5 control-label" for="auto_approve_provider">{{ trans('lang.auto_approve_provider') }}</label>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-newspaper"></i>{{ trans('lang.advertisement_settings') }}</legend>
                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="checkbox" class="form-check-inline" id="enable_adv_feature">
                                <label class="col-5 control-label" for="enable_adv_feature">{{ trans('lang.enable_adv_feature') }}</label>
                                <div class="form-text text-muted">
                                    {{ trans('lang.enable_adv_feature_help') }}
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-truck-delivery"></i>{{ trans('lang.self_delivery_settings') }}</legend>
                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="checkbox" class="form-check-inline" id="enable_self_delivery">
                                <label class="col-5 control-label" for="enable_self_delivery">{{ trans('lang.enable_self_delivery') }}</label>
                                <div class="form-text text-muted">
                                    {{ trans('lang.enable_self_delivery_help') }}
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-music-box"></i>{{ trans('lang.order_ringtone_settings') }}</legend>
                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="file" id="ringtone_file" onchange="handleRingtoneSelect(event)">
                                <div class="ringtone_file mt-2"></div>
                                <div class="form-text text-muted w-50">{!! nl2br(trans('lang.audio_information')) !!}
                                </div>
                            </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 fa fa-envelope text-white"></i> {{ trans('lang.email_settings') }}</legend>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.smtp') }} {{ trans('lang.from_name') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control from_name">
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.smtp') }} {{ trans('lang.host') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control host">
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.smtp') }} {{ trans('lang.port') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control port">
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.smtp_user_name') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control user_name">
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.smtp') }} {{ trans('lang.password') }}</label>
                            <div class="col-7">
                                <input type="password" class="form-control password">
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-comment-alert"></i>{{ trans('lang.notification_settings') }}</legend>
                        <div class="form-group row width-100">
                            <label class="col-5 control-label">{{ trans('lang.sender_id') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="sender_id">
                            </div>
                            <div class="form-text pl-3 text-muted">
                                {{ trans('lang.notification_sender_id_help') }}
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.upload_json_file') }}</label>
                            <input type="file" class="col-7 pb-2" onChange="handleUploadJsonFile(event)">
                            <div id="uploding_json_file"></div>
                            <div id="uploded_json_file" class="pl-3"></div>
                            <div class="form-text pl-3 text-muted">
                                {{ trans('lang.notification_json_file_help') }}
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><i class="mr-3 fa fa-solid fa fa-android"></i>{{ trans('lang.version') }}</legend>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.app_version') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control app_version">
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.web_version') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="web_version">
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.setting_website_url') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="website_url">
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.setting_store_url') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="store_url">
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.setting_provider_url') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="provider_url">
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-5 control-label">{{ trans('lang.setting_api_secure_key') }}</label>
                            <div class="col-7">
                                <input type="password" class="form-control" id="api_secure_key">
                                <div class="form-text text-muted">{{ trans("lang.setting_api_secure_key_help") }}</div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
            <a href="{{ url('/dashboard') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}
            </a>
        </div>
    </div>
@endsection
@section('scripts')

    <script type="text/javascript">

        var database = firebase.firestore();
        var ref = database.collection('settings').doc("globalSettings");
        var mapKey = database.collection('settings').doc("googleMapKey");
        var refPlaceholderImage = database.collection('settings').doc("placeHolderImage");
        var contactUs = database.collection('settings').doc("ContactUs");
        var version = database.collection('settings').doc("Version");
        var story = database.collection('settings').doc("story");
        var vendor = database.collection('settings').doc("vendor");
        var provider = database.collection('settings').doc("provider");
        var DriverNearByRef = database.collection('settings').doc("DriverNearBy");
        var digitalProductRef = database.collection('settings').doc("digitalProduct");
        var refCurrency = regionCurrencyRef();
        var refEmailSetting = database.collection('settings').doc("emailSetting");
        var refNotificationSetting = database.collection('settings').doc("notification_setting");
        
        var homepagethemeRef = database.collection('settings').doc("home_page_theme");
        var services = database.collection('sections').where('isActive', '==', true).orderBy('order');

        var storageRef = firebase.storage().ref('images');
        var storageAudioRef = firebase.storage().ref('audio');
        var audioData = '';
        var audioFileName = '';
        var oldAudioData = '';
        
        var newcountriesjs = '<?php echo json_encode($newcountriesjs); ?>';
        var newcountriesjs = JSON.parse(newcountriesjs);
        
        function formatState(state) {
            if (!state.id) {
                return state.text;
            }
            var baseUrl = "<?php echo URL::to('/'); ?>/flags/120/";
            var $state = $(
                '<span><img src="' + baseUrl + '/' + newcountriesjs[state.element.value].toLowerCase() + '.png" class="img-flag" /> ' + state.text + '</span>'
            );
            return $state;
        }
        
        function formatState2(state) {
            if (!state.id) {
                return state.text;
            }
            var baseUrl = "<?php echo URL::to('/'); ?>/flags/120/"
            var $state = $(
                '<span><img class="img-flag" /> <span></span></span>'
            );
            $state.find("span").text(state.text);
            $state.find("img").attr("src", baseUrl + "/" + newcountriesjs[state.element.value].toLowerCase() + ".png");
            return $state;
        }

        $(document).ready(function () {
            services.get().then(async function (snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    $('.service_type').append($("<option></option>")
                        .attr("value", data.id)
                        .text(data.name));
                })
            });
            jQuery("#country").select2({
                templateResult: formatState,
                templateSelection: formatState2,
                placeholder: "Select Country",
                allowClear: true
            });
            $('.tax_menu').addClass('active');
        });

        refCurrency.get().then(async function(snapshots) {
            var currencyData = snapshots.docs[0].data();
            $(".currentCurrency").text(currencyData.symbol);
        });
        
        var photo = "";
        var placeholderphoto = '';
        var providerLogo = '';
        var workerLogo = '';
        var serviceJsonFile = '';
        
        $(document).ready(function() {

            jQuery("#data-table_processing").show();

            ref.get().then(async function(snapshots) {

                var globalSettings = snapshots.data();
                $(".application_name").val(globalSettings.applicationName);
                $("#website_color").val(globalSettings.website_color);
                $("#admin_color").val(globalSettings.admin_panel_color);
                $("#store_color").val(globalSettings.store_panel_color);
                $("#customer_app_color").val(globalSettings.app_customer_color);
                $("#driver_app_color").val(globalSettings.app_driver_color);
                $("#store_app_color").val(globalSettings.app_store_color);
                $("#provider_app_color").val(globalSettings.provider_app_color);
                $("#worker_app_color").val(globalSettings.worker_app_color);
                $("#provider_panel_color").val(globalSettings.provider_panel_color);
                $("#api_secure_key").val(shortString(globalSettings.apiSecureKey));
                $("#tax_scope").val(globalSettings.taxScope);
                if (globalSettings.defaultCountryCode) {
                    var selectedCountry = $('.defaultCountryCode option').filter(function() {
                    return $(this).data('phonecode') === globalSettings.defaultCountryCode;
                    }).val();
                    $('.defaultCountryCode').val(selectedCountry).trigger('change');
                }

                photo = globalSettings.appLogo;
                $(".logo_img_thumb").append('<img class="rounded" style="width:50px" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + placeholderphoto + '\'" alt="image">');
                providerLogo = globalSettings.providerLogo;
                $(".provider_logo_img_thumb").html('<img class="rounded" style="width:100px;padding:15px" src="' + providerLogo + '" onerror="this.onerror=null;this.src=\'' + placeholderphoto + '\'" alt="image">');
                workerLogo = globalSettings.workerLogo;
                $(".worker_logo_img_thumb").html('<img class="rounded" style="width:100px;padding:15px" src="' + workerLogo + '" onerror="this.onerror=null;this.src=\'' + placeholderphoto + '\'" alt="image">');
                (globalSettings.isEnableAdsFeature) ? $('#enable_adv_feature').prop('checked', true): '';
                (globalSettings.isSelfDelivery) ? $('#enable_self_delivery').prop('checked', true): '';
                if (globalSettings.order_ringtone_url != '' && globalSettings.order_ringtone_url != null) {
                    audioData = globalSettings.order_ringtone_url;
                    oldAudioData = globalSettings.order_ringtone_url;
                    var html = '<div class="col-md-3">\n' +
                        '<div class="audio-inner">\n' +
                        '  <audio controls>\n' +
                        '    <source src="' + audioData + '" type="audio/mp3">\n' +
                        '    Your browser does not support the audio element.\n' +
                        '  </audio>\n' +
                        '</div>\n' +
                        '</div>';
                    $(".ringtone_file").html(html);
                }
            });
            
            refPlaceholderImage.get().then(async function(snapshots) {
                var placeholderImage = snapshots.data();
                placeholderphoto = placeholderImage.image;
                $(".placeholder_img_thumb").append('<img class="rounded" style="width:50px" src="' + placeholderphoto + '" alt="image">');
            });

            contactUs.get().then(async function(snapshots) {
                var contactUsData = snapshots.data();
                $('.contact_us_address').val(contactUsData.Address);
                $('.contact_us_email').val(contactUsData.Email);
                $('.contact_us_phone').val(contactUsData.Phone);
            });

            vendor.get().then(async function(snapshots) {
                var vendorData = snapshots.data();
                if (vendorData.auto_approve_vendor) {
                    $("#auto_approve_vendor").prop('checked', true);
                }
                if (vendorData.isEmployeeManagement) {
                    $("#emaployee_detail_management").prop('checked', true);
                }

            });

            provider.get().then(async function(snapshots) {
                var providerData = snapshots.data();
                if (providerData.auto_approve_provider) {
                    $("#auto_approve_provider").prop('checked', true);
                }
            })

            story.get().then(async function(snapshots) {
                var story_data = snapshots.data();
                if (story_data.isEnabled) {
                    $("#store_can_upload_story").prop('checked', true);
                    $("#story_upload_time_div").show();
                }
                $("#story_upload_time").val(story_data.videoDuration);
            });

            version.get().then(async function(snapshots) {
                var version_data = snapshots.data();
                $('.app_version').val(version_data.app_version);
                $('#web_version').val(version_data.web_version);
                $('#store_url').val(version_data.storeUrl);
                $('#website_url').val(version_data.websiteUrl);
                $('#provider_url').val(version_data.providerUrl);
            });

            mapKey.get().then(async function(snapshots) {
                var key = snapshots.data();
                $('#map_key').val(shortString(key.key));
            });

            DriverNearByRef.get().then(async function(snapshots) {
                var DriverNearData = snapshots.data();
                $(".minimum_deposit_amount").val(DriverNearData.minimumDepositToRideAccept);
                $(".minimum_deposit_amount_owner").val(DriverNearData.ownerMinimumDepositToRideAccept);
                $(".minimum_withdrawal_amount").val(DriverNearData.minimumAmountToWithdrawal);
                if (DriverNearData.mapType) {
                    $('#map_type').val(DriverNearData.mapType).trigger('change');
                }
                if (DriverNearData.selectedMapType) {
                    $('#selectedMapType').val(DriverNearData.selectedMapType).trigger('change');
                }
                if (DriverNearData.driverLocationUpdate) {
                    $('#driver_location_update').val(DriverNearData.driverLocationUpdate);
                }
                if (DriverNearData.singleOrderReceive) {
                    $('#single_order_receive').prop('checked', true);
                } else {
                    $('#single_order_receive').prop('checked', false);
                }
            });

            digitalProductRef.get().then(async function(snapshots) {
                var digitalProductData = snapshots.data();
                $(".fileSize").val(digitalProductData.fileSize);
            });

            refEmailSetting.get().then(async function(snapshots) {
                var emailSettingData = snapshots.data();
                if (emailSettingData.fromName) {
                    $('.from_name').val(emailSettingData.fromName);
                }
                if (emailSettingData.host) {
                    $('.host').val(shortString(emailSettingData.host));
                }
                if (emailSettingData.port) {
                    $('.port').val(emailSettingData.port);
                }
                if (emailSettingData.userName) {
                    $('.user_name').val(shortString(emailSettingData.userName));
                }
                if (emailSettingData.password) {
                    $('.password').val(shortString(emailSettingData.password));
                }
            });

            refNotificationSetting.get().then(async function(snapshots) {
                var notificationData = snapshots.data();
                if (notificationData.senderId != '' && notificationData.senderId != null) {
                    $('#sender_id').val(shortString(notificationData.senderId));
                }
                if (notificationData.serviceJson != '' && notificationData.serviceJson != null) {
                    $('#uploded_json_file').html("File Uploaded");
                    serviceJsonFile = notificationData.serviceJson;
                }
                jQuery("#data-table_processing").hide();
            });

            $(".edit-setting-btn").click(async function() {
                var website_color = $("#website_color").val();
                var admin_color = $("#admin_color").val();
                var store_color = $("#store_color").val();
                var googleApiKey = $("#map_key").val();
                var contact_us_address = $('.contact_us_address').val();
                var contact_us_email = $('.contact_us_email').val();
                var contact_us_phone = $('.contact_us_phone').val();
                var defaultCountryCode = $('.defaultCountryCode').find(':selected').data('phonecode');
                var app_version = $('.app_version').val();
                var web_version = $('#web_version').val();
                var website_url = $('#website_url').val();
                var store_url = $('#store_url').val();
                var provider_url = $('#provider_url').val();
                var auto_approve_vendor = $("#auto_approve_vendor").is(":checked");
                var isEmployeeManagement = $("#emaployee_detail_management").is(":checked");
                var auto_approve_provider = $("#auto_approve_provider").is(":checked");
                var emaployee_detail_management = $("#emaployee_detail_management").is(":checked");
                var store_can_upload_story = $("#store_can_upload_story").is(":checked");
                var story_upload_time = parseInt($('#story_upload_time').val());
                var minimumDepositToRideAccept = $(".minimum_deposit_amount").val();
                var ownerMinimumDepositToRideAccept = $(".minimum_deposit_amount_owner").val();
                var minimumAmountToWithdrawal = $(".minimum_withdrawal_amount").val();
                var fileSize = $(".fileSize").val();
                var fromName = $('.from_name').val();
                var host = $('.host').val();
                var port = $('.port').val();
                var userName = $('.user_name').val();
                var password = $('.password').val();
                var customer_app_color = $("#customer_app_color").val();
                var driver_app_color = $("#driver_app_color").val();
                var store_app_color = $("#store_app_color").val();
                var provider_app_color = $("#provider_app_color").val();
                var worker_app_color = $("#worker_app_color").val();
                var provider_panel_color = $("#provider_panel_color").val();
                var senderId = $("#sender_id").val();
                var enable_adv_feature = $("#enable_adv_feature").is(":checked");
                var enable_self_delivery = $("#enable_self_delivery").is(":checked");
                if (admin_color != null) {
                    setCookie('admin_panel_color', admin_color, 365);
                }
                var applicationName = $(".application_name").val();
                var selectedMapType = $("#selectedMapType").val();
                var driver_location_update = $('#driver_location_update').val();
                var single_order_receive = $("#single_order_receive").is(":checked");
                var map_type = $('#map_type').val();

                var api_base_url = "{{ url('/') }}";
                var api_secure_key = $('#api_secure_key').val();
                var tax_scope = $('#tax_scope').val();
                
                if (applicationName == '') {
                    alert("Please enter application name");
                } else if (minimumDepositToRideAccept == '' || ownerMinimumDepositToRideAccept == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_minimum_deposit_amount_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (minimumAmountToWithdrawal == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_minimum_withdrawal_amount_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (fileSize == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_digital_product_filesize_error') }}</p>");
                    window.scrollTo(0, 0);
                    window.scrollTo(0, 0);
                } else if (host == "") {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.host_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (port == "") {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.port_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (userName == "") {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.username_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (password == "") {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.password_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (senderId == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.notification_sender_id_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (serviceJsonFile == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.notification_service_json_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (defaultCountryCode == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>Please enter default country</p>");
                    window.scrollTo(0, 0);
                } else if (api_secure_key == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.setting_api_secure_key_error') }}</p>");
                    window.scrollTo(0, 0);
                } else {
                    jQuery("#data-table_processing").show();
                    await storeRingtone().then(ringtone => {
                        database.collection('settings').doc("globalSettings").update({
                            'website_color': website_color,
                            'admin_panel_color': admin_color,
                            'store_panel_color': store_color,
                            'applicationName': applicationName,
                            'appLogo': photo,
                            'app_customer_color': customer_app_color,
                            'app_driver_color': driver_app_color,
                            'app_store_color': store_app_color,
                            'provider_app_color': provider_app_color,
                            'worker_app_color': worker_app_color,
                            'provider_panel_color': provider_panel_color,
                            'workerLogo': workerLogo,
                            'providerLogo': providerLogo,
                            'isEnableAdsFeature': enable_adv_feature,
                            'isSelfDelivery': enable_self_delivery,
                            'order_ringtone_url': ringtone,
                            'apiBaseUrl': api_base_url,
                            'apiSecureKey': api_secure_key,
                            'taxScope': tax_scope,
                            'defaultCountryCode': defaultCountryCode
                        });
                    }).catch(err => {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + err + "</p>");
                        window.scrollTo(0, 0);
                    });
                    database.collection('settings').doc('placeHolderImage').update({
                        'image': placeholderphoto
                    });
                    database.collection('settings').doc("ContactUs").update({
                        'Address': contact_us_address,
                        'Email': contact_us_email,
                        'Phone': contact_us_phone
                    });
                    database.collection('settings').doc("vendor").update({
                        'auto_approve_vendor': auto_approve_vendor,
                        'isEmployeeManagement': emaployee_detail_management,
                    });
                    database.collection('settings').doc("provider").update({
                        'auto_approve_provider': auto_approve_provider,
                    });
                    database.collection('settings').doc("story").update({
                        'isEnabled': store_can_upload_story,
                        'videoDuration': story_upload_time,
                    });
                    database.collection('settings').doc("Version").update({
                        'app_version': app_version,
                        'web_version': web_version,
                        'websiteUrl': website_url,
                        'storeUrl': store_url,
                        'providerUrl': provider_url,
                    });
                    database.collection('settings').doc("googleMapKey").update({
                        'key': googleApiKey,
                    });
                    database.collection('settings').doc("DriverNearBy").update({
                        'minimumDepositToRideAccept': minimumDepositToRideAccept,
                        'ownerMinimumDepositToRideAccept': ownerMinimumDepositToRideAccept,
                        'minimumAmountToWithdrawal': minimumAmountToWithdrawal,
                        'selectedMapType': selectedMapType,
                        'driverLocationUpdate': driver_location_update,
                        'singleOrderReceive': single_order_receive,
                        'mapType': map_type,
                    });
                    database.collection('settings').doc("digitalProduct").update({
                        'fileSize': fileSize,
                    });
                    database.collection('settings').doc("notification_setting").update({
                        'senderId': senderId,
                        'serviceJson': serviceJsonFile,
                    });
                    database.collection('settings').doc("emailSetting").update({
                        'fromName': fromName,
                        'host': host,
                        'port': port,
                        'userName': userName,
                        'password': password,
                        'mailMethod': "smtp",
                        'mailEncryptionType': "ssl",
                    }).then(function(result) {
                        window.location.href = '{{ url('settings/app/globals') }}';
                    });
                }
            })
        })
        
        $("#store_can_upload_story").click(function() {
            if ($(this).is(':checked')) {
                $("#story_upload_time_div").show();
            } else {
                $("#story_upload_time_div").hide();
            }
        });

        function handleFileSelect(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var timestamp = Number(new Date());
                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                    var uploadTask = storageRef.child(filename).put(theFile);
                    uploadTask.on('state_changed', function(snapshot) {
                        var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                        jQuery("#uploding_image").text("Image is uploading...");
                    }, function(error) {
                    }, function() {
                        uploadTask.snapshot.ref.getDownloadURL().then(function(downloadURL) {
                            jQuery("#uploding_image").text("Upload is completed");
                            photo = downloadURL;
                        });
                    });
                };
            })(f);
            reader.readAsDataURL(f);
        }
        function handleFileSelectplaceholder(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var timestamp = Number(new Date());
                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                    var uploadTask = storageRef.child(filename).put(theFile);
                    uploadTask.on('state_changed', function(snapshot) {
                        var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                        console.log('Upload is ' + progress + '% done');
                        jQuery("#uploading_placeholder").text("Image is uploading...");
                    }, function(error) {
                    }, function() {
                        uploadTask.snapshot.ref.getDownloadURL().then(function(downloadURL) {
                            jQuery("#uploading_placeholder").text("Upload is completed");
                            placeholderphoto = downloadURL;
                        });
                    });
                };
            })(f);
            reader.readAsDataURL(f);
        }
        function handleFileSelectProviderLogo(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var timestamp = Number(new Date());
                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                    var uploadTask = storageRef.child(filename).put(theFile);
                    uploadTask.on('state_changed', function(snapshot) {
                        var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                        jQuery("#provider_uploding_image").text("Image is uploading...");
                    }, function(error) {
                    }, function() {
                        uploadTask.snapshot.ref.getDownloadURL().then(function(downloadURL) {
                            jQuery("#provider_uploding_image").text("Upload is completed");
                            providerLogo = downloadURL;
                            $(".provider_logo_img_thumb").html('<img class="rounded" style="width:100px;padding:15px" src="' + providerLogo + '" onerror="this.onerror=null;this.src=\'' + placeholderphoto + '\'" alt="image">');
                        });
                    });
                };
            })(f);
            reader.readAsDataURL(f);
        }
        function handleFileSelectWorkerLogo(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var timestamp = Number(new Date());
                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                    var uploadTask = storageRef.child(filename).put(theFile);
                    uploadTask.on('state_changed', function(snapshot) {
                        var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                        jQuery("#worker_uploding_image").text("Image is uploading...");
                    }, function(error) {
                    }, function() {
                        uploadTask.snapshot.ref.getDownloadURL().then(function(downloadURL) {
                            jQuery("#worker_uploding_image").text("Upload is completed");
                            workerLogo = downloadURL;
                            $(".worker_logo_img_thumb").html('<img class="rounded" style="width:100px;padding:15px" src="' + workerLogo + '" onerror="this.onerror=null;this.src=\'' + placeholderphoto + '\'" alt="image">');
                        });
                    });
                };
            })(f);
            reader.readAsDataURL(f);
        }
        function handleUploadJsonFile(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var timestamp = Number(new Date());
                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                    var uploadTask = firebase.storage().ref('/').child(filename).put(theFile);
                    uploadTask.on('state_changed', function(snapshot) {
                        var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                        jQuery("#uploding_json_file").text("File is uploading...");
                    }, function(error) {
                    }, function() {
                        uploadTask.snapshot.ref.getDownloadURL().then(function(downloadURL) {
                            jQuery("#uploding_json_file").text("Upload is completed");
                            serviceJsonFile = downloadURL;
                            setTimeout(function() {
                                jQuery("#uploding_json_file").hide();
                            }, 3000);
                        });
                    });
                };
            })(f);
            reader.readAsDataURL(f);
        }
        async function handleRingtoneSelect(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            var isAudio = document.getElementById('ringtone_file');
            var audioValue = isAudio.value;
            var allowedExtensions = /(\.mp3)$/i;
            if (!allowedExtensions.exec(audioValue)) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>Error: Invalid audio type</p>");
                window.scrollTo(0, 0);
                isAudio.value = '';
                return false;
            }
            var audio = document.createElement('audio');
            audio.preload = 'metadata';
            audio.onloadedmetadata = function() {
                window.URL.revokeObjectURL(audio.src);
                reader.onload = (function(theFile) {
                    return function(e) {
                        var filePayload = e.target.result;
                        var val = f.name;
                        var ext = val.split('.').pop(); // get the extension
                        var filename = val.replace(/C:\\fakepath\\/i, '');
                        var timestamp = Number(new Date());
                        var filenameWithoutExt = filename.split('.').slice(0, -1).join('.');
                        var finalFilename = filenameWithoutExt + "_" + timestamp + '.' + ext;
                        audioData = filePayload;
                        audioFileName = finalFilename;
                        $(".ringtone_file").empty();
                        var html = '<div class="col-md-3">\n' +
                            '<div class="audio-inner">\n' +
                            '  <audio controls>\n' +
                            '    <source src="' + audioData + '" type="audio/mp3">\n' +
                            '    Your browser does not support the audio element.\n' +
                            '  </audio>\n' +
                            '</div>\n' +
                            '</div>';
                        jQuery(".ringtone_file").append(html);
                        $("#ringtone_file").val('');
                    };
                })(f);
                reader.readAsDataURL(f);
            };
            audio.src = URL.createObjectURL(f);
        }
        async function storeRingtone() {
            var newAudioURL = audioData;
            try {
                if (audioData && audioData !== oldAudioData) {
                    if (oldAudioData) {
                        try {
                            var OldImageUrlRef = await storage.refFromURL(oldAudioData);
                            var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                            if (OldImageUrlRef.bucket === envBucket) {
                                await OldImageUrlRef.delete();
                                console.log("Old file deleted!");
                            } else {
                                console.log('Bucket not matched');
                            }
                        } catch (error) {
                            console.log("Error deleting old file:", error);
                        }
                    }
                    var base64String = audioData.split(',')[1];
                    var audioBlob = base64ToBlob(base64String, 'audio/mp3');
                    var uploadTask = storageAudioRef.child(audioFileName).put(audioBlob);
                    newAudioURL = await new Promise((resolve, reject) => {
                        uploadTask.on(
                            'state_changed',
                            (snapshot) => {
                                var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                                console.log(`Upload is ${progress}% done`);
                            },
                            (error) => {
                                console.log("Error uploading video:", error);
                                reject(error); // Reject promise if an error occurs
                            },
                            async () => {
                                try {
                                    let downloadURL = await uploadTask.snapshot.ref.getDownloadURL();
                                    audioData = downloadURL;
                                    console.log("Video available at:", downloadURL);
                                    resolve(downloadURL);
                                } catch (error) {
                                    reject(error);
                                }
                            }
                        );
                    });
                }
            } catch (error) {
                console.log("Error uploading video:", error);
            }
            return newAudioURL;
        }
        function base64ToBlob(base64, contentType) {
            var byteCharacters = atob(base64); // Remove Data URL header
            var byteNumbers = new Array(byteCharacters.length);
            for (var i = 0; i < byteCharacters.length; i++) {
                byteNumbers[i] = byteCharacters.charCodeAt(i);
            }
            var byteArray = new Uint8Array(byteNumbers);
            return new Blob([byteArray], {
                type: contentType
            });
        }
    </script>
@endsection
