@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <div class="d-flex top-title-section justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/category.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.advertisement_plural')}} <span class="page-title"></span></h3>
                        <span class="counter ml-3 advertisement_count"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.advertisement_plural') }}</li>
                </ol>
            </div>
            <div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="table-list">
                <div class="row">
                    <div class="col-12">
                        <?php if ($id != '') { ?>
                        <div class="menu-tab">
                            <ul>
                                <li>
                                    <a href="{{ route('stores.view', $id) }}"><i class="ri-list-indefinite"></i>{{ trans('lang.tab_basic') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('vendors.items', $id) }}"><i class="ri-shopping-basket-fill"></i>{{ trans('lang.tab_items') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('vendors.orders', $id) }}"><i class="ri-shopping-bag-line"></i>{{ trans('lang.tab_orders') }}</a>
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
                               <li class="dine_in_future" style="display:none;">

                            <a href="{{route('vendors.booktable',$id)}}"><i class="ri-restaurant-line"></i>{{trans('lang.dine_in_booking_history')}}</a>

                        </li>
                                <?php if (in_array('wallet-transaction', json_decode(@session('user_permissions'), true))) { ?>
                                <li>
                                    <a class="wallet_transaction" href=""><i class="ri-wallet-line"></i>{{ trans('lang.wallet_transaction') }}</a>
                                </li>
                                <?php }?>
                                <?php
                                $subscription = route('subscription.subscriptionPlanHistory', ':id');
                                $subscription = str_replace(':id', 'storeID=' . $id, $subscription);
                                ?>
                                <li>
                                    <a href="{{ $subscription }}"><i class="ri-chat-history-fill"></i>{{ trans('lang.subscription_history') }}</a>
                                </li>

                                <li class="active">
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
                        <?php } ?>

                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.advertisement_table') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.advertisement_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <?php if ($id != '' && $id != null) { ?>
                                        <a class="btn-primary btn rounded-full" href="{!! route('advertisements.create') !!}/{{$id}}" onclick="localStorage.removeItem('copiedAdvertisement')"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.advertisement_create') }}</a>
                                            <?php  } else { ?>
                                        <a class="btn-primary btn rounded-full" href="{!! route('advertisements.create') !!}" onclick="localStorage.removeItem('copiedAdvertisement')"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.advertisement_create') }}</a>
                                     <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="advertisementTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <?php if (in_array('advertisements.delete', json_decode(@session('user_permissions'),true))) {
                                                ?>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active">
                                                        <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label></th>
                                                <?php }
                                                ?>
                                                <th>{{ trans('lang.ads_title') }}</th>
                                                <?php if ($id == '') { ?>
                                                <th>{{ trans('lang.res_info') }}</th>
                                                <?php } ?>
                                                <th> {{ trans('lang.ads_type') }}</th>
                                                <th> {{ trans('lang.duration') }}</th>
                                                <th> {{ trans('lang.status') }}</th>
                                                <th> {{ trans('lang.priority') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="advPauseModal" aria-hidden="true">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img id="toggle-status-image" alt="" class="mb-20" src="{{ asset('images/ad-pause.png') }}">
                                <h5 class="modal-title" id="toggle-status-title">{{ trans('lang.are_you_sure_you_want_to_pause_the_request') }}</h5>
                            </div>
                            <div class="text-center mt-3" id="toggle-status-message">
                                <p class="toggal-status-msg">{{ trans('lang.this_add_will_paused_not_show_in_app_and_web') }}</p>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group row">
                                <div class="form-group row width-100">

                                    <div class="col-12">
                                        <input type="text" placeholder="{{ trans('lang.your_note_here') }}" name="pause_reason" class="form-control" id="pause_reason">
                                        <div id="add_pause_error" class="font-weight-bold text-danger"></div>
                                    </div>
                                    <input type="hidden" id="pauseAdvId">
                                </div>
                            </div>
                        </div>
                        <div class="btn-container justify-content-center text-center">
                            <button type="button" id="toggle-status-ok-button" class="btn btn-primary min-w-120 confirm-pause-btn" toggle-ok-button="is_paid">{{ trans('lang.ok') }}</button>
                            <button id="reset_btn" type="reset" class="btn btn-cancel min-w-120" data-bs-dismiss="modal">
                                {{ trans('lang.cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="advResumeModal" aria-hidden="true">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img id="toggle-status-image" alt="" class="mb-20" src="{{ asset('images/ad-resume.png') }}">
                                <h5 class="modal-title" id="toggle-status-title">{{ trans('lang.are_you_sure_you_want_to_resume_the_request') }}</h5>
                            </div>
                            <div class="text-center mt-3" id="toggle-status-message">
                                <p class="toggal-status-msg">{{ trans('lang.this_app_will_resume_and_show_in_app_and_web') }}</p>
                            </div>
                            <input type="hidden" id="resumeAdvId">
                        </div>

                        <div class="btn-container justify-content-center text-center">
                            <button type="button" id="toggle-status-ok-button" class="btn btn-primary min-w-120 confirm-resume-btn" toggle-ok-button="is_paid">{{ trans('lang.ok') }}</button>
                            <button id="reset_btn" type="reset" class="btn btn-cancel min-w-120" data-bs-dismiss="modal">
                                {{ trans('lang.cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        var id = "{{ $id }}";
        var section_id = getCookie('section_id') || '';
        if (id != '') {
            database.collection('vendors').where("id", "==", '<?php echo $id; ?>').get().then(async function(snapshots) {
                var vendorData = snapshots.docs[0].data();
                var wallet_route = "{{ route('users.walletstransaction', 'id') }}";
                $(".wallet_transaction").attr("href", wallet_route.replace('id', 'storeID=' + vendorData.author));
                if (vendorData.hasOwnProperty('dine_in_active') && vendorData.dine_in_active == true) {
                    $(".dine_in_future").show();
                }
                if (vendorData.section_id) {
                const sectionSnap = await database.collection('sections').doc(vendorData.section_id).get();
                if (sectionSnap.exists) {
                    const sectionData = sectionSnap.data();
                    if (sectionData.dine_in_active === true) {
                        $(".dine_in_future").show();
                    }
                }
            }
            });
            var ref = database.collection('advertisements').where('status', '==', 'approved').where('vendorId', '==', id);
        } else {
            var ref = database.collection('advertisements').where('status', '==', 'approved');
        }
        var placeholderImage = '';
        var user_permissions = '<?php echo @session('user_permissions'); ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkDeletePermission = false;
        if ($.inArray('advertisements.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }
        var advPausedSub = '';
        var advPausedMsg = '';
        var advResumedSub = '';
        var advResumedMsg = '';
        database.collection('dynamic_notification').get().then(async function(snapshot) {
            if (snapshot.docs.length > 0) {
                snapshot.docs.map(async (listval) => {
                    val = listval.data();
                    if (val.type == "advertisement_paused") {
                        advPausedSub = val.subject;
                        advPausedMsg = val.message;
                    } else if (val.type == "advertisement_resumed") {
                        advResumedSub = val.subject;
                        advResumedMsg = val.message;
                    }
                });
            }
        });
        $(document).ready(function() {
            jQuery("#data-table_processing").show();
            var placeholder = database.collection('settings').doc('placeHolderImage');
            placeholder.get().then(async function(snapshotsimage) {
                var placeholderImageData = snapshotsimage.data();
                placeholderImage = placeholderImageData.image;
            });
            const table = $('#advertisementTable').DataTable({
                pageLength: 10, // Number of rows per page
                processing: false, // Show processing indicator
                serverSide: true, // Enable server-side processing
                responsive: true,
                ajax: function(data, callback, settings) {
                    const start = data.start;
                    const length = data.length;
                    const searchValue = data.search.value.toLowerCase();
                    const orderColumnIndex = data.order[0].column;
                    const orderDirection = data.order[0].dir;
                    @if ($id != '')
                        const orderableColumns = (checkDeletePermission) ? ['', 'title', 'type', 'duration', 'status', 'priority', ''] : ['title', 'type', 'duration', 'status', 'priority']; // Ensure this matches the actual column names
                    @else
                        const orderableColumns = (checkDeletePermission) ? ['', 'title', 'rest_info', 'type', 'duration', 'status', 'priority', ''] : ['title', 'rest_info', 'type', 'duration', 'status', 'priority']; // Ensure this matches the actual column names
                    @endif
                    const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                    if (searchValue.length >= 3 || searchValue.length === 0) {
                        $('#data-table_processing').show();
                    }
                    ref.get().then(async function(querySnapshot) {
                        if (querySnapshot.empty) {
                            $('.advertisement_count').text(0);

                            $('#data-table_processing').hide(); // Hide loader
                            callback({
                                draw: data.draw,
                                recordsTotal: 0,
                                recordsFiltered: 0,
                                data: [] // No data
                            });
                            return;
                        }
                        let records = [];
                        let filteredRecords = [];
                        await Promise.all(querySnapshot.docs.map(async (doc) => {
                            let childData = doc.data();
                            childData.id = doc.id; // Ensure the document ID is included in the data
                            childData.vendorId = childData.vendorId || childData.vendor_id;

                            if (childData.vendorId && id == '') {
                                let vendorDetail = await getRestaurant(childData.vendorId);
                                if (!vendorDetail) return;
                                if (vendorDetail.section_id !== section_id) {
                                    return; 
                                }
                                childData.vendorTitle = vendorDetail.title;
                                childData.vendorImage = vendorDetail.image;
                                childData.vendorEmail = vendorDetail.email;
                            } else {
                                childData.vendorTitle = "-";
                                childData.vendorImage = "-";
                                childData.vendorEmail = "-";
                            }

                            const options = {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            };
                            const endDate = childData.endDate.toDate().toLocaleDateString('en-US', options);
                            const ExpiryDate = childData.endDate;
                            if (ExpiryDate && new Date(ExpiryDate.seconds * 1000) < new Date()) {
                                childData.status = 'Expired';
                            } else {
                                const startDate = childData.startDate;
                                if (startDate && new Date(startDate.seconds * 1000) < new Date() && childData.paymentStatus) {
                                    childData.status = 'Running';
                                } else {
                                    childData.status = 'Approved';

                                }
                            }

                            if (searchValue) {
                                if (
                                    (childData.title && childData.title.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.type && childData.type.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.status && childData.status.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.priority && childData.priority.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.duration && childData.duration.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.vendorTitle && childData.vendorTitle.toString().toLowerCase().includes(searchValue))


                                ) {
                                    filteredRecords.push(childData);
                                }
                            } else {
                                filteredRecords.push(childData);
                            }
                        }));
                        filteredRecords.sort((a, b) => {
                            let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase() : '';
                            let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase() : '';

                            if (orderByField === "rest_info") {
                                aValue = a.vendorTitle ? a.vendorTitle.toLowerCase() : '';
                                bValue = b.vendorTitle ? b.vendorTitle.toLowerCase() : '';
                            }
                            if (orderDirection === 'asc') {
                                return (aValue > bValue) ? 1 : -1;
                            } else {
                                return (aValue < bValue) ? 1 : -1;
                            }
                        });
                        const totalRecords = filteredRecords.length;
                        $('.advertisement_count').text(totalRecords);
                        const paginatedRecords = filteredRecords.slice(start, start + length);
                        const formattedRecords = await Promise.all(paginatedRecords.map(async (
                            childData) => {
                            return await buildHTML(childData);
                        }));
                         $(function () {
                                $('[data-toggle="tooltip"]').tooltip();
                            });
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: totalRecords, // Total number of records in Firestore
                            recordsFiltered: totalRecords, // Number of records after filtering (if any)
                            data: formattedRecords // The actual data to display in the table
                        });
                    }).catch(function(error) {
                        console.error("Error fetching data from Firestore:", error);
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: [] // No data due to error
                        });
                    });
                },
                order: (checkDeletePermission) ? [1, 'asc'] : [0, 'asc'],
                columnDefs: [{
                    orderable: false,
                    targets: (id != '') ? (checkDeletePermission) ? [0, 6] : [5] : (checkDeletePermission) ? [0, 7] : [6]
                }, ],
                "language": datatableLang,
            });
            table.columns.adjust().draw();

            function debounce(func, wait) {
                let timeout;
                const context = this;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), wait);
                };
            }
            $('#search-input').on('input', debounce(function() {
                const searchValue = $(this).val();
                if (searchValue.length >= 3) {
                    $('#data-table_processing').show();
                    table.search(searchValue).draw();
                } else if (searchValue.length === 0) {
                    $('#data-table_processing').show();
                    table.search('').draw();
                }
            }, 300));
        });

        async function buildHTML(val) {
            var html = [];
            var id = val.id;
            var route1 = '{{ route('advertisements.edit', ':id') }}';
            route1 = route1.replace(':id', id);

            var advertisementsView = '{{ route('advertisements.view', ':id') }}';
            advertisementsView = advertisementsView.replace(':id', id);

            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const startDate = val.startDate.toDate().toLocaleDateString('en-US', options);
            const endDate = val.endDate.toDate().toLocaleDateString('en-US', options);
            if (checkDeletePermission) {
                html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' +
                    id + '"><label class="col-3 control-label"\n' +
                    'for="is_open_' + id + '" ></label></td>');
            }

            html.push('<td><a href="' + advertisementsView + '" data-toggle="tooltip" data-bs-original-title="' + val.title + '">' + (val.title.length > 6 ? val.title.substring(0, 8) + '...' : val.title) + '</a></td>');
            if ("{{ $id }}" == '') {
                if (val.vendorImage != '') {
                    html.push(`<td><img src="${val.vendorImage}" style="width:50px; height:50px; border-radius:50%;" onerror="this.onerror=null;this.src=\''+placeholderImage+'\'"><span>${val.vendorTitle} <br> ${val.vendorEmail}</td></span>`);
                } else {
                    html.push(`<td><img src="${placeholderImage}" style="width:50px; height:50px; border-radius:50%;" onerror="this.onerror=null;this.src=\''+placeholderImage+'\'"><span>${val.vendorTitle} <br> ${val.vendorEmail}</td></span>`);

                }
            }

            if (val.type === 'restaurant_promotion') {
                html.push('<td>{{ trans('lang.restaurant_promotion') }}</td>');
            } else {
                html.push('<td>{{ trans('lang.video_promotion') }}</td>');
            }

            html.push('<td>' + startDate + '-'+'<br>' + endDate + '</td>');
            if (val.hasOwnProperty('isPaused') && val.isPaused) {
                html.push('<td><span class="badge badge-info py-2 px-3" >{{ trans('lang.paused') }}</span></td>');
            } else {
                if (val.status == "Running") {
                    html.push('<td><span class="badge badge-info py-2 px-3" >{{ trans('lang.running') }}</span></td>');
                } else if (val.status == "Expired") {
                    html.push('<td><span class="badge badge-danger py-2 px-3" >{{ trans('lang.expired') }}</span></td>')
                } else if (val.status == "Pending") {
                    html.push('<td><span class="badge badge-primary py-2 px-3" >{{ trans('lang.pending') }}</span></td>')
                } else if (val.status == "Canceled") {
                    html.push('<td><span class="badge badge-danger py-2 px-3" >{{ trans('lang.canceled') }}</span></td>')
                } else {
                    html.push('<td><span class="badge badge-success py-2 px-3" >{{ trans('lang.approved') }}</span></td>')
                }
            }
            if (val.hasOwnProperty('priority') && val.priority != null && val.priority != '') {
                html.push('<td>' + val.priority + '</td>');
            } else {
                html.push('<td>N/A</td>');
            }


           var action = '';
           var baseChatRoute = "{{ url('advertisement/chat') }}";
            action += ` <span class="action-btn"><a href="${baseChatRoute}/${val.id}"><i class="fa fa-commenting" title="Chat"></i></a>`
            action = action + '<?php if(in_array('advertisements.view', json_decode(@session('user_permissions'),true))){ ?><a href="' + advertisementsView + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view') }}"><i class="mdi mdi-eye"></i><?php } ?></a><?php if(in_array('advertisements.edit', json_decode(@session('user_permissions'),true))){ ?><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a><?php } ?>';
            action = action + '<?php if(in_array('advertisements.delete', json_decode(@session('user_permissions'),true))){ ?><a id="' + val.id +
                '" class="do_not_delete" name="advertisements-delete" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a><?php } ?>';
            action = action + '<a id="' + val.id +
                '"  name="advertisements-copy" href="javascript:void(0)"><i class="fa fa-copy"></i></a>';
            if (val.status == "Running") {
                if (val.hasOwnProperty('isPaused') && val.isPaused) {
                    var actionClass = "ri-play-circle-line";
                    tooltipTxt = '{{trans('lang.play')}}';
                } else {
                    var actionClass = "ri-pause-circle-line";
                    tooltipTxt = '{{trans('lang.pause')}}';
                }

                action = action + '<a href="javascript:void(0)" name="pause-btn" id="' + val.id + '" data-status="' + val.isPaused + '"  data-bs-toggle="tooltip" data-bs-placement="top" title="' + tooltipTxt + '"><i class="' + actionClass + '"></i></a>'
            }
            action = action + '</span>';
            html.push(action);
            return html;
        }

        async function getRestaurant(vendorid) {

            if (!vendorid) {
                return {
                    title: "-",
                    image: "",
                    email: "-",
                    section_id: "",
                }; 
            }
            const vendorRef = database.collection('vendors').where('id', '==', vendorid);
            const vendorSnapshot = await vendorRef.get();

            const vendor_userRef = database.collection('users').where('vendorID', '==', vendorid).where('role', '==', 'vendor');
            const vendor_userSnapshot = await vendor_userRef.get();


            if (vendorSnapshot.empty) {
                return {
                    title: "-"
                }; 
            }

            if (vendor_userSnapshot.empty) {
                return {
                    image: "",
                    email: "-"
                };
            }

            let vendorData = {};
            let vendor_userData = {};
            vendorSnapshot.forEach((doc) => {
                vendorData = doc.data();
            });

            vendor_userSnapshot.forEach((doc) => {
                vendor_userData = doc.data();

            });

            return {
                title: vendorData.title || "-",
                image: vendor_userData.profilePictureURL || "",
                email: vendor_userData.email || "-",
                section_id: vendor_userData.sectionId || "-",
            };
        }
        $(document).on("click", "a[name='advertisements-delete']", async function(e) {
            var id = this.id;
            jQuery("#data-table_processing").show();
            await database.collection('advertisements').doc(id).get().then(async function(snapshots) {
                var data = snapshots.data();

                if (data.type == 'video_promotion') {
                    var checkVideoSize = await database.collection('advertisements').where('video', '==', data.video).get();
                    var videoSize = checkVideoSize.size;
                    if (videoSize > 1) {
                        await deleteDocumentWithImage('advertisements', id);
                    } else {
                        await deleteDocumentWithImage('advertisements', id, ['video']);
                    }

                } else {
                    var checkprofileSize = await database.collection('advertisements').where('profileImage', '==', data.profileImage).get();
                    var profileSize = checkprofileSize.size;
                    var checkCoverSize = await database.collection('advertisements').where('coverImage', '==', data.coverImage).get();
                    var coverSize = checkCoverSize.size;
                    let fieldsToDelete = [];

                    if (profileSize === 1) {
                        fieldsToDelete.push('profileImage');
                    }

                    if (coverSize === 1) {
                        fieldsToDelete.push('coverImage');
                    }

                    if (fieldsToDelete.length > 0) {

                        await deleteDocumentWithImage('advertisements', id, fieldsToDelete);
                    } else {
                        await deleteDocumentWithImage('advertisements', id);
                    }
                }
            })
            window.location.href = '{{ route('advertisements') }}';
        });

        $(document).on("click", "a[name='pause-btn']", async function(e) {
            var id = this.id;
            var status = $(this).attr('data-status');
            if (status == null || status == "null") {
                $('#pauseAdvId').val(id);
                $('#advPauseModal').modal('show');
            } else if (status.toString() == "false") {
                $('#pauseAdvId').val(id);
                $('#advPauseModal').modal('show');
            } else {
                $('#resumeAdvId').val(id);
                $('#advResumeModal').modal('show');

            }

        });
        $('.confirm-resume-btn').click(async function() {
            var id = $('#resumeAdvId').val();
            await database.collection('advertisements').doc(id).update({
                'isPaused': false
            }).then(async function(snapshot) {
                await sendNotification('resume', id);
                window.location.href = '{{ route('advertisements') }}';
            })
        })
        $('.confirm-pause-btn').click(async function() {

            var id = $('#pauseAdvId').val();
            var reason = $('#pause_reason').val();
            if (reason == '') {
                $('#add_pause_error').html("{{ trans('lang.add_pause_note') }}").show();
                return false;
            }
            await database.collection('advertisements').doc(id).update({
                'isPaused': true,
                'pauseNote': reason
            }).then(async function(snapshot) {
                await sendNotification('pause', id);
                window.location.href = '{{ route('advertisements') }}';
            })

        })

        $(document).on('click', 'a[name="advertisements-copy"]', async function() {
            let id = this.id;
            await database.collection('advertisements').doc(id).get().then(async function(snapshot) {
                var advData = snapshot.data();
                localStorage.setItem('copiedAdvertisement', JSON.stringify(advData));
                window.location.href = "{{ route('advertisements.create') }}"
            })
        });
        $("#is_active").click(function() {
            $("#advertisementTable .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function() {
            if ($('#advertisementTable .is_open:checked').length) {
                if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                    jQuery("#data-table_processing").show();
                    $('#advertisementTable .is_open:checked').each(async function() {
                        var dataId = $(this).attr('dataId');
                        await database.collection('advertisements').doc(dataId).get().then(async function(snapshots) {
                            var data = snapshots.data();
                            if (data.type == 'video_promotion') {
                                var checkVideoSize = await database.collection('advertisements').where('video', '==', data.video).get();
                                var videoSize = checkVideoSize.size;
                                if (videoSize > 1) {
                                    await deleteDocumentWithImage('advertisements', dataId);
                                } else {
                                    await deleteDocumentWithImage('advertisements', dataId, ['video']);
                                }

                            } else {
                                var checkprofileSize = await database.collection('advertisements').where('profileImage', '==', data.profileImage).get();
                                var profileSize = checkprofileSize.size;
                                var checkCoverSize = await database.collection('advertisements').where('coverImage', '==', data.coverImage).get();
                                var coverSize = checkCoverSize.size;
                                let fieldsToDelete = [];

                                if (profileSize === 1) {
                                    fieldsToDelete.push('profileImage');
                                }

                                if (coverSize === 1) {
                                    fieldsToDelete.push('coverImage');
                                }

                                if (fieldsToDelete.length > 0) {

                                    await deleteDocumentWithImage('advertisements', dataId, fieldsToDelete);
                                } else {
                                    await deleteDocumentWithImage('advertisements', dataId);
                                }
                            }
                        })

                        window.location.reload();
                    });
                }
            } else {
                alert("{{ trans('lang.select_delete_alert') }}");
            }
        });
        async function sendNotification(status, advId) {
            var vendorFcm = '';
            await database.collection('advertisements').doc(advId).get().then(async function(snapshot) {
                var data = snapshot.data();
                await database.collection('users').where('vendorID', '==', data.vendorId).where('role', '==', 'vendor').get().then(async function(snapshot) {
                    if (snapshot.docs.length > 0) {
                        var data = snapshot.docs[0].data();
                        vendorFcm = data.fcmToken;
                    }
                })
            })
            if (status == 'resume') {
                var title = advResumedSub;
                var message = advResumedMsg
            } else {
                var title = advPausedSub;
                var message = advPausedMsg;
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
        async function getVendorData(vendorId) {
            if (!vendorId) return null;
            let vendorSnapshot = await database.collection('vendors')
                .where('id', '==', vendorId)
                .get();
            if (!vendorSnapshot.empty) {
                return vendorSnapshot.docs[0].data();
            }
            return null;
        }

    </script>
@endsection
