@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">
                    @if (request()->is('drivers/approved'))
                        @php $type = 'approved'; @endphp
                        {{ trans('lang.approved_drivers') }}
                    @elseif(request()->is('drivers/pending'))
                        @php $type = 'pending'; @endphp
                        {{ trans('lang.approval_pending_drivers') }}
                    @else
                        @php $type = 'all'; @endphp
                        {{ trans('lang.all_drivers') }}
                    @endif
                </h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.driver_table') }}</li>
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
                            <div class="d-flex top-title-left align-self-center">
                                <span class="icon mr-3"><img src="{{ asset('images/driver.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.driver_plural') }}</h3>
                                <span class="counter ml-3 total_count"></span>
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
                                    <select class="form-control status_selector filteredRecords">
                                        <option value="" selected>{{ trans('lang.status') }}</option>
                                        <option value="active">{{ trans('lang.active') }}</option>
                                        <option value="inactive">{{ trans('lang.in_active') }}</option>
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
                <div class="table-list">
                    <div class="row">
                        <div class="col-12">
                            <div class="card border">
                                <div class="card-header d-flex justify-content-between align-items-center border-0">
                                    <div class="card-header-title">
                                        <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.driver_table') }}</h3>
                                        <p class="mb-0 text-dark-2">{{ trans('lang.driver_table_text') }}</p>
                                    </div>
                                    <div class="card-header-right d-flex align-items-center">
                                        <div class="card-header-btn mr-3">
                                            <a class="btn-primary btn rounded-full" href="{!! route('drivers.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.drivers_create') }}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive m-t-10">
                                        <table id="driverTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <?php if (($type == "approved" && in_array('approve.driver.delete', json_decode(@session('user_permissions'), true))) || ($type == "pending" && in_array('pending.driver.delete', json_decode(@session('user_permissions'), true))) || ($type == "all" && in_array('drivers.delete', json_decode(@session('user_permissions'), true)))) { ?>
                                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label></th>
                                                    <?php } ?>
                                                    <th>{{ trans('lang.actions') }}</th>
                                                    <th>{{ trans('lang.driver_info') }}</th>
                                                    <th>{{ trans('lang.active') }}</th>
                                                    <th>{{ trans('lang.driver_online') }}</th>
                                                    <th>{{ trans('lang.date') }}</th>
                                                    <th>{{ trans('lang.total_orders') }}</th>
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
            var type = "{{ $type }}";

            var sectionType = getCookie('service_type') || '';        
            var user_permissions = '<?php echo @session('user_permissions'); ?>';
            user_permissions = Object.values(JSON.parse(user_permissions));
            var checkDeletePermission = false;
            var checkChatPermission = false;
            if (
                (type == 'pending' && $.inArray('pending.driver.delete', user_permissions) >= 0) ||
                (type == 'approved' && $.inArray('approve.driver.delete', user_permissions) >= 0) ||
                (type == 'all' && $.inArray('drivers.delete', user_permissions) >= 0)
            ) {
                checkDeletePermission = true;
            }
            if ($.inArray('drivers.chat', user_permissions) >= 0) {
                checkChatPermission = true;
            }
            $('.status_selector').select2({
                placeholder: '{{ trans('lang.status') }}',
                minimumResultsForSearch: Infinity,
                allowClear: true
            });
            
            $('select').on("select2:unselecting", function(e) {
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

            $('.filteredRecords').change(async function() {

                var status = $('.status_selector').val();
                var daterangepicker = $('#daterange').data('daterangepicker');
                ref = regionScoped(database.collection('users').where("role", "==", "driver").where('isOwner','==',false).where('sectionId', '==', section_id));
                if (status) {
                    ref = (status == "active") ? ref.where('active', '==', true) : ref.where('active', '==', false);
                }
                if ($('#daterange span').html() != '{{ trans('lang.select_range') }}' && daterangepicker) {
                    var from = moment(daterangepicker.startDate).toDate();
                    var to = moment(daterangepicker.endDate).toDate();
                    if (from && to) {
                        var fromDate = firebase.firestore.Timestamp.fromDate(new Date(from));
                        ref = ref.where('createdAt', '>=', fromDate);
                        var toDate = firebase.firestore.Timestamp.fromDate(new Date(to));
                        ref = ref.where('createdAt', '<=', toDate);
                    }
                }
                $('#driverTable').DataTable().ajax.reload();
            });

            var ref = regionScoped(database.collection('users').where("role", "==", "driver").where('isOwner','==',false));
            ref = ref.where('sectionIds', 'array-contains', section_id).orderBy('createdAt', 'desc');

            var alldriver = regionScoped(database.collection('users').where("role", "==", "driver").where('isOwner','==',false).where('sectionId', '==', section_id)).orderBy('createdAt', 'desc');
            
            var placeholderImage = '';
            var placeholder = database.collection('settings').doc('placeHolderImage');
            placeholder.get().then(async function(snapshotsimage) {
                var placeholderImageData = snapshotsimage.data();
                placeholderImage = placeholderImageData.image;
            })

            var append_list = '';
            var serviceRef = database.collection('services');

            $(document).ready(function() {

                
                jQuery("#data-table_processing").show();

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
                var fieldConfig = {
                    columns: [{
                            key: 'name',
                            header: "{{ trans('lang.driver_info') }}"
                        },
                        {
                            key: 'serviceName',
                            header: "{{ trans('lang.service_type') }}"
                        },
                        {
                            key: 'totalOrders',
                            header: "{{ trans('lang.total_orders') }}"
                        },
                        {
                            key: 'active',
                            header: "{{ trans('lang.active') }}"
                        },
                        {
                            key: 'createdAt',
                            header: "{{ trans('lang.date') }}"
                        },

                    ],

                    fileName: "{{ trans('lang.driver_table') }}",
                };

                const table = $('#driverTable').DataTable({
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

                        const orderableColumns = (checkDeletePermission) ? ['', '', 'name', '', '', 'createdAt', '','', '', ''] : ['', 'name', '', '', 'createdAt', '', '','', '']; // Ensure this matches the actual column names
                        const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table

                        if (searchValue.length >= 3 || searchValue.length === 0) {
                            $('#data-table_processing').show();
                        }

                        ref.get().then(async function(querySnapshot) {
                            if (querySnapshot.empty) {
                                $('.total_count').text(0);
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
                            let serviceNames = {};
                            // Fetch service names
                            const serviceDocs = await serviceRef.get();
                            serviceDocs.forEach(doc => {
                                serviceNames[doc.data().flag] = doc.data().name;
                            });

                            querySnapshot.docs.map(async doc => {
                                let childData = doc.data();
                                childData.id = doc.id;
                                childData.name = childData.firstName + ' ' + childData.lastName;
                                childData.serviceName = serviceNames[childData.serviceType] || '-';

                                const isFleetDriver = childData.ownerId && childData.isOwner === false;
                                if (isFleetDriver) {
                                    return;
                                }
                                const isDocVerified = childData.isDocumentVerify === true;
                                const isAutoVerified = childData.isAutoVerify === true;
                                if (type === 'pending') {
                                    if (isDocVerified || isAutoVerified) {
                                        return;
                                    }
                                }
                                if (type === 'approved') {
                                     if (!isDocVerified && !isAutoVerified) {
                                        return;
                                    }
                                }
                                
                                if (searchValue) {
                                    var date = '';
                                    var time = '';
                                    if (childData.hasOwnProperty("createdAt")) {
                                        try {
                                            date = childData.createdAt.toDate().toDateString();
                                            time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                                        } catch (err) {}
                                    }
                                    var createdAt = date + ' ' + time;
                                    if (
                                        (childData.name && childData.name.toString().toLowerCase().includes(searchValue)) ||
                                        (childData.serviceName && childData.serviceName.toString().toLowerCase().includes(searchValue)) ||
                                        (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1)
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
                                } else if (orderByField === 'totalOrders') {
                                    aValue = parseInt(a[orderByField]) || 0;
                                    bValue = parseInt(b[orderByField]) || 0;
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
                                childData.totalOrders = await orderDetails(childData.id, childData.serviceTypes);
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
                    order: (checkDeletePermission) ? [5, 'desc'] : [4, 'desc'],
                    columnDefs: [{
                            orderable: false,
                            targets: (checkDeletePermission) ? [0, 1, 3, 4, 5, 6] : [0, 2, 3, 5, 6],
                        },
                        {
                            type: 'date',
                            render: function(data) {
                                return data;
                            },
                            targets: (checkDeletePermission) ? [5] : [4],
                        }

                    ],
                      "language": datatableLang,
                    dom: 'lfrtipB',
                    buttons: [{
                        extend: 'collection',
                        text: '<i class="mdi mdi-cloud-download"></i>{{ trans('lang.export_as') }}',
                        className: 'btn btn-info',
                        buttons: [{
                                extend: 'excelHtml5',
                                text: '{{ trans('lang.export_excel') }}',
                                action: function(e, dt, button, config) {
                                    exportData(dt, 'excel', fieldConfig);
                                }
                            },
                            {
                                extend: 'pdfHtml5',
                                text: '{{ trans('lang.export_pdf') }}',
                                action: function(e, dt, button, config) {
                                    exportData(dt, 'pdf', fieldConfig);
                                }
                            },
                            {
                                extend: 'csvHtml5',
                                text: '{{ trans('lang.export_csv') }}',
                                action: function(e, dt, button, config) {
                                    exportData(dt, 'csv', fieldConfig);
                                }
                            }
                        ]
                    }],
                    initComplete: function() {
                        $(".dataTables_filter").append($(".dt-buttons").detach());
                        $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete', 'new-password').val('');
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

                alldriver.get().then(async function(snapshotsdriver) {
                    snapshotsdriver.docs.forEach((listval) => {
                        database.collection('vendor_orders').where('driverID', '==', listval.id).where("status", "in", ["Order Completed"]).get().then(async function(orderSnapshots) {
                            var count_order_complete = orderSnapshots.docs.length;
                            database.collection('users').doc(listval.id).update({
                                'orderCompleted': count_order_complete
                            }).then(function(result) {

                            });

                        });

                    });
                });

            });

            //document verification status icon add new code 
            async function getDocumentStatusIcon(driverId) {
                const docSnap = await database.collection('documents_verify').doc(driverId).get();
                if (!docSnap.exists) return '';

                const docs = docSnap.data().documents || [];

                const approved = docs.filter(d => d.status === 'approved').length;
                const rejected = docs.filter(d => d.status === 'rejected').length;
                const total   = docs.length;

                if (approved === total && total > 0) {
                    return '<i class="mdi mdi-verified verified-icon" data-toggle="tooltip" data-bs-original-title="Verified"></i>';
                }
                if (rejected > 0) {
                    return '<i class="mdi mdi-close-circle unverified-icon" data-toggle="tooltip" data-bs-original-title="Rejected" style="color:red;"></i>';
                }
                return '';
            }
            
            async function buildHTML(val) {
                var html = [];
                var id = val.id;
                var route1 = '{{ route('drivers.edit', ':id') }}';
                route1 = route1.replace(':id', id);

                var driverView = '{{ route('drivers.view', ':id') }}';
                driverView = driverView.replace(':id', id);

                if (checkDeletePermission) {
                    html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                        'for="is_open_' + id + '" ></label></td>');
                }
                var actionHtml = '';
                var unreadHtml = '';
                var chatViewRoute = "{{ route('drivers.chat', ':id') }}".replace(':id', val.id);
                actionHtml += '<span class="action-btn">';
                
                if(val.isAutoVerify !== true){
                    var document_list_view = "{{ route('drivers.document', ':id') }}";
                    document_list_view = document_list_view.replace(':id', val.id);
                    actionHtml += '<a href="' + document_list_view + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.document') }}"><i class="fa fa-file"></i></a>';
                }

                var payoutRequests = '{{ route('users.walletstransaction', ':id') }}';
                payoutRequests = payoutRequests.replace(':id', 'driverID=' + val.id);
                actionHtml += '<a href="' + payoutRequests + '"><i class="mdi mdi-wallet" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.wallet_transaction') }}"></i></a>';
                
                actionHtml += '<a href="' + driverView + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view') }}"><i class="mdi mdi-eye"></i></a><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
                if (checkDeletePermission) {
                    actionHtml += '<a id="' + val.id + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}" name="driver-delete" class="delete-btn" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a>';
                }
                if(checkChatPermission){
                    actionHtml += '<a href="' + chatViewRoute + '" class="chat-message" style="position: relative; display: inline-block;">' +
                            '<i class="mdi mdi-wechat mdi-24px"></i>' + unreadHtml +
                            '</a>' 
                }
                actionHtml += '</span>';
                
                html.push(actionHtml);
                
                var verified = await getDocumentStatusIcon(val.id);

                if(val.isAutoVerify === true){
                    verified += ' <i class="mdi mdi-check-circle verified-icon" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.auto_approved') }}"></i>';
                }

                if (val.profilePictureURL == '') {
                    html.push('<td><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image"></td> ' + ' <a data-url="' + driverView + '" href="' + driverView + '" class="redirecttopage left_space">' + val.firstName + ' ' + val.lastName + '</a>' + verified);
                } else {
                    if (val.profilePictureURL) {
                        photo = val.profilePictureURL;
                    } else {
                        photo = placeholderImage;
                    }
                    html.push('<td><img class="rounded" style="width:50px" src="' + photo + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"></td>' + '<a data-url="' + driverView + '" href="' + driverView + '" class="redirecttopage left_space">' + val.firstName + ' ' + val.lastName + '</a>' + verified);
                }

                if (val.active == true) {
                    html.push('<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isActive"><span class="slider round"></span></label></td>');
                } else {
                    html.push('<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isActive"><span class="slider round"></span></label></td>');
                }
                if (val.isActive) {
                    html.push('<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isOnline"><span class="slider round"></span></label></td>');
                } else {
                    html.push('<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isOnline"><span class="slider round"></span></label></td>');
                }

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

                let serviceTypes = val.serviceTypes || (val.serviceType ? [val.serviceType] : []);
                if (serviceTypes){

                    var url = "Javascript:void(0)";
                    if (serviceTypes.includes("parcel_delivery") && sectionType == "parcel_delivery") {
                        url = "{{ route('parcel_orders.driver', 'id') }}";
                        url = url.replace("id", val.id);
                    }else if(serviceTypes.includes("rental-service") && sectionType == "rental-service"){
                        url = "{{ route('rental_orders.driver', 'id') }}";
                        url = url.replace("id", val.id);
                    } else if ((serviceTypes.includes("delivery-service") && sectionType == "delivery-service") || (serviceTypes.includes("ecommerce-service") && sectionType == "ecommerce-service")) {
                        url = "{{ route('orders', 'id') }}";
                        url = url.replace("id", 'driverId=' + val.id);
                    }else if(serviceTypes.includes("cab-service") && sectionType == "cab-service"){
                        url = "{{ route('drivers.rides', 'driverId') }}";
                        url = url.replace('driverId', val.id);
                    }

                    html.push((val.totalOrders > 0 ? '<a href="' + url + '">' + val.totalOrders + '</a>' : val.totalOrders));

                } else {
                    html.push('');
                }

                return html;
            }

            async function orderDetails(driverId, serviceTypes) {
                let totalOrders = 0;
                if (serviceTypes.includes("cab-service") && sectionType == "cab-service") {
                    const ordersSnapshot = await database.collection('rides').where('driverId', '==', driverId).get();
                    totalOrders += ordersSnapshot.docs.length;
                }
                if (serviceTypes.includes("rental-service") && sectionType == "rental-service") {
                    const ordersSnapshot = await database.collection('rental_orders').where('driverId', '==', driverId).get();
                    totalOrders += ordersSnapshot.docs.length;
                } 
                if ((serviceTypes.includes("delivery-service") && sectionType == "delivery-service") || (serviceTypes.includes("ecommerce-service") && sectionType == "ecommerce-service")) {
                    const ordersSnapshot = await database.collection('vendor_orders').where('driverID', '==', driverId).get();
                    totalOrders += ordersSnapshot.docs.length;
                } 
                if (serviceTypes.includes("parcel_delivery")  && sectionType == "parcel_delivery") {
                    const ordersSnapshot = await database.collection('parcel_orders').where('driverId', '==', driverId).get();
                    totalOrders += ordersSnapshot.docs.length;
                }
                return totalOrders;
            }

            $(document).on("click", "input[name='isOnline']", function(e) {
                var ischeck = $(this).is(':checked');
                var id = this.id;
                if (ischeck) {
                    database.collection('users').doc(id).update({
                        'isActive': true
                    }).then(function(result) {});
                } else {
                    database.collection('users').doc(id).update({
                        'isActive': false
                    }).then(function(result) {});
                }
            });
            $(document).on("click", "input[name='isActive']", function(e) {
                var ischeck = $(this).is(':checked');
                var id = this.id;
                if (ischeck) {
                    database.collection('users').doc(id).update({
                        'active': true
                    }).then(function(result) {});
                } else {
                    database.collection('users').doc(id).update({
                        'active': false
                    }).then(function(result) {});
                }
            });

            $("#is_active").click(function() {
                $("#driverTable .is_open").prop('checked', $(this).prop('checked'));

            });

            $("#deleteAll").click(function() {
                if ($('#driverTable .is_open:checked').length) {
                    if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                        jQuery("#data-table_processing").show();
                        $('#driverTable .is_open:checked').each(async function() {
                            var dataId = $(this).attr('dataId');
                            const car_info = database.collection('users').doc(dataId).get()
                                .then(async function(querySnapshot) {
                                    const data = querySnapshot.data();
                                    if(data.carInfo != null){
                                        const car_image = data.carInfo.car_image;
                                        if (car_image.length > 0) {
                                            for (var i = 0; i < car_image.length; i++) {
                                                deleteImageFromBucket(car_image[i]);
                                            }
                                        }
                                    }
                                });

                            deleteDocumentWithImage('users', dataId, 'carPictureURL', '', 'profilePictureURL', 'carProofPictureURL', 'driverProofPictureURL')
                                .then(() => {
                                    return deleteDriverData(dataId);
                                })
                                .then(result => {
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 7000);
                                })
                                .catch(error => {
                                    console.error("Error occurred:", error);
                                });

                        });
                    }
                } else {
                    alert("{{ trans('lang.select_delete_alert') }}");
                }
            });

            async function deleteDriverData(driverId) {

                await database.collection('driver_payouts').where('driverID', '==', driverId).get().then(async function(snapshotsItem) {

                    if (snapshotsItem.docs.length > 0) {
                        snapshotsItem.docs.forEach((temData) => {
                            var item_data = temData.data();

                            database.collection('driver_payouts').doc(item_data.id).delete().then(function() {

                            });
                        });
                    }

                });

                //delete user from authentication
                var dataObject = {
                    "data": {
                        "uid": driverId
                    }
                };
                var projectId = '<?php echo env('FIREBASE_PROJECT_ID'); ?>';
                jQuery.ajax({
                    url: 'https://us-central1-' + projectId + '.cloudfunctions.net/deleteUser',
                    method: 'POST',
                    contentType: "application/json; charset=utf-8",
                    data: JSON.stringify(dataObject),
                    success: function(data) {
                        console.log('Delete user success:', data.result);
                    },
                    error: function(xhr, status, error) {
                        var responseText = JSON.parse(xhr.responseText);
                        console.log('Delete user error:', responseText.error);
                    }
                });
            }

            $(document.body).on('click', '.redirecttopage', function() {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });


            $(document).on("click", "a[name='driver-delete']", function(e) {
                var id = this.id;
                jQuery("#data-table_processing").show();
                const car_info = database.collection('users').doc(id).get()
                    .then(async function(querySnapshot) {
                        const data = querySnapshot.data();
                        if(data.carInfo != null){
                            const car_image = data.carInfo.car_image;
                            if (car_image.length > 0) {
                                for (var i = 0; i < car_image.length; i++) {
                                    deleteImageFromBucket(car_image[i]);
                                }
                            }
                        }
                    });

                deleteDocumentWithImage('users', id, 'carPictureURL', '', 'profilePictureURL', 'carProofPictureURL', 'driverProofPictureURL')
                    .then(() => {
                        return deleteDriverData(id);
                    })
                    .then(result => {
                        setTimeout(function() {
                            window.location.reload();
                        }, 7000);
                    })
                    .catch(error => {
                        console.error("Error occurred:", error);
                    });
            });

            var rows = document.getElementsByTagName("table")[0].rows;
        </script>
    @endsection