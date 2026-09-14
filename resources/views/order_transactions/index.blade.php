@extends('layouts.app')

@section('content')

<div class="page-wrapper">


    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.order_transaction_table')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.order_transaction_table')}}</li>
            </ol>
        </div>

        <div>

        </div>

    </div>


    <div class="container-fluid">
        <div class="row">

            <div class="col-12">

                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li class="nav-item">
                                <a class="nav-link active" href="{!! url()->current() !!}"><i
                                            class="fa fa-list mr-2"></i>{{trans('lang.order_transaction_table')}}
                                </a>
                            </li>

                        </ul>
                    </div>
                    <div class="card-body">
                        
                    <div class="table-responsive m-t-10">

                        <table id="example24"
                               class="display nowrap table table-hover table-striped table-bordered table table-striped"
                               cellspacing="0" width="100%">

                            <thead>

                            <tr>
                                <th>{{ trans('lang.order_id')}}</th>
                                <th>{{ trans('lang.driver')}}</th>
                                <th>{{trans('lang.amount')}}</th>
                                <th>{{ trans('lang.vendor')}}</th>
                                <th>{{trans('lang.amount')}}</th>
                                <th>{{trans('lang.date')}}</th>
                                <th>{{trans('lang.order_order_status_id')}}</th>
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
    var refData = database.collection('order_transactions');
    var search = jQuery("#search").val();

    $(document.body).on('keyup', '#search', function () {
        search = jQuery(this).val();
    });

    <?php if($id != ''){ ?>

    var actionId = '<?php echo $id; ?>';
    actionId = actionId.split('=')[1];

    if (window.location.href.indexOf("vendorId") > -1) {
        if (search != '') {

            ref = refData.where('vendorId', '==', actionId);
        } else {

            ref = refData.orderBy('date', 'desc').where('vendorId', '==', actionId);
        }

    } else if (window.location.href.indexOf("driverId") > -1) {

        if (search != '') {

            ref = refData.where('driverId', '==', actionId);
        } else {

            ref = refData.orderBy('date', 'desc').where('driverId', '==', actionId);
        }
    }

    <?php } else{ ?>

        if (search != '') {
            ref = refData;
        } else {
            ref = refData.orderBy('date', 'desc');
        }
    <?php } ?>

    var append_list = '';

    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;

    var refCurrency = regionCurrencyRef();
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;

        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });

    $(document).ready(function () {

        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        var inx = parseInt(offest) * parseInt(pagesize);
        jQuery("#data-table_processing").show();

        append_list = document.getElementById('append_list1');
        append_list.innerHTML = '';

        ref.get().then(async function (snapshots) {
            html = '';

            html =await buildHTML(snapshots);

            if (html != '') {
                append_list.innerHTML = html;
                start = snapshots.docs[snapshots.docs.length - 1];
                endarray.push(snapshots.docs[0]);
                if (snapshots.docs.length < pagesize) {
                    jQuery("#data-table_paginate").hide();
                }
            }
            $('#example24').DataTable({
                
                order: [],
                columnDefs: [{
                         targets: 5,
                         type: 'date',
                        render: function(data) {
                            return data;
                        }
                    },
                    {orderable: false, targets: [0, 4, 6]},
                ],
                order: [5,"desc"],
               "language": datatableLang,
                responsive: true,
            });
            jQuery("#data-table_processing").hide();
        });

    });


    async function buildHTML(snapshots) {
        var html = '';
        var html = '';
   
    await Promise.all(snapshots.docs.map(async (listval) => {
    var val = listval.data();
    var route1 = '{{route("drivers.edit",":id")}}';
        route1 = route1.replace(':id', val.id);
    var getData = await getListData(val);
    
    html += getData;
    }));
    return html;
}

async function getListData(val) {
    var html = '';
            var route1 = '{{route("users.edit",":id")}}';
            route1 = route1.replace(':id', val.id);
            html = html + '<tr>';

            var order_detail = '{{route("orders.edit",":id")}}';
            order_detail = order_detail.replace(':id', val.order_id);


            html = html + '<td data-url="' + order_detail + '" class="redirecttopage" >' + val.order_id + '</td>';
            if (val.driverId != undefined) {
                const driverName = driverFunction(val.driverId);
                html = html + '<td class="driver_' + val.driverId + ' redirecttopage" ></td>';

            } else {
                html = html + '<td></td>';

            }

            var driverAmount = 0;
            if (val.driverAmount) {
                driverAmount = val.driverAmount;
            }
            if (currencyAtRight) {
                html = html + '<td>' + parseFloat(driverAmount).toFixed(decimal_degits) + '' + currentCurrency + '</td>';
            } else {
                html = html + '<td>' + currentCurrency + '' + parseFloat(driverAmount).toFixed(decimal_degits) + '</td>';
            }

            if (val.vendorId != '') {
                const vendorName = vendorFunction(val.vendorId);
                html = html + '<td class="vendor_' + val.vendorId + ' redirecttopage" ></td>';

            } else {
                html = html + '<td></td>';

            }

            var vendorAmount = 0;

            if (val.vendorAmount) {
                vendorAmount = val.vendorAmount;
            }
            if (currencyAtRight) {
                html = html + '<td>' + parseFloat(vendorAmount).toFixed(decimal_degits) + '' + currentCurrency + '</td>';
            } else {
                html = html + '<td>' + currentCurrency + '' + parseFloat(vendorAmount).toFixed(decimal_degits) + '</td>';
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

            html = html + '<td>' + date + '<br> ' + time + '</td>';

            const orderStatus = orderFunction(val.order_id);
            html = html + '<td class="order_' + val.order_id + '"></td>';


            html = html + '</tr>';

        return html;
    }

    async function driverFunction(driverId) {

        var driverName = '';
        var routeuser = '{{route("drivers.view",":id")}}';
        routeuser = routeuser.replace(':id', driverId);
        await database.collection('users').where("id", "==", driverId).get().then(async function (snapshotss) {

            if (snapshotss.docs[0]) {
                var user_data = snapshotss.docs[0].data();
                payoutuser = user_data.firstName + " " + user_data.lastName;
                jQuery(".driver_" + driverId).attr("data-url", routeuser).html(payoutuser);
            } else {
                jQuery(".driver_" + driverId).attr("data-url", routeuser).html('');
            }
        });
        return driverName;
    }

    async function vendorFunction(vendorId) {
        if(vendorId != undefined){
        var driverName = '';
        var routeuser = '{{route("vendors.edit",":id")}}';
        routeuser = routeuser.replace(':id', vendorId);
        await database.collection('vendors').where("id", "==", vendorId).get().then(async function (snapshotss) {
                
            if (snapshotss.docs[0]) {
                var user_data = snapshotss.docs[0].data();
                payoutuser = user_data.title;
                jQuery(".vendor_" + vendorId).attr("data-url", routeuser).html(payoutuser);
            } else {
                jQuery(".vendor_" + vendorId).attr("data-url", routeuser).html('');
            }
        });
        return driverName;
    }
    }

    async function orderFunction(orderId) {


        await database.collection('vendor_orders').where("id", "==", orderId).get().then(async function (snapshotss) {

            if (snapshotss.docs[0]) {
                var user_data = snapshotss.docs[0].data();
                payoutuser = user_data.status;
                jQuery(".order_" + orderId).html(payoutuser);
            } else {
                jQuery(".order_" + orderId).html('');
            }
        });

    }

</script>

@endsection
