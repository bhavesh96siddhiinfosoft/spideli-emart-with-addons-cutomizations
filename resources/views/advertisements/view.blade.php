@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.advertisement_details') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('advertisements') !!}">{{ trans('lang.advertisement_plural') }}</a></li>
                    <li class="breadcrumb-item">{{ trans('lang.advertisement_details') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card-body pb-5 p-0">
                <div class="text-right print-btn pb-3">
                    <a href="{{ route('advertisement.chat', $id) }}">
                        <button type="button" class="fa fa-commenting"></button>
                    </a>
                </div>
                <div class="order_detail" id="order_detail">
                    <div class="order_detail-top">
                        <div class="row">
                            <div class="order_edit-genrl col-lg-7 col-md-12">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h3>{{ trans('lang.general_details') }}</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="order_detail-top-box">
                                            <div class="form-group row widt-100 gendetail-col">
                                                <label class="col-12 control-label"><strong>{{ trans('lang.id') }}
                                                        : </strong><span id="advId"></span></label>
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col">
                                                <label class="col-12 control-label"><strong>{{ trans('lang.date_created') }}
                                                        : </strong><span id="createdAt"></span></label>
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col advType">
                                                <label class="col-12 control-label"><strong>{{ trans('lang.advertisement_type') }}
                                                        : </strong><span id="advType"></span></label>
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col">
                                                <label class="col-12 control-label"><strong>{{ trans('lang.duration') }}:</strong>
                                                    <span id="duration"></span></label>
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col">
                                                <label class="col-12 control-label"><strong>{{ trans('lang.payment_status') }}:</strong>
                                                    <span id="paymentStatus"></span></label>
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col">
                                                <label class="col-12 control-label"><strong>{{ trans('lang.status') }}:</strong>
                                                    <span id="status"></span></label>
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col d-none paused-note-div">
                                                <label class="col-12 control-label"><strong>{{ trans('lang.paused_note') }}:</strong>
                                                    <span id="paused_note" class="text-danger"></span></label>
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col d-none cancel-note-div">
                                                <label class="col-12 control-label"><strong>{{ trans('lang.cancel_note') }}:</strong>
                                                    <span id="cancel_note" class="text-danger"></span></label>
                                            </div>

                                            <div class="form-group row widt-100  update-status-div d-none">
                                                <label class="col-3 control-label">{{ trans('lang.update_status') }}:</label>
                                                <div class="col-7">
                                                    <select id="update_status" class="form-control">
                                                        <option value="pending" id="pending" disabled selected>
                                                            {{ trans('lang.pending') }}
                                                        </option>
                                                        <option value="approved" id="approve">
                                                            {{ trans('lang.approve') }}
                                                        </option>
                                                        <option value="canceled" id="deny">
                                                            {{ trans('lang.canceled') }}
                                                        </option>

                                                    </select>
                                                </div>

                                            </div>
                                            <div class="form-group row width-100 update-status-div d-none">
                                                <label class="col-3 control-label"></label>
                                                <div class="col-7 text-right">
                                                    <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i> {{ trans('lang.update') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mt-5">
                                    <div class="card-header bg-white">
                                        <h3>{{ trans('lang.advertisement_details') }}</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="address order_detail-top-box">
                                            <p>
                                                <strong>{{ trans('lang.title') }}: </strong><span id="advTitle"></span>
                                            </p>
                                            <p>
                                                <strong>{{ trans('lang.description') }}: </strong>
                                                <span id="advDesc"></span>

                                            </p>
                                            <div class="row">
                                                <div class="preview-box col-md-3 image-div">
                                                    <h4>{{ trans('lang.profile_image') }}</h4>
                                                    <div id="profileImage"></div>
                                                </div>
                                                <div class="preview-box col-md-4 image-div">
                                                    <h4>{{ trans('lang.cover_image') }}</h4>
                                                    <div id="coverImage"></div>
                                                </div>
                                                <div class="preview-box col-md-12 video-div">
                                                    <h4>{{ trans('lang.video') }}</h4>
                                                    <div id="video"></div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="order_addre-edit col-lg-5 col-md-12">

                                <div class="order_addre-edit driver_details_hide">
                                    <div class="card mt-4">
                                        <div class="card-header bg-white">
                                            <h3>{{ trans('lang.advertisement_setup') }}</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="address order_detail-top-box">
                                                <div class="form-group row mt-1 ">
                                                    <div class="col-12 switch-box p-0">
                                                        <div class="switch-box-inner">
                                                            <label class=" control-label">{{ trans('lang.payment_status') }}</label>
                                                            <label class="switch"> <input type="checkbox" name="update_pay_status" id="update_pay_status"><span class="slider round"></span></label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row width-100 calendar-box">
                                                    <label class="col-12 control-label p-0 mb-3"><strong>{{ trans('lang.validity') }}</strong></label>
                                                    <div class="col-12 p-0">
                                                        <div id="daterange" class="form-control"><i class="fa fa-calendar"></i>&nbsp;
                                                            <span></span>&nbsp; <i class="fa fa-caret-down"></i>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="resturant-detail mt-4">
                                    <div class="card">
                                        <div class="card-header bg-white">
                                            <h4 class="card-header-title">{{ trans('lang.store_info') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            <a href="#" class="row redirecttopage align-items-center" id="resturant-view">
                                                <div class="col-md-3">
                                                    <img src="" class="resturant-img rounded-circle" alt="vendor" width="70px" height="70px">
                                                </div>
                                                <div class="col-md-9">
                                                    <h4 class="vendor-title"></h4>
                                                </div>
                                            </a>
                                            <h5 class="contact-info">{{ trans('lang.contact_info') }}:</h5>
                                            <p><strong>{{ trans('lang.phone') }}:</strong>
                                                <span id="vendor_phone"></span>
                                            </p>
                                            <p><strong>{{ trans('lang.address') }}:</strong>
                                                <span id="vendor_address"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-12 text-center btm-btn">
                <a href="{!! route('advertisements') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}
                </a>
            </div>
        </div>
    </div>

    <div class="modal fade" id="toggle-status-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img id="toggle-status-image" alt="" class="mb-20" src="{{ asset('images/dm-tips.png') }}">
                                <h5 class="modal-title" id="toggle-status-title">{{ trans('lang.are_you_sure') }}</h5>
                            </div>
                            <div class="text-center" id="toggle-status-message">
                                <p class="toggal-status-msg"></p>
                            </div>
                        </div>
                        <div class="btn-container justify-content-center text-center">
                            <button type="button" id="toggle-status-ok-button" class="btn btn-primary min-w-120 confirm-Status-Toggle" data-dismiss="modal" toggle-ok-button="is_paid">{{ trans('lang.ok') }}</button>
                            <button id="reset_btn" type="reset" class="btn btn-cancel min-w-120" data-dismiss="modal">
                                {{ trans('lang.cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ad-cancel-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img id="toggle-status-image" alt="" class="mb-20" src="{{ asset('images/ad-deny.png') }}">
                                <h5 class="modal-title" id="toggle-status-title">{{ trans('lang.are_you_sure_to_cancel_ad') }}</h5>
                            </div>
                            <div class="text-center" id="toggle-status-message">
                                <p class="toggal-status-msg">{{ trans('lang.you_will_lose_restaurant_ad_request') }}</p>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group row">
                                <div class="form-group row width-100">

                                    <div class="col-12">
                                        <input type="text" placeholder="{{ trans('lang.your_note_here') }}" name="cancel_reason" class="form-control" id="cancel_reason">
                                        <div id="add_cancel_note_error" class="font-weight-bold text-danger"></div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="btn-container justify-content-center text-center">
                            <button type="button" id="cancel-btn" class="btn btn-primary min-w-120 cancel-btn" data-dismiss="modal">{{ trans('lang.ok') }}</button>
                            <button id="reset_btn" type="reset" class="btn btn-cancel min-w-120" data-dismiss="modal">
                                {{ trans('lang.cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('style')
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.js"></script>
    <script>
        var id = "<?php echo $id; ?>";
        var database = firebase.firestore();
        var placeholder = database.collection('settings').doc('placeHolderImage');
        var vendorFcm = '';
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        });
        var ref = database.collection('advertisements').where("id", "==", id);
        var advApprovedSub = '';
        var advApprovedMsg = '';
        var advCancelledSub = '';
        var advCancelledMsg = '';
        database.collection('dynamic_notification').get().then(async function(snapshot) {
            if (snapshot.docs.length > 0) {
                snapshot.docs.map(async (listval) => {
                    val = listval.data();
                    if (val.type == "advertisement_approved") {
                        advApprovedSub = val.subject;
                        advApprovedMsg = val.message;
                    } else if (val.type == "advertisement_cancelled") {
                        advCancelledSub = val.subject;
                        advCancelledMsg = val.message;
                    }
                });
            }
        });

        function setDate(startDate = null, endDate = null) {
            if (!startDate || !endDate) {
                $('#daterange span').html('{{ trans('lang.select_range') }}');
            } else {
                $('#daterange span').html(moment(startDate).format('MMMM D, YYYY') + ' - ' + moment(endDate).format('MMMM D, YYYY'));
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
                $('#daterange span').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
                updateValidity();
            });
            $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                $('#daterange span').html('{{ trans('lang.select_range') }}');
            });
        }

        ref.get().then(async function(snapshots) {
            var data = snapshots.docs[0].data();
            $('#advId').html(data.id);
            if (data.status == 'pending') {
                $('.update-status-div').removeClass('d-none');
            } else if (data.status == 'updated') {
                $('.update-status-div').removeClass('d-none');
            } else if (data.status == 'canceled') {
                $('#update_status').val(data.status);
                $("#update_status option[value='canceled']").prop('disabled', true);
                $('.update-status-div').removeClass('d-none');
            }
            var date1 = data.createdAt.toDate().toDateString();
            var date = new Date(date1);
            var dd = String(date.getDate()).padStart(2, '0');
            var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
            var yyyy = date.getFullYear();
            var createdAt_val = yyyy + '-' + mm + '-' + dd;
            var time = data.createdAt.toDate().toLocaleTimeString('en-US');
            $('#createdAt').html(createdAt_val + ' ' + time);
            (data.type == 'restaurant_promotion') ? $('#advType').html('{{ trans('lang.restaurant_promotion') }}'): $('#advType').html('{{ trans('lang.video_promotion') }}');
            if (data.type == 'restaurant_promotion') {
                $('.video-div').addClass('d-none');
                $('#profileImage').append('<img src="' + data.profileImage + '" style="width: 100%; height: 150px;" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"> ');
                $('#coverImage').append('<img src="' + data.coverImage + '" style="width: 100%; height: 150px;" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"> ')

            } else {
                $('.image-div').addClass('d-none');
                $('#video').append('<video width="100%" height="300px" controls="controls" src="' + data.video + '" type="video/mp4"></video>')
            }
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            var startDate = data.startDate.toDate().toLocaleDateString('en-US', options);
            var endDate = data.endDate.toDate().toLocaleDateString('en-US', options);
            $('#duration').html(startDate + '-' + endDate);
            startDate = new Date(data.startDate.seconds * 1000);
            endDate = new Date(data.endDate.seconds * 1000);
            setDate(startDate, endDate);
            if (data.paymentStatus) {
                $('#paymentStatus').html('<span class="badge badge-success py-2 px-3">{{ trans('lang.paid') }}</span>');
                $("#update_pay_status").prop('checked', true);
            } else {
                $('#paymentStatus').html('<span class="badge badge-danger py-2 px-3">{{ trans('lang.unpaid') }}</span>');
                $("#update_pay_status").prop('checked', false);
            }
            var status = data.status.charAt(0).toUpperCase() + data.status.slice(1);

            if (data.hasOwnProperty('isPaused') && data.isPaused) {
                $('#status').html('<span class="badge badge-info py-2 px-3">{{ trans('lang.paused') }}</span>');
                $('.paused-note-div').removeClass('d-none');
                if (data.hasOwnProperty('pauseNote') && data.pauseNote != null && data.pauseNote != '') {
                    $('#paused_note').html(data.pauseNote);
                }
            } else {
                if (data.status == 'approved') {
                    const ExpiryDate = data.endDate;
                    if (ExpiryDate && new Date(ExpiryDate.seconds * 1000) < new Date()) {
                        $('#status').html('<span class="badge badge-danger py-2 px-3">{{ trans('lang.expired') }}</span>');
                    } else {
                        const startDate = data.startDate;
                        if (startDate && new Date(startDate.seconds * 1000) < new Date() && data.paymentStatus) {
                            $('#status').html('<span class="badge badge-success py-2 px-3">{{ trans('lang.running') }}</span>');
                        } else {
                            $('#status').html('<span class="badge badge-success py-2 px-3">{{ trans('lang.approved') }}</span>');

                        }
                    }
                } else if (data.status == 'pending' || data.status == 'updated') {

                    $('#status').html('<span class="badge badge-info py-2 px-3">{{ trans('lang.pending') }}</span>');
                } else {
                    $('.cancel-note-div').removeClass('d-none');
                    if (data.hasOwnProperty('canceledNote') && data.canceledNote != null && data.canceledNote != '') {
                        $('#cancel_note').html(data.canceledNote);
                    }
                    $('#status').html('<span class="badge badge-danger py-2 px-3">{{ trans('lang.canceled') }}</span>');
                }
            }
            $('#advTitle').html(data.title);
            $('#advDesc').html(data.description);

            getVendorDetails(data.vendorId);
        })
        async function getVendorDetails(vendorId) {
            database.collection('vendors').where('id', '==', vendorId).get().then(async function(snapshots) {
                if (snapshots.docs.length > 0) {
                    var vendordata = snapshots.docs[0].data();
                    var vendorUserId = vendordata.author;
                    await database.collection('users').doc(vendorUserId).get().then(async function(snapshots) {
                        if (snapshots.exists) {
                            var data = snapshots.data();
                            vendorFcm = data.fcmToken;
                        }
                    })
                    if (vendordata.id) {
                        var route_view = '{{ route('stores.view', ':id') }}';
                        route_view = route_view.replace(':id', vendordata.id);
                        $('#resturant-view').attr('data-url', route_view);
                    }
                    if (vendordata.photo != "" && vendordata.photo != null) {
                        $('.resturant-img').attr('src', vendordata.photo);
                    } else {
                        $('.resturant-img').attr('src', placeholderImage);
                    }
                    if (vendordata.title != "" && vendordata.title != null) {
                        $('.vendor-title').html(vendordata.title);
                    }
                    if (vendordata.phonenumber != "" && vendordata.phonenumber !=
                        null) {
                        $('#vendor_phone').text(shortEditNumber(vendordata
                            .phonenumber));
                    } else {
                        $('#vendor_phone').text("");
                    }
                    if (vendordata.location != "" && vendordata.location != null) {
                        $('#vendor_address').text(vendordata.location);
                    }
                } else {
                    $('.resturant-img').attr('src', placeholderImage);
                    $('.vendor-title').html("{{ trans('lang.unknown') }}");


                }
            })
        }
        $('#update_pay_status').on('click', function() {
            if ($("#update_pay_status").is(':checked')) {
                $('.toggal-status-msg').html('{{ trans('lang.you_want_to_mark_adv_paid') }}')
            } else {
                $('.toggal-status-msg').html('{{ trans('lang.you_want_to_mark_adv_unpaid') }}')
            }
            $('#toggle-status-modal').modal('show');
        })
        $('#toggle-status-ok-button').on('click', async function() {
            $('#data-table_processing').show();
            var status = false;
            if ($("#update_pay_status").is(':checked')) {
                status = true;
            }
            await database.collection('advertisements').doc(id).update({
                'paymentStatus': status
            }).then(async function(result) {
                window.location.reload();
            })
        })
        async function updateValidity() {
            var daterangepicker = $('#daterange').data('daterangepicker');
            if (daterangepicker) {
                var from = moment(daterangepicker.startDate).toDate();
                var to = moment(daterangepicker.endDate).toDate();
                if (from && to) {
                    $('#data-table_processing').show();
                    var fromDate = firebase.firestore.Timestamp.fromDate(new Date(from));
                    var toDate = firebase.firestore.Timestamp.fromDate(new Date(to));
                    await database.collection('advertisements').doc(id).update({
                        'startDate': fromDate,
                        'endDate': toDate
                    }).then(async function(result) {
                        window.location.reload();
                    })
                } else {
                    window.location.reload();
                }
            } else {
                window.location.reload();
            }

        }
        $('.edit-form-btn').on('click', async function() {
            var status = $('#update_status').val();
            
                if (status == 'canceled') {
                    $('#ad-cancel-modal').modal('show');
                }else if(status=='approved') {
                    await database.collection('advertisements').doc(id).update({
                        'status': status
                    }).then(async function(result) {
                        await sendNotification('approved');

                    })
                }else{
                    window.location.href = '{{ route('advertisements') }}';
                }
            

        })
        $('#cancel-btn').click(async function() {
            var reason = $('#cancel_reason').val();
            if (reason == '') {
                $('#add_cancel_note_error').html('{{ trans('lang.add_cancel_note_error') }}');
                return false;
            }
            await database.collection('advertisements').doc(id).update({
                'status': 'canceled',
                'canceledNote': reason
            }).then(async function(result) {
                await sendNotification('canceled');

            })
        })
        async function sendNotification(status) {
            if (status == 'canceled') {
                var title = advCancelledSub;
                var message = advCancelledMsg
            } else {
                var title = advApprovedSub;
                var message = advApprovedMsg;
            }
            await $.ajax({
                type: 'POST',
                url: "<?php echo route('advertisement.sendnotification'); ?>",
                data: {
                    _token: '<?php echo csrf_token(); ?>',
                    'fcm': vendorFcm,
                    'title': title,
                    'message': message
                },
                success: function(data) {
                    window.location.href = '{{ route('advertisements') }}';
                }
            });
        }
    </script>
@endsection
