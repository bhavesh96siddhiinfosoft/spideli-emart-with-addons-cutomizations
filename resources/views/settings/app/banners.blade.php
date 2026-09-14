@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">

            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{trans('lang.app_setting_banners')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.app_setting_banners')}}</li>
                </ol>
            </div>
        </div>

        <div class="card-body">
            <div class="error_top" style="display:none"></div>

            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend><i class="mr-3 fa fa-facebook"></i>{{trans('lang.app_setting_banners_fieldset')}}</legend>
                        <div class="form-group row width-50 vendor_image">
                            <label class="col-3 control-label">{{trans('lang.app_setting_upload_app_banners')}}</label>
                            <input type="file" class="col-7" onChange="handleFileSelect(event)">
                            <div id="uploding_image" class="ml-3 mt-1"></div>
                            <div class="app-home-banners mt-2"></div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>

        <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i> {{trans('lang.save')}}</button>
                <a href="{{url('/dashboard')}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}
            </a>
        </div>
    </div>

@endsection

@section('scripts')

    <script type="text/javascript">

        var database = firebase.firestore();
        var storageRef = firebase.storage().ref('images');
        var appHomeBanners = database.collection('settings').doc("AppHomeBanners");

        var app_banners = [];
        var app_new_banners = [];
        var app_new_banners_filename = [];
        var app_banners_to_delete = [];
        var photocount = 0;
        var photo = "";

        var place_image = '';
        var ref_place = database.collection('settings').doc("placeHolderImage");
        ref_place.get().then(async function (snapshots) {
            var placeHolderImage = snapshots.data();
            place_image = placeHolderImage.image;
        });

        $(document).ready(function () {

            jQuery("#data-table_processing").show();
          
            appHomeBanners.get().then(async function (snapshots) {
                
                var data = snapshots.data();
                app_banners = data.banners;
                if (Array.isArray(data.banners) && data.banners.length > 0) {
                    let banners_html = '';
                    data.banners.forEach((photo) => {
                        photocount++;
                        banners_html = banners_html + '<span class="image-item" id="photo_' + photocount + '"><span class="remove-btn" data-id="' + photocount + '" data-img="' + photo + '" data-status="old"><i class="fa fa-remove"></i></span><img width="100px" id="" height="auto" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"></span>';
                    })
                    $(".app-home-banners").html(banners_html);
                }
                jQuery("#data-table_processing").hide();
            })

            $(".edit-setting-btn").click(async function () {

                if (app_banners.length == 0 && app_new_banners.length == 0){
                    
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.error_upload_app_banners')}}</p>");
                    window.scrollTo(0, 0);

                } else {

                    jQuery("#data-table_processing").show();

                    await storeAppBannerImages().then(async (banners) => {
                        database.collection('settings').doc("AppHomeBanners").update({
                            'banners': banners,
                        }).then(function (result) {
                            window.location.reload();
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

            $(document).on("click", ".remove-btn", function() {

                var id = $(this).attr('data-id');
                var photo_remove = $(this).attr('data-img');
                var status=$(this).attr('data-status');
                if(status=="old"){
                    app_banners_to_delete.push(firebase.storage().refFromURL(photo_remove));
                }
                $("#photo_" + id).remove();
                index = app_banners.indexOf(photo_remove);
                if (index > -1) {
                    app_banners.splice(index, 1); // 2nd parameter means remove one item only
                }
                index = app_new_banners.indexOf(photo_remove);
                if (index > -1) {
                    app_new_banners.splice(index, 1); // 2nd parameter means remove one item only
                    app_new_banners_filename.splice(index, 1);
                }
            });
        })
        
        function handleFileSelect(evt) {

            var f = evt.target.files[0];
            var reader = new FileReader();

            reader.onload = (function (theFile) {
                return function (e) {

                    var filePayload = e.target.result;
                    var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')

                    var timestamp = Number(new Date());
                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                    var uploadTask = storageRef.child(filename).put(theFile);
                    uploadTask.on('state_changed', function (snapshot) {
                        var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                        jQuery("#uploding_image").text("Image is uploading...").show();
                    }, function (error) {

                    }, function () {
                        uploadTask.snapshot.ref.getDownloadURL().then(function (downloadURL) {
                            jQuery("#uploding_image").text("Upload is completed").show().delay(3000).fadeOut();
                            photo = filePayload;
                            photocount++;
                            let banner_html = '<span class="image-item" id="photo_' + photocount + '"><span class="remove-btn" data-id="' + photocount + '" data-img="' + photo + '" data-status="new"><i class="fa fa-remove"></i></span><img width="100px" id="" height="auto" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"></span>';
                            $(".app-home-banners").append(banner_html);
                            app_new_banners.push(photo);
                            app_new_banners_filename.push(filename);
                        });
                    });

                };
            })(f);
            reader.readAsDataURL(f);
        }

        async function storeAppBannerImages() {

            var imageFiles = [];
            if (app_banners.length > 0) {
                imageFiles = app_banners;
            }

            if (app_new_banners.length > 0) {
                await Promise.all(app_new_banners.map(async (imageItem, index) => {
                    imageItem = imageItem.replace(/^data:image\/[a-z]+;base64,/, "");
                    var uploadTask = await storageRef.child(app_new_banners_filename[index]).putString(imageItem, 'base64', {contentType: 'image/jpg'});
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    imageFiles.push(downloadURL);
                }));
            }

            if (app_banners_to_delete.length > 0) {
                await Promise.all(app_banners_to_delete.map(async (imageFile, index) => {
                    var imageUrlRef = await firebase.storage().refFromURL(imageFile);
                    imageBucket = imageUrlRef.bucket;
                    var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                    if (imageBucket == envBucket) {
                        await imageUrlRef.delete().then(() => {
                            console.log("Old file deleted!")
                        }).catch((error) => {
                            console.log("ERR File delete ===", error);
                        });
                    } else {
                        console.log('Bucket not matched');
                    }
                }));
            }

            return imageFiles;
        }

    </script>

@endsection