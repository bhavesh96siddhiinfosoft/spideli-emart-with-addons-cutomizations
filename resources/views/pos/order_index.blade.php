@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor orderTitle">{{trans('lang.pos_orders')}} </h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.pos_orders')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>

    <div class="container-fluid">
        <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
            {{trans('lang.processing')}}...
        </div>

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive m-t-10">
                            <table id="orderTable"
                                class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                       <?php if (in_array('orders.delete', json_decode(@session('user_permissions'), true))) { ?>
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active">
                                                        <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                            <i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a></label>
                                                </th>
                                                <?php } ?>

                                        <th>{{trans('lang.order_id')}}</th>                                       
                                        <th>{{trans('lang.order_user_id')}}</th>                                        
                                        <th>{{trans('lang.date')}}</th>
                                        <th>{{trans('lang.vendors_payout_amount')}}</th>
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

@endsection

@section('scripts')

<script type="text/javascript">

    var section_id = getCookie('section_id') || null;

    var database = firebase.firestore();

    var redData = ref;
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

    var order_status = jQuery('#order_status').val();
    var search = jQuery("#search").val();

    var refData = database.collection('vendor_orders').where('section_id', '==', section_id).where('isPosOrder','==',true);

    var ref = '';

    var user_permissions = '<?php echo @session('user_permissions'); ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('orders.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }

    var checkPrintPermission = false;

    if ($.inArray('vendors.orderprint', user_permissions) >= 0) {
        checkPrintPermission = true;
    }

    $(document.body).on('change', '#order_status', function () {
        order_status = jQuery(this).val();
    });

    $(document.body).on('keyup', '#search', function () {
        search = jQuery(this).val();
    });

    var orderStatus = '<?php if (isset($_GET['status'])) {
        echo $_GET['status'];
    } else {
        echo '';
    } ?>';          


    if (orderStatus) {

        ref = refData.orderBy('createdAt', 'desc').where('status', '==', orderStatus);


    } else {

        if ((order_status == 'All' || order_status != '') && search != '') {

            ref = refData;
        } else {
            ref = refData.orderBy('createdAt', 'desc');
        }
    }

    $(document).ready(async function () {

        jQuery('#search').hide();  

        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        $(document.body).on('change', '#selected_search', function () {

            if (jQuery(this).val() == 'status') {
                jQuery('#order_status').show();
                jQuery('#search').hide();
            } else {

                jQuery('#order_status').hide();
                jQuery('#search').show();

            }
        });

        jQuery("#data-table_processing").show();

        const table = $('#orderTable').DataTable({
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

                const orderableColumns =  checkDeletePermission
                ? ['', 'id',  'user', 'createdAt', 'amount', 'status', '']
                : ['id',  'user', 'createdAt', 'amount', 'status', ''];

                const orderByField = orderableColumns[orderColumnIndex];

                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }

                try {
                    const querySnapshot = await ref.get();
                    if (querySnapshot.empty) {
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
                        if (childData.userID) {
                            var user = await getuserName(childData.authorID);
                            childData['user'] = user || "";
                        } else {
                            childData['user'] = "";
                        }

                        childData.amount = await buildHTMLProductstotal(childData);

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
                            var createdAt = date + ' ' + time;
                            if (
                                (user && user.toLowerCase().includes(searchValue))  ||
                                (childData.id && childData.id.toLowerCase().includes(searchValue)) ||
                                (childData.status && childData.status.toLowerCase().includes(searchValue)) || (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1)
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
                        if (orderByField === 'createdAt') {
                            try {
                                aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                                bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                            } catch (err) {
                            }
                        }
                        if (orderByField === 'amount') {
                            aValue = a[orderByField].slice(1) ? parseInt(a[orderByField].slice(1)) : 0;
                            bValue = b[orderByField].slice(1) ? parseInt(b[orderByField].slice(1)) : 0;
                        }
                        if (orderDirection === 'asc') {
                            return (aValue > bValue) ? 1 : -1;
                        } else {
                            return (aValue < bValue) ? 1 : -1;
                        }
                    });

                    const totalRecords = filteredRecords.length;
                    const paginatedRecords = filteredRecords.slice(start, start + length);

                    const formattedRecords = await Promise.all(paginatedRecords.map(async (childData) => {
                        return await buildHTML(childData);
                    }));

                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords,
                        recordsFiltered: totalRecords,
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
           
            order:  checkDeletePermission ? [3, 'desc'] : [2, 'desc'],
            columnDefs: [
                {
                   
                    targets: checkDeletePermission ? [3] : [2],
                    type: 'date',
                    render: function (data) {
                        return data; // Adjust formatting if needed
                    }
                },
                {
                    orderable: false,                   
                    targets: checkDeletePermission ? [0,6] : [5]
                }
            ],
            "language": {
                "zeroRecords": "{{ trans('lang.no_record_found') }}",
                "emptyTable": "{{ trans('lang.no_record_found') }}",
                "processing": ""
            },
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
        newdate = '';
        var id = val.id;
        var vendorID = val.vendorID;

        var user_id = val.authorID;
        var route1 = '{{route("orders.edit",":id")}}';
        route1 = route1.replace(':id', id);

        var printRoute = '{{route("vendors.orderprint",":id")}}';
        printRoute = printRoute.replace(':id', id);

        var customer_view = '{{route("users.edit",":id")}}';
        customer_view = customer_view.replace(':id', user_id);
        if (checkDeletePermission) {
            html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label></td>');
        }
        html.push('<a data-url="' + route1 + '" href="'+route1+'" class="redirecttopage">' + val.id + '</a>');

       
        if (val.hasOwnProperty("authorID") && val.authorID) {
            var user = await getuserName(val.authorID);

            if (user) {
                html.push('<a  data-url="' + customer_view + '" href="'+customer_view+'" class="redirecttopage">' + user + '</a>');
            } else {
                html.push('<td>{{trans("lang.unknown")}}</td>');
            }

        } else {
            html.push('<td></td>');
        }

        


        var date = '';
        var time = '';
        if (val.hasOwnProperty("createdAt")) {

            try {
                date = val.createdAt.toDate().toDateString();
                time = val.createdAt.toDate().toLocaleTimeString('en-US');
            } catch (err) {

            }
            html.push('<td class="dt-time">' + date + ' ' + time + '</td>');
        } else {
            html.push('<td></td>');
        }
        var price = 0;


        var price = await buildHTMLProductstotal(val);
        html.push('<span class="text-green">' + price + '</span>');


        if (val.status == 'InProcess') {
            html.push('<span class="order_placed"><span>' + val.status + '</span></span>');
        } else if (val.status == 'InTransit') {
            html.push('<span class="in_transit"><span>' + val.status + '</span></span>');
        } else if (val.status == 'Delivered') {
            html.push('<span class="order_completed"><span>' + val.status + '</span></span>');
        } else {
            html.push('<span class="order_completed"><span>' + val.status + '</span></span>');
        }
        var actionHtml = '';
        actionHtml = actionHtml + '<span class="action-btn">';
        actionHtml = actionHtml + '<a href="' + route1 + '"><i class="fa fa-eye"></i></a>';

        if (checkPrintPermission) {
            actionHtml = actionHtml + '<a href="' + printRoute + '"><i class="fa fa-print" style="font-size:20px;"></i></a>';
        }

        if (checkDeletePermission) {
            actionHtml = actionHtml + '<a id="' + val.id + '" class="delete-btn" name="order-delete" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
        }
        actionHtml = actionHtml + '</span>';
        html.push(actionHtml);
        return html;
    }

    $("#is_active").click(function () {
        $("#orderTable .is_open").prop('checked', $(this).prop('checked'));

    });

    async function getuserName(id) {
        var name = '';
        await database.collection('users').where("id", "==", id).get().then(async function (snapshotsorder) {

            if (snapshotsorder.docs.length) {
                var user = snapshotsorder.docs[0].data();
                name = user.firstName + ' ' + user.lastName;
            }
        });
        return name;
    }

    $("#deleteAll").click(function () {

        if ($('#orderTable .is_open:checked').length) {

            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                $('#orderTable .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');

                    database.collection('vendor_orders').doc(dataId).delete().then(function () {

                        setTimeout(function () {
                            window.location.reload();
                        }, 7000);

                    });

                });

            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });

    $(document).on("click", "a[name='order-delete']", function (e) {


        if (confirm("{{trans('lang.delete_alert')}}")) {

            var id = this.id;
            database.collection('vendor_orders').doc(id).delete().then(function (result) {
                window.location.href = '{{ url()->current() }}';
            });
        }
    });


    async function getStoreNameFunction(vendorId) {
        var vendorName = '';
        await database.collection('vendors').where('id', '==', vendorId).get().then(async function (snapshots) {
            if (!snapshots.empty) {
                var vendorData = snapshots.docs[0].data();

                vendorName = vendorData.title;
                $('.orderTitle').html('{{trans("lang.pos_orders")}} - ' + vendorName);

                if (vendorData.dine_in_active == true) {
                    $(".dine_in_future").show();
                }
                walletRoute = "{{route('users.walletstransaction', ':id')}}";
                walletRoute = walletRoute.replace(":id", vendorData.author);
                $('#vendor_wallet').append('<a href="' + walletRoute + '">{{trans("lang.wallet_transaction")}}</a>');

            }
        });

        return vendorName;

    }


    async function getUserNameFunction(userId) {
        var userName = '';
        await database.collection('users').where('id', '==', userId).get().then(async function (snapshots) {
            var user = snapshots.docs[0].data();

            userName = user.name;
            $('.orderTitle').html('{{trans("lang.pos_orders")}} - ' + userName + "(" + user.role + ")");
        });

        return userName;

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
                            taxAmount = tax.tax;
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

</script>

@endsection
