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
                <h3 class="text-themecolor">{{ trans('lang.service_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{!! route('dashboard') !!}">{{ trans('lang.dashboard') }}</a></li>
                    @if (!isset($_GET['id']))
                        <li class="breadcrumb-item"><a href="{!! route('ondemand.services.index') !!}">{{ trans('lang.service_plural') }}</a>
                        </li>
                    @else
                        <li class="breadcrumb-item"><a href="{!! route('ondemand.services.index', @$_GET['id']) !!}">{{ trans('lang.service_plural') }}</a>
                        </li>
                    @endif
                    <li class="breadcrumb-item active">{{ trans('lang.service_edit') }}</li>
                </ol>
            </div>
        </div>
        <div>
            <div class="card-body">
                <div class="error_top" style="display:none"></div>
                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner service_detail_div">
                        <fieldset>
                            <legend>{{ trans('lang.service_information') }}</legend>
                            <div class="form-group row width-50">
                                <input type="hidden" class="form-control author_name">
                                <input type="hidden" class="form-control author_profile">
                                <label class="col-3 control-label">{{ trans('lang.service_name') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control service_name" required>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.service_name_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_category_id') }}</label>
                                <div class="col-7">
                                    <select id='item_category' name="item_category" class="form-control item_category" required>
                                        <option value="">{{ trans('lang.select_category') }}</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.item_category_id_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.sub_category_id') }}</label>
                                <div class="col-7">
                                    <select id='sub_category' name="sub_category" class="form-control sub_category" required>
                                        <option value="">{{ trans('lang.select_sub_category') }}</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.sub_category_id_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.price') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control price" required>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.item_price_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_discount') }}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control item_discount">
                                    <div class="form-text text-muted">
                                        {{ trans('lang.item_discount_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.price_unit') }}</label>
                                <div class="col-7">
                                    <select id='price_unit' name="price_unit" class="form-control price_unit" required>
                                        <option value="Hourly">{{ trans('lang.hourly') }}</option>
                                        <option value="Fixed">{{ trans('lang.fixed') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_image') }}</label>
                                <div class="col-7">
                                    <input type="file" id="service_image">
                                    <div class="placeholder_img_thumb service_image"></div>
                                    <div id="uploding_image"></div>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.item_image_help') }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <div class="form-check">
                                    <input type="checkbox" class="item_publish" id="item_publish">
                                    <label class="col-3 control-label" for="item_publish">{{ trans('lang.item_publish') }}</label>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.item_description') }}</label>
                                <div class="col-7">
                                    <textarea rows="8" class="form-control item_description" id="item_description"></textarea>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.address') }}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control address" id="address" autocomplete="on">
                                    <div id="autocomplete-list"></div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.Days') }}</label>
                                <div class="col-7">
                                    <input type="checkbox" class="days" name="days" id="monday" value="Monday">
                                    <label class="col-3 control-label" for="monday">{{ trans('lang.monday') }}</label>
                                    <input type="checkbox" class="days" name="days" id="tuesday" value="Tuesday">
                                    <label class="col-3 control-label" for="tuesday">{{ trans('lang.tuesday') }}</label>
                                    <input type="checkbox" class="days" name="days" id="wednesday" value="Wednesday">
                                    <label class="col-3 control-label" for="wednesday">{{ trans('lang.wednesday') }}</label>
                                    <input type="checkbox" class="days" name="days" id="thursday" value="Thursday">
                                    <label class="col-3 control-label" for="thursday">{{ trans('lang.thursday') }}</label>
                                    <input type="checkbox" class="days" name="days" id="friday" value="Friday">
                                    <label class="col-3 control-label" for="friday">{{ trans('lang.friday') }}</label>
                                    <input type="checkbox" class="days" name="days" id="saturday" value="Saturday">
                                    <label class="col-3 control-label" for="saturday">{{ trans('lang.saturday') }}</label>
                                    <input type="checkbox" class="days" name="days" id="sunday" value="Sunday">
                                    <label class="col-3 control-label" for="sunday">{{ trans('lang.sunday') }}</label>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.start_Time') }}</label>
                                <div class="col-7">
                                    <input type="time" class="form-control start_Time" id="start_Time" required>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.end_Time') }}</label>
                                <div class="col-7">
                                    <input type="time" class="form-control end_Time" id="end_Time" required>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary  edit-form-btn"><i class="fa fa-save"></i>
                        {{ trans('lang.save') }}
                    </button>
                    @if (!isset($_GET['id']))
                        <a href="{!! route('ondemand.services.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                    @else
                        <a href="{!! route('ondemand.services.index', @$_GET['id']) !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
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
        var days = [];
        var Id = "<?php echo $id; ?>";
        var authorName = '';
        var photos = [];
        var new_added_photos = [];
        var new_added_photos_filename = [];
        var photosToDelete = [];
        var providers_services = database.collection('providers_services').doc(Id);
        var categories = database.collection('provider_categories').where('publish', '==', true);
        var googleApiKey = '';
        var serviceImagesCount = 0;
        var placeholderImage = '';
        var placeholder = database.collection('settings').doc('placeHolderImage');
        var allowed_file_size = '';
        var idOfProviderDetailPage = "{{ @$_GET['id'] }}";
        var new_added_photos = [];
        var new_added_photos_filename = [];
        var photosToDelete = [];
        var product_image_filename = [];
        var productImagesCount = 0;
        var mapType = 'ONLINE';
        database.collection('settings').doc('DriverNearBy').get().then(async function(snapshots) {
            var data = snapshots.data();
            if (data && data.selectedMapType && data.selectedMapType == "osm") {
                mapType = "OFFLINE"
            }
        });
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })
        $(document).ready(function() {
            if (idOfProviderDetailPage != '') {
                $('.provider-div').css('display', 'none');
            }
            providers_services.get().then(async function(snapshots) {
                var serviceData = snapshots.data();
                if (serviceData != undefined) {
                    $(".service_name").val(serviceData.title)
                    $(".price").val(serviceData.price)
                    $(".item_discount").val(serviceData.disPrice)
                    $("#item_description").val(serviceData.description);
                    $("#address").val(serviceData.address);
                    $('#address').val(serviceData.address).attr('data-latitude', serviceData.latitude).attr('data-longitude', serviceData.longitude);
                    $("#start_Time").val(serviceData.startTime);
                    $("#end_Time").val(serviceData.endTime);
                    $("input:checkbox[name=days]").each(function(index) {
                        var val = $(this).val();
                        if (serviceData.days.includes(val)) {
                            $(this).prop('checked', true);
                        }
                    });
                    if (serviceData.hasOwnProperty('priceUnit')) {
                        $('#price_unit').val(serviceData.priceUnit).trigger('change');
                    }
                    if (serviceData.publish) {
                        $("#item_publish").prop('checked', true);
                    }
                    if (serviceData.photos.length > 0) {
                        photos = serviceData.photos;
                    }
                    if (photos.length > 0) {
                        photos.forEach((element, index) => {
                            $(".service_image").append('<span class="image-item" id="photo_' + index + '"><span class="remove-btn" data-id="' + index + '" data-img="' + photos[index] + '" data-status="old"><i class="fa fa-remove"></i></span><img class="rounded" width="50px" id="" height="auto" src="' + photos[index] + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"></span>');
                        })
                    } else {
                        $(".service_image").append('<span class="image-item" id="photo_1"><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">');
                    }
                    await categories.where('parentCategoryId', '==', null).where('sectionId', '==', serviceData.sectionId).get().then(async function(snapshots) {
                        snapshots.docs.forEach((listval) => {
                            var data = listval.data();
                            $('#item_category').append($("<option></option>")
                                .attr("value", data.id)
                                .text(data.title));
                        });
                    });
                    $('#item_category').val(serviceData.categoryId);
                    await categories.where('parentCategoryId', '==', serviceData.categoryId).get().then(async function(snapshots) {
                        if (snapshots.docs.length > 0) {
                            $('#sub_category').html('<option value="">{{ trans('lang.select_sub_category') }}</option>');
                            snapshots.docs.forEach((listval) => {
                                var data = listval.data();
                                $('#sub_category').append($("<option></option>")
                                    .attr("value", data.id)
                                    .text(data.title));
                            });
                        }
                    });
                    $('#sub_category').val(serviceData.subCategoryId)
                } else {
                    $('.edit-form-btn').hide();
                    $('.service_detail_div').html('<h5 class="text-danger text-center font-weight-bold">{{ trans('lang.service_unknown_deleted') }}</h5>')
                }
            });
            $('#item_category').on('change', function() {
                var categoryId = $(this).val();
                if (categoryId) {
                    categories.where('parentCategoryId', '==', categoryId).get().then(async function(snapshots) {
                        if (snapshots.docs.length > 0) {
                            $('#sub_category').html('<option value="">{{ trans('lang.select_sub_category') }}</option>');
                            snapshots.docs.forEach((listval) => {
                                var data = listval.data();
                                $('#sub_category').append($("<option></option>")
                                    .attr("value", data.id)
                                    .text(data.title));
                            });
                        } else {
                            $('#sub_category').html('<option value="">{{ trans('lang.select_sub_category') }}</option>');
                        }
                    });
                } else {
                    $('#sub_category').html('<option value="">{{ trans('lang.select_sub_category') }}</option>');
                }
            })
            function initialize(id) {
                if (mapType == "OFFLINE") {
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
                } else {
                    var input = document.getElementById(id);
                    var autocomplete = new google.maps.places.Autocomplete(input);
                    autocomplete.addListener('place_changed', function() {
                        var place = autocomplete.getPlace();
                        var placeaddress = autocomplete.getPlace().address_components;
                        var city = place.address_components.filter(f => JSON.stringify(f.types) === JSON.stringify(['locality', 'political']))[0].long_name;
                        var state = place.address_components.filter(f => JSON.stringify(f.types) === JSON.stringify(['administrative_area_level_1', 'political']))[0].long_name;
                        var country = place.address_components.filter(f => JSON.stringify(f.types) === JSON.stringify(['country', 'political']))[0].long_name;
                        $("#" + id).val(place.formatted_address).attr('data-latitude', place.geometry.location.lat()).attr('data-longitude', place.geometry.location.lng()).attr('data-city', city).attr('data-state', state).attr('data-country', country)
                    });
                }
            }
            $(document).on("click", "#address", function() {
                var id = $(this).attr('id');
                initialize(id);
            });
            $(".edit-form-btn").click(async function() {
                var days = [];
                var name = $(".service_name").val();
                var price = $(".price").val();
                var discount = $(".item_discount").val();
                var category = $("#item_category option:selected").val();
                var sub_category = $("#sub_category option:selected").val();
                var description = $("#item_description").val();
                var itemPublish = $(".item_publish").is(":checked");
                var price_unit = $("#price_unit option:selected").val();
                var address = $("#address").val();
                var startTime = $("#start_Time").val();
                var endTime = $("#end_Time").val();
                var longitude = parseFloat($('#address').attr('data-longitude'));
                var latitude = parseFloat($('#address').attr('data-latitude'));
                $("input:checkbox[name=days]:checked").each(function() {
                    days.push($(this).val());
                });
                if (discount == '') {
                    discount = "0";
                }
                if (photos != '') {
                    photo = photos[0]
                }
                if (name == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.insert_service_name_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (category == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.select_service_category_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (sub_category == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.select_service_sub_category_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (price == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.insert_service_price_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (parseInt(price) < parseInt(discount)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.price_should_not_less_then_discount_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (description == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.insert_service_description_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (isNaN(latitude) || isNaN(longitude)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.service_select_address_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (days.length == 0) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.service_select_days_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (startTime == '' || endTime == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.service_select_time_error') }}</p>");
                    window.scrollTo(0, 0);
                } else if (startTime > endTime) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.start_time_grater_than_endtime_error') }}</p>");
                    window.scrollTo(0, 0);
                } else {
                    await storeImageData().then(async (IMG) => {
                        geoFirestore.collection('providers_services').doc(Id).update({
                            "address": address,
                            'categoryId': category,
                            'days': days,
                            'description': description,
                            'disPrice': discount,
                            'latitude': latitude,
                            'longitude': longitude,
                            'photos': IMG,
                            'price': price,
                            'priceUnit': price_unit,
                            'publish': itemPublish,
                            'startTime': startTime,
                            'endTime': endTime,
                            'subCategoryId': sub_category,
                            'title': name,
                            'coordinates': new firebase.firestore.GeoPoint(latitude, longitude),
                            'g' : {
                                'geohash' : encodeGeohash(latitude, longitude),
                                'geopoint' : new firebase.firestore.GeoPoint(latitude, longitude)
                            }
                        }).then(function(result) {
                            if (idOfProviderDetailPage != '') {
                                window.location.href = '{{ route('ondemand.services.index', @$_GET['id']) }}';
                            } else {
                                window.location.href = '{{ route('ondemand.services.index') }}';
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
        });
        var storageRef = firebase.storage().ref('images');
        $("#service_image").resizeImg({
            callback: function(base64str) {
                var val = $('#service_image').val().toLowerCase();
                var ext = val.split('.')[1];
                var docName = val.split('fakepath')[1];
                var filename = $('#service_image').val().replace(/C:\\fakepath\\/i, '')
                var timestamp = Number(new Date());
                var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                serviceImagesCount++;
                photos_html = '<span class="image-item" id="photo_' + serviceImagesCount + '"><span class="remove-btn" data-id="' + serviceImagesCount + '" data-img="' + base64str + '" data-status="new"><i class="fa fa-remove"></i></span><img class="rounded" width="50px" id="" height="auto" src="' + base64str + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"></span>'
                $(".service_image").append(photos_html);
                new_added_photos.push(base64str);
                new_added_photos_filename.push(filename);
                $("#service_image").val('');
            }
        });
        $(document).on("click", ".remove-btn", function() {
            var id = $(this).attr('data-id');
            var photo_remove = $(this).attr('data-img');
            var status = $(this).attr('data-status');
            if (status == "old") {
                photosToDelete.push(firebase.storage().refFromURL(photo_remove));
            }
            $("#photo_" + id).remove();
            index = photos.indexOf(photo_remove);
            if (index > -1) {
                photos.splice(index, 1); // 2nd parameter means remove one item only
            }
            index = new_added_photos.indexOf(photo_remove);
            if (index > -1) {
                new_added_photos.splice(index, 1); // 2nd parameter means remove one item only
                new_added_photos_filename.splice(index, 1);
            }
        });
        async function storeImageData() {
            var newPhoto = [];
            if (photos.length > 0) {
                newPhoto = photos;
            }
            if (new_added_photos.length > 0) {
                await Promise.all(new_added_photos.map(async (foodPhoto, index) => {
                    foodPhoto = foodPhoto.replace(/^data:image\/[a-z]+;base64,/, "");
                    var uploadTask = await storageRef.child(new_added_photos_filename[index]).putString(foodPhoto, 'base64', {
                        contentType: 'image/jpg'
                    });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto.push(downloadURL);
                }));
            }
            if (photosToDelete.length > 0) {
                await Promise.all(photosToDelete.map(async (delImage) => {
                    imageBucket = delImage.bucket;
                    var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                    if (imageBucket == envBucket) {
                        await delImage.delete().then(() => {
                            console.log("Old file deleted!")
                        }).catch((error) => {
                            console.log("ERR File delete ===", error);
                        });
                    } else {
                        console.log('Bucket not matched');
                    }
                }));
            }
            return newPhoto;
        }
    </script>
@endsection
