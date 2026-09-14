@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.category_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a
                        href="{!! route('ondemandcategory') !!}">{{trans('lang.category_plural')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.category_create')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="cat-edite-page max-width-box">
            <div class="card  pb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                        <li role="presentation" class="nav-item">
                            <a href="#category_information" aria-controls="description" role="tab" data-toggle="tab"
                                class="nav-link active">{{trans('lang.category_create')}}</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="error_top" style="display:none"></div>
                    <div class="row vendor_payout_create" role="tabpanel">
                        <div class="vendor_payout_create-inner tab-content">
                            <div role="tabpanel" class="tab-pane active" id="category_information">
                                <fieldset>
                                    <legend>{{trans('lang.category_create')}}</legend>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{trans('lang.category_name')}}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control cat-name">
                                            <div class="form-text text-muted">{{ trans("lang.category_name_help") }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label ">{{trans('lang.select_parent_category')}}</label>
                                        <div class="col-7">
                                            <select name="parent_category_id" id="parent_category_id"
                                                class="form-control">
                                                <option value="">{{trans('lang.select_category')}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row width-100">
                                        <label class="col-3 control-label">{{trans('lang.category_image')}}</label>
                                        <div class="col-7">
                                            <input type="file" id="category_image" onChange="handleFileSelect(event)">
                                            <div class="placeholder_img_thumb cat_image"></div>
                                            <div id="uploding_image"></div>
                                            <div class="form-text text-muted w-50">{{ trans("lang.category_image_help")
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-check width-100">
                                        <input type="checkbox" class="item_publish" id="item_publish">
                                        <label class="col-3 control-label"
                                            for="item_publish">{{trans('lang.item_publish')}}</label>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary save-setting-btn"><i class="fa fa-save"></i>
                        {{trans('lang.save')}}
                    </button>
                    <a href="{!! route('ondemandcategory') !!}" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
@endsection
@section('scripts')

<script type="text/javascript">

    var section_id = getCookie('section_id') || '';
    var database = firebase.firestore();
    var ref = database.collection('provider_categories');
    var ref_category = database.collection('provider_categories');
    
    var photo = "";
    var fileName = '';
    var id_category = database.collection("tmp").doc().id;
    var category_length = 1;
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })
    $(document).ready(function () {

        ref_category.where('parentCategoryId', '==', null).where('sectionId', '==', section_id).get().then(async function (snapshots) {
            if (snapshots.docs.length > 0) {
                $('#parent_category_id').html('<option value="">{{trans("lang.select_category")}}</option>');
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    $('#parent_category_id').append($("<option></option>")
                        .attr("value", data.id)
                        .text(data.title));
                });
            } else {
                $('#parent_category_id').html('<option value="">{{trans("lang.select_category")}}</option>');
            }
        });
        
        jQuery("#data-table_processing").show();
        ref.get().then(async function (snapshots) {
            category_length = snapshots.size + 1;
            jQuery("#data-table_processing").hide();
        })

        $(".save-setting-btn").click(async function () {

            var title = $(".cat-name").val();
            var itemPublish = $(".item_publish").is(":checked");
            var parentCategoryId = $('#parent_category_id').val();
            var level = parentCategoryId == "" ? 0 : 1;
            
            if (title == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_cat_title_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (photo == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.upload_image_error')}}</p>");
                window.scrollTo(0, 0);
            } else {
                jQuery("#data-table_processing").show();
                storeImageData().then(IMG => {
                    database.collection('provider_categories').doc(id_category).set({
                        'id': id_category,
                        'title': title,
                        'publish': itemPublish,
                        'image': IMG,
                        'parentCategoryId': parentCategoryId ? parentCategoryId : null,
                        'level': parseInt(level),
                        'sectionId': section_id
                    }).then(function (result) {
                        window.location.href = '{{ route("ondemandcategory")}}';
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
    });
    var storageRef = firebase.storage().ref('images');
    async function storeImageData() {
        var newPhoto = '';
        try {
            if (photo != "") {
                photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")
                var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', { contentType: 'image/jpg' });
                var downloadURL = await uploadTask.ref.getDownloadURL();
                newPhoto = downloadURL;
                photo = downloadURL;
            }
        } catch (error) {
            console.log("ERR ===", error);
        }
        return newPhoto;
    }
    function handleFileSelect(evt) {
        var f = evt.target.files[0];
        var reader = new FileReader();
        reader.onload = (function (theFile) {
            return function (e) {
                var filePayload = e.target.result;
                var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                var val = $('#category_image').val().toLowerCase();
                var ext = val.split('.')[1];
                var docName = val.split('fakepath')[1];
                var filename = $('#category_image').val().replace(/C:\\fakepath\\/i, '')
                var timestamp = Number(new Date());
                var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                photo = filePayload;
                fileName = filename;
                $(".cat_image").empty();
                $(".cat_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
            };
        })(f);
        reader.readAsDataURL(f);
    }
</script>
@endsection