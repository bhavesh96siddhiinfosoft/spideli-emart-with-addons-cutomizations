@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.rental_discount') }}</h3>
            </div>

            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>

                    <li class="breadcrumb-item"><a href="{!! route('rentaldiscount') !!}">{{ trans('lang.rental_discount') }}</a></li>

                    <li class="breadcrumb-item active">{{ trans('lang.rentaldiscount_edit') }}</li>
                </ol>
            </div>

        </div>
        <div>

            <div class="card-body">

                <div class="error_top" style="display:none"></div>

                <div class="row vendor_payout_create">

                    <div class="vendor_payout_create-inner">

                        <fieldset>
                            <legend>{{ trans('lang.rentaldiscount_edit') }}</legend>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.rentaldiscount_code') }}</label>
                                <div class="col-7">
                                    <input type="text" type="text" class="form-control rentaldiscount_code">
                                    <div class="form-text text-muted">{{ trans('lang.discount_code_help') }} </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.rentaldiscount_type') }}</label>
                                <div class="col-7">
                                    <select id="rentaldiscount_type" class="form-control">
                                        <option value="Percentage">{{ trans('lang.discount_percent') }}</option>
                                        <option value="Fix Price">{{ trans('lang.discount_fixed') }}</option>
                                    </select>
                                    <div class="form-text text-muted">{{ trans('lang.discount_type_help') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.discount') }}</label>
                                <div class="col-7">
                                    <input type="number" type="text" class="form-control discount">
                                    <div class="form-text text-muted">{{ trans('lang.discount_help') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.rentaldiscount_expires_at') }}</label>
                                <div class="col-7">
                                    <div class='input-group date' id='datetimepicker1'>
                                        <input type='text' class="form-control date_picker input-group-addon" />
                                        <span class="">
                                        </span>
                                    </div>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.discount_expires_at_help') }}
                                    </div>
                                </div>
                            </div>



                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.rentaldiscount_description') }}</label>
                                <div class="col-7">
                                    <textarea rows="12" class="form-control rentaldiscount_description" id="rentaldiscount_description"></textarea>
                                    <div class="form-text text-muted">{{ trans('lang.rentaldiscount_description_help') }}
                                    </div>
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
                                    <label class="col-3 control-label"
                                        for="coupon_enabled">{{ trans('lang.rentaldiscount_enabled') }}</label>

                                </div>
                            </div>
                        </fieldset>
                    </div>

                </div>

            </div>
            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i>
                    {{ trans('lang.save') }}</button>
                <a href="{!! route('coupons') !!}" class="btn btn-default"><i
                        class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>

            </div>

        </div>
    @endsection

    @section('scripts')
        <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
        <link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">

        <script type="text/javascript">
            var id = "<?php echo $id; ?>";
            var database = firebase.firestore();
            var ref = database.collection('rental_coupons').where("id", "==", id);
            var photo = "";
            var fileName = "";
            var oldImageFile = "";
            var storageRef = firebase.storage().ref('images');
            var storage = firebase.storage();

            var placeholderImage = '';
            var placeholder = database.collection('settings').doc('placeHolderImage');

            placeholder.get().then(async function(snapshotsimage) {
                var placeholderImageData = snapshotsimage.data();
                placeholderImage = placeholderImageData.image;
            })

            $(document).ready(function() {

                $(function() {
                    $('#datetimepicker1').datepicker({
                        dateFormat: 'mm/dd/yyyy'
                    });
                });


                jQuery("#data-table_processing").show();
                ref.get().then(async function(snapshots) {
                    var coupon = snapshots.docs[0].data();

                    if (coupon.image != '' && coupon.image != null) {
                        photo = coupon.image;
                        oldImageFile = coupon.image;
                        $(".coupon_image").append('<img class="rounded" style="width:50px" src="' + photo +
                            '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage +
                            '\'">');
                    } else {

                        $(".coupon_image").append('<img class="rounded" style="width:50px" src="' +
                            placeholderImage + '" alt="image">');
                    }
                    $(".rentaldiscount_code").val(coupon.code);
                    $("#rentaldiscount_type").val(coupon.discountType);
                    $(".discount").val(parseInt(coupon.discount));
                    $(".rentaldiscount_description").val(coupon.description);

                    if (coupon.isEnabled) {
                        $(".coupon_enabled").prop("checked", true);
                    }


                    if (coupon.hasOwnProperty("expiresAt")) {

                        try {
                            var date1 = coupon.expiresAt.toDate().toDateString();
                            var date = new Date(date1);
                            var dd = String(date.getDate()).padStart(2, '0');
                            var mm = String(date.getMonth() + 1).padStart(2, '0');
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
                    jQuery("#data-table_processing").hide();

                })

                $(".edit-form-btn").click(function() {


                    var code = $(".rentaldiscount_code").val();
                    var discount = $(".discount").val();
                    var description = $(".rentaldiscount_description").val();
                    var newdate = new Date($(".date_picker").val());
                    var expiresAt = new Date(newdate.setHours(23, 59, 59, 999));
                    var isEnabled = $(".coupon_enabled").is(":checked");
                    var discountType = $("#rentaldiscount_type").val();
                    var codeAlreadyExist = false;
                    var section_id = getCookie('section_id');

                    database.collection('rental_coupons').where('id', '!=', id).where('code', '==', code).get()
                        .then(async function(snapshot) {
                            if (!snapshot.empty && snapshot.docs.length > 0) {
                                codeAlreadyExist = true;
                            }
                            if (code == '') {
                                $(".error_top").show();
                                $(".error_top").html("");
                                $(".error_top").append(
                                    "<p>{{ trans('lang.enter_coupon_code_error') }}</p>");
                                window.scrollTo(0, 0);
                            } else if (discount == '') {
                                $(".error_top").show();
                                $(".error_top").html("");
                                $(".error_top").append(
                                    "<p>{{ trans('lang.enter_coupon_discount_error') }}</p>");
                                window.scrollTo(0, 0);
                            } else if (discountType == '') {
                                $(".error_top").show();
                                $(".error_top").html("");
                                $(".error_top").append(
                                    "<p>{{ trans('lang.select_coupon_discountType_error') }}</p>");
                                window.scrollTo(0, 0);
                            } else if (newdate == 'Invalid Date') {
                                $(".error_top").show();
                                $(".error_top").html("");
                                $(".error_top").append(
                                    "<p>{{ trans('lang.select_coupon_expdate_error') }}</p>");
                                window.scrollTo(0, 0);
                            } else if (codeAlreadyExist == true) {
                                $(".error_top").show();
                                $(".error_top").html("");
                                $(".error_top").append(
                                    "<p>{{ trans('lang.enter_coupon_code_already_exist_error') }}</p>"
                                    );
                                window.scrollTo(0, 0);
                            } else if (discountType == "Percentage" && (discount >= 100 || discount <
                                0)) {
                                $(".error_top").show();
                                $(".error_top").html("");
                                $(".error_top").append(
                                    "<p>{{ trans('lang.enter_coupon_percentage_discount_error') }}</p>"
                                    );
                                window.scrollTo(0, 0);
                            } else {
                                jQuery("#data-table_processing").show();
                                storeImageData().then(IMG => {
                                    database.collection('rental_coupons').doc(id).update({
                                        'code': code,
                                        'description': description,
                                        'discount': discount,
                                        'expiresAt': expiresAt,
                                        'isEnabled': isEnabled,
                                        'id': id,
                                        'discountType': discountType,
                                        'image': IMG,
                                'sectionId': section_id || null,
                                    }).then(function(result) {
                                        window.location.href =
                                            '{{ route('rentaldiscount') }}';
                                    });
                                }).catch(err => {
                                    jQuery("#data-table_processing").hide();
                                    $(".error_top").show();
                                    $(".error_top").html("");
                                    $(".error_top").append("<p>" + err + "</p>");
                                    window.scrollTo(0, 0);
                                });
                            }
                        });
                })

            })


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
                        photo = filePayload;
                        fileName = filename;
                        $(".coupon_image").empty();
                        $(".coupon_image").append('<img class="rounded" style="width:50px" src="' + photo +
                            '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image">');

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

                        photo = photo.replace(/^data:image\/[a-z]+;base64,/, "");

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
