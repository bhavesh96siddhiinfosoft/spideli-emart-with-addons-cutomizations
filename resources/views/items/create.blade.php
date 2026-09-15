@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <?php if ($id != '') { ?>
                <h3 class="text-themecolor vendor_name_heading"></h3>
                <?php } else { ?>
                <h3 class="text-themecolor">{{ trans('lang.item_plural') }}</h3>
                <?php } ?>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{!! route('dashboard') !!}">{{ trans('lang.dashboard') }}</a>
                    </li>
                    <?php 
                    if ($id != '') { ?>
                    <li class="breadcrumb-item"><a
                            href="{{ route('vendors.items', $id) }}">{{ trans('lang.item_plural') }}</a>
                    </li>
                    <?php } else { ?>
                    <li class="breadcrumb-item"><a href="{!! route('items') !!}">{{ trans('lang.item_plural') }}</a></li>
                    <?php } ?>
                    <li class="breadcrumb-item active">{{ trans('lang.item_create') }}</li>
                </ol>
            </div>
        </div>
        <div>
            <div class="card-body">
                <div class="error_top" style="display:none"></div>
                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">
                        <fieldset>
                            <legend>{{ trans('lang.item_information') }}</legend>
                            <div class="form-group row width-100" id="admin_commision_info">
                                <div class="m-3">
                                    <div class="form-text font-weight-bold text-danger h6">
                                        {{ trans('lang.price_instruction') }}</div>
                                    <div class="form-text font-weight-bold text-danger h6" id="admin_commision"></div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
                                    <div class="col-12"> 
                                        <label class="control-label">{{ trans('lang.item_name') }}</label>
                                        <button type="button" class="btn bg-white text-primary generate_btn_wrapper opacity-1 pl-1 mb-2 auto_fill_title"
                                            data-error="{{ trans('lang.ai_name_error') }}"
                                            data-lang="{{ App::getLocale() }}"
                                            data-route="{{ route('ai.title-auto-fill') }}">
                                            <div class="btn-svg-wrapper">
                                                <img width="18" height="18" class="" src="{{ asset('images/svg/blink-icon-orange.svg') }}" alt="">
                                            </div>
                                            <span class="ai-text-animation d-none" role="status">
                                                {{ trans('lang.ai_just_asecond') }}
                                            </span>
                                            <span class="btn-text">{{ trans('lang.ai_generate') }}</span>
                                        </button>
                                        <div class="col-7 outline-wrapper">
                                            <input type="text" class="form-control" id="item_name" required>
                                        </div>
                                    </div>
                                @else
                                    <label class="control-label col-3">{{ trans('lang.item_name') }}</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control" id="item_name" required>
                                    </div>
                                @endif
                            </div>
                            <div class="form-group row width-100 desciption-wrapper">
                                @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
                                    <div class="col-12"> 
                                        <label class="control-label">{{ trans('lang.item_description') }}</label>
                                        <button type="button" class="btn bg-white text-primary generate_btn_wrapper opacity-1 pl-1 mb-2 auto_fill_description"
                                            data-error="{{ trans('lang.ai_description_error') }}"
                                            data-lang="{{ App::getLocale() }}"
                                            data-route="{{ route('ai.description-auto-fill') }}">
                                            <div class="btn-svg-wrapper">
                                                <img width="18" height="18" class="" src="{{ asset('images/svg/blink-icon-orange.svg') }}" alt="">
                                            </div>
                                            <span class="ai-text-animation d-none" role="status">
                                                {{ trans('lang.ai_just_asecond') }}
                                            </span>
                                            <span class="btn-text">{{ trans('lang.ai_generate') }}</span>
                                        </button>
                                        <div class="col-7 outline-wrapper">
                                            <textarea rows="8" class="form-control" id="item_description"></textarea>
                                        </div>
                                    </div>
                                @else
                                    <label class="control-label col-3">{{ trans('lang.item_description') }}</label>
                                    <div class="col-7">
                                        <textarea rows="8" class="form-control" id="item_description"></textarea>
                                    </div>    
                                @endif
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_price') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control" id="item_price" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" required>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.item_price_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_discount') }}</label>
                                <div class="col-7">
                                    <input class="form-control item_discount" id="item_discount" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');">
                                    <div class="form-text text-muted">
                                        {{ trans('lang.item_discount_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="wholesale_enabled" id="wholesale_enabled">
                                <label class="col-3 control-label"
                                    for="wholesale_enabled">{{ trans('lang.offer_at_wholesale') }}</label>
                                <div class="form-text text-muted">
                                    {{ trans('lang.offer_at_wholesale_help') }}
                                </div>
                            </div>

                            <div class="wholesale_fields" style="display:none;">
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.wholesale_price') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control wholesale_price" id="wholesale_price" min="0">
                                        <div class="form-text text-muted">
                                            {{ trans('lang.wholesale_price_help') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.wholesale_min_qty') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control wholesale_min_qty" id="wholesale_min_qty" min="2">
                                        <div class="form-text text-muted">
                                            {{ trans('lang.wholesale_min_qty_help') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_quantity') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control item_quantity" id="item_quantity" value="-1">
                                    <div class="form-text text-muted">
                                        {{ trans('lang.item_quantity_help') }}
                                    </div>
                                </div>
                            </div>

                            <div class="variation_wrapper">
                                @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
                                <div class="width-100 text-right">
                                    <button type="button" class="btn bg-white text-primary generate_btn_wrapper opacity-1 pl-1 mb-2 variation_setup_auto_fill"
                                        data-error="{{ trans('lang.ai_name_description_error') }}"
                                        data-lang="{{ App::getLocale() }}"
                                        data-route="{{ route('ai.variation-setup-auto-fill') }}">
                                        <div class="btn-svg-wrapper">
                                            <img width="18" height="18" class="" src="{{ asset('images/svg/blink-icon-orange.svg') }}" alt="">
                                        </div>
                                        <span class="ai-text-animation d-none" role="status">
                                            {{ trans('lang.ai_just_asecond') }}
                                        </span>
                                        <span class="btn-text">{{ trans('lang.ai_generate') }}</span>
                                    </button>
                                </div>
                                @endif
                                <div class="outline-wrapper">
                                    <?php if ($id == '') { ?>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{ trans('lang.item_vendor_id') }}</label>
                                        <div class="col-7">
                                            <select id="item_vendor" class="form-control" required>
                                                <option value="">{{ trans('lang.select_vendor') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{ trans('lang.item_category_id') }}</label>
                                        <div class="col-7">
                                            <select id='item_category' class="form-control" required>
                                                <option value="">{{ trans('lang.select_category') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row width-100" id="attributes_div">
                                        <label class="col-3 control-label">{{ trans('lang.item_attribute_id') }}</label>
                                        <div class="col-7">
                                            <select id='item_attribute' class="form-control chosen-select" required multiple="multiple" onchange="selectAttribute();"></select>
                                        </div>
                                    </div>
                                    <div class="form-group row width-100">
                                        <div class="item_attributes" id="item_attributes"></div>
                                        <div class="item_variants" id="item_variants"></div>
                                        <input type="hidden" id="attributes" value="" />
                                        <input type="hidden" id="variants" value="" />
                                    </div>
                                </div>
                            </div>

                            <div class="form-check row width-50 mb-3" id="is_digital_div" style="display: none;">
                                <input type="checkbox" class="is_digital_product" id="is_digital_product">
                                <label class="col-3 control-label"
                                    for="item_publish">{{ trans('lang.item_is_digital') }}</label>
                            </div>
                            <div class="form-group row width-50" id="upload_file_div" style="display: none;">
                                <label class="col-3 control-label">{{ trans('lang.item_upload_file') }}</label>
                                <div class="col-7">
                                    <input type="file" onChange="handleZipUpload(event)" id="digital_product_file">
                                    <div id="uploding_zip" class="placeholder_img_thumb mt-2"></div>
                                    <div class="form-text text-muted max_file_size"></div>
                                    <div class="form-text text-muted">{{ trans('lang.item_upload_file_ext') }}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50 brandDiv" style="display: none;">
                                <label class="col-3 control-label">{{ trans('lang.brand') }}</label>
                                <div class="col-7">
                                    <select id='brand' class="form-control" required>
                                        <option value="">{{ trans('lang.select_brand') }}</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.brand_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.item_image') }}</label>
                                <div class="col-7">
                                    <input type="file" id="product_image">
                                    <div class="placeholder_img_thumb product_image"></div>
                                    <div id="uploding_image"></div>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.item_image_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="item_publish" id="item_publish">
                                <label class="col-3 control-label"
                                    for="item_publish">{{ trans('lang.item_publish') }}</label>
                            </div>
                            <div class="form-check width-100 food_delivery_div d-none">
                                <input type="checkbox" class="item_nonveg" id="item_nonveg">
                                <label class="col-3 control-label" for="item_nonveg">{{ trans('lang.non_veg') }}</label>
                            </div>
                            <div class="form-check width-100 food_delivery_take_away d-none">
                                <input type="checkbox" class="item_take_away_option" id="item_take_away_option">
                                <label class="col-3 control-label"
                                    for="item_take_away_option">{{ trans('lang.item_take_away') }}</label>
                            </div>
                        </fieldset>
                        <fieldset class="product-taxes d-none">
                            <legend>{{ trans('lang.tax_settings') }}</legend>
                            <div class="form-group row">
                                <label class="col-3 control-label">{{ trans('lang.select_taxes') }}</label>
                                <div class="col-7">
                                    <select id="taxes" class="form-control chosen-select" multiple="multiple"></select>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="food_delivery_div ingredients-wrapper">
                            <legend>{{ trans('lang.ingredients') }}</legend>
                            @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
                            <div class="width-100 text-right">
                                <button type="button" class="btn bg-white text-primary generate_btn_wrapper opacity-1 pl-1 mb-2 ingredients_auto_fill"
                                    data-error="{{ trans('lang.ai_ingredients_error') }}"
                                    data-lang="{{ App::getLocale() }}"
                                    data-route="{{ route('ai.ingredients-auto-fill') }}">
                                    <div class="btn-svg-wrapper">
                                        <img width="18" height="18" class="" src="{{ asset('images/svg/blink-icon-orange.svg') }}" alt="">
                                    </div>
                                    <span class="ai-text-animation d-none" role="status">
                                        {{ trans('lang.ai_just_asecond') }}
                                    </span>
                                    <span class="btn-text">{{ trans('lang.ai_generate') }}</span>
                                </button>
                            </div>
                            @endif
                            <div class="outline-wrapper">
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.calories') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control item_calories">
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.grams') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control item_grams">
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.fats') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control item_fats">
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.proteins') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control item_proteins">
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="addons-wrapper">
                            <legend>{{ trans('lang.item_add_one') }}</legend>
                            @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
                            <div class="width-100 text-right">
                                <button type="button" class="btn bg-white text-primary generate_btn_wrapper opacity-1 pl-1 mb-2 addons_auto_fill"
                                    data-error="{{ trans('lang.ai_addons_error') }}"
                                    data-lang="{{ App::getLocale() }}"
                                    data-route="{{ route('ai.addons-auto-fill') }}">
                                    <div class="btn-svg-wrapper">
                                        <img width="18" height="18" class="" src="{{ asset('images/svg/blink-icon-orange.svg') }}" alt="">
                                    </div>
                                    <span class="ai-text-animation d-none" role="status">
                                        {{ trans('lang.ai_just_asecond') }}
                                    </span>
                                    <span class="btn-text">{{ trans('lang.ai_generate') }}</span>
                                </button>
                            </div>
                            @endif
                            <div class="outline-wrapper">
                                <div class="form-group add_ons_list extra-row">
                                </div>
                                <div class="form-group row width-100">
                                    <div class="col-7">
                                        <button type="button" onclick="addOneFunction()" class="btn btn-primary" id="add_one_btn">{{ trans('lang.item_add_one') }}</button>
                                    </div>
                                </div>
                                <div class="form-group row width-100" id="add_ones_div" style="display:none">
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="col-3 control-label">{{ trans('lang.item_title') }}</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control add_ons_title">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="col-3 control-label">{{ trans('lang.item_price') }}</label>
                                            <div class="col-7">
                                                <input type="number" class="form-control add_ons_price">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row save_add_one_btn width-100" style="display:none">
                                    <div class="col-7">
                                        <button type="button" onclick="saveAddOneFunction()" class="btn btn-primary">{{ trans('lang.save_add_ones') }}</button>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        
                        <fieldset class="specification-wrapper">
                            <legend>{{ trans('lang.product_specification') }}</legend>
                            @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
                            <div class="width-100 text-right">
                                <button type="button" class="btn bg-white text-primary generate_btn_wrapper opacity-1 pl-1 mb-2 specification_auto_fill"
                                    data-error="{{ trans('lang.ai_specification_error') }}"
                                    data-lang="{{ App::getLocale() }}"
                                    data-route="{{ route('ai.specification-auto-fill') }}">
                                    <div class="btn-svg-wrapper">
                                        <img width="18" height="18" class="" src="{{ asset('images/svg/blink-icon-orange.svg') }}" alt="">
                                    </div>
                                    <span class="ai-text-animation d-none" role="status">
                                        {{ trans('lang.ai_just_asecond') }}
                                    </span>
                                    <span class="btn-text">{{ trans('lang.ai_generate') }}</span>
                                </button>
                            </div>
                            @endif
                            <div class="outline-wrapper">
                                <div class="form-group product_specification extra-row">
                                </div>
                                <div class="form-group row width-100">
                                    <div class="col-7">
                                        <button type="button" onclick="addProductSpecificationFunction()" class="btn btn-primary" id="add_one_btn">
                                            {{ trans('lang.add_product_specification') }}</button>
                                    </div>
                                </div>
                                <div class="form-group row width-100" id="add_product_specification_div" style="display:none">
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="col-2 control-label">{{ trans('lang.lable') }}</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control add_label">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="col-3 control-label">{{ trans('lang.value') }}</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control add_value">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row save_product_specification_btn width-100" style="display:none">
                                    <div class="col-7">
                                        <button type="button" onclick="saveProductSpecificationFunction()" class="btn btn-primary">{{ trans('lang.save_product_specification') }}</button>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <input type="hidden" class="item_vendor_id" id="item_vendor_id" value="{{ $id }}">
                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary  save-form-btn"><i class="fa fa-save"></i>
                        {{ trans('lang.save') }}
                    </button>
                    <?php if ($id != '') { ?>
                    <a href="{{ route('vendors.items', $id) }}" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                    <?php } else { ?>
                    <a href="{!! route('items') !!}" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    @includeif('layouts.ai_sidebar')

@endsection

@section('scripts')

    @if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
        <link href="{{ asset('css/AI/ai-sidebar.css') }}" rel="stylesheet">
        <script src="{{ asset('js/AI/product-details-autofill.js') }}"></script>
        <script src="{{ asset('js/AI/variation-setup-auto-fill.js') }}"></script>
        <script src="{{ asset('js/AI/ai-sidebar.js') }}"></script>
        <script src="{{ asset('js/AI/compressor/image-compressor.js')}}"></script>
        <script src="{{ asset('js/AI/compressor/compressor.min.js')}}"></script>
    @endif
    
    <script type="text/javascript">
        
        var section_id = getCookie('section_id') || '';
        var vendor_id = "{{ $id }}";

        var database = firebase.firestore();
        var photo = "";
        var digital_product_file = '';
        var digital_product_file_name = '';
        var digital_product_ext = '';
        var addOnesTitle = [];
        var addOnesPrice = [];
        var categories_list = [];
        var brand_list = [];
        var attributes_list = [];
        var vendor_list = [];
        var product_specification = {};
        var photos = [];
        var product_image_filename = [];
        var variant_photos = [];
        var variant_filename = [];
        var variant_vIds = [];
        var productImagesCount = 0;
        var allowed_file_size = '';
        var vendor_section_id = '';
        
        var sections_list = [];
        var itemLimit = '-1';
        var createdItem = 0;
        var subscriptionModel = false;
        var commissionModel = false;
        var vendorId = '';

        var ref_sections = database.collection('sections').where('isActive', '==', true).orderBy('order');

        ref_sections.get().then(async function(snapshots) {
            await snapshots.docs.forEach((listval) => {
                var data = listval.data();
                sections_list.push(data);
            });
            
            var result = sections_list.find(function(e) {
                return e.id === section_id;
            });
            if (result && result.serviceTypeFlag == "delivery-service") {
                $('.food_delivery_take_away').removeClass('d-none');
                if (result && result.is_product_details) {
                    $('.food_delivery_div').removeClass('d-none');
                }
            }
        });

        var refCurrency = regionCurrencyRef();
        refCurrency.get().then(async function(snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        });

        var subscriptionBusinessModel = database.collection('settings').doc("vendor");
        subscriptionBusinessModel.get().then(async function(snapshots) {
            var subscriptionSetting = snapshots.data();
            if (subscriptionSetting.subscription_model == true) {
                subscriptionModel = true;
            }
        });

        var sectionData = '';
        var sectionRef = database.collection('sections').doc(section_id);
        sectionRef.get().then(async function(snapshots) {
            sectionData = snapshots.data();
            if (sectionData.adminCommision.enable == true) {
                commissionModel = true;
            }
            if(sectionData.serviceTypeFlag == "ecommerce-service"){
                $(".brandDiv").show();
                $("#is_digital_div").show();
                $("#upload_file_div").show();
            }else{
                $("#is_digital_product").prop('checked', false);
            }

            if (sectionData.serviceTypeFlag == "delivery-service") {
                $('.food_delivery_take_away').removeClass('d-none');
            } else {
                $('.food_delivery_take_away').addClass('d-none');
            }
            
            if (sectionData.is_product_details) {
                $(".food_delivery_div").removeClass('d-none');
            } else {
                $(".food_delivery_div").addClass('d-none');
            }

            if (sectionData.serviceTypeFlag == "ecommerce-service" || sectionData.serviceTypeFlag == "delivery-service") {
                $("#attributes_div").show();
                $("#item_attribute_chosen").css({
                    'width': '100%'
                });
            }else{
                $("#item_attribute").val('').trigger("chosen:updated");
                $("#attributes_div").hide();
                $("#item_attributes").html('');
                $("#item_variants").html('');
                $("#attributes").val('');
                $("#variants").val('');
                $("#is_digital_product").prop('checked', false);
            }
        });
        
        var reataurantIDDirec = "<?php echo $id; ?>";
        
        
        $(document).ready(async function() {

            jQuery(document).on("click", ".mdi-cloud-upload", function() {
                var variant = jQuery(this).data('variant');
                var photo_remove = $(this).attr('data-img');
                index = variant_photos.indexOf(photo_remove);
                if (index > -1) {
                    variant_photos.splice(index, 1); // 2nd parameter means remove one item only
                }
                var file_remove = $(this).attr('data-file');
                fileindex = variant_filename.indexOf(file_remove);
                if (fileindex > -1) {
                    variant_filename.splice(fileindex, 1); // 2nd parameter means remove one item only
                }
                variantindex = variant_vIds.indexOf(variant);
                if (variantindex > -1) {
                    variant_vIds.splice(variantindex, 1); // 2nd parameter means remove one item only
                }
                $('[id="file_' + variant + '"]').click();
            });

            jQuery(document).on("click", ".mdi-delete", function() {
                var variant = jQuery(this).data('variant');
                $('[id="variant_' + variant + '_image"]').empty();
                var photo_remove = $(this).attr('data-img');
                index = variant_photos.indexOf(photo_remove);
                if (index > -1) {
                    variant_photos.splice(index, 1); // 2nd parameter means remove one item only
                }
                var file_remove = $(this).attr('data-file');
                fileindex = variant_filename.indexOf(file_remove);
                if (fileindex > -1) {
                    variant_filename.splice(fileindex, 1); // 2nd parameter means remove one item only
                }
                variantindex = variant_vIds.indexOf(variant);
                if (variantindex > -1) {
                    variant_vIds.splice(variantindex, 1); // 2nd parameter means remove one item only
                }
            });

            jQuery(document).on("click", "#is_digital_product", function() {
                var selected_section = $('#item_vendor').find('option:selected').attr('data-section-id');
                if (selected_section) {
                    selected_section = selected_section;
                } else {
                    selected_section = vendor_section_id;
                }
                var section_info = $.map(sections_list, function(section, i) {
                    if (section.id == selected_section) {
                        return section;
                    }
                });
                if (jQuery(this).is(':checked') && section_info.length > 0 && (section_info[0].serviceTypeFlag == "ecommerce-service")) {
                    $("#upload_file_div").show();
                } else {
                    $("#upload_file_div").hide();
                }
            });

            jQuery("#data-table_processing").show();
            
            database.collection('vendors').where('section_id', '==', section_id).orderBy('title').where('title','!=', '').get().then(async function(snapshots) {

                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    vendor_list.push(data);
                    $('#item_vendor').append($("<option></option>")
                        .attr("value", data.id)
                        .attr("data-lat", data.latitude)
                        .attr("data-long", data.longitude)
                        .attr("data-section-id", data.section_id)
                        .attr("data-user-id", data.author)
                        .text(data.title));

                    if (reataurantIDDirec == data.id) {

                        vendor_section_id = data.section_id;
                        localStorage.setItem('vendor_section_id', vendor_section_id);

                        $(".vendor_name_heading").html(data.title);
                        var section_info = $.map(sections_list, function(section, i) {
                            if (section.id == data.section_id) {
                                return section;
                            }
                        });
                        if (section_info.length > 0 && (section_info[0].serviceTypeFlag == "ecommerce-service" || section_info[0].serviceTypeFlag == "delivery-service")) {
                            $("#attributes_div").show();
                            $("#item_attribute_chosen").css({
                                'width': '100%'
                            });
                        }
                        if (section_info.length > 0 && (section_info[0].serviceTypeFlag == "ecommerce-service")) {
                            $("#is_digital_div").show();
                        }
                    }
                    if (reataurantIDDirec && reataurantIDDirec !== '') {
                        change_categories(reataurantIDDirec);
                        $(".vendor_name_heading").html($('#item_vendor option[value="' + reataurantIDDirec + '"]').text());
                        $(".item_vendor_id").val(reataurantIDDirec).val();
                    }
                })
            });

            var vendorsCatRef = database.collection('vendor_categories').where('section_id', '==', section_id);
            vendorsCatRef.where('publish', '==', true).get().then(async function(snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    categories_list.push(data);
                })
            });
            
            var brandRef = database.collection('brands').where('sectionId', '==', section_id);
            brandRef.get().then(async function(snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    brand_list.push(data);
                    $('#brand').append($("<option></option>")
                        .attr("value", data.id)
                        .text(data.title));
                })
            });

            var digitalProductRef = database.collection('settings').doc("digitalProduct");
            digitalProductRef.get().then(async function(snapshots) {
                var digitalProductData = snapshots.data();
                allowed_file_size = digitalProductData.fileSize;
                $(".max_file_size").text('{{ trans('lang.item_upload_file_max') }}' +
                    allowed_file_size + 'Mb');
            })

            var attributes = database.collection('vendor_attributes');
            attributes.get().then(async function(snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    attributes_list.push(data);
                    $('#item_attribute').append($("<option></option>")
                        .attr("value", data.id)
                        .text(data.title));
                })
                $("#item_attribute").show().chosen({
                    "placeholder_text": "{{ trans('lang.select_attribute') }}"
                });
            });

            jQuery("#data-table_processing").hide();

            $(".save-form-btn").click(async function() {
                
                var name = $("#item_name").val();
                var price = $("#item_price").val();
                var item_quantity = $("#item_quantity").val();
                var set_vendor_id = vendor_id ? vendor_id : $("#item_vendor option:selected").val();
                var category = $("#item_category").val();
                var section_id = $('#item_category').find('option:selected').attr('section_id');
                var brand = $("#brand").val() || '';
                var itemCalories = parseInt($(".item_calories").val());
                var itemGrams = parseInt($(".item_grams").val());
                var itemProteins = parseInt($(".item_proteins").val());
                var itemFats = parseInt($(".item_fats").val());
                var description = $("#item_description").val();
                var itemPublish = $(".item_publish").is(":checked");
                var nonveg = $(".item_nonveg").is(":checked");
                var veg = !nonveg;
                var itemTakeaway = $(".item_take_away_option").is(":checked");
                var discount = $("#item_discount").val();
                var is_digital_product = $("#is_digital_product").is(":checked");
                var wholesaleEnabled = $("#wholesale_enabled").is(":checked");
                var wholesalePrice = $("#wholesale_price").val();
                var wholesaleMinQty = $("#wholesale_min_qty").val();

                if (discount == '') {
                    discount = "0";
                }
                if (!itemCalories) {
                    itemCalories = 0;
                }
                if (!itemGrams) {
                    itemGrams = 0;
                }
                if (!itemFats) {
                    itemFats = 0;
                }
                if (!itemProteins) {
                    itemProteins = 0;
                }
                if (photos != '') {
                    photo = photos[0]
                }

                let selectedTaxes = [];
                $('#taxes option:selected').each(function() {
                    let taxData = $(this).attr('data-tax');
                    if (taxData) {
                        selectedTaxes.push(JSON.parse(decodeURIComponent(taxData)));
                    }
                });

                var id = database.collection('temp').doc().id;
                
                if (name == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_item_name_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (price == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_item_price_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (price <= 0) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_positive_price_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (wholesaleEnabled && (wholesalePrice == '' || parseFloat(wholesalePrice) <= 0)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.wholesale_price_positive_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (wholesaleEnabled && parseFloat(wholesalePrice) >= parseFloat(price)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.wholesale_price_less_than_price_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (wholesaleEnabled && (wholesaleMinQty == '' || parseInt(wholesaleMinQty) < 2)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.wholesale_min_qty_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (item_quantity == '' || item_quantity < -1) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    if (item_quantity == '') {
                        $(".error_top").append("<p>{{ trans('lang.enter_item_quantity_error') }}</p>");
                    } else {
                        $(".error_top").append("<p>{{ trans('lang.invalid_item_quantity_error') }}</p>");
                    }
                    window.scrollTo(0, 0);
                } else if (set_vendor_id == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.select_vendor_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (category == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.select_item_category_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (brand == '' && sectionData.serviceTypeFlag == "ecommerce-service") {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.select_brand_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (parseInt(price) < parseInt(discount)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.price_should_not_less_then_discount_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (description == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_item_description_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (is_digital_product == true && digital_product_file == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.upload_digital_file_error') }}</p>");
                    window.scrollTo(0, 0);
                } else {

                    var vendorRef = await database.collection('vendors').doc(set_vendor_id).get();
                    var vendorData = vendorRef.data();
                    var userId = vendorData.author;

                    database.collection('vendor_products').where('vendorID', '==', set_vendor_id).get().then(async function(snapshot) {
                        createdItem = snapshot.docs.length;
                    })

                    await database.collection('users').where('id', '==', userId).get().then(async function(snapshots) {
                        var data = snapshots.docs[0].data();
                        if (subscriptionModel || commissionModel) {
                            if (data.hasOwnProperty('subscription_plan') && data
                                .subscription_plan != null && data
                                .subscription_plan != '') {
                                itemLimit = data.subscription_plan.itemLimit;
                            }
                        }
                    });

                    if (!(parseInt(itemLimit) == -1 || parseInt(createdItem) < parseInt(itemLimit))) {
                      $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append(
                            "<p>{{ trans('lang.create_item_limit_exceed') }}</p>"
                        );
                        window.scrollTo(0, 0);
                        return false;
                    } 

                    $(".error_top").hide();

                    var attributes = [];
                    var variants = [];
                    var quantityerror = 0;
                    var priceerror = 0;
                    var wholesalepriceerror = 0;
                    var wholesalepricehigherror = 0;
                    
                    if ($("#item_attribute").val().length > 0) {
                        if ($('#attributes').val().length > 0) {
                            var attributes = $.parseJSON($('#attributes').val());
                        } else {
                            alert('Please add your attribute value');
                            return false;
                        }
                        if ($("#item_attribute").val().length !== attributes.length) {
                            alert('Please add your attribute value');
                            return false;
                        }
                    }
                    
                    if ($('#variants').val().length > 0) {
                        var variantsSet = $.parseJSON($('#variants').val());
                        await storeVariantImageData().then(async (vIMG) => {
                            $.each(variantsSet, function(key, variant) {
                                var variant_id = uniqid();
                                var variant_sku = variant;
                                var variant_price = $('#price_' + variant)
                                    .val();
                                var variant_quantity = $('#qty_' + variant)
                                    .val();
                                var variant_image = $('#variant_' + variant +
                                    '_url').val();
                                var variant_wholesale_price = wholesaleEnabled ?
                                    $('#wholesale_price_' + variant).val() : '';
                                if (variant_image) {
                                    variants.push({
                                        'variant_id': variant_id,
                                        'variant_sku': variant_sku,
                                        'variant_price': variant_price,
                                        'variant_wholesale_price': variant_wholesale_price,
                                        'variant_quantity': variant_quantity,
                                        'variant_image': variant_image
                                    });
                                } else {
                                    variants.push({
                                        'variant_id': variant_id,
                                        'variant_sku': variant_sku,
                                        'variant_price': variant_price,
                                        'variant_wholesale_price': variant_wholesale_price,
                                        'variant_quantity': variant_quantity
                                    });
                                }
                                if (variant_quantity = '' ||
                                    variant_quantity < -1 ||
                                    variant_quantity == 0) {
                                    quantityerror++;
                                }
                                if (variant_price == "" || variant_price <=
                                    0) {
                                    priceerror++;
                                }
                                if (wholesaleEnabled) {
                                    if (variant_wholesale_price == "" ||
                                        parseFloat(variant_wholesale_price) <= 0) {
                                        wholesalepriceerror++;
                                    } else if (parseFloat(variant_wholesale_price) >=
                                        parseFloat(variant_price)) {
                                        wholesalepricehigherror++;
                                    }
                                }
                            });
                        }).catch(err => {
                            jQuery("#data-table_processing").hide();
                            $(".error_top").show();
                            $(".error_top").html("");
                            $(".error_top").append("<p>" + err + "</p>");
                            window.scrollTo(0, 0);
                        });
                    }

                    var item_attribute = null;
                    if (attributes.length > 0 && variants.length > 0) {
                        if (quantityerror > 0) {
                            alert(
                                'Please add your variants quantity it should be -1 or greater than -1'
                            );
                            return false;
                        }
                        if (priceerror > 0) {
                            alert('Please add your variants  Price');
                            return false;
                        }
                        if (wholesalepriceerror > 0) {
                            alert("{{ trans('lang.enter_positive_variant_wholesale_price_error') }}");
                            return false;
                        }
                        if (wholesalepricehigherror > 0) {
                            alert("{{ trans('lang.variant_wholesale_price_less_than_price_error') }}");
                            return false;
                        }
                        var item_attribute = {
                            'attributes': attributes,
                            'variants': variants
                        };
                    }

                    jQuery("#data-table_processing").show();

                    await storeDigitalImageData().then(async (DigitalImg) => {
                        await storeProductImageData().then(async (IMG) => {
                            if (IMG.length > 0) {
                                photo = IMG[0];
                            }
                            var objects = {
                                'name': name,
                                'price': price.toString(),
                                'quantity': parseInt(item_quantity),
                                'disPrice': discount.toString(),
                                'wholesaleEnabled': wholesaleEnabled,
                                'wholesalePrice': wholesaleEnabled ? wholesalePrice.toString() : '',
                                'wholesaleMinQty': wholesaleEnabled ? wholesaleMinQty.toString() : '',
                                'vendorID': set_vendor_id,
                                'categoryID': category,
                                'brandID': brand,
                                'section_id': section_id,
                                'photo': photo,
                                'calories': itemCalories,
                                "grams": itemGrams,
                                'proteins': itemProteins,
                                'fats': itemFats,
                                'description': description,
                                'publish': itemPublish,
                                'nonveg': nonveg,
                                'veg': veg,
                                'addOnsTitle': addOnesTitle,
                                'addOnsPrice': addOnesPrice,
                                'takeawayOption': itemTakeaway,
                                'product_specification': product_specification,
                                'id': id,
                                'item_attribute': item_attribute,
                                'photos': IMG,
                                'isDigitalProduct': is_digital_product,
                                'digitalProduct': DigitalImg,
                                'createdAt': firebase.firestore.FieldValue.serverTimestamp(),
                                'taxSetting': selectedTaxes,
                            };
                            database.collection('vendor_products').doc(id)
                                .set(objects).then(function(result) {
                                    if (reataurantIDDirec) {
                                        window.location.href =
                                            "{{ route('vendors.items', $id) }}";
                                    } else {
                                        window.location.href =
                                            '{{ route('items') }}';
                                    }
                                });
                        }).catch(err => {
                            jQuery("#data-table_processing").hide();
                            $(".error_top").show();
                            $(".error_top").html("");
                            $(".error_top").append("<p>" + err + "</p>");
                            window.scrollTo(0, 0);
                        });
                    }).catch(err => {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + err + "</p>");
                        window.scrollTo(0, 0);
                    });
                }
                
            })
        })
        var storageRef = firebase.storage().ref('images');
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
                            $(".item_image").empty()
                            $(".item_image").append(
                                '<img class="rounded" style="width:50px" src="' + photo +
                                '" alt="image">');
                        });
                    });
                };
            })(f);
            reader.readAsDataURL(f);
        }

        function handleFileSelectProduct(evt) {
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
                        $('.product_image').find(".uploding_image_photos").text(
                            "Image is uploading...");
                    }, function(error) {
                    }, function() {
                        uploadTask.snapshot.ref.getDownloadURL().then(function(downloadURL) {
                            jQuery("#uploding_image").text("Upload is completed");
                            if (downloadURL) {
                                productImagesCount++;
                                photos_html = '<span class="image-item" id="photo_' +
                                    productImagesCount +
                                    '"><span class="remove-btn" data-id="' +
                                    productImagesCount + '" data-img="' + downloadURL +
                                    '"><i class="fa fa-remove"></i></span><img width="100px" id="" height="auto" src="' +
                                    downloadURL + '"></span>';
                                $(".product_image").append(photos_html);
                                photos.push(downloadURL);
                            }
                        });
                    });
                };
            })(f);
            reader.readAsDataURL(f);
        }

        function handleZipUpload(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var size = f.size;
                    var max_file_size = parseInt(allowed_file_size) * 1000000;
                    if (size > max_file_size) {
                        $("#digital_product_file").val('');
                        alert('{{ trans('lang.max_file_limit_error') }}' + allowed_file_size + 'Mb');
                        return false;
                    }
                    if (ext == "jpg" || ext == "jpeg" || ext == "png" || ext == "gif" || ext == "zip" || ext ==
                        "pdf") {
                        var docName = val.split('fakepath')[1];
                        var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                        var timestamp = Number(new Date());
                        var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                        digital_product_file_name = filename;
                        digital_product_file = filePayload;
                        if (ext == "zip") {
                            digital_product_ext = 'zip';
                            $("#uploding_zip").html(
                                '<span class="image-item zip-file"><span class=""   data-file="' +
                                filePayload + '"></span><a href="' + filePayload +
                                '" download><i class="fa fa-file-text" style="font-size:45px"></i></a></span>'
                            );
                        } else if (ext == 'pdf') {
                            digital_product_ext = 'pdf';
                            $("#uploding_zip").html(
                                '<span class="image-item zip-file"><span class=""   data-file="' +
                                filePayload + '"></span><a href="' + filePayload +
                                '" target="_blank"><i class="fa fa-file-text" style="font-size:45px"></i></a></span>'
                            );
                        } else {
                            digital_product_ext = 'image';
                            $("#uploding_zip").html(
                                '<span class="image-item zip-file"><span class=""  data-file="' +
                                filePayload + '"></span><img width="100px" id="" height="auto" src="' +
                                filePayload + '"></span>');
                        }
                        $("#digital_product_file").val('');
                    } else {
                        $("#digital_product_file").val('');
                        alert('{{ trans('lang.enter_valid_file_ext') }}')
                        return false;
                    }
                };
            })(f);
            reader.readAsDataURL(f);
        }

        $(document).on("click", ".remove-btn", function() {
            var id = $(this).attr('data-id');
            var photo_remove = $(this).attr('data-img');
            $("#photo_" + id).remove();
            index = photos.indexOf(photo_remove);
            if (index > -1) {
                photos.splice(index, 1);
            }
        });

        function handleVariantFileSelect(evt, vid) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var timestamp = Number(new Date());
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var filename = 'variant_' + vid + '_' + timestamp + '.' + ext;
                    variant_filename.push(filename);
                    variant_photos.push(filePayload);
                    variant_vIds.push(vid);
                    $('[id="variant_' + vid + '_image"]').empty();
                    $('[id="variant_' + vid + '_image"]').html('<img class="rounded" style="width:50px" src="' +
                        filePayload + '" alt="image"><i class="mdi mdi-delete" data-variant="' + vid +
                        '" data-img="' + filePayload + '" data-file="' + filename + '"></i>');
                    $('#upload_' + vid).attr('data-img', filePayload);
                    $('#upload_' + vid).attr('data-file', filename);
                };
            })(f);
            reader.readAsDataURL(f);
        }
        function addOneFunction() {
            $("#add_ones_div").show();
            $(".save_add_one_btn").show();
        }
        function addProductSpecificationFunction() {
            $("#add_product_specification_div").show();
            $(".save_product_specification_btn").show();
        }
        function addProductimagesFunction() {
            $("#add_product_images_div").show();
            $(".save_product_images_btn").show();
        }
        function saveAddOneFunction() {
            var optiontitle = $(".add_ons_title").val();
            var optionPrice = $(".add_ons_price").val();
            $(".add_ons_price").val('');
            $(".add_ons_title").val('');
            if (optiontitle != '' && optionPrice != '') {
                addOnesPrice.push(optionPrice.toString());
                addOnesTitle.push(optiontitle);
                var index = addOnesTitle.length - 1;
                $(".add_ons_list").append('<div class="row" style="margin-top:5px;" id="add_ones_list_iteam_' + index +
                    '"><div class="col-5"><input class="form-control" type="text" value="' + optiontitle +
                    '" disabled ></div><div class="col-5"><input class="form-control" type="text" value="' +
                    optionPrice +
                    '" disabled ></div><div class="col-2"><button class="btn" type="button" onclick="deleteAddOnesSingle(' +
                    index + ')"><span class="fa fa-trash"></span></button></div></div>');
            } else {
                alert("Please enter Title and Price");
            }
        }
        function saveProductSpecificationFunction() {
            var optionlabel = $(".add_label").val();
            var optionvalue = $(".add_value").val();
            $(".add_label").val('');
            $(".add_value").val('');
            if (optionlabel != '' && optionlabel != '') {
                product_specification[optionlabel] = optionvalue;
                $(".product_specification").append(
                    '<div class="row" style="margin-top:5px;" id="add_product_specification_iteam_' + optionlabel +
                    '"><div class="col-5"><input class="form-control" type="text" value="' + optionlabel +
                    '" disabled ></div><div class="col-5"><input class="form-control" type="text" value="' +
                    optionvalue +
                    '" disabled ></div><div class="col-2"><button class="btn" type="button" onclick=deleteProductSpecificationSingle("' +
                    optionlabel + '")><span class="fa fa-trash"></span></button></div></div>');
            } else {
                alert("Please enter Label and Value");
            }
        }
        function deleteAddOnesSingle(index) {
            addOnesTitle.splice(index, 1);
            addOnesPrice.splice(index, 1);
            $("#add_ones_list_iteam_" + index).hide();
        }
        function deleteProductSpecificationSingle(index) {
            delete product_specification[index];
            $("#add_product_specification_iteam_" + index).hide();
        }
         var item_vendor_id = $(".item_vendor_id").val();
         
         if (item_vendor_id) {
                var selected_vendor = item_vendor_id;
         var vendor_name = $(this).find('option:selected').text();
            $(".vendor_name_heading").html(vendor_name);
            change_categories(selected_vendor);

            database.collection('settings').doc('globalSettings').get().then(async function(snapshots) {
                let globalTax = snapshots.data();
                let vendorLatitude = $("#item_vendor option:selected").data('lat');
                let vendorLongitude = $("#item_vendor option:selected").data('long');
                let countryName = getCookie('vendorCountryName_'+selected_vendor);
                if (!countryName && (vendorLatitude && vendorLongitude)) {
                    countryName = await getCountryFromLatLng(vendorLatitude,vendorLongitude);
                    setCookie('vendorCountryName_'+selected_vendor, countryName, 365);
                }
                if(globalTax.taxScope == "product" && countryName){
                    $(".product-taxes").removeClass('d-none');
                    $('#taxes').chosen('destroy').empty();
                    database.collection('tax').where('enable','==',true).where('scope','==','product').where('country','==',countryName).where('sectionId','==',section_id).get().then(async function(snapshots) {
                        if(snapshots.docs.length > 0){
                            snapshots.docs.forEach((listval) => {
                                var data = listval.data();
                                let taxText = data.title + ' (';
                                if (data.type === 'percentage') {
                                    taxText += data.tax + '%';
                                } else {
                                    if (currencyAtRight) {
                                        taxText += parseFloat(data.tax).toFixed(decimal_degits) + ' ' + currentCurrency;
                                    } else {
                                        taxText += currentCurrency + parseFloat(data.tax).toFixed(decimal_degits);
                                    }
                                }
                                taxText += ')';
                                $('#taxes').append(
                                    $('<option></option>')
                                        .attr('value', data.id)
                                        .attr('data-tax', encodeURIComponent(JSON.stringify(data)))
                                        .text(taxText)
                                );
                            })
                            $('#taxes').chosen({
                                width: '100%',
                                placeholder_text_multiple: '{{ trans('lang.select_taxes') }}',
                            });
                        }else{
                            $(".product-taxes").addClass('d-none');        
                        }
                    });
                }else{
                    $(".product-taxes").addClass('d-none');
                }
            });
            
         }

        $("#item_vendor").change(function() {
            var selected_vendor = this.value || '';
            var vendor_name = $(this).find('option:selected').text();
            $(".vendor_name_heading").html(vendor_name);
            change_categories(selected_vendor);

            database.collection('settings').doc('globalSettings').get().then(async function(snapshots) {
                let globalTax = snapshots.data();
                let vendorLatitude = $("#item_vendor option:selected").data('lat');
                let vendorLongitude = $("#item_vendor option:selected").data('long');
                let countryName = getCookie('vendorCountryName_'+selected_vendor);
                if (!countryName && (vendorLatitude && vendorLongitude)) {
                    countryName = await getCountryFromLatLng(vendorLatitude,vendorLongitude);
                    setCookie('vendorCountryName_'+selected_vendor, countryName, 365);
                }
                if(globalTax.taxScope == "product" && countryName){
                    $(".product-taxes").removeClass('d-none');
                    $('#taxes').chosen('destroy').empty();
                    database.collection('tax').where('enable','==',true).where('scope','==','product').where('country','==',countryName).where('sectionId','==',section_id).get().then(async function(snapshots) {
                        if(snapshots.docs.length > 0){
                            snapshots.docs.forEach((listval) => {
                                var data = listval.data();
                                let taxText = data.title + ' (';
                                if (data.type === 'percentage') {
                                    taxText += data.tax + '%';
                                } else {
                                    if (currencyAtRight) {
                                        taxText += parseFloat(data.tax).toFixed(decimal_degits) + ' ' + currentCurrency;
                                    } else {
                                        taxText += currentCurrency + parseFloat(data.tax).toFixed(decimal_degits);
                                    }
                                }
                                taxText += ')';
                                $('#taxes').append(
                                    $('<option></option>')
                                        .attr('value', data.id)
                                        .attr('data-tax', encodeURIComponent(JSON.stringify(data)))
                                        .text(taxText)
                                );
                            })
                            $('#taxes').chosen({
                                width: '100%',
                                placeholder_text_multiple: '{{ trans('lang.select_taxes') }}',
                            });
                        }else{
                            $(".product-taxes").addClass('d-none');        
                        }
                    });
                }else{
                    $(".product-taxes").addClass('d-none');
                }
            });
        });
        
        function change_categories(selected_vendor) {
            database.collection('vendors').doc(selected_vendor).get().then(async function(snapshot) {
                if (snapshot.exists) {
                    var data = snapshot.data();
                   
                    var categoryIDs = data.categoryID || [];
                    $('#item_category').empty();
                    $('#item_category').append($("<option></option>")
                        .attr("value", "").text("{{ trans('lang.select_category') }}")); //new line added
                    var matched = 0; //new line added
                    categories_list.forEach((val) => {
                        if (categoryIDs.includes(val.id)) {
                            $('#item_category').append($("<option></option>")
                                .attr("value", val.id)
                                .attr("section_id", val.section_id)
                                .text(val.title));
                            matched++; //new line added
                        }
                    })
                    if (matched === 0) {
                        $('#item_category').append($("<option disabled></option>")
                            .text("{{ trans('lang.no_categories_found') }}"));
                    } //new line added
                    $('#item_category').trigger('change');
                }
            })
        }
       
        function selectAttribute(item_attribute = '') {
            if (item_attribute) {
                var item_attribute = $.parseJSON(atob(item_attribute));
            }
            var html = '';
            $("#item_attribute").find('option:selected').each(function() {
                var $this = $(this);
                var selected_options = [];
                if (item_attribute) {
                    $.each(item_attribute.attributes, function(index, attribute) {
                        if ($this.val() == attribute.attribute_id) {
                            selected_options.push(attribute.attribute_options);
                        }
                    });
                }
                html += '<div class="row" id="attr_' + $this.val() + '">';
                html += '<div class="col-md-3">';
                html += '<label>' + $this.text() + '</label>';
                html += '</div>';
                html += '<div class="col-lg-9">';
                html += '<input type="text" class="form-control" id="attribute_options_' + $this.val() + '" value="' + selected_options + '" placeholder="Add attribute values" data-role="tagsinput" onchange="variants_update(\'' + btoa(JSON.stringify(item_attribute)) + '\')">';
                html += '</div>';
                html += '</div>';
            });
            $("#item_attributes").html(html);
            $("#item_attributes input[data-role=tagsinput]").tagsinput();
            if ($("#item_attribute").val().length == 0) {
                $("#attributes").val('');
                $("#variants").val('');
                $("#item_variants").html('');
            }
        }
        function variants_update(item_attributeX = '') {
            if (item_attributeX) {
                var item_attributeX = $.parseJSON(atob(item_attributeX));
            }
            var html = '';
            var item_attribute = $("#item_attribute").map(function(idx, ele) {
                return $(ele).val();
            }).get();
            if (item_attribute.length > 0) {
                var attributes = [];
                var attributeSet = [];
                $.each(item_attribute, function(index, attribute) {
                    var attribute_options = $("#attribute_options_" + attribute).val();
                    if (attribute_options) {
                        var attribute_options = attribute_options.split(',');
                        attribute_options = $.map(attribute_options, function(value) {
                            return value.replace(/[^a-zA-Z0-9]/g, '');
                        });
                        attributeSet.push(attribute_options);
                        attributes.push({
                            'attribute_id': attribute,
                            'attribute_options': attribute_options
                        });
                    }
                });
                $('#attributes').val(JSON.stringify(attributes));
                var variants = getCombinations(attributeSet);
                $('#variants').val(JSON.stringify(variants));

                if (attributeSet.length > 0) {
                    html += '<table class="table table-bordered">';
                    html += '<thead class="thead-light">';
                    html += '<tr>';
                    html += '<th class="text-center"><span class="control-label">Variant</span></th>';
                    html += '<th class="text-center"><span class="control-label">Variant Price</span></th>';
                    html += '<th class="text-center wholesale_column"><span class="control-label">{{ trans('lang.variant_wholesale_price') }}</span></th>';
                    html += '<th class="text-center"><span class="control-label">Variant Quantity</span></th>';
                    html += '<th class="text-center"><span class="control-label">Variant Image</span></th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    $.each(variants, function(index, variant) {
                        var variant_price = 1;
                        var variant_qty = -1;
                        var variant_wholesale_price = '';
                        var variant_image = variant_image_url = '';
                        if (item_attributeX) {
                            var variant_info = $.map(item_attributeX.variants, function(v, i) {
                                if (v.variant_sku == variant) {
                                    return v;
                                }
                            });
                            if (variant_info[0]) {
                                variant_price = variant_info[0].variant_price;
                                variant_qty = variant_info[0].variant_quantity;
                                if (variant_info[0].variant_wholesale_price) {
                                    variant_wholesale_price = variant_info[0].variant_wholesale_price;
                                }
                                if (variant_info[0].variant_image) {
                                    variant_image = '<img class="rounded" style="width:50px" src="' + variant_info[0].variant_image + '" alt="image"><i class="mdi mdi-delete" data-variant="' + variant + '" data-status="old"></i>';
                                    variant_image_url = variant_info[0].variant_image;
                                }
                            }
                        }
                        html += '<tr>';
                        html += '<td><label for="" class="control-label">' + variant + '</label></td>';
                        html += '<td>';
                        html += '<input type="number" id="price_' + variant + '" value="' + variant_price + '" min="0" class="form-control">';
                        html += '</td>';
                        html += '<td class="wholesale_column">';
                        html += '<input type="number" id="wholesale_price_' + variant + '" value="' + variant_wholesale_price + '" min="0" class="form-control">';
                        html += '</td>';
                        html += '<td>';
                        html += '<input type="number" id="qty_' + variant + '" value="' + variant_qty + '" min="-1" class="form-control">';
                        html += '</td>';
                        html += '<td>';
                        html += '<div class="variant-image">';
                        html += '<div class="upload">';
                        html += '<div class="image" id="variant_' + variant + '_image">' + variant_image + '</div>';
                        html += '<div class="icon"><i class="mdi mdi-cloud-upload" data-variant="' + variant + '" id="upload_' + variant + '"></i></div>';
                        html += '</div>';
                        html += '<div id="variant_' + variant + '_process"></div>';
                        html += '<div class="input-file">';
                        html += '<input type="file" id="file_' + variant + '" onChange="handleVariantFileSelect(event,\'' + variant + '\')" class="form-control" style="display:none;">';
                        html += '<input type="hidden" id="variant_' + variant + '_url" value="' + variant_image_url + '">';
                        html += '</div>';
                        html += '</div>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody>';
                    html += '</table>';
                }
            }
            $("#item_variants").html(html);
            applyWholesaleVisibility();
        }

        /* The wholesale column is always drawn and shown or hidden with the
         * toggle, so a price already typed into it survives being switched off
         * and on again. Mirrors the store panel. */
        function applyWholesaleVisibility() {
            if ($('#wholesale_enabled').is(':checked')) {
                $('.wholesale_fields').show();
                $('.wholesale_column').show();
            } else {
                $('.wholesale_fields').hide();
                $('.wholesale_column').hide();
            }
        }

        $(document).on('change', '#wholesale_enabled', applyWholesaleVisibility);

        function getCombinations(arr) {
            if (arr.length) {
                if (arr.length == 1) {
                    return arr[0];
                } else {
                    var result = [];
                    var allCasesOfRest = getCombinations(arr.slice(1));
                    for (var i = 0; i < allCasesOfRest.length; i++) {
                        for (var j = 0; j < arr[0].length; j++) {
                            result.push(arr[0][j] + '-' + allCasesOfRest[i]);
                        }
                    }
                    return result;
                }
            }
        }
        function uniqid(prefix = "", random = false) {
            const sec = Date.now() * 1000 + Math.random() * 1000;
            const id = sec.toString(16).replace(/\./g, "").padEnd(14, "0");
            return `${prefix}${id}${random ? `.${Math.trunc(Math.random() * 100000000)}` : ""}`;
        }
        $("#product_image").resizeImg({
            callback: function(base64str) {
                var val = $('#product_image').val().toLowerCase();
                var ext = val.split('.')[1];
                var docName = val.split('fakepath')[1];
                var filename = $('#product_image').val().replace(/C:\\fakepath\\/i, '')
                var timestamp = Number(new Date());
                var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                product_image_filename.push(filename);
                productImagesCount++;
                photos_html = '<span class="image-item" id="photo_' + productImagesCount +
                    '"><span class="remove-btn" data-id="' + productImagesCount + '" data-img="' + base64str +
                    '"><i class="fa fa-remove"></i></span><img class="rounded" width="50px" id="" height="auto" src="' +
                    base64str + '"></span>'
                $(".product_image").append(photos_html);
                photos.push(base64str);
                $("#product_image").val('');
            }
        });
        async function storeProductImageData() {
            var newPhoto = [];
            if (photos.length > 0) {
                await Promise.all(photos.map(async (productPhoto, index) => {
                    productPhoto = productPhoto.replace(/^data:image\/[a-z]+;base64,/, "");
                    var uploadTask = await storageRef.child(product_image_filename[index]).putString(
                        productPhoto, 'base64', {
                            contentType: 'image/jpg'
                        });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto.push(downloadURL);
                }));
            }
            return newPhoto;
        }
        async function storeDigitalImageData() {
            var newPhoto = '';
            try {
                if (digital_product_file != '') {
                    digital_product_file = digital_product_file.replace(/^data:image\/[a-z]+;base64,/, "")
                    if (digital_product_ext == 'zip' || digital_product_ext == "pdf") {
                        var uploadTask = await storageRef.child(digital_product_file_name).put(digital_product_file);
                    } else {
                        var uploadTask = await storageRef.child(digital_product_file_name).putString(
                            digital_product_file, 'base64', {
                                contentType: 'image/jpg'
                            });
                    }
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto = downloadURL;
                    digital_product_file = downloadURL;
                }
            } catch (error) {
                console.log("ERR ===", error);
            }
            return newPhoto;
        }
        async function storeVariantImageData() {
            var newPhoto = [];
            if (variant_photos.length > 0) {
                await Promise.all(variant_photos.map(async (variantPhoto, index) => {
                    variantPhoto = variantPhoto.replace(/^data:image\/[a-z]+;base64,/, "");
                    var uploadTask = await storageRef.child(variant_filename[index]).putString(
                        variantPhoto, 'base64', {
                            contentType: 'image/jpg'
                        });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    $('[id="variant_' + variant_vIds[index] + '_url"]').val(downloadURL);
                    newPhoto.push(downloadURL);
                }));
            }
            return newPhoto;
        }
    </script>
@endsection
