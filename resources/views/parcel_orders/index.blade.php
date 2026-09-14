@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.parcel_plural')}} {{trans('lang.order_plural')}}<span class="page-title"></span></h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.parcel_plural')}} {{trans('lang.order_plural')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/service_order.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.parcel_plural')}} {{trans('lang.order_plural')}}</h3>
                        <span class="counter ml-3 total_count"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                            <div class="select-box pl-3">
                                <select class="form-control status_selector filteredRecords">
                                    <option value="" selected>{{trans("lang.status")}}</option>
                                    <option value="Order Placed">{{trans("lang.order_placed")}}</option>
                                    <option value="Order Accepted">{{trans("lang.order_accepted")}}</option>
                                    <option value="Order Rejected">{{trans("lang.order_rejected")}}</option>
                                    <option value="Driver Pending">{{trans("lang.driver_pending")}}</option>
                                    <option value="Driver Rejected">{{trans("lang.driver_rejected")}}</option>
                                    <option value="Order Shipped">{{trans("lang.order_shipped")}}</option>
                                    <option value="Order Completed">{{trans("lang.order_completed")}}</option>
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
                                        <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_total_orders')}}</p>
                                       </div>
                                        <span class="box-icon ab"><img src="{{ asset('images/parcel_order.png') }}"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-box-with-icon bg--5">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                       <div class="card-box-with-content">
                                        <h4 class="text-dark-2 mb-1 h4 placed_count" id="placed_count"></h4>
                                        <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_order_placed')}}</p>
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
                                        <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_order_accepted')}}</p>
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
                                        <p class="mb-0 small text-dark-2">{{trans('lang.dashboard_order_completed')}}</p>
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
       </div>
       <div class="table-list">
       <div class="row">
           <div class="col-12">
                <?php if ($id != '') { ?>
                <div class="menu-tab vendorMenuTab">
                    <ul>
                        <li>
                            <a href="{{route('drivers.view',$id)}}">{{trans('lang.tab_basic')}}</a>
                        </li>
                        <li class="vehicle_tab" style="display:none">
                            <a href="{{route('drivers.vehicle',$id)}}">{{trans('lang.vehicle')}}</a>
                        </li>
                        <li class="active">
                            <a href="{{route('parcel_orders.driver',$id)}}">{{trans('lang.order_plural')}}</a>
                        </li>
                        <li>
                            <a href="{{route('driver.payouts',$id)}}">{{trans('lang.tab_payouts')}}</a>
                        </li>
                        <li>
                            <a href="{{route('payoutRequests.drivers.view',$id)}}" >{{trans('lang.tab_payout_request')}}</a>
                        </li>
                        <li>
                            <a href="{{route('users.walletstransaction',$id)}}"
                                    class="wallet_transaction">{{trans('lang.wallet_transaction')}}</a>
                        </li>
                    </ul>
                </div>
                <?php } ?>
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.parcel_plural')}} {{trans('lang.order_plural')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.parcelorder_table_text')}}</p>
                   </div>             
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="parcelTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                <?php if (in_array('parcel.orders.delete', json_decode(@session('user_permissions'),true))) { ?>
                                    <th class="delete-all">
                                        <input type="checkbox" id="is_active">
                                        <label class="col-3 control-label" for="is_active">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="fa fa-trash"></i> {{trans('lang.all')}}</a>
                                        </label>
                                    </th>
                                    <?php }?>
                                    <th>{{trans('lang.order_id')}}</th>
                                    <th>{{trans('lang.item_review_user_id')}}</th>
                                    @if(!$id)
                                    <th>{{trans('lang.driver')}}</th>
                                    @endif
                                    <th>{{trans('lang.amount')}}</th>
                                    <th>{{trans('lang.date')}}</th>
                                    <th>{{trans('lang.order_order_status_id')}}</th>
                                    <th>{{trans('lang.actions')}}</th>
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

    var section_id = getCookie('section_id') || null;
    var user_permissions = '<?php echo @session('user_permissions') ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    var serviceType = getCookie('service_type');    

    if ($.inArray('parcel.orders.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
    $('.status_selector').select2({
        placeholder: '{{trans("lang.status")}}',  
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
        $('#daterange span').html('{{trans("lang.select_range")}}');
        $('#daterange').daterangepicker({
            autoUpdateInput: false, 
        }, function (start, end) {
            $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $('.filteredRecords').trigger('change'); 
        });
        $('#daterange').on('apply.daterangepicker', function (ev, picker) {
            $('#daterange span').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
            $('.filteredRecords').trigger('change');
        });
        $('#daterange').on('cancel.daterangepicker', function (ev, picker) {
            $('#daterange span').html('{{trans("lang.select_range")}}');
            $('.filteredRecords').trigger('change'); 
        });
    }
    setDate(); 
    
    var database = firebase.firestore();
    var offest = 1;
    var pagesize = 10;
    var end = null;
    var endarray = [];
    var start = null;
    var append_list = '';
    var user_number = [];
    var driverID = '{{$id}}';
    
    var refData = database.collection('parcel_orders');
    var ref = database.collection('parcel_orders').orderBy('createdAt', 'desc');
    
    if(driverID){
        getDriverInfo(driverID);
        var wallet_route = "{{route('users.walletstransaction','id')}}";
        $(".wallet_transaction").attr("href", wallet_route.replace('id', 'driverID='+driverID));
        var refData = database.collection('parcel_orders').where('driverId','==',driverID);
        var ref = database.collection('parcel_orders').where('driverId','==',driverID).orderBy('createdAt', 'desc');
    }

    if(section_id){
        ref = ref.where('sectionId', '==', section_id);
    }
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

    var initialRef=ref;
    $('.filteredRecords').change(async function() {
        var status = $('.status_selector').val();
        var daterangepicker = $('#daterange').data('daterangepicker');
        var refData=initialRef; 
        if(status) {
            refData=refData.where('status','==',status);
        }
        if ($('#daterange span').html() != '{{trans("lang.select_range")}}' && daterangepicker) {
            var from = moment(daterangepicker.startDate).toDate();
            var to = moment(daterangepicker.endDate).toDate();
            if (from && to) { 
                var fromDate = firebase.firestore.Timestamp.fromDate(new Date(from));
                refData = refData.where('createdAt', '>=', fromDate);
                var toDate = firebase.firestore.Timestamp.fromDate(new Date(to));
                refData = refData.where('createdAt', '<=', toDate);
            }
        }
        ref=refData;
        $('#parcelTable').DataTable().ajax.reload();
    });
    
    $(document).ready(function() {
        if(serviceType !== 'delivery-service' && serviceType !== 'parcel_delivery'){
            $('.vehicle_tab').show();
        }else{
            $('.vehicle_tab').hide();
        }
        ref.get().then((snapshot) => {
            jQuery("#order_count").empty();
            jQuery("#order_count").text(snapshot.docs.length);
        });
        ref.where('status', '==', "Order Placed").get().then((snapshot) => {
            jQuery("#placed_count").empty();
            jQuery("#placed_count").text(snapshot.docs.length);
        });
        ref.where('status', '==', "Order Accepted").get().then((snapshot) => {
            jQuery("#accepted_count").empty();
            jQuery("#accepted_count").text(snapshot.docs.length);
        });
        ref.where('status', '==', "Order Completed").get().then((snapshot) => {
            jQuery("#order_completed").empty();
            jQuery("#order_completed").text(snapshot.docs.length);
        });
        var order_status = jQuery('#order_status').val();
        var search = jQuery("#search").val();
        $(document.body).on('click', '.redirecttopage', function() {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });
        jQuery('#search').hide();
        $(document.body).on('change', '#selected_search', function() {
            if (jQuery(this).val() == 'status') {
                jQuery('#order_status').show();
                jQuery('#search').hide();
            } else {
                jQuery('#order_status').hide();
                jQuery('#search').show();
            }
        });
        jQuery("#data-table_processing").show();
        jQuery("#data-table_processing").show();
        $(document).on('click', '.dt-button-collection .dt-button', function () {
            $('.dt-button-collection').hide();
            $('.dt-button-background').hide();
        });
        $(document).on('click', function (event) {
            if (!$(event.target).closest('.dt-button-collection, .dt-buttons').length) {
                $('.dt-button-collection').hide();
                $('.dt-button-background').hide();
            }
        });
        var fieldConfig = {
            columns: [
                { key: 'authorName', header: "{{ trans('lang.author')}}" },
                { key: 'driverName', header: "{{ trans('lang.driver')}}" },
                { key: 'final_amount', header: "{{trans('lang.amount')}}" },
                { key: 'createdAt', header: "{{trans('lang.date')}}" },
                { key: 'status', header: "{{trans('lang.order_order_status_id')}}" },
            ],
            fileName: "{{trans('lang.parcel_plural_orders')}}",
        };
        const table = $('#parcelTable').DataTable({
            pageLength: 10,
            processing: false,
            serverSide: true,
            responsive: true,
            ajax: async function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;
                const orderableColumns =  (checkDeletePermission==true) ? ['','id','authorName','driverName','final_amount','createdAt','status',''] : ['id','authorName','driverName','final_amount','createdAt','status',''];
                const orderByField = orderableColumns[orderColumnIndex];
                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }
                try {
                    const querySnapshot = await ref.get();
                    if (querySnapshot.empty) {
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
                    await Promise.all(querySnapshot.docs.map(async (doc) => {
                        let childData = doc.data();
                        childData.id = doc.id;
                        var date = '';
                        var time = '';
                        if (childData.hasOwnProperty("createdAt") && childData.createdAt != '') {
                            try {
                                date = childData.createdAt.toDate().toDateString();
                                time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                            } catch (err) {
                            }
                        }
                        var createdAt = date + '<br> ' + time ;
                        var authorName = (childData.author != undefined) ? (childData.author.firstName + ' ' + childData.author.lastName) : '';
                        var driverName = (childData.driver != undefined) ? (childData.driver.firstName + ' ' + childData.driver.lastName) : '';
                        childData.authorName = authorName ? authorName : '';
                        childData.driverName = driverName ? driverName : '';
                        var final_amount = 0.00;
                        final_amount = buildParcelTotal(childData);
                        childData.final_amount = final_amount ? final_amount : '';
                        if (searchValue) {
                            if (
                                (childData.id && childData.id.toLowerCase().includes(searchValue)) ||
                                (childData.status && childData.status.toLowerCase().includes(searchValue)) ||
                                (childData.final_amount && childData.final_amount.toLowerCase().includes(searchValue)) ||
                                (authorName && authorName.toLowerCase().includes(searchValue)) ||
                                (driverName && driverName.toLowerCase().includes(searchValue)) ||
                                (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1)
                            ) {
                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    }));
                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase().trim() : '';
                        let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase().trim() : '';
                        if (orderByField === 'createdAt' && a[orderByField] != '' && b[orderByField] != '') {
                            try {
                                aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                                bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                            } catch (err) {
                            }
                        }
                        if (orderByField === 'final_amount') {
                            const parseAmount = (amountString) => {
                                return parseFloat(amountString.replace(/[$,]/g, ''));
                            };
                            aValue = a[orderByField] ? parseAmount(a[orderByField]) : 0;
                            bValue = b[orderByField] ? parseAmount(b[orderByField]) : 0;
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
                    const formattedRecords = await Promise.all(paginatedRecords.map(async (childData) => {
                        return await buildHTML(childData);
                    }));
                     $(function () {
                                $('[data-toggle="tooltip"]').tooltip();
                            });
                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords,
                        recordsFiltered: totalRecords,
                        filteredData: filteredRecords,
                        data: formattedRecords
                    });
                } catch (error) {
                    console.error("Error fetching data from Firestore:", error);
                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: []
                    });
                }
            },
            order: driverID ? ((checkDeletePermission==true) ? [4, 'desc'] : [3,'desc']) : (checkDeletePermission==true) ? [5, 'desc'] : [4,'desc'],
            columnDefs: [{
                targets: driverID ? ((checkDeletePermission==true) ? 4 : 3) : (checkDeletePermission==true) ? 5 : 4,
                type: 'date',
                render: function(data) {
                    return data;
                }
            },
                {
                    orderable: false,
                    targets: driverID ? ((checkDeletePermission==true) ? [0, 5, 6] : [4, 5]) : (checkDeletePermission==true) ? [0, 6, 7] : [5, 6]
                },
            ],
          "language": datatableLang,
            dom: 'lfrtipB',
            buttons: [
                    {
                        extend: 'collection',
                        text: '<i class="mdi mdi-cloud-download"></i> {{trans('lang.export_as')}}',
                        className: 'btn btn-info',
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                text: '{{trans('lang.export_excel')}}',
                                action: function (e, dt, button, config) {
                                    exportData(dt, 'excel',fieldConfig);
                                }
                            },
                            {
                                extend: 'pdfHtml5',
                                text: '{{trans('lang.export_pdf')}}',
                                action: function (e, dt, button, config) {
                                    exportData(dt, 'pdf',fieldConfig);
                                }
                            },   
                            {
                                extend: 'csvHtml5',
                                text: '{{trans('lang.export_csv')}}',
                                action: function (e, dt, button, config) {
                                    exportData(dt, 'csv',fieldConfig);
                                }
                            }
                        ]
                    }
            ],
            initComplete: function() {
                $(".dataTables_filter").append($(".dt-buttons").detach());
                $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete','new-password').val('');
                $('.dataTables_filter label').contents().filter(function() {
                    return this.nodeType === 3; 
                }).remove();
            }
        });
        function debounce(func, wait) {
            let timeout;
            const context = this;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }
        $('#search-input').on('input', debounce(function () {
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
        let result = user_number.filter(obj => {
            return obj.id == val.author;
        })
        if (result.length > 0) {
            val.phoneNumber = result[0].phoneNumber;
            val.isActive = result[0].isActive;
        } else {
            val.phoneNumber = '';
            val.isActive = false;
        }
        newdate = '';
        var id = val.id;
        var vendorID = val.vendorID;
        var user_id = val.authorID;
        var route1 = '{{route("parcel_orders.edit",":id")}}';
        route1 = route1.replace(':id', id);
        var user_view = '{{route("users.view",":id")}}';
        user_view = user_view.replace(':id', user_id);
        var driver_id = val.driverId;
        var driverView = '{{route("drivers.view",":id")}}';
        driverView = driverView.replace(':id', driver_id);
        if (checkDeletePermission) {
            html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label></td>');
        }
        html.push('<a href="' + route1 + '" class="redirecttopage">' + val.id + '</a>');
        html.push('<a href="' + user_view + '" class="redirecttopage">' + val.authorName + '</a>');
        if(!driverID){
            html.push('<a href="' + driverView + '" class="redirecttopage">' + val.driverName + '</a>');
        }
        
        html.push('<td>' + val.final_amount + '</td>');
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
            html.push('<td></td>');
        }
        if (val.status == 'Order Placed') {
            html.push('<td class="order_placed"><span class="badge badge-warning">' + val.status + '</span></td>');
        } else if (val.status == 'Order Accepted') {
            html.push('<td class="order_accepted"><span class="badge badge-info">' + val.status + '</span></td>');
        } else if (val.status == 'Order Rejected') {
            html.push('<td class="order_rejected"><span class="badge badge-danger ">' + val.status + '</span></td>');
        } else if (val.status == 'Driver Pending') {
            html.push('<td class="driver_pending"><span class="badge badge-secondary ">' + val.status + '</span></td>');
        } else if (val.status == 'Driver Rejected') {
            html.push('<td class="driver_rejected"><span class="badge badge-danger ">' + val.status + '</span></td>');
        } else if (val.status == 'Order Shipped') {
            html.push('<td class="order_shipped"><span class="badge badge-primary ">' + val.status + '</span></td>');
        } else if (val.status == 'In Transit') {
            html.push('<td class="in_transit"><span class="badge badge-info ">' + val.status + '</span></td>');
        } else if (val.status == 'Order Completed') {
            html.push('<td class="order_completed"><span class="badge badge-success ">' + val.status + '</span></td>');
        } else {
            html.push('<td class="order_completed"><span class="badge badge-dark ">' + val.status + '</span></td>');
        }
        var action = '';
        action = action + '<span class="action-btn"></i></a><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
        if (checkDeletePermission) {
            action = action + '<a id="' + val.id + '" class="delete-btn" name="delete-btn" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
        }
        action = action + '</span>';
        html.push(action);
        return html;
    }
    $(document.body).on('change', '#order_status', function() {
        order_status = jQuery(this).val();
    });
    $(document.body).on('keyup', '#search', function() {
        search = jQuery(this).val();
    });
    var orderStatus = '<?php if (isset($_GET['status'])) {
                            echo $_GET['status'];
                        } else {
                            echo '';
                        } ?>';
    if (orderStatus) {
        if (orderStatus == 'order-placed') {
            ref = refData.where('sectionId', '==', section_id).where('status', '==', 'Order Placed').orderBy('createdAt', 'desc');
            $("ol.breadcrumb ").append("<li class='breadcrumb-item active'>{{trans('lang.order_placed')}}</li>");
        } else if (orderStatus == 'order-confirmed') {
            ref = refData.where('sectionId', '==', section_id).where('status', 'in', ['Order Accepted', 'Driver Accepted']).orderBy('createdAt', 'desc');
            $("ol.breadcrumb ").append("<li class='breadcrumb-item active'>{{trans('lang.order_accepted')}}</li>");
        } else if (orderStatus == 'order-shipped') {
            ref = refData.where('sectionId', '==', section_id).where('status', 'in', ['Order Shipped', 'In Transit']).orderBy('createdAt', 'desc');
            $("ol.breadcrumb ").append("<li class='breadcrumb-item active'>{{trans('lang.order_shipped')}}</li>");
        } else if (orderStatus == 'order-completed') {
            ref = refData.where('sectionId', '==', section_id).where('status', '==', 'Order Completed').orderBy('createdAt', 'desc');
            $("ol.breadcrumb ").append("<li class='breadcrumb-item active'>{{trans('lang.order_completed')}}</li>");
        } else if (orderStatus == 'order-canceled') {
            ref = refData.where('sectionId', '==', section_id).where('status', '==', 'Order Rejected').orderBy('createdAt', 'desc');
            $("ol.breadcrumb ").append("<li class='breadcrumb-item active'>{{trans('lang.order_rejected')}}</li>");
        } else if (orderStatus == 'order-failed') {
            ref = refData.where('sectionId', '==', section_id).where('status', '==', 'Driver Rejected').orderBy('createdAt', 'desc');
            $("ol.breadcrumb ").append("<li class='breadcrumb-item active'>{{trans('lang.driver_rejected')}}</li>");
        } else if (orderStatus == 'order-pending') {
            ref = refData.where('sectionId', '==', section_id).where('status', '==', 'Driver Pending').orderBy('createdAt', 'desc');
            $("ol.breadcrumb ").append("<li class='breadcrumb-item active'>{{trans('lang.driver_pending')}}</li>");
        } else {
            ref = refData.orderBy('createdAt', 'desc');
        }
    }
    $(document).on("click", "a[name='delete-btn']", function(e) {
        var id = this.id;
        database.collection('parcel_orders').doc(id).delete().then(function(result) {
            window.location.href = '{{ url()->current() }}';
        });
    });

    function buildParcelTotal(orderData) {

        let order_subtotal = parseFloat(orderData.subTotal || 0);
        let total_discount = 0;
        let total_tax_amount = 0;

        // Discount
        let discount = parseFloat(orderData.discount || 0);
        total_discount = isNaN(discount) ? 0 : discount;

        // Order tax
        let orderTaxable = Math.max(0, order_subtotal - total_discount);

        (orderData.taxSetting || []).forEach(tax => {
            if (tax.enable) {
                let taxAmount = tax.type === "percentage"
                    ? (parseFloat(tax.tax) / 100) * orderTaxable
                    : parseFloat(tax.tax);

                total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
            }
        });

        // Extra charges
        let platformFee = parseFloat(orderData.platformFee || 0);

        // Extra taxes
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
        let total_price = (order_subtotal - total_discount) + platformFee + total_tax_amount;

        if (currencyAtRight) {
            return parseFloat(total_price).toFixed(decimal_degits) + currentCurrency;
        } else {
            return currentCurrency + parseFloat(total_price).toFixed(decimal_degits);
        }
    }
    
    $("#is_active").click(function() {
        $("#parcelTable .is_open").prop('checked', $(this).prop('checked'));
    });
    $("#deleteAll").click(function() {
        if ($('#parcelTable .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                $('#parcelTable .is_open:checked').each(function() {
                    var dataId = $(this).attr('dataId');
                    database.collection('parcel_orders').doc(dataId).delete().then(function() {
                        setTimeout(function() {
                            window.location.reload();
                        }, 5000);
                    });
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
async function getDriverInfo(driverId){
    await database.collection('users').where("id", "==", driverId).get().then(async function (snapshotss) {
        if (snapshotss.docs[0]) {
            var driver_data = snapshotss.docs[0].data();
            driverName = driver_data.firstName + " " + driver_data.lastName;
            $('.page-title').html(" - "+driverName);
        }
    });
}
</script>
@endsection