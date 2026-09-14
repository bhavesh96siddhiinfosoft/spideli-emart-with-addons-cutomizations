@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <div class="d-flex top-title-section justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/driver.png') }}"></span>
                        <h3 class="mb-0">{{ trans('lang.driver_plural') }} <span class="userTitle"></span></h3>
                        <span class="counter ml-3 total_count"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.driver_table') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="table-list">
                <div class="row">
                    <div class="col-12">
                        <?php if ($id != '') { ?>
                       
                        <div class="menu-tab">
                            <ul>
                                <li>
                                    <a href="{{route('owners.view',$id)}}" class="basic"><i class="ri-list-indefinite"></i>{{trans('lang.tab_basic')}}</a>
                                </li>
                                <li class="active">
                                    <a href="{{route('owner.driver.list',$id)}}" class="payout"><i class="ri-group-3-fill"></i>{{trans('lang.driver_plural')}}</a>
                                </li>
                                <li class="service_type_orders">
                                </li>
                                <li>
                                    <a href="{{route('owners.payouts',$id)}}" class="payout"><i class="ri-bank-card-line"></i>{{trans('lang.tab_payouts')}}</a>
                                </li>
                                <li>
                                    <a href="{{route('payoutRequests.owners.view',$id)}}" class="vendor_payout"><i class="ri-refund-line"></i>{{trans('lang.tab_payout_request')}}</a>
                                </li>
                                <li >
                                    <a href="{{route('owners.walletTransaction',$id)}}"
                                        class="wallet_transaction"><i class="ri-wallet-line"></i>{{trans('lang.wallet_transaction')}}</a>
                                </li>
                            </ul>
                        </div>  
                        <?php } ?>
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.driver_table') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.driver_table_text') }}</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="driverTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <?php if (in_array('drivers.delete', json_decode(@session('user_permissions'), true))) { ?>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label></th>
                                                <?php } ?>
                                                <th>{{ trans('lang.actions') }}</th>
                                                <th>{{ trans('lang.driver_info') }}</th>
                                                <th>{{ trans('lang.service_type') }}</th>
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
        var sectionType = getCookie('service_type') || ''; 

        var user_permissions = '<?php echo @session('user_permissions'); ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkDeletePermission = false;
        var id = "{{$id}}";     
        if (
            $.inArray('drivers.delete', user_permissions) >= 0
        ) {
            checkDeletePermission = true;
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
            ref = database.collection('users').where("role", "in", ["customer"]);
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
            if (status) {
                ref = (status == "active") ? ref.where('active', '==', true) : ref.where('active', '==', false);
            }
            $('#driverTable').DataTable().ajax.reload();
        });   


        var database = firebase.firestore();

        var ref = database.collection('users').where("role", "==", "driver").where('ownerId','==', id).orderBy('createdAt', 'desc');
        
        var alldriver = database.collection('users').where("role", "==", "driver").where('ownerId','==', id).orderBy('createdAt', 'desc');
        var placeholderImage = '';
        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })

        var append_list = '';
        var serviceRef = database.collection('services');

        const serviceLabels = {
            "cab-service": "Cab Service",
            "parcel_delivery": "Parcel Delivery Service",
            "rental-service": "Rental Service",
            "delivery-service": "Multivendor Delivery Service",
            "ecommerce-service": "Ecommerce Service",
            "ondemand-service": "On Demand Service"
        };

        $(document).ready(async function() {          

            jQuery("#data-table_processing").show();

            let userRef = database.collection('users').doc(id);
            let userDoc = await userRef.get();

            if (userDoc.exists) {
                let userData = userDoc.data();
                let firstName = userData.firstName || '';
                let lastName = userData.lastName || '';
                $(".userTitle").text(' - ' + firstName + " " + lastName);

                let serviceTypes = userData.serviceTypes || (userData.serviceType ? [userData.serviceType] : []);

                if (serviceTypes.includes("cab-service") && sectionType == "cab-service") {
                    var url = "{{route('owner.rides','ownerId')}}";
                    url = url.replace('ownerId', id);
                    $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i>{{ trans('lang.order_plural') }}</a>');
                }else if (serviceTypes.includes("rental-service") && sectionType == "rental-service") {
                    var url = "{{route('rental.orders.owner','id')}}";
                    url = url.replace("id", id);
                    $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i>{{ trans('lang.order_plural') }}</a>');
                 } else if ((serviceTypes.includes("delivery-service") && sectionType == "delivery-service") || (serviceTypes.includes("ecommerce-service") && sectionType == "ecommerce-service")) {
                    var url = "{{route('orders.owner','id')}}";
                    url = url.replace("id",  id);
                    $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i>{{ trans('lang.order_plural') }}</a>');
                } else if (serviceTypes.includes("parcel_delivery") && sectionType == "parcel_delivery") {
                    var url = "{{route('parcel_orders.owner','id')}}";
                    url = url.replace("id", id);
                    $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i>{{ trans('lang.order_plural') }}</a>');
                }
            
            } else {
                console.log("No user found with ID:", id);
            }
            

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

                    var orderableColumns = (checkDeletePermission) ? ['', '', 'name', 'serviceName', '', '', 'createdAt', '', ''] : ['', 'name', 'serviceName', '', '', 'createdAt', '', ''];

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
                            if (!childData.hasOwnProperty('vendorID') || (childData.hasOwnProperty('vendorID') && (childData.vendorID == '' || childData.vendorID == null))) {

                                childData.id = doc.id; // Ensure the document ID is included in the data
                                childData.name = childData.firstName + ' ' + childData.lastName;
                                childData.serviceName = serviceNames[childData.serviceType] || '-';

                                if (searchValue) {
                                    var date = '';
                                    var time = '';
                                    if (childData.hasOwnProperty("createdAt")) {
                                        try {
                                            date = childData.createdAt.toDate().toDateString();
                                            time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                                        } catch (err) {}
                                    }
                                    var createdAt = date + '<br> ' + time;
                                    if (
                                        (childData.name && childData.name.toString().toLowerCase().includes(searchValue)) ||
                                        (childData.serviceName && childData.serviceName.toString().toLowerCase().includes(searchValue)) ||
                                        (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1) /* || (childData.totalOrders && childData.totalOrders.toString().toLowerCase().includes(searchValue)) */
                                    ) {
                                        filteredRecords.push(childData);
                                    }
                                } else {
                                    filteredRecords.push(childData);
                                }
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
                            childData.totalOrders = await orderDetails(childData.id, childData.serviceType);
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
                order: (checkDeletePermission) ? [6, 'desc'] : [5, 'desc'],
                columnDefs: [{
                        orderable: false,
                        targets: (checkDeletePermission) ? [0, 1, 4, 5, 7] : [0, 3, 4, 6],
                    },
                    {
                        type: 'date',
                        render: function(data) {
                            return data;
                        },
                        targets: (checkDeletePermission) ? [6] : [5],
                    }

                ],
               "language": datatableLang,
                dom: 'lfrtipB',
                buttons: [{
                    extend: 'collection',
                    text: '<i class="mdi mdi-cloud-download"></i> {{ trans('lang.export_as') }}',
                    className: 'btn btn-info',
                    buttons: [{
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

        async function buildHTML(val) {
            var html = [];
            var id = val.id;
            var route1 = '{{ route('drivers.edit', ':id') }}';
            route1 = route1.replace(':id', id);

            var driverView = '{{ route('drivers.view', ':id') }}';
            driverView = driverView.replace(':id', id);
             var document_list_view = "{{ route('drivers.document', ':id') }}";
            document_list_view = document_list_view.replace(':id', val.id);


            if (checkDeletePermission) {
                html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                    'for="is_open_' + id + '" ></label></td>');
            }
            var actionHtml = '';
            var payoutRequests = '{{ route('users.walletstransaction', ':id') }}';
            payoutRequests = payoutRequests.replace(':id', 'driverID=' + val.id);
            actionHtml += '<span class="action-btn"><a href="' + driverView + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view') }}"><i class="mdi mdi-eye"></i></a><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
            if (checkDeletePermission) {
                actionHtml += '<a id="' + val.id + '" name="driver-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
            }
            actionHtml += '</span>';
            html.push(actionHtml);

            if (val.profilePictureURL == '') {
                html.push('<td><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image"></td> ' + ' <a data-url="' + driverView + '" href="' + driverView + '" class="redirecttopage left_space">' + val.firstName + ' ' + val.lastName + '</a>');
            } else {
                if (val.profilePictureURL) {
                    photo = val.profilePictureURL;
                } else {
                    photo = placeholderImage;
                }
                html.push('<td><img class="rounded" style="width:50px" src="' + photo + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"></td>' + '<a data-url="' + driverView + '" href="' + driverView + '" class="redirecttopage left_space">' + val.firstName + ' ' + val.lastName + '</a>');
            }

            let serviceTypes = val.serviceTypes || (val.serviceType ? [val.serviceType] : []);
            if (serviceTypes) {
                let serviceTypeText = serviceTypes.map(type => serviceLabels[type] || type).join(", ");
                html.push('<td>' + serviceTypeText + '</td>');
            } else {
                html.push('-');

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
           
            
            if (serviceTypes) {

                var url = "Javascript:void(0)";
                
                 if (serviceTypes.includes("cab-service") && sectionType == "cab-service") {
                    var url = "{{route('owner.rides','ownerId')}}";
                    url = url.replace('ownerId', id);
                }else if (serviceTypes.includes("rental-service") && sectionType == "rental-service") {
                    var url = "{{route('rental.orders.owner','id')}}";
                    url = url.replace("id", id);
                } else if ((serviceTypes.includes("delivery-service") && sectionType == "delivery-service") || (serviceTypes.includes("ecommerce-service") && sectionType == "ecommerce-service")) {
                    var url = "{{route('orders.owner','id')}}";
                    url = url.replace("id",  id);
                } else if (serviceTypes.includes("parcel_delivery") && sectionType == "parcel_delivery") {
                    var url = "{{route('parcel_orders.owner','id')}}";
                    url = url.replace("id", id);
                }

                html.push(val.totalOrders > 0 ? ('<a href="' + url + '">' + val.totalOrders + '</a>') : val.totalOrders);

            } else {
                html.push('');
            }


            var payoutRequests = '{{ route('users.walletstransaction', ':id') }}';
            payoutRequests = payoutRequests.replace(':id', 'driverID=' + val.id);

          
            return html;
        }

        async function orderDetails(driver, type) {
            var count_order_complete = 0;

            if (type == "cab-service") {

                await database.collection('rides').where('driverId', '==', driver).get().then(async function(orderSnapshots) {
                    count_order_complete = orderSnapshots.docs.length;

                });

            } else if (type == "rental-service") {
                await database.collection('rental_orders').where('driverId', '==', driver).get().then(async function(orderSnapshots) {
                    count_order_complete = orderSnapshots.docs.length;

                });

            } else if (type == "delivery-service" || type == "ecommerce-service") {
                await database.collection('vendor_orders').where('driverID', '==', driver).get().then(async function(orderSnapshots) {
                    count_order_complete = orderSnapshots.docs.length;

                });

            } else if (type == "parcel_delivery") {
                await database.collection('parcel_orders').where('driverId', '==', driver).get().then(async function(orderSnapshots) {
                    count_order_complete = orderSnapshots.docs.length;
                });

            }

            return count_order_complete;
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
                                const car_image = data.carInfo.car_image;
                                if (car_image.length > 0) {
                                    for (var i = 0; i < car_image.length; i++) {
                                        deleteImageFromBucket(car_image[i]);
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

        async function serviceTypes(service) {
            var serviceTypes = '';

            await database.collection('services').where("flag", "==", service).get().then(async function(snapshotservice) {

                if (snapshotservice.docs[0]) {
                    var ride_data = snapshotservice.docs[0].data();
                    serviceTypes = ride_data.name;
                } else {}
            });
            return serviceTypes;
        }


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
                    const car_image = data.carInfo.car_image;
                    if (car_image.length > 0) {
                        for (var i = 0; i < car_image.length; i++) {
                            deleteImageFromBucket(car_image[i]);
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
