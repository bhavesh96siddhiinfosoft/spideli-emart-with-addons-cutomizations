@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{trans('lang.reports_sale')}}</h3>
            </div>

            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.reports_sale')}}</li>
                </ol>
            </div>
            <div>

                <div class="card-body">
                    <div class="error_top"></div>

                    <div class="row vendor_payout_create">
                        <div class="vendor_payout_create-inner">
                            <fieldset>
                                <legend>{{trans('lang.reports_sale')}}</legend>

                                <div class="form-group row width-50 vendors_div">
                                    <label class="col-3 control-label">{{trans('lang.select_vendor')}}</label>
                                    <div class="col-7">
                                        <select class="form-control vendors">
                                            <option value="">{{trans('lang.all')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row width-50 driver_div">
                                    <label class="col-3 control-label">{{trans('lang.select_driver')}}</label>
                                    <div class="col-7">
                                        <select class="form-control driver">
                                            <option value="">{{trans('lang.all')}}</option>
                                        </select>
                                    </div>
                                </div>
                                 <div class="form-group row width-50 provider_div d-none">
                                    <label class="col-3 control-label">{{trans('lang.select_provider')}}</label>
                                    <div class="col-7">
                                        <select class="form-control providers">
                                            <option value="">{{trans('lang.all')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row width-50 worker_div d-none">
                                    <label class="col-3 control-label">{{trans('lang.select_worker')}}</label>
                                    <div class="col-7">
                                        <select class="form-control workers">
                                            <option value="">{{trans('lang.all')}}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.select_user')}}</label>
                                    <div class="col-7">
                                        <select class="form-control customer">
                                            <option value="">{{trans('lang.all')}}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row width-50 category_div">
                                    <label class="col-3 control-label">{{trans('lang.select_category')}}</label>
                                    <div class="col-7">
                                        <select class="form-control category">
                                            <option value="">{{trans('lang.all')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-3 control-label">{{trans('lang.select_date')}}</label>
                                    <div class="col-7">
                                        <div id="reportrange"
                                             style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                            <i class="fa fa-calendar"></i>&nbsp;
                                            <span></span> <i class="fa fa-caret-down"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row width-100">
                                    <label class="col-3 control-label">{{trans('lang.file_format')}}<span
                                                class="required-field"></span></label>
                                    <div class="col-7">
                                        <select class="form-control file_format">
                                            <option value="">{{trans('lang.file_format')}}</option>
                                            <option value="csv">{{trans('lang.csv')}}</option>
                                            <option value="pdf">{{trans('lang.pdf')}}</option>
                                        </select>
                                    </div>
                                </div>


                            </fieldset>


                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="submit" class="btn btn-primary download-sales-report"><i
                                    class="fa fa-save"></i> {{ trans('lang.download')}}</button>

                    </div>

                </div>
            </div>

        </div>

    </div>


@endsection
@section('scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>

    <script>
        var database = firebase.firestore();
        var refCurrency = regionCurrencyRef().limit('1');
        var vendorsRef = database.collection('vendors').orderBy('title').orderBy('createdAt');
        var driverUserRef = database.collection('users').where('role', '==', 'driver').orderBy('firstName').orderBy('createdAt');
        var customerRef = database.collection('users').where('role', '==', 'customer').orderBy('firstName').orderBy('createdAt');
        var categoryRef = database.collection('vendor_categories').orderBy('title');
        var paymentMethodRef = database.collection('settings').doc('payment');
        var providerRef = database.collection('users').where('role', '==', 'provider').orderBy('firstName').orderBy('createdAt');
        var workerRef = database.collection('providers_workers').orderBy('firstName');

        var section_id = getCookie('section_id') || '';
        var service_type = getCookie('service_type') || '';

        setDate();

        async function setDate() {
            var start = moment().subtract(29, 'days');
            var end = moment();

            function cb(start, end) {
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            }

            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);

            cb(start, end);

            $('#data-table_processing').show();
            
            if (service_type == 'cab-service' || service_type == 'parcel_delivery' || service_type == 'rental-service') {
                $('.vendors_div').addClass('d-none');
                $('.category_div').addClass('d-none');

            }else if(service_type=='ondemand-service'){
                $('.vendors_div').addClass('d-none');
                $('.category_div').addClass('d-none');
                $('.driver_div').addClass('d-none');
                $('.provider_div').removeClass('d-none');
                $('.worker_div').removeClass('d-none');
            }
            else {

                $('.vendors_div').removeClass('d-none');
                $('.category_div').removeClass('d-none');

                await database.collection('vendors').orderBy('title').where('section_id', '==', section_id).get().then(function (snapShots) {
                    $('.vendors').empty();
                    $('.vendors').html('<option value="">{{trans("lang.all")}}</option>');

                    if (snapShots.docs.length > 0) {

                        snapShots.docs.forEach((listval) => {
                            var data = listval.data();
                            arraySectionVendor.push(data.id);
                            $('.vendors').append('<option value="' + data.id + '">' + data.title + '</option>');
                        });

                    }
                });

                await database.collection('vendor_categories').orderBy('title').where('section_id', '==', section_id).get().then(function (snapShots) {
                    $('.category').empty();
                    $('.category').html('<option value="">{{trans("lang.all")}}</option>');

                    if (snapShots.docs.length > 0) {

                        snapShots.docs.forEach((listval) => {
                            var data = listval.data();
                            arraySectionCategory.push(data.id);

                            $('.category').append('<option value="' + data.id + '">' + data.title + '</option>');
                        });

                    }
                });
            }

            await database.collection('users').where('role', '==', 'driver').orderBy('firstName').where('serviceType', '==', service_type).get().then(function (snapShots) {
                $('.driver').empty();
                $('.driver').html('<option value="">{{trans("lang.all")}}</option>');

                if (snapShots.docs.length > 0) {

                    snapShots.docs.forEach((listval) => {
                        var data = listval.data();
                        arraySectionDriver.push(data.id);

                        $('.driver').append('<option value="' + data.id + '">' + data.firstName + ' ' + data.lastName + '</option>');
                    });

                }
            });

            $('#data-table_processing').hide();
        }

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
        
        
        customerRef.get().then(function (snapShots) {
            if (snapShots.docs.length > 0) {
                snapShots.docs.forEach((listval) => {
                    var data = listval.data();
                    $('.customer').append('<option value="' + data.id + '">' + data.firstName + ' ' + data.lastName + '</option>');
                });
            }
        });
        
        getProviders();
        
        getWorkers();
        
        async function getProviders(){

            providerRef.get().then(function (snapShots) {

                if (snapShots.docs.length > 0) {

                    snapShots.docs.forEach((listval) => {
                        var data = listval.data();

                        $('.providers').append('<option value="' + data.id + '">' + data.firstName + ' ' + data.lastName + '</option>');
                    });

                }
            });
        }

        async function getWorkers(){ 
            workerRef.get().then(function (snapShots) {

                if (snapShots.docs.length > 0) {

                    snapShots.docs.forEach((listval) => {
                        var data = listval.data();

                        $('.workers').append('<option value="' + data.id + '">' + data.firstName + ' ' + data.lastName + '</option>');
                    });

                }
            });
        }

        $('.providers').on('change',function(){
            var providerId=$('.providers').val();
            if(providerId!=''){
                    workerRef.where('providerId','==',providerId).get().then(function (snapShots) {

                    if (snapShots.docs.length > 0) {

                        snapShots.docs.forEach((listval) => {
                            var data = listval.data();
                            $('.workers').html('');
                            $('.workers').append('<option value="">All</option>')
                            $('.workers').append('<option value="' + data.id + '">' + data.firstName + ' ' + data.lastName + '</option>');
                        });

                    }
            
                });
            }else{
                getWorkers();
            }
        });

        var arraySectionVendor = [];
        var arraySectionDriver = [];
        var arraySectionCategory = [];

        async function generateReport(orderData, headers, fileFormat) {

            if ((fileFormat == "pdf") ? document.title = "sales-report" : "") ;

            objectExporter({
                type: fileFormat,
                exportable: orderData,
                headers: headers,
                fileName: 'sales-report',
                columnSeparator: ',',
                headerStyle: 'font-weight: bold; padding: 5px; border: 1px solid #dddddd;',
                cellStyle: 'border: 1px solid lightgray; margin-bottom: -1px;',
                sheetName: 'sales-report',
                documentTitle: '',
            });

        }

        async function getReportData(orderSnapshots, service_type) {

            var orderData = [];

            await Promise.all(orderSnapshots.docs.map(async (order) => {

                var orderObj = order.data();
                var orderId = orderObj.id;
                var finalOrderObject = {};

                finalOrderObject['Order ID'] = orderId;
                var driverData = ((orderObj.driver && orderObj.driver != null) ? orderObj.driver : '');

                if (service_type == 'cab-service') {
                    finalOrderObject['From'] = ((orderObj.sourceLocationName) ? orderObj.sourceLocationName.replace(/,/g, " ") : "");
                    finalOrderObject['To'] = ((orderObj.destinationLocationName) ? orderObj.destinationLocationName.replace(/,/g, " ") : "");
                    finalOrderObject['Driver Name'] = (driverData.firstName) ? ((driverData.lastName) ? driverData.firstName + ' ' + driverData.lastName : driverData.firstName) : "";
                    finalOrderObject['Driver Email'] = (driverData.email) ? shortEmail(driverData.email) : "";
                    finalOrderObject['Driver Phone'] = ((driverData.phoneNumber) ? (driverData.phoneNumber.includes('+') ? EditPhoneNumber(driverData.phoneNumber.slice(1)) : '(+) ' + EditPhoneNumber(driverData.phoneNumber)) : '');
                    finalOrderObject['Vehicle Name'] = ((driverData.carName) ? driverData.carName : "");
                    finalOrderObject['Vehicle Make'] = ((driverData.carMakes) ? driverData.carMakes : "");
                    finalOrderObject['Vehicle Number'] = ((driverData.carNumber) ? driverData.carNumber : "");
                }
                else if (service_type == 'parcel_delivery') {
                    finalOrderObject['From'] = ((orderObj.sender.address) ? orderObj.sender.address.replace(/,/g, " ") : "");
                    finalOrderObject['To'] = ((orderObj.receiver.address) ? orderObj.receiver.address.replace(/,/g, " ") : "");
                    finalOrderObject['Driver Name'] = (driverData.firstName) ? ((driverData.lastName) ? driverData.firstName + ' ' + driverData.lastName : driverData.firstName) : "";
                    finalOrderObject['Driver Email'] = (driverData.email) ? shortEmail(driverData.email) : "";
                    finalOrderObject['Driver Phone'] = ((driverData.phoneNumber) ? (driverData.phoneNumber.includes('+') ? EditPhoneNumber(driverData.phoneNumber.slice(1)) : '(+) ' + EditPhoneNumber(driverData.phoneNumber)) : '');
                    finalOrderObject['Vehicle Name'] = ((driverData.carName) ? driverData.carName : "");
                    finalOrderObject['Vehicle Make'] = ((driverData.carMakes) ? driverData.carMakes : "");
                    finalOrderObject['Vehicle Number'] = ((driverData.carNumber) ? driverData.carNumber : "");
                } 
                else if (service_type == 'rental-service') {
                    finalOrderObject['From'] = ((orderObj.pickupAddress) ? orderObj.pickupAddress.replace(/,/g, " ") : "");
                    finalOrderObject['To'] = ((orderObj.dropAddress) ? orderObj.dropAddress.replace(/,/g, " ") : "");
                    finalOrderObject['Driver Name'] = (driverData.firstName) ? ((driverData.lastName) ? driverData.firstName + ' ' + driverData.lastName : driverData.firstName) : "";
                    finalOrderObject['Driver Email'] = (driverData.email) ? shortEmail(driverData.email) : "";
                    finalOrderObject['Driver Phone'] = ((driverData.phoneNumber) ? (driverData.phoneNumber.includes('+') ? EditPhoneNumber(driverData.phoneNumber.slice(1)) : '(+) ' + EditPhoneNumber(driverData.phoneNumber)) : '');
                    finalOrderObject['Vehicle Name'] = ((driverData.carName) ? driverData.carName : "");
                    finalOrderObject['Vehicle Make'] = ((driverData.carMakes) ? driverData.carMakes : "");
                    finalOrderObject['Vehicle Number'] = ((driverData.carNumber) ? driverData.carNumber : "");
                } 
                else if(service_type=='ondemand-service'){
                    var workerData=((orderObj.workerId && orderObj.workerId != null && orderObj.workerId != '')? await getWorkerData(orderObj.workerId) : '');
                    finalOrderObject['Provider'] = ((orderObj.provider.authorName) ? orderObj.provider.authorName : "");
                    finalOrderObject['Provider Phone'] = ((orderObj.provider.phoneNumber) ? EditPhoneNumber(orderObj.provider.phoneNumber) : "");
                    finalOrderObject['Worker Name'] = ((workerData.firstName) ? workerData.firstName+' '+workerData.lastName : "");
                    finalOrderObject['Worker Phone'] = ((workerData.phoneNumber) ? EditPhoneNumber(workerData.phoneNumber) : "");
                    finalOrderObject['Service Name'] = ((orderObj.provider.title) ? orderObj.provider.title : "");
                }
                else {
                    var vendorData = ((orderObj.vendor && orderObj.vendor != null) ? orderObj.vendor : '');
                    finalOrderObject['Vendor Name'] = ((vendorData.title) ? vendorData.title : "");
                    finalOrderObject['Category'] = ((vendorData.categoryTitle) ? vendorData.categoryTitle : "");
                    finalOrderObject['Driver Name'] = (driverData.firstName) ? ((driverData.lastName) ? driverData.firstName + ' ' + driverData.lastName : driverData.firstName) : "";
                    finalOrderObject['Driver Email'] = (driverData.email) ? shortEmail(driverData.email) : "";
                    finalOrderObject['Driver Phone'] = ((driverData.phoneNumber) ? (driverData.phoneNumber.includes('+') ? EditPhoneNumber(driverData.phoneNumber.slice(1)) : '(+) ' + EditPhoneNumber(driverData.phoneNumber)) : '');

                }


                var userData = ((orderObj.author && orderObj.author != null) ? orderObj.author : '');
                var date = orderObj.createdAt.toDate();

                var distanceType = ((orderObj.distanceType && orderObj.distanceType != "" && orderObj.distanceType != null) ? orderObj.distanceType : "");

                finalOrderObject['User Name'] = ((userData.firstName) ? ((userData.lastName) ? userData.firstName + ' ' + userData.lastName : userData.firstName) : "");
                finalOrderObject['User Email'] = ((userData.email) ? shortEmail(userData.email) : "");
                finalOrderObject['User Phone'] = ((userData.phoneNumber) ? (userData.phoneNumber.includes('+') ? EditPhoneNumber(userData.phoneNumber.slice(1)) : '(+) ' + EditPhoneNumber(userData.phoneNumber)) : '');

                finalOrderObject['Date'] = moment(date).format('ddd MMM DD YYYY h:mm:ss A');

                if (service_type == 'cab-service') {
                    finalOrderObject['Payment Method'] = orderObj.paymentMethod;

                } else {
                    finalOrderObject['Payment Method'] = orderObj.payment_method;

                }
                var total_amount = getProductsTotal(orderObj, service_type);
                var adminCommission = getAdminCommission(orderObj, service_type);

                if (symbolAtRight) {
                    total_amount = parseFloat(total_amount).toFixed(decimal_degits) + "" + currentCurrency;
                    adminCommission = parseFloat(adminCommission).toFixed(decimal_degits) + "" + currentCurrency;
                } else {
                    total_amount = currentCurrency + "" + parseFloat(total_amount).toFixed(decimal_degits);
                    adminCommission = currentCurrency + "" + parseFloat(adminCommission).toFixed(decimal_degits);
                }

                finalOrderObject['Total'] = (total_amount);
                finalOrderObject['Admin Commission'] = adminCommission;

                orderData.push(finalOrderObject);
            }));

            return orderData;
        }
        
        function getProductsTotal(orderData, service_type) {

            let total_price = 0;
            let discount = parseFloat(orderData.discount || 0);
            let tip_amount = parseFloat(orderData.tip_amount || 0);
            let deliveryCharge = parseFloat(orderData.deliveryCharge || 0);
            let total_tax_amount = 0;
            
            const intRegex = /^\d+$/;
            const floatRegex = /^((\d+(\.\d*)?)|((\d*\.)?\d+))$/;

            function calculateTaxes(amount, taxSettings = []) {
                let taxTotal = 0;
                if (taxSettings && taxSettings.length > 0) {
                    taxSettings.forEach(tax => {
                        if (tax.enable) {
                            let taxAmount = 0;
                            if (tax.type === 'percentage') {
                                taxAmount = (tax.tax / 100) * amount;
                            } else {
                                taxAmount = parseFloat(tax.tax);
                            }
                            taxTotal += taxAmount;
                        }
                    });
                }
                return taxTotal;
            }

            if (service_type === 'ondemand-service') {
                // Item price calculation
                let itemPrice = parseFloat(orderData.provider.price || 0);
                if (orderData.provider.disPrice && parseFloat(orderData.provider.disPrice) > 0) {
                    itemPrice = parseFloat(orderData.provider.disPrice);
                }
                let quantity = parseInt(orderData.quantity || 1);
                total_price += itemPrice * quantity;

                // Apply discount
                if ((intRegex.test(discount) || floatRegex.test(discount)) && discount > 0) {
                    total_price -= discount;
                }

                // Apply taxes
                total_tax_amount = calculateTaxes(total_price, orderData.taxSetting);
                total_price += total_tax_amount;

                // Add tip if any
                if (!isNaN(tip_amount) && tip_amount > 0) {
                    total_price += tip_amount;
                }

            } else if (service_type === 'parcel_delivery' || service_type === 'cab-service' || service_type === 'rental-service') {

                let subTotal = parseFloat(orderData.subTotal || 0);
                let driverRate = parseFloat(orderData.driverRate || 0);
                total_price = subTotal + driverRate;

                // Apply discount
                if ((intRegex.test(discount) || floatRegex.test(discount)) && discount > 0) {
                    total_price -= discount;
                }

                // Taxes
                total_tax_amount = calculateTaxes(total_price, orderData.taxSetting);
                total_price += total_tax_amount;

                // Tip / delivery charges
                if (!isNaN(tip_amount)) total_price += tip_amount;
                if (!isNaN(deliveryCharge)) total_price += deliveryCharge;

            } else {

                // Default / delivery service
                let products = orderData.products || [];
                products.forEach(product => {
                    let basePrice = parseFloat(product.price || 0);
                    if (product.discountPrice && parseFloat(product.discountPrice) > 0) {
                        basePrice = parseFloat(product.discountPrice);
                    }
                    let quantity = parseInt(product.quantity || 1);
                    let extrasPrice = parseFloat(product.extras_price || 0) * quantity;
                    total_price += (basePrice * quantity) + extrasPrice;
                });

                // Discounts
                if ((intRegex.test(discount) || floatRegex.test(discount)) && discount > 0) {
                    total_price -= discount;
                }
                if (orderData.specialDiscount) {
                    total_price -= parseFloat(orderData.specialDiscount.special_discount || 0);
                }

                // Taxes
                total_tax_amount = calculateTaxes(total_price, orderData.taxSetting);
                total_price += total_tax_amount;

                // Delivery charge & tip
                if (!isNaN(deliveryCharge)) total_price += deliveryCharge;
                if (!isNaN(tip_amount)) total_price += tip_amount;
            }

            return parseFloat(total_price).toFixed(decimal_degits);
        }

        function getAdminCommission(orderData, service_type) {
            let admin_commission = 0;
            let total_price = 0;

            const intRegex = /^\d+$/;
            const floatRegex = /^((\d+(\.\d*)?)|((\d*\.)?\d+))$/;

            // Helper function to compute total for ondemand / delivery items
            function calculateOrderTotal(data) {

                let price = 0;

                if (service_type === 'ondemand-service') {

                    let basePrice = parseFloat(data.provider.price || 0);
                    if (data.provider.disPrice && parseFloat(data.provider.disPrice) > 0) {
                        basePrice = parseFloat(data.provider.disPrice);
                    }
                    let quantity = parseInt(data.quantity || 1);
                    price += basePrice * quantity;

                } else if (service_type === 'parcel_delivery' || service_type === 'cab-service' || service_type === 'rental-service') {

                    let subTotal = parseFloat(data.subTotal || 0);
                    let driverRate = parseFloat(data.driverRate || 0);
                    price += subTotal + driverRate;

                } else {
                    // delivery/restaurant service
                    let products = data.products || [];
                    products.forEach(product => {
                        let basePrice = parseFloat(product.price || 0);
                        if (product.discountPrice && parseFloat(product.discountPrice) > 0) {
                            basePrice = parseFloat(product.discountPrice);
                        }
                        let quantity = parseInt(product.quantity || 1);
                        let extrasPrice = parseFloat(product.extras_price || 0) * quantity;
                        price += (basePrice * quantity) + extrasPrice;
                    });
                }

                // Apply discount
                let discount = parseFloat(data.discount || 0);
                if ((intRegex.test(discount) || floatRegex.test(discount)) && discount > 0) {
                    price -= discount;
                }

                // Apply special discount if exists
                if (data.specialDiscount) {
                    price -= parseFloat(data.specialDiscount.special_discount || 0);
                }

                // Add tip & delivery if applicable
                if (!isNaN(data.tip_amount)) price += parseFloat(data.tip_amount);
                if (!isNaN(data.deliveryCharge)) price += parseFloat(data.deliveryCharge);

                // Add taxes
                if (data.taxSetting && Array.isArray(data.taxSetting)) {
                    data.taxSetting.forEach(tax => {
                        if (tax.enable) {
                            if (tax.type === 'percentage') {
                                price += (tax.tax / 100) * price;
                            } else {
                                price += parseFloat(tax.tax);
                            }
                        }
                    });
                }

                return price;
            }

            total_price = calculateOrderTotal(orderData);

            // Calculate admin commission
            if (orderData.adminCommission != undefined && orderData.adminCommissionType != undefined) {
                if (orderData.adminCommissionType.toLowerCase() === "percent") {
                    admin_commission = (total_price * parseFloat(orderData.adminCommission)) / 100;
                } else {
                    admin_commission = parseFloat(orderData.adminCommission);
                }
            } else if (orderData.adminCommission != undefined) {
                admin_commission = parseFloat(orderData.adminCommission);
            }

            return parseFloat(admin_commission).toFixed(decimal_degits);
        }

        $(document).on('click', '.download-sales-report', function () {

            var vendors = $(".vendors :selected").val();
            var driver = $(".driver :selected").val();
            var customer = $(".customer :selected").val();
            var provider = $(".providers :selected").val();
            var worker = $(".workers :selected").val();
            var category = $(".category :selected").val();
            var payment_method = $(".payment_method :selected").val();
            var fileFormat = $(".file_format :selected").val();
            let start_date = moment($('#reportrange').data('daterangepicker').startDate).toDate();
            let end_date = moment($('#reportrange').data('daterangepicker').endDate).toDate();

            var headers = [];

            $(".error_top").html("");

            if (fileFormat == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.file_format_error')}}</p>");
                window.scrollTo(0, 0);
            } else {
                jQuery("#data-table_processing").show();

                var ordersRef = "";
                var headerArray = [];


                if (service_type == 'cab-service') {
                    ordersRef = database.collection('rides').where('status', 'in', ["Order Completed"]).orderBy('createdAt', 'desc');
                    headerArray = ['Order ID', 'From', 'To', 'Driver Name', 'Driver Email', 'Driver Phone', 'Vehicle Name', 'Vehicle Make', 'Vehicle Number', 'User Name', 'User Email', 'User Phone', 'Date', 'Payment Method', 'Total', 'Admin Commission'];
                    if (driver == "") {
                        ordersRef = ordersRef.where('driver.service_type', '==', service_type)
                    }


                } else if (service_type == 'parcel_delivery') {
                    ordersRef = database.collection('parcel_orders').where('status', 'in', ["Order Completed"]).orderBy('createdAt', 'desc');
                    headerArray = ['Order ID', 'From', 'To', 'Driver Name', 'Driver Email', 'Driver Phone', 'Vehicle Name', 'Vehicle Make', 'Vehicle Number', 'User Name', 'User Email', 'User Phone', 'Date', 'Payment Method', 'Total', 'Admin Commission'];

                    if (driver == "") {
                        ordersRef = ordersRef.where('driver.service_type', '==', service_type)
                    }


                } else if (service_type == 'rental-service') {
                    ordersRef = database.collection('rental_orders').where('status', 'in', ["Order Completed"]).orderBy('createdAt', 'desc');
                    headerArray = ['Order ID', 'From', 'To', 'Driver Name', 'Driver Email', 'Driver Phone', 'Vehicle Name', 'Vehicle Make', 'Vehicle Number', 'User Name', 'User Email', 'User Phone', 'Date', 'Payment Method', 'Total', 'Admin Commission'];

                    if (driver == "") {
                        ordersRef = ordersRef.where('driver.service_type', '==', service_type)
                    }

                } else if (service_type == 'ondemand-service') {
                    ordersRef = database.collection('provider_orders').where('status', 'in', ["Order Completed"]).where('sectionId','==',section_id).orderBy('createdAt', 'desc');
                   
                    if(provider!=''){
                            ordersRef = ordersRef.where('provider.author', '==', provider);
                    }
                    if (worker != "") {
                        ordersRef = ordersRef.where('workerId', '==', worker);
                    }
                    if (customer != "") {
                        ordersRef = ordersRef.where('authorID', '==', customer);
                    }
                    headerArray = ['Order ID', 'Provider', 'Provider Phone', 'Worker Name', 'Worker Phone', 'Service Name','User Name', 'User Email', 'User Phone', 'Date', 'Payment Method', 'Total', 'Admin Commission'];
                }
                else {

                    headerArray = ['Order ID', 'Vendor Name', 'Category', 'Driver Name', 'Driver Email', 'Driver Phone', 'User Name', 'User Email', 'User Phone', 'Date', 'Payment Method', 'Total', 'Admin Commission'];

                    ordersRef = database.collection('vendor_orders').where('status', 'in', ["Order Completed"]).where('section_id', '==', section_id).orderBy('createdAt', 'desc');

                    if (vendors != "") {
                        ordersRef = ordersRef.where('vendorID', '==', vendors)
                    }

                    if (category != "") {
                        ordersRef = ordersRef.where('vendor.categoryID', '==', category)
                    }
                }

                if (driver != "") {
                    ordersRef = ordersRef.where('driverID', '==', driver)
                }


                if (customer != "") {
                    ordersRef = ordersRef.where('authorID', '==', customer)
                }

                if (start_date != "") {
                    ordersRef = ordersRef.where('createdAt', '>=', start_date)
                }

                if (end_date != "") {
                    ordersRef = ordersRef.where('createdAt', '<=', end_date)
                }

                if (fileFormat == 'xls' || fileFormat == 'csv') {
                    headers = headerArray;
                    var script = document.createElement("script");
                    script.setAttribute("src", "https://unpkg.com/object-exporter@3.2.1/dist/objectexporter.min.js");

                    var head = document.head;
                    head.insertBefore(script, head.firstChild);
                } else {
                    for (var k = 0; k < headerArray.length; k++) {
                        headers.push({
                            alias: headerArray[k],
                            name: headerArray[k],
                            flex: 1,
                        });
                    }

                    var script = document.createElement("script");
                    script.setAttribute("src", "{{ asset('js/objectexporter.min.js') }}");
                    script.setAttribute("async", "false");
                    var head = document.head;
                    head.insertBefore(script, head.firstChild);

                }

                ordersRef.get().then(async function (orderSnapshots) {

                    if (orderSnapshots.docs.length > 0) {
                        var reportData = await getReportData(orderSnapshots, service_type);

                        generateReport(reportData, headers, fileFormat);

                        jQuery("#data-table_processing").hide();
                        setDate();
                        $('.file_format').val('').trigger('change');
                        $('.section_id').val('').trigger('change');
                        $('.driver').val('').trigger('change');
                        $('.customer').val('').trigger('change');
                        $('.providers').val('').trigger('change');
                        $('.workers').val('').trigger('change');
                        $('.service').val('').trigger('change');
                        $('.status').val('').trigger('change');
                        $('.payment_method').val('').trigger('change');
                        $('.payment_status').val('').trigger('change');

                    } else {
                        jQuery("#data-table_processing").hide();
                        setDate();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{trans('lang.not_found_data_error')}}</p>");
                        window.scrollTo(0, 0);

                    }

                }).catch((error) => {

                    jQuery("#data-table_processing").hide();

                    console.log("Error getting documents: ", error);
                    $(".error_top").show();
                    $(".error_top").html(error);
                    window.scrollTo(0, 0);
                });
            }
        });

       async function getWorkerData(workerId){
            var workerData='';
            await database.collection('providers_workers').get().then(async function(snapshot){
                if(snapshot.docs.length>0){
                    workerData=snapshot.docs[0].data();
                }
            })
            return workerData;
       }

    </script>

@endsection