@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.store_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.vendor_table') }}</li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/store_list.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.store_plural') }}</h3>
                                <span class="counter ml-3 total_count"></span>
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
                                    <select class="form-control cuisine_selector filteredRecords">
                                        <option value="" disabled selected>{{ trans('lang.select_categoty') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                                    <h4 class="text-dark-2 mb-1 h4 rest_count">00</h4>
                                                    <p class="mb-0 small text-dark-2">{{ trans('lang.dashboard_total_stores') }}</p>
                                                </div>
                                                <span class="box-icon ab"><img src="{{ asset('images/restaurant_icon.png') }}"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card card-box-with-icon bg--5">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="card-box-with-content">
                                                    <h4 class="text-dark-2 mb-1 h4 rest_active_count">00</h4>
                                                    <p class="mb-0 small text-dark-2">{{ trans('lang.active_restaurants') }}</p>
                                                </div>
                                                <span class="box-icon ab"><img src="{{ asset('images/active_restaurant.png') }}"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card card-box-with-icon bg--8">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="card-box-with-content">
                                                    <h4 class="text-dark-2 mb-1 h4 rest_inactive_count">00</h4>
                                                    <p class="mb-0 small text-dark-2">{{ trans('lang.inactive_restaurants') }}</p>
                                                </div>
                                                <span class="box-icon ab"><img src="{{ asset('images/inactive_restaurant.png') }}"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card card-box-with-icon bg--6">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div class="card-box-with-content">
                                                    <h4 class="text-dark-2 mb-1 h4 new_joined_rest">00</h4>
                                                    <p class="mb-0 small text-dark-2">{{ trans('lang.new_joined_restaurants') }}</p>
                                                </div>
                                                <span class="box-icon ab"><img src="{{ asset('images/new_restaurant.png') }}"></span>
                                            </div>
                                        </div>
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
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.vendor_table') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.store_table_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('stores.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.create_vendor') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="storeTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <?php if (in_array('stores.delete', json_decode(@session('user_permissions'),true))) { ?>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label></th>
                                                <?php } ?>
                                                <th>{{ trans('lang.actions') }}</th>
                                                <th>{{ trans('lang.store_info') }}</th>
                                                <th>{{ trans('lang.vendor_phone') }}</th>
                                                <th>{{ trans('lang.date') }}</th>
                                                <th>{{ trans('lang.item') }}</th>
                                                <th>{{ trans('lang.order_plural') }}</th>
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
     <div class="modal fade" id="create_vendor" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered notification-main" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{trans('lang.copy_vendor')}}
                        <span id="vendor_title_lable"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="data-table_processing2"
                            class="dataTables_processing panel panel-default"
                            style="display: none;">{{trans('lang.processing')}}
                    </div>
                    <div class="error_top"></div>
                    <!-- Form -->
                    <div class="form-row">
                        <div class="col-md-12 form-group">
                            <label class="form-label">{{trans('lang.first_name')}}</label>
                            <div class="input-group">
                                <input placeholder="Name" type="text" id="user_name"
                                        class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">{{trans('lang.last_name')}}</label>
                            <div class="input-group">
                                <input placeholder="Name" type="text" id="user_last_name"
                                        class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">{{trans('lang.vendor_title')}}</label>
                            <div class="input-group">
                                <input placeholder="Vendor Title" type="text" id="vendor_title"
                                        class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12 form-group"><label
                                class="form-label">{{trans('lang.email')}}</label><input
                                placeholder="Email" value="" id="user_email" type="text"
                                class="form-control"></div>
                        <div class="col-md-12 form-group"><label
                                class="form-label">{{trans('lang.password')}}</label><input
                                placeholder="Password" id="user_password" type="password"
                                class="form-control">
                        </div>
                    </div>
                    <!-- Form -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary"
                            id="create_vendor_submit">{{trans('lang.create')}}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    <script type="text/javascript">
        var database = firebase.firestore();
        var user_permissions = '<?php echo @session('user_permissions'); ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkDeletePermission = false;
        var checkCopyPermission = false;
        var active_id = getCookie('section_id');
        var createdAt = firebase.firestore.FieldValue.serverTimestamp();

        if ($.inArray('stores.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }
        if ($.inArray('stores.copy', user_permissions) >= 0) {
            checkCopyPermission = true;
        }
        $(document).on('click', '#create_vendor .close, #create_vendor [data-dismiss="modal"], #create_vendor [data-bs-dismiss="modal"]', function () {
            $('#create_vendor').modal('hide');
        });
        var refData = regionScoped(database.collection('vendors').where('section_id', '==', active_id));
        var ref = refData.orderBy('createdAt', 'desc');
        var userData = [];
        var vendorData = [];
        var vendorProducts = [];
        var placeholderImage = '';
        var placeholder = database.collection('settings').doc('placeHolderImage');
        
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })
        database.collection('vendor_categories').where('section_id', '==', active_id).where('publish', '==', true).get().then(async function(snapshots) {
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                $('.cuisine_selector').append($("<option></option>")
                    .attr("value", data.id)
                    .text(data.title));
            })
        });

        var initialRef = ref;
        $('select').change(async function() {
            var businessModelValue = $('.business_model_selector').val();
            var cuisineValue = $('.cuisine_selector').val();
           
            refData = initialRef;
           

            if (cuisineValue) {
                refData = refData.where('categoryID', 'array-contains', cuisineValue);
            }
            ref = refData;
            $('#storeTable').DataTable().ajax.reload();
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
        var fieldConfig = {
            columns: [
                {
                    key: 'title',
                    header: "{{ trans('lang.store_info') }}"
                },
                {
                    key: 'exportPhone',
                    header: "{{ trans('lang.phone') }}",
                    cell: row => (row.hasPlusSign ? `+${row.maskedPhone}` : row.maskedPhone)
                },
                {
                    key: 'createdAt',
                    header: "{{ trans('lang.date') }}"
                },
                {
                    key: 'items',
                    header: "{{ trans('lang.item') }}"
                },
                {
                    key: 'orders',
                    header: "{{ trans('lang.order_plural') }}"
                },
            ],
            fileName: "{{ trans('lang.vendor_table') }}",
        };
        $(document).ready(function() {
            $('.cuisine_selector').select2({
                placeholder: "{{ trans('lang.select_categoty') }}",
                minimumResultsForSearch: Infinity,
                allowClear: true
            });
          
            $('select').on("select2:unselecting", function(e) {
                var self = $(this);
                setTimeout(function() {
                    self.select2('close');
                }, 0);
            });
            jQuery("#data-table_processing").show();
            const table = $('#storeTable').DataTable({
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
                    const orderableColumns = (checkDeletePermission) ? ['', '', 'title',  'phonenumber', 'createdAt', 'items', 'orders'] : ['', 'title',  'phonenumber', 'createdAt', 'items', 'orders']; // Ensure this matches the actual column names
                    const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                    if (searchValue.length >= 3 || searchValue.length === 0) {
                        $('#data-table_processing').show();
                    }
                    await ref.get().then(async function(querySnapshot) {
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
                        await Promise.all(querySnapshot.docs.map(async (doc) => {
                            let childData = doc.data();
                            childData.phone = (childData.phonenumber != '' && childData.phonenumber != null && childData.phonenumber.slice(0, 1) == '+') ? childData.phonenumber.slice(1) : childData.phonenumber;
                            childData.maskedPhone = EditPhoneNumber(childData.phone);
                            childData.hasPlusSign = childData.phonenumber.startsWith('+');
                            childData.exportPhone = childData.hasPlusSign ? `+${childData.maskedPhone}` : childData.maskedPhone;
                            childData.id = doc.id; // Ensure the document ID is included in the data
                            if (childData.id) {
                                childData.orders = await getTotalOrders(childData.id);
                                childData.items = await getTotalProduct(childData.id);
                            } else {
                                childData.orders = 0;
                                childData.items = 0;
                                childData.foods = 0;
                            }
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
                                    (childData.title && childData.title.toLowerCase().toString().includes(searchValue)) ||
                                    (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1) ||
                                    (childData.email && childData.email.toLowerCase().toString().includes(searchValue)) ||
                                    (childData.phone && childData.phone.toLowerCase().toString().includes(searchValue)) 
                                   
                                ) {
                                    if (childData.title != '') {
                                        filteredRecords.push(childData);
                                    }
                                }
                            } else {
                                if (childData.title != '') {
                                    filteredRecords.push(childData);
                                }
                            }
                        }));
                        filteredRecords.sort((a, b) => {
                            let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase().trim() : '';
                            let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase().trim() : '';
                            if (orderByField === 'createdAt') {
                                try {
                                    aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                                    bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                                } catch (err) {
                                }
                            }
                            if (orderByField === 'items') {
                                aValue = a[orderByField] ? parseFloat(String(a[orderByField]).replace(/[^0-9.]/g, '')) || 0 : 0;
                                bValue = b[orderByField] ? parseFloat(String(b[orderByField]).replace(/[^0-9.]/g, '')) || 0 : 0;
                            }
                            if (orderByField === 'orders') {
                                aValue = a[orderByField] ? parseFloat(String(a[orderByField]).replace(/[^0-9.]/g, '')) || 0 : 0;
                                bValue = b[orderByField] ? parseFloat(String(b[orderByField]).replace(/[^0-9.]/g, '')) || 0 : 0;
                            }
                            if (orderDirection === 'asc') {
                                return (aValue > bValue) ? 1 : -1;
                            } else {
                                return (aValue < bValue) ? 1 : -1;
                            }
                        });
                        const totalRecords = filteredRecords.length;
                        $('.total_count').text(totalRecords);
                        let active_rest = 0;
                        let inactive_rest = 0;
                        let new_joined_rest = 0;
                        const today = new Date().setHours(0, 0, 0, 0);
                        await Promise.all(filteredRecords.map(async (childData) => {
                            var isActive = false;
                            if (childData.author) {
                                const user_id = childData.author;
                                isActive = await vendorStatus(user_id);
                            }
                            if (isActive) {
                                active_rest += 1;
                            } else {
                                inactive_rest += 1;
                            }
                            if (childData.createdAt && new Date(childData.createdAt.seconds * 1000).setHours(0, 0, 0, 0) === today) {
                                new_joined_rest += 1;
                            }
                        }));
                        $('.rest_count').text(totalRecords);
                        $('.rest_active_count').text(active_rest);
                        $('.rest_inactive_count').text(inactive_rest);
                        $('.new_joined_rest').text(new_joined_rest);
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
                order: (checkDeletePermission) ? [
                    [4, 'desc']
                ] : [
                    [3, 'desc']
                ],
                columnDefs: [
                    {
                        targets: (checkDeletePermission) ? 4 : 3,
                        type: 'date',
                        render: function(data) {
                            return data;
                        }
                    },
                    {
                        orderable: false,
                        targets: (checkDeletePermission) ? [0, 1] : [0]
                    },
                ],
                "language": datatableLang,

                dom: 'lfrtipB',
                buttons: [
                    {
                        extend: 'collection',
                        text: '<i class="mdi mdi-cloud-download"></i> {{ trans('lang.export_as') }}',
                        className: 'btn btn-info',
                        buttons: [
                            {
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
                    }
                ],
                initComplete: function() {
                    $(".dataTables_filter").append($(".dt-buttons").detach());
                    $('.dataTables_filter input').attr('placeholder', '{{trans("lang.search_here")}}').attr('autocomplete', 'new-password').val('');
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
        })
        async function buildHTML(val) {
            var html = [];
            newdate = '';
            var id = val.id;
            var route1 = '{{ route('stores.edit', ':id') }}';
            route1 = route1.replace(':id', id);
            var route_view = '{{ route('stores.view', ':id') }}';
            route_view = route_view.replace(':id', id);
            if (checkDeletePermission) {
                html.push('<span class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '" author="' + val.author + '"><label class="col-3 control-label"\n' +
                    'for="is_open_' + id + '" ></label></span>');
            }
            var actionHtml = '';
            actionHtml = actionHtml + '<span class="action-btn">';
            var payoutRequests = '{{ route('users.walletstransaction', ':id') }}';
            payoutRequests = payoutRequests.replace(':id', 'storeID=' + val.author);
            actionHtml = actionHtml+'<a href="' + payoutRequests + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.wallet_history') }}"><i class="mdi mdi-wallet" ></i></a>';
          
            if (checkCopyPermission) {
                actionHtml = actionHtml + '<a href="javascript:void(0)" vendor_id="' + val.id + '" author="' + val.author + '" name="vendor-clone" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.copy') }}"><i class="mdi mdi-content-copy"></i></a>';
            }
            actionHtml = actionHtml + '<a href="' + route_view + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view') }}"><i class="mdi mdi-eye"></i></a><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
            if (checkDeletePermission) {
                actionHtml = actionHtml + '<a id="' + val.id + '" author="' + val.author + '" name="delete-btn" class="do_not_delete" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
            }
            actionHtml = actionHtml + '</span>';
            html.push(actionHtml);
            if (val.photo != '') {
                if (val.photo) {
                    photo = val.photo;
                } else {
                    photo = placeholderImage;
                }
                html.push('<img alt="" width="100%" style="width:70px;height:70px;" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image">' + '<a href="' + route_view + '" class="redirecttopage left_space">' + val.title + '</a>');
            } else {
                html.push('<img alt="" width="100%" style="width:70px;height:70px;" src="' + placeholderImage + '" alt="image">' + '<a href="' + route_view + '" class="redirecttopage left_space">' + val.title + '</a>');
            }
        
            if (val.hasOwnProperty('phonenumber')) {
                if (val.phonenumber.includes('+')) {
                    html.push('+' + EditPhoneNumber(val.phonenumber.slice(1)));
                } else {
                    html.push(EditPhoneNumber(val.phonenumber));
                }
            } else {
                html.push('');
            }
            var date = '';
            var time = '';
            if (val.hasOwnProperty("createdAt")) {
                try {
                    date = val.createdAt.toDate().toDateString();
                    time = val.createdAt.toDate().toLocaleTimeString('en-US');
                } catch (err) {
                }
                html.push('<span class="dt-time">' + date + '<br> ' + time + '</span>');
            } else {
                html.push('');
            }
            
            var vendorId = val.id;
            var url = '{{ route('vendors.items', ':id') }}';
            url1 = url.replace(":id", vendorId);
            html.push((val.items > 0 ? '<a  href="' + url1 + '">' + val.items + '</a>' : val.items));
            
            var url = '{{ route('vendors.orders', ':id') }}';
            url2 = url.replace(":id", vendorId);
            html.push((val.orders > 0 ? '<a  href="' + url2 + '">' + val.orders + '</a>' : val.orders));
           
            var active = val.isActive;
            return html;
        }
        async function vendorStatus(id) {
            let status = true;
            await database.collection('users').doc(id).get().then((snapshots) => {
                let data = snapshots.data();
                if (data) {
                    status = data.active;
                }
            });
            return status;
        }
      
        async function getTotalProduct(id) {
            var totalProduct = '';
            await database.collection('vendor_products').where('vendorID', '==', id).get().then(async function(productSnapshots) {
                totalProduct = productSnapshots.docs.length;
            });
            return totalProduct;
        }
        async function getTotalOrders(id) {
            var order_total = '';
            await database.collection('vendor_orders').where('vendorID', '==', id).get().then(async function(productSnapshots) {
                order_total = productSnapshots.docs.length;
            });
            return order_total;
        }
        $("#is_active").click(function() {
            $("#storeTable .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(async function() {
            if ($('#storeTable .is_open:checked').length) {
                if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                    jQuery("#data-table_processing").show();
                    $('#storeTable .is_open:checked').each(async function() {
                            var dataId = $(this).attr('dataId');
                            var author = $(this).attr('author');
                            await deleteDocumentWithImage('vendors', dataId, 'photo', ['vendorMenuPhotos', 'photos'])
                                .then(() => {
                                    const getStoreName = deleteStoreData(dataId);
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 7000);
                                })
                                .catch((error) => {
                                    console.error('Error deleting document with image:', error);
                                });
                        }
                    );
                }
            } else {
                alert("{{ trans('lang.select_delete_alert') }}");
            }
        });
        async function deleteStoreData(storeId) {
            /* Deleting a store must not delete its owner's login unless this was
             * their last store. A vendor may own several, and the account is what
             * the others are reached through - removing it would take them all.
             *
             * The owner is named on the store. The old query asked "whose
             * SELECTED store is this?", which on a multi-store account is the
             * wrong person or nobody. */
            var storeSnapshot = await database.collection('vendors').doc(storeId).get();
            var ownerId = storeSnapshot.exists ? (storeSnapshot.data().author || '') : '';
            var remainingStores = [];

            if (ownerId) {
                var ownedSnapshot = await database.collection('vendors').where('author', '==', ownerId).get();

                ownedSnapshot.docs.forEach(function(doc) {
                    var otherId = doc.data().id || doc.id;

                    if (otherId !== storeId) {
                        remainingStores.push(otherId);
                    }
                });
            }

            if (ownerId && remainingStores.length > 0) {
                /* The owner keeps their account. If the store being deleted was
                 * the one they had selected, move them to one that still exists
                 * so the panel does not open on nothing. */
                var ownerSnapshot = await database.collection('users').doc(ownerId).get();

                if (ownerSnapshot.exists && ownerSnapshot.data().vendorID === storeId) {
                    await database.collection('users').doc(ownerId).update({
                        'vendorID': remainingStores[0]
                    });
                }
            }

            /* Only when this was their last store is the account removed. */
            var ownerDoc = (ownerId && remainingStores.length === 0)
                ? await database.collection('users').doc(ownerId).get() : null;

            await Promise.resolve().then(async function() {
                if (ownerDoc && ownerDoc.exists) {
                    var projectId = '<?php echo env('FIREBASE_PROJECT_ID'); ?>';
                    var item_data = ownerDoc.data();
                    var dataObject = {
                        "data": {
                            "uid": item_data.id
                        }
                    };
                    //delete vendor from mysql
                    await database.collection('settings').doc("Version").get().then(function(snapshot) {
                        var settingData = snapshot.data();
                        if (settingData && settingData.storeUrl) {
                            var siteurl = settingData.storeUrl + "/api/delete-user";
                            var dataObject = {
                                "uuid": item_data.id
                            };
                            jQuery.ajax({
                                url: siteurl,
                                method: 'POST',
                                contentType: "application/json; charset=utf-8",
                                data: JSON.stringify(dataObject),
                                success: function(data) {
                                    console.log('Delete user from sql success:', data);
                                },
                                error: function(error) {
                                    console.log('Delete user from sql error:', error.responseJSON.message);
                                }
                            });
                        }
                    });
                    jQuery.ajax({
                        url: 'https://us-central1-' + projectId + '.cloudfunctions.net/deleteUser',
                        method: 'POST',
                        contentType: "application/json; charset=utf-8",
                        data: JSON.stringify(dataObject),
                        success: async function(data) {
                            console.log('Delete user success:', data.result);
                            await deleteDocumentWithImage('users', item_data.id, 'profilePictureURL');
                        },
                        error: function(xhr, status, error) {
                            var responseText = JSON.parse(xhr.responseText);
                            console.log('Delete user error:', responseText.error);
                        }
                    });
                }
            });
            var productSnapshot = await database.collection('vendor_products').where('vendorID', '==', storeId).get();
            if (!productSnapshot.empty) {
                for (const doc of productSnapshot.docs) {
                    await deleteDocumentWithImage('vendor_products', doc.id, 'photo', 'photos');
                }
            }
            var orderSnapshot = await database.collection('vendor_orders').where('vendorID', '==', storeId).get();
            if (!orderSnapshot.empty) {
                for (const doc of orderSnapshot.docs) {
                    await deleteDocumentWithImage('vendor_orders', doc.id);
                }
            }
            var reviewSnapshot = await database.collection('items_review').where('VendorId', '==', storeId).get();
            if (!reviewSnapshot.empty) {
                for (const doc of reviewSnapshot.docs) {
                    await deleteDocumentWithImage('items_review', doc.id, '', 'photos');
                }
            }
            var couponSnapshot = await database.collection('coupons').where('vendorID', '==', storeId).get();
            if (!couponSnapshot.empty) {
                for (const doc of couponSnapshot.docs) {
                    await deleteDocumentWithImage('coupons', doc.id, 'image');
                }
            }
            var payoutSnapshot = await database.collection('payouts').where('vendorID', '==', storeId).get();
            if (!payoutSnapshot.empty) {
                for (const doc of payoutSnapshot.docs) {
                    await database.collection('payouts').doc(doc.id).delete()
                }
            }
            const storySnapshot = await database.collection('story').where('vendorID', '==', storeId).get();
            if (!storySnapshot.empty) {
                for (const doc of storySnapshot.docs) {
                    await deleteDocumentWithImage('story', doc.id, 'videoThumbnail', 'videoUrl');
                }
            }
            const snapshots = await database.collection('advertisements').where('vendorId', '==', storeId).get();
            if (!snapshots.empty) {
                for (const doc of snapshots.docs) {
                    await deleteDocumentWithImage('advertisements', doc.id);
                }
            }
            const driverSnapshots = await database.collection('users').where('role', '==', 'driver').where('vendorID', '==', storeId).get();
            if (!driverSnapshots.empty) {
                for (const doc of driverSnapshots.docs) {
                    await deleteDocumentWithImage('users', doc.id, 'profilePictureURL');
                }
            }
        }
        $(document.body).on('click', '.redirecttopage', function() {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });
        async function userPhone(author) {
            var userPhones = '';
            await database.collection('users').where("id", "==", author).get().then(async function(snapshotss) {
                if (snapshotss.docs[0]) {
                    var user = snapshotss.docs[0].data();
                    userPhones = user.phoneNumber;
                    if (user.isActive) {
                        jQuery(".active_vendor_" + author + " span").addClass('badge-danger');
                        jQuery(".active_vendor_" + author + " span").text('No');
                    } else {
                        jQuery(".active_vendor_" + author + " span").addClass('badge-success');
                        jQuery(".active_vendor_" + author + " span").text('Yes');
                    }
                } else {
                    jQuery(".phone_" + author).html('');
                    jQuery(".active_vendor_" + author + " span").addClass('badge-success');
                    jQuery(".active_vendor_" + author + " span").text('Yes');
                }
            });
            return userPhones;
        }
        function clickpage(value) {
            setCookie('pagesizes', value, 30);
            location.reload();
        }
        $(document).on("click", "a[name='delete-btn']", async function(e) {
            var id = this.id;
            var author = $(this).attr('author');
            jQuery("#data-table_processing").show();
            await deleteDocumentWithImage('vendors', id, 'photo', ['vendorMenuPhotos', 'photos'])
                .then(() => {
                    return deleteStoreData(id);
                })
                .then(() => {
                    setTimeout(function() {
                        window.location.reload();
                    }, 7000);
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
        });
       
        $(document).on("click", "a[name='vendor-clone']", async function(e) {
            jQuery("#data-table_processing").show();
            var id = $(this).attr('vendor_id');
            var author = $(this).attr('author');
            await database.collection('users').doc(author).get().then(async function(snapshotsusers) {
                userData = snapshotsusers.data();
            });
            await database.collection('vendors').doc(id).get().then(async function(snapshotsvendors) {
                vendorData = snapshotsvendors.data();
            });
            await database.collection('vendor_products').where('vendorID', '==', id).get().then(async function(snapshotsproducts) {
                vendorProducts = [];
                snapshotsproducts.docs.forEach(async (product) => {
                    vendorProducts.push(product.data());
                });
            });
            if (userData && vendorData) {
                jQuery("#create_vendor").modal('show');
                jQuery("#vendor_title_lable").text(vendorData.title);
                jQuery("#data-table_processing").hide();
            }
        });
        $(document).on("click", "#create_vendor_submit", async function(e) {
            var vendor_id = database.collection("tmp").doc().id;
            if (userData && vendorData) {
                var vendor_title = jQuery("#vendor_title").val();
                var userFirstName = jQuery("#user_name").val();
                var userLastName = jQuery("#user_last_name").val();
                var email = jQuery("#user_email").val();
                var password = jQuery("#user_password").val();
                if (userFirstName == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.user_name_required') }}</p>");
                    window.scrollTo(0, 0);
                } else if (userLastName == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.user_last_name_required') }}</p>");
                    window.scrollTo(0, 0);
                } else if (vendor_title == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.vendor_title_required') }}</p>");
                    window.scrollTo(0, 0);
                } else if (email == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.user_email_required') }}</p>");
                    window.scrollTo(0, 0);
                } else if (password == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.enter_owners_password_error') }}</p>");
                    window.scrollTo(0, 0);
                } else {
                    jQuery("#data-table_processing").show();
                    firebase.auth().createUserWithEmailAndPassword(email, password).then(async function(firebaseUser) {
                        var user_id = firebaseUser.user.uid;
                        userData.email = email;
                        userData.firstName = userFirstName;
                        userData.lastName = userLastName;
                        userData.id = user_id;
                        userData.vendorID = vendor_id;
                        userData.createdAt = createdAt;
                        userData.wallet_amount = 0;
                        /* A store keeps its own balance, so it starts at zero
                         * rather than being absent and read as undefined. */
                        vendorData.wallet_amount = 0;
                        vendorData.author = user_id;
                        vendorData.authorName = userFirstName + ' ' + userLastName;
                        vendorData.title = vendor_title;
                        vendorData.id = vendor_id;
                        coordinates = new firebase.firestore.GeoPoint(vendorData.latitude, vendorData.longitude);
                        vendorData.coordinates = coordinates;
                        vendorData.createdAt = createdAt;
                        await database.collection('users').doc(user_id).set(userData).then(async function(result) {
                            await geoFirestore.collection('vendors').doc(vendor_id).set(vendorData).then(async function(result) {
                                if(vendorProducts.length > 0){
                                    var count = 0;
                                    await vendorProducts.forEach(async (product) => {
                                        var product_id = await database.collection("tmp").doc().id;
                                        product.id = product_id;
                                        product.vendorID = vendor_id;
                                        await database.collection('vendor_products').doc(product_id).set(product).then(function(result) {
                                            count++;
                                            if (count == vendorProducts.length) {
                                                jQuery("#data-table_processing").hide();
                                                jQuery("#create_vendor").modal('hide');
                                                Swal.fire({
                                                    title: "{{trans('lang.vendor_created')}}",
                                                    text: "{{trans('lang.vendor_data_copied_successfully')}}",
                                                    icon: 'success',
                                                    confirmButtonText: "{{trans('lang.ok')}}"
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        location.reload();
                                                    }
                                                });
                                            }
                                        });
                                    });
                                }else{
                                    jQuery("#data-table_processing").hide();
                                    jQuery("#create_vendor").modal('hide');
                                    Swal.fire({
                                        title: "{{trans('lang.vendor_created')}}",
                                        text: "{{trans('lang.vendor_data_copied_successfully')}}",
                                        icon: 'success',
                                        confirmButtonText: "{{trans('lang.ok')}}"
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            location.reload();
                                        }
                                    });
                                }
                            });
                        })
                    }).catch(function(error) {
                        $(".error_top").show();
                        jQuery("#data-table_processing").hide();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + error + "</p>");
                    });
                }
            }
        });
    </script>
    
    <style>
        #data-table_processing.page-overlay {
            z-index: 99999 !important;
        }
    </style>
    
@endsection
