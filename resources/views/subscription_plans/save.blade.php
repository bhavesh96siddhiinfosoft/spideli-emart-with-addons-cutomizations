@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            @if ($id == '')
            <h3 class="text-themecolor">{{ trans('lang.create_subscription_plan') }}</h3>
            @else
            <h3 class="text-themecolor">{{ trans('lang.edit_subscription_plan') }}</h3>
            @endif
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a
                        href="{{ route('subscription-plans.index') }}">{{ trans('lang.subscription_plans') }}</a>
                </li>
                @if ($id == '')
                <li class="breadcrumb-item active">{{ trans('lang.create_subscription_plan') }}</li>
                @else
                <li class="breadcrumb-item active">{{ trans('lang.edit_subscription_plan') }}</li>
                @endif
            </ol>
        </div>
    </div>
    <div class="container-fluid">
      
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="success_top" style="display:none"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.plan_details') }}</legend>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ trans('lang.plan_name') }}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="plan_name"
                                    placeholder="{{ trans('lang.enter_plan_name') }}">
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label" for="">{{ trans('lang.plan_type') }}</label>
                            <div class="form-check width-50">
                                <input type="radio" id="free_type" name="planType" value="free" checked>
                                <label class="control-label" for="free_type">{{ trans('lang.free') }}</label>
                            </div>
                            <div class="form-check width-50">
                                <input type="radio" id="paid_type" name="planType" value="paid">
                                <label class="control-label" for="paid_type">{{ trans('lang.paid') }}</label>
                            </div>
                        </div>
                        <div class="form-group row width-100 d-none plan_price_div">
                            <label class="col-3 control-label">{{ trans('lang.plan_price') }}</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="plan_price"
                                    placeholder="{{ trans('lang.enter_plan_price') }}">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.plan_validity_days') }}</label>
                            <div class="form-check width-100">
                                <input type="radio" id="unlimited_days" name="set_expiry_limit" value="unlimited"
                                    checked>
                                <label class="control-label" for="unlimited_days">{{ trans('lang.unlimited') }}</label>
                            </div>
                            <div id="limited_days_div">
                                <div class="form-check width-50">
                                    <input type="radio" id="limited_days" name="set_expiry_limit" value="limited">
                                    <label class="control-label" for="limited_days">{{ trans('lang.limited') }}</label>
                                </div>
                                <div class="form-check width-50 d-none expiry-limit-div">
                                    <input type="number" id="plan_validity" class="form-control"
                                        placeholder="{{ trans('lang.ex_365') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.description') }}</label>
                            <div class="col-7">
                                <textarea class="form-control" id="description" rows="5"></textarea>
                            </div>
                        </div>
                        
                        <div class="form-group row width-100 status-div">
                            <div class="form-check width-100">
                                <input type="checkbox" id="status">
                                <label class="control-label" for="status">{{ trans('lang.status') }}</label>
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{ trans('lang.image') }}</label>
                            <div class="col-7">
                                <input type="file" onChange="handleFileSelect(event)" class="form-control">
                                <div class="form-text text-muted">{{ trans('lang.image') }}</div>
                            </div>
                            <div class="placeholder_img_thumb plan_image"></div>
                            <div id="uploding_image"></div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>{{ trans('lang.available_features') }}</legend>
                        <div class="form-group row width-100 subscriptionPlan-features-div">
                            <div class="form-check">
                                <input type="checkbox" id="mobile_app" name="features" value="ownerMobileApp">
                                <label class="control-label" for="mobile_app">{{ trans('lang.mobile_app') }}</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="chat" name="features" value="chat">
                                <label class="control-label" for="chat">{{ trans('lang.chat') }}</label>
                            </div>
                            <div class="form-check qrCodeGenerateDiv" style="display:none">
                                <input type="checkbox" id="qrCodeGenerate" name="features" value="qrCodeGenerate">
                                <label class="control-label" for="qrCodeGenerate">{{ trans('lang.generate_qr_code') }}</label>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset id="commissionPlan-features-div" class="d-none">
                        <legend>{{ trans('lang.plan_points') }}</legend>
                        <div class="form-group row width-100 ">
                            <div id="options-container"></div>
                            <button id="add-plan-point" onclick="addPlanPoint()"
                                class="btn btn-primary">{{ trans('lang.add_more') }}</button>
                        </div>
                    </fieldset>
                    <fieldset class="d-none" id="item_service_limit">
                        <legend id="item_service_limit_heading"></legend>
                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="radio" id="unlimited_item" name="set_item_limit" value="unlimited" checked>
                                <label class="control-label" for="unlimited_item">{{ trans('lang.unlimited') }}</label>
                            </div>
                            <div class="d-flex ">
                                <div class="form-check width-50 limited_item_div  ">
                                    <input type="radio" id="limited_item" name="set_item_limit" value="limited">
                                    <label class="control-label" for="limited_item">{{ trans('lang.limited') }}</label>
                                </div>
                                <div class="form-check width-50 d-none item-limit-div">
                                    <input type="number" id="item_limit" class="form-control"
                                        placeholder="{{ trans('lang.ex_1000') }}" min="1"  oninput="this.value = Math.max(this.value, 1)">
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="d-none" id="order_booking_limit">
                        <legend id="order_booking_limit_heading"></legend>
                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="radio" id="unlimited_order" name="set_order_limit" value="unlimited"
                                    checked>
                                <label class="control-label" for="unlimited_order">{{ trans('lang.unlimited') }}</label>
                            </div>
                            <div class="d-flex  ">
                                <div class="form-check width-50 limited_order_div">
                                    <input type="radio" id="limited_order" name="set_order_limit" value="limited">
                                    <label class="control-label" for="limited_order">{{ trans('lang.limited') }}</label>
                                </div>
                                <div class="form-check width-50 d-none order-limit-div">
                                    <input type="number" id="order_limit" class="form-control"
                                        placeholder="{{ trans('lang.ex_1000') }}" min="1"  oninput="this.value = Math.max(this.value, 1)">
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i>
                {{ trans('lang.save') }}
            </button>
            <a href="{{ url('subscription-plans') }}" class="btn btn-default"><i
                    class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')

<script>

    var section_id = getCookie('section_id') || null;
    var requestId = "<?php echo $id; ?>";
    var database = firebase.firestore();
    var createdAt = firebase.firestore.FieldValue.serverTimestamp();
    var id = (requestId == '') ? database.collection("tmp").doc().id : requestId;
    var pagesize = 20;
    var start = '';
    var placeholderImage = '';
    var planPoints = [];
    var photo = "";
    var fileName = '';
    var planImageFile = '';
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    var storageRef = firebase.storage().ref('images');
    var storage = firebase.storage();

    var enabledSubscriptions = 0; 
    var serviceTypeFlag = '';
    var sectionData = '';
    
    $(document).ready(async function() {

        let sectionRef = await database.collection('sections').doc(section_id).get();
        if(sectionRef.exists){
            sectionData = sectionRef.data();
            serviceTypeFlag = sectionData.serviceTypeFlag;
        }
        
        let subscriptionRef = await database.collection('subscription_plans').where('isEnable', '==', true)
        .where('isCommissionPlan', '==', false).where('sectionId', '==', section_id).get();
        enabledSubscriptions = subscriptionRef.docs.length;
        
        var itemLimitLegend = $("#item_service_limit_heading");
        var orderLimitLegend  = $("#order_booking_limit_heading");
        if(serviceTypeFlag == "ondemand-service" || serviceTypeFlag == "ecommerce-service"){
            itemLimitLegend.text("{{ trans('lang.maximum_service_limit') }}");
            orderLimitLegend.text("{{ trans('lang.maximum_booking_limit') }}");             
            $('.qrCodeGenerateDiv').hide();
        } else {
            itemLimitLegend.text("{{ trans('lang.maximum_item_limit') }}");
            orderLimitLegend.text("{{ trans('lang.maximum_order_limit') }}");
            $('.qrCodeGenerateDiv').removeAttr('style').show();
        }

        $("#item_service_limit").removeClass('d-none');
        $("#order_booking_limit").removeClass('d-none');
        
        $('input[name="set_expiry_limit"]').on('change', function() {
            if ($('#limited_days').is(':checked')) {
                $('.expiry-limit-div').removeClass('d-none');
            } else {
                $('.expiry-limit-div').addClass('d-none');
            }
        });
 
        $('input[name="set_item_limit"]').on('change', function() {
            if ($('#limited_item').is(':checked')) {
                $('.item-limit-div').removeClass('d-none');
            } else {
                $('.item-limit-div').addClass('d-none');
            }
        });

        $('input[name="set_order_limit"]').on('change', function() {
            if ($('#limited_order').is(':checked')) {
                $('.order-limit-div').removeClass('d-none');
            } else {
                $('.order-limit-div').addClass('d-none');
            }
        });

        if (requestId != '') {
            var ref = database.collection('subscription_plans').where('id', '==', id);
            jQuery("#data-table_processing").show();
            ref.get().then(async function(snapshots) {
                if (snapshots.docs.length) {
                    var data = snapshots.docs[0].data();
                    $("#plan_name").val(data.name);
                    $("#plan_price").val(data.price);
                    $('#description').val(data.description);
                    if (data.isEnable) {
                        $("#status").prop('checked', true);
                    } 
                    if (data.expiryDay != '-1') {
                        $("#limited_days").prop('checked', true);
                        $('.expiry-limit-div').removeClass('d-none');
                        $('#plan_validity').val(data.expiryDay);
                    } else {
                        $("#unlimited_days").prop('checked', true);
                    }
                    if (data.itemLimit != '-1') {
                        $("#limited_item").prop('checked', true);
                        $('.item-limit-div').removeClass('d-none');
                    } else {
                        $("#unlimited_item").prop('checked', true);
                    }
                    if (data.orderLimit != '-1') {
                        $("#limited_order").prop('checked', true);
                        $('.order-limit-div').removeClass('d-none');
                    } else {
                        $("#unlimited_order").prop('checked', true);
                    }
                    $('#item_limit').val(data.itemLimit);
                    $('#order_limit').val(data.orderLimit);

                    if (data.hasOwnProperty('features')) {
                        Object.entries(data.features).forEach(([key, value]) => {
                            if (value) {
                                $('input[name="features"][value="' + key + '"]').prop(
                                    'checked',
                                    true);
                            }
                        })

                        if (data.isCommissionPlan == true) {
                            $('input[name="features"]').attr('disabled', true);
                            $('input[name="planType"]').attr('disabled', true);
                            $('#status').attr('disabled', true);
                            $('.status-div').addClass('d-none');
                            $('#section').attr('disabled', true);
                            $('#plan_price').attr('readonly', true);
                        }
                    }

                    if (data.isCommissionPlan == true) {
                        $('#commissionPlan-features-div').removeClass('d-none');
                        planPoints = data.plan_points;
                        renderPlanPoints();
                        $('#free_type').prop('checked', true);
                        $('#section').attr('disabled', true);
                        $("#limited_days_div").addClass('d-none');
                        $('.limited_item_div').addClass('d-none');
                        $('.limited_order_div').addClass('d-none'); 
                    } else {
                        $('.plan_price_div').removeClass('d-none');
                        $("#limited_days_div").removeClass('d-none');
                    }
                    if (data.type == 'paid') {
                        $('#paid_type').prop('checked', true);
                        $('.plan_price_div').removeClass('d-none');
                    } else {
                        $('#free_type').prop('checked', true);
                        $('.plan_price_div').addClass('d-none');
                    }
                  
                    if (data.image != '' && data.image != null) {
                        photo = data.image;
                        planImageFile = data.image;
                        $(".plan_image").append('<img onerror="this.onerror=null;this.src=\'' +
                            placeholderImage + '\'" class="rounded" style="width:50px" src="' +
                            photo + '" alt="image">');
                    } else {
                        $(".plan_image").append('<img class="rounded" style="width:50px" src="' +
                            placeholderImage + '" alt="image">');
                    }
                }
                jQuery("#data-table_processing").hide();
            });
        }
    });

    $('input[name="planType"]').on('change', function() {
        if ($('input[name="planType"]:checked').val() == 'free') {
            $('.plan_price_div').addClass('d-none');
            $("#plan_price").val(0);
        } else {
            $('.plan_price_div').removeClass('d-none');
        }
    });
    
    $(".edit-form-btn").click(async function() {

        $(".success_top").hide();
        $(".error_top").hide();
        
        var planType = $('input[name="planType"]:checked').val();
        var plan_name = $("#plan_name").val();
        var plan_price = $("#plan_price").val();
        if (planType == 'free') {
            plan_price = '0';
        }
        var description = $('#description').val();
        var expiryDay = $('#plan_validity').val();
        var set_expiry_limit = $('input[name="set_expiry_limit"]:checked').val();
        var expiry_limit = (set_expiry_limit == 'limited') ? expiryDay : '-1';
        var status = $("#status").is(":checked");
        var set_item_limit = $('input[name="set_item_limit"]:checked').val();
        var item_limit = (set_item_limit == 'limited') ? $('#item_limit').val() : '-1';
        var set_order_limit = $('input[name="set_order_limit"]:checked').val();
        var order_limit = (set_order_limit == 'limited') ? $('#order_limit').val() : '-1';
        var checkboxes = document.querySelectorAll('input[name="features"]');
        
        var featuresObject = {};
        var selectedCheckboxCount = 0;
        checkboxes.forEach((checkbox) => {
            featuresObject[checkbox.value] = checkbox.checked;
            selectedCheckboxCount = (checkbox.checked == true) ? selectedCheckboxCount + 1 :
                selectedCheckboxCount;
        });
        
        if (plan_name.trim() == "") {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{ trans('lang.enter_plan_name') }}</p>");
            window.scrollTo(0, 0);
            return false;
        } else if (planType == 'paid' && plan_price == "") {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{ trans('lang.enter_plan_price') }}</p>");
            window.scrollTo(0, 0);
            return false;
        } else if (planType == 'paid' && plan_price <=0) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{ trans('lang.enter_proper_price') }}</p>");
            window.scrollTo(0, 0);
            return false;
        } else if (description.trim() == "") {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{ trans('lang.enter_description') }}</p>");
            window.scrollTo(0, 0);
            return false;
        } else if (set_expiry_limit == 'limited' && expiryDay == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{ trans('lang.please_enter_expiry') }}</p>");
            window.scrollTo(0, 0);
            return false;
        } else if (expiry_limit == 0) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{ trans('lang.expiry_day_zero') }}</p>");
            window.scrollTo(0, 0);
            return false;
        } else if (expiry_limit < 0 && expiry_limit != '-1') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{ trans('lang.expiry_day_in_positive_no') }}</p>");
            window.scrollTo(0, 0);
            return false;
        }   else if (selectedCheckboxCount == 0) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{ trans('lang.select_any_one_feature') }}</p>");
            window.scrollTo(0, 0);
            return false;
        } else if (set_item_limit == 'limited' && ($('#item_limit').val() == '' || $('#item_limit').val() <= '0')) {
            $(".error_top").html("");
            $(".error_top").show();
            if(serviceTypeFlag == "ondemand-service"){
                $(".error_top").html("");
                $(".error_top").show();
                $(".error_top").append("<p>{{ trans('lang.enter_service_limit') }}</p>");
               
            }
            else
            {
                $(".error_top").html("");
                $(".error_top").show();
                $(".error_top").append("<p>{{ trans('lang.enter_item_limit') }}</p>");
            }
            window.scrollTo(0, 0);
            return false;
           
        } else if (set_order_limit == 'limited' && ($('#order_limit').val() == '' || $('#order_limit').val() <= '0')) {
            $(".error_top").html("");
            $(".error_top").show();
            if(serviceTypeFlag == "ondemand-service"){
                $(".error_top").html("");
                $(".error_top").show();
                $(".error_top").append("<p>{{ trans('lang.enter_booking_limit') }}</p>");
            }
            else
            {
                $(".error_top").html("");
                $(".error_top").show();
                $(".error_top").append("<p>{{ trans('lang.enter_order_limit') }}</p>");
            }
            window.scrollTo(0, 0);
            return false;
          
        } else if (!validatePlanPoints()) {

            return false;

        } else if (enabledSubscriptions == 0 && status == false) {
            
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append(
                "<p>{{ trans('lang.atleast_one_subscription_plan_should_be_active') }}</p>");
            window.scrollTo(0, 0);
            return false;

        } else {

            requestId == '' ? (
                    storeImageData().then(IMG => {
                        if (IMG == '') {
                            $(".error_top").show();
                            $(".error_top").html("");
                            $(".error_top").append(
                                "<p>{{ trans('lang.item_image_help') }}</p>");
                            window.scrollTo(0, 0);
                            return false;
                        }
                        jQuery("#data-table_processing").show();
                        database.collection('subscription_plans').doc(id).set({
                            'id': id,
                            'name': plan_name,
                            'price': plan_price,
                            'description': description,
                            'expiryDay': expiry_limit,
                            'isEnable': status,
                            'itemLimit': item_limit,
                            'orderLimit': order_limit,
                            'features': featuresObject,
                            'plan_points': null,
                            'type': planType,
                            'createdAt': createdAt,
                            'image': IMG,
                            'sectionId': section_id,
                            'isCommissionPlan':false
                        }).then(function(result) {
                            jQuery("#data-table_processing").hide();
                            $(".success_top").show();
                            $(".success_top").html("");
                            window.scrollTo(0, 0);
                            window.location.href = "{{ route('subscription-plans.index') }}";
                        }).catch(function(error) {
                            $(".error_top").show();
                            $(".error_top").html("");
                            $(".error_top").append("<p>" + error + "</p>");
                        })
                    }).catch(function(error) {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + error + "</p>");
                    })) :
                (
                    storeImageData().then(IMG => {
                        if (IMG == '') {
                            $(".error_top").show();
                            $(".error_top").html("");
                            $(".error_top").append(
                                "<p>{{ trans('lang.item_image_help') }}</p>");
                            window.scrollTo(0, 0);
                            return false;
                        }
                        database.collection('subscription_plans').doc(id).update({
                            'name': plan_name,
                            'price': plan_price,
                            'description': description,
                            'expiryDay': expiry_limit,
                            'isEnable': status,
                            'itemLimit': item_limit,
                            'orderLimit': order_limit,
                            'features': featuresObject,
                            'plan_points': planPoints,
                            'image': IMG,
                            'type': planType,
                            'sectionId': section_id
                        }).then(function(result) {
                            jQuery("#data-table_processing").hide();
                            $(".success_top").show();
                            $(".success_top").html("");
                            window.scrollTo(0, 0);
                            window.location.href = "{{ route('subscription-plans.index') }}";
                        }).catch(function(error) {
                            $(".error_top").show();
                            $(".error_top").html("");
                            $(".error_top").append("<p>" + error + "</p>");
                        })
                    }).catch(function(error) {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + error + "</p>");
                    }));
        }
    });

    function renderPlanPoints() {
        const container = document.getElementById('options-container');
        container.innerHTML = '';
        planPoints.forEach((point, index) => {
            const html = `
            <div class="form-group  d-flex ml-1 option-row mt-1" id="plan-point-${index}">
                <input type="text" class="form-control" id="input-${index}" value="${point}" 
                    oninput="updatePlanPoint(${index}, this.value)">
                <button type="button" class="btn btn-danger ml-2" onclick="deletePlanPoint(${index})">
                    <i class="mdi mdi-delete"></i>
                </button>
            </div>`;
            container.insertAdjacentHTML('beforeend', html);
        });
    }

    function addPlanPoint() {
        planPoints.push('');
        renderPlanPoints();
    }

    function deletePlanPoint(index) {
        planPoints.splice(index, 1);
        renderPlanPoints();
    }

    function updatePlanPoint(index, value) {
        planPoints[index] = value;
    }

    function validatePlanPoints() {
        const errorMessageContainer = document.querySelector('.error_top');
        const hasEmptyPoint = planPoints.some(point => point.trim() === '');
        if (hasEmptyPoint) {
            if (errorMessageContainer) {
                errorMessageContainer.style.display = 'block';
                errorMessageContainer.innerHTML = "<p>{{ trans('lang.add_plan_points') }}</p>";
            }
            window.scrollTo(0, 0);
            return false;
        }
        if (errorMessageContainer) {
            errorMessageContainer.style.display = 'none';
            errorMessageContainer.innerHTML = '';
        }
        return true;
    }
    async function storeImageData() {
        var newPhoto = '';
        try {
            if (planImageFile != "" && photo != planImageFile) {
                var oldImageRef = await storage.refFromURL(planImageFile);
                imageBucket = oldImageRef.bucket;
                var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                if (imageBucket == envBucket) {
                    await oldImageRef.delete().then(() => {
                        console.log("Old file deleted!")
                    }).catch((error) => {
                        console.log("ERR File delete ===", error);
                    });
                } else {
                    console.log('Bucket not matched');
                }
            }
            if (photo != planImageFile) {
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

    function handleFileSelect(evt) {
        var f = evt.target.files[0];
        var reader = new FileReader();
        reader.onload = (function(theFile) {
            return function(e) {
                var filePayload = e.target.result;
                var val = f.name;
                var ext = val.split('.')[1];
                var docName = val.split('fakepath')[1];
                var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                var timestamp = Number(new Date());
                var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                photo = filePayload;
                fileName = filename;
                $(".plan_image").empty();
                $(".plan_image").append('<img class="rounded" style="width:50px" src="' + photo +
                    '" alt="image" onerror="this.onerror=null;this.src=\'' +
                            placeholderImage + '\'">');
            };
        })(f);
        reader.readAsDataURL(f);
    }
</script>
@endsection