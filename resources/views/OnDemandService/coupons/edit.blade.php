@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.coupon_plural') }}</h3>
            </div>

            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    @if (!isset($_GET['id']))
                        <li class="breadcrumb-item"><a href="{!! route('ondemand.coupons') !!}">{{ trans('lang.coupon_plural') }}</a></li>
                    @else
                        <li class="breadcrumb-item"><a href="{!! route('ondemand.coupons', @$_GET['id']) !!}">{{ trans('lang.coupon_plural') }}</a></li>
                    @endif
                    <li class="breadcrumb-item active">{{ trans('lang.coupon_edit') }}</li>
                </ol>
            </div>

        </div>
        <div>

            <div class="card-body">

                <div class="error_top" style="display:none"></div>

                <div class="row vendor_payout_create">

                    <div class="vendor_payout_create-inner">

                        <fieldset>
                            <legend>{{ trans('lang.coupon_edit') }}</legend>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.coupon_code') }}</label>
                                <div class="col-7">
                                    <input type="text" type="text" class="form-control coupon_code">
                                    <div class="form-text text-muted">{{ trans('lang.coupon_code_help') }} </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.coupon_discount_type') }}</label>
                                <div class="col-7">
                                    <select id="coupon_discount_type" class="form-control">
                                        <option value="Percentage">{{ trans('lang.coupon_percent') }}</option>
                                        <option value="Fix Price">{{ trans('lang.coupon_fixed') }}</option>
                                    </select>
                                    <div class="form-text text-muted">{{ trans('lang.coupon_discount_type_help') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.coupon_discount') }}</label>
                                <div class="col-7">
                                    <input type="number" type="text" class="form-control coupon_discount">
                                    <div class="form-text text-muted">{{ trans('lang.coupon_discount_help') }}</div>

                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.coupon_expires_at') }}</label>
                                <div class="col-7">
                                    <div class='input-group date' id='datetimepicker1'>
                                        <input type='text' class="form-control date_picker input-group-addon" />
                                        <span class="">
                                        </span>
                                    </div>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.coupon_expires_at_help') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50 provider-div">
                                <label class="col-3 control-label">{{ trans('lang.provider') }}</label>
                                <div class="col-7">
                                    <select id="provider_select" class="form-control">
                                        <option value="">{{ trans('lang.select_provider') }}</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.select_provider') }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.coupon_description') }}</label>
                                <div class="col-7">
                                    <textarea rows="12" class="form-control coupon_description" id="coupon_description"></textarea>
                                    <div class="form-text text-muted">{{ trans('lang.coupon_description_help') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.category_image') }}</label>
                                <div class="col-7">
                                    <input type="file" onChange="handleFileSelect(event)">
                                    <div class="placeholder_img_thumb coupon_image"></div>
                                    <div id="uploding_image"></div>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <div class="form-check">
                                    <input type="checkbox" class="coupon_enabled" id="coupon_enabled">
                                    <label class="col-3 control-label" for="coupon_enabled">{{ trans('lang.coupon_enabled') }}</label>

                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <div class="form-check">
                                    <input type="checkbox" class="coupon_public" id="coupon_public">
                                    <label class="col-3 control-label" for="coupon_public">{{ trans('lang.coupon_public') }}</label>
                                </div>
                            </div>

                        </fieldset>
                    </div>

                </div>

            </div>
            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i>
                    {{ trans('lang.save') }}</button>
                @if (!isset($_GET['id']))
                    <a href="{!! route('ondemand.coupons') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                @else
                    <a href="{!! route('ondemand.coupons', @$_GET['id']) !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                @endif
            </div>

        </div>
    @endsection

    @section('scripts')
        <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
        <link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">

        <script type="text/javascript">
            var id = "<?php echo $id; ?>";
            var database = firebase.firestore();
            var ref = database.collection('providers_coupons').where("id", "==", id);
            var photo_coupon = "";
            var idOfProviderDetailPage = "{{ @$_GET['id'] }}";
            var photo = "";
            var fileName = "";
            var oldImageFile = '';
            var section_id = null;
            var placeholderImage = '';
            var placeholder = database.collection('settings').doc('placeHolderImage');
            var sectionId = getCookie('section_id');
            placeholder.get().then(async function(snapshotsimage) {
                var placeholderImageData = snapshotsimage.data();
                placeholderImage = placeholderImageData.image;
            })
            if (idOfProviderDetailPage != '' && idOfProviderDetailPage != null) {
                getProviderInfo(idOfProviderDetailPage);
            }
            $(document).ready(function() {
                if (idOfProviderDetailPage != '') {
                    $('.provider-div').css('display', 'none');
                }
                $(function() {
                    $('#datetimepicker1').datepicker({
                        dateFormat: 'mm/dd/yyyy'
                    });
                });

                jQuery("#data-table_processing").show();

                ref.get().then(async function(snapshots) {

                    var coupon = snapshots.docs[0].data();

                    database.collection('users').where('role', '==', 'provider').where('section_id','==',sectionId).get().then(async function(snapshots) {
                        snapshots.docs.forEach((listval) => {
                            var data = listval.data();
                            if (data.id == coupon.providerId) {
                                $('#provider_select').append($("<option selected></option>")
                                    .attr("value", data.id)
                                    .text(data.firstName + ' ' + data.lastName));
                            } else {
                                $('#provider_select').append($("<option></option>")
                                    .attr("value", data.id)
                                    .text(data.firstName + ' ' + data.lastName));
                            }
                        })
                    });
                    section_id = coupon.sectionId;
                    if (coupon.image != '' && coupon.image != null) {
                        photo_coupon = coupon.image;
                        photo = coupon.image;
                        oldImageFile = coupon.image;
                        $(".coupon_image").append('<img class="rounded" style="width:50px" src="' + photo_coupon + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image">');
                    } else {

                        $(".coupon_image").append('<img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">');
                    }
                    $(".coupon_code").val(coupon.code);
                    $("#coupon_discount_type").val(coupon.discountType);
                    $(".coupon_discount").val(parseInt(coupon.discount));
                    $(".coupon_description").val(coupon.description);

                    if (coupon.isEnabled) {
                        $(".coupon_enabled").prop("checked", true);
                    }
                    if (coupon.isPublic) {
                        $(".coupon_public").prop("checked", true);
                    }

                    if (coupon.hasOwnProperty("expiresAt")) {

                        try {
                            var date1 = coupon.expiresAt.toDate().toDateString();
                            var date = new Date(date1);
                            var dd = String(date.getDate()).padStart(2, '0');
                            var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
                            var yyyy = date.getFullYear();
                            var expiresDate = mm + '/' + dd + '/' + yyyy;
                        } catch (err) {

                            var date1 = '';
                            var date = '';
                            var dd = '';
                            var mm = '';
                            var yyyy = '';
                            var expiresDate = '';

                        }
                        var $datepicker = $('.date_picker');
                        $datepicker.datepicker();
                        $datepicker.datepicker('setDate', expiresDate);
                    }

                    var resturant = "<?php echo $id; ?>";

                    $("#service_select").change(function() {
                        var serviceID = $(this).val();
                    });

                    jQuery("#data-table_processing").hide();

                })


                $(".edit-form-btn").click(async function() {

                    var code = $(".coupon_code").val();
                    var discount = $(".coupon_discount").val();
                    var description = $(".coupon_description").val();
                    var newdate = new Date($(".date_picker").val());
                    var expiresAt = new Date(newdate.setHours(23, 59, 59, 999));
                    var isEnabled = $(".coupon_enabled").is(":checked");
                    var discountType = $("#coupon_discount_type").val();
                    var providerId = $("#provider_select").val();
                    var isPublic = $(".coupon_public").is(":checked");
                    await getProviderInfo(providerId);

                    if (code == '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{ trans('lang.enter_coupon_code_error') }}</p>");
                        window.scrollTo(0, 0);
                    } else if (discount == '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{ trans('lang.enter_coupon_discount_error') }}</p>");
                        window.scrollTo(0, 0);
                    } else if (discountType == '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{ trans('lang.select_coupon_discountType_error') }}</p>");
                        window.scrollTo(0, 0);
                    } else if (newdate == 'Invalid Date') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{ trans('lang.select_coupon_expdate_error') }}</p>");
                        window.scrollTo(0, 0);
                    } else if (providerId == '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{ trans('lang.select_provider_error') }}</p>");
                        window.scrollTo(0, 0);
                    } else {
                        storeImageData().then(IMG => {
                            database.collection('providers_coupons').doc(id).update({
                                'code': code,
                                'description': description,
                                'discount': discount,
                                'expiresAt': expiresAt,
                                'isEnabled': isEnabled,
                                'id': id,
                                'discountType': discountType,
                                'image': IMG,
                                'providerId': providerId,
                                'isPublic': isPublic,
                                'sectionId': section_id
                            }).then(function(result) {
                                if (idOfProviderDetailPage != '') {
                                    window.location.href = '{{ route('ondemand.coupons', @$_GET['id']) }}';
                                } else {
                                    window.location.href = '{{ route('ondemand.coupons') }}';
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
                })
            })
            async function getProviderInfo(provider_id) {
                await database.collection('users').where('id', '==', provider_id).get().then(async function(snapshot) {
                    var provider_data = snapshot.docs[0].data();
                    if (provider_data.hasOwnProperty('section_id') && provider_data.section_id != null && provider_data.section_id != '') {
                        section_id = provider_data.section_id;
                    }
                });
            }
            var storageRef = firebase.storage().ref('images');
            var storage = firebase.storage();

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
                        fileName = filename;
                        photo = filePayload;
                        $(".coupon_image").empty();
                        $(".coupon_image").append('<img class="rounded" style="width:50px" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image">');

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
        </script>
    @endsection
