@extends('layouts.app')
@section('content')
    <style>
        #autocomplete-list {
            border: 1px solid #d4d4d4;
            z-index: 99;
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
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    @if (!isset($_GET['id']))
                        <li class="breadcrumb-item"><a href="{!! route('ondemand.services.index') !!}">{{ trans('lang.service_plural') }}</a></li>
                    @else
                        <li class="breadcrumb-item"><a href="{!! route('ondemand.services.index', @$_GET['id']) !!}">{{ trans('lang.service_plural') }}</a></li>
                    @endif
                    <li class="breadcrumb-item active">{{ trans('lang.service_create') }}</li>
                </ol>
            </div>
        </div>
        <div>
            <div class="card-body">
                <div class="error_top" style="display:none"></div>
                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">
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
                            @if (!isset($_GET['id']))
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.provider') }}</label>
                                    <div class="col-7">
                                        <select id="provider_select" class="form-control">
                                            <option value="">{{ trans('lang.select_provider') }}</option>
                                        </select>
                                        <div class="form-text text-muted">
                                            {{ trans('lang.provider_help') }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="form-group row width-50">
                                <label class="col-3 control-label ">{{ trans('lang.select_section') }}</label>
                                <div class="col-7">
                                    <select name="section_id" class="form-control" id="section_id">
                                        <option value="">{{ trans('lang.select_section') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_category_id') }}</label>
                                <div class="col-7">
                                    <select id='item_category' class="form-control" required>
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
                                    <select id='sub_category' class="form-control" required>
                                        <option value="">{{ trans('lang.select_sub_category') }}</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        {{ trans('lang.select_sub_category') }}
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
                                    <select id='price_unit' name="price_unit" class="form-control" required>
                                        <option value="Hourly">{{ trans('lang.hourly') }}</option>
                                        <option value="Fixed">{{ trans('lang.fixed') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.item_image') }}</label>
                                <div class="col-7">
                                    <input type="file" id="service_image" required>
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
                                    <input type="time" class="form-control" id="start_Time" required>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.end_Time') }}</label>
                                <div class="col-7">
                                    <input type="time" class="form-control" id="end_Time" required>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary  save-form-btn"><i class="fa fa-save"></i>
                        {{ trans('lang.save') }}
                    </button>
                    @if (!isset($_GET['id']))
                        <a href="{!! route('ondemand.services.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                    @else
                        <a href="{!! route('ondemand.services.index', $_GET['id']) !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
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
        var authorName = '';
        var createdAt = firebase.firestore.FieldValue.serverTimestamp();
        var photos = [];
        var author = database.collection('users').orderBy('createdAt', 'desc');
        var categories = database.collection('provider_categories').where('publish', '==', true);
        var googleApiKey = '';
        var photos = [];
        var serviceImageFileName = [];
        var serviceImageCount = 0;
        var placeholderImage = '';
        var placeholder = database.collection('settings').doc('placeHolderImage');
        var provider_id = "{{ @$_GET['id'] }}";
        var providerName = '';
        var providerPic = '';
        var providerPhone = '';
        var productImagesCount = 0;
        var mapType = 'ONLINE';
        var itemLimit = '-1';
        var createdItem = 0;
        var subscriptionModel = false;
        var commissionModel = false;
        var subscription_plan = '';
        var subscriptionPlanId = '';
        var subscriptionExpiryDate = '';
        var subscriptionTotalOrders = '';
        var subscriptionTotalOrders = '';
        var isSectionIdExist = false;
        var section_id = getCookie('section_id');
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
        var subscriptionBusinessModel = database.collection('settings').doc("vendor");
        subscriptionBusinessModel.get().then(async function(snapshots) {
            var subscriptionSetting = snapshots.data();
            if (subscriptionSetting.subscription_model == true) {
                subscriptionModel = true;
            }
        });
        $(document).ready(function() {
            database.collection('users').where('role', '==', 'provider').where('section_id','==',section_id).get().then(async function(snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    $('#provider_select').append($("<option></option>")
                        .attr("value", data.id)
                        .text(data.firstName + ' ' + data.lastName)
                        .attr("data-authorName", data.firstName + ' ' + data.lastName)
                        .attr("data-authorpic", data.profilePictureURL)
                        .attr("data-authorphone", data.phoneNumber));
                })
            });
            database.collection('sections').where('serviceTypeFlag', '==', 'ondemand-service').orderBy('order').get().then(async function(snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    $('#section_id').append($("<option></option>")
                        .attr("value", data.id)
                        .attr("data-type", data.serviceTypeFlag)
                        .text(data.name + ' (' + data.serviceType + ')'));
                });
            });
            if (provider_id != '') {
                getProviderInfo(provider_id);
            }
            $('#provider_select').on('change', function() {
                var providerId = $(this).val();
                getProviderInfo(providerId);
            })
            $('#section_id').on('change', function() {
                var section_id = $(this).val();
                if (section_id) {
                    categories.where('parentCategoryId', '==', null).where('sectionId', '==', section_id).get().then(async function(snapshots) {
                        if (snapshots.docs.length > 0) {
                            $('#item_category').html('<option value="">{{ trans('lang.select_category') }}</option>');
                            snapshots.docs.forEach((listval) => {
                                var data = listval.data();
                                $('#item_category').append($("<option></option>")
                                    .attr("value", data.id)
                                    .text(data.title));
                            });
                        } else {
                            $('#item_category').html('<option value="">{{ trans('lang.select_category') }}</option>');
                        }
                    });
                } else {
                    $('#item_category').html('<option value="">{{ trans('lang.select_category') }}</option>');
                }
                $('#sub_category').html('<option value="">{{ trans('lang.select_sub_category') }}</option>');
            })
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
            $(".save-form-btn").click(async function() {
                var id = database.collection("tmp").doc().id;
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
                var selectedOption = $('#provider_select').find("option:selected");
                var providerUserId = $("#provider_select").val();
                if (parseInt(itemLimit) == -1 || parseInt(createdItem) < parseInt(itemLimit)) {
                    if (provider_id != '') {
                        var providerId = provider_id;
                        var authorName = providerName;
                        var authorProfilePic = providerPic;
                        var authorPhone = providerPhone;
                    } else {
                        var providerId = $("#provider_select").val();
                        var authorName = selectedOption.attr("data-authorname");
                        var authorProfilePic = selectedOption.attr("data-authorpic");
                        var authorPhone = selectedOption.attr("data-authorphone");
                    }
                    var section_id = $("#section_id").val();
                    $("input:checkbox[name=days]:checked").each(function() {
                        days.push($(this).val());
                    });
                    if (discount == '') {
                        discount = "0";
                    }
                    if (name == '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{ trans('lang.insert_service_name_error') }}</p>");
                        window.scrollTo(0, 0);
                    } else if (section_id == '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{ trans('lang.select_section_error') }}</p>");
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
                    } else if (providerId == '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{ trans('lang.select_service_provider_error') }}</p>");
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
                    } else if (photos == '') {
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{ trans('lang.image_required') }}</p>");
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
                            if (IMG.length == 0) {
                                $(".error_top").show();
                                $(".error_top").html("");
                                $(".error_top").append("<p>{{ trans('lang.image_required') }}</p>");
                                window.scrollTo(0, 0);
                            }

                            var objects = {
                                "address": address,
                                'author': providerId,
                                'authorName': authorName,
                                'authorProfilePic': authorProfilePic,
                                'categoryId': category,
                                'createdAt': createdAt,
                                'days': days,
                                'description': description,
                                'disPrice': discount,
                                'id': id,
                                'latitude': latitude,
                                'longitude': longitude,
                                'phoneNumber': authorPhone,
                                'photos': IMG,
                                'price': price,
                                'priceUnit': price_unit,
                                'publish': itemPublish,
                                'reviewsCount': 0,
                                'reviewsSum': 0,
                                'sectionId': section_id,
                                'startTime': startTime,
                                'endTime': endTime,
                                'subCategoryId': sub_category,
                                'title': name,
                                'coordinates': new firebase.firestore.GeoPoint(latitude, longitude),
                                'g' : {
                                    'geohash' : encodeGeohash(latitude, longitude),
                                    'geopoint' : new firebase.firestore.GeoPoint(latitude, longitude)
                                },
                                'subscription_plan': subscription_plan,
                                'subscriptionPlanId': subscriptionPlanId,
                                'subscriptionExpiryDate': subscriptionExpiryDate,
                                'subscriptionTotalOrders': subscriptionTotalOrders
                            };
                            database.collection('providers_services').doc(id).set(objects).then(async function(result) {
                                if (!isSectionIdExist) {
                                    var commissionObj = null;
                                    await database.collection('sections').doc(section_id).get().then(async function(snapshot) {
                                        commissionObj = snapshot.data().adminCommision;
                                        await database.collection('users').doc(providerId).update({
                                            'adminCommission': commissionObj,
                                            'section_id': section_id
                                        });
                                    });
                                }
                                if (provider_id == '') {
                                    window.location.href = '{{ route('ondemand.services.index') }}';
                                } else {
                                    window.location.href = '{{ route('ondemand.services.index', @$_GET['id']) }}';
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
                } else {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append(
                        "<p>{{ trans('lang.create_service_limit_exceed') }}</p>"
                    );
                    window.scrollTo(0, 0);
                }
            })
        })
        var storageRef = firebase.storage().ref('images');
        $("#service_image").resizeImg({
            callback: function(base64str) {
                var val = $('#service_image').val().toLowerCase();
                var ext = val.split('.')[1];
                var docName = val.split('fakepath')[1];
                var filename = $('#service_image').val().replace(/C:\\fakepath\\/i, '')
                var timestamp = Number(new Date());
                var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                serviceImageFileName.push(filename);
                serviceImageCount++;
                photos_html = '<span class="image-item" id="photo_' + serviceImageCount + '"><span class="remove-btn" data-id="' + serviceImageCount + '" data-img="' + base64str + '"><i class="fa fa-remove"></i></span><img class="rounded" width="50px" id="" height="auto" src="' + base64str + '"></span>'
                $(".service_image").append(photos_html);
                photos.push(base64str);
                $("#service_image").val('');
            }
        });      
        $(document).on("click", ".remove-btn", function() {
            var id = $(this).attr('data-id');
            var photo_remove = $(this).attr('data-img');
            $("#photo_" + id).remove();
            index = photos.indexOf(photo_remove);
            if (index > -1) {
                photos.splice(index, 1); // 2nd parameter means remove one item only
            }
        });
        async function getProviderInfo(provider_id) {
            await database.collection('users').where('id', '==', provider_id).get().then(async function(snapshot) {
                var provider_data = snapshot.docs[0].data();
                providerName = provider_data.firstName + ' ' + provider_data.lastName;
                providerPic = provider_data.profilePictureURL;
                providerPhone = provider_data.phoneNumber;
                if (provider_data.hasOwnProperty('section_id') && provider_data.section_id != null && provider_data.section_id != '') {
                    $('#section_id').val(provider_data.section_id).trigger('change');
                    $('#section_id').prop('disabled', true);
                    isSectionIdExist = true;
                    await database.collection('sections').doc(provider_data.section_id).get().then(
                        async function(snapshot) {
                            if (snapshot.data().adminCommision != null && snapshot.data()
                                .adminCommision != '') {
                                if (snapshot.data().adminCommision.enable) {
                                    commissionModel = true;
                                }
                            }
                        });
                }
                subscription_plan = provider_data.subscription_plan ? provider_data.subscription_plan : null;
                subscriptionPlanId = provider_data.subscriptionPlanId ? provider_data.subscriptionPlanId : null;
                subscriptionExpiryDate = provider_data.subscriptionExpiryDate ? provider_data.subscriptionExpiryDate : null;
                subscriptionTotalOrders = provider_data.subscriptionTotalOrders ? provider_data.subscriptionTotalOrders : null;
                if (subscriptionModel || commissionModel) {
                    if (provider_data.hasOwnProperty('subscription_plan') && provider_data.subscription_plan != null && provider_data.subscription_plan != '') {
                        itemLimit = provider_data.subscription_plan.itemLimit;
                    }
                }
            });
            await database.collection('providers_services').where('author', '==', provider_id).get().then((querySnapshot) => {
                createdItem = querySnapshot.size;
            }).catch((error) => {
                console.error("Error fetching documents: ", error);
            });
        }
        async function storeImageData() {
            var newPhoto = [];
            if (photos.length > 0) {
                await Promise.all(photos.map(async (servicePhoto, index) => {
                    servicePhoto = servicePhoto.replace(/^data:image\/[a-z]+;base64,/, "");
                    var uploadTask = await storageRef.child(serviceImageFileName[index]).putString(servicePhoto, 'base64', {
                        contentType: 'image/jpg'
                    });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto.push(downloadURL);
                }));
            }
            return newPhoto;
        }
    </script>
@endsection
