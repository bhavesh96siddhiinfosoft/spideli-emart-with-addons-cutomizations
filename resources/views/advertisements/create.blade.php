@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.advertisement_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('advertisements') !!}">{{ trans('lang.advertisement_plural') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ trans('lang.advertisement_create') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class=" create-left col-lg-6 col-md-12 col-sm-12">
                    <div class="cat-edite-page max-width-box">
                        <div class="card  pb-4">
                            <div class="card-body">
                                <div class="error_top" style="display:none"></div>
                                <div class="row vendor_payout_create max-w-100">
                                    <div class="vendor_payout_create-inner">
                                        <div class="tab-pane active" id="advertisement_info">
                                            <fieldset>
                                                <legend>{{ trans('lang.advertisement_create') }}</legend>
                                                <div class="form-group row width-100">
                                                    <label class="col-3 control-label">{{ trans('lang.adv_title') }}</label>
                                                    <div class="col-7">
                                                        <input type="text" class="form-control title">
                                                        <div class="form-text text-muted">{{ trans('lang.adv_title_help') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row width-100">
                                                    <label
                                                        class="col-3 control-label ">{{ trans('lang.short_description') }}</label>
                                                    <div class="col-7">
                                                        <textarea rows="3" class="description form-control" id="description"></textarea>
                                                        <div class="form-text text-muted">
                                                            {{ trans('lang.short_description_help') }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php 
              if ($id == '') { ?>
                                                <div class="form-group row width-100">
                                                    <label class="col-3 control-label">{{ trans('lang.store') }}</label>
                                                    <div class="col-7">
                                                        <div class="sis-selected-single">
                                                            <select class="form-control restaurant" id="restaurant">
                                                                <option value="" disabled selected>
                                                                    {{ trans('lang.select_vendor') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php }?>
                                                <div class="form-group row width-100">
                                                    <label class="col-3 control-label">{{ trans('lang.priority') }}</label>
                                                    <div class="col-7">
                                                        <select class="form-control priority" id="priority">
                                                            <option value="" disabled selected>
                                                                {{ trans('lang.select_priority') }}</option>
                                                            <option value="N/A">N/A</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row width-100">
                                                    <label
                                                        class="col-3 control-label">{{ trans('lang.advertisement_type') }}</label>
                                                    <div class="col-7">
                                                        <select class="form-control advertisement_type"
                                                            id="advertisement_type">
                                                            <option value="" disabled selected>
                                                                {{ trans('lang.select_advertisement_type') }}</option>
                                                            <option value="restaurant_promotion">
                                                                {{ trans('lang.restaurant_promotion') }}</option>
                                                            <option value="video_promotion">
                                                                {{ trans('lang.video_promotion') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row width-100">
                                                    <label class="col-3 control-label">{{ trans('lang.validity') }}</label>
                                                    <div class="col-7">
                                                        <div id="daterange" class="form-control"><i
                                                                class="fa fa-calendar"></i>&nbsp;
                                                            <span></span>&nbsp; <i class="fa fa-caret-down"></i>
                                                        </div>
                                                        <div class="form-text text-muted">{{ trans('lang.validity_help') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row width-100 review-rating-input">
                                                    <label
                                                        class="col-3 control-label">{{ trans('lang.show_review_rating') }}</label>
                                                    <div class="form-check">
                                                        <input type="checkbox" id="review" name="review" value="review"
                                                            checked>
                                                        <label class="control-label"
                                                            for="review">{{ trans('lang.review') }}</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="checkbox" id="rating" name="rating" value="rating"
                                                            checked>
                                                        <label class="control-label"
                                                            for="rating">{{ trans('lang.rating') }}</label>
                                                    </div>
                                                </div>
                                                <div class="form-group row width-100 d-none image-div">
                                                    <label
                                                        class="col-3 control-label">{{ trans('lang.profile_image') }}</label>
                                                    <div class="col-7">
                                                        <input type="file" id="profile_image"
                                                            onchange="handleProfileSelect(event)">
                                                        <div class="placeholder_img_thumb profile_image"></div>
                                                        <div id="uploding_image"></div>
                                                        <div class="form-text text-muted w-50">
                                                            {{ trans('lang.profile_image_help') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row width-100 d-none image-div">
                                                    <label
                                                        class="col-3 control-label">{{ trans('lang.cover_image') }}</label>
                                                    <div class="col-7">
                                                        <input type="file" id="cover_image"
                                                            onchange="handleCoverSelect(event)">
                                                        <div class="placeholder_img_thumb cover_image"></div>
                                                        <div id="uploding_image"></div>
                                                        <div class="form-text text-muted w-50">
                                                            {{ trans('lang.cover_image_help') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row width-100 d-none video-div">
                                                    <label class="col-3 control-label">{{ trans('lang.video') }}</label>
                                                    <div class="col-7">
                                                        <input type="file" id="video_file"
                                                            onchange="handleVideoSelect(event)">
                                                        <div class="placeholder_img_thumb video_file"></div>
                                                        <div id="uploding_image"></div>
                                                        <div class="form-text text-muted w-50">{!! nl2br(trans('lang.video_info')) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-12 text-center btm-btn">
                                <button type="button" class="btn btn-primary save-setting-btn"><i
                                        class="fa fa-save"></i>
                                    {{ trans('lang.save') }}
                                </button>
                                <?php if ($id != '') { ?>
                                <a href="{!! route('advertisements', $id) !!}" class="btn btn-default"><i
                                        class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                                <?php } else { ?>
                                <a href="{!! route('advertisements') !!}" class="btn btn-default"><i
                                        class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class=" create-right col-lg-6 col-md-12 col-sm-12">
                    <div class="bg-light p-3 p-sm-4 rounded">
                        <label class="form-label">{{ trans('lang.advertisement_preview') }}</label>
                        <div id="profile-preview-box" class="profile-preview-box pt-4">
                            <div class="bg--secondary rounded">

                                <div class="main-image rounded min-h-200" id="preview-cover"
                                    style="background: url('') center center / cover no-repeat">
                                </div>
                                <!--<div class="video h-200">
                                                                                                                                                                                        <video controls="" src="" >
                                                                                                                                                                                        </video>
                                                                                                                                                                                    </div>-->
                                <div class="rounded bg-white px-3 py-4 position-relative mt-n2">
                                    <div class="preview-title preview-description">
                                        <div class="wishlist-btn bg--secondary placeholder-text preview-wishlist"></div>
                                        <div class="static-text wishlist-btn-2 preview-wishlist" style="display: block;">
                                            <div class="h-100 w-100 d-flex align-items-center justify-content-center">
                                                <i class="fa fa-heart-o"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <!-- Existing Profile Image -->
                                        <div class="profile-prev-image bg--secondary me-xl-3" id="preview-profile"
                                            style="background: url('') center center / cover no-repeat">
                                        </div>
                                        <div class="review-rating-demo">
                                            <div class="rating-text static-text" style="display: block;"
                                                id="preview-rating">
                                                <div class="rating-number d-flex align-items-center">
                                                    <i class="fa fa-star"></i><span id="rating_data"></span>
                                                </div>
                                            </div>
                                            <span class="review--text static-text" style="display: inline;"
                                                id="preview-review"></span>
                                        </div>
                                        <div class="w-0 d-flex flex-column gap-2 flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <div class="preview-title w-100">
                                                    <h5 class="main-text pe-4" id="preview-title">{{trans('lang.title')}}</h5>
                                                    <div class="placeholder-text bg--secondary p-2 w-50"
                                                        id="placeholder-title"></div>
                                                </div>
                                            </div>
                                            <div class="preview-description w-100">
                                                <div class="main-text line-limit-2" id="preview-desc">{{trans('lang.description')}}
                                                </div>
                                                <div class="placeholder-text bg--secondary p-2 w-75"
                                                    id="placeholder-desc"></div>
                                            </div>
                                        </div>
                                        <a class="btn btn-primary py-1 px-3 cursor-auto text-white d-none"
                                            id="preview-arrow">
                                            <span class="fa fa-arrow-right"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var database = firebase.firestore();
        var ref = database.collection('advertisements');
        var profilePhoto = "";
        var profileFileName = '';
        var coverPhoto = "";
        var coverFileName = '';
        var videoData = '';
        var videoFileName = '';
        var oldCover = '';
        var oldProfile = '';
        var oldVideo = '';
        var refVendor = database.collection('vendors');
        var storageRef = firebase.storage().ref('images');
        var storageVideoRef = firebase.storage().ref('videos');
        var section_id = getCookie('section_id') || '';
        var restaurant = '';

        $('#restaurant').select2({
            placeholder: "{{ trans('lang.select_vendor') }}",
            allowClear: true,

        });

        refVendor.where('section_id', '==', section_id).orderBy('title', 'asc').get().then(async function(snapshots) {
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                if (data.title != '') {
                    $('#restaurant').append($("<option></option>")
                        .attr("value", data.id)
                        .text(data.title));
                }
            })
        });

        function setDate(startDate = null, endDate = null) {
            if (!startDate || !endDate) {
                $('#daterange span').html('{{ trans('lang.select_range') }}');
            } else {
                $('#daterange span').html(moment(startDate).format('MMMM D, YYYY') + ' - ' + moment(endDate).format(
                    'MMMM D, YYYY'));
            }
            $('#daterange').daterangepicker({
                autoUpdateInput: false,
                minDate: moment(),
                startDate: startDate ? startDate : moment(), // Set default if null
                endDate: endDate ? endDate : moment(),
            }, function(start, end) {
                $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

            });
            $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                $('#daterange span').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format(
                    'MMMM D, YYYY'));
            });
            $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                $('#daterange span').html('{{ trans('lang.select_range') }}');
            });
        }

        setDate();

        const copiedAd = localStorage.getItem('copiedAdvertisement');
        if (copiedAd) {
            setTimeout(function() {
                const data = JSON.parse(copiedAd);
                $(".title").val(data.title);
                var title = data.title;
                (title != '') ? $('#placeholder-title').addClass('d-none'): $('#placeholder-title').removeClass(
                    'd-none');
                (title == '') ? $('#preview-title').html('Title'): $('#preview-title').html(title);
                $(".description").val(data.description);
                var description = data.description;
                (description != '') ? $('#placeholder-desc').addClass('d-none'): $('#placeholder-desc').removeClass(
                    'd-none');
                (description == '') ? $('#preview-desc').html("Description"): $('#preview-desc').html(description);
                $("#restaurant").val(data.vendorId);
                getVendorData(data.vendorId);
                $('#priority').val(data.priority);
                $('#advertisement_type').val(data.type);
                if (data.type == 'restaurant_promotion') {
                    $('.image-div').removeClass('d-none');
                    $('.video-div').addClass('d-none');
                    $('.review-rating-input').removeClass('d-none');
                    $('.review-rating-demo').removeClass('d-none');
                    $('#preview-profile').removeClass('d-none');
                    $('.preview-wishlist').removeClass('d-none');
                    $('#preview-arrow').addClass('d-none');
                } else {

                    $('.image-div').addClass('d-none');
                    $('.video-div').removeClass('d-none');
                    $('.review-rating-input').addClass('d-none');
                    $('.review-rating-demo').addClass('d-none');
                    $('#preview-profile').addClass('d-none');
                    $('.preview-wishlist').addClass('d-none');
                    $('#preview-arrow').removeClass('d-none');

                }
                (data.showRating) ? $("#rating").prop("checked", true): '';
                (data.showReview) ? $("#review").prop("checked", true): '';
                if (data.showRating) {
                    $('#preview-rating').removeClass('d-none');
                    $('.review-rating-demo').removeClass('d-none');
                } else {
                    $('#preview-rating').addClass('d-none');
                }
                if (data.showReview) {
                    $('#preview-review').removeClass('d-none');
                    $('.review-rating-demo').removeClass('d-none');
                } else {
                    $('#preview-review').addClass('d-none');
                }
                if (!data.showRating && !data.showReview) {
                    $('.review-rating-demo').addClass('d-none');
                }
                startDate = new Date(data.startDate.seconds * 1000);
                endDate = new Date(data.endDate.seconds * 1000);
                setDate(startDate, endDate);
                if (data.coverImage != '' && data.coverImage != null) {
                    coverPhoto = data.coverImage;
                    oldCover = data.coverImage;
                    $(".cover_image").append('<img class="rounded" style="width:50px" src="' + coverPhoto +
                        '" alt="image">');
                    $("#preview-cover").css("background-image", "url('" + coverPhoto + "')");

                }
                if (data.profileImage != '' && data.profileImage != null) {
                    profilePhoto = data.profileImage;
                    oldProfile = data.profileImage;
                    $(".profile_image").append('<img class="rounded" style="width:50px" src="' + profilePhoto +
                        '" alt="image">');
                    $("#preview-profile").css("background-image", "url('" + profilePhoto + "')");

                }

                if (data.video != '' && data.video != null) {
                    videoData = data.video;
                    oldVideo = data.video;
                    html = '<div class="col-md-3">\n' +
                        '<div class="video-inner"><video width="320px" height="240px"\n' +
                        '                                   controls="controls">\n' +
                        '                            <source src="' + videoData + '"\n' +
                        '            type="video/mp4"></video></div></div>';

                    jQuery(".video_file").append(html);
                    $("#preview-cover").html('<video width="100%" height="250px" controls="controls" src="' +
                        videoData + '" type="video/mp4"></video>');

                }

            }, 3000);

        }

        $('#advertisement_type').on('change', function() {
            var advType = $(this).val();
            $('#preview-cover').html('').css('background-image', 'none');
            if (advType == 'restaurant_promotion') {
                $('.image-div').removeClass('d-none');
                $('.video-div').addClass('d-none');
                $('.review-rating-input').removeClass('d-none');
                $('.review-rating-demo').removeClass('d-none');
                $('#preview-profile').removeClass('d-none');
                $('.preview-wishlist').removeClass('d-none');
                $('#preview-arrow').addClass('d-none');
            } else {
                $('.image-div').addClass('d-none');
                $('.video-div').removeClass('d-none');
                $('.review-rating-input').addClass('d-none');
                $('.review-rating-demo').addClass('d-none');
                $('#preview-profile').addClass('d-none');
                $('.preview-wishlist').addClass('d-none');
                $('#preview-arrow').removeClass('d-none');
            }

        })


        $(".title").keyup(function() {
            var title = $('.title').val();
            (title != '') ? $('#placeholder-title').addClass('d-none'): $('#placeholder-title').removeClass(
                'd-none');
            (title == '') ? $('#preview-title').html('Title'): $('#preview-title').html(title);

        })
        $(".description").keyup(function() {
            var description = $('.description').val();
            (description != '') ? $('#placeholder-desc').addClass('d-none'): $('#placeholder-desc').removeClass(
                'd-none');
            (description == '') ? $('#preview-desc').html("Description"): $('#preview-desc').html(description);
        });
        $("#rating").on("change", function() {
            if ($(this).is(":checked")) {
                $('#preview-rating').removeClass('d-none');
                $('.review-rating-demo').removeClass('d-none');
            } else {
                $('#preview-rating').addClass('d-none');
                $('#review').is(":checked") ? '' : $('.review-rating-demo').addClass('d-none');
            }
        });
        $("#review").on("change", function() {
            if ($(this).is(":checked")) {
                $('#preview-review').removeClass('d-none');
                $('.review-rating-demo').removeClass('d-none');
            } else {
                $('#preview-review').addClass('d-none');
                $('#rating').is(":checked") ? '' : $('.review-rating-demo').addClass('d-none');
            }
        });

        var resturant = "<?php echo $id; ?>";
        var vendorID = '';
        if (resturant == '') {
            $('#restaurant').on("change", function() {
                vendorID = $(this).val() || '';
       
            })
        } else {
            vendorID = "<?php echo $id; ?>";

        }
        $(".save-setting-btn").click(async function() {
            var title = $(".title").val();
            var description = $(".description").val();
            // var restaurant = $("#restaurant").val();
            var priority = $('#priority').val();
            var advType = $('#advertisement_type').val();

            var rating = $("#rating").is(":checked") ? true : false;
            var review = $("#review").is(":checked") ? true : false;
            if (advType == 'video_promotion') {
                rating = false;
                review = false;
            }
            var daterangepicker = $('#daterange').data('daterangepicker');
            var startDate = '';
            var endDate = '';
            if (daterangepicker) {
                var from = moment(daterangepicker.startDate).toDate();
                var to = moment(daterangepicker.endDate).toDate();
                if (from && to) {
                    startDate = firebase.firestore.Timestamp.fromDate(new Date(from));
                    endDate = firebase.firestore.Timestamp.fromDate(new Date(to));
                }
            }

            if (title == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{ trans('lang.adv_title_help') }}</p>");
                window.scrollTo(0, 0);
            } else if (description == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{ trans('lang.short_description_help') }}</p>");
                window.scrollTo(0, 0);
            } else if (priority == '' || priority == null) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{ trans('lang.select_priority') }}</p>");
                window.scrollTo(0, 0);
            } else if (advType == '' || advType == null) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{ trans('lang.select_advertisement_type') }}</p>");
                window.scrollTo(0, 0);
            } else if ($('#daterange span').html() == '{{ trans('lang.select_range') }}' || startDate == '' ||
                endDate == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{ trans('lang.validity_help') }}</p>");
                window.scrollTo(0, 0);
            } else if (advType == 'restaurant_promotion' && profilePhoto == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{ trans('lang.profile_image_help') }}</p>");
                window.scrollTo(0, 0);
            } else if (advType == 'restaurant_promotion' && coverPhoto == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{ trans('lang.cover_image_help') }}</p>");
                window.scrollTo(0, 0);
            } else if (advType == 'video_promotion' && videoData == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{ trans('lang.video_help') }}</p>");
                window.scrollTo(0, 0);
            } else {
                var id = database.collection("tmp").doc().id;
                jQuery("#data-table_processing").show();
                if (copiedAd) {
                    const promises = [];
                    if (oldCover != coverPhoto) {
                        promises.push(storeCoverImage().then(img => coverIMG = img));
                    } else {
                        coverIMG = coverPhoto;
                    }
                    if (oldProfile != profilePhoto) {
                        promises.push(storeProfileImage().then(img => profileIMG = img));
                    } else {
                        profileIMG = profilePhoto;
                    }
                    if (oldVideo != videoData) {
                        promises.push(storeVideo().then(img => videoFile = img));
                    } else {
                        videoFile = videoData;
                    }
                    Promise.all(promises).then(() => {
                        database.collection('advertisements').doc(id).set({
                            'id': id,
                            'title': title,
                            'description': description,
                            'coverImage': coverIMG,
                            'profileImage': profileIMG,
                            'video': videoFile,
                            'vendorId': vendorID,
                            'priority': priority,
                            'type': advType,
                            'showRating': rating,
                            'showReview': review,
                            'startDate': startDate,
                            'endDate': endDate,
                            'status': 'approved',
                            'createdAt': firebase.firestore.FieldValue.serverTimestamp(),
                            'paymentStatus': true,
                            'isPaused': null,

                        }).then(function(result) {
                            jQuery("#data-table_processing").hide();
                            localStorage.removeItem('copiedAdvertisement');
                            if (resturant) {
                                window.location.href =
                                    '{{ route('advertisements') . '/' . $id }}';
                            } else {
                                window.location.href = '{{ route('advertisements') }}';
                            }

                        });
                    }).catch(function(error) {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + error + "</p>");
                    })

                } else {
                    storeProfileImage().then(profileIMG => {
                        storeCoverImage().then(coverIMG => {
                            storeVideo().then(videoFile => {
                                if (advType == 'restaurant_promotion') {
                                    videoFile = null;
                                } else {
                                    coverIMG = null;
                                    profileIMG = null;
                                }
                                database.collection('advertisements').doc(id).set({
                                    'id': id,
                                    'title': title,
                                    'description': description,
                                    'coverImage': coverIMG,
                                    'profileImage': profileIMG,
                                    'video': videoFile,
                                    'vendorId': vendorID,
                                    'priority': priority,
                                    'type': advType,
                                    'showRating': rating,
                                    'showReview': review,
                                    'startDate': startDate,
                                    'endDate': endDate,
                                    'status': 'approved',
                                    'createdAt': firebase.firestore.FieldValue
                                        .serverTimestamp(),
                                    'paymentStatus': true,
                                    'isPaused': null,

                                }).then(function(result) {
                                    jQuery("#data-table_processing").hide();
                                    if (resturant) {
                                        window.location.href =
                                            '{{ route('advertisements') . '/' . $id }}';
                                    } else {
                                        window.location.href =
                                            '{{ route('advertisements') }}';
                                    }

                                });
                            }).catch(function(error) {
                                jQuery("#data-table_processing").hide();
                                $(".error_top").show();
                                $(".error_top").html("");
                                $(".error_top").append("<p>" + error + "</p>");
                            })
                        }).catch(function(error) {
                            jQuery("#data-table_processing").hide();
                            $(".error_top").show();
                            $(".error_top").html("");
                            $(".error_top").append("<p>" + error + "</p>");
                        })
                    }).catch(function(error) {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + error + "</p>");
                    })
                }

            }
        });



        async function storeProfileImage() {
            var newPhoto = '';
            try {
                if (profilePhoto != '') {

                    profilePhoto = profilePhoto.replace(/^data:image\/[a-z]+;base64,/, "")
                    var uploadTask = await storageRef.child(profileFileName).putString(profilePhoto, 'base64', {
                        contentType: 'image/jpg'
                    });

                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto = downloadURL;
                    profilePhoto = downloadURL;
                }
            } catch (error) {
                console.log("ERR ===", error);
            }
            return newPhoto;
        }

        function handleCoverSelect(evt) {
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
                    coverPhoto = filePayload;
                    coverFileName = filename;
                    $(".cover_image").empty();
                    $(".cover_image").append('<img class="rounded" style="width:50px" src="' + coverPhoto +
                        '" alt="image">');
                    $("#preview-cover").css("background-image", "url('" + coverPhoto + "')");
                };
            })(f);
            reader.readAsDataURL(f);
        }

        function handleProfileSelect(evt) {
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
                    profilePhoto = filePayload;
                    profileFileName = filename;
                    $(".profile_image").empty();
                    $(".profile_image").append('<img class="rounded" style="width:50px" src="' + profilePhoto +
                        '" alt="image">');
                    $("#preview-profile").css("background-image", "url('" + profilePhoto + "')");

                };
            })(f);
            reader.readAsDataURL(f);
        }
        async function storeCoverImage() {
            var newPhoto = '';
            try {
                if (coverPhoto != '') {
                    coverPhoto = coverPhoto.replace(/^data:image\/[a-z]+;base64,/, "")
                    var uploadTask = await storageRef.child(coverFileName).putString(coverPhoto, 'base64', {
                        contentType: 'image/jpg'
                    });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto = downloadURL;
                    coverPhoto = downloadURL;

                }
            } catch (error) {
                console.log("ERR ===", error);
            }
            return newPhoto;
        }

        async function handleVideoSelect(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            var isVideo = document.getElementById('video_file');
            var videoValue = isVideo.value;
            var allowedExtensions = /(\.mp4|\.webm|\.mkv)$/i;
            var maxSize = 5 * 1024 * 1024;
            if (!allowedExtensions.exec(videoValue)) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.invalid_video_type')}}</p>");
                window.scrollTo(0, 0);
                isVideo.value = '';
                return false;
            }
            if (f.size > maxSize) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.file_size_must_be_less_than_5MB')}}</p>");
                window.scrollTo(0, 0);
                isVideo.value = '';
                return false;
            }
            var video = document.createElement('video');

            video.onloadedmetadata = function() {

                window.URL.revokeObjectURL(video.src);
                var width = video.videoWidth;
                var height = video.videoHeight;
                if (width > (height * 1.7)) {
                    $(".error_top").hide();
                } else {
                    $(".error_top").show().html(
                        "<p>{{trans('lang.only_landscape_videos_are_allowed')}}</p>");
                    window.scrollTo(0, 0);
                    isVideo.value = '';
                    return false;
                }
                reader.onload = (function(theFile) {
                    return function(e) {

                        var filePayload = e.target.result;
                        var val = f.name;
                        var ext = val.split('.')[1];
                        var docName = val.split('fakepath')[1];
                        var filename = (f.name).replace(/C:\\fakepath\\/i, '')

                        var timestamp = Number(new Date());
                        var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                        videoData = filePayload;
                        videoFileName = filename;
                        $(".video_file").empty();

                        html = '<div class="col-md-3">\n' +
                            '<div class="video-inner"><video width="320px" height="240px"\n' +
                            '                                   controls="controls">\n' +
                            '                            <source src="' + videoData + '"\n' +
                            '            type="video/mp4"></video></div></div>';

                        jQuery(".video_file").append(html);
                        $("#video_file").val('');
                        $("#preview-cover").html(
                            '<video width="100%" height="250px" controls="controls" src="' +
                            videoData + '" type="video/mp4"></video>');

                    };
                })(f);
                reader.readAsDataURL(f);
            }
            video.src = URL.createObjectURL(f);

        }
        async function storeVideo() {
            var newVideoURL = "";
            try {
                if (videoData != '') {

                    var base64String = videoData.split(',')[1];
                    var videoBlob = base64ToBlob(base64String, 'video/mp4');
                    var uploadTask = storageVideoRef.child(videoFileName).put(videoBlob);

                    newVideoURL = await new Promise((resolve, reject) => {
                        uploadTask.on(
                            'state_changed',
                            (snapshot) => {
                                var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;

                            },
                            (error) => {
                                console.log("Error uploading video:", error);
                                reject(error); // Reject promise if an error occurs
                            },
                            async () => {
                                try {
                                    let downloadURL = await uploadTask.snapshot.ref.getDownloadURL();
                                    videoData = downloadURL;

                                    resolve(downloadURL);
                                } catch (error) {
                                    reject(error);
                                }
                            }
                        );
                    });
                }

            } catch (error) {
                console.log("Error uploading video:", error);
            }

            return newVideoURL;
        }

        function base64ToBlob(base64, contentType) {
            var byteCharacters = atob(base64); // Remove Data URL header
            var byteNumbers = new Array(byteCharacters.length);
            for (var i = 0; i < byteCharacters.length; i++) {
                byteNumbers[i] = byteCharacters.charCodeAt(i);
            }
            var byteArray = new Uint8Array(byteNumbers);
            return new Blob([byteArray], {
                type: contentType
            });
        }
    </script>
@endsection
