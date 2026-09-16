@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <div class="d-flex top-title-section justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/order.png') }}"></span>
                        <h3 class="mb-0">{{ trans('lang.order_plural') }}  <span class="orderTitle"></span></h3>
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
                                    <select class="form-control status_selector">
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
                       
                        <div class="menu-tab ">
                            <ul>
                                <li>
                                    <a href="{{route('owners.view',$id)}}" class="basic"><i class="ri-list-indefinite"></i>{{trans('lang.tab_basic')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('owner.driver.list',$id)}}" class="payout"><i class="ri-group-3-fill"></i>{{trans('lang.driver_plural')}}</a>
                                </li>
                                <li class="active">
                                    <a href="{{route('orders.owner',$id)}}"><i class="ri-shopping-bag-line"></i>{{trans('lang.order_plural')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('owners.payouts',$id)}}" class="payout"><i class="ri-bank-card-line"></i>{{trans('lang.tab_payouts')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('payoutRequests.owners.view',$id)}}" class="vendor_payout"><i class="ri-refund-line"></i>{{trans('lang.tab_payout_request')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('owners.walletTransaction',$id)}}" class="wallet_transaction"><i class="ri-wallet-line"></i>{{trans('lang.wallet_transaction')}}</a>
                                </li>
                            </ul>
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
                                                <th>{{ trans('lang.section') }}</th>         
                                                <th>{{ trans('lang.driver_name') }}</th>
                                                <th>{{ trans('lang.order_user_id') }}</th>
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
@endsection
@section('scripts')

    <script type="text/javascript">

        var section_id = getCookie('section_id') || '';
        var service_type = getCookie('service_type') || '';

        var database = firebase.firestore();
        var user_permissions = '<?php echo @session('user_permissions'); ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        
        var checkDeletePermission = false;
        var checkPrintPermission = false;
        if ($.inArray('orders.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }
        if ($.inArray('orders.print', user_permissions) >= 0) {
            checkPrintPermission = true;
        }

        var decimal_degits = 0;
        var append_list = '';

        var currentCurrency = '';
        var currencyAtRight = false;
        var refCurrency = regionCurrencyRef();
        refCurrency.get().then(async function(snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        });
        
        var vendorID = "{{ $id }}";
        var orderStatus = "{{ request()->get('status', '') }}";

        const sectionsRef = database.collection('sections').where('isActive', '==', true).orderBy('order');
        $('.status_selector').select2({
            placeholder: '{{ trans('lang.status') }}',
            minimumResultsForSearch: Infinity,
            allowClear: true
        });
        
        $('.allModules').select2({
            placeholder: "{{ trans('lang.select') }} {{ trans('lang.section_plural') }}",
            minimumResultsForSearch: Infinity,
            allowClear: true
        });

        $('select').on("select2:unselecting", function(e) {
            var self = $(this);
            setTimeout(function() {
                self.select2('close');
            }, 0);
        });

        setDate();
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

        $(document).ready(async function() {
           
            $('#data-table_processing').show();

            const getUserName = getUserNameFunction(vendorID);

            const ownedDriversSnapshot = await database.collection('users').where('role', '==', 'driver').where('ownerId', '==', vendorID).get();
            const ownedDriverIds = ownedDriversSnapshot.docs.map(doc => doc.data().id);
            if (ownedDriverIds.length === 0) {
                console.log("No drivers found");
                return;
            }

            function chunkArray(arr, size = 10) {
                const result = [];
                for (let i = 0; i < arr.length; i += size) {
                    result.push(arr.slice(i, i + size));
                }
                return result;
            }
            const driverChunks = chunkArray(ownedDriverIds);

            async function getOrdersFromCollection(collectionName) {

                const status = $('.status_selector').val();
                const daterange = $('#daterange').data('daterangepicker');
                
                let orders = [];
                for (const chunk of driverChunks) {
                    let colRef = regionScoped(database.collection(collectionName));
                    if(status){
                        colRef = colRef.where('status', '==', status);
                    }
                    if(daterange && $('#daterange span').html() !== '{{ trans('lang.select_range') }}'){
                        const from = firebase.firestore.Timestamp.fromDate(daterange.startDate.toDate());
                        const to   = firebase.firestore.Timestamp.fromDate(daterange.endDate.toDate());
                        colRef = colRef.where('createdAt', '>=', from).where('createdAt', '<=', to);
                    }
                    colRef = colRef.where('driverId', 'in', chunk).orderBy('createdAt', 'desc');
                    const snapshot = await colRef.get();;
                    
                    snapshot.docs.forEach(doc => {
                        orders.push({ 
                            id: doc.id,
                            ...doc.data(),
                            collection: collectionName
                        });
                    });
                }
                return orders;
            }

            var rides  = await getOrdersFromCollection('rides');
            var rental = await getOrdersFromCollection('rental_orders');
            var parcel = await getOrdersFromCollection('parcel_orders');
            var allOrders = [...rides, ...rental, ...parcel];

            $('select').change(async function() {
                $("#data-table_processing").show();
                rides  = await getOrdersFromCollection('rides');
                rental = await getOrdersFromCollection('rental_orders');
                parcel = await getOrdersFromCollection('parcel_orders');
                allOrders = [...rides, ...rental, ...parcel];
                $('#orderTable').DataTable().ajax.reload(null, false);
                $("#data-table_processing").hide();
            });

            $('#daterange').on('apply.daterangepicker cancel.daterangepicker', async function() {
                $("#data-table_processing").show();
                rides  = await getOrdersFromCollection('rides');
                rental = await getOrdersFromCollection('rental_orders');
                parcel = await getOrdersFromCollection('parcel_orders');
                allOrders = [...rides, ...rental, ...parcel];
                $('#orderTable').DataTable().ajax.reload(null, false);
                $("#data-table_processing").hide();
            });

            $(document.body).on('click', '.redirecttopage', function() {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });
            
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

            append_list = document.getElementById('append_list1');
            append_list.innerHTML = '';

            var columns = [
                {
                    key: 'id',
                    header: "{{ trans('lang.order_id') }}"
                },
                {
                    key: 'sectionName',
                    header: "{{ trans('lang.section') }}"
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
        
            var fieldConfig = {
                columns: columns, // Assign the dynamically generated array here
                fileName: "{{ trans('lang.order_table') }}",
            };
            const table = $('#orderTable').DataTable({
                pageLength: 10, // Number of rows per page
                processing: false, // Show processing indicator
                serverSide: true, // Enable server-side processing
                responsive: true,
                ajax: async function(data, callback, settings) {
                    const start = data.start;
                    const length = data.length;
                    const searchValue = data.search.value.toLowerCase();
                    const orderColumnIndex = data.order[0].column;
                    const orderDirection = data.order[0].dir;
                    
                    var orderableColumns = (checkDeletePermission) ? ['', 'id', 'sectionName', 'driverName', 'clientName', 'createdAt', 'amount', 'orderType', 'status', ''] : ['id', 'sectionName', 'driverName', 'clientName', 'createdAt', 'amount', 'orderType', 'status', '']; // Ensure this matches the actual column names
                    
                    const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                    if (searchValue.length >= 3 || searchValue.length === 0) {
                        $('#data-table_processing').show();
                    }

                    if (!allOrders || allOrders.length === 0) {
                        $('.total_count').text(0);
                        $('#data-table_processing').hide();
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                        return;
                    }

                    let records = [];
                    let filteredRecords = [];
                    let sectionNames = {};

                    // Load section names (same as old code)
                    const sectionDocs = await sectionsRef.get();
                    sectionDocs.forEach(doc => {
                        sectionNames[doc.id] = doc.data().name;
                    });

                    allOrders.map(childData => {

                        childData.sectionName = sectionNames[childData.sectionId] || 'N/A';

                        var driverName = '';
                        if (childData.hasOwnProperty("driver") && childData.driver != null) {
                            driverName = childData.driver.firstName + ' ' + childData.driver.lastName;
                            childData.driverName = driverName;
                        }

                        if (childData.author) {
                            childData.clientName = childData.author.firstName + ' ' + childData.author.lastName;
                        }

                        if (childData.takeAway) {
                            childData.orderType = "{{ trans('lang.order_takeaway') }}";
                        } else {
                            childData.orderType = "{{ trans('lang.order_delivery') }}";
                        }

                        if(service_type === "cab-service"){
                            childData.amount = buildHTMLProductstotalCab(childData);
                        }else if(service_type === "parcel_delivery"){
                            childData.amount = buildHTMLProductstotalParcel(childData);
                        }else if(service_type === "rental-service"){
                            childData.amount = buildHTMLProductstotalRental(childData);    
                        }
                        
                        if (searchValue) {

                            var date = '';
                            var time = '';
                            try {
                                if (childData.createdAt) {
                                    date = childData.createdAt.toDate().toDateString();
                                    time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                                }
                            } catch (err) {}

                            var createdAt = date + '<br> ' + time;

                            if (
                                (childData.id && childData.id.toString().includes(searchValue)) ||
                                (childData.sectionName && childData.sectionName.toLowerCase().includes(searchValue)) ||
                                (childData.driverName && childData.driverName.toLowerCase().includes(searchValue)) ||
                                (childData.clientName && childData.clientName.toLowerCase().includes(searchValue)) ||
                                (createdAt && createdAt.toLowerCase().includes(searchValue)) ||
                                (childData.orderType && childData.orderType.toLowerCase().includes(searchValue)) ||
                                (childData.status && childData.status.toLowerCase().includes(searchValue)) ||
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

                        if (orderByField === 'createdAt') {
                            aValue = aValue ? aValue.toDate().getTime() : 0;
                            bValue = bValue ? bValue.toDate().getTime() : 0;
                        } else if (orderByField === 'amount') {
                            aValue = parseFloat(aValue) || 0;
                            bValue = parseFloat(bValue) || 0;
                        } else {
                            aValue = aValue ? aValue.toString().toLowerCase() : '';
                            bValue = bValue ? bValue.toString().toLowerCase() : '';
                        }

                        return orderDirection === 'asc'
                            ? (aValue > bValue ? 1 : -1)
                            : (aValue < bValue ? 1 : -1);
                    });

                    const totalRecords = filteredRecords.length;
                    $('.total_count').text(totalRecords);

                    const paginatedRecords = filteredRecords.slice(start, start + length);
                    await Promise.all(paginatedRecords.map(async (childData) => {
                        var getData = await buildHTML(childData);
                        records.push(getData);
                    }));

                    $('#data-table_processing').hide();

                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords,
                        recordsFiltered: totalRecords,
                        data: records
                    });
                    
                },
                order:  (checkDeletePermission) ? [
                    [6, 'desc']
                ] : [
                    [5, 'desc']
                ],
                columnDefs: [
                    {
                        orderable: false,
                        targets:  (checkDeletePermission == true) ? [0, 9] : [8],
                    },
                    {
                        type: 'date',
                        render: function(data) {
                            return data;
                        },
                        targets: (checkDeletePermission == true) ? 5 : 4,
                    }
                ],
               "language": datatableLang,
                dom: 'lfrtipB',
                buttons: [
                    {
                        extend: 'collection',
                        text: '<i class="mdi mdi-cloud-download"></i> Export as',
                        className: 'btn btn-info',
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                text: 'Export Excel',
                                action: function(e, dt, button, config) {
                                    exportData(dt, 'excel', fieldConfig);
                                }
                            },
                            {
                                extend: 'pdfHtml5',
                                text: 'Export PDF',
                                action: function(e, dt, button, config) {
                                    exportData(dt, 'pdf', fieldConfig);
                                }
                            },
                            {
                                extend: 'csvHtml5',
                                text: 'Export CSV',
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

        async function buildHTML(val) {
            var html = [];
            var id = val.id;
            var vendorID = val.vendorID;
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

            var order_view = '#';    
            if(val.driver){
                let serviceTypes = val.driver.serviceTypes || (val.driver.serviceType ? [val.driver.serviceType] : []);
                if (serviceTypes.includes("parcel_delivery") && service_type == "parcel_delivery") {
                    order_view = '{{ route('parcel_orders.edit', ':id') }}';    
                }else if(serviceTypes.includes("rental-service") && service_type == "rental-service"){
                    order_view = '{{ route('rental_orders.edit', ':id') }}';    
                }else if(serviceTypes.includes("cab-service") && service_type == "cab-service"){
                    order_view = '{{ route('rides.edit', ':id') }}';    
                }
                order_view = order_view.replace(':id', id);
                order_view = order_view + '?eid={{ $id }}';
            }
            
            var printRoute = '{{ route('vendors.orderprint', ':id') }}';
            printRoute = printRoute.replace(':id', id);
            printRoute = printRoute + '?eid={{ $id }}';

            var driver_view = '{{ route('drivers.view', ':id') }}';
            driver_view = driver_view.replace(':id', val.driver.id);

            var customer_view = '{{ route('users.view', ':id') }}';
            customer_view = customer_view.replace(':id', user_id);

            if (checkDeletePermission) {
                html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' + 'for="is_open_' + id + '" ></label></td>');
            }

            html.push('<a data-url="' + order_view + '" href="' + order_view + '" class="redirecttopage">' + val.id + '</a>');
        
            html.push('<td>' + val.sectionName + '</td>');

            html.push('<a  data-url="' + driver_view + '" href="' + driver_view + '" class="redirecttopage">' + val.driverName + '</a>');

            html.push('<a  data-url="' + customer_view + '" href="' + customer_view + '" class="redirecttopage">' + val.clientName + '</a>');
            
            var date = '';
            var time = '';
            if (val.hasOwnProperty("createdAt")) {
                try {
                    date = val.createdAt.toDate().toDateString();
                    time = val.createdAt.toDate().toLocaleTimeString('en-US');
                } catch (err) {
                }
                html.push('<td class="dt-time">' + date + '<br> ' + time + '</td>');
            } else {
                html.push('');
            }

            html.push('<td class="text-green">' + val.amount + '</td>');

            if (val.hasOwnProperty('takeAway') && val.takeAway) {
                html.push('<td>{{ trans('lang.order_takeaway') }}</td>');
            } else {
                html.push('<td>{{ trans('lang.order_delivery') }}</td>');
            }
            
            if (val.status == 'Order Placed') {
                html.push('<td class="order_placed"><span>' + val.status + '</span></td>');
            } else if (val.status == 'Order Accepted') {
                html.push('<td class="order_accepted"><span>' + val.status + '</span></td>');
            } else if (val.status == 'Order Rejected') {
                html.push('<td class="order_rejected"><span>' + val.status + '</span></td>');
            } else if (val.status == 'Driver Pending') {
                html.push('<td class="driver_pending"><span>' + val.status + '</span></td>');
            } else if (val.status == 'Driver Rejected') {
                html.push('<td class="driver_rejected"><span>' + val.status + '</span></td>');
            } else if (val.status == 'Order Shipped') {
                html.push('<td class="order_shipped"><span>' + val.status + '</span></td>');
            } else if (val.status == 'In Transit') {
                html.push('<td class="in_transit"><span>' + val.status + '</span></td>');
            } else if (val.status == 'Order Completed') {
                html.push('<td class="order_completed"><span>' + val.status + '</span></td>');
            } else {
                html.push('<td class="order_completed"><span>' + val.status + '</span></td>');
            }

            var actionHtml = '';
            actionHtml = actionHtml + '<span class="action-btn"><?php if (in_array('orders.print', json_decode(@session('user_permissions'),true))) { ?><a href="' + printRoute + '" data-toggle="tooltip" title="{{trans('lang.print')}}"><i class="mdi mdi-printer" style="font-size:20px;"></i></a><?php } ?><a href="' + order_view + '" data-toggle="tooltip" title="{{trans('lang.edit')}}"><i class="mdi mdi-lead-pencil"></i></a> ';
            if (checkDeletePermission) {
                actionHtml = actionHtml + '<a id="' + val.id + '" class="delete-btn" name="order-delete" href="javascript:void(0)" data-toggle="tooltip" title="{{trans('lang.delete')}}"><i class="mdi mdi-delete"></i></a>';
            }
            actionHtml = actionHtml + '</span>';
            html.push(actionHtml);
            return html;
        }

        $("#is_active").click(function() {
            $("#orderTable .is_open").prop('checked', $(this).prop('checked'));
        });

        $("#deleteAll").click(function() {
            if ($('#orderTable .is_open:checked').length) {
                if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                    jQuery("#data-table_processing").show();
                    $('#orderTable .is_open:checked').each(function() {
                        var dataId = $(this).attr('dataId');
                        database.collection('vendor_orders').doc(dataId).delete().then(function() {
                            setTimeout(function() {
                                window.location.reload();
                            }, 5000);
                        });
                    });
                }
            } else {
                alert("{{ trans('lang.select_delete_alert') }}");
            }
        });

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
                $(".orderTitle").text(' - ' + vendorName);
                if (vendorData.dine_in_active == true) {
                    $(".dine_in_future").show();
                }
                var wallet_route = "{{ route('users.walletstransaction', 'id') }}";
                $(".wallet_transaction_vendor").attr("href", wallet_route.replace('id', 'storeID=' + vendorData.author));
            });
            return vendorName;
        }
        async function getUserNameFunction(userId) {
            var userName = '';
            await database.collection('users').where('id', '==', userId).get().then(async function(snapshots) {
                var userData = snapshots.docs[0].data();
                userName = userData.firstName + " " + userData.lastName;
                $(".orderTitle").text(' - ' + userName);
            });
            return userName;
        }
        $(document).on("click", "a[name='order-delete']", function(e) {
            var id = this.id;
            jQuery("#data-table_processing").show();
            database.collection('vendor_orders').doc(id).delete().then(function(result) {
                window.location.href = '{{ url()->current() }}';
            });
        });
        function clickpage(value) {
            setCookie('pagesizes', value, 30);
            location.reload();
        }

        function buildHTMLProductstotalCab(ride) {

            let order_subtotal = parseFloat(ride.subTotal || 0);
            let total_discount = 0;
            let total_tax_amount = 0;

            let tip_amount = parseFloat(ride.tip_amount || 0);
            let platformFee = parseFloat(ride.platformFee || 0);

            // Discount
            let order_discount = parseFloat(ride.discount || 0);
            total_discount = isNaN(order_discount) ? 0 : order_discount;

            // ORDER LEVEL TAX
            let orderTaxable = Math.max(0, order_subtotal - total_discount);
            (ride.taxSetting || []).forEach(tax => {
                if (tax.enable) {
                    let taxAmount = tax.type === "percentage"
                        ? (parseFloat(tax.tax) / 100) * orderTaxable
                        : parseFloat(tax.tax);
                    total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                }
            });

            // EXTRA TAXES
            [
                { amount: platformFee, taxes: ride.platformTax || [] },
            ].forEach(scope => {
                scope.taxes?.forEach(tax => {
                    if (tax.enable) {
                        let taxAmount = 0;
                        if(scope.amount > 0){
                            taxAmount = tax.type === "percentage"
                            ? (parseFloat(tax.tax) / 100) * scope.amount
                            : parseFloat(tax.tax);
                        }
                        total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                    }
                });
            });

            let order_total = (order_subtotal - total_discount) + tip_amount + platformFee + total_tax_amount;

            if (currencyAtRight) {
                order_total_val = parseFloat(order_total).toFixed(decimal_degits) + '' + currentCurrency;
            } else {
                order_total_val = currentCurrency + '' + parseFloat(order_total).toFixed(decimal_degits);
            }

            return order_total_val;
        }

        function buildHTMLProductstotalParcel(orderData) {

            let order_subtotal = parseFloat(orderData.subTotal || 0);
            let total_discount = 0;
            let total_tax_amount = 0;

            // Discount
            let discount = parseFloat(orderData.discount || 0);
            total_discount = isNaN(discount) ? 0 : discount;

            // Order Level Tax
            let orderTaxable = Math.max(0, order_subtotal - total_discount);

            (orderData.taxSetting || []).forEach(tax => {
                if (tax.enable) {
                    let taxAmount = tax.type === "percentage"
                        ? (parseFloat(tax.tax) / 100) * orderTaxable
                        : parseFloat(tax.tax);

                    total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                }
            });

            // Extra Charges
            let platformFee = parseFloat(orderData.platformFee || 0);

            // Extra Taxes
            [
                { amount: platformFee, taxes: orderData.platformTax || [] }
            ].forEach(scope => {
                scope.taxes?.forEach(tax => {
                    if (tax.enable) {
                        let taxAmount = 0;
                        if(scope.amount > 0){
                            taxAmount = tax.type === "percentage"
                            ? (parseFloat(tax.tax) / 100) * scope.amount
                            : parseFloat(tax.tax);
                        }
                        total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                    }
                });
            });

            // Final price
            let order_total = (order_subtotal - total_discount) + platformFee + total_tax_amount;

            if (currencyAtRight) {
                order_total_val = parseFloat(order_total).toFixed(decimal_degits) + '' + currentCurrency;
            } else {
                order_total_val = currentCurrency + '' + parseFloat(order_total).toFixed(decimal_degits);
            }

            return order_total_val;
        }

        function buildHTMLProductstotalRental(snapshotsProducts) {

            var discount = parseFloat(snapshotsProducts.discount || 0);
            let subTotal = parseFloat(snapshotsProducts.subTotal || 0);
            let driverRate = parseFloat(snapshotsProducts.driverRate || 0);
            var notes = snapshotsProducts.note;

            var startKm = parseFloat(snapshotsProducts.startKitoMetersReading) || 0;
            var endKm = parseFloat(snapshotsProducts.endKitoMetersReading) || 0;
            var includedDistance = parseFloat(snapshotsProducts?.rentalPackageModel?.includedDistance) || 0;
            var extraKmFare = parseFloat(snapshotsProducts?.rentalPackageModel?.extraKmFare) || 0;
            var extraMinuteFare = parseFloat(snapshotsProducts?.rentalPackageModel?.extraMinuteFare) || 0;
            var totalMinutesUsed = parseFloat(snapshotsProducts.totalMinutesUsed) || 0;
            var includedMinutes = parseFloat(snapshotsProducts?.rentalPackageModel?.includedMinutes) || 0;

            var extraKm = 0;
            var extraKilometerCharge = 0;
            var extraMinutesCharge = 0;

            // Extra Kilometer Calculation
            if (endKm > startKm) {
                let totalKm = endKm - startKm;
                if (totalKm > includedDistance) {
                    totalKm = totalKm - includedDistance;
                    extraKm = totalKm;
                    extraKilometerCharge = totalKm * extraKmFare;
                }
            }
            
            // Convert Firestore timestamps to JS Date
            var startTime = snapshotsProducts.startTime ? snapshotsProducts.startTime.toDate() : null;
            var endTime = snapshotsProducts.endTime ? snapshotsProducts.endTime.toDate() : null;

            var totalMinutesUsed = 0;

            // Total minutes difference
            if (startTime && endTime) {
                totalMinutesUsed = Math.floor((endTime - startTime) / (1000 * 60));
            }

            // Get included hours from rental package and convert to minutes
            var includedHours = parseFloat(snapshotsProducts?.rentalPackageModel?.includedHours) || 0;
            var includedMinutes = includedHours * 60;

            // Extra Minutes Calculation
            var extraMinutesCharge = 0;
            var extraMinutes = 0;

            if (totalMinutesUsed > includedMinutes) {
                extraMinutes = totalMinutesUsed - includedMinutes;
                extraMinutesCharge = extraMinutes * extraMinuteFare;
            }

            // Add extra cost before discount
            var order_subtotal = parseFloat(subTotal) + parseFloat(driverRate) + parseFloat(extraKilometerCharge) + parseFloat(extraMinutesCharge);
            var total_discount = discount;

            let total_tax_amount = 0;
            // Order tax
            let orderTaxable = Math.max(0, order_subtotal - total_discount);
            let orderCombinedTax = 0;
            (snapshotsProducts.taxSetting || []).forEach(tax => {
                if (tax.enable) {
                    let taxAmount = tax.type === "percentage"
                        ? (parseFloat(tax.tax) / 100) * orderTaxable
                        : parseFloat(tax.tax);

                    total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                    orderCombinedTax += parseFloat(taxAmount);
                }
            });
            
            // Extra charges
            let platformFee = parseFloat(snapshotsProducts.platformFee || 0);

            // Extra taxes
            [
                {key: 'platform', amount: platformFee, taxes: snapshotsProducts.platformTax || []},
            ].forEach(scope => {
                scope.taxes?.forEach(tax => {
                    if (tax.enable) {
                        let taxAmount = 0;
                        if(scope.amount > 0){
                            taxAmount = tax.type === "percentage"
                            ? (parseFloat(tax.tax) / 100) * scope.amount
                            : parseFloat(tax.tax);
                        }
                        total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                    }
                });
            });

            // Final price
            let final_total = (order_subtotal - total_discount) + platformFee + total_tax_amount;

            return currencyAtRight ? final_total.toFixed(decimal_degits) + currentCurrency : currentCurrency + final_total.toFixed(decimal_degits);
        }

    </script>
@endsection
