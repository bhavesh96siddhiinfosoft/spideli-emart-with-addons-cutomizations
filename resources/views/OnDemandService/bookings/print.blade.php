@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">

        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.print_booking')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('ondemand.bookings.index') !!}">{{trans('lang.booking_plural')}}</a>
                </li>
                <li class="breadcrumb-item">{{trans('lang.print_booking')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card" id="printableArea" style="font-family: emoji;">
            <div class="col-md-12">
                <div class="print-top non-printable mt-3">
                   
                    <div class="text-right print-btn non-printable">
                        <button type="button" class="fa fa-print non-printable" onclick="printDiv('printableArea')"></button>
                    </div>
                </div>

                <hr class="non-printable">
            </div>
            <div class="col-12" id="printableArea">
                <div class="text-left pt-4 mb-5" style="text-align:center;">
                     <h5 style="font-weight: bold" class="provider_div">{{trans('lang.provider_name')}} : <label class="providerName"></label></h5>
                     
                     <h5 style="font-weight: bold" class="provider_div">
                            {{trans('lang.provider_phone')}} :
                            <label class="providerPhone"></label>
                    </h5>
                </div>
                <span><hr style="border-top: 2px dashed;"></span>
                <div class="row mt-3">
                    <div class="col-6">
                        <h5>{{trans('lang.order_id')}} : <label class="orderId"></label></h5>
                    </div>
                    <div class="col-6">
                        <h5 style="font-weight: bold">
                                <label class="orderDate"></label>

                        </h5>
                    </div>
                    <div class="col-12">
                        <h5>
                            {{trans('lang.customer_name')}} :
                            <label class="customerName"></label>
                        </h5>
                        <h5>
                            {{trans('lang.phone')}} :

                            <label class="customerPhone"></label>
                        </h5>
                        <h5 class="text-break">
                            {{trans('lang.address')}} :

                            <label class="customerAddress"></label>
                        </h5>
                    </div>
                </div>
                <h5 class="text-uppercase"></h5>
                <span><hr style="border-top: 2px dashed;"></span>
                <table class="table table-bordered mt-5 mb-5 table-product-list" style="width: 100%">
                        <thead>
                        <tr>
                            <th>{{trans('lang.service')}}</th>
                            <th>{{trans('lang.price')}}</th>
                            <th>{{trans('lang.qty')}}</th>
                            <th>{{trans('lang.total')}}</th>
                        </tr>
                        </thead>
                        <tbody id="order_products">
                            <td id="service_name"></td>
                            <td id="price"></td>
                            <td id="qty"></td>
                            <td class="total_price"></td>
                        </tbody>
                    </table>
                    
                <span><hr style="border-top: 2px dashed;"></span>
                <div class="row justify-content-md-end mb-5" style="width: 100%;">
                    <div class="col-md-3 col-lg-3">
                        <table class="table-subtotal" cellpadding="5" cellspacing="5">
                            <tbody>
                                <tr>
                                    <td>{{trans('lang.sub_total')}} :</td>
                                    <td><label class="total_price"></label></td>
                                </tr>
                                <tr>
                                    <td>{{trans('lang.coupon_discount')}} :</td>
                                        <td>-<label class="total_discount_amount"></label></td>
                                </tr>
                                <tr>
                                    <td>{{trans('lang.add_extra_charges')}} :</td>
                                        <td><label class="extra_charges"></label></td>
                                </tr>
                                <tr id="platform_charge">
                                    <td>{{trans('lang.platform_charge')}} :</td>
                                    <td><label class="platform_charge"></label></td>
                                </tr>
                                <tr>
                                    <td>{{trans('lang.total')}} :</td>
                                    <td><label class="total_amount"></label></td>
                                </tr>
                                <tr><td><span class="admin_commission row w-100 m-0"></span></td></tr>
                            </tbody>
                        </table>
                    </div>             
                </div>
                 <div class="clearfix" style="text-align:center;width: 100%;display:table">
                        <span><hr style="border-top: 2px dashed;"></span>
                        <h5 class="text-center pt-3" style="font-size: 18px;font-weight: bold">{{trans('lang.thank_you')}}</h5>
                        <span><hr style="border-top: 2px dashed;"></span>
                </div>
            </div>
        </div>
    </div>
</div>
    @endsection

    @section('style')
    <style type="text/css">
        #printableArea * {
            color: black !important;
            font-weight: bold;
        }
        body {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        .table-subtotal td{
            text-align:right;
        }
        
    </style>
    <style type="text/css" media="print">
         @page {
            size: portrait;
        }

        @page {
            size: auto;
            margin: 2px;
        }
    </style>
    @section('scripts')
    <script>
        var adminCommission = 0;
        var id_rendom = "<?php echo uniqid(); ?>";
        var id = "<?php echo $id; ?>";
        var driverId = '';
        var fcmToken = '';
        var old_order_status = '';
        var payment_shared = false;
        var deliveryChargeVal = 0;
        var tip_amount_val = 0;
        var tip_amount = 0;
        var total_price = 0;
        var total_item_price = 0;
        var total_addon_price = 0;
        var vendorname = '';
        var database = firebase.firestore();

        var ref = database.collection('provider_orders').where("id", "==", id);
        var currentCurrency = '';
        var currencyAtRight = false;
        var refCurrency = regionCurrencyRef();
        var decimal_degits = 0;

        var currencyData = '';
        refCurrency.get().then(async function(snapshots) {
            currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        });

        let taxBreakdownGrouped = {
            order: {},
            platform: {}
        };
        
        ref.get().then(async function(snapshots) {

            jQuery("#data-table_processing").show();
            var order = snapshots.docs[0].data();

            $(".customerName").text(order.author.firstName + " " + order.author.lastName);
            var billingAddressstring = '';

            $(".orderId").text(id);

            var date = order.createdAt.toDate().toDateString();
            var time = order.createdAt.toDate().toLocaleTimeString('en-US');
            $(".orderDate").text(date + " " + time);

            var billingAddressstring = '';

            if (order.address.hasOwnProperty('address')) {
                billingAddressstring = billingAddressstring + order.address.address;
            }

            if (order.address.hasOwnProperty('locality')) {
                billingAddressstring = billingAddressstring + "," + order.address.locality;
            }
            if (order.address.hasOwnProperty('landmark')) {
                billingAddressstring = billingAddressstring + " " + order.address.landmark;
            }
            $(".customerAddress").text(billingAddressstring);

            if (order.author.hasOwnProperty('phoneNumber')) {
                if(order.author.phoneNumber.includes('+')){
                    $(".customerPhone").text('+' + EditPhoneNumber(order.author.phoneNumber.slice(1)));
                }else{
                    $(".customerPhone").text(EditPhoneNumber(order.author.phoneNumber));
                }
            }


            if (order.address.hasOwnProperty('country')) {

                $("#billing_country").text(order.address.country);

            }

            if (order.address.hasOwnProperty('email')) {
                $("#billing_email").html('<a href="mailto:' + order.address.email + '">' + shortEmail(order.address.email) +
                    '</a>');
            }

            if (order.createdAt) {
                var date1 = order.createdAt.toDate().toDateString();
                var date = new Date(date1);
                var dd = String(date.getDate()).padStart(2, '0');
                var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
                var yyyy = date.getFullYear();
                var createdAt_val = yyyy + '-' + mm + '-' + dd;
                var time = order.createdAt.toDate().toLocaleTimeString('en-US');
                $('#createdAt').text(createdAt_val + ' ' + time);
            }

            if (order.payment_method) {

                if (order.payment_method == 'cod') {
                    $('#payment_method').text('{{trans("lang.cash_on_delivery")}}');
                } else if (order.payment_method == 'paypal') {
                    $('#payment_method').text('{{trans("lang.paypal")}}');
                } else {
                    $('#payment_method').text(order.payment_method);
                }

            }

            if (order.provider && order.provider.author != '' && order.provider.author != undefined) {
                vendorAuthor = order.provider.author;
            }
            fcmToken = order.author.fcmToken;
            vendorname = order.provider.title;

            fcmTokenVendor = order.provider.fcmToken;
            customername = order.author.firstName;

            vendorId = order.provider.id;
            old_order_status = order.status;
            if (order.payment_shared != undefined) {
                payment_shared = order.payment_shared;
            }
            var productstotalHTML = buildHTMLProductstotal(order);

            orderPreviousStatus = order.status;
            if (order.hasOwnProperty('payment_method')) {
                orderPaymentMethod = order.payment_method;
            }

            $("#order_status option[value='" + order.status + "']").attr("selected", "selected");
            if (order.status == "Order Rejected" || order.status == "Driver Rejected") {
                $("#order_status").prop("disabled", true);
            }
            var price = 0;

            if (order.provider.author) {
                var provider = database.collection('users').where("id", "==",order.provider.author);
               
                provider.get().then(async function(snapshotsnew) {
                    if(!snapshotsnew.empty){
                    var providerData = snapshotsnew.docs[0].data();

                    $('.providerName').html(providerData.firstName+' '+providerData.lastName);
                    
                    if (providerData.phoneNumber) {
                        if(providerData.phoneNumber.includes('+')){
                            $('.providerPhone').text('+' + EditPhoneNumber(providerData.phoneNumber.slice(1)));
                        }else{
                            $('.providerPhone').text(EditPhoneNumber(providerData.phoneNumber));
                        }
                    }
                    
                }else{
                    $(".provider_div").hide();
                }
                });

            }
            $('#service_name').text(order.provider.title);
            $('#qty').text(order.quantity);
           
            
            jQuery("#data-table_processing").hide();
        })

        function buildHTMLProductstotal(snapshotsProducts) {

            let html = '';
            let intRegex = /^\d+$/;
            let floatRegex = /^((\d+(\.\d*)?)|((\d*\.)?\d+))$/;

            let adminCommission = snapshotsProducts.adminCommission;
            let adminCommissionType = snapshotsProducts.adminCommissionType;
            let discount = parseFloat(snapshotsProducts.discount || 0);
            let couponCode = snapshotsProducts.couponCode;
            let notes = snapshotsProducts.notes;
            let extraCharges = parseFloat(snapshotsProducts.extraCharges || 0);
            let platformFee = parseFloat(snapshotsProducts.platformFee || 0);

            let formattedplatformFee = currencyAtRight
                ? parseFloat(platformFee).toFixed(decimal_degits) + currentCurrency
                : currentCurrency + parseFloat(platformFee).toFixed(decimal_degits);
            $('.platform_charge').text(formattedplatformFee);

            let quantity = parseFloat(snapshotsProducts.quantity || 1);
            let basePrice = parseFloat(snapshotsProducts.provider.price || 0);
            if (snapshotsProducts.provider.disPrice && parseFloat(snapshotsProducts.provider.disPrice) > 0) {
                basePrice = parseFloat(snapshotsProducts.provider.disPrice);
            }

            let priceUnit = snapshotsProducts.provider.priceUnit === 'Hourly' ? ' /hr' : '';
            let sub_total = basePrice * quantity;
            let total_price = sub_total;

            // Display base price
            let formattedBasePrice = currencyAtRight
                ? parseFloat(basePrice).toFixed(decimal_degits) + currentCurrency + priceUnit
                : currentCurrency + parseFloat(basePrice).toFixed(decimal_degits) + priceUnit;

            let formattedSubtotal = currencyAtRight
                ? parseFloat(sub_total).toFixed(decimal_degits) + currentCurrency
                : currentCurrency + parseFloat(sub_total).toFixed(decimal_degits);

            $('#price').text(formattedBasePrice);
            $('.total_price').text(formattedSubtotal);

            // Apply discount
            let discount_val = 0;
            if ((intRegex.test(discount) || floatRegex.test(discount))) {
                total_price -= discount;
                discount_val = currencyAtRight
                    ? discount.toFixed(decimal_degits) + currentCurrency
                    : currentCurrency + discount.toFixed(decimal_degits);

                $('.total_discount_amount').text(discount_val);
            } else {
                $('.total_discount_amount').text(currencyAtRight
                    ? discount_val.toFixed(decimal_degits) + currentCurrency
                    : currentCurrency + discount_val.toFixed(decimal_degits));
            }

            let total_item_price = total_price;

            // TAX CALCULATION
            let total_tax_amount = 0;
            let orderTaxable = Math.max(0, total_item_price);
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
            taxBreakdownGrouped.order[''] = orderCombinedTax;

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
                        taxBreakdownGrouped[scope.key][tax.title] = (taxBreakdownGrouped[scope.key][tax.title] || 0) + parseFloat(taxAmount);
                    }
                });
            });

            var taxHtml = '';
            taxHtml += renderTaxSection('order', 'Tax on Order Total');
            taxHtml += renderTaxSection('platform', 'Tax on Platform Fee');
            taxHtml += '<tr><td>{{ trans('lang.total_tax') }}</td><td>+' + formatCurrency(total_tax_amount, currencyData) + '</td></tr>';
            $('.table-subtotal tr:eq(3)').after(taxHtml);

            // Extra charges
            let extraDisplay = currencyAtRight
                ? extraCharges.toFixed(decimal_degits) + currentCurrency
                : currentCurrency + extraCharges.toFixed(decimal_degits);
            $('.extra_charges').text(extraDisplay);

            // FINAL TOTAL
            let order_total = total_item_price + extraCharges + platformFee + total_tax_amount;

            // Display total amount
            let total_price_val = currencyAtRight
                ? order_total.toFixed(decimal_degits) + currentCurrency
                : currentCurrency + order_total.toFixed(decimal_degits);

            $('.total_amount').text(total_price_val);
            html += `<tr><td class="label">{{trans("lang.total_amount")}}</td><td class="total_price_val">${total_price_val}</td></tr>`;

            // Admin commission
            if ((intRegex.test(adminCommission) || floatRegex.test(adminCommission)) && adminCommission > 0) {
                let adminCommHtml = "";
                let adminCommission_val = 0;
                if (adminCommissionType.toLowerCase() === "percentage") {
                    adminCommHtml = `(${adminCommission}%)`;
                    adminCommission_val = (total_item_price * adminCommission) / 100;
                } else {
                    adminCommission_val = parseFloat(adminCommission);
                }
                let formattedAdminCommission = currencyAtRight
                    ? adminCommission_val.toFixed(decimal_degits) + currentCurrency
                    : currentCurrency + adminCommission_val.toFixed(decimal_degits);

                $('.table-subtotal tr:eq(7)').after(`<tr><td>{{trans('lang.admin_commission')}} <label>${adminCommHtml}</label></td><td><label>${formattedAdminCommission}</label></td></tr>`);
            }

            // Notes
            if (notes) {
                html += `<tr><td class="label">{{trans("lang.notes")}}</td><td class="adminCommission_val">${notes}</td></tr>`;
            }

            return html;
        }

        function printDiv(divName) {

            var printContents = document.getElementById(divName).innerHTML;
            printContents = printContents.replace(/<img[^>]*>/g,"");
            var printWindow = window.open();
            var style = `<style type="text/css"> 
                .table-subtotal{ 
                    width: auto; float: right;
                } 
                .table-subtotal td{
                    text-align:right;
                }
                .table-product-list td{
                    font-size: 12px;
                    text-align:center;
                }
                </style>`;
            
            printWindow.document.write(style);
            printWindow.document.write(printContents);
            printWindow.document.close();
            printWindow.print();
        }

        function renderTaxSection(section, labelSuffix) {
            let html = '';
            if (!taxBreakdownGrouped[section]) return '';
            for (let title in taxBreakdownGrouped[section]) {
                let taxlabel = title;
                let taxAmount = parseFloat(taxBreakdownGrouped[section][title]);
                html = html + '<tr><td>' + taxlabel + " " + labelSuffix + '</td><td>+' + formatCurrency(taxAmount, currencyData) + '</td></tr>';
            }
            return html;
        }

    </script>

    @endsection