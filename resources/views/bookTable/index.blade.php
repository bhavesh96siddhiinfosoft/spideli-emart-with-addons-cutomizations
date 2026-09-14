@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.dine_in_booking_history') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.dine_in_booking_history') }}</li>
                </ol>
            </div>
            <div>
            </div>
        </div>
        <div class="container-fluid">

            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="menu-tab">

                            <ul>
                               <li>
                                    <a href="{{ route('stores.view', $id) }}"><i class="ri-list-indefinite"></i>{{ trans('lang.tab_basic') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('vendors.items', $id) }}"><i class="ri-shopping-basket-fill"></i>{{ trans('lang.tab_items') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('vendors.orders', $id) }}"><i class="ri-shopping-bag-line"></i> {{ trans('lang.tab_orders') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('vendors.reviews', $id) }}"><i class="ri-shield-star-fill"></i>{{ trans('lang.tab_reviews') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('vendors.coupons', $id) }}"><i class="ri-discount-percent-fill"></i>{{ trans('lang.tab_promos') }}</a>
                                <li>
                                    <a href="{{ route('vendors.payout', $id) }}"><i class="ri-bank-card-line"></i>{{ trans('lang.tab_payouts') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('payoutRequests.vendor.view', $id) }}"><i class="ri-refund-line"></i>{{ trans('lang.tab_payout_request') }}</a>
                                </li>
                                <li class="dine_in_future active">
                                    <a href="{{ route('vendors.booktable', $id) }}"><i class="ri-restaurant-line"></i>{{ trans('lang.dine_in_booking_history') }}</a>
                                </li>
                                <?php if (in_array('wallet-transaction', json_decode(@session('user_permissions'),true))) { ?>
                                <li>
                                    <a class="wallet_transaction"><i class="ri-wallet-line"></i>{{ trans('lang.wallet_transaction') }}</a>
                                </li>
                                <?php }?>
                                <?php
                                $subscription = route('subscription.subscriptionPlanHistory', ':id');
                                $subscription = str_replace(':id', 'storeID=' . $id, $subscription);
                                ?>
                                <li>
                                    <a href="{{ $subscription }}"><i class="ri-chat-history-fill"></i>{{ trans('lang.subscription_history') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('restaurants.advertisements', $id) }}"><i class="mdi mdi-newspaper"></i>{{ trans('lang.advertisement_plural') }}</a>
                                </li>
                                 @php
                                    $sectionType = $_COOKIE['service_type'] ?? ''; 
                                    
                                @endphp
                                <?php if($sectionType == 'ecommerce-service'){ ?>
                               
                                <?php }else{ ?>
                                <li class="">
                                    <a href="{{ route('restaurants.deliveryman', $id) }}"><i class="ri-riding-fill"></i>{{ trans('lang.deliveryman') }}</a>
                                </li>
                                    <?php }?>
                            </ul>

                        </div>
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-self-center">
                                <span class="icon mr-3"><img src="{{ asset('images/table_booking.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.dine_in_booking_history') }}</h3>
                                <span class="counter ml-3 total_count"></span>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="table-list">
                <div class="row">
                    <div class="col-12">
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.dine_in_booking_history') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.book_table_text') }}</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>

                                                <th>{{ trans('lang.date') }}</th>
                                                <th>{{ trans('lang.guestNumber') }}</th>
                                                <th>{{ trans('lang.guestName') }}</th>
                                                <th>{{ trans('lang.guestPhone') }}</th>
                                                <th>{{ trans('lang.status') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                        </tbody>
                                    </table>
                                    <div id="data-table_paginate" style="display:none">
                                        <nav aria-label="Page navigation example">
                                            <ul class="pagination justify-content-center">
                                                <li class="page-item ">
                                                    <a class="page-link" href="javascript:void(0);" id="users_table_previous_btn" onclick="prev()" data-dt-idx="0" tabindex="0">{{ trans('lang.previous') }}</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="javascript:void(0);" id="users_table_next_btn" onclick="next()" data-dt-idx="2" tabindex="0">{{ trans('lang.next') }}</a>
                                                </li>
                                            </ul>
                                        </nav>
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
    <script type="text/javascript">
        var database = firebase.firestore();
        var offest = 1;
        var pagesize = 10;
        var end = null;
        var endarray = [];
        var start = null;
        var user_number = [];
        var vendorUserId = "<?php echo $id; ?>";
        var vendorId;
        var ref;
        var append_list = '';
        var placeholderImage = '';

        <?php if($id != ''){ ?>
        getStoreNameFunction('<?php echo $id; ?>');
        <?php } ?>

        var dineInOrderAcceptedSubject = '';
        var dineInOrderAcceptedMsg = '';
        var dineInOrderRejectedSubject = '';
        var dineInOrderRejectedMsg = '';
        var wallet_route = "{{ route('users.walletstransaction', 'id') }}";


        database.collection('dynamic_notification').where('type', 'in', ['dinein_accepted', 'dinein_canceled']).get().then(async function(snapshot) {
            if (snapshot.docs.length > 0) {
                snapshot.docs.map(async (listval) => {
                    val = listval.data();
                    if (val.type == "dinein_accepted") {
                        dineInOrderAcceptedSubject = val.subject;
                        dineInOrderAcceptedMsg = val.message;
                    } else if (val.type == "dinein_canceled") {
                        dineInOrderRejectedSubject = val.subject;
                        dineInOrderRejectedMsg = val.message;

                    }

                });
            }
        });
        ref = database.collection('booked_table').orderBy('createdAt', 'desc').where('vendorID', "==", vendorUserId);
        if (vendorUserId != '') {
            $(".wallet_transaction").attr("href", wallet_route.replace('id', 'storeID=' + vendorUserId));
        }
        $(document).ready(function() {

            $(document.body).on('click', '.redirecttopage', function() {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });
            var inx = parseInt(offest) * parseInt(pagesize);
            jQuery("#data-table_processing").show();
            append_list = document.getElementById('append_list1');
            append_list.innerHTML = '';

            var placeholder = database.collection('settings').doc('placeHolderImage');
            placeholder.get().then(async function(snapshotsimage) {
                var placeholderImageData = snapshotsimage.data();
                placeholderImage = placeholderImageData.image;
            })

            ref.limit(pagesize).get().then(async function(snapshots) {
                html = '';
                html = buildHTML(snapshots);
                if (snapshots.docs.length > 0) {
                    $('.total_count').text(snapshots.docs.length);
                    html = await buildHTML(snapshots);
                } else {
                    $('.total_count').text(0);
                }
                if (html != '') {
                    append_list.innerHTML = html;
                    start = snapshots.docs[snapshots.docs.length - 1];
                    endarray.push(snapshots.docs[0]);
                }
                var table = $('#example24').DataTable({

                    order: [],
                    columnDefs: [{
                        orderable: false,
                        targets: [0, 1, 2, 4]
                    }, ],
                    "language": datatableLang,
                    responsive: true,
                });
                table.on('search.dt', function() {
                    var filteredCount = table.rows({
                        search: 'applied'
                    }).count();
                    $('.total_count').text(filteredCount); // Update count
                });
                if (snapshots.docs.length < pagesize) {
                    jQuery("#data-table_paginate").hide();
                } else {
                    jQuery("#data-table_paginate").show();
                }

                jQuery("#data-table_processing").hide();
            });
        });

        function getStoreNameFunction(vendorId) {
            var vendorName = '';
            database.collection('vendors').where('id', '==', vendorId).get().then(function(snapshots) {
                var vendorData = snapshots.docs[0].data();
                vendorName = vendorData.title;
                $(".storeTitle").text(' - ' + vendorName);
            });
            return vendorName;
        }

        function buildHTML(snapshots) {
            var html = '';
            var alldata = [];
            var number = [];
            snapshots.docs.forEach((listval) => {
                var datas = listval.data();
                datas.id = listval.id;
                alldata.push(datas);
            });

            var count = 0;
            alldata.forEach((listval) => {

                var val = listval;

                html = html + '<tr>';
                newdate = '';

                var id = val.id;
                var route1 = '{{ route('booktable.edit', ':id') }}?id=<?php echo $id; ?>';
                route1 = route1.replace(':id', id);

                html = html + '<td data-url="' + route1 + '" class="redirecttopage">' + val.date.toDate().toDateString() + '</td>';
                html = html + '<td data-url="' + route1 + '" class="redirecttopage">' + val.totalGuest + '</td>';
                html = html + '<td data-url="' + route1 + '" class="redirecttopage">' + val.guestFirstName + ' ' + val.guestLastName + '</td>';
                html = html + '<td data-url="' + route1 + '" class="redirecttopage">' + val.guestPhone + '</td>';
                var statustext = "";
                if (val.status == "Order Rejected") {
                    statustext = "Request Rejected";
                } else if (val.status == "Order Placed") {
                    statustext = "Requested";
                } else if (val.status == "Order Accepted") {
                    statustext = "Request Accepted";
                }
                html = html + '<td data-url="' + route1 + '" class="redirecttopage">' + statustext + '</td>';


                html = html + '<td><span class="action-btn"><a id="' + val.id + '" name="book-table-check" data-name="' + val.vendor.title + '" data-auth="' + val.author.id + '" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.accept') }}"><i class="mdi mdi-check" ></i></a><a id="' + val.id + '" name="book-table-dismiss" data-auth="' + val.author.id + '" data-name="' + val.vendor.title + '" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.reject') }}"><i class="mdi mdi-close" ></i></a><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a><a id="' + val
                    .id + '" name="book-table-delete" class="do_not_delete" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a></span></td>';


                html = html + '</tr>';
                count = count + 1;
            });
            return html;
        }

        function prev() {
            if (endarray.length == 1) {
                return false;
            }
            end = endarray[endarray.length - 2];

            if (end != undefined || end != null) {
                jQuery("#data-table_processing").show();
                if (jQuery("#selected_search").val() == 'name' && jQuery("#search").val().trim() != '') {

                    listener = ref.orderBy('name').limit(pagesize).startAt(jQuery("#search").val()).endAt(jQuery("#search").val() + '\uf8ff').startAt(end).get();
                } else {
                    listener = ref.startAt(end).limit(pagesize).get();
                }

                listener.then((snapshots) => {
                    html = '';
                    html = buildHTML(snapshots);
                     $(function () {
                                $('[data-toggle="tooltip"]').tooltip();
                            });
                    jQuery("#data-table_processing").hide();
                    if (html != '') {
                        append_list.innerHTML = html;
                        start = snapshots.docs[snapshots.docs.length - 1];
                        endarray.splice(endarray.indexOf(endarray[endarray.length - 1]), 1);

                        if (snapshots.docs.length < pagesize) {

                            jQuery("#users_table_previous_btn").hide();
                        }

                    }
                });
            }
        }

        function next() {
            if (start != undefined || start != null) {

                jQuery("#data-table_processing").hide();

                if (jQuery("#selected_search").val() == 'name' && jQuery("#search").val().trim() != '') {

                    listener = ref.orderBy('name').limit(pagesize).startAt(jQuery("#search").val()).endAt(jQuery("#search").val() + '\uf8ff').startAfter(start).get();
                } else {
                    listener = ref.startAfter(start).limit(pagesize).get();
                }
                listener.then((snapshots) => {

                    html = '';
                    html = buildHTML(snapshots);

                    jQuery("#data-table_processing").hide();
                    if (html != '') {
                        append_list.innerHTML = html;
                        start = snapshots.docs[snapshots.docs.length - 1];

                        if (endarray.indexOf(snapshots.docs[0]) != -1) {
                            endarray.splice(endarray.indexOf(snapshots.docs[0]), 1);
                        }
                        endarray.push(snapshots.docs[0]);
                    }
                });
            }
        }

        function searchclear() {
            jQuery("#search").val('');
            searchtext();
        }

        function searchtext() {

            var offest = 1;

            jQuery("#data-table_processing").show();

            append_list.innerHTML = '';

            if (jQuery("#selected_search").val() == 'name' && jQuery("#search").val().trim() != '') {

                wherequery = ref.orderBy('name').limit(pagesize).startAt(jQuery("#search").val()).endAt(jQuery("#search").val() + '\uf8ff').get();

            } else {

                wherequery = ref.limit(pagesize).get();
            }

            wherequery.then((snapshots) => {
                html = '';
                html = buildHTML(snapshots);
                jQuery("#data-table_processing").hide();
                if (html != '') {
                    append_list.innerHTML = html;
                    start = snapshots.docs[snapshots.docs.length - 1];

                    endarray.push(snapshots.docs[0]);

                    if (snapshots.docs.length < pagesize) {

                        jQuery("#data-table_paginate").hide();
                    } else {

                        jQuery("#data-table_paginate").show();
                    }
                }
            });

        }

        $(document).on("click", "a[name='book-table-delete']", function(e) {
            var id = this.id;
            database.collection('booked_table').doc(id).delete().then(function(result) {
                window.location.href = '{{ url()->current() }}';
            });
        });

        $(document).on("click", "a[name='book-table-check']", function(e) {
            var id = this.id;
            var fullname = $(this).attr('data-name');
            var auth = $(this).attr('data-auth');
            database.collection('booked_table').doc(id).update({
                'status': 'Order Accepted'
            }).then(function(result) {

                database.collection('users').where('id', '==', auth).get().then(function(snapshots) {

                    if (snapshots.docs.length) {
                        snapshots.forEach((doc) => {
                            user = doc.data();
                            if (user.fcmToken) {
                                $.ajax({
                                    method: 'POST',
                                    url: '<?php echo route('sendnotification'); ?>',
                                    data: {
                                        'fcm': user.fcmToken,
                                        'type': 'booktable_request_accepted',
                                        'authorName': fullname,
                                        '_token': '<?php echo csrf_token(); ?>',
                                        'subject': dineInOrderAcceptedSubject,
                                        'message': dineInOrderAcceptedMsg
                                    }
                                }).done(function(data) {
                                    window.location.href = '{{ url()->current() }}';
                                }).fail(function(xhr, textStatus, errorThrown) {
                                    window.location.href = '{{ url()->current() }}';
                                });
                            } else {
                                window.location.href = '{{ url()->current() }}';
                            }
                        });
                    } else {
                        //window.location.href = '{{ url()->current() }}';
                    }
                });

            });

        });

        $(document).on("click", "a[name='book-table-dismiss']", function(e) {
            var id = this.id;
            var fullname = $(this).attr('data-name');
            var auth = $(this).attr('data-auth');
            database.collection('booked_table').doc(id).update({
                'status': 'Order Rejected'
            }).then(function(result) {

                database.collection('users').where('id', '==', auth).get().then(function(snapshots) {
                    if (snapshots.docs.length) {
                        snapshots.forEach((doc) => {

                            user = doc.data();
                            if (user.fcmToken) {
                                $.ajax({
                                    method: 'POST',
                                    url: '<?php echo route('sendnotification'); ?>',
                                    data: {
                                        'fcm': user.fcmToken,
                                        'type': 'booktable_request_reject',
                                        'authorName': fullname,
                                        '_token': '<?php echo csrf_token(); ?>',
                                        'subject': dineInOrderRejectedSubject,
                                        'message': dineInOrderRejectedMsg
                                    }
                                }).done(function(data) {
                                    window.location.href = '{{ url()->current() }}';
                                }).fail(function(xhr, textStatus, errorThrown) {
                                    window.location.href = '{{ url()->current() }}';
                                });
                            } else {
                                window.location.href = '{{ url()->current() }}';
                            }
                        });
                    } else {
                        window.location.href = '{{ url()->current() }}';
                    }
                });


            });

        });
    </script>
@endsection
