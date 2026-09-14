@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <div class="d-flex top-title-section justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/wallet.png') }}"></span>
                        <h3 class="mb-0">{{ trans('lang.wallet_transaction_plural') }} <span class="userTitle"></span></h3>
                        <span class="counter ml-3 total_count"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.wallet_transaction_plural') }}</li>
                </ol>
            </div>
            <div>
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
                                <li>
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
                                <li class="active">
                                    <a href="{{route('owners.walletTransaction',$id)}}"
                                        class="wallet_transaction"><i class="ri-wallet-line"></i>{{trans('lang.wallet_transaction')}}</a>
                                </li>
                            </ul>
                        </div>  
                        <?php } ?>
                        <div class="card border">
                            <div class="card-header d-flex justify-content-between align-items-center border-0">
                                <div class="card-header-title">
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.wallet_transaction_plural') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.wallet_transactions_table_text') }}</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="walletTransactionTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label></th>
                                                <?php if ($id == '') { ?>
                                                <th>{{ trans('lang.users') }}</th>
                                                <th>{{ trans('lang.role') }}</th>
                                                <?php } ?>
                                                <th>{{ trans('lang.amount') }}</th>
                                                <th>{{ trans('lang.date') }}</th>
                                                <th>{{ trans('lang.note') }}</th>
                                                <th>{{ trans('lang.payment_method') }}</th>
                                                <th>{{ trans('lang.payment_status') }}</th>
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
    <script>
        var database = firebase.firestore();
        var id = '<?php echo $id; ?>';
        var offest = 1;
        var pagesize = 10;
        var end = null;
        var endarray = [];
        var start = null;
        var user_number = [];
        var vendorId = '';
        var refData = database.collection('wallet');
        var search = jQuery("#search").val();
        var placeholderImage = '';
        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })
        $(document.body).on('keyup', '#search', function() {
            search = jQuery(this).val();
        });
        var id = '{{ $id }}';
       
        var wallet_route = "{{ route('owners.walletTransaction', 'id') }}";
       
        var append_list = '';
        var currentCurrency = '';
        var currencyAtRight = false;
        var decimal_degits = 0;
        var refCurrency = regionCurrencyRef();
        refCurrency.get().then(async function(snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        });
        $(document).ready(async function() {
            
            if (id) {
                ref = refData.where('user_id', '==', id).orderBy('date', 'desc');
                $(".wallet_transaction").attr("href", wallet_route.replace('id', "{{ $id }}"));
            } else {
                ref = refData.orderBy('date', 'desc');
            }
            if (id) {
                var username = database.collection('users').where('id', '==', id);
                username.get().then(async function(snapshots) {
                    if (snapshots.empty) {
                        return;
                    }
                    var driver = snapshots.docs[0].data();
                    $(".userTitle").text(' - ' + driver.firstName + " " + driver.lastName);
                   
                    if (driver.serviceType == "cab-service") {
                        var url = "{{route('owner.rides','ownerId')}}";
                        url = url.replace('ownerId', id);
                        $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i>{{ trans('lang.order_plural') }}</a>');
                    } else if (driver.serviceType == "rental-service") {
                        var url = "{{route('rental.orders.owner','id')}}";
                        url = url.replace("id", id);
                        $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i>{{ trans('lang.order_plural') }}</a>');
                    } else if (driver.serviceType == "delivery-service" || driver.serviceType == "ecommerce-service") {
                        var url = "{{route('orders.owner','id')}}";
                        url = url.replace("id",  id);
                        $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i>{{ trans('lang.order_plural') }}</a>');
                    } else if (driver.serviceType == "parcel_delivery") {
                        var url = "{{route('parcel_orders.owner','id')}}";
                        url = url.replace("id", id);
                        $('.service_type_orders').html('<a href="' + url + '"><i class="ri-shopping-bag-line"></i>{{ trans('lang.order_plural') }}</a>');
                    }
                });
            }
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
            var fieldConfig = {
                columns: [
                    <?php if ($id == '') { ?> {
                        key: 'Name',
                        header: "{{ trans('lang.user') }}"
                    },
                    {
                        key: 'role',
                        header: "{{ trans('lang.role') }}"
                    },
                    <?php } ?>
                    {
                        key: 'amount',
                        header: "{{ trans('lang.amount') }}"
                    },
                    {
                        key: 'payment_method',
                        header: "{{ trans('lang.payment_method') }}"
                    },
                    {
                        key: 'payment_status',
                        header: "{{ trans('lang.payment_status') }}"
                    },
                    {
                        key: 'date',
                        header: "{{ trans('lang.date') }}"
                    },
                ],
                fileName: "{{ trans('lang.wallet_transaction_plural') }}",
            };
            jQuery("#data-table_processing").show();
            const table = $('#walletTransactionTable').DataTable({
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
                    var orderableColumns = (id == '') ? ['', 'Name', 'role', 'amount', 'date', 'note', 'payment_method', 'payment_status', ''] : ['', 'amount', 'date', 'note', 'payment_method', 'payment_status', '']; // Ensure this matches the actual column names
                    const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                    if (searchValue.length >= 3 || searchValue.length === 0) {
                        $('#data-table_processing').show();
                    }
                    await ref.get().then(async function(querySnapshot) {
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
                        await Promise.all(querySnapshot.docs.map(async (doc) => {
                            let childData = doc.data();
                            childData.id = doc.id; // Ensure the document ID is included in the data
                            var payoutuser = await payoutuserfunction(childData.user_id);
                            if (payoutuser != ' ') {
                                var name = '';
                                if (payoutuser.hasOwnProperty('firstName')) {
                                    name = payoutuser.firstName;
                                }
                                if (payoutuser.hasOwnProperty('lastName')) {
                                    name = name + ' ' + payoutuser.lastName;
                                }
                                childData.Name = name;
                                if (payoutuser.hasOwnProperty('role')) {
                                    childData.role = payoutuser['role'];
                                } else
                                {
                                    childData.role = ' ';
                                }
                                var amount = 0.00;
                                if (childData.hasOwnProperty('amount') && childData.amount && childData.amount != '') {
                                    amount = childData.amount;
                                }
                                if (!isNaN(amount)) {
                                    amount = parseFloat(amount).toFixed(decimal_degits);
                                }
                                if ((childData.hasOwnProperty('isTopUp') && childData.isTopUp) || (childData.payment_method == "Cancelled Order Payment")) {
                                    if (currencyAtRight) {
                                        childData.amount = parseFloat(amount).toFixed(decimal_degits) + '' + currentCurrency;
                                    } else {
                                        childData.amount = currentCurrency + '' + parseFloat(amount).toFixed(decimal_degits);
                                    }
                                } else if (childData.hasOwnProperty('isTopUp') && !childData.isTopUp) {
                                    if (currencyAtRight) {
                                        childData.amount = '(-' + parseFloat(amount).toFixed(decimal_degits) + '' + currentCurrency + ')';
                                    } else {
                                        childData.amount = '(-' + currentCurrency + '' + parseFloat(amount).toFixed(decimal_degits) + ')';
                                    }
                                } else {
                                    if (currencyAtRight) {
                                        childData.amount = parseFloat(childData.amount).toFixed(decimal_degits) + '' + currentCurrency;
                                    } else {
                                        childData.amount = currentCurrency + '' + parseFloat(childData.amount).toFixed(decimal_degits);
                                    }
                                }
                                if (searchValue) {
                                    if (childData.hasOwnProperty("date")) {
                                        try {
                                            date = childData.date.toDate().toDateString();
                                            time = childData.date.toDate().toLocaleTimeString('en-US');
                                        } catch (err) {
                                        }
                                    }
                                    var createdAt = date + '<br> ' + time;
                                    if (
                                        (childData.Name && childData.Name.toString().toLowerCase().includes(searchValue)) ||
                                        (childData.role && childData.role.toString().includes(searchValue)) ||
                                        (childData.amount && childData.amount.toString().toLowerCase().includes(searchValue)) ||
                                        (createdAt && createdAt.toString().toLowerCase().includes(searchValue)) ||
                                        (childData.note && childData.note.toString().toLowerCase().includes(searchValue)) ||
                                        (childData.payment_method && childData.payment_method.toString().toLowerCase().includes(searchValue)) ||
                                        (childData.payment_status && childData.payment_status.toString().toLowerCase().includes(searchValue))
                                    ) {
                                        filteredRecords.push(childData);
                                    }
                                } else {
                                    filteredRecords.push(childData);
                                }
                            }
                        }));
                        filteredRecords.sort((a, b) => {
                            let aValue = a[orderByField];
                            let bValue = b[orderByField];
                            if (orderByField === 'date') {
                                try {
                                    aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                                    bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                                } catch (err) {
                                }
                            }
                            function parseAmount(value) {
                                const cleanedValue = String(value)
                                    .replace(/[^0-9.-]/g, '')
                                    .replace(/(\d+)\.(?=\d{3}\b)/g, '$1');
                                return parseFloat(cleanedValue) || 0;
                            }
                            if (orderByField === 'amount') {
                                aValue = parseAmount(a[orderByField]);
                                bValue = parseAmount(b[orderByField]);
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
                <?php if($id == '') { ?>
                order: [4, 'desc'],
                columnDefs: [{
                        targets: 4,
                        type: 'date',
                        render: function(data) {
                            return data;
                        }
                    },
                    {
                        orderable: false,
                        targets: [0, 6, 7,8]
                    },
                ],
                <?php } else { ?>
                order: [2, "desc"],
                columnDefs: [{
                        targets: 2,
                        type: 'date',
                        render: function(data) {
                            return data;
                        }
                    },
                    {
                        orderable: false,
                        targets: [0, 4, 5, 6]
                    },
                ],
                <?php } ?>
               "language": datatableLang,
                dom: 'lfrtipB',
                buttons: [{
                    extend: 'collection',
                    text: '<i class="mdi mdi-cloud-download"></i> {{ trans('lang.export_as') }}',
                    className: 'btn btn-info',
                    buttons: [{
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
        });
        async function buildHTML(val) {
            var html = [];
            html.push('<input type="checkbox" id="is_open_' + val.id + '" class="is_open" dataId="' + val.id + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + val.id + '" ></label>');
            if (id == "") {
                if (val.user_id) {
                    var role = val.role;
                    var routeuser = "Javascript:void(0)";
                    if (role == "customer") {
                        routeuser = '{{ route('users.view', ':id') }}';
                        routeuser = routeuser.replace(':id', val.user_id);
                    } else if (role == "driver") {
                        routeuser = '{{ route('drivers.view', ':id') }}';
                        routeuser = routeuser.replace(':id', val.user_id);
                    } else if (role == "vendor") {
                        if (val.vendorID != '') {
                            routeuser = '{{ route('stores.view', ':id') }}';
                            routeuser = routeuser.replace(':id', val.vendorID);
                        }
                    }
                    html.push('<td class="user_' + val.user_id + '"><a href="' + routeuser + '">' + val.Name + '</a></td>');
                    html.push('<td class="user_role_' + val.user_id + '" >' + val.role + '</td>');
                } else {
                    html.push('<td></td><td></td>');
                }
            }
            if ((val.hasOwnProperty('isTopUp') && val.isTopUp) || (val.payment_method == "Cancelled Order Payment")) {
                html.push('<td class="text-green">' + val.amount + '</td>');
            } else if (val.hasOwnProperty('isTopUp') && !val.isTopUp) {
                html.push('<td class="text-red">' + val.amount + '</td>');
            } else {
                html.push('<td class="">' + val.amount + '</td>');
            }
            var date = "";
            var time = "";
            try {
                if (val.hasOwnProperty("date")) {
                    date = val.date.toDate().toDateString();
                    time = val.date.toDate().toLocaleTimeString('en-US');
                }
            } catch (err) {
            }
            html.push('<td>' + date + '<br> ' + time + '</td>');
            if (val.note != undefined && val.note != '') {
                html.push('<td>' + val.note + '</td>');
            } else {
                html.push('<td></td>');
            }
            var payment_method = '';
            if (val.payment_method) {
                if (val.payment_method == "Stripe" || val.payment_method == "stripe") {
                    image = '{{ asset('images/payment/stripe.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;" src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" >';
                } else if (val.payment_method == "RazorPay" || val.payment_method == "razorPay") {
                    image = '{{ asset('images/payment/razorepay.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;" src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" >';
                } else if (val.payment_method == "Paypal" || val.payment_method == "paypal") {
                    image = '{{ asset('images/payment/paypal.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;" src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                } else if (val.payment_method == "payFast" || val.payment_method == "payFast") {
                    image = '{{ asset('images/payment/payfast.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;" src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                } else if (val.payment_method == "PayStack" || val.payment_method == "payStack") {
                    image = '{{ asset('images/payment/paystack.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;" src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" >';
                } else if (val.payment_method == "FlutterWave" || val.payment_method == "flutterWave") {
                    image = '{{ asset('images/payment/flutter_wave.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;" src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" >';
                } else if (val.payment_method == "Mercado Pago" || val.payment_method == "mercado Pago") {
                    image = '{{ asset('images/payment/marcado_pago.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;" src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                } else if (val.payment_method == "Wallet" || val.payment_method == "wallet") {
                    image = '{{ asset('images/payment/emart_wallet.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;" src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                } else if (val.payment_method == "Paytm" || val.payment_method == "paytm") {
                    image = '{{ asset('images/payment/paytm.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;" src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                } else if (val.payment_method == "Xendit" || val.payment_method == "xendit") {
                    image = '{{ asset('images/xendit.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;" src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                } else if (val.payment_method == "OrangePay" || val.payment_method == "orangepay") {
                    image = '{{ asset('images/orangeMoney.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;"  src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                } else if (val.payment_method == "MidTrans" || val.payment_method == "midtrans") {
                    image = '{{ asset('images/midtrans.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;"  src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                } else if (val.payment_method == "Cancelled Order Payment") {
                    image = '{{ asset('images/payment/cancel_order.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;" src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                } else if (val.payment_method == "Refund Amount") {
                    image = '{{ asset('images/payment/refund_amount.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;" src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                } else if (val.payment_method == "Referral Amount") {
                    image = '{{ asset('images/payment/reffral_amount.png') }}';
                    payment_method = '<img alt="image" style="max-width:100px;" src="' + image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">';
                } else {
                    if (val.payment_method == "tax") {
                        payment_method = "-";
                    } else {
                        payment_method = val.payment_method;
                    }
                }
            }
            html.push('<td class="payment_images">' + payment_method + '</td>');
            if (val.payment_status == 'success') {
                html.push('<td class="success"><span>' + val.payment_status + '</span></td>');
            } else if (val.payment_status == 'undefined') {
                html.push('<td class="undefined"><span>' + val.payment_status + '</span></td>');
            } else if (val.payment_status == 'Refund success') {
                html.push('<td class="refund_success"><span>' + val.payment_status + '</span></td>');
            } else {
                html.push('<td class="refund_success"><span>' + val.payment_status + '</span></td>');
            }
            html.push('<span class="action-btn"><a id="' + val.id + '" class="delete-btn" name="transaction-delete" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a></span>');
            return html;
        }
        $("#is_active").click(function() {
            $("#walletTransactionTable .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function() {
            if ($('#walletTransactionTable .is_open:checked').length) {
                if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                    jQuery("#data-table_processing").show();
                    $('#walletTransactionTable .is_open:checked').each(function() {
                        var dataId = $(this).attr('dataId');
                        database.collection('wallet').doc(dataId).delete().then(function() {
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
        $(document).on("click", "a[name='transaction-delete']", function(e) {
            var id = this.id;
            database.collection('wallet').doc(id).delete().then(function() {
                window.location.reload();
            });
        });
        async function payoutuserfunction(user) {
            var payoutuser = '';
            await database.collection('users').where("id", "==", user).get().then(async function(snapshotss) {
                if (snapshotss.docs[0]) {
                    payoutuser = snapshotss.docs[0].data();
                }
            });
            return payoutuser;
        }
    </script>
@endsection
