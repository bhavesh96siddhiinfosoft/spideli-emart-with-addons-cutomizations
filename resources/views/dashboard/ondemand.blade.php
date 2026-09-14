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
                                <select id="yearFilter" name="year" class="form-control"  style="display:none">
                                    
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
                            <div class="card card-box-with-icon border"
                                onclick="javascript:void(0)">
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
                            <div class="card card-box-with-icon border" onclick="javascript:void(0)">
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
                            <div class="card card-box-with-icon border cursor-pointer"
                                onclick="location.href='{!! route('ondemand.bookings.index') !!}'">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/total_booking.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                           {{ trans('lang.dashboard_total_bookings') }}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning" id="booking_count"></h2>
                                        <h6 id="booking_percent" class="up-down-list font-semibold"></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border cursor-pointer"
                                onclick="location.href='{!! route('ondemand.bookings.index') !!}'">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/total_booking.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                          {{ trans('lang.dashboard_total_service') }}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning" id="service_count"></h2>
                                        <h6 id="service_percent" class="up-down-list font-semibold"></h6>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border cursor-pointer"
                                onclick="location.href='{!! route('providers') !!}'">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/total_customers.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                            {{ trans('lang.dashboard_total_providers') }}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning" id="provider_count">0
                                        </h2>
                                        <h6 id="provider_percent" class="up-down-list font-semibold "></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="card card-box-with-icon border cursor-pointer"
                                onclick="location.href='{!! route('ondemand.workers.index') !!}'">
                                <div class="card-body p-3">
                                    <span class="box-icon ab"><img src="{{ asset('images/total_drivers.png') }}"></span>
                                    <div class="card-box-with-content mt-3">
                                        <h4 class="card-left-title text-dark font-medium">
                                             {{ trans('lang.dashboard_total_workers') }}
                                        </h4>
                                        <h2 class="m-b-0 text-dark-2 font-bold mb-2 total_earning" id="worker_count"></h2>
                                        <h6 id="worker_percent" class="up-down-list font-semibold "></h6>
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
                            <input type="hidden" name="assigned_count" id="assigned_count">
                            <input type="hidden" name="accepted_count" id="accepted_count">
                            <input type="hidden" name="ongoing_count" id="ongoing_count">
                            <input type="hidden" name="completed_count" id="completed_count">
                            <input type="hidden" name="rejected_count" id="rejected_count">
                            <input type="hidden" name="cancelled_count" id="cancelled_count">                          

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

                            <div class="d-flex flex-row justify-content-end">
                                <span class="mr-2"> <i class="fa fa-square" style="color:red"></i>
                                    {{ trans('lang.dashboard_this_year') }} </span>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <div class="row daes-sec-sec mb-3">
                <div class="col-md-6 col-lg-6">
                    <div class="card border">
                        <div class="card-header no-border d-flex justify-content-between">
                            <h3 class="card-title">{{trans('lang.dashboard_ondemand_top_services')}}</h3>

                            <a href="{{ route('ondemand.services.index') }}" class="btn btn-tool btn-sm">{{trans('lang.view_all')}}</i>
                            </a>

                        </div>
                        <div class="card-body p-2">

                            <table class="table table-striped table-valign-middle" id="servicesTable">
                                <thead>
                                    <tr>
                                        <th>{{trans('lang.service')}}</th>
                                        <th>{{trans('lang.tab_reviews')}}</th>
                                        <th>{{trans('lang.actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody id="append_list_services">

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border">
                        <div class="card-header no-border d-flex justify-content-between">
                            <h3 class="card-title">{{trans('lang.dashboard_ondemand_top_providers')}}</h3>

                            <a href="{{route('providers')}}" class="btn btn-tool btn-sm">{{trans('lang.view_all')}}
                            </a>

                        </div>
                        <div class="card-body p-2">

                            <table class="table table-striped table-valign-middle" id="providersTable">
                                <thead>
                                    <tr>
                                        <th>{{trans('lang.provider')}}</th>
                                        <th>{{trans('lang.services')}}</th>
                                        <th>{{trans('lang.actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody id="append_list_top_providers">

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row daes-sec-sec">

                <div class="col-lg-12">
                    <div class="card border">
                        <div class="card-header no-border d-flex justify-content-between">
                            <h3 class="card-title">{{trans('lang.dashboard_ondemand_recent_orders')}}</h3>

                            <a href="{{ route('ondemand.bookings.index') }}" class="btn btn-tool btn-sm">{{trans('lang.view_all')}}
                            </a>

                        </div>
                        <div class="card-body p-2">

                            <table class="table table-striped table-valign-middle" id="orderTable">
                                <thead>
                                    <tr>
                                        <th style="text-align:center">{{trans('lang.order_id')}}</th>
                                        <th>{{trans('lang.order_user_id')}}</th>
                                        <th>{{trans('lang.service')}}</th>
                                        <th>{{trans('lang.provider')}}</th>
                                        <th>{{trans('lang.total_amount')}}</th>
                                        <th>{{trans('lang.quantity')}}</th>
                                        <th>{{trans('lang.booking_date')}}</th>
                                        <th>{{trans('lang.status')}}</th>
                                    </tr>
                                </thead>
                                <tbody id="append_list_recent_order">

                                </tbody>
                            </table>

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
            })
                .catch((error) => {
                    console.error("Error getting section:", error);
                });
            loadVendorDashboardData(null, null, null, null, null, active_id, active_type);
            getTotalEarnings(null, null, null, null, null, active_id);
            loadOrderStatusCounts(null, null, null, null, null, active_id);
            loadDashboardLists(null, null, null, null, null, active_id, active_type);

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

            let startTS = null, endTS = null;
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

            const append_list_services = document.getElementById('append_list_services');
            append_list_services.innerHTML = '';
            /* Top-N lists filter inside the query: narrowing after limit(10)
             * would return fewer than ten rows, or none at all. */
            ref = regionScoped(db.collection('providers_services')
                .where('sectionId', '==', active_id));

            ref = ref.orderBy('reviewsCount', 'desc'); 

            snapshots = await ref.limit(10).get();
            html = buildServicesHTML(snapshots);
            append_list_services.innerHTML = html;

            const append_list_recent_order = document.getElementById('append_list_recent_order');
            append_list_recent_order.innerHTML = '';
            ref = regionScoped(db.collection('provider_orders')
                .where('sectionId', '==', active_id)
                .where('status', 'in', ["Order Placed", "Order Accepted", "Order Assigned", "Order Ongoing", "Order Completed", "Order Cancelled"]));
            ref = ref.orderBy('createdAt', 'desc');
            snapshots = await ref.limit(10).get();
            html = buildOrderHTML(snapshots);
            append_list_recent_order.innerHTML = html;


            const append_list_top_providers = document.getElementById('append_list_top_providers');
            append_list_top_providers.innerHTML = '<tr><td colspan="3">Loading...</td></tr>';

            ref = regionScoped(db.collection('users').where('role', '==', 'provider').where('section_id', '==', active_id));
           
            snapshots = await ref.get();


            html = await buildProvidersHTML(snapshots);
            append_list_top_providers.innerHTML = html;
            
            ['#servicesTable', '#orderTable', '#providersTable'].forEach(selector => {

                if (!$.fn.DataTable.isDataTable(selector)) {

                    let config = {
                        order: [],
                        responsive: true,
                        paging: false,
                        info: false,
                       "language": datatableLang,
                    };

                    if (selector === '#servicesTable') {                        
                        config.columnDefs = [
                            { targets: [1, 2], orderable: false }
                        ];
                    }

                    if (selector === '#providersTable') {                        
                        config.columnDefs = [
                            { targets: [2], orderable: false }
                        ];
                    }
                    
                    $(selector).DataTable(config);
                }
            });

        }

        async function loadOrderStatusCounts(filterType = null, year = null, month = null, startDate = null, endDate = null, active_id) {
            let startTS = null, endTS = null;
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

            const statuses = {
                placed: ["Order Placed"],
                assigned: ["Order Assigned"],
                accepted: ["Order Accepted"],
                ongoing: ["Order Ongoing"],
                completed: ["Order Completed"],
                rejected: ["Order Rejected"],
                cancelled: ["Order Cancelled"],
              
            };

            const promises = Object.entries(statuses).map(([key, statusArray]) => {
                let query = db.collection('provider_orders')
                    .where('sectionId', '==', active_id).where('status', 'in', statusArray);
                    
                if (startTS && endTS) query = query.where('createdAt', '>=', startTS).where('createdAt', '<=', endTS);
                query = query.orderBy('createdAt', 'desc');
                return query.get().then(snapshot => ({ key, count: regionDocs(snapshot).length }));
            });

            const results = await Promise.all(promises);

            results.forEach(item => {
                $(`#${item.key}_count`).val(item.count || 0);
            });

            setOrderStatus();
        }

        async function setOrderStatus() {
            const dataValues = [
                parseInt($('#placed_count').val()) || 0,
                parseInt($('#assigned_count').val()) || 0,
                parseInt($('#accepted_count').val()) || 0,
                parseInt($('#ongoing_count').val()) || 0,               
                parseInt($('#completed_count').val()) || 0,
                parseInt($('#rejected_count').val()) || 0,
                parseInt($('#cancelled_count').val()) || 0           
            ];

            const totalOrders = dataValues.reduce((a, b) => a + b, 0);
            const ctx = document.getElementById("order_status").getContext("2d");

            // Clear previous chart
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);

            // Remove old plugin to avoid duplicates
            Chart.plugins.unregister(Chart.plugins.getAll().find(p => p.id === 'totalOrdersPlugin'));

            // Register plugin for center text
            Chart.plugins.register({
                id: 'totalOrdersPlugin',
                beforeDraw: function (chart) {
                    if (chart.config.type === 'doughnut') {
                        const ctx = chart.chart.ctx;
                        const chartArea = chart.chartArea;

                        const centerX = (chartArea.left + chartArea.right) / 2;
                        const centerY = (chartArea.top + chartArea.bottom) / 2;

                        ctx.save();
                        ctx.font = "16px Arial";
                        ctx.fillStyle = "#111";
                        ctx.textAlign = "center";
                        ctx.textBaseline = "middle";
                        ctx.fillText("{{trans('lang.dashboard_total_orders')}}", centerX, centerY - 20);

                        ctx.font = "bold 26px Arial";
                        ctx.fillText(totalOrders, centerX, centerY + 15);
                        ctx.restore();
                    }
                }
            });

            // Render or update chart
            if (window.orderStatusChart) {
                window.orderStatusChart.data.datasets[0].data = dataValues;
                window.orderStatusChart.update();
            } else {
                window.orderStatusChart = new Chart(ctx, {
                    type: "doughnut",
                    data: {
                        labels: [
                            "{{trans('lang.order_placed')}}",
                            "{{trans('lang.order_assigned')}}",
                            "{{trans('lang.order_accepted')}}",                           
                            "{{trans('lang.order_ongoing')}}",                           
                            "{{trans('lang.order_completed')}}",
                            "{{trans('lang.order_rejected')}}",
                            "{{trans('lang.order_canceled')}}",                           
                           
                        ],
                        datasets: [{
                            data: dataValues,
                            backgroundColor: [
                                "#4CC9F0",
                                "#F9C74F",
                                "#43AA8B",
                                "#90E0EF",
                                "#ef90d2ff",
                                "#F3722C",                               
                                "#F94144"
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: "right",
                                labels: {
                                    boxWidth: 20,
                                    generateLabels: function (chart) {
                                        return chart.data.labels.map((label, i) => ({
                                            text: `${label} - ${chart.data.datasets[0].data[i]}`,
                                            fillStyle: chart.data.datasets[0].backgroundColor[i],
                                            strokeStyle: chart.data.datasets[0].backgroundColor[i],
                                            index: i
                                        }));
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        function loadVendorDashboardData(filterType = null, year = null, month = null, startDate = null, endDate = null, active_id, active_type) {
            let startOfThisPeriod = new Date();
            let endOfThisPeriod = new Date();
            let startOfLastPeriod = null;
            let endOfLastPeriod = null;

            if (filterType === 'year' && year) {
                startOfThisPeriod = new Date(year, 0, 1);
                endOfThisPeriod = new Date(year, 11, 31, 23, 59, 59);
                startOfLastPeriod = new Date(year - 1, 0, 1);
                endOfLastPeriod = new Date(year - 1, 11, 31, 23, 59, 59);
            } else if (filterType === 'month' && year && month) {
                startOfThisPeriod = new Date(year, month - 1, 1);
                endOfThisPeriod = new Date(year, month, 0, 23, 59, 59);
                startOfLastPeriod = new Date(year, month - 2, 1);
                endOfLastPeriod = new Date(year, month - 1, 0, 23, 59, 59);
            } else if (filterType === 'custom' && startDate && endDate) {
                startOfThisPeriod = new Date(startDate);
                endOfThisPeriod = new Date(endDate);
            }

            const startThisTS = firebase.firestore.Timestamp.fromDate(startOfThisPeriod);
            const endThisTS = firebase.firestore.Timestamp.fromDate(endOfThisPeriod);
            const startLastTS = startOfLastPeriod ? firebase.firestore.Timestamp.fromDate(startOfLastPeriod) : null;
            const endLastTS = endOfLastPeriod ? firebase.firestore.Timestamp.fromDate(endOfLastPeriod) : null;

            Promise.all([
                // All-time
                db.collection('provider_orders').where('sectionId', '==', active_id).get(),
                db.collection('providers_services').where('sectionId', '==', active_id).get(),
                db.collection('providers_workers').orderBy("createdAt").get(),
                db.collection('users').where("role", "==", "provider").where('section_id', 'in', [active_id, '']).orderBy('createdAt', 'desc').get(),

                // Current period
                db.collection('provider_orders').where('sectionId', '==', active_id).where('createdAt', '>=', startThisTS).where('createdAt', '<=', endThisTS).get(),
                db.collection('providers_services').where('sectionId', '==', active_id).where('createdAt', '>=', startThisTS).where('createdAt', '<=', endThisTS).orderBy("createdAt").get(),
                db.collection('providers_workers').where('createdAt', '>=', startThisTS).where('createdAt', '<=', endThisTS).orderBy("createdAt").get(),
                db.collection('users').where("role", "==", "provider").where('section_id', 'in', [active_id, '']).where('createdAt', '>=', startThisTS).where('createdAt', '<=', endThisTS).orderBy('createdAt', 'desc').get(),

                // Last period
                startLastTS ? db.collection('provider_orders').where('sectionId', '==', active_id).where('createdAt', '>=', startLastTS).where('createdAt', '<=', endLastTS).get() : Promise.resolve({ docs: [] }),
                startLastTS ? db.collection('providers_services').where('sectionId', '==', active_id).where("role", "==", "customer").where('createdAt', '>=', startLastTS).where('createdAt', '<=', endLastTS).orderBy("createdAt").get() : Promise.resolve({ docs: [] }),
                startLastTS ? db.collection('providers_workers').where('createdAt', '>=', startLastTS).where('createdAt', '<=', endLastTS).orderBy("createdAt").get() : Promise.resolve({ docs: [] }),
                startLastTS ? db.collection('users').where("role", "==", "provider").where('section_id', 'in', [active_id, '']).where('createdAt', '>=', startLastTS).where('createdAt', '<=', endLastTS).orderBy('createdAt', 'desc').get() : Promise.resolve({ docs: [] }),
            ])
            .then(([allOrders, allService, allWorker, allUsers,
                ordersCurr, serviceCurr, workerCurr, usersCurr,
                ordersLast, serviceLast, workerLast, usersLast]) => {

                /* Statistics are narrowed to the active region here rather
                 * than in each query, so the figures follow the region being
                 * worked in without needing an index per query. */
                let totalOrdersDisplay = filterType ? regionDocs(ordersCurr).length : regionDocs(allOrders).length;
                let totalUsersDisplay = filterType ? regionDocs(usersCurr).length : regionDocs(allUsers).length;
                let totalDriversDisplay = filterType ? regionDocs(workerCurr).length : regionDocs(allWorker).length;
                let totalServiceDisplay = filterType ? regionDocs(serviceCurr).length : regionDocs(allService).length;

                let totalOrdersLastDisplay = filterType ? regionDocs(ordersLast).length : 0;
                let totalUsersLastDisplay = filterType ? regionDocs(usersLast).length : 0;
                let totalDriversLastDisplay = filterType ? regionDocs(workerLast).length : 0;
                let totalServiceLastDisplay = filterType ? regionDocs(serviceLast).length : regionDocs(allService).length;

                function calcPercent(curr, last) {
                    return last === 0 ? (curr === 0 ? 0 : 100) : ((curr - last) / last) * 100;
                }
        

                let ordersPercent = calcPercent(totalOrdersDisplay, totalOrdersLastDisplay);
                let usersPercent = calcPercent(totalUsersDisplay, totalUsersLastDisplay);
                let driversPercent = calcPercent(totalDriversDisplay, totalDriversLastDisplay);
                let servicePercent = calcPercent(totalServiceDisplay, totalServiceLastDisplay);

                let ordersInfo = getArrowAndClass(ordersPercent);
                let usersInfo = getArrowAndClass(usersPercent);
                let driversInfo = getArrowAndClass(driversPercent);
                let serviceInfo = getArrowAndClass(servicePercent);

                jQuery("#booking_count").text(totalOrdersDisplay);
                jQuery("#provider_count").text(totalUsersDisplay);
                jQuery("#worker_count").text(totalDriversDisplay);
                jQuery("#service_count").text(totalServiceDisplay);

                if(filterType !== null){
                    jQuery("#booking_percent").html(`<i class="fa ${ordersInfo.arrow}"></i> ${Math.abs(ordersPercent).toFixed(2)}% vs last period`).removeClass('green red').addClass(ordersInfo.className);
                    jQuery("#provider_percent").html(`<i class="fa ${usersInfo.arrow}"></i> ${Math.abs(usersPercent).toFixed(2)}% vs last period`).removeClass('green red').addClass(usersInfo.className);
                    jQuery("#worker_percent").html(`<i class="fa ${driversInfo.arrow}"></i> ${Math.abs(driversPercent).toFixed(2)}% vs last period`).removeClass('green red').addClass(driversInfo.className);
                    jQuery("#service_percent").html(`<i class="fa ${serviceInfo.arrow}"></i> ${Math.abs(servicePercent).toFixed(2)}% vs last period`).removeClass('green red').addClass(serviceInfo.className);
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

        async function getTotalEarnings(filterType = null, year = null, month = null, startDate = null, endDate = null) {
            const intRegex = /^\d+$/;
            const floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;
            const currentYear = new Date().getFullYear();

            // Monthly/daily arrays
            let vArr = Array(12).fill(0);
            let cArr = Array(12).fill(0);

            let totalEarning = 0;
            let adminCommission = 0;

            // Setup current period timestamps
            let startTS = null, endTS = null;
            if (filterType === 'year' && year) {
                startTS = firebase.firestore.Timestamp.fromDate(new Date(year, 0, 1));
                endTS = firebase.firestore.Timestamp.fromDate(new Date(year, 11, 31, 23, 59, 59));
            } else if (filterType === 'month' && year && month) {
                startTS = firebase.firestore.Timestamp.fromDate(new Date(year, month - 1, 1));
                endTS = firebase.firestore.Timestamp.fromDate(new Date(year, month, 0, 23, 59, 59));
                vArr = []; cArr = [];
            } else if (filterType === 'custom' && startDate && endDate) {
                startTS = firebase.firestore.Timestamp.fromDate(new Date(startDate));
                endTS = firebase.firestore.Timestamp.fromDate(new Date(endDate));
                vArr = []; cArr = [];
            }

            // Setup previous period for percentage change
            let prevStartTS = null, prevEndTS = null;
            if (filterType === 'year' && year) {
                prevStartTS = firebase.firestore.Timestamp.fromDate(new Date(year - 1, 0, 1));
                prevEndTS = firebase.firestore.Timestamp.fromDate(new Date(year - 1, 11, 31, 23, 59, 59));
            } else if (filterType === 'month' && year && month) {
                let prevMonth = month - 1, prevYear = year;
                if (prevMonth < 1) { prevMonth = 12; prevYear = year - 1; }
                prevStartTS = firebase.firestore.Timestamp.fromDate(new Date(prevYear, prevMonth - 1, 1));
                prevEndTS = firebase.firestore.Timestamp.fromDate(new Date(prevYear, prevMonth, 0, 23, 59, 59));
            } else if (filterType === 'custom' && startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const diff = end - start;
                prevEndTS = firebase.firestore.Timestamp.fromDate(new Date(start.getTime() - 1));
                prevStartTS = firebase.firestore.Timestamp.fromDate(new Date(start.getTime() - diff - 1));
            }
            // Fetch current period orders
            let ordersQuery = db.collection('provider_orders')
                .where('status', '==', "Order Completed")
                .where('sectionId', '==', active_id);

            if (startTS && endTS){
                ordersQuery = ordersQuery.where('createdAt', '>=', startTS).where('createdAt', '<=', endTS);
            } 
                
            ordersQuery = ordersQuery.orderBy('createdAt', 'desc');
            const snapshot = await ordersQuery.get();

            snapshot.docs.forEach(doc => {

                const orderData = doc.data();

                let order_subtotal = 0;
                let total_discount = 0;
                let total_tax_amount = 0;

                let quantity = parseFloat(orderData.quantity || 1);

                let basePrice = parseFloat(orderData.provider.price || 0);
                if (orderData.provider.disPrice && orderData.provider.disPrice != '0') {
                    basePrice = parseFloat(orderData.provider.disPrice);
                }

                // Subtotal
                order_subtotal = basePrice * quantity;

                // Discount
                let order_discount = parseFloat(orderData.discount || 0);
                total_discount = isNaN(order_discount) ? 0 : order_discount;

                // Order level tax
                let orderTaxable = Math.max(0, order_subtotal - total_discount);

                (orderData.taxSetting || []).forEach(tax => {
                    if (tax.enable) {
                        let taxAmount = tax.type === "percentage"
                            ? (parseFloat(tax.tax) / 100) * orderTaxable
                            : parseFloat(tax.tax);

                        total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                    }
                });

                // Extra Charges
                let platformFee = parseFloat(orderData.platformFee || 0);

                // Extra Taxes
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
                let price = (order_subtotal - total_discount) + platformFee + total_tax_amount;

                // Commission
                let commission = 0;
                let commissionBase = (order_subtotal - total_discount) + platformFee + total_tax_amount;

                if (orderData.adminCommission && orderData.adminCommission > 0) {
                    let commissionValue = parseFloat(orderData.adminCommission);
                    if (!isNaN(commissionValue)) {
                        if (orderData.adminCommissionType?.toLowerCase() === 'percentage') {
                            commission = (commissionBase * commissionValue) / 100;
                        } else {
                            commission = commissionValue;
                        }
                    }
                }

                // Add totals
                totalEarning += price;
                adminCommission += commission;

                // Monthly/daily aggregation
                if (orderData.createdAt) {
                    const orderDate = orderData.createdAt.toDate();
                    if (filterType === 'year' || !filterType) {
                        const monthIdx = orderDate.getMonth();
                        vArr[monthIdx] += price;
                        cArr[monthIdx] += commission;
                    } else if (filterType === 'month') {
                        if (orderDate.getFullYear() === year && orderDate.getMonth() + 1 === month) {
                            const dayIdx = orderDate.getDate() - 1;
                            vArr[dayIdx] = (vArr[dayIdx] || 0) + price;
                            cArr[dayIdx] = (cArr[dayIdx] || 0) + commission;
                        }
                    } else if (filterType === 'custom') {
                        const start = new Date(startDate);
                        const end = new Date(endDate);
                        if (orderDate >= start && orderDate <= end) {
                            const dayIdx = Math.floor((orderDate - start) / (1000 * 60 * 60 * 24));
                            vArr[dayIdx] = (vArr[dayIdx] || 0) + price;
                            cArr[dayIdx] = (cArr[dayIdx] || 0) + commission;
                        }
                    }
                }
            });

            // Fetch previous period totals
            let totalEarningPrev = 0, adminCommissionPrev = 0;
            if (prevStartTS && prevEndTS) {
                let prevQuery = db.collection('provider_orders')
                    .where('status', '==', "Order Completed")
                    .where('sectionId', '==', active_id)
                    .where('createdAt', '>=', prevStartTS)
                    .where('createdAt', '<=', prevEndTS);

                const prevSnapshot = await prevQuery.get();

                prevSnapshot.docs.forEach(doc => {

                    const orderData = doc.data();

                    let order_subtotal = 0;
                    let total_discount = 0;
                    let total_tax_amount = 0;

                    let quantity = parseFloat(orderData.quantity || 1);

                    let basePrice = parseFloat(orderData.provider.price || 0);
                    if (orderData.provider.disPrice && orderData.provider.disPrice != '0') {
                        basePrice = parseFloat(orderData.provider.disPrice);
                    }

                    order_subtotal = basePrice * quantity;

                    let order_discount = parseFloat(orderData.discount || 0);
                    total_discount = isNaN(order_discount) ? 0 : order_discount;

                    let orderTaxable = Math.max(0, order_subtotal - total_discount);

                    (orderData.taxSetting || []).forEach(tax => {
                        if (tax.enable) {
                            let taxAmount = tax.type === "percentage"
                                ? (parseFloat(tax.tax) / 100) * orderTaxable
                                : parseFloat(tax.tax);

                            total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                        }
                    });

                    let platformFee = parseFloat(orderData.platformFee || 0);

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

                    let price = (order_subtotal - total_discount) + platformFee + total_tax_amount;

                    let commission = 0;
                    let commissionBase = (order_subtotal - total_discount) + platformFee + total_tax_amount;

                    if (orderData.adminCommission && orderData.adminCommission > 0) {
                        let commissionValue = parseFloat(orderData.adminCommission);
                        if (!isNaN(commissionValue)) {
                            if (orderData.adminCommissionType?.toLowerCase() === 'percentage') {
                                commission = (commissionBase * commissionValue) / 100;
                            } else {
                                commission = commissionValue;
                            }
                        }
                    }

                    totalEarningPrev += price;
                    adminCommissionPrev += commission;
                });
            }

            const percentChange = totalEarningPrev === 0 ? (totalEarning === 0 ? 0 : 100) : ((totalEarning - totalEarningPrev) / totalEarningPrev) * 100;
            const percentCommission = adminCommissionPrev === 0 ? (adminCommission === 0 ? 0 : 100) : ((adminCommission - adminCommissionPrev) / adminCommissionPrev) * 100;

            const totalEarningDisplay = currencyAtRight
                ? parseFloat(totalEarning).toFixed(decimal_degits) + currentCurrency
                : currentCurrency + parseFloat(totalEarning).toFixed(decimal_degits);

            const adminCommissionDisplay = currencyAtRight
                ? parseFloat(adminCommission).toFixed(decimal_degits) + currentCurrency
                : currentCurrency + parseFloat(adminCommission).toFixed(decimal_degits);

            $("#earnings_count").text(totalEarningDisplay);
            $("#earnings_count_graph").text(totalEarningDisplay);
            $("#admincommission_count_graph").text(adminCommissionDisplay);
            $("#admincommission_count").text(adminCommissionDisplay);
            $("#total_earnings_header").text(totalEarningDisplay);
            $(".earnings_over_time").text(totalEarningDisplay);

            const ordersInfo = getArrowAndClass(percentChange);
            const commissionInfo = getArrowAndClass(percentCommission);
            if(filterType !== null){
                $("#earning_percent").html(`<i class="fa ${ordersInfo.arrow}"></i> ${Math.abs(percentChange).toFixed(2)}% vs last period`).removeClass('green red').addClass(ordersInfo.className);
                $("#commission_percent").html(`<i class="fa ${commissionInfo.arrow}"></i> ${Math.abs(percentCommission).toFixed(2)}% vs last period`).removeClass('green red').addClass(commissionInfo.className);
            }

            // 🔹 Chart
            let labels = [];
            if (filterType === 'year' || !filterType) {
                labels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            } else if (filterType === 'month') {
                const daysInMonth = new Date(year, month, 0).getDate();
                labels = Array.from({ length: daysInMonth }, (_, i) => (i + 1).toString());
            } else if (filterType === 'custom') {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const days = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
                labels = Array.from({ length: days }, (_, i) => { const d = new Date(start); d.setDate(d.getDate() + i); return `${d.getDate()}-${d.getMonth() + 1}`; });
            }

            const ctx = $('#sales-chart')[0].getContext('2d');
            renderChart(ctx, vArr, labels);
            setCommision(cArr, vArr);

            jQuery("#data-table_processing").hide();
        }


        function buildServicesHTML(snapshots) {
            var html = '';
            var count = 1;
            var rating = 0;
            snapshots.docs.forEach((listval) => {
                val = listval.data();

                val.id = listval.id;

                var route = '<?php echo url("ondemand-services/edit/{id}");?>';
                route = route.replace('{id}', val.id);

                html = html + '<tr>';
                let profileSrc = val.photos ? val.photos[0] : placeholderImage;

                html += '<td class="redirecttopage"><div class="top-driver-name">' +
                    '<img class="img-circle img-size-32" style="width:40px;height:40px; margin-right:5px;" src="' + profileSrc + '" alt="image">' +
                    '<a href="' + route + '">' + val.title + '</a>' +
                    '</div></td>';


                if (val.hasOwnProperty('reviewsCount') && val.reviewsCount != 0) {
                    rating = Math.round(parseFloat(val.reviewsSum) / parseInt(val.reviewsCount));
                } else {
                    rating = 0;
                }

                html = html + '<td><ul class="rating" data-rating="' + rating + '">';
                html = html + '<li class="rating__item"></li>';
                html = html + '<li class="rating__item"></li>';
                html = html + '<li class="rating__item"></li>';
                html = html + '<li class="rating__item"></li>';
                html = html + '<li class="rating__item"></li>';
                html = html + '</ul></td>';
                html = html + '<td><span class="action-btn"><a href="' + route + '" > <i class="mdi mdi-lead-pencil"></i></a></span></td>';
                html = html + '</tr>';

                rating = 0;
                count++;
            });
            return html;
        }

        async function getProviderServices(providerId) {
            var total = 0;
            await database.collection('providers_services').where('author', '==', providerId).get().then(async function (snapshots) {

                total = snapshots.docs.length;
            });
            return total;
        }
        
        async function buildProvidersHTML(snapshots) {          

            const providerList = await Promise.all(
                snapshots.docs.map(async (doc) => {
                    const val = doc.data();
                    const serviceTotal = await getProviderServices(doc.id);
                    return { val, serviceTotal };
                })
            );

            providerList.sort((a, b) => b.serviceTotal - a.serviceTotal);

            const topProviders = providerList.slice(0, 10);

            let html = '';
            for (const item of topProviders) {
                html += await getListData(item.val, item.serviceTotal);
            }

            return html;
        }
        
        async function getListData(val, serviceTotal) {
            let html = '';

            const provider_route = '<?php echo url("providers/edit/{id}");?>'.replace('{id}', val.id);
            const profileSrc = val.profilePic ? val.profilePic : placeholderImage;

            html += `
                <tr>
                    <td class="redirecttopage">
                        <div class="top-driver-name">
                            <img class="img-circle img-size-32" style="width:40px;height:40px; margin-right:5px;" 
                                src="${profileSrc}" alt="image">
                            <a href="${provider_route}">${val.firstName} ${val.lastName}</a>
                        </div>
                    </td>
                    <td data-url="${provider_route}" class="total_services_${val.id} redirecttopage">${serviceTotal}</td>
                    <td data-url="${provider_route}" class="redirecttopage">
                        <span class="action-btn">
                            <a><i class="mdi mdi-lead-pencil"></i></a>
                        </span>
                    </td>
                </tr>
            `;

            return html;
        }

        function buildOrderHTML(snapshots) {
            var html = '';
            var count = 1;
            snapshots.docs.forEach((listval) => {
                val = listval.data();
                val.id = listval.id;
                var route = '<?php echo url("ondemand-bookings/edit/{id}"); ?>';
                route = route.replace('{id}', val.id);

                var service_route = '<?php echo url("ondemand-services/edit/{id}");?>';
                service_route = service_route.replace('{id}', val.provider.id);

                var provider_route = '<?php echo url("providers/edit/{id}");?>';
                provider_route = provider_route.replace('{id}', val.provider.author);

                var user_route = '<?php echo url("users/view/{id}");?>';
                user_route = user_route.replace('{id}', val.author.id);

                html = html + '<tr>';

                html = html + '<td data-url="' + route + '" class="redirecttopage">' + val.id + '</td>';

                html = html + '<td data-url="' + user_route + '" class="redirecttopage">' + val.author.firstName + ' ' + val.author.lastName + '</td>';

                html = html + '<td data-url="' + service_route + '" class="redirecttopage">' + val.provider.title + '</td>';

                html = html + '<td data-url="' + provider_route + '" class="redirecttopage">' + val.provider.authorName + '</td>';

                var price = buildHTMLProductstotal(val);

                html = html + '<td data-url="' + route + '" class="redirecttopage">' + price + '</td>';

                html = html + '<td data-url="' + route + '" class="redirecttopage"><i class="fa fa-shopping-cart"></i> ' + val.quantity + '</td>';

                var date = val.createdAt.toDate().toDateString();
                var time = val.createdAt.toDate().toLocaleTimeString('en-US');
                html = html + '<td>' + date + ' ' + time + '</td>';

                if (val.status == 'Order Placed') {
                    html = html + '<td class="order_placed"><span>' + val.status + '</span></td>';

                } else if (val.status == 'Order Assigned') {
                    html = html + '<td class="order_assigned"><span>' + val.status + '</span></td>';
                }
                else if (val.status == 'Order Ongoing') {
                    html = html + '<td class="order_ongoing"><span>' + val.status + '</span></td>';

                }
                else if (val.status == 'Order Accepted') {
                    html = html + '<td class="order_accept"><span>' + val.status + '</span></td>';

                } else if (val.status == 'Order Rejected') {
                    html = html + '<td class="order_rejected"><span>' + val.status + '</span></td>';

                } else if (val.status == 'Order Completed') {
                    html = html + '<td class="order_completed"><span>' + val.status + '</span></td>';

                }
                else if (val.status == 'Order Cancelled') {
                    html = html + '<td class="order_rejected"><span>' + val.status + '</span></td>';
                } else {
                    html = html + '<td class="order_completed"><span>' + val.status + '</span></td>';

                }

                html = html + '</a></tr>';
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
                        label: "{{ trans('lang.total_sales') }}",
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

        $(document).ready(function () {
            $(document.body).on('click', '.redirecttopage', function () {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });
        });

        function buildHTMLProductstotal(orderData) {

            let order_subtotal = 0;
            let total_discount = 0;
            let total_tax_amount = 0;

            let quantity = parseFloat(orderData.quantity || 1);

            let basePrice = parseFloat(orderData.provider.price || 0);
            if (orderData.provider.disPrice && orderData.provider.disPrice != '0') {
                basePrice = parseFloat(orderData.provider.disPrice);
            }

            // Subtotal
            order_subtotal = basePrice * quantity;

            // Discount
            let order_discount = parseFloat(orderData.discount || 0);
            total_discount = isNaN(order_discount) ? 0 : order_discount;

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

        function setCommision(commissionsData, earningsData) {
            const earnings = parseFloat(jQuery("#earnings_count").text().replace(currentCurrency, ""));
            const adminCommission = parseFloat(jQuery("#admincommission_count").text().replace(currentCurrency, ""));

            const data = {
                labels: ["{{ trans('lang.total_sales') }}", "{{ trans('lang.admin_commissions') }}"],
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