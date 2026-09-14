@extends('layouts.app')

@section('content')
<style>
    #autocomplete-list {
        border: 1px solid #d4d4d4;
        z-index: 99;
        position: absolute;
        background-color: white;
        cursor: pointer;
    }
    .autocomplete-item {
        padding: 10px;
        border-bottom: 1px solid #d4d4d4;
    }
    .autocomplete-item:hover {
        background-color: #e9e9e9;
    }
</style>
<div class="page-wrapper">
    <div class="row page-titles">

        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.worker_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>

                @if(!isset($_GET['id']))
                <li class="breadcrumb-item"><a href="{!! route('ondemand.workers.index') !!}">{{trans('lang.worker_table')}}</a>
                </li>
                @else
                <li class="breadcrumb-item"><a href="{!! route('ondemand.workers.index',@$_GET['id']) !!}">{{trans('lang.worker_table')}}</a>
                </li>
                @endif

                <li class="breadcrumb-item">{{trans('lang.worker_edit')}}</li>
            </ol>
        </div>
    </div>

    <div class="card-body">
        <div class="error_top"></div>
        <div class="row vendor_payout_create">
            <div class="vendor_payout_create-inner">
                <fieldset>
                    <legend>{{trans('lang.worker_edit')}}</legend>

                    <div class="form-group row width-50">
                        <input type="hidden" class="form-control author_profile">
                        <label class="col-3 control-label">{{trans('lang.first_name')}}</label>
                        <div class="col-7">
                            <input type="text" class="form-control first_name">
                            <div class="form-text text-muted" min="0">
                                {{ trans("lang.user_first_name_help") }}
                            </div>
                        </div>
                    </div>


                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{ trans('lang.last_name')}}</label>
                        <div class="col-7">
                            <input type="text" class="form-control last_name">
                            <div class="form-text text-muted" min="0">
                                {{ trans("lang.user_last_name_help") }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.email')}}</label>
                        <div class="col-7">
                            <input type="text" class="form-control email">
                            <div class="form-text text-muted">
                                {{ trans("lang.user_email_help") }}
                            </div>
                        </div>
                    </div>


                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.user_phone')}}</label>
                        <div class="col-7">
                            <input type="text" class="form-control phone"
                                onkeypress="return chkAlphabets2(event,'error1')">
                            <div id="error1" class="err"></div>
                            <div class="form-text text-muted w-50">
                                {{ trans("lang.user_phone_help") }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.salary')}}</label>
                        <div class="col-7">
                            <input type="number" class="form-control salary">
                            <div class="form-text text-muted">
                                {{ trans("lang.user_salary_help") }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.address')}}</label>
                        <div class="col-7">
                            <input type="text" class="form-control address" id="address" autocomplete="on">
                            <div id="autocomplete-list"></div>

                        </div>
                    </div>
                    @if(!isset($_GET['id']))
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{trans('lang.provider')}}</label>
                            <div class="col-7">
                                <select id="provider_select" class="form-control">
                                    <option value="">{{trans('lang.select_provider')}}</option>
                                </select>
                                <div class="form-text text-muted">
                                    {{ trans("lang.coupon_provider_help") }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.user_profile_picture')}}</label>
                        <input type="file" onChange="handleFileSelectowner(event)" class="col-7">
                        <div id="uploding_image_owner"></div>
                        <div class="uploaded_image_owner" style="display:none;"></div>
                    </div>
                    <div class="form-group row width-50">
                        <div class="form-check">
                            <input type="checkbox" class="item_publish" id="item_publish">
                            <label class="col-3 control-label" for="item_publish">{{trans('lang.active')}}</label>
                        </div>
                    </div>

                </fieldset>
            </div>
        </div>
    </div>

    <div class="form-group col-12 text-center btm-btn">
        <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i>
            {{trans('lang.save')}}
        </button>
        @if(!isset($_GET['id']))
        <a href="{!! route('ondemand.workers.index') !!}" class="btn btn-default"><i
                class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        @else
         <a href="{!! route('ondemand.workers.index',@$_GET['id']) !!}" class="btn btn-default"><i
                class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        @endif
    </div>
</div>
</div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.js"></script>

<script>

    var database = firebase.firestore();

    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;
    var createdAt = firebase.firestore.FieldValue.serverTimestamp();
    var id = "<?php echo $id; ?>";
    var workersRef = database.collection('providers_workers').doc(id);
    var workerImagesCount = 0;
    var ownerphoto = '';
    var ownerFileName = '';
    var ownerOldImageFile = '';
    var photo = "";
    var refCurrency = regionCurrencyRef();
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    var allowed_file_size = '';
    var ownerId = '';
    var storage = firebase.storage();
    var storageRef = firebase.storage().ref('images');
    var idOfProviderDetailPage="{{@$_GET['id']}}";
    var mapType = 'ONLINE';
    var section_id = getCookie('section_id');

    database.collection('settings').doc('DriverNearBy').get().then(async function (snapshots) {
        var data = snapshots.data();
        if (data && data.selectedMapType && data.selectedMapType == "osm") {
            mapType = "OFFLINE"
        }
    });
    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;
        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });


    $(document).ready(function () {

        workersRef.get().then(async function (snapshots) {

            var workerData = snapshots.data();

            $(".first_name").val(workerData.firstName)
            $(".last_name").val(workerData.lastName)
            $(".email").val(shortEmail(workerData.email))
            if(workerData.phoneNumber.includes('+')){
                $(".phone").val('+' + EditPhoneNumber(workerData.phoneNumber.slice(1)));
            }else{
                $(".phone").val(EditPhoneNumber(workerData.phoneNumber));
            }
            $("#address").val(workerData.address);
            $('#address').val(workerData.address).attr('data-latitude', workerData.latitude).attr('data-longitude', workerData.longitude);
            $(".salary").val(workerData.salary);
            ownerId = workerData.author;
            if (workerData.active) {
                $("#item_publish").prop('checked', true);
            }
            if (workerData.profilePictureURL != '') {
                ownerOldImageFile = workerData.profilePictureURL;
                ownerphoto = workerData.profilePictureURL;
                $(".uploaded_image_owner").html('<img id="uploaded_image_owner" src="' + ownerphoto + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" width="150px" height="150px;">');
            } else {
                $(".uploaded_image_owner").html('<img id="uploaded_image_owner" src="' + placeholderImage + '"  width="150px" height="150px;">');
            }

            $(".uploaded_image_owner").show();

            database.collection('users').where('role', '==', 'provider').where('section_id','==',section_id).get().then(async function (snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    if (workerData.providerId == data.id) {
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
        });

        $(".edit-form-btn").click(async function () {

            var userFirstName = $(".first_name").val();
            var userLastName = $(".last_name").val();
            var email = $(".email").val();

            var userPhone = $(".phone").val();
            var salary = $(".salary").val();
            var address = $(".address").val();
            var itemPublish = $(".item_publish").is(":checked");
            var authorProfilePic = $('.author_profile').val();
            var longitude = parseFloat($('#address').attr('data-longitude'));
            var latitude = parseFloat($('#address').attr('data-latitude'));
            var providerId = (idOfProviderDetailPage!='') ? idOfProviderDetailPage : $("#provider_select").val();

            if (userFirstName == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_worker_first_name_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (userLastName == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_worker_last_name_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (email == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_worker_email_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (userPhone == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_worker_userphone_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (salary == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_worker_salary_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (isNaN(latitude) || isNaN(longitude)) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_worker_address_error')}}</p>");
                window.scrollTo(0, 0);
            } else {
                jQuery("#data-table_processing").show();

                await storeImageData().then(async (IMG) => {

                    geoFirestore.collection('providers_workers').doc(id).update({
                        'firstName': userFirstName,
                        'lastName': userLastName,
                        'email': email,
                        'phoneNumber': userPhone,
                        'email': email,
                        'salary': salary,
                        "address": address,
                        'profilePictureURL': IMG,
                        'active': itemPublish,
                        'latitude': latitude,
                        'longitude': longitude,
                        'providerId': providerId,
                        coordinates: new firebase.firestore.GeoPoint(latitude, longitude),

                    }).then(function (result) {
                        if(idOfProviderDetailPage!=''){
                            window.location.href = '{{ route("ondemand.workers.index",@$_GET['id'])}}';
                        }else{
                        window.location.href = '{{ route("ondemand.workers.index")}}';
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

    function initialize(id) {
        if (mapType == "OFFLINE"){
                var input = document.getElementById('address');
                var autocompleteList = document.getElementById('autocomplete-list');
                input.addEventListener('input', function() {
                    var query = this.value;
                    if (query.length < 3) {
                        autocompleteList.innerHTML = '';
                        return;
                    }

                    fetch(`https://nominatim.openstreetmap.org/search?q=${query}&format=json&addressdetails=1`)
                        .then(response => response.json())
                        .then(data => {
                            autocompleteList.innerHTML = '';
                            data.forEach(place => {
                                var item = document.createElement('div');
                                item.classList.add('autocomplete-item');
                                item.innerText = place.display_name;
                                item.onclick = function() {
                                    input.value = place.display_name;
                                    input.setAttribute('data-latitude', place.lat);
                                    input.setAttribute('data-longitude', place.lon);
                                    if (place.address) {
                                        var city = place.address.city || place.address.town || place.address.village || 'N/A';
                                        var state = place.address.state || 'N/A';
                                        var country = place.address.country || 'N/A';
                                        input.setAttribute('data-city', city);
                                        input.setAttribute('data-state', state);
                                        input.setAttribute('data-country', country);
                                    }
                                    autocompleteList.innerHTML = ''; // Clear the autocomplete list
                                };
                                autocompleteList.appendChild(item);
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                });
                document.addEventListener('click', function(e) {
                    if (e.target !== input) {
                        autocompleteList.innerHTML = '';
                    }
                });
            }else{
                var input = document.getElementById(id);
                var autocomplete = new google.maps.places.Autocomplete(input);
                autocomplete.addListener('place_changed', function () {
                    var place = autocomplete.getPlace();
                    var placeaddress = autocomplete.getPlace().address_components;
                    var city = place.address_components.filter(f => JSON.stringify(f.types) === JSON.stringify(['locality', 'political']))[0].long_name;
                    var state = place.address_components.filter(f => JSON.stringify(f.types) === JSON.stringify(['administrative_area_level_1', 'political']))[0].long_name;
                    var country = place.address_components.filter(f => JSON.stringify(f.types) === JSON.stringify(['country', 'political']))[0].long_name;
                    $("#" + id).val(place.formatted_address).attr('data-latitude', place.geometry.location.lat()).attr('data-longitude', place.geometry.location.lng()).attr('data-city', city).attr('data-state', state).attr('data-country', country)
                });
            }
    }

    $(document).on("click", "#address", function () {
        var id = $(this).attr('id');
        initialize(id);
    });


    function handleFileSelectowner(evt) {
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
                ownerphoto = filePayload;
                ownerFileName = filename;
                $(".uploaded_image_owner").html('<img id="uploaded_image_owner" src="' + ownerphoto + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"  width="150px" height="150px;">');
                $(".uploaded_image_owner").show();

            };
        })(f);
        reader.readAsDataURL(f);
    }
    async function storeImageData() {
        var newPhoto = ownerphoto;

        try {
            if (ownerphoto != '') {
                if (ownerOldImageFile != "" && ownerphoto != ownerOldImageFile) {
                    var ownerOldImageUrlRef = await storage.refFromURL(ownerOldImageFile);
                    imageBucket = ownerOldImageUrlRef.bucket;
                    var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";

                    if (imageBucket == envBucket) {
                        await ownerOldImageUrlRef.delete().then(() => {
                            console.log("Old file deleted!")
                        }).catch((error) => {
                            console.log("ERR File delete ===", error);
                        });
                    } else {
                        console.log('Bucket not matched');
                    }
                }

                if (ownerphoto != ownerOldImageFile) {

                    ownerphoto = ownerphoto.replace(/^data:image\/[a-z]+;base64,/, "")
                    var uploadTask = await storageRef.child(ownerFileName).putString(ownerphoto, 'base64', { contentType: 'image/jpg' });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto = downloadURL;
                    ownerphoto = downloadURL;
                }
            }
        } catch (error) {
            console.log("ERR ===", error);
        }

        return newPhoto;
    }


    function chkAlphabets(event, msg) {
        if (!(event.which >= 97 && event.which <= 122) && !(event.which >= 65 && event.which <= 90)) {
            document.getElementById(msg).innerHTML = "Accept only Alphabets";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

    function chkAlphabets2(event, msg) {
        if (!(event.which >= 48 && event.which <= 57)
        ) {
            document.getElementById(msg).innerHTML = "Accept only Number";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

    function chkAlphabets3(event, msg) {
        if (!((event.which >= 48 && event.which <= 57) || (event.which >= 97 && event.which <= 122))) {
            document.getElementById(msg).innerHTML = "Special characters not accepted ";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

</script>

@endsection
