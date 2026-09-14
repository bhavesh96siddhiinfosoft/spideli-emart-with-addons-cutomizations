@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{trans('lang.reports_tax')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{{url('/report/tax')}}">{{trans('lang.report_plural')}}</a>
                    </li>
                    <li class="breadcrumb-item active">{{trans('lang.reports_tax')}}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card  pb-4">
                <div class="card-body">
                    <div class="error_top"></div>
                    <div class="row vendor_payout_create">
                        <div class="vendor_payout_create-inner">
                            <fieldset>
                                <legend>{{trans('lang.reports_tax')}}</legend>
                                <div class="form-group row width-100">
                                    <label class="col-3 control-label">{{trans('lang.select_date_range')}}</label>
                                    <div class="col-7">
                                        <div id="reportrange"
                                             style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                            <i class="fa fa-calendar"></i>&nbsp;
                                            <span></span> <i class="fa fa-caret-down"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.tax_calculation_method')}}</label>
                                    <div class="col-7">
                                        <select class="form-control" id="tax_calculation_method">
                                            <option value="">{{trans('lang.select_tax_calculation_method')}}</option>
                                            <option value="all">{{trans('lang.report_all_tax')}}</option>
                                            <option value="individual">{{trans('lang.report_individual_tax')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row width-50 all_taxes d-none">
                                    <label class="col-3 control-label">{{trans('lang.select_taxes')}}</label>
                                    <div class="col-7">
                                        <div class="select2-container-full">
                                            <select class="form-control" id="all_taxes"></select>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-left pl-4 mb-4 btm-btn">
                                    <button type="submit" class="btn btn-primary generate_report">{{ trans('lang.generate_report')}}</button>
                                </div>
                                
                                <div class="form-group row">
                                    <div class="col-12" id="tax_report_table_container"></div>
                                </div>

                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="taxDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="min-width: 70%">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tax Details</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="filterData" class="mb-3"></div>
                    <div id="taxDetailContent"></div>
                </div>
                <div class="modal-footer">
                    <button id="download_detailed_report" class="btn btn-primary">Download Report</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Close">{{ trans('lang.close') }}</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>

    var section_id = getCookie('section_id') || '';
    var service_type = getCookie('service_type') || '';
    var ordersCollection = getOrdersCollectionByService(service_type);
    var ordersSectionField = getOrdersSectionField(service_type);
    
    var database = firebase.firestore();
    var refCurrency = regionCurrencyRef().limit('1');
    var refTaxes = database.collection('tax').where('enable', '==', true).where('sectionId','==',section_id);

    var decimal_degits = 0;
    var symbolAtRight = false;
    var currentCurrency = '';
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        decimal_degits = currencyData.decimalDigits;
        if (currencyData.symbolAtRight) {
            symbolAtRight = true;
        }
    });
    
    $('#reportrange').daterangepicker({
        showDropdowns: true,
        minYear: 2020,
        maxYear: moment().year(),
        linkedCalendars: false,
        autoApply: false,
        autoUpdateInput: false,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'This Year': [moment().startOf('year'), moment().endOf('year')],
            'Last Year': [
                moment().subtract(1, 'year').startOf('year'),
                moment().subtract(1, 'year').endOf('year')
            ]
        }
    });

    $('#reportrange span').html('{{ trans("lang.select_date_range") }}');
    $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
        $('#reportrange span').html(
            picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY')
        );
    });

    $('#all_taxes').select2({
        placeholder: '{{trans("lang.select_taxes")}}',  
        multiple: true,
    });

    refTaxes.where('scope','in',['admin_commission','vendor_subscription']).get()
    .then(function (snapShots) {
        if (snapShots.docs.length > 0) {

            let adminTaxes = [];
            let vendorTaxes = [];

            snapShots.docs.forEach((doc) => {
                let data = doc.data();
                let taxText = data.title + ' (';
                if (data.type === 'percentage') {
                    taxText += data.tax + '%';
                } else {
                    if (symbolAtRight) {
                        taxText += data.tax + ' ' + currentCurrency;
                    } else {
                        taxText += currentCurrency + data.tax;
                    }
                }
                taxText += ')';

                // Push into correct scope array
                if (data.scope === 'admin_commission') adminTaxes.push({id: data.id, text: taxText});
                else if (data.scope === 'vendor_subscription') vendorTaxes.push({id: data.id, text: taxText});
            });

            $('#all_taxes').empty();

            // Append admin taxes
            if (adminTaxes.length > 0) {
                let optgroupAdmin = $('<optgroup label="Admin Taxes"></optgroup>');
                adminTaxes.forEach(t => {
                    optgroupAdmin.append($('<option></option>').attr('value', t.id).text(t.text));
                });
                $('#all_taxes').append(optgroupAdmin);
            }

            if(service_type == "delivery-service" || service_type == "ecommerce-service" || service_type == "ondemand-service"){
                // Append vendor taxes
                if (vendorTaxes.length > 0) {
                    let optgroupVendor = $('<optgroup label="Vendor Taxes"></optgroup>');
                    vendorTaxes.forEach(t => {
                        optgroupVendor.append($('<option></option>').attr('value', t.id).text(t.text));
                    });
                    $('#all_taxes').append(optgroupVendor);
                }
            }
        }
    });

    $(document.body).on('change', '#tax_calculation_method', function() {
        if(jQuery(this).val() == "individual"){
            $(".all_taxes").removeClass('d-none');
            $("#all_taxes").prop('required',true)
        }else{
            $(".all_taxes").addClass('d-none');
            $("#all_taxes").prop('required',false)
        }
    });

    $(document).on('click', '.generate_report', async function () {

        let taxMethod = $("#tax_calculation_method").val();
        let selectedTaxes = $("#all_taxes").val() || [];
        let start_date = moment($('#reportrange').data('daterangepicker').startDate).toDate();
        let end_date = moment($('#reportrange').data('daterangepicker').endDate).toDate();
        let startTime = start_date.getTime();
        let endTime   = end_date.getTime();
        
        if ($('#reportrange span').text() == "{{ trans('lang.select_date_range') }}") {
            Swal.fire({ icon: 'error', text: '{{ trans("lang.select_date_range") }}' });
            return;
        } else if (taxMethod == "") {
            Swal.fire({ icon: 'error', text: '{{ trans("lang.select_tax_calculation_method") }}' });
            return;
        } else if (taxMethod == "individual" && !selectedTaxes.length) {
            Swal.fire({ icon: 'error', text: '{{ trans("lang.select_taxes") }}' });
            return;
        }

        $("#data-table_processing").show();

        try {

            // Orders
            let ordersRef = database.collection(ordersCollection)
                .where('status', '==', 'Order Completed')
                .where(ordersSectionField, '==', section_id)
                .where('createdAt', '>=', start_date)
                .where('createdAt', '<=', end_date)
                .orderBy('createdAt', 'desc');

            // Subscriptions
            let subsRef = database.collection('subscription_history')
                .where('subscription_plan.type','==','paid')
                .where('createdAt', '>=', start_date)
                .where('createdAt', '<=', end_date)
                .orderBy('createdAt', 'desc');

            let [ordersSnap, subsSnap] = await Promise.all([
                ordersRef.get(),
                subsRef.get()
            ]);

            let orders = ordersSnap.docs.map(d => d.data());

            // Get active subscriptions
            let subscriptions = [];
            subsSnap.docs.forEach(d => {
                let sub = d.data();
                if (!sub.createdAt || !sub.expiry_date) return;
                subscriptions.push(sub);
            });

            let allTaxesSnapshot = await refTaxes.get();
            let allTaxes = allTaxesSnapshot.docs.map(d => d.data());
            let totals = calculateTotalsWithBreakdown(orders, subscriptions, allTaxes, taxMethod, selectedTaxes);

            window.reportData = {
                orders: orders,
                subscriptions: subscriptions,
                allTaxes: allTaxes
            };

            let html = '<div class="d-flex justify-content-between align-items-center mb-2">';
                html += '<h4 class="mb-0">Tax Report List</h4>';
                    html += '<button id="download_report" class="btn btn-primary">Download Report</button>';
            html += '</div>';

            html += '<table class="table table-bordered">';
            html += '<tr><th>No</th><th>Income Source</th><th>Total Income</th><th>Total Tax</th><th>Action</th></tr>';

            let rows = [];
            if(service_type == "delivery-service" || service_type == "ecommerce-service" || service_type == "ondemand-service"){
                rows = [
                    { key: 'adminCommission', label: 'Admin Commission' },
                    { key: 'vendorSubscription', label: 'Vendor Subscription' },
                    { key: 'platformFee', label: 'Platform Fee' }
                ];
            }else{
                rows = [
                    { key: 'adminCommission', label: 'Admin Commission' },
                    { key: 'platformFee', label: 'Platform Fee' }
                ];
            }

            rows.forEach((row, i) => {

                let src = totals[row.key];

                html += `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${row.label}</td>
                        <td>$${src.amount.toFixed(2)}</td>
                        <td>${buildTaxSummary(src.taxes)}</td>
                        <td>
                            ${src.amount > 0 
                                ? `<i class="fa fa-eye history-view" style="cursor:pointer" data-source="${row.key}"></i>` 
                                : ''}
                        </td>
                    </tr>`;
            });

            html += '</table>';
            
            $('#tax_report_table_container').html(html);

            $(".download-section").removeClass('d-none');

        } catch (e) {
            Swal.fire({ icon: 'error', text: e.message });
        }

        $("#data-table_processing").hide();
    });


    // Calculate totals including subscription history and filter
    function calculateTotalsWithBreakdown(orders, subscriptions, allTaxes, taxMethod, selectedTaxes) {

        let totals = {
            adminCommission:    { amount: 0, taxes: {} },
            vendorSubscription: { amount: 0, taxes: {} },
            platformFee:        { amount: 0, taxes: {} }
        };

        // Split by scope
        let adminSelectedTaxes = [];
        let vendorSelectedTaxes = [];
        allTaxes.forEach(t => {
            if (!t.enable) return;
            if (selectedTaxes.includes(t.id)) {
                if (t.scope === 'admin_commission') adminSelectedTaxes.push(t.id);
                else if (t.scope === 'vendor_subscription') vendorSelectedTaxes.push(t.id);
            }
        });

        orders.forEach(order => {

            const orderData = normalizeOrder(service_type, order);
            
            let commissionBase = Math.max(0, orderData.subtotal - orderData.discount);
            
            // Admin Commission
            let adminCommission = 0;
            if (orderData.adminCommission && orderData.adminCommissionType) {
                let commissionValue = parseFloat(orderData.adminCommission);

                if (orderData.isCommissionIncluded) {
                    if (orderData.adminCommissionType === 'percentage') {
                        let basePrice = commissionBase / (1 + commissionValue / 100);
                        adminCommission = commissionBase - basePrice;
                    } else {
                        adminCommission = commissionValue;
                    }
                } else {
                    if (orderData.adminCommissionType === 'percentage') {
                        adminCommission = (commissionBase * commissionValue) / 100;
                    } else {
                        adminCommission = commissionValue;
                    }
                }
            }
            
            totals.adminCommission.amount += adminCommission;

            (allTaxes || []).forEach(tax => {
                if (!tax.enable) return;
                if (tax.scope !== 'admin_commission') return;
                if (taxMethod === 'individual' && adminSelectedTaxes.length && !adminSelectedTaxes.includes(tax.id)) return;
                applyTax(totals.adminCommission, adminCommission, tax);
            });

            // Platform Fee
            let platformAmount = parseFloat(orderData.platformFee || 0);
            totals.platformFee.amount += platformAmount;
            (orderData.platformTax || []).forEach(tax => {
                applyTax(totals.platformFee, platformAmount, tax);
            });
        });

        // Vendor Subscription
        subscriptions.forEach(sub => {
            if (!sub.subscription_plan || !sub.subscription_plan.price) return;
            let price = parseFloat(sub.subscription_plan.price);
            totals.vendorSubscription.amount += price;
            (allTaxes || []).forEach(tax => {
                if (!tax.enable) return;
                if (tax.scope !== 'vendor_subscription') return;
                if (taxMethod === 'individual' && vendorSelectedTaxes.length && !vendorSelectedTaxes.includes(tax.id)) return;
                applyTax(totals.vendorSubscription, price, tax);
            });
        });

        return totals;
    }

    function buildTaxSummary(taxes) {
        let percentTaxes = [];
        let fixedTaxes = [];

        let percentTotalAmount = 0;
        let percentTotalRate = 0;
        let fixedTotalAmount = 0;

        Object.values(taxes).forEach(t => {
            let amount = Math.max(0, parseFloat(t.amount) || 0);
            let taxRate = Math.max(0, parseFloat(t.tax) || 0);
            if (t.type === 'percentage') {
                percentTotalAmount += amount;
                percentTotalRate += taxRate;
                percentTaxes.push({
                    ...t,
                    amount,
                    tax: taxRate
                });
            }
            if (t.type === 'fix') {
                fixedTotalAmount += amount;
                fixedTaxes.push({
                    ...t,
                    amount,
                    tax: taxRate
                });
            }
        });

        let html = '';

        if (percentTaxes.length) {
            html += `<strong>Total Percentage Tax (${percentTotalRate.toFixed(2)}%): $${percentTotalAmount.toFixed(2)}</strong><br>`;
            percentTaxes.forEach(t => {
                html += `${t.title} (${t.tax}%): $${t.amount.toFixed(2)}<br>`;
            });
            html += `<br>`;
        }

        if (fixedTaxes.length) {
            html += `<strong>Total Fix Tax ($${fixedTotalAmount.toFixed(2)}): $${fixedTotalAmount.toFixed(2)}</strong><br>`;
            fixedTaxes.forEach(t => {
                html += `${t.title} ($${parseFloat(t.tax).toFixed(2)}): $${t.amount.toFixed(2)}<br>`;
            });
            html += `<br>`;
        }

        let finalTotal = percentTotalAmount + fixedTotalAmount;

        html += `<strong>Final Total Tax: $${finalTotal.toFixed(2)}</strong>`;

        return html;
    }

    function applyTax(target, baseAmount, tax) {
        if (!target.taxes[tax.title]) {
            target.taxes[tax.title] = {
                title: tax.title,
                tax: tax.tax,
                type: tax.type,
                amount: 0
            };
        }
        let taxAmount = tax.type === 'percentage'
            ? (baseAmount * parseFloat(tax.tax) / 100)
            : parseFloat(tax.tax);
        target.taxes[tax.title].amount += taxAmount;
    }

    $(document).on('click', '#download_detailed_report', function () {
        Swal.fire({
            title: 'Download Detailed Report',
            text: 'Choose format to download:',
            showCancelButton: true,
            confirmButtonText: 'CSV',
            cancelButtonText: 'PDF',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                downloadDetailedCSV();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                downloadDetailedPDF();
            }
        });
    });

    $(document).on('click', '#download_report', function () {
        Swal.fire({
            title: 'Download Report',
            text: 'Choose format to download:',
            showCancelButton: true,
            confirmButtonText: 'CSV',
            cancelButtonText: 'PDF',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                downloadCSV();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                downloadPDF();
            }
        });
    });

    // CSV Download
    function downloadCSV() {
        let table = $('#tax_report_table_container table');
        let startDate = $('#reportrange').data('daterangepicker').startDate.format('YYYY-MM-DD');
        let endDate = $('#reportrange').data('daterangepicker').endDate.format('YYYY-MM-DD');
        let displayRange = $('#reportrange').data('daterangepicker').startDate.format('MMMM D, YYYY') + ' - ' + $('#reportrange').data('daterangepicker').endDate.format('MMMM D, YYYY');
                        
        let csv = [];
        csv.push(`"Tax Report"`);
        csv.push(`"Date Range: ${displayRange}"`);
        
        // Table header
        let headerRow = [];
        table.find('thead tr').first().find('th').each(function(tdIndex) {
            if (tdIndex === 4) return;
            headerRow.push(`"${$(this).text().trim().replace(/"/g, '""')}"`);
        });
        csv.push(headerRow.join(','));

        table.find('tbody tr').each(function() {
            let row = [];
            $(this).find('th, td').each(function(tdIndex) {
                if (tdIndex === 4) return;
                let text = $(this).text().trim().replace(/\n/g, ' ');
                text = '"' + text.replace(/"/g, '""') + '"';
                row.push(text);
            });
            csv.push(row.join(','));
        });
        let csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
        let encodedUri = encodeURI(csvContent);
        let link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `tax_report_${startDate}_to_${endDate}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // CSV Download for Detailed Report
    function downloadDetailedCSV() {
        let table = $('#taxDetailContent table');
        let startDate = $('#reportrange').data('daterangepicker').startDate.format('YYYY-MM-DD');
        let endDate = $('#reportrange').data('daterangepicker').endDate.format('YYYY-MM-DD');
        let displayRange = $('#reportrange').data('daterangepicker').startDate.format('MMMM D, YYYY') + ' - ' + $('#reportrange').data('daterangepicker').endDate.format('MMMM D, YYYY');

        let csv = [];
        csv.push(`"Detailed Tax Report"`);
        csv.push(`"Date Range: ${displayRange}"`);
        
        // Table header
        let headerRow = [];
        table.find('thead tr').first().find('th').each(function(tdIndex) {
            if (tdIndex === 4) return;
            headerRow.push(`"${$(this).text().trim().replace(/"/g, '""')}"`);
        });
        csv.push(headerRow.join(','));

        table.find('tbody tr').each(function() {
            let row = [];
            $(this).find('th, td').each(function () {
                let text = $(this).text().trim().replace(/\n/g, ' ');
                text = '"' + text.replace(/"/g, '""') + '"';
                row.push(text);
            });
            if (row.length > 0) {
                csv.push(row.join(','));
            }
        });
        if (!csv.length) {
            return Swal.fire({ icon: 'error', text: 'No data to download!' });
        }
        let csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
        let encodedUri = encodeURI(csvContent);
        let link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `detailed_tax_report_${startDate}_to_${endDate}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // PDF Download
    async function downloadPDF() {
        let table = $('#tax_report_table_container table');
        let startDate = $('#reportrange').data('daterangepicker').startDate.format('YYYY-MM-DD');
        let endDate = $('#reportrange').data('daterangepicker').endDate.format('YYYY-MM-DD');
        let displayRange = $('#reportrange').data('daterangepicker').startDate.format('MMMM D, YYYY') + ' - ' + $('#reportrange').data('daterangepicker').endDate.format('MMMM D, YYYY');

        let tableClone = table.clone();
        tableClone.find('th:last-child').remove();
        tableClone.find('tr').each(function() {
            $(this).find('td:last-child').remove();
        });
        tableClone.css({ position: 'absolute', left: '-9999px', top: '-9999px' });
        $('body').append(tableClone);
        const { jsPDF } = window.jspdf;
        let pdf = new jsPDF('p', 'pt', 'a4');

        pdf.setFontSize(12);
        pdf.text('Tax Report', 40, 40);
        pdf.setFontSize(10);
        pdf.text(`Date Range: ${displayRange}`, 40, 60);
        
        let canvas = await html2canvas(tableClone[0], { scale: 2 });
        let imgData = canvas.toDataURL('image/png');
        let pdfWidth = pdf.internal.pageSize.getWidth();
        let pdfHeight = (canvas.height * pdfWidth) / canvas.width;
        pdf.addImage(imgData, 'PNG', 40, 80, pdfWidth - 80, pdfHeight);
        pdf.save(`tax_report_${startDate}_to_${endDate}.pdf`);
    }

    
    // PDF Download for Detailed Report
    async function downloadDetailedPDF() {
        let table = $('#taxDetailContent table');
        let startDate = $('#reportrange').data('daterangepicker').startDate.format('YYYY-MM-DD');
        let endDate = $('#reportrange').data('daterangepicker').endDate.format('YYYY-MM-DD');
        let displayRange = $('#reportrange').data('daterangepicker').startDate.format('MMMM D, YYYY') + ' - ' + $('#reportrange').data('daterangepicker').endDate.format('MMMM D, YYYY');

        let tableClone = table.clone();
        tableClone.find('br').replaceWith('\n');
        tableClone.css({ position: 'absolute', left: '-9999px', top: '-9999px' });
        $('body').append(tableClone);
        const { jsPDF } = window.jspdf;
        let pdf = new jsPDF('p', 'pt', 'a4');
        
        pdf.setFontSize(12);
        pdf.text('Tax Detailed Report', 40, 40);
        pdf.setFontSize(10);
        pdf.text(`Date Range: ${displayRange}`, 40, 60);

        let canvas = await html2canvas(tableClone[0], { scale: 2 });
        let imgData = canvas.toDataURL('image/png');
        let pdfWidth = pdf.internal.pageSize.getWidth();
        let pdfHeight = (canvas.height * pdfWidth) / canvas.width;
        pdf.addImage(imgData, 'PNG', 40, 80, pdfWidth - 80, pdfHeight);
        pdf.save(`detailed_tax_report_${startDate}_to_${endDate}.pdf`);
    }
    
    $(document).on('click', '.history-view', function() {

        let source = $(this).data('source'); // adminCommission, vendorSubscription, platformFee
        let startDate = $('#reportrange').data('daterangepicker').startDate.format('MMMM D, YYYY');
        let endDate = $('#reportrange').data('daterangepicker').endDate.format('MMMM D, YYYY');

        // Show search criteria
        let filterHtml = `<strong>Date:</strong> ${startDate} - ${endDate}<br>
        <strong>Income Source:</strong> ${source === 'adminCommission' ? 'Admin Commission' : source === 'vendorSubscription' ? 'Vendor Subscription' : 'Platform Fee'}`;
        $('#filterData').html(filterHtml);

        let html = '<table class="table table-bordered">';
        
        let count = 1;

        if (source === 'adminCommission') {

            html += '<tr><th>No</th><th>Order ID</th><th>Order Date</th><th>Admin Commission</th><th>Tax Amount</th></tr>';
            
            window.reportData.orders.forEach(order => {
                
                const orderData = normalizeOrder(service_type, order);
            
                let commissionBase = Math.max(0, orderData.subtotal - orderData.discount);
                
                // Admin Commission
                let adminCommission = 0;
                if (orderData.adminCommission && orderData.adminCommissionType) {
                    let commissionValue = parseFloat(orderData.adminCommission);

                    if (orderData.isCommissionIncluded) {
                        if (orderData.adminCommissionType === 'percentage') {
                            let basePrice = commissionBase / (1 + commissionValue / 100);
                            adminCommission = commissionBase - basePrice;
                        } else {
                            adminCommission = commissionValue;
                        }
                    } else {
                        if (orderData.adminCommissionType === 'percentage') {
                            adminCommission = (commissionBase * commissionValue) / 100;
                        } else {
                            adminCommission = commissionValue;
                        }
                    }
                }
                
                // TAX based on adminCommission as in main list
                let taxes = {};
                if (adminCommission > 0) {
                    (window.reportData.allTaxes || []).forEach(tax => {
                        if (!tax.enable || tax.scope !== 'admin_commission') return;
                        applyTax({ taxes }, adminCommission, tax);
                    });
                }

                html += `<tr>
                            <td>${count++}</td>
                            <td>${order.id}</td>
                            <td>${order.createdAt.toDate().toDateString()}</td>
                            <td>$${adminCommission.toFixed(2)}</td>
                            <td>${buildTaxSummary(taxes)}</td>
                        </tr>`;
            });


        } else if (source === 'vendorSubscription') {

            html += '<tr><th>No</th><th>Order ID</th><th>Order Date</th><th>Subscription Price</th><th>Tax Amount</th></tr>';
            
            window.reportData.subscriptions.forEach(sub => {
                if (!sub.subscription_plan || !sub.subscription_plan.price) return;
                let price = parseFloat(sub.subscription_plan.price);

                let taxes = {};
                if (price > 0) {
                    (window.reportData.allTaxes || []).forEach(tax => {
                        if (!tax.enable || tax.scope !== 'vendor_subscription') return;
                        applyTax({ taxes }, price, tax);
                    });
                }
                
                html += `<tr>
                            <td>${count++}</td>
                            <td>${sub.id}</td>
                            <td>${sub.createdAt.toDate().toDateString()}</td>
                            <td>$${price.toFixed(2)}</td>
                            <td>${buildTaxSummary(taxes)}</td>
                        </tr>`;
            });

        } else if (source === 'platformFee') {

            html += '<tr><th>No</th><th>Order ID</th><th>Order Date</th><th>Platform Fee</th><th>Tax Amount</th></tr>';

            window.reportData.orders.forEach(order => {

                const orderData = normalizeOrder(service_type, order);

                let platformAmount = parseFloat(orderData.platformFee || 0);

                let taxes = {};
                if (platformAmount > 0) {
                    (orderData.platformTax || []).forEach(tax => {
                        applyTax({ taxes }, platformAmount, tax);
                    });
                }

                html += `<tr>
                            <td>${count++}</td>
                            <td>${order.id}</td>
                            <td>${order.createdAt.toDate().toDateString()}</td>
                            <td>$${platformAmount.toFixed(2)}</td>
                            <td>${buildTaxSummary(taxes)}</td>
                        </tr>`;
            });
        }

        html += '</table>';
        
        $('#taxDetailContent').html(html);
        
        $('#taxDetailModal').modal('show');
    });

    function getOrdersCollectionByService(serviceType) {
        switch(serviceType) {
            case 'cab-service': return 'rides';
            case 'parcel_delivery': return 'parcel_orders';
            case 'rental-service': return 'rental_orders';
            case 'ondemand-service': return 'provider_orders';
            case 'delivery-service': return 'vendor_orders';
            default: return 'vendor_orders';
        }
    }

    function getOrdersSectionField(serviceType) {
        switch(serviceType) {
            case 'delivery-service':
            case 'ecommerce-service':
                return 'section_id';
            case 'cab-service':
            case 'parcel_delivery':
            case 'rental-service':
            case 'ondemand-service':
            default:
                return 'sectionId';
        }
    }

    function normalizeOrder(serviceType, order) {
        
        switch (serviceType) {

            case 'cab-service': {
                return {
                    subtotal: parseFloat(order.subTotal || 0),
                    discount: parseFloat(order.discount || 0),
                    taxSetting: order.taxSetting || [],
                    platformFee: parseFloat(order.platformFee || 0),
                    platformTax: order.platformTax || [],
                    adminCommission: order.adminCommission || 0,
                    adminCommissionType: order.adminCommissionType,
                    tip_amount: parseFloat(order.tip_amount || 0),
                    isCommissionIncluded: false
                };
            }

            case 'ondemand-service': {
                let quantity = parseFloat(order.quantity || 1);

                let basePrice = parseFloat(order.provider.price || 0);
                if (order.provider?.disPrice && order.provider.disPrice != '0') {
                    basePrice = parseFloat(order.provider.disPrice);
                }

                let subtotal = basePrice * quantity;

                return {
                    subtotal,
                    discount: order.discount || 0,
                    taxSetting: order.taxSetting || [],
                    platformFee: order.platformFee || 0,
                    platformTax: order.platformTax || [],
                    adminCommission: order.adminCommission || 0,
                    adminCommissionType: order.adminCommissionType,
                    isCommissionIncluded: false
                };
            }

            case 'parcel_delivery': {
                return {
                    subtotal: parseFloat(order.subTotal || 0),
                    discount: order.discount || 0,
                    taxSetting: order.taxSetting || [],
                    platformFee: order.platformFee || 0,
                    platformTax: order.platformTax || [],
                    adminCommission: order.adminCommission || 0,
                    adminCommissionType: order.adminCommissionType,
                    isCommissionIncluded: false
                };
            }

            case 'rental-service': {

                let subTotal = parseFloat(order.subTotal || 0);
                let driverRate = parseFloat(order.driverRate || 0);

                let startKm = parseFloat(order.startKitoMetersReading || 0);
                let endKm = parseFloat(order.endKitoMetersReading || 0);
                let includedDistance = parseFloat(order?.rentalPackageModel?.includedDistance || 0);
                let extraKmFare = parseFloat(order?.rentalPackageModel?.extraKmFare || 0);

                let extraKilometerCharge = 0;
                if (endKm > startKm) {
                    let totalKm = endKm - startKm;
                    if (totalKm > includedDistance) {
                        extraKilometerCharge = (totalKm - includedDistance) * extraKmFare;
                    }
                }

                let extraMinuteFare = parseFloat(order?.rentalPackageModel?.extraMinuteFare || 0);
                let includedHours = parseFloat(order?.rentalPackageModel?.includedHours || 0);
                let includedMinutes = includedHours * 60;

                let extraMinutesCharge = 0;
                let startTime = order.startTime ? order.startTime.toDate() : null;
                let endTime = order.endTime ? order.endTime.toDate() : null;

                if (startTime && endTime) {
                    let totalMinutesUsed = Math.floor((endTime - startTime) / (1000 * 60));
                    if (totalMinutesUsed > includedMinutes) {
                        extraMinutesCharge = (totalMinutesUsed - includedMinutes) * extraMinuteFare;
                    }
                }

                let subtotal = subTotal + driverRate + extraKilometerCharge + extraMinutesCharge;

                return {
                    subtotal,
                    discount: parseFloat(order.discount || 0),
                    taxSetting: order.taxSetting || [],
                    platformFee: parseFloat(order.platformFee || 0),
                    platformTax: order.platformTax || [],
                    adminCommission: order.adminCommission || 0,
                    adminCommissionType: order.adminCommissionType,
                    isCommissionIncluded: false
                };
            }

            case 'delivery-service':
            
            default: {

                let orderSubtotal = 0;

                (order.products || []).forEach(p => {
                    const price = parseFloat(p.discountPrice ?? p.price);
                    const extras = parseFloat(p.extras_price || 0);
                    const qty = parseInt(p.quantity || 1);
                    orderSubtotal += (price + extras) * qty;
                });

                return {
                    subtotal: orderSubtotal,
                    discount: (order.discount || 0) + (order.specialDiscount?.special_discount || 0),
                    taxSetting: order.taxSetting || [],
                    platformFee: order.platformFee || 0,
                    platformTax: order.platformTax || [],
                    adminCommission: order.adminCommission || 0,
                    adminCommissionType: order.adminCommissionType,
                    isCommissionIncluded: true
                };
            }
        }
    }

</script>

@endsection
