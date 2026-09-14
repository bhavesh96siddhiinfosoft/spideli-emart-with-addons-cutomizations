@extends('layouts.app')


@section('content')

<div class="page-wrapper">
    <div class="row page-titles">


        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.menu_items')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>

                <li class="breadcrumb-item"><a href="{!! route('banners') !!}">{{trans('lang.menu_items')}}</a>
                </li>

                <li class="breadcrumb-item active">{{trans('lang.menu_items_edit')}}</li>

            </ol>

        </div>

    </div>

    <div class="card-body">



        <div class="error_top"></div>

        <div class="row vendor_payout_create">

            <div class="vendor_payout_create-inner">

                <fieldset>

                    <legend>
                        {{trans('lang.menu_items')}}
                    </legend>

                    <div class="form-group row width-50">

                        <label class="col-3 control-label">{{trans('lang.title')}}</label>

                        <div class="col-7">

                            <input type="text" class="form-control title">

                        </div>

                    </div>
                    <div class="form-group row width-50">

                        <label class="col-3 control-label">{{trans('lang.set_order')}}</label>

                        <div class="col-7">

                            <input type="number" class="form-control set_order" min="0">

                        </div>

                    </div>


                    <div class="form-group row width-50">
                        <label class="col-3 control-label ">{{trans('lang.select_section')}}</label>
                        <div class="col-7">
                            <select name="section_id" id="section_id" class="form-control">
                                <option value="">{{trans('lang.select')}}</option>
                            </select>
                            <p style="color: red;font-size: 13px;"> {{trans('lang.rental_parcel_cab_service_are_not')}}
                            </p>
                        </div>
                    </div>

                    <div class="form-group row width-50" id="banner_position" style="display:none">
                        <label class="col-3 control-label ">{{trans('lang.banner_position')}}</label>
                        <div class="col-7">
                            <select name="position" id="position" class="form-control">
                                <option value="top">{{trans('lang.top')}}</option>
                                <option value="middle">{{trans('lang.middle')}}</option>
                            </select>
                        </div>
                    </div>


                    <div class="form-group row width-100 radio-form-row" id="redirect_type_div" style="display: none;">
                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="store" name="redirect_type" id="store">
                            <label class="custom-control-label">{{trans('lang.vendor')}}</label>
                        </div>

                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="product" name="redirect_type" id="product">

                            <label class="custom-control-label">{{trans('lang.product')}}</label>
                        </div>

                        <div class="radio-form col-md-4">
                            <input type="radio" class="redirect_type" value="external_link" name="redirect_type">

                            <label class="custom-control-label">{{trans('lang.external_link')}}</label>
                        </div>
                    </div>

                    <div class="form-group row width-50" id="vendor_div" style="display: none;">
                        <label class="col-3 control-label ">{{trans('lang.vendor')}}</label>
                        <div class="col-7">
                            <select name="storeId" id="storeId" class="form-control">

                            </select>
                        </div>
                    </div>

                    <div class="form-group row width-50" id="product_div" style="display: none;">
                        <label class="col-3 control-label ">{{trans('lang.product')}}</label>
                        <div class="col-7">
                            <select name="productId" id="productId" class="form-control">

                            </select>
                        </div>
                    </div>

                    <div class="form-group row width-100" id="external_link_div" style="display: none;">

                        <label class="col-3 control-label">{{trans('lang.external_link')}}</label>

                        <div class="col-7">

                            <input type="text" class="form-control" id="external_link">

                        </div>

                    </div>

                    <div class="form-group row width-100">

                        <div class="form-check width-100">

                            <input type="checkbox" id="is_publish">

                            <label class="col-3 control-label" for="is_publish">{{trans('lang.is_publish')}}</label>

                        </div>

                    </div>

                    <div class="form-group row width-50">

                        <label class="col-3 control-label">{{trans('lang.app_banner')}}</label>

                        <input type="file" id="banner_img" onChange="handleFileSelect(event)" class="col-7">

                        <div id="uploding_image"></div>

                        <div class="placeholder_img_thumb user_image"></div>
                    </div>
                    <div class="form-group row width-50">
                    
                        <label class="col-3 control-label">{{trans('lang.web_banner')}}</label>
                        <div class="col-7">
                            <input type="file" id="web_banner_img" onChange="handleWebBannerFileSelect(event)">
                            <div id="uploding_image"></div>
                        </div>
                        <div class="placeholder_img_thumb web_banner_image"></div>
                    </div>

                </fieldset>

            </div>
        </div>

    </div>

    <div class="form-group col-12 text-center btm-btn">

        <button type="button" class="btn btn-primary  edit-setting-btn"><i class="fa fa-save"></i>
            {{trans('lang.save')}}
        </button>

        <a href="{!! route('banners') !!}" class="btn btn-default"><i
                class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>

    </div>

</div>


@endsection

@section('scripts')


<script type="text/javascript">

    var database = firebase.firestore();

    var photo = "";
    var fileName = "";
    var webPhoto="";
    var webFileName="";
    var oldWebFile="";
    var oldImageFile = "";
    var storageRef = firebase.storage().ref('images');
    var storage = firebase.storage();

    var id = "<?php echo $id; ?>";

    var ref = database.collection('banner_items').where("id", "==", id);

    var ref_sections = database.collection('sections').orderBy('order');
    var sections_list = [];
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })

    $(document).ready(function () {
        ref_sections.get().then(async function (snapshots) {

            $("#section_id").append('<optgroup label="Multivendor Delivery Service"></optgroup');

            snapshots.docs.forEach((listval) => {
                var data = listval.data();

                if (data.serviceTypeFlag == "delivery-service") {
                    sections_list.push(data);
                    $('#section_id').append($("<option></option>")
                        .attr("value", data.id).attr("data-service-type", data.serviceTypeFlag)
                        .text(data.name));
                }
            })

            $("#section_id").append('<optgroup label="Ecommerce Service"><optgroup');
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                if (data.serviceTypeFlag == "ecommerce-service") {
                    sections_list.push(data);
                    $('#section_id').append($("<option></option>")
                        .attr("value", data.id).attr("data-service-type", data.serviceTypeFlag)
                        .text(data.name));
                }
            })

            $("#section_id").append('<optgroup label="Parcel Delivery Service"><optgroup');
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                if (data.serviceTypeFlag == "parcel_delivery") {
                    sections_list.push(data);
                    $('#section_id').append($("<option></option>")
                        .attr("value", data.id).attr("data-service-type", data.serviceTypeFlag)
                        .text(data.name));
                }
            })

            $("#section_id").append('<optgroup label="Cab Service"><optgroup');
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                if (data.serviceTypeFlag == "cab-service") {
                    sections_list.push(data);
                    $('#section_id').append($("<option></option>")
                        .attr("value", data.id).attr("data-service-type", data.serviceTypeFlag)
                        .text(data.name));
                }
            })

            $("#section_id").append('<optgroup label="On Demand Service"><optgroup');
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                if (data.serviceTypeFlag == "ondemand-service") {

                    sections_list.push(data);
                    $('#section_id').append($("<option></option>")
                        .attr("value", data.id).attr("data-service-type", data.serviceTypeFlag)
                        .text(data.name));
                }
            })
            
        });

        $("input[name='redirect_type']:radio").change(function () {

            var redirect_type = $(this).val();
            var section_id = $("#section_id").val();

            if (redirect_type == "store") {

                getTypeWiseDetails('store', section_id);


                $('#vendor_div').show();
                $('#product_div').hide();
                $('#external_link_div').hide();
            } else if (redirect_type == "product") {

                getTypeWiseDetails('product', section_id);


                $('#vendor_div').hide();
                $('#product_div').show();
                $('#external_link_div').hide();
            } else if (redirect_type == "external_link") {
                $('#vendor_div').hide();
                $('#product_div').hide();
                $('#external_link_div').show();
            }

        });

    });

    $(document).ready(function () {

        jQuery("#data-table_processing").show();


        ref.get().then(async function (snapshots) {

            var menuItems = snapshots.docs[0].data();
            $(".title").val(menuItems.title);
            $(".set_order").val(menuItems.set_order);

            if (menuItems.is_publish) {
                $("#is_publish").prop("checked", true);
            }

            if (menuItems.hasOwnProperty('sectionId')) {
                $('#section_id').val(menuItems.sectionId).trigger('change');
            }

            photo = menuItems.photo;
            if (photo != '') {
                oldImageFile = menuItems.photo;
                $(".user_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">');

            }
            if(menuItems.hasOwnProperty('web_banner') && menuItems.web_banner!='' && menuItems.web_banner!=null){
                webPhoto=menuItems.web_banner;
                oldWebFile=menuItems.web_banner;
                $(".web_banner_image").append('<img class="rounded" style="width:50px" src="'+webPhoto+'" alt="image" onerror="this.onerror=null;this.src=\''+placeholderImage+'\'">');
 
            }


            if (menuItems.redirect_type != '') {

                var redirect_type = menuItems.redirect_type;

                var redirect_id = menuItems.redirect_id;

                $("input[name=redirect_type][value=" + redirect_type + "]").attr('checked', 'checked');
                if (menuItems.hasOwnProperty('sectionId')) {

                    if (redirect_type == "store") {

                        getTypeWiseDetails('store', menuItems.sectionId, redirect_id);


                        $('#vendor_div').show();
                        $('#product_div').hide();
                        $('#external_link_div').hide();
                    } else if (redirect_type == "product") {

                        getTypeWiseDetails('product', menuItems.sectionId, redirect_id);


                        $('#vendor_div').hide();
                        $('#product_div').show();
                        $('#external_link_div').hide();
                    } else if (redirect_type == "external_link") {
                        $('#vendor_div').hide();
                        $('#product_div').hide();
                        $('#external_link_div').show();
                        $('#external_link').val(redirect_id);
                    }

                }
            }

            if (
                $("#section_id").val() && 
                (
                    $("#section_id").find(':selected').data('service-type') == "ecommerce-service" || 
                    $("#section_id").find(':selected').data('service-type') == "delivery-service" || 
                    $("#section_id").find(':selected').data('service-type') == "ondemand-service"
                )
            ) {
                $("#position").val(menuItems.position);
                $("#banner_position").show();
            } else {
                $("#banner_position").hide();

            }

            jQuery("#data-table_processing").hide();

        });
    });

    $("#section_id").change(function () {

        var service_type = $(this).find(':selected').data('service-type')
        var section_id = $(this).val();
        
        if (service_type == "ecommerce-service" || service_type == "delivery-service") {
        
            $("#banner_position").show();
            $("#redirect_type_div").addClass('d-flex');
            $("#redirect_type_div").show();

            var redirect_type = $(".redirect_type:checked").val();

            if (redirect_type == "store") {
                getTypeWiseDetails('store', section_id);
                $('#vendor_div').show();
                $('#product_div').hide();
                $('#external_link_div').hide();
            } else if (redirect_type == "product") {
                getTypeWiseDetails('product', section_id);
                $('#vendor_div').hide();
                $('#product_div').show();
                $('#external_link_div').hide();
            } else if (redirect_type == "external_link") {
                $('#vendor_div').hide();
                $('#product_div').hide();
                $('#external_link_div').show();
            }

        } else {
            $("#banner_position").hide();
            $("#redirect_type_div").removeClass('d-flex');
            $("#vendor_div").hide();
            $('#product_div').hide();
            $('#external_link_div').hide();
            $("#redirect_type_div").hide();
        }

        if(service_type == "ondemand-service"){
            $("#banner_position").show();
        }
    });

    function handleFileSelect(evt) {
		var f = evt.target.files[0];
		var reader = new FileReader();
		reader.onload = (function (theFile) {
			return function (e) {
				var filePayload = e.target.result;
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
                $("#banner_img").val('');			};
		})(f);
		reader.readAsDataURL(f);
	}
        function handleWebBannerFileSelect(evt) {
            var f=evt.target.files[0];
            var reader=new FileReader();
            reader.onload=(function(theFile) {
                return function(e) {
                    var filePayload=e.target.result;
                    var val=f.name;
                    var ext=val.split('.')[1];
                    var docName=val.split('fakepath')[1];
                    var filename=(f.name).replace(/C:\\fakepath\\/i,'')
                    var timestamp=Number(new Date());
                    var filename=filename.split('.')[0]+"_"+timestamp+'.'+ext;
                    webPhoto=filePayload;
                    webFileName=filename;
                    $(".web_banner_image").empty();
                    $(".web_banner_image").append('<img class="rounded" style="width:50px" src="'+webPhoto+'" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">');
                    $("#web_banner_image").val('');
                };
            })(f);
            reader.readAsDataURL(f);
        }

    $(".edit-setting-btn").click(function () {

        var section = $('#section_id').val();
        var title = $(".title").val();
        var set_order = parseInt($('.set_order').val());
        var is_publish = false;
        var position = $("#position").val();
        var redirect_type = "";

        if ($(".redirect_type").is(":visible")) {
            redirect_type = $(".redirect_type:checked").val();
        }

        var redirect_id = "";

        var checkFlag = true;
        var checkFlagRedirection = true;
        if (redirect_type == "store") {
            redirect_id = $('#storeId').val();

            if (redirect_id == "") {
                checkFlag = false;
                checkFlagRedirection = "store";
            }


        } else if (redirect_type == "product") {
            redirect_id = $('#productId').val();

            if (redirect_id == "") {
                checkFlag = false;
                checkFlagRedirection = "product";

            }

        } else if (redirect_type == "external_link") {
            redirect_id = $('#external_link').val();

            if (redirect_id == "") {
                checkFlag = false;
                checkFlagRedirection = "external_link";

            }
        }

        if ($("#is_publish").is(':checked')) {
            is_publish = true;
        }

        var storeId = $("#storeId").val();
        var productId = $('#productId').val();
        var ext = $("#external_link").val();

        var store_rd = $("#store").is(':checked');
        var product_rd = $("#product").is(':checked');
        var ext_rd = $("#external").is(':checked');

        if (title == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.title_error')}}</p>");
            window.scrollTo(0, 0);
        } else if (isNaN(set_order)) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.set_order_error')}}</p>");
            window.scrollTo(0, 0);
        } else if (section == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.set_section_error')}}</p>");
            window.scrollTo(0, 0);
        } else if(storeId == '' && store_rd == true) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.set_store_error')}}</p>");
            window.scrollTo(0, 0);
        } else if(productId == '' && product_rd == true) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.set_product_error')}}</p>");
            window.scrollTo(0, 0);
        } else if(ext == '' && ext_rd == true) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.set_external_error')}}</p>");
            window.scrollTo(0, 0);
        } else if (photo == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.please_choose_banner')}}</p>");
            window.scrollTo(0, 0);
        } else if (checkFlag == false) {
            $(".error_top").show();
            $(".error_top").html("");
            if (checkFlagRedirection == "external_link") {
                $(".error_top").append("<p>{{trans('lang.please_enter_external_link')}}</p>");
            } else {
                $(".error_top").append("<p>{{trans('lang.please_select_your')}} "+checkFlagRedirection+"</p>");
            }
            window.scrollTo(0, 0);
        } else {

            jQuery("#data-table_processing").show();
            
            storeImageData().then(IMG => {
                storeWebImageData().then(webIMG => {
                database.collection('banner_items').doc(id).update({
                    'title': title,
                    'photo': IMG,
                    'web_banner': webIMG,
                    'id': id,
                    'set_order': set_order,
                    'is_publish': is_publish,
                    'sectionId': section,
                    'position': position ? position : 'top',
                    'redirect_type': redirect_type ? redirect_type : '',
                    'redirect_id': redirect_id ? redirect_id : '',
                }).then(function (result) {
                    window.location.href = '{{ route("banners")}}';

                }).catch(function (error) {
                    jQuery("#data-table_processing").hide();
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>" + error + "</p>");

                });
            }).catch(function (error) {
                jQuery("#data-table_processing").hide();
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>" + error + "</p>");

            });
              }).catch(function(error) {
                jQuery("#data-table_processing").hide();
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>"+error+"</p>");

            });
        }
    });

    function getTypeWiseDetails(redirect_type, sectionId, redirect_id = '') {

        if (redirect_type == "store") {

            $('#storeId').html("");
            $('#storeId').append($("<option value=''>{{trans('lang.select_vendor')}}</option>"));
                
            var ref_vendors = database.collection('vendors').where('section_id', '==', sectionId);

            ref_vendors.get().then(async function (snapshots) {

                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    $('#storeId').append($("<option></option>")
                        .attr("value", data.id)
                        .text(data.title));
                })

                if (redirect_id) {

                    $('#storeId').val(redirect_id);

                }
            })

        } else if (redirect_type == "product") {

            $('#productId').html("");
            $('#productId').append($("<option value=''>{{trans('lang.select_product')}}</option>"));
            var ref_vendor_products = database.collection('vendor_products').where('section_id', '==', sectionId);

            ref_vendor_products.get().then(async function (snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    $('#productId').append($("<option></option>")
                        .attr("value", data.id)
                        .text(data.name));
                })
                if (redirect_id) {
                    $('#productId').val(redirect_id);
                }
            })

        }
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
                    var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', { contentType: 'image/jpg' });
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

    async function storeWebImageData() {
            var newPhoto='';
            try {
                if(oldWebFile!=""&& webPhoto!=oldWebFile) {
                    var oldImageUrl=await storage.refFromURL(oldWebFile);
                    imageBucket=oldImageUrl.bucket;
                    var envBucket="<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                    if(imageBucket==envBucket) {
                        await oldImageUrl.delete().then(() => {
                            console.log("Old file deleted!")
                        }).catch((error) => {
                            console.log("ERR File delete ===",error);
                        });
                    } else {
                        console.log('Bucket not matched');
                    }
                }
                if(webPhoto!=oldWebFile) {
                    webPhoto=webPhoto.replace(/^data:image\/[a-z]+;base64,/,"")
                    var uploadTask=await storageRef.child(webFileName).putString(webPhoto,'base64',{contentType: 'image/jpg'});
                    var downloadURL=await uploadTask.ref.getDownloadURL();
                    newPhoto=downloadURL;
                    webPhoto=downloadURL;

                } else {
                    newPhoto=webPhoto;
                }
            } catch(error) {
                console.log("ERR ===",error);
            }
            return newPhoto;
        }
    

</script>

@endsection