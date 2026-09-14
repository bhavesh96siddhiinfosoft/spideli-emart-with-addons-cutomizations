@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{trans('lang.tax_plural')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('tax') !!}">{{trans('lang.tax_plural')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.tax_create')}}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card pb-4">
                <div class="card-body">
                    <div class="error_top"></div>
                    <div class="row vendor_payout_create">
                        <div class="vendor_payout_create-inner">
                            <fieldset>
                                <legend>{{trans('lang.tax_details')}}</legend>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.tax_title')}}<span
                                                class="required-field"></span></label>
                                    <div class="col-7">
                                        <input type="text" class="form-control tax_title">
                                        <div class="form-text text-muted">
                                            {{ trans("lang.tax_title_help") }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.tax_type')}}<span
                                                class="required-field"></span></label>
                                    <div class="col-7">
                                        <select class="form-control tax_type">
                                            <option value="fix">
                                                {{trans('lang.fix')}}
                                            </option>
                                            <option value="percentage">
                                                {{trans('lang.percentage')}}
                                            </option>
                                        </select>
                                        <div class="form-text text-muted">
                                            {{ trans("lang.tax_type_help") }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.tax_scope')}}<span
                                                class="required-field"></span></label>
                                    <div class="col-7">
                                        <select class="form-control tax_scope">
                                            <option value="">
                                                {{trans('lang.select_tax_scope')}}
                                            </option>
                                            <option value="admin_commission">
                                                {{trans('lang.admin_commission_tax')}}
                                            </option>
                                            <option value="delivery" id="delivery_tax">
                                                {{trans('lang.delivery_wise_tax')}}
                                            </option>
                                            <option value="order">
                                                {{trans('lang.order_wise_tax')}}
                                            </option>
                                            <option value="packaging" id="packaging_tax">
                                                {{trans('lang.packaging_wise_tax')}}
                                            </option>
                                            <option value="platform">
                                                {{trans('lang.platform_wise_tax')}}
                                            </option>
                                            <option value="product" id="product_tax">
                                                {{trans('lang.product_wise_tax')}}
                                            </option>
                                            <option value="vendor_subscription" id="vendor_subscription_tax">
                                                {{trans('lang.vendor_subscription_tax')}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                 <div class="form-group row width-50 country_div">
                                    <label class="col-3 control-label">{{trans('lang.country')}}<span
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
                                        <select name="country" id="country" class="form-control tax_country">
                                            @foreach($countries_data as $country)
                                                <option
                                                        value="{{$country->countryName}}">{{$country->countryName}} +({{ $country->phoneCode }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.tax_amount')}}<span
                                                class="required-field"></span></label>
                                    <div class="col-7">
                                        <input type="number" class="form-control tax_amount" min="0">
                                        <div class="form-text text-muted w-50">
                                            {{ trans("lang.tax_amount_help") }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <div class="form-check">
                                        <input type="checkbox" class="tax_active" id="tax_active">
                                        <label class="col-3 control-label"
                                               for="tax_active">{{trans('lang.enable')}}</label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="button" class="btn btn-primary  save-setting-btn"><i class="fa fa-save"></i> {{
                        trans('lang.save')}}
                        </button>
                        <a href="{!! route('tax') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
                        trans('lang.cancel')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')

<link href="{{ asset('assets/plugins/select2/dist/css/select2.min.css')}}" rel="stylesheet">
<script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>

<script>

    var sectionId = getCookie('section_id') || null;
    var serviceType = getCookie('service_type') || null;
    var database = firebase.firestore();
    var globalSettingsRef = database.collection('settings').doc("globalSettings");
    var newcountriesjs = '<?php echo json_encode($newcountriesjs); ?>';
    var newcountriesjs = JSON.parse(newcountriesjs);
    
    // Load countries from JSON
    var countries = <?php echo json_encode($countries); ?>;
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
        var baseUrl = "<?php echo URL::to('/'); ?>/flags/120/";
        var $state = $(
            '<span><img class="img-flag" /> <span></span></span>'
        );
        $state.find("span").text(state.text);
        $state.find("img").attr("src", baseUrl + "/" + newcountriesjs[state.element.value].toLowerCase() + ".png");
        return $state;
    }

    $(document).ready(function () {
    // Initialize Select2 for country dropdown
        if(serviceType != "delivery-service" && serviceType != "ecommerce-service"){
            $("#delivery_tax, #packaging_tax, #product_tax").hide();
        }
        if(serviceType != "delivery-service" && serviceType != "ecommerce-service" && serviceType != "ondemand-service" ){
            $("#vendor_subscription_tax").hide();
        }
        
        jQuery("#country").select2({
            templateResult: formatState,
            templateSelection: formatState2,
            placeholder: "Select Country",
            allowClear: true
        });
        
        // Fetch default country code from global settings
        globalSettingsRef.get().then(async function (snapshot) {
            var globalSettings = snapshot.data();
            if (globalSettings && globalSettings.defaultCountryCode) {
                // Find the country name corresponding to the defaultCountryCode
                var defaultCountryName = null;
                for (var i = 0; i < countries.length; i++) {
                    if (countries[i].phoneCode === globalSettings.defaultCountryCode.replace('+', '')) {
                        defaultCountryName = countries[i].countryName;
                        break;
                    }
                }
                if (defaultCountryName) {
                    // Set the default country in the select field
                    $('.tax_country').val(defaultCountryName).trigger('change');
                }
            }
        }).catch(function (error) {
            console.error("Error fetching global settings: ", error);
        });
    
        $('.tax_menu').addClass('active');

         $(document.body).on('change', '.tax_scope', function() {
            if(jQuery(this).val() == "admin_commission" || jQuery(this).val() == "vendor_subscription"){
                $(".country_div").hide();
            }else{
                $(".country_div").show();
            }
        });
    });

    $(".save-setting-btn").click(function () {
        var title = $(".tax_title").val();
        var country = $(".tax_country").val();
        var type = $(".tax_type :selected").val();
        var tax = $(".tax_amount").val();
        var scope = $(".tax_scope :selected").val();
        var enable = false;
        if ($(".tax_active").is(':checked')) {
            enable = true;
        }
        var id = database.collection("tmp").doc().id;
        if (title == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.tax_title_error')}}</p>");
            window.scrollTo(0, 0);
        } else if (tax == '' || tax <= 0) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.tax_amount_error')}}</p>");
            window.scrollTo(0, 0);
        }else if (scope == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.tax_scope_error')}}</p>");
            window.scrollTo(0, 0);
        } else {
            database.collection('tax').doc(id).set({
                'title': title,
                'country': (scope == "admin_commission" || scope == "vendor_subscription" ? null : country),
                'tax': tax,
                'type': type,
                'id': id,
                'enable': enable,
                'sectionId': sectionId,
                'scope': scope,
            }).then(function (result) {
                window.location.href = '{{ route("tax")}}';
            }).catch(function (error) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>" + error + "</p>");
            });
        }
    })
</script>
@endsection
