@extends('layouts.app')

@section('content')

    <div id="main-wrapper" class="page-wrapper" style="min-height: 207px;">

        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor" id="section_title"></h3>
            </div>
        </div>


        <div class="container-fluid">

            <div class="top-filter">
                <div class="row">
                    <div class="col-lg-12">

                        <div class="sis-card-head-select-box d-flex align-items-center gap-2 mb-4">
                            <div class="head-select-box">
                                <label class="mb-0 text-dark-2">{{ trans('lang.filter_by') }}:</label>
                                <select id="viewFilter" name="view" class="form-control">
                                    <option value="">{{ trans('lang.all') }}</option>
                                    <option value="year">{{ trans('lang.view_full_year') }}</option>
                                    <option value="month">{{ trans('lang.view_by_month') }}</option>
                                    <option value="custom">{{ trans('lang.custom_date_range') }}</option>
                                </select>
                            </div>

                            <div id="monthYearFilters" class="head-select-box" style="display:inline-block;">
                                <select id="monthFilter" name="month" class="form-control" style="display: none;">
                                    <option value="1">{{ trans('lang.january') }}</option>
                                    <option value="2">{{ trans('lang.february') }}</option>
                                    <option value="3">{{ trans('lang.march') }}</option>
                                    <option value="4">{{ trans('lang.april') }}</option>
                                    <option value="5">{{ trans('lang.may') }}</option>
                                    <option value="6">{{ trans('lang.june') }}</option>
                                    <option value="7">{{ trans('lang.july') }}</option>
                                    <option value="8">{{ trans('lang.august') }}</option>
                                    <option value="9">{{ trans('lang.september') }}</option>
                                    <option value="10">{{ trans('lang.october') }}</option>
                                    <option value="11">{{ trans('lang.november') }}</option>
                                    <option value="12">{{ trans('lang.december') }}</option>

                                </select>
                                <select id="yearFilter" name="year" class="form-control" style="display:none">
                                   
                                </select>
                            </div>

                            <div id="customDateFilters" class="head-select-box" style="display: none;">
                                <input class="form-control" type="date" name="start_date" id="startDate" value="">
                                <input class="form-control" type="date" name="end_date" id="endDate" value="">
                            </div>
                            <button type="button" id="applyFilterBtn" class="btn btn-primary">{{trans('lang.apply_filter')}}</button>
                            <a href="#" class="btn btn-secondary" onclick="window.location.reload();">{{trans('lang.clear_filter')}}</a>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-7">
                    <div class="row">
                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/total_earnings.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                            {{ trans('lang.dashboard_total_earnings') }}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning" id="earnings_count"></h2>
                                        <h6 id="earning_percent" class="up-down-list font-semibold "></h6>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/admin_commission.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                            {{ trans('lang.admin_commission') }}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning"
                                            id="admincommission_count"></h2>
                                        <h6 id="commission_percent" class="up-down-list font-semibold "></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border cursor-pointer" onclick="location.href='{!! route('rides') !!}'">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/total_booking.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                            {{ trans('lang.dashboard_total_rides') }}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning" id="total_rides"></h2>
                                        <h6 id="rides_percent" class="up-down-list font-semibold"></h6>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border cursor-pointer" onclick="location.href='{!! route('users') !!}'">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/total_customers.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                            {{trans('lang.dashboard_total_clients')}}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning" id="users_count"></h2>
                                        <h6 id="customer_percent" class="up-down-list font-semibold "></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border cursor-pointer" onclick="location.href='{!! route('drivers') !!}'">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/total_drivers.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                            {{trans('lang.dashboard_total_drivers')}}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning" id="driver_count"></h2>
                                        <h6 id="driver_percent" class="up-down-list font-semibold "></h6>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

                <div class="col-sm-5">
                    <div class="card border">

                        <div class="card-header no-border">
                            <h3 class="card-title">{{ trans('lang.order_status_overview') }}</h3>
                            <p class="mb-0">{{ trans('lang.quick_insight_orders') }}</p>

                        </div>
                        <div class="card-body">
                            <canvas id="order_status" height="330"></canvas>
                            <input type="hidden" name="placed_count" id="placed_count">
                            <input type="hidden" name="confirmed_count" id="confirmed_count">
                            <input type="hidden" name="shipped_count" id="shipped_count">
                            <input type="hidden" name="completed_count" id="completed_count">
                            <input type="hidden" name="canceled_count" id="canceled_count">
                            <input type="hidden" name="failed_count" id="failed_count">
                            <input type="hidden" name="pending_count" id="pending_count">

                        </div>
                    </div>
                </div>

            </div>


            <div class="row daes-sec-sec">
                <div class="col-lg-5 col-md-12">
                    <div class="card border">
                        <div class="card-header no-border">

                            <h3 class="card-title">{{ trans('lang.total_sales') }}</h3>
                            <p class="mb-0">{{ trans('lang.quick_insight_sales') }}</p>

                        </div>
                        <div class="card-body">
                            <div class="position-relative mb-4">
                                <canvas id="sales-chart" height="250"></canvas>
                            </div>

                            <div class="d-flex flex-row justify-content-end">
                                <span class="mr-2"> <i class="fa fa-square" style="color:red"></i>
                                    {{ trans('lang.dashboard_this_year') }} </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 col-md-12">
                    <div class="card border">
                        <div class="card-header no-border">

                            <h3 class="card-title">{{ trans('lang.service_overview') }}</h3>
                            <p class="mb-0">{{ trans('lang.quick_insight_sales_overview') }}</p>

                        </div>
                        <div class="card-body">
                            <div class="flex-row">
                                <canvas id="commissions" height="250"></canvas>

                            </div>
                        </div>
                        <div class="d-flex flex-row justify-content-end">
                            <span class="mr-2"> <i class="fa fa-square" style="color:red"></i>
                                {{ trans('lang.dashboard_this_year') }} </span>
                        </div>
                    </div>
                </div>


            </div>

            <div class="row daes-sec-sec pt-3">

                <div class="col-md-6 col-lg-6">
                    <div class="card border">
                        <div class="card-header no-border d-flex justify-content-between">
                            <h3 class="card-title">{{trans('lang.top_users')}}</h3>
                            <a href="{!! route('users') !!}">{{trans('lang.view_all')}}</a>

                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-valign-middle">
                                <thead>
                                    <tr>
                                        <th>{{trans('lang.customer_name')}}</th>
                                        <th>{{trans('lang.ride_completed')}}</th>
                                        <th>{{trans('lang.actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody id="append_list_top_customers">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6">
                    <div class="card border">
                        <div class="card-header no-border d-flex justify-content-between">
                            <h3 class="card-title">{{trans('lang.top_drivers')}}</h3>
                            <a href="{!! route('drivers') !!}">{{trans('lang.view_all')}}</a>

                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-valign-middle">
                                <thead>
                                    <tr>
                                        <th>{{trans('lang.driver_name')}}</th>
                                        <th>{{trans('lang.ride_completed')}}</th>
                                        <th>{{trans('lang.actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody id="append_list_top_drivers">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 col-md-12">
                    <div class="card border">
                        <div class="card-header no-border d-flex justify-content-between">
                            <h3 class="card-title">{{trans('lang.recent_rides')}}</h3>

                            <a href="{{route('rides')}}">{{trans('lang.view_all')}}
                            </a>

                        </div>
                        <div class="card-body">
                           <div class="table-responsive">
                            <table class="table table-striped table-valign-middle" id="orderTable">
                                <thead>
                                    <tr>
                                        <th>{{trans('lang.ride_id')}}</th>
                                        <th>{{trans('lang.ridetype')}}</th>
                                        <th>{{trans('lang.dashboard_user')}}</th>
                                        <th>{{trans('lang.dashboard_driver')}}</th>
                                        <th>{{trans('lang.address')}}</th>
                                    </tr>
                                </thead>
                                <tbody id="append_list_recent_rides">

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

    <script src="{{asset('js/chart.js')}}"></script>
    <script src="{{asset('js/highcharts.js')}}"></script>

    <script>

        var active_id = "{{$id}}";
        var active_type = "{{$type}}";      
        var db = firebase.firestore();
        var currency = db.collection('settings');
        var intercity_enabled = false;
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

        var placeholderImage = '';
        var placeholder = db.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function (snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })


        $(document).ready(function () {

            jQuery("#data-table_processing").show();

            const yearFilter = $('#yearFilter');
            const currentYear = new Date().getFullYear();
            const numberOfYears = 5;
            yearFilter.empty();
            for (let i = 0; i <= numberOfYears; i++) {
                yearFilter.append(`<option value="${currentYear - i}">${currentYear - i}</option>`);
            }
            $('#viewFilter').on('change', function () {
                const selected = $(this).val();

                if (selected === 'year') {
                    $('#monthFilter').hide();
                    $('#yearFilter').show();
                    $('#monthYearFilters').show();
                    $('#customDateFilters').hide();
                } else if (selected === 'month') {
                    $('#monthFilter').show();
                    $('#yearFilter').show();
                    $('#monthYearFilters').show();
                    $('#customDateFilters').hide();
                } else if (selected === 'custom') {
                    $('#monthYearFilters').hide();
                    $('#customDateFilters').show();
                }else{
                    $('#monthFilter').hide();
                    $('#yearFilter').hide();
                    $('#monthYearFilters').hide();
                    $('#customDateFilters').hide();
                }
            });

            $('#viewFilter').trigger('change');
            
            db.collection('sections').where('id', '==', active_id).get().then((snapshot) => {
                if (!snapshot.empty) {
                    var sectionData = snapshot.docs[0].data();
                    jQuery("#section_title").empty();
                    jQuery("#section_title").text(sectionData.name+' - '+sectionData.serviceType);
                    jQuery("#section_title").after('<p>{{trans("lang.here_quick_overview_of_your")}} ' + sectionData.name+' - '+sectionData.serviceType + ' {{trans("lang.platform_today")}}</p>')

                } else {
                    jQuery("#section_title").text('No section found');
                }
            }).catch((error) => {
                console.error("Error getting section:", error);
            });

            loadVendorDashboardData(null, null, null, null, null, active_id, active_type);
            getTotalEarnings(null, null, null, null, null, active_id);
            loadOrderStatusCounts(null, null, null, null, null, active_id);
            loadDashboardLists(null, null, null, null, null, active_id, active_type);
            $(document.body).on('click', '.redirecttopage', function () {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });
        })

        $('#applyFilterBtn').on('click', function (e) {
            e.preventDefault();

            const view = $('#viewFilter').val();
            const year = parseInt($('#yearFilter').val()) || null;
            const month = parseInt($('#monthFilter').val()) || null;
            const startDate = $('#startDate').val() || null;
            const endDate = $('#endDate').val() || null;

            loadVendorDashboardData(view, year, month, startDate, endDate, active_id, active_type);
            getTotalEarnings(view, year, month, startDate, endDate, active_id);
            loadOrderStatusCounts(view, year, month, startDate, endDate, active_id);
            loadDashboardLists(view, year, month, startDate, endDate, active_id, active_type);
        });

        async function loadDashboardLists(filterType = null, year = null, month = null, startDate = null, endDate = null, active_id, active_type) {
            let ref, snapshots, html;

            let startTS = null;
            let endTS = null;
            if (filterType === 'year' && year) {
                startTS = firebase.firestore.Timestamp.fromDate(new Date(year, 0, 1));
                endTS = firebase.firestore.Timestamp.fromDate(new Date(year, 11, 31, 23, 59, 59));
            } else if (filterType === 'month' && year && month) {
                startTS = firebase.firestore.Timestamp.fromDate(new Date(year, month - 1, 1));
                endTS = firebase.firestore.Timestamp.fromDate(new Date(year, month, 0, 23, 59, 59));
            } else if (filterType === 'custom' && startDate && endDate) {
                startTS = firebase.firestore.Timestamp.fromDate(new Date(startDate));
                endTS = firebase.firestore.Timestamp.fromDate(new Date(endDate));
            }

            const append_listvendors = document.getElementById('append_list_top_customers');
            append_listvendors.innerHTML = '';

            ref = regionScoped(db.collection('users').where('role', '==', 'customer'));


            ref = ref.orderBy('createdAt', 'desc');

          
            const snapshotsPromise = ref.get();

            [snapshots, html] = await Promise.all([
                snapshotsPromise,
                snapshotsPromise.then(buildHTML)
            ]);

            append_listvendors.innerHTML = html;

            const append_listrecent_order = document.getElementById('append_list_recent_rides');
            append_listrecent_order.innerHTML = '';

            /* Top-N lists filter inside the query: narrowing after limit(10)
             * would return fewer than ten rows, or none at all. */
            ref = regionScoped(db.collection('rides').where('sectionId', '==', active_id).where('status', 'in', ["Order Placed", "Order Accepted", "Driver Pending", "Driver Accepted", "Order Shipped", "In Transit"]));

          
            ref = ref.orderBy('createdAt', 'desc');

            snapshots = await ref.limit(10).get();
            html = buildRidesHTML(snapshots);
            append_listrecent_order.innerHTML = html;

            const append_listtop_drivers = document.getElementById('append_list_top_drivers');
            append_listtop_drivers.innerHTML = '<tr><td colspan="3">Loading...</td></tr>';

            ref = regionScoped(db.collection('users').where('role', '==', 'driver').where('isOwner','==',false));

            if (typeof active_id !== 'undefined' && active_id) {
                ref = ref.where('sectionId', '==', active_id);
            }

            
            snapshots = await ref.get();
            html = await buildDriverHTML(snapshots);
            if(html == ''){
                append_listtop_drivers.innerHTML = '<tr><td colspan="3">{{trans("lang.no_record_found")}}</td></tr>';
            }else{
                append_listtop_drivers.innerHTML = html;
            }
           

            $('#orderTable, #driverTable').each(function () {
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable({
                        order: [],
                        responsive: true,
                        paging: false,
                        info: false,
                        "language": datatableLang,
                    });
                }
            });
        }


        async function getTotalEarnings(filterType = null, year = null, month = null, startDate = null, endDate = null) {
            var intRegex = /^\d+$/;
            var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;

            let vArr = Array(12).fill(0); // monthly earnings or daily/custom
            let cArr = Array(12).fill(0); // monthly commission or daily/custom

            let totalEarning = 0;
            let adminCommission = 0;
            let totalEarningLast = 0;
            let totalCommLast = 0;

            // 🔹 Setup date range based on filter
            let startTS = null;
            let endTS = null;
            let lastStartTS = null;
            let lastEndTS = null;

            if (filterType === 'year' && year) {
                startTS = firebase.firestore.Timestamp.fromDate(new Date(year, 0, 1));
                endTS = firebase.firestore.Timestamp.fromDate(new Date(year, 11, 31, 23, 59, 59));

                lastStartTS = firebase.firestore.Timestamp.fromDate(new Date(year - 1, 0, 1));
                lastEndTS = firebase.firestore.Timestamp.fromDate(new Date(year - 1, 11, 31, 23, 59, 59));

            } else if (filterType === 'month' && year && month) {
                startTS = firebase.firestore.Timestamp.fromDate(new Date(year, month - 1, 1));
                endTS = firebase.firestore.Timestamp.fromDate(new Date(year, month, 0, 23, 59, 59));

                // previous month calculation
                const prevMonth = month === 1 ? 12 : month - 1;
                const prevYear = month === 1 ? year - 1 : year;
                lastStartTS = firebase.firestore.Timestamp.fromDate(new Date(prevYear, prevMonth - 1, 1));
                lastEndTS = firebase.firestore.Timestamp.fromDate(new Date(prevYear, prevMonth, 0, 23, 59, 59));

            } else if (filterType === 'custom' && startDate && endDate) {
                startTS = firebase.firestore.Timestamp.fromDate(new Date(startDate));
                endTS = firebase.firestore.Timestamp.fromDate(new Date(endDate));

                // previous period of same duration
                const start = new Date(startDate);
                const end = new Date(endDate);
                const durationDays = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;

                const lastEnd = new Date(start);
                lastEnd.setDate(lastEnd.getDate() - 1);
                const lastStart = new Date(lastEnd);
                lastStart.setDate(lastStart.getDate() - (durationDays - 1));

                lastStartTS = firebase.firestore.Timestamp.fromDate(lastStart);
                lastEndTS = firebase.firestore.Timestamp.fromDate(lastEnd);
            }

            // 🔹 Current period query
            let ridesQuery = db.collection('rides').where('sectionId', '==', active_id).where('status', '==', "Order Completed");
            if (startTS && endTS) {
                ridesQuery = ridesQuery.where('createdAt', '>=', startTS).where('createdAt', '<=', endTS);
            }
            ridesQuery = ridesQuery.orderBy('createdAt', 'desc');

            const snapshot = await ridesQuery.get();

            regionDocs(snapshot).forEach(doc => {
                const ride = doc.data();
                let { price, commission } = calculateRideEarnings(ride, intRegex, floatRegex);
                totalEarning += price;
                adminCommission += commission;

                // Month/Day wise aggregation
                if (ride.createdAt) {
                    let orderDate = ride.createdAt.toDate();
                    if (filterType === 'year') {
                        let monthIdx = orderDate.getMonth();
                        vArr[monthIdx] += price;
                        cArr[monthIdx] += commission;
                    } else if (filterType === 'month') {
                        if (orderDate.getFullYear() === year && orderDate.getMonth() + 1 === month) {
                            const dayIndex = orderDate.getDate() - 1;
                            vArr[dayIndex] = (vArr[dayIndex] || 0) + price;
                            cArr[dayIndex] = (cArr[dayIndex] || 0) + commission;
                        }
                    } else if (filterType === 'custom') {
                        const start = new Date(startDate);
                        const dayIndex = Math.floor((orderDate - start) / (1000 * 60 * 60 * 24));
                        vArr[dayIndex] = (vArr[dayIndex] || 0) + price;
                        cArr[dayIndex] = (cArr[dayIndex] || 0) + commission;
                    } else {
                        let monthIdx = orderDate.getMonth();
                        vArr[monthIdx] += price;
                        cArr[monthIdx] += commission;
                    }
                }
            });

            // 🔹 Previous period query
            if (lastStartTS && lastEndTS) {
                let lastQuery = db.collection('rides').where('sectionId', '==', active_id).where('status', '==', "Order Completed")
                    .where('createdAt', '>=', lastStartTS)
                    .where('createdAt', '<=', lastEndTS);

                const lastSnapshot = await lastQuery.get();
                regionDocs(lastSnapshot).forEach(doc => {
                    const ride = doc.data();
                    let { price, commission } = calculateRideEarnings(ride, intRegex, floatRegex);
                    totalEarningLast += price;
                    totalCommLast += commission;
                });
            }

            // 🔹 Calculate percentage change
            let percentChange = totalEarningLast === 0
                ? (totalEarning === 0 ? 0 : 100)
                : ((totalEarning - totalEarningLast) / totalEarningLast) * 100;

            let percentCommission = totalCommLast === 0
                ? (adminCommission === 0 ? 0 : 100)
                : ((adminCommission - totalCommLast) / totalCommLast) * 100;


            // 🔹 Display
            let totalEarningDisplay = currencyAtRight
                ? parseFloat(totalEarning).toFixed(decimal_degits) + currentCurrency
                : currentCurrency + parseFloat(totalEarning).toFixed(decimal_degits);

            let adminCommissionDisplay = currencyAtRight
                ? parseFloat(adminCommission).toFixed(decimal_degits) + currentCurrency
                : currentCurrency + parseFloat(adminCommission).toFixed(decimal_degits);

            $("#earnings_count").text(totalEarningDisplay);
            $("#earnings_count_graph").text(totalEarningDisplay);
            $("#admincommission_count_graph").text(adminCommissionDisplay);
            $("#admincommission_count").text(adminCommissionDisplay);
            $("#total_earnings_header").text(totalEarningDisplay);
            $(".earnings_over_time").text(totalEarningDisplay);

            let ordersInfo = getArrowAndClass(percentChange);
            let commissionInfo = getArrowAndClass(percentCommission);
            if(filterType !== null){

                jQuery("#earning_percent").html(`<i class="fa ${ordersInfo.arrow}"></i> ${Math.abs(percentChange).toFixed(2)}% vs last period`).removeClass('green red').addClass(ordersInfo.className);
                jQuery("#commission_percent").html(`<i class="fa ${commissionInfo.arrow}"></i> ${Math.abs(percentCommission).toFixed(2)}% vs last period`).removeClass('green red').addClass(commissionInfo.className);
            }

            // Chart labels & data
            let labels = [];
            let chartData = [];
            let commissionData = [];

            if (filterType === 'year') {
                labels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
                chartData = vArr;
                commissionData = cArr;
            } else if (filterType === 'month') {
                const daysInMonth = new Date(year, month, 0).getDate();
                labels = Array.from({ length: daysInMonth }, (_, i) => (i + 1).toString());
                chartData = vArr.slice(0, daysInMonth);
                commissionData = cArr.slice(0, daysInMonth);
            } else if (filterType === 'custom') {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const days = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
                labels = Array.from({ length: days }, (_, i) => {
                    const d = new Date(start);
                    d.setDate(d.getDate() + i);
                    return `${d.getDate()}-${d.getMonth() + 1}`;
                });
                chartData = vArr.slice(0, days);
                commissionData = cArr.slice(0, days);
            } else {
                labels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
                chartData = vArr;
                commissionData = cArr;
            }

            const ctx = $('#sales-chart')[0].getContext('2d');
            renderChart(ctx, chartData, labels);
            setCommision(commissionData, chartData);

            jQuery("#data-table_processing").hide();
        }

        // 🔹 Helper for calculating ride earnings
        function calculateRideEarnings(ride, intRegex, floatRegex) {

            let order_subtotal = parseFloat(ride.subTotal || 0);
            let total_discount = 0;
            let total_tax_amount = 0;

            let tip_amount = parseFloat(ride.tip_amount || 0);
            let platformFee = parseFloat(ride.platformFee || 0);

            // Discount
            let order_discount = parseFloat(ride.discount || 0);
            total_discount = isNaN(order_discount) ? 0 : order_discount;

            // ORDER LEVEL TAX
            let orderTaxable = Math.max(0, order_subtotal - total_discount);
            (ride.taxSetting || []).forEach(tax => {
                if (tax.enable) {
                    let taxAmount = tax.type === "percentage"
                        ? (parseFloat(tax.tax) / 100) * orderTaxable
                        : parseFloat(tax.tax);
                    total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                }
            });

            // EXTRA TAXES
            [
                { amount: platformFee, taxes: ride.platformTax || [] },
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

            // Final subtotal after discount
            let final_subtotal = order_subtotal - total_discount;

            // Commission base
            let commissionBase = final_subtotal + platformFee + total_tax_amount;

            let commission = 0;
            if (ride.adminCommission && ride.adminCommission > 0) {
                let commissionValue = parseFloat(ride.adminCommission);
                if (!isNaN(commissionValue)) {
                    if (ride.adminCommissionType?.toLowerCase() === 'percent') {
                        commission = (commissionBase * commissionValue) / 100;
                    } else {
                        commission = commissionValue;
                    }
                }
            }

            // Final earning
            let price = final_subtotal + tip_amount + platformFee + total_tax_amount;
            return {
                price: isNaN(price) ? 0 : price,
                commission: isNaN(commission) ? 0 : commission
            };
        }

        async function loadOrderStatusCounts(filterType = null, year = null, month = null, startDate = null, endDate = null, active_id) {
            let startTS = null;
            let endTS = null;

            if (filterType === 'year' && year) {
                let startOfYear = new Date(year, 0, 1);
                let endOfYear = new Date(year, 11, 31, 23, 59, 59);
                startTS = firebase.firestore.Timestamp.fromDate(startOfYear);
                endTS = firebase.firestore.Timestamp.fromDate(endOfYear);
            } else if (filterType === 'month' && year && month) {
                let startOfMonth = new Date(year, month - 1, 1);
                let endOfMonth = new Date(year, month, 0, 23, 59, 59);
                startTS = firebase.firestore.Timestamp.fromDate(startOfMonth);
                endTS = firebase.firestore.Timestamp.fromDate(endOfMonth);
            } else if (filterType === 'custom' && startDate && endDate) {
                let start = new Date(startDate);
                let end = new Date(endDate);
                startTS = firebase.firestore.Timestamp.fromDate(start);
                endTS = firebase.firestore.Timestamp.fromDate(end);
            }

            const statuses = {
                placed: ["Order Placed"],
                confirmed: ["Order Accepted", "Driver Accepted"],
                shipped: ["Order Shipped", "In Transit"],
                completed: ["Order Completed"],
                canceled: ["Order Rejected"],
                pending: ["Driver Pending"],
            };

            const promises = Object.entries(statuses).map(([key, statusArray]) => {
                let query = db.collection('rides').where('sectionId', '==', active_id)
                    .where('status', 'in', statusArray);
                if (startTS && endTS) {
                    query = query.where('createdAt', '>=', startTS)
                        .where('createdAt', '<=', endTS);
                }

                return query.get().then(snapshot => ({ key, count: regionDocs(snapshot).length }));
            });

            const results = await Promise.all(promises);

            results.forEach(item => {
                const selector = `#${item.key}_count`;
                jQuery(selector).empty().val(item.count);
            });
            setorderStatus();

        }
        async function setorderStatus() {
            var placed = parseInt($('#placed_count').val()) || 0;
            var confirmed = parseInt($('#confirmed_count').val()) || 0;
            var shipped = parseInt($('#shipped_count').val()) || 0;
            var completed = parseInt($('#completed_count').val()) || 0;
            var canceled = parseInt($('#canceled_count').val()) || 0;
            // var failed = parseInt($('#failed_count').val()) || 0;
            var pending = parseInt($('#pending_count').val()) || 0;

            var dataValues = [placed, confirmed, shipped, completed, canceled,/*  failed, */ pending];
            var totalOrders = dataValues.reduce((a, b) => a + b, 0);
            var ctx = document.getElementById("order_status").getContext("2d");

            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);

            Chart.plugins.unregister(Chart.plugins.getAll().find(p => p.id === 'totalOrdersPlugin'));

            Chart.plugins.register({
                id: 'totalOrdersPlugin',
                beforeDraw: function (chart) {
                    if (chart.config.type === 'doughnut') {
                        var ctx = chart.chart.ctx;
                        var chartArea = chart.chartArea;

                        var centerX = (chartArea.left + chartArea.right) / 2;
                        var centerY = (chartArea.top + chartArea.bottom) / 2;

                        ctx.save();
                        ctx.font = "16px Arial";
                        ctx.fillStyle = "#111";
                        ctx.textAlign = "center";
                        ctx.textBaseline = "middle";
                        ctx.fillText("{{trans('lang.dashboard_total_rides')}}", centerX, centerY - 20);

                        ctx.font = "bold 26px Arial";
                        ctx.fillText(totalOrders, centerX, centerY + 15);
                        ctx.restore();
                    }
                }
            });

            if (window.orderStatusChart) {
                window.orderStatusChart.data.datasets[0].data = dataValues;
                window.orderStatusChart.update();
            } else {
                window.orderStatusChart = new Chart(ctx, {
                    type: "doughnut",
                    data: {
                        labels: [
                            "{{ trans('lang.pending_rides') }}",
                            "{{ trans('lang.confirmed_rides') }}",
                            "{{ trans('lang.ongoing_ride') }}",
                            "{{ trans('lang.completed_ride') }}",
                            "{{ trans('lang.canceled_ride') }}",
                            "{{trans('lang.driver_pending')}}"
                        ],
                        datasets: [{
                            data: dataValues,
                            backgroundColor: [
                                "#FF6110",
                                "#FFB32C",
                                "#0DBDFD",
                                "#2AD587",
                                "#B2262B",
                                "#4426b2ff",
                            ],
                            borderWidth: 2
                        }]
                    },

                    legend: {
                        position: "right",
                        labels: {
                            boxWidth: 20,
                            fontSize: 12,
                            generateLabels: function (chart) {
                                var data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map(function (label, i) {
                                        var value = data.datasets[0].data[i];
                                        var bgColor = data.datasets[0].backgroundColor[i];
                                        return {
                                            text: label + " - " + value,
                                            fillStyle: bgColor,
                                            strokeStyle: bgColor,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    }

                });
            }
        }

       async function buildHTML(snapshots) {           

            const userList = await Promise.all(
                snapshots.docs.map(async (doc) => {
                    const val = doc.data();
                    val.id = doc.id;
                    const rideCount = await getCustomerRideCount(doc.id);
                    return { val, rideCount };
                })
            );

            userList.sort((a, b) => b.rideCount - a.rideCount);

            const topDrivers = userList.slice(0, 5);

            let html = '';
            for (const driver of topDrivers) {
                const val = driver.val;
                const userroute = '<?php echo route("users.edit", ":id");?>'.replace(':id', val.id);
                const userView = '<?php echo route("users.view", ":id");?>'.replace(':id', val.id);
                const profileSrc = val.profilePic ? val.profilePic : placeholderImage;

                html += `
                    <tr>
                        <td class="redirecttopage">
                            <div class="top-driver-name">
                                <img class="img-circle img-size-32" style="width:40px;height:40px; margin-right:5px;" src="${profileSrc}" alt="image">
                                <a href="${userView}">${val.firstName} ${val.lastName}</a>
                            </div>
                        </td>
                        <td data-url="${userView}" class="redirecttopage">${driver.rideCount}</td>
                        <td class="action-btn">
                            <a href="${userroute}">
                                <span class="mdi mdi-lead-pencil"></span>
                            </a>
                        </td>
                    </tr>
                `;
            }

            return html;
        }
        async function getCustomerRideCount(customerId) {
            const ridesSnapshot = await db.collection('rides')
                .where('authorID', '==', customerId)
                .get();
            return ridesSnapshot.size;
        }

        async function getDriverRideCount(driverId) {
            // Count all rides where driverId == this driver's id
            const ridesSnapshot = await db.collection('rides')
                .where('driverId', '==', driverId)
                .get();
            return ridesSnapshot.size;
        }

        async function buildDriverHTML(snapshots) {           

            const driverList = await Promise.all(
                snapshots.docs.map(async (doc) => {
                    const val = doc.data();
                    val.id = doc.id;
                    const rideCount = await getDriverRideCount(doc.id);
                    return { val, rideCount };
                })
            );

            driverList.sort((a, b) => b.rideCount - a.rideCount);

            const topDrivers = driverList.slice(0, 5);

            let html = '';
            for (const driver of topDrivers) {
                const val = driver.val;
                const driverroute = '<?php echo route("drivers.edit", ":id");?>'.replace(':id', val.id);
                const driverView = '<?php echo route("drivers.view", ":id");?>'.replace(':id', val.id);
                const profileSrc = val.profilePic ? val.profilePic : placeholderImage;

                html += `
                    <tr>
                        <td class="redirecttopage">
                            <div class="top-driver-name">
                                <img class="img-circle img-size-32" style="width:40px;height:40px; margin-right:5px;" src="${profileSrc}" alt="image">
                                <a href="${driverView}">${val.firstName} ${val.lastName}</a>
                            </div>
                        </td>
                        <td data-url="${driverView}" class="redirecttopage">${driver.rideCount}</td>
                        <td class="action-btn">
                            <a href="${driverroute}">
                                <span class="mdi mdi-lead-pencil"></span>
                            </a>
                        </td>
                    </tr>
                `;
            }

            return html;
        }


        function buildRidesHTML(snapshots) {
            var html = '';
            var count = 1;
            snapshots.docs.forEach((listval) => {
                val = listval.data();
                val.id = listval.id;
                var route = '<?php echo route("rides.edit", ":id"); ?>';
                route = route.replace(':id', val.id);

                var user_id = val.author.id;
                var customer_view = '{{route("users.edit", ":id")}}';
                customer_view = customer_view.replace(':id', user_id);
                var user_view = '{{route("users.view", ":id")}}';
                user_view = user_view.replace(':id', user_id);

                html = html + '<tr>';

                html = html + '<td data-url="' + route + '" class="redirecttopage">' + val.id + '</td>';

                if (val.rideType != undefined) {
                    html = html + '<td>' + val.rideType + '</td>';
                } else {
                    html = html + '<td></td>';
                }

                if (val.hasOwnProperty("author")) {
                    html = html + '<td data-url="' + user_view + '" class="redirecttopage">' + val.author.firstName + '</td>';
                } else {
                    html = html + '<td></td>';
                }

                if (val.hasOwnProperty("driver")) {
                    var driverId = val.driver.id;
                    var diverRoute = '{{route("drivers.edit", ":id")}}';
                    diverRoute = diverRoute.replace(':id', driverId);
                    var driverView = '{{route("drivers.view", ":id")}}';
                    driverView = driverView.replace(':id', driverId);
                    html = html + '<td data-url="' + driverView + '" class="redirecttopage">' + val.driver.firstName + '</td>';
                } else {
                    html = html + '<td></td>';
                }

                html = html + '<td>' + val.destinationLocationName + '</td>';

                html = html + '</tr>';
                count++;
            });
            return html;
        }


        function renderChart(ctx, data, labels) {
            var gradientStroke = ctx.createLinearGradient(0, 0, 0, 300);
            gradientStroke.addColorStop(0, "rgba(255,94,0,0.4)");
            gradientStroke.addColorStop(1, "rgba(255,94,0,0)");

            var ticksStyle = {
                fontColor: '#495057',
                fontStyle: 'bold'
            };

            var mode = 'index';
            var intersect = true;

            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "{{trans('lang.total_sales')}}",
                        data: data,
                        borderColor: "#FF5E00",
                        backgroundColor: gradientStroke,
                        pointRadius: 0,
                        borderWidth: 3,
                        fill: true,
                        lineTension: 0.3
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        mode: mode,
                        intersect: intersect,
                        callbacks: {
                            label: function (tooltipItems, data) {
                                let val = data.datasets[0].data[tooltipItems.index];
                                val = Number.isInteger(val) ? val : val.toFixed(2);

                                if (currencyAtRight) {
                                    return val + currentCurrency;
                                } else {
                                    return currentCurrency + val;
                                }
                            }
                        }
                    },
                    hover: {
                        mode: mode,
                        intersect: intersect
                    },
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            fontColor: '#495057',
                            fontSize: 12
                        }
                    },
                    scales: {
                        yAxes: [{
                            gridLines: {
                                display: true,
                                lineWidth: '1px',
                                color: 'rgba(0, 0, 0, .05)',
                                zeroLineColor: 'transparent'
                            },
                            ticks: $.extend({
                                beginAtZero: true,
                                callback: function (value) {
                                    // no long decimals on axis
                                    value = Number.isInteger(value) ? value : value.toFixed(2);

                                    if (value >= 1000) {
                                        value = (value / 1000) + "k";
                                    }

                                    return currencyAtRight ? value + currentCurrency : currentCurrency + value;
                                }
                            }, ticksStyle)
                        }],
                        xAxes: [{
                            display: true,
                            gridLines: {
                                display: false
                            },
                            ticks: ticksStyle
                        }]
                    }
                }
            });
        }


        function loadVendorDashboardData(filterType = null, year = null, month = null, startDate = null, endDate = null, active_id, active_type) {
            let startOfThisPeriod = new Date();
            let endOfThisPeriod = new Date();
            let startOfLastPeriod = null;
            let endOfLastPeriod = null;

            // 🔹 Setup date ranges
            if (filterType === 'year' && year) {
                // This year
                startOfThisPeriod = new Date(year, 0, 1);
                endOfThisPeriod = new Date(year, 11, 31, 23, 59, 59);

                // Last year
                startOfLastPeriod = new Date(year - 1, 0, 1);
                endOfLastPeriod = new Date(year - 1, 11, 31, 23, 59, 59);

            } else if (filterType === 'month' && year && month) {
                // This month
                startOfThisPeriod = new Date(year, month - 1, 1);
                endOfThisPeriod = new Date(year, month, 0, 23, 59, 59);

                // Last month (handle January edge case)
                const prevMonth = month === 1 ? 12 : month - 1;
                const prevYear = month === 1 ? year - 1 : year;
                startOfLastPeriod = new Date(prevYear, prevMonth - 1, 1);
                endOfLastPeriod = new Date(prevYear, prevMonth, 0, 23, 59, 59);

            } else if (filterType === 'custom' && startDate && endDate) {
                // This custom range
                startOfThisPeriod = new Date(startDate);
                endOfThisPeriod = new Date(endDate);

                // Last custom range (same number of days before startDate)
                const durationDays = Math.floor((endOfThisPeriod - startOfThisPeriod) / (1000 * 60 * 60 * 24)) + 1;
                const lastEnd = new Date(startOfThisPeriod);
                lastEnd.setDate(lastEnd.getDate() - 1);
                const lastStart = new Date(lastEnd);
                lastStart.setDate(lastStart.getDate() - (durationDays - 1));

                startOfLastPeriod = lastStart;
                endOfLastPeriod = lastEnd;
            }

            // 🔹 Firestore Timestamps
            const startThisTS = firebase.firestore.Timestamp.fromDate(startOfThisPeriod);
            const endThisTS = firebase.firestore.Timestamp.fromDate(endOfThisPeriod);
            const startLastTS = startOfLastPeriod ? firebase.firestore.Timestamp.fromDate(startOfLastPeriod) : null;
            const endLastTS = endOfLastPeriod ? firebase.firestore.Timestamp.fromDate(endOfLastPeriod) : null;

            // 🔹 Fetch current & last data together
            Promise.all([
                // All-time data
                db.collection('rides').where('sectionId', '==', active_id).orderBy('createdAt','desc').get(),
                db.collection('users').where("role", "==", "customer").orderBy("createdAt").get(),
                db.collection('users').where("role", "==", "driver").where('sectionId', '==', active_id).where('isOwner','==',false).orderBy("createdAt").get(),

                // Current period
                db.collection('rides').where('sectionId', '==', active_id).where('createdAt', '>=', startThisTS).where('createdAt', '<=', endThisTS).get(),
                db.collection('users').where("role", "==", "customer").where('createdAt', '>=', startThisTS).where('createdAt', '<=', endThisTS).orderBy("createdAt").get(),
                db.collection('users').where("role", "==", "driver").where('sectionId', '==', active_id).where('isOwner','==',false).where('createdAt', '>=', startThisTS).where('createdAt', '<=', endThisTS).orderBy("createdAt").get(),

                // Last period (if applicable)
                startLastTS ? db.collection('rides').where('sectionId', '==', active_id).where('createdAt', '>=', startLastTS).where('createdAt', '<=', endLastTS).get() : Promise.resolve({ docs: [] }),
                startLastTS ? db.collection('users').where("role", "==", "customer").where('createdAt', '>=', startLastTS).where('createdAt', '<=', endLastTS).orderBy("createdAt").get() : Promise.resolve({ docs: [] }),
                startLastTS ? db.collection('users').where("role", "==", "driver").where('sectionId', '==', active_id).where('isOwner','==',false).where('createdAt', '>=', startLastTS).where('createdAt', '<=', endLastTS).orderBy("createdAt").get() : Promise.resolve({ docs: [] }),
            ])
            .then(([allOrders, allUsers, allDrivers,
                ordersCurr, usersCurr, driversCurr,
                ordersLast, usersLast, driversLast]) => {

              

                /* Statistics are narrowed to the active region here rather than
                 * in each query, so the figures follow the region being worked
                 * in without needing an index per query. */
                const totalOrdersDisplay = filterType ? regionDocs(ordersCurr).length : regionDocs(allOrders).length;
                const totalUsersDisplay = filterType ? regionDocs(usersCurr).length : regionDocs(allUsers).length;
                const totalDriversDisplay = filterType ? regionDocs(driversCurr).length : regionDocs(allDrivers).length;

                const totalOrdersLastDisplay = filterType ? regionDocs(ordersLast).length : 0;
                const totalUsersLastDisplay = filterType ? regionDocs(usersLast).length : 0;
                const totalDriversLastDisplay = filterType ? regionDocs(driversLast).length : 0;

                // 🔹 % Calculation helper
                function calcPercent(curr, last) {
                    return last === 0 ? (curr === 0 ? 0 : 100) : ((curr - last) / last) * 100;
                }

                const ordersPercent = calcPercent(totalOrdersDisplay, totalOrdersLastDisplay);
                const usersPercent = calcPercent(totalUsersDisplay, totalUsersLastDisplay);
                const driversPercent = calcPercent(totalDriversDisplay, totalDriversLastDisplay);

                

                const ordersInfo = getArrowAndClass(ordersPercent);
                const usersInfo = getArrowAndClass(usersPercent);
                const driversInfo = getArrowAndClass(driversPercent);

                // 🔹 Update UI
                jQuery("#total_rides").text(totalOrdersDisplay);
                jQuery("#users_count").text(totalUsersDisplay);
                jQuery("#driver_count").text(totalDriversDisplay);
                if(filterType !== null){
                    jQuery("#rides_percent").html(`<i class="fa ${ordersInfo.arrow}"></i> ${Math.abs(ordersPercent).toFixed(2)}% vs last period`)
                        .removeClass('green red').addClass(ordersInfo.className);
                    jQuery("#customer_percent").html(`<i class="fa ${usersInfo.arrow}"></i> ${Math.abs(usersPercent).toFixed(2)}% vs last period`)
                        .removeClass('green red').addClass(usersInfo.className);
                    jQuery("#driver_percent").html(`<i class="fa ${driversInfo.arrow}"></i> ${Math.abs(driversPercent).toFixed(2)}% vs last period`)
                        .removeClass('green red').addClass(driversInfo.className);
                }
            })
            .catch(err => console.error(err));
        }

        function getArrowAndClass(percent) {
            return {
                arrow: percent > 0 ? 'fa-arrow-up' : 'fa-arrow-down',
                className: percent > 0 ? 'green' : 'red'
            };
        }

        function setCommision(commissionsData, earningsData) {
            const earnings = parseFloat(jQuery("#earnings_count").text().replace(currentCurrency, ""));
            const adminCommission = parseFloat(jQuery("#admincommission_count").text().replace(currentCurrency, ""));

            const data = {
                labels: ["{{trans('lang.total_sales')}}", "{{trans('lang.admin_commissions')}}"],
                datasets: [{
                    data: [earnings, adminCommission],
                    backgroundColor: ["#2EC7D9", "#28a745"],
                    borderWidth: 2
                }]
            };
            var labels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            return new Chart(document.getElementById("commissions"), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "{{trans('lang.total_sales')}}",
                            data: earningsData,
                            borderColor: "#2EC7D9",
                            backgroundColor: "transparent",
                            borderWidth: 3,
                            fill: false,
                        },
                        {
                            label: "{{trans('lang.admin_commission')}}",
                            data: commissionsData,
                            borderColor: "#28a745",
                            backgroundColor: "transparent",
                            borderWidth: 3,
                            fill: false,
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        callbacks: {
                            label: function (tooltipItem, chartData) {
                                let datasetLabel = chartData.datasets[tooltipItem.datasetIndex].label || '';
                                let value = tooltipItem.yLabel;
                                if (currencyAtRight) {
                                    return datasetLabel + ": " + value.toFixed(2) + currentCurrency;
                                } else {
                                    return datasetLabel + ": " + currentCurrency + value.toFixed(2);
                                }
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            fontColor: '#333',
                            generateLabels: function (chart) {
                                let datasets = chart.data.datasets;
                                return datasets.map(function (ds, i) {
                                    let value = ds.data.reduce((a, b) => a + b, 0);
                                    return {
                                        text: ds.label + " $" + value.toLocaleString(),
                                        fillStyle: ds.borderColor,
                                        strokeStyle: ds.borderColor,
                                        hidden: !chart.isDatasetVisible(i),
                                        datasetIndex: i
                                    };
                                });
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            },
                            gridLines: {
                                color: "rgba(0,0,0,0.1)"
                            }
                        }],
                        xAxes: [{
                            gridLines: {
                                display: false
                            }
                        }]
                    }
                }
            });
        }

    </script>
@endsection