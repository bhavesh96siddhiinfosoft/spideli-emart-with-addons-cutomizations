@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="row page-titles">


        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.destination')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>

                <li class="breadcrumb-item"><a href="{!! route('destinations') !!}">{{trans('lang.destination')}}</a>
                </li>

                <li class="breadcrumb-item active">{{trans('lang.destination_edit')}}</li>

            </ol>

        </div>

    </div>

    <div class="card-body">

        <div class="error_top"></div>

        <div class="row vendor_payout_create">

            <div class="vendor_payout_create-inner">

                <fieldset>

                    <legend>{{trans('lang.destination')}}</legend>


                    <div class="form-group row width-50">

                        <label class="col-3 control-label">{{trans('lang.title')}}</label>

                        <div class="col-7">

                            <input type="text" class="form-control title">

                        </div>

                    </div>                   

                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.vendor_latitude')}}</label>
                        <div class="col-7">
                            <input class="form-control latitude" type="number" min="-90" max="90"
                                onkeypress="return chkAlphabets3(event,'error1')">
                            <div id="error1" class="err"></div>
                        </div>
                    </div>

                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.vendor_longitude')}}</label>
                        <div class="col-7">
                            <input class="form-control longitude" type="number" min="-180" max="180"
                                onkeypress="return chkAlphabets3(event,'error2')">
                            <div id="error2" class="err"></div>
                        </div>
                    </div>

                    <div class="form-group row width-100">
                        <div class="col-12">
                            <h6>
                                {{ trans("lang.know_your_cordinates") }}
                                <a target="_blank"
                                    href="https://www.latlong.net/">{{trans("lang.latitude_and_longitude_finder") }}</a>
                            </h6>
                        </div>
                    </div>

                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.image')}}</label>
                        <div class="col-7">
                            <input type="file" onChange="handleFileSelect(event)">
                            <div class="placeholder_img_thumb destination_image"></div>
                            <div id="uploding_image"></div>
                        </div>
                    </div>

                    <div class="form-group row width-50">

                        <div class="form-check width-50">

                            <input type="checkbox" id="is_publish">

                            <label class="col-3 control-label" for="is_publish">{{trans('lang.is_publish')}}</label>

                        </div>

                    </div>



                </fieldset>

            </div>
        </div>

    </div>

    <div class="form-group col-12 text-center">

        <button type="button" class="btn btn-primary  edit-form-btn"><i class="fa fa-save"></i>
            {{trans('lang.save')}}
        </button>

        <a href="{!! route('destinations') !!}" class="btn btn-default"><i
                class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>

    </div>

</div>

@endsection

@section('scripts')

<script type="text/javascript">

    var database = firebase.firestore();
    var storageRef = firebase.storage().ref('images');

    var photo = "";
    var fileName = '';
    var oldImageFile = "";
    var storage = firebase.storage();

    var id = "<?php echo $id; ?>";
    var ref = database.collection('popular_destinations').where("id", "==", id);
    var sectionId = getCookie('section_id');

    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    });

    $(document).ready(function () {

        jQuery("#data-table_processing").show();

        ref.get().then(async function (snapshots) {

            var menuItems = snapshots.docs[0].data();

            $(".title").val(menuItems.title);

            $(".latitude").val(menuItems.latitude);

            $(".longitude").val(menuItems.longitude);
            if (menuItems.image != '' && menuItems.image != null) {
                photo = menuItems.image;
                oldImageFile = menuItems.image;
                $(".destination_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">');

            } else {
                $(".destination_image").append('<img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">');

            }

            if (menuItems.is_publish) {
                $("#is_publish").prop("checked", true);
            }           

            jQuery("#data-table_processing").hide();

        });
    });

    $(".edit-form-btn").click(function () {

        var title = $(".title").val();
        var latitude = parseFloat($(".latitude").val());
        var longitude = parseFloat($(".longitude").val());
        var is_publish = $("#is_publish").is(':checked');


        if (title == '') {

            $(".error_top").show();

            $(".error_top").html("");

            $(".error_top").append("<p>{{trans('lang.title_error')}}</p>");

            window.scrollTo(0, 0);

        } else if ( $(".latitude").val().trim() === '' ||  $(".longitude").val().trim() === '' ) {

            $(".error_top").show();

            $(".error_top").html("");

            $(".error_top").append("<p>{{trans('lang.latlong_error')}}</p>");

            window.scrollTo(0, 0);

        } else {
            jQuery("#data-table_processing").show();
            storeImageData().then(IMG => {
                database.collection('popular_destinations').doc(id).update({
                    'id': id,
                    'title': title,
                    'image': IMG,
                    'latitude': latitude,
                    'longitude': longitude,
                    'is_publish': is_publish,
                    'sectionId': sectionId
                }).then(function (result) {

                    window.location.href = '{{ route("destinations")}}';

                }).catch(function (error) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>" + error + "</p>");

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
                fileName=filename;
                photo=filePayload;
                $(".destination_image").html('<img class="rounded" style="50px" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image">');

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

    function chkAlphabets3(event, msg) {
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
            document.getElementById(msg).innerHTML = "Accept only Number and Dot(.)";
            return false;
        }
        else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

</script>

@endsection