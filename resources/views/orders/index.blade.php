@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <div class="d-flex top-title-section justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/order.png') }}"></span>
                        <h3 class="mb-0">{{ trans('lang.order_plural') }} <span class="orderTitle"></span></h3>
                        <span class="counter ml-3 total_count"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.order_table') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-self-center">
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
                                    <select class="form-control status_selector filteredRecords">
                                        <option value="" selected>{{ trans('lang.status') }}</option>
                                        <option value="Order Placed">{{ trans('lang.order_placed') }}</option>
                                        <option value="Order Accepted">{{ trans('lang.order_accepted') }}</option>
                                        <option value="Order Rejected">{{ trans('lang.order_rejected') }}</option>
                                        <option value="Driver Pending">{{ trans('lang.driver_pending') }}</option>
                                        <option value="Driver Rejected">{{ trans('lang.driver_rejected') }}</option>
                                        <option value="Order Shipped">{{ trans('lang.order_shipped') }}</option>
                                        <option value="In Transit">{{ trans('lang.in_transit') }}</option>
                                        <option value="Order Completed">{{ trans('lang.order_completed') }}</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <select class="form-control order_type_selector">
                                        <option value="" selected>{{ trans('lang.order_type') }}</option>
                                        <option value="takeaway">{{ trans('lang.order_takeaway') }}</option>
                                        <option value="delivery">{{ trans('lang.delivery') }}</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <div id="daterange"><i class="fa fa-calendar"></i>&nbsp;
                                        <span></span>&nbsp; <i class="fa fa-caret-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-list">
                <div class="row">
                    <div class="col-12">
                        <?php if (isset($_GET['driverId'])) { ?>
                        <div class="menu-tab">
                            <ul>
                                <li>
                                    <a href="{{ route('drivers.view', $_GET['driverId']) }}"><i class="ri-list-indefinite"></i>{{ trans('lang.tab_basic') }}</a>
                                </li>
                                <li class="vehicle_tab" style="display:none">
                                    <a href="{{ route('drivers.vehicle', $_GET['driverId']) }}"><i class="ri-car-line"></i>{{ trans('lang.vehicle') }}</a>
                                </li>
                                <li class="active">
                                    <a href="{{ route('orders', 'driverId=' . $_GET['driverId']) }}"><i class="ri-shopping-bag-line"></i> {{ trans('lang.order_plural') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('driver.payouts', $_GET['driverId']) }}"><i class="ri-bank-card-line"></i>{{ trans('lang.tab_payouts') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('payoutRequests.drivers.view', $_GET['driverId']) }}" class="vendor_payout"><i class="ri-refund-line"></i>{{ trans('lang.tab_payout_request') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('users.walletstransaction', $_GET['driverId']) }}" class="wallet_transaction"><i class="ri-wallet-line"></i>{{ trans('lang.wallet_transaction') }}</a>
                                </li>
                            </ul>
                        </div>
                        <?php } ?>
                        <?php if (isset($_GET['userId'])) { ?>
                        <div class="menu-tab">
                            <ul>
                                <li><a href="{{ route('users.view', $_GET['userId']) }}"><i class="ri-list-indefinite"></i>{{ trans('lang.tab_basic') }}</a>
                                </li>
                                <li class="active"><a href="{{ route('orders', 'userId=' . $_GET['userId']) }}"><i class="ri-shopping-bag-line"></i>{{ trans('lang.tab_orders') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('users.walletstransaction', $_GET['userId']) }}"><i class="ri-wallet-line"></i>{{ trans('lang.wallet_transaction') }}</a>
                                </li>
                            </ul>
                        </div>
                        <?php } ?>
                        <div class="menu-tab vendorMenuTab d-none">
                            <ul>
                                <li>
                                    <a href="{{ route('stores.view', $id) }}"><i class="ri-list-indefinite"></i>{{ trans('lang.tab_basic') }}</a>
                                </li>
                                <li>
                                    <a href="{{ route('vendors.items', $id) }}"><i class="ri-shopping-basket-fill"></i>{{ trans('lang.tab_items') }}</a>
                                </li>
                                <li class="active">
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
                                <li>
                                    <a class="wallet_transaction_vendor"><i class="ri-wallet-line"></i>{{ trans('lang.wallet_transaction') }}</a>
                                </li>
                                <li class="dine_in_future" style="display:none;">
                                    <a href="{{ route('vendors.booktable', $id) }}"><i class="ri-restaurant-line"></i>{{ trans('lang.dine_in_booking_history') }}</a>
                                </li>
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
                                @if($service_type != 'ecommerce-service')
                                    <li class="">
                                        <a href="{{ route('restaurants.deliveryman', $id) }}"><i class="ri-riding-fill"></i>{{ trans('lang.deliveryman') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="card card-box-with-icon bg--1">
                                                    <div class="card-body d-flex justify-content-between align-items-center">
                                                        <div class="card-box-with-content">
                                                            <h4 class="text-dark-2 mb-1 h4 order_count" id="order_count"></h4>
                                                            <p class="mb-0 small text-dark-2">{{ trans('lang.dashboard_total_orders') }}</p>
                                                        </div>
                                                        <span class="box-icon ab"><img src="{{ asset('images/total_orders.png') }}"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card card-box-with-icon bg--5">
                                                    <div class="card-body d-flex justify-content-between align-items-center">
                                                        <div class="card-box-with-content">
                                                            <h4 class="text-dark-2 mb-1 h4 placed_count" id="placed_count"></h4>
                                                            <p class="mb-0 small text-dark-2">{{ trans('lang.dashboard_order_placed') }}</p>
                                                        </div>
                                                        <span class="box-icon ab"><img src="{{ asset('images/order_placed.png') }}"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card card-box-with-icon bg--6">
                                                    <div class="card-body d-flex justify-content-between align-items-center">
                                                        <div class="card-box-with-content">
                                                            <h4 class="text-dark-2 mb-1 h4 accepted_count" id="accepted_count"></h4>
                                                            <p class="mb-0 small text-dark-2">{{ trans('lang.dashboard_order_accepted') }}</p>
                                                        </div>
                                                        <span class="box-icon ab"><img src="{{ asset('images/order_accepted.png') }}"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card card-box-with-icon bg--8">
                                                    <div class="card-body d-flex justify-content-between align-items-center">
                                                        <div class="card-box-with-content">
                                                            <h4 class="text-dark-2 mb-1 h4 order_completed" id="order_completed"></h4>
                                                            <p class="mb-0 small text-dark-2">{{ trans('lang.dashboard_order_completed') }}</p>
                                                        </div>
                                                        <span class="box-icon ab"><img src="{{ asset('images/order_completed.png') }}"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.order_table') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.order_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="orderTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <?php if (in_array('orders.delete', json_decode(@session('user_permissions'), true))) { ?>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label></th>
                                                <?php } ?>
                                                <th>{{ trans('lang.order_id') }}</th>
                                                
                                                @if($id == '')
                                                    <th>{{ trans('lang.vendor') }}</th>
                                                @endif
                                                
                                                @if($service_type == 'ecommerce-service')        
                                                    @if (isset($_GET['userId']))
                                                    @elseif (isset($_GET['driverId']))
                                                        <th>{{ trans('lang.order_user_id') }}</th>
                                                    @else
                                                        <th>{{ trans('lang.order_user_id') }}</th>
                                                    @endif
                                                @else
                                                    @if (isset($_GET['userId']))
                                                        <th>{{ trans('lang.driver') }}</th>
                                                    @elseif (isset($_GET['driverId']))
                                                        <th>{{ trans('lang.order_user_id') }}</th>
                                                    @else
                                                        <th>{{ trans('lang.driver') }}</th>
                                                        <th>{{ trans('lang.order_user_id') }}</th>
                                                    @endif
                                                @endif
                                                
                                                <th>{{ trans('lang.date') }}</th>
                                                <th>{{ trans('lang.vendors_payout_amount') }}</th>
                                                <th>{{ trans('lang.order_type') }}</th>
                                                <th>{{ trans('lang.order_order_status_id') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_list1">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Driver Assignment Modal -->
    <div class="modal fade" id="assignDriverModal" tabindex="-1" aria-labelledby="assignDriverModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('lang.assign_driver') }}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="currentOrderId">
                    <div class="mb-3">
                        <label for="driver-select" class="form-label">{{ trans('lang.select_driver') }}</label>
                        <select class="form-control" id="driver-select">
                        </select>
                    </div>
                    <div id="driver-info" class="mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="assign-driver-btn">{{ trans('lang.assign') }}</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('lang.canceled') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    <script type="text/javascript">

        var section_id = getCookie('section_id') || null;
        var service_type = getCookie('service_type') || null;    
        
        var user_permissions = '<?php echo @session('user_permissions'); ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkDeletePermission = false;
        if ($.inArray('orders.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }

        var database = firebase.firestore();
        var refData = regionScoped(database.collection('vendor_orders').where('isPosOrder','==',false));
        if (section_id) {
            refData = refData.where('section_id', '==', section_id);
        }

        var append_list = '';
        var currentCurrency = '';
        var currencyAtRight = false;
        var refCurrency = regionCurrencyRef();
        
        var decimal_degits = 0;
        refCurrency.get().then(async function(snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        });
        
        var user_permissions = '<?php echo @session('user_permissions'); ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkPrintPermission = false;
        if ($.inArray('orders.print', user_permissions) >= 0) {
            checkPrintPermission = true;
        }

        var order_status = jQuery('#order_status').val();
        var search = jQuery("#search").val();
        
        var vendorID = "{{ $id }}";
        var userID = "{{ request()->get('userId', '') }}";
        var driverID = "{{ request()->get('driverId', '') }}";
        
        var ref = '';
        $(document.body).on('change', '#order_status', function() {
            order_status = jQuery(this).val();
        });
        $(document.body).on('keyup', '#search', function() {
            search = jQuery(this).val();
        });

        if (userID) {
            
            const getUserName = getUserNameFunction(userID);
            ref = refData.orderBy('createdAt', 'desc').where('authorID', '==', userID);

        } else if (driverID) {

            const getUserName = getUserNameFunction(driverID);
            var wallet_route = "{{ route('users.walletstransaction', 'id') }}";
            $(".wallet_transaction").attr("href", wallet_route.replace('id', 'driverID=' + driverID));
            
            if(service_type !== 'delivery-service' && service_type !== 'parcel_delivery'){
                $('.vehicle_tab').show();
            }else{
                $('.vehicle_tab').hide();
            }

            ref = refData.orderBy('createdAt', 'desc').where('driverID', '==', driverID);

        } else if (vendorID != '') {

            $('.vendorMenuTab').removeClass('d-none');
            const getStoreName = getStoreNameFunction(vendorID);
            
            ref = refData.orderBy('createdAt', 'desc').where('vendorID', '==', vendorID);

        } else {
            
            ref = refData.orderBy('createdAt', 'desc');
        }

        const sectionsRef = firebase.firestore().collection('sections');
        $('.status_selector').select2({
            placeholder: '{{ trans('lang.status') }}',
            minimumResultsForSearch: Infinity,
            allowClear: true
        });
        $('.order_type_selector').select2({
            placeholder: '{{ trans('lang.order_type') }}',
            minimumResultsForSearch: Infinity,
            allowClear: true
        });
        $('.allModules').select2({
            placeholder: "{{ trans('lang.select') }} {{ trans('lang.section_plural') }}",
            minimumResultsForSearch: Infinity,
            allowClear: true
        });
        $('.filteredRecords').on("select2:unselecting", function(e) {
            var self = $(this);
            setTimeout(function() {
                self.select2('close');
            }, 0);
        });

        function setDate() {
            $('#daterange span').html('{{ trans('lang.select_range') }}');
            $('#daterange').daterangepicker({
                autoUpdateInput: false,
            }, function(start, end) {
                $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                $('.filteredRecords').trigger('change');
            });
            $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                $('#daterange span').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
                $('.filteredRecords').trigger('change');
            });
            $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                $('#daterange span').html('{{ trans('lang.select_range') }}');
                $('.filteredRecords').trigger('change');
            });
        }

        setDate();
        
        var initialRef = regionScoped(database.collection('vendor_orders'));
        
        $('select').change(async function() {

            var status = $('.status_selector').val();
            var orderType = $('.order_type_selector').val();
            var daterangepicker = $('#daterange').data('daterangepicker');

            var refData = initialRef;  
            if (status) {
                refData = refData.where('status', '==', status);
            }
            if (section_id) {
                refData = refData.where('section_id', '==', section_id);
            }

            if (orderType) {
                refData = (orderType == 'takeaway')
                    ? refData.where('takeAway', '==', true)
                    : refData.where('takeAway', '==', false);
            }

            if ($('#daterange span').html() != '{{ trans('lang.select_range') }}' && daterangepicker) {

                var from = moment(daterangepicker.startDate).toDate();
                var to = moment(daterangepicker.endDate).add(1, 'day').toDate(); // FIX

                var fromDate = firebase.firestore.Timestamp.fromDate(from);
                var toDate = firebase.firestore.Timestamp.fromDate(to);

                refData = refData.where('createdAt', '>=', fromDate);
                refData = refData.where('createdAt', '<', toDate); // FIX
            }

            ref = refData;
            $('#orderTable').DataTable().ajax.reload(null, false);
        });


        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });

        $(document).ready(function() {

            ref.get().then((snapshot) => {
                jQuery("#order_count").empty();
                jQuery("#order_count").text(snapshot.docs.length);
            });

            var refPlacedOrder = ref.where('status', 'in', ["Order Placed"]);
            refPlacedOrder.get().then((snapshot) => {
                jQuery("#placed_count").empty();
                jQuery("#placed_count").text(snapshot.docs.length);
            });
            
            var refAcceptedOrder = ref.where('status', 'in', ["Order Accepted"]);
            refAcceptedOrder.get().then((snapshot) => {
                jQuery("#accepted_count").empty();
                jQuery("#accepted_count").text(snapshot.docs.length);
            });
            
            var refCompletedOrder = ref.where('status', 'in', ["Order Completed"]);
            refCompletedOrder.get().then((snapshot) => {
                jQuery("#order_completed").empty();
                jQuery("#order_completed").text(snapshot.docs.length);
            });

            $(document.body).on('click', '.redirecttopage', function() {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });
          
            $(document.body).on('change', '#selected_search', function() {
                if (jQuery(this).val() == 'status') {
                    jQuery('#order_status').show();
                } else {
                    jQuery('#order_status').hide();
                }
            });

            jQuery("#data-table_processing").show();

            append_list = document.getElementById('append_list1');
            append_list.innerHTML = '';
            
            ref.get().then(async function(snapshots) {

                jQuery("#data-table_processing").hide();
                
                $(document).on('click', '.dt-button-collection .dt-button', function() {
                    $('.dt-button-collection').hide();
                    $('.dt-button-background').hide();
                });
                
                $(document).on('click', function(event) {
                    if (!$(event.target).closest('.dt-button-collection, .dt-buttons').length) {
                        $('.dt-button-collection').hide();
                        $('.dt-button-background').hide();
                    }
                });
                
                var columns = [];
                if (vendorID != '') {
                    columns = [
                        {
                            key: 'id',
                            header: "{{ trans('lang.order_id') }}"
                        },
                        {
                            key: 'driverName',
                            header: "{{ trans('lang.driver_plural') }}"
                        },
                        {
                            key: 'clientName',
                            header: "{{ trans('lang.order_user_id') }}"
                        },
                        {
                            key: 'createdAt',
                            header: "{{ trans('lang.date') }}"
                        },
                        {
                            key: 'amount',
                            header: "{{ trans('lang.vendors_payout_amount') }}"
                        },
                        {
                            key: 'orderType',
                            header: "{{ trans('lang.order_type') }}"
                        },
                        {
                            key: 'status',
                            header: "{{ trans('lang.order_order_status_id') }}"
                        },
                    ];
                } else if (driverID) {
                    columns = [
                        {
                            key: 'id',
                            header: "{{ trans('lang.order_id') }}"
                        },
                        {
                            key: 'storeName',
                            header: "{{ trans('lang.vendor') }}"
                        },
                        {
                            key: 'clientName',
                            header: "{{ trans('lang.order_user_id') }}"
                        },
                        {
                            key: 'createdAt',
                            header: "{{ trans('lang.date') }}"
                        },
                        {
                            key: 'amount',
                            header: "{{ trans('lang.vendors_payout_amount') }}"
                        },
                        {
                            key: 'orderType',
                            header: "{{ trans('lang.order_type') }}"
                        },
                        {
                            key: 'status',
                            header: "{{ trans('lang.order_order_status_id') }}"
                        },
                    ];
                } else if (userID) {
                    columns = [
                        {
                            key: 'id',
                            header: "{{ trans('lang.order_id') }}"
                        },
                        {
                            key: 'storeName',
                            header: "{{ trans('lang.vendor') }}"
                        },
                        {
                            key: 'driverName',
                            header: "{{ trans('lang.driver_plural') }}"
                        },
                        {
                            key: 'createdAt',
                            header: "{{ trans('lang.date') }}"
                        },
                        {
                            key: 'amount',
                            header: "{{ trans('lang.vendors_payout_amount') }}"
                        },
                        {
                            key: 'orderType',
                            header: "{{ trans('lang.order_type') }}"
                        },
                        {
                            key: 'status',
                            header: "{{ trans('lang.order_order_status_id') }}"
                        },
                    ];
                } else {
                    columns = [
                        {
                            key: 'id',
                            header: "{{ trans('lang.order_id') }}"
                        },
                        {
                            key: 'storeName',
                            header: "{{ trans('lang.vendor') }}"
                        },
                        {
                            key: 'driverName',
                            header: "{{ trans('lang.driver_plural') }}"
                        },
                        {
                            key: 'clientName',
                            header: "{{ trans('lang.order_user_id') }}"
                        },
                        {
                            key: 'createdAt',
                            header: "{{ trans('lang.date') }}"
                        },
                        {
                            key: 'amount',
                            header: "{{ trans('lang.vendors_payout_amount') }}"
                        },
                        {
                            key: 'orderType',
                            header: "{{ trans('lang.order_type') }}"
                        },
                        {
                            key: 'status',
                            header: "{{ trans('lang.order_order_status_id') }}"
                        },
                    ];
                }
                var fieldConfig = {
                    columns: columns, // Assign the dynamically generated array here
                    fileName: "{{ trans('lang.order_table') }}",
                };

                var dateIndex = 0;
                if (service_type === "ecommerce-service") {
                    if (checkDeletePermission) {
                        if (vendorID !== "") dateIndex = 3;
                        else if (vendorID !== "" || driverID || userID) dateIndex = 3;
                        else dateIndex = 4;
                    } else {
                        if (vendorID !== "") dateIndex = 2;
                        else if (driverID || userID) dateIndex = 2;
                        else dateIndex = 3;
                    }
                } else {
                    if (checkDeletePermission) {
                        if (vendorID !== "") dateIndex = 4;
                        else if (driverID || userID) dateIndex = 4;
                        else dateIndex = 5;
                    } else {
                        if (vendorID !== "") dateIndex = 3;
                        else if (driverID || userID) dateIndex = 3;
                        else dateIndex = 4;
                    }
                }
                
                var nonOrderableTargets = [];
                if (service_type === "ecommerce-service") {
                    if (vendorID !== "") {
                        nonOrderableTargets = checkDeletePermission ? [0, 6, 7] : [5, 6];
                    } else if (driverID || userID) {
                        nonOrderableTargets = checkDeletePermission ? [0, 6, 7] : [5, 6];
                    } else {
                        nonOrderableTargets = checkDeletePermission ? [0, 7, 8] : [6, 7];
                    }
                } else {
                    if (vendorID !== "") {
                        nonOrderableTargets = checkDeletePermission ? [0, 7, 8] : [6, 7];
                    } else if (driverID || userID) {
                        nonOrderableTargets = checkDeletePermission ? [0, 7, 8] : [6, 7];
                    } else {
                        nonOrderableTargets = checkDeletePermission ? [0, 8, 9] : [7, 8];
                    }
                }

                const table = $('#orderTable').DataTable({
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

                        var orderableColumns = [];
                        if(service_type == "ecommerce-service"){
                            if (vendorID != '') {
                                orderableColumns = (checkDeletePermission) ? ['', 'id', 'clientName', 'createdAt', 'amount', 'orderType', 'status', ''] : ['id', 'clientName', 'createdAt', 'amount', 'orderType', 'status', '']; 
                            } else if (driverID) {
                                orderableColumns = (checkDeletePermission) ? ['', 'id', 'storeName', 'clientName', 'createdAt', 'amount', 'orderType', 'status', ''] : ['id', 'storeName',  'clientName', 'createdAt', 'amount', 'orderType', 'status', '']; 
                            } else if (userID) {
                                orderableColumns = (checkDeletePermission) ? ['', 'id', 'storeName', 'createdAt', 'amount', 'orderType', 'status', ''] : ['id', 'storeName', 'createdAt', 'amount', 'orderType', 'status', ''];
                            } else {
                                orderableColumns = (checkDeletePermission) ? ['', 'id', 'storeName', 'clientName', 'createdAt', 'amount', 'orderType', 'status', ''] : ['id', 'storeName', 'clientName', 'createdAt', 'amount', 'orderType', 'status', ''];
                            }
                        }else{
                            if (vendorID != '') {
                                orderableColumns = (checkDeletePermission) ? ['', 'id', 'driverName', 'clientName', 'createdAt', 'amount', 'orderType', 'status', ''] : ['id', 'driverName', 'clientName', 'createdAt', 'amount', 'orderType', 'status', '']; 
                            } else if (driverID) {
                                orderableColumns = (checkDeletePermission) ? ['', 'id', 'storeName', 'clientName', 'createdAt', 'amount', 'orderType', 'status', ''] : ['id', 'storeName',  'clientName', 'createdAt', 'amount', 'orderType', 'status', '']; 
                            } else if (userID) {
                                orderableColumns = (checkDeletePermission) ? ['', 'id', 'storeName', 'driverName', 'createdAt', 'amount', 'orderType', 'status', ''] : ['id', 'storeName', 'driverName', 'createdAt', 'amount', 'orderType', 'status', ''];
                            } else {
                                orderableColumns = (checkDeletePermission) ? ['', 'id', 'storeName',  'driverName', 'clientName', 'createdAt', 'amount', 'orderType', 'status', ''] : ['id', 'storeName', 'driverName', 'clientName', 'createdAt', 'amount', 'orderType', 'status', ''];
                            }
                        }
                        
                        const orderByField = orderableColumns[orderColumnIndex]; 
                        if (searchValue.length >= 3 || searchValue.length === 0) {
                            $('#data-table_processing').show();
                        }

                        ref.get().then(async function(querySnapshot) {

                            if (querySnapshot.empty) {
                                $('.total_count').text(0);
                                console.error("No data found in Firestore.");
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
                            let sectionNames = {};
                            // Fetch section names
                            const sectionDocs = await sectionsRef.get();
                            sectionDocs.forEach(doc => {
                                sectionNames[doc.id] = doc.data().name;
                            });
                            querySnapshot.docs.map(async doc => {
                                let childData = doc.data();
                                childData.id = doc.id; // Ensure the document ID is included in the data
                                childData.sectionName = sectionNames[childData.section_id] || 'N/A';
                                var driverName = '';
                                if (childData.hasOwnProperty("driver") && childData.driver != null) {
                                    var driverId = childData.driver.id;
                                    driverName = childData.driver.firstName + ' ' + childData.driver.lastName;
                                    childData.driverName = driverName;
                                }
                                if (childData.hasOwnProperty('vendor') && childData.vendor != null) {
                                    childData.storeName = childData.vendor.title;
                                }
                                if (childData.hasOwnProperty('author') && childData.author != null) {
                                    childData.clientName = childData.author.firstName + ' ' + childData.author.lastName;
                                }
                                if (childData.hasOwnProperty('takeAway') && childData.takeAway) {
                                    childData.orderType = "{{ trans('lang.order_takeaway') }}"
                                } else {
                                    childData.orderType = "{{ trans('lang.order_delivery') }}";
                                }
                                var price = 0;
                                price = buildHTMLProductstotal(childData);
                                childData.amount = price;
                                if (searchValue) {
                                    var date = '';
                                    var time = '';
                                    if (childData.hasOwnProperty("createdAt")) {
                                        try {
                                            date = childData.createdAt.toDate().toDateString();
                                            time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                                        } catch (err) {
                                        }
                                    }
                                    var createdAt = date + '<br> ' + time;
                                    if (
                                        (childData.id && childData.id.toString().includes(searchValue)) ||
                                        (childData.storeName && childData.storeName.toLowerCase().toString().includes(searchValue))/*  ||
                                        (childData.sectionName && childData.sectionName.toString().includes(searchValue)) */ ||
                                        (childData.driverName && childData.driverName.toString().includes(searchValue)) ||
                                        (childData.clientName && childData.clientName.toString().includes(searchValue)) ||
                                        (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1) ||
                                        (childData.orderType && childData.orderType.toLowerCase().toString().includes(searchValue)) ||
                                        (childData.status && childData.status.toLowerCase().toString().includes(searchValue)) ||
                                        (childData.amount && childData.amount.toString().toLowerCase().includes(searchValue))
                                    ) {
                                        filteredRecords.push(childData);
                                    }
                                } else {
                                    filteredRecords.push(childData);
                                }
                            });
                            filteredRecords.sort((a, b) => {
                                let aValue = a[orderByField];
                                let bValue = b[orderByField];
                                if (orderByField === 'createdAt' && a[orderByField] != '' && b[orderByField] != '' && a[orderByField] != null && b[orderByField] != null) {
                                    aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                                    bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                                } else if (orderByField === 'amount') {
                                    aValue = a[orderByField] ? parseFloat(String(a[orderByField]).replace(/[^0-9.]/g, '')) || 0 : 0;
                                    bValue = b[orderByField] ? parseFloat(String(b[orderByField]).replace(/[^0-9.]/g, '')) || 0 : 0;
                                } else {
                                    aValue = a[orderByField] ? a[orderByField].toString().toLowerCase().trim() : '';
                                    bValue = b[orderByField] ? b[orderByField].toString().toLowerCase().trim() : ''
                                }
                                if (orderDirection === 'asc') {
                                    return (aValue > bValue) ? 1 : -1;
                                } else {
                                    return (aValue < bValue) ? 1 : -1;
                                }
                            });
                            const totalRecords = filteredRecords.length;
                            $('.total_count').text(totalRecords);

                            const paginatedRecords = filteredRecords.slice(start, start + length);
                            await Promise.all(paginatedRecords.map(async (childData) => {
                                var getData = await buildHTML(childData);
                                records.push(getData);
                            }));
                            
                            $(function () {
                                $('[data-toggle="tooltip"]').tooltip();
                            });
                            
                            $('#data-table_processing').hide(); // Hide loader

                            callback({
                                draw: data.draw,
                                recordsTotal: totalRecords, // Total number of records in Firestore
                                recordsFiltered: totalRecords, // Number of records after filtering (if any)
                                filteredData: filteredRecords,
                                data: records // The actual data to display in the table
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
                    order: [[dateIndex, 'desc']],
                    columnDefs: [
                        {
                            orderable: false,
                            targets: nonOrderableTargets,
                        },
                        {
                            type: 'date',
                            targets: dateIndex,
                            render: function(data) {
                                return data;
                            }
                        }
                    ],
                   "language": datatableLang,
                    dom: 'lfrtipB',
                    buttons: [
                        {
                            extend: 'collection',
                            text: '<i class="mdi mdi-cloud-download"></i> {{trans("lang.export_as")}}',
                            className: 'btn btn-info',
                            buttons: [
                                {
                                    extend: 'excelHtml5',
                                    text: '{{trans("lang.export_excel")}}',
                                    action: function(e, dt, button, config) {
                                        exportData(dt, 'excel', fieldConfig);
                                    }
                                },
                                {
                                    extend: 'pdfHtml5',
                                    text: '{{trans("lang.export_pdf")}}',
                                    action: function(e, dt, button, config) {
                                        exportData(dt, 'pdf', fieldConfig);
                                    }
                                },
                                {
                                    extend: 'csvHtml5',
                                    text: '{{trans("lang.export_csv")}}',
                                    action: function(e, dt, button, config) {
                                        exportData(dt, 'csv', fieldConfig);
                                    }
                                }
                            ]
                        }
                    ],
                    initComplete: function() {
                        $(".dataTables_filter").append($(".dt-buttons").detach());
                        $('.dataTables_filter input').attr('placeholder', '{{ trans("lang.search_here") }}').attr('autocomplete', 'new-password').val('');
                        $('.dataTables_filter label').contents().filter(function() {
                            return this.nodeType === 3;
                        }).remove();
                    }
                });
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
        });
        async function buildHTML(val) {
            var html = [];
            var id = val.id;
            /* This row's own currency, not the page's.
             *
             * The order's own regionId wins, so an order keeps reading in the currency
             * it was charged in - matching orders/edit and orders/print. Orders from
             * before regions existed fall back to their store's region.
             *
             * These shadow the page-level variables of the same names, so every
             * formatting line below is unchanged. Cached. */
            const rowCurrency = await currencyOfOrder(val);
            const currentCurrency = rowCurrency ? rowCurrency.symbol : '';
            const currencyAtRight = rowCurrency ? Boolean(rowCurrency.symbolAtRight) : false;
            const decimal_degits = (rowCurrency && rowCurrency.decimal_degits !== undefined && rowCurrency.decimal_degits !== null)
                ? rowCurrency.decimal_degits : 2;
            
            var user_id = val.authorID;
            var route1 = '{{ route('orders.edit', ':id') }}';
            route1 = route1.replace(':id', id);
            var printRoute = '{{ route('vendors.orderprint', ':id') }}';
            printRoute = printRoute.replace(':id', id);
            
            if (val.vendorID != '') {
                route1 = route1 + '?eid={{ $id }}';
                printRoute = printRoute + '?eid={{ $id }}';
            }

            var route_view = '{{ route('stores.view', ':id') }}';
            route_view = route_view.replace(':id', val.vendorID);
            var customer_view = '{{ route('users.view', ':id') }}';
            customer_view = customer_view.replace(':id', user_id);
            if (checkDeletePermission) {
                html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                    'for="is_open_' + id + '" ></label></td>');
            }
            
            html.push('<a href="' + route1 + '" class="redirecttopage"  data-toggle="tooltip" data-bs-original-title="' + val.id + '">' + (val.id.length > 8 ? val.id.substring(0, 8) + '...' : val.id) + '</a>');

            if(service_type == "ecommerce-service"){

                if (userID) {

                    if (val.hasOwnProperty('vendor') && val.vendor.title != undefined) {
                        html.push('<a  data-url="' + route_view + '" href="' + route_view + '" class="redirecttopage">' + val.storeName + '</a>');
                    }        
               } else if (driverID) {
                    
                    if (val.hasOwnProperty('vendor') && val.vendor.title != undefined) {
                        html.push('<a  data-url="' + route_view + '" href="' + route_view + '" class="redirecttopage">' + val.storeName + '</a>');
                    }
                    
                    if (val.hasOwnProperty("author") && val.author != null) {
                        html.push('<a  data-url="' + customer_view + '" href="' + customer_view + '" class="redirecttopage">' + val.author.firstName + ' ' + val.author.lastName + '</a>');
                    } else {
                        html.push('');
                    }

                } else if (vendorID != '') {
                    
                    if (val.hasOwnProperty("author") && val.author != null) {
                        var driverId = val.author.id;
                        html.push('<a  data-url="' + customer_view + '" href="' + customer_view + '" class="redirecttopage">' + val.author.firstName + ' ' + val.author.lastName + '</a>');
                    } else {
                        html.push('');
                    }

                } else {

                    var title = '';
                    if (val.hasOwnProperty('vendor') && val.vendor.title != undefined) {
                        title = val.vendor.title;
                    }
                    html.push('<a  data-url="' + route_view + '" href="' + route_view + '" class="redirecttopage">' + title + '</a>');
                    
                    if (val.hasOwnProperty("author") && val.author != null) {
                        var driverId = val.author.id;
                        html.push('<a  data-url="' + customer_view + '" href="' + customer_view + '" class="redirecttopage">' + val.author.firstName + ' ' + val.author.lastName + '</a>');
                    } else {
                        html.push('');
                    }
                }

            }else{

                if (userID) {

                    var title = '';
                    if (val.hasOwnProperty('vendor') && val.vendor.title != undefined) {
                        title = val.vendor.title;
                    }
                    html.push('<td  data-url="' + route_view + '" href="' + route_view + '" class="redirecttopage">' + title + '</td>');
                    
                    if (val.hasOwnProperty("driver") && val.driver != null) {
                        var driverId = val.driver.id;
                        var diverRoute = '{{ route('drivers.view', ':id') }}';
                        diverRoute = diverRoute.replace(':id', driverId);
                        html.push('<td  data-url="' + diverRoute + '" class="redirecttopage">' + val.driver.firstName + ' ' + val.driver.lastName + '</td>');
                    } else {
                        html.push('');
                    }
                    
                } else if (driverID) {
                    
                    if (val.hasOwnProperty('vendor') && val.vendor.title != undefined) {
                        html.push('<a  data-url="' + route_view + '" href="' + route_view + '" class="redirecttopage">' + val.storeName + '</a>');
                    }
                    
                    if (val.hasOwnProperty("author") && val.author != null) {
                        html.push('<a  data-url="' + customer_view + '" href="' + customer_view + '" class="redirecttopage">' + val.author.firstName + ' ' + val.author.lastName + '</a>');
                    } else {
                        html.push('');
                    }

                } else if (vendorID != '') {

                    if (val.hasOwnProperty("driver") && val.driver != null) {
                        var driverId = val.driver.id;
                        var diverRoute = '{{ route('drivers.view', ':id') }}';
                        diverRoute = diverRoute.replace(':id', driverId);
                        html.push('<a  data-url="' + diverRoute + '" href="' + diverRoute + '"  class="redirecttopage">' + val.driver.firstName + ' ' + val.driver.lastName + '</a>');
                    } else {
                        html.push('');
                    }

                    if (val.hasOwnProperty("author") && val.author != null) {
                        var driverId = val.author.id;
                        html.push('<a  data-url="' + customer_view + '" href="' + customer_view + '" class="redirecttopage">' + val.author.firstName + ' ' + val.author.lastName + '</a>');
                    } else {
                        html.push('');
                    }

                } else {

                    var title = '';
                    if (val.hasOwnProperty('vendor') && val.vendor.title != undefined) {
                        title = val.vendor.title;
                    }
                    html.push('<a  data-url="' + route_view + '" href="' + route_view + '" class="redirecttopage">' + title + '</a>');
                    
                    if (val.hasOwnProperty("driver") && val.driver != null) {
                        var driverId = val.driver.id;
                        var diverRoute = '{{ route('drivers.view', ':id') }}';
                        diverRoute = diverRoute.replace(':id', driverId);
                        html.push('<a  data-url="' + diverRoute + '" href="' + diverRoute + '" class="redirecttopage">' + val.driver.firstName + ' ' + val.driver.lastName + '</a>');
                    } else {
                        html.push('');
                    }
                    
                    if (val.hasOwnProperty("author") && val.author != null) {
                        var driverId = val.author.id;
                        html.push('<a  data-url="' + customer_view + '" href="' + customer_view + '" class="redirecttopage">' + val.author.firstName + ' ' + val.author.lastName + '</a>');
                    } else {
                        html.push('');
                    }
                }
            }

            var date = '';
            var time = '';
            if (val.hasOwnProperty("createdAt")) {
                try {
                    date = val.createdAt.toDate().toDateString();
                    time = val.createdAt.toDate().toLocaleTimeString('en-US');
                } catch (err) {
                }
                html.push('<td class="dt-time">' + date + ' <br>' + time + '</td>');
            } else {
                html.push('');
            }

            html.push('<td class="text-green">' + val.amount + '</td>');
            if (val.hasOwnProperty('takeAway') && val.takeAway) {
                html.push('<td>{{ trans('lang.order_takeaway') }}</td>');
            } else {
                html.push('<td>{{ trans('lang.order_delivery') }}</td>');
            }
            if (val.status === 'Order Placed') {
                html.push('<td><span class="badge badge-warning ">' + val.status + '</span></td>');
            } else if (val.status === 'Order Accepted') {
                html.push('<td><span class="badge badge-info ">' + val.status + '</span></td>');
            } else if (val.status === 'Order Rejected') {
                html.push('<td><span class="badge badge-danger ">' + val.status + '</span></td>');
            } else if (val.status === 'Driver Pending') {
                html.push('<td><span class="badge badge-secondary ">' + val.status + '</span></td>');
            } else if (val.status === 'Driver Rejected') {
                html.push('<td><span class="badge badge-danger ">' + val.status + '</span></td>');
            } else if (val.status === 'Order Shipped') {
                html.push('<td><span class="badge badge-primary ">' + val.status + '</span></td>');
            } else if (val.status === 'In Transit') {
                html.push('<td><span class="badge badge-info ">' + val.status + '</span></td>');
            } else if (val.status === 'Order Completed') {
                html.push('<td><span class="badge badge-success ">' + val.status + '</span></td>');
            } else {
                html.push('<td><span class="badge badge-dark ">' + val.status + '</span></td>');
            }

            var actionHtml = '';
            actionHtml = actionHtml + '<span class="action-btn"><?php if (in_array('orders.print', json_decode(@session('user_permissions'),true))) { ?><a href="' + printRoute + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.print') }}"><i class="mdi mdi-printer" style="font-size:20px;"></i></a><?php } ?><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a> ';
            if (val.status === "Order Accepted" || val.status === "Driver Rejected") {
                actionHtml += '<a id="' + val.id + '" class="order-assign-btn" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.assign_driver') }}" name="order-assign" href="javascript:void(0)"><i class="mdi mdi-truck-delivery"></i></a>';
            }
            if (checkDeletePermission) {
                actionHtml = actionHtml + '<a id="' + val.id + '" class="delete-btn" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}" name="order-delete" href="javascript:void(0)" ><i class="mdi mdi-delete"></i></a>';
            }
            actionHtml = actionHtml + '</span>';
            html.push(actionHtml);
            return html;
        }

        $("#is_active").click(function() {
            $("#orderTable .is_open").prop('checked', $(this).prop('checked'));
        });
        
        $("#deleteAll").click(function() {
            const checkedOrders = $('#orderTable .is_open:checked');
            if (!checkedOrders.length) {
                alert("{{ trans('lang.select_delete_alert') }}");
                return;
            }
            if (!confirm("{{ trans('lang.selected_delete_alert') }}")) return;
            const orderIds = [];
            checkedOrders.each(function() {
                orderIds.push($(this).attr('dataId'));
            });
            deleteOrders(orderIds);
        });

        $(document).on("click", "a[name='order-delete']", function(e) {
            const orderId = this.id;
            deleteOrders([orderId]);
        });

        async function deleteOrders(orderIds) {
            if (!orderIds || !orderIds.length) return;
            jQuery("#data-table_processing").show();

            try {
                // Loop through each order
                for (let i = 0; i < orderIds.length; i++) {
                    const orderId = orderIds[i];

                    // Get drivers who have this order in orderRequestData
                    let snapshot1 = await database.collection("users")
                        .where("role", "==", "driver")
                        .where("orderRequestData", "array-contains", orderId)
                        .get();

                    // Get drivers who have this order in inProgressOrderID
                    let snapshot2 = await database.collection("users")
                        .where("role", "==", "driver")
                        .where("inProgressOrderID", "array-contains", orderId)
                        .get();

                    // Merge drivers (avoid duplicates)
                    let driversMap = new Map();
                    snapshot1.docs.concat(snapshot2.docs).forEach(doc => {
                        driversMap.set(doc.id, doc);
                    });

                    // Remove order ID from both arrays
                    let batch = database.batch();
                    driversMap.forEach((doc, driverId) => {
                        const driverRef = database.collection("users").doc(driverId);
                        batch.update(driverRef, {
                            orderRequestData: firebase.firestore.FieldValue.arrayRemove(orderId),
                            inProgressOrderID: firebase.firestore.FieldValue.arrayRemove(orderId)
                        });
                    });

                    await batch.commit();

                    // Delete the order itself
                    await database.collection('vendor_orders').doc(orderId).delete();

                }

                // Reload page after all orders processed
                setTimeout(() => {
                    window.location.reload();
                }, 1500);

            } catch (error) {
                console.error("Error deleting orders:", error);
                alert("Failed to delete some orders. Check console for details.");
            }
        }

        async function getSectionName(sectionId) {
            var sectionName = '';
            await database.collection('sections').where("id", "==", sectionId).get().then(async function(snapshots) {
                if (snapshots.docs.length > 0) {
                    var data = snapshots.docs[0].data();
                    sectionName = data.name;
                }
            });
            return sectionName;
        }
        async function getStoreNameFunction(vendorId) {
            var vendorName = '';
            await database.collection('vendors').where('id', '==', vendorId).get().then(async function(snapshots) {
                var vendorData = snapshots.docs[0].data();
                vendorName = vendorData.title;
                $('.orderTitle').html(" - " + vendorName);
                if (vendorData.dine_in_active == true) {
                    $(".dine_in_future").show();
                }
                var wallet_route = "{{ route('users.walletstransaction', 'id') }}";
                $(".wallet_transaction_vendor").attr("href", wallet_route.replace('id', 'storeID=' + vendorData.author));
                if (vendorData.section_id) {
                    let sectionSnap = await database.collection('sections').doc(vendorData.section_id).get();
                    if (sectionSnap.exists) {
                        let sectionData = sectionSnap.data();
                        if (sectionData.dine_in_active === true) {
                            $(".dine_in_future").show();
                        }
                    }
                }
            });
            return vendorName;
        }
        
        async function getUserNameFunction(userId) {
            var userName = '';
            await database.collection('users').where('id', '==', userId).get().then(async function(snapshots) {
                var userData = snapshots.docs[0].data();
                userName = userData.firstName + " " + userData.lastName;
                $('.orderTitle').html(' - ' + userName);
            });
            return userName;
        }
        
        function clickpage(value) {
            setCookie('pagesizes', value, 30);
            location.reload();
        }

        function buildHTMLProductstotal(snapshotsProducts) {
            let order_subtotal = 0;
            let total_discount = 0;
            let total_tax_amount = 0;
            let tip_amount = parseFloat(snapshotsProducts.tip_amount || 0);
            let deliveryCharge = parseFloat(snapshotsProducts.deliveryCharge || 0);
            let platformFee = parseFloat(snapshotsProducts.platformFee || 0);
            let packagingCharge = parseFloat(snapshotsProducts.vendor.packagingCharge || 0);
            let packagingChargeEnable = snapshotsProducts.packagingChargeEnable;
            //  Calculate subtotal and product extras
            for (let i = 0; i < snapshotsProducts.products.length; i++) {
                let product = snapshotsProducts.products[i];
                let basePrice = (product.discountPrice && parseFloat(product.discountPrice) > 0) ? parseFloat(product.discountPrice) : parseFloat(product.price);
                let itemGross = (basePrice + parseFloat(product.extras_price || 0)) * parseInt(product.quantity);
                order_subtotal += itemGross;
            }

            // Total discounts
            let order_discount = parseFloat(snapshotsProducts.discount || 0);
            let special_discount = parseFloat(snapshotsProducts.specialDiscount?.special_discount || 0);
                total_discount = order_discount + special_discount;

            // Calculate item-level taxes (if product-level)
            if (snapshotsProducts.taxScope === "product") {
                let itemSubtotal = order_subtotal;
                snapshotsProducts.products.forEach(product => {
                    let basePrice = (product.discountPrice && parseFloat(product.discountPrice) > 0) ? parseFloat(product.discountPrice) : parseFloat(product.price);
                    let itemGross = (basePrice + parseFloat(product.extras_price || 0)) * parseInt(product.quantity);
                    let itemDiscount = (itemSubtotal > 0) ? (itemGross / itemSubtotal) * total_discount : 0;
                    let itemTaxable = Math.max(0, itemGross - itemDiscount);
                    let itemTaxes = product.taxSetting || [];
                    itemTaxes.forEach(tax => {
                        if (tax.enable) {
                            let taxAmount = 0;
                            if (tax.type === "percentage") {
                                taxAmount = (tax.tax / 100) * itemTaxable;
                            } else {
                                taxAmount = tax.tax * product.quantity;
                            }
                            total_tax_amount += parseFloat(taxAmount);
                        }
                    });
                });
            } 

            // Order-level taxes (if order-level)
            if (snapshotsProducts.taxScope === "order") {
                let orderTaxable = Math.max(0, order_subtotal - total_discount);
                (snapshotsProducts.taxSetting || []).forEach(tax => {
                    if (tax.enable) {
                        let taxAmount = 0;
                        if (tax.type === "percentage") {
                            taxAmount = (tax.tax / 100) * orderTaxable;
                        } else {
                            taxAmount = tax.tax;
                        }
                        total_tax_amount += parseFloat(taxAmount);
                    }
                });
            }

            // Delivery, packaging, platform taxes
            let extraCharges = [
                {key: 'delivery', amount: deliveryCharge, taxes: snapshotsProducts.driverDeliveryTax || []},
                {key: 'packaging', amount: packagingCharge, taxes: snapshotsProducts.packagingTax || []},
                {key: 'platform',amount: platformFee, taxes: snapshotsProducts.platformTax || []},
            ];

            extraCharges.forEach(scope => {
                if (scope.key === "packaging" && !packagingChargeEnable) {
                    return;
                }
                scope.taxes?.forEach(tax => {
                    if (tax.enable) {
                        let taxAmount = 0;
                        if(scope.amount > 0){
                            if (tax.type === "percentage") {
                                taxAmount = (tax.tax / 100) * scope.amount;
                            } else {
                                taxAmount = tax.tax;
                            }
                        }
                        total_tax_amount += parseFloat(taxAmount);
                    }
                });
            });

            //Final subtotal after discounts
            order_subtotal = order_subtotal - total_discount;
            
            // Final total
            let order_total = order_subtotal + deliveryCharge + tip_amount + (packagingChargeEnable ? packagingCharge : 0) + platformFee + total_tax_amount;

            if (currencyAtRight) {
                order_total_val = parseFloat(order_total).toFixed(decimal_degits) + '' + currentCurrency;
            } else {
                order_total_val = currentCurrency + '' + parseFloat(order_total).toFixed(decimal_degits);
            }

            return order_total_val;
        }

        function clickLink(value) {
            setCookie('section_id', value, 30);
            location.reload();
        }

        $(document).on("click", ".order-assign-btn", async function(e) {

            jQuery("#data-table_processing").show();

            let orderId = this.id;
            $("#currentOrderId").val(orderId);
            
            // Get order data
            let orderRef = await database.collection('vendor_orders').doc(orderId).get();
            let orderData = orderRef.data();

            // Get DriverNearBy settings
            let driverNearByRef = await database.collection("settings").doc('DriverNearBy').get();
            let driverNearByData = driverNearByRef.data();

            let minimumDepositToRideAccept = parseInt(driverNearByData.minimumDepositToRideAccept || 0);
            let kDistanceRadiusForDispatch = parseInt(driverNearByData.driverRadios || 50);
            if(driverNearByData.distanceType === 'miles'){
                kDistanceRadiusForDispatch = kDistanceRadiusForDispatch * 1.60934;
            }
            let singleOrderReceive = driverNearByData.singleOrderReceive || false;

            let zone_id = null;
            if(orderData.address?.location?.latitude && orderData.address?.location?.longitude){
                zone_id = await getUserZoneId(orderData.address.location.longitude, orderData.address.location.latitude);
            }

            let rejectedByDrivers = orderData.rejectedByDrivers || [];

            let driverSnapshots = await database.collection("users")
                .where('role', '==', "driver")
                .where('isActive', '==', true)
                .where('wallet_amount', '>=', minimumDepositToRideAccept)
                .get();

            let eligibleDrivers = [];

            driverSnapshots.docs.forEach((snapshot) => {
                let driver = snapshot.data();
                if(driver.vendorID) return;
                if(!driver.fcmToken) return;
                if(zone_id && driver.zoneId && driver.zoneId !== zone_id) return;
                if(rejectedByDrivers.includes(snapshot.id)) return;
                if (singleOrderReceive) {
                    if (Array.isArray(driver.orderRequestData) && driver.orderRequestData.length > 0) {
                        return;
                    }
                }
                if(driver.location && orderData.vendor){
                    const distance = distanceRadius(
                        driver.location.latitude,
                        driver.location.longitude,
                        orderData.vendor.latitude,
                        orderData.vendor.longitude
                    );
                    if(distance > kDistanceRadiusForDispatch) return;
                }
                eligibleDrivers.push({
                    id: snapshot.id,
                    name: driver.firstName+' '+driver.lastName,
                    email: driver.email,
                    distance: driver.location && orderData.vendor 
                        ? distanceRadius(driver.location.latitude, driver.location.longitude, orderData.vendor.latitude, orderData.vendor.longitude)
                        : null
                });
            });

            // Populate dropdown
            let dropdown = $("#driver-select");
            dropdown.empty();
            if(eligibleDrivers.length > 0){
                eligibleDrivers.sort((a, b) => {
                    return a.name.localeCompare(b.name);
                });
                dropdown.append(`<option value="">{{ trans('lang.select_driver') }}</option>`);
                eligibleDrivers.forEach(driver => {
                    dropdown.append(`<option value="${driver.id}">${driver.name}</option>`);
                });
            } else {
                dropdown.append('<option value="">No drivers found</option>');
            }
            
            $("#assignDriverModal").modal('show');
            jQuery("#data-table_processing").hide();
        });

        $(document).on("click", "#assign-driver-btn", async function() {
            
            jQuery("#data-table_processing").show();

            let orderId = $("#currentOrderId").val();
            let driverId = $("#driver-select").val();
            if(!driverId){
                Swal.fire({
                    icon: 'error',
                    title: '{{ trans('lang.select_driver') }}',
                    showConfirmButton: true
                });
                return;
            }
            let orderRef = await database.collection('vendor_orders').doc(orderId).get();
            let orderData = orderRef.data();

            let driverNearByRef = await database.collection("settings").doc('DriverNearBy').get();
            let driverNearByData = driverNearByRef.data();
            
            await assignDriverToOrder(orderId, driverId, driverNearByData, orderData);
            
            $("#assignDriverModal").modal('hide');

            window.location.reload();
        });
        
        async function assignDriverToOrder(orderId, driverId, driverNearByData, orderData) {
            
            let orderAcceptRejectDuration = parseInt(driverNearByData.driverOrderAcceptRejectDuration || 0);
            let singleOrderReceive = driverNearByData.singleOrderReceive || false;

            // Get driver ref
            let driverRef = database.collection('users').doc(driverId);
            let orderRef = database.collection('vendor_orders').doc(orderId);

            // Set order status to Driver Pending
            await orderRef.set({ status: "Driver Pending" }, { merge: true });

            // Update driver's orderRequestData
            let driverSnap = await driverRef.get();
            let driverData = driverSnap.data();
            let orderRequestData = driverData.orderRequestData || [];
            if(singleOrderReceive === false){
                if(orderRequestData.indexOf(orderId) === -1){
                    orderRequestData.push(orderId);
                }
            } else {
                orderRequestData = [orderId];
            }

            await driverRef.update({ orderRequestData });
        }

    </script>
@endsection
