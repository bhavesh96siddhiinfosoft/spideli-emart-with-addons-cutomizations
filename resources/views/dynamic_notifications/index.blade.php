@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.dynamic_notification') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.notificaions_table') }}</li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/notification.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.dynamic_notification') }}</h3>
                                <span class="counter ml-3 notification_count"></span>
                            </div>
                            <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">

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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.notificaions_table') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.notifications_table_text') }}</p>
                                </div>

                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="notificationTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('lang.service_type') }}</th>

                                                <th>{{ trans('lang.notification_type') }}</th>

                                                <th>{{ trans('lang.subject') }}</th>

                                                <th>{{ trans('lang.message') }}</th>

                                                <th>{{ trans('lang.date_created') }}</th>

                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="append_restaurants">
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
        var database = firebase.firestore();
        var refData = database.collection('dynamic_notification').orderBy('service_type', 'asc');
        var ref = refData.orderBy('createdAt', 'desc');
        var append_list = '';

        $(document).ready(function() {

            jQuery("#data-table_processing").show();
           
            $('body').tooltip({
                selector: '[data-toggle="tooltip"]'
            });


            const table = $('#notificationTable').DataTable({
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
                    var orderableColumns = ['service_type', 'notificationType', 'subject', 'message', 'createdAt', '']; // Ensure this matches the actual column names
                    const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                    if (searchValue.length >= 3 || searchValue.length === 0) {
                        $('#data-table_processing').show();
                    }
                    await ref.get().then(async function(querySnapshot) {
                        if (querySnapshot.empty) {
                            $('.notification_count').text(0);
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
                            if (childData.type == "restaurant_rejected") {

                                childData.notificationType = "{{ trans('lang.order_rejected_by_restaurant') }}";
                                childData.title = "{{ trans('lang.order_reject_notification') }}";
                            } else if (childData.type == "restaurant_accepted") {
                                childData.notificationType = "{{ trans('lang.order_accepted_by_restaurant') }}";
                                childData.title = "{{ trans('lang.order_accept_notification') }}";
                            } else if (childData.type == "takeaway_completed") {
                                childData.notificationType = "{{ trans('lang.takeaway_order_completed') }}";
                                childData.title = "{{ trans('lang.takeaway_order_complete_notification') }}";
                            } else if (childData.type == "store_accepted") {
                                childData.notificationType = "{{ trans('lang.order_accepted_by_restaurant') }}";
                                childData.title = "{{ trans('lang.order_accept_notification') }}";
                            } else if (childData.type == "store_intransit") {
                                childData.notificationType = "{{ trans('lang.order_intransit_by_restaurant') }}";
                                childData.title = "{{ trans('lang.order_intransit_notification') }}";
                            } else if (childData.type == "store_completed") {
                                childData.notificationType = "{{ trans('lang.order_completed_by_restaurant') }}";
                                childData.title = "{{ trans('lang.order_complete_notification') }}";
                            } else if (childData.type == "cab_accepted") {
                                childData.notificationType = "{{ trans('lang.cab_accepted_by_driver') }}";
                                childData.title = "{{ trans('lang.cab_accepted_order_notification') }}";
                            } else if (childData.type == "cab_completed") {
                                childData.notificationType = "{{ trans('lang.cab_completed_by_driver') }}";
                                childData.title = "{{ trans('lang.cab_completed_order_notification') }}";
                            } else if (childData.type == "driver_completed") {
                                childData.notificationType = "{{ trans('lang.driver_completed_order') }}";
                                childData.title = "{{ trans('lang.order_complete_notification') }}";

                            } else if (childData.type == "driver_accepted") {
                                childData.notificationType = "{{ trans('lang.driver_accepted_order') }}";
                                childData.title = "{{ trans('lang.driver_accept_order_notification') }}";
                            } else if (childData.type == "dinein_canceled") {
                                childData.notificationType = "{{ trans('lang.dine_order_book_canceled_by_restaurant') }}";
                                childData.title = "{{ trans('lang.dinein_cancel_notification') }}";
                            } else if (childData.type == "dinein_accepted") {
                                childData.notificationType = "{{ trans('lang.dine_order_book_accepted_by_restaurant') }}";
                                childData.title = "{{ trans('lang.dinein_accept_notification') }}";
                            } else if (childData.type == "order_placed") {
                                childData.notificationType = "{{ trans('lang.new_order_place') }}";
                                childData.title = "{{ trans('lang.order_placed_notification') }}";
                            } else if (childData.type == "dinein_placed") {
                                childData.notificationType = "{{ trans('lang.new_dine_booking') }}";
                                childData.title = "{{ trans('lang.dinein_order_place_notification') }}";

                            } else if (childData.type == "schedule_order") {
                                childData.notificationType = "{{ trans('lang.shedule_order') }}";
                                childData.title = "{{ trans('lang.schedule_order_notification') }}";
                            } else if (childData.type == "payment_received") {
                                childData.notificationType = "{{ trans('lang.pament_received') }}";
                                childData.title = "{{ trans('lang.payment_receive_notification') }}";
                            } else if (childData.type == "parcel_accepted") {

                                childData.notificationType = "{{ trans('lang.parcel_accepted_by_driver') }}";
                                childData.title = "{{ trans('lang.parcel_accept_notification') }}";
                            } else if (childData.type == "parcel_rejected") {
                                childData.notificationType = "{{ trans('lang.parcel_rejected_by_driver') }}";
                                childData.title = "{{ trans('lang.parcel_reject_notification') }}";
                            } else if (childData.type == "rental_booked") {
                                childData.notificationType = "{{ trans('lang.rental_booked_by_customer') }}";
                                childData.title = "{{ trans('lang.rental_book_notification') }}";
                            } else if (childData.type == "rental_rejected") {
                                childData.notificationType = "{{ trans('lang.rental_rejected_by_driver') }}";
                                childData.title = "{{ trans('lang.rental_reject_notification') }}";
                            } else if (childData.type == "rental_accepted") {
                                childData.notificationType = "{{ trans('lang.rental_accepted_by_driver') }}";
                                childData.title = "{{ trans('lang.rental_accept_notification') }}";
                            } else if (childData.type == "start_ride") {
                                childData.notificationType = "{{ trans('lang.start_ride_by_driver') }}";
                                childData.title = "{{ trans('lang.start_ride_notification') }}";
                            } else if (childData.type == "rental_completed") {
                                childData.notificationType = "{{ trans('lang.rental_completed_by_driver') }}";
                                childData.title = "{{ trans('lang.rental_complete_notification') }}";
                            } else if (childData.type == "parcel_completed") {
                                childData.notificationType = "{{ trans('lang.parcel_completed_by_driver') }}";
                                childData.title = "{{ trans('lang.parcel_complete_notification') }}";
                            } else if (childData.type == "provider_accepted") {
                                childData.notificationType = "{{ trans('lang.booking_accepted_by_provider') }}";
                                childData.title = "{{ trans('lang.booking_accepted_notification') }}";
                            } else if (childData.type == "booking_placed") {
                                childData.notificationType = "{{ trans('lang.service_booked_by_customer') }}";
                                childData.title = "{{ trans('lang.service_booked_notification') }}";
                            } else if (childData.type == "service_intransit") {
                                childData.notificationType = "{{ trans('lang.service_intransit_by_provider') }}";
                                childData.title = "{{ trans('lang.service_intransit_notification') }}";
                            } else if (childData.type == "provider_rejected") {
                                childData.notificationType = "{{ trans('lang.service_rejected_by_provider') }}";
                                childData.title = "{{ trans('lang.service_rejected_notification') }}";
                            } else if (childData.type == "service_completed") {
                                childData.notificationType = "{{ trans('lang.service_completed_by_provider') }}";
                                childData.title = "{{ trans('lang.service_completed_notification') }}";
                            } else if (childData.type == "stop_time") {
                                childData.notificationType = "{{ trans('lang.service_stop_by_provider') }}";
                                childData.title = "{{ trans('lang.service_stop_notification') }}";
                            } else if (childData.type == "service_cancelled") {
                                childData.notificationType = "{{ trans('lang.service_cancelled_by_customer') }}";
                                childData.title = "{{ trans('lang.service_cancelled_notification') }}";
                            } else if (childData.type == "service_charges") {
                                childData.notificationType = "{{ trans('lang.service_add_extra_charges') }}";
                                childData.title = "{{ trans('lang.extra_charge_notification') }}";
                            } else if (childData.type == "worker_assigned") {
                                childData.notificationType = "{{ trans('lang.service_assigned_to_worker') }}";
                                childData.title = "{{ trans('lang.service_assigned_to_worker_notification') }}";
                            } else if (childData.type == "advertisement_approved") {
                                childData.notificationType = "{{ trans('lang.advertisement_approved') }}";
                                childData.title = "{{ trans('lang.advertisement_approved_notification') }}";
                            } else if (childData.type == "advertisement_cancelled") {
                                childData.notificationType = "{{ trans('lang.advertisement_cancelled') }}";
                                childData.title = "{{ trans('lang.advertisement_cancelled_notification') }}";
                            } else if (childData.type == "advertisement_paused") {
                                childData.notificationType = "{{ trans('lang.advertisement_paused') }}";
                                childData.title = "{{ trans('lang.advertisement_paused_notification') }}";
                            } else if (childData.type == "advertisement_resumed") {
                                childData.notificationType = "{{ trans('lang.advertisement_resumed') }}";
                                childData.title = "{{ trans('lang.advertisement_resumed_notification') }}";
                            } else if (childData.type == "assign_order") {
                                childData.notificationType = "{{ trans('lang.assign_order') }}";
                                childData.title = "{{ trans('lang.assign_order_notification') }}";
                            } else if (childData.type == "restaurant_cancelled") {
                                childData.notificationType = "{{ trans('lang.restaurant_cancelled') }}";
                                childData.title = "{{ trans('lang.restaurant_cancelled_notification') }}";
                            } else if (childData.type == "driver_cancelled") {
                                childData.notificationType = "{{ trans('lang.driver_cancelled') }}";
                                childData.title = "{{ trans('lang.driver_cancelled_notification') }}";
                            } else if (childData.type == "new_delivery_order") {
                                childData.notificationType = "{{ trans('lang.new_delivery_order') }}";
                                childData.title = "{{ trans('lang.new_delivery_order_notification') }}";
                            } else if (childData.type == "customer_cancelled") {
                                childData.notificationType = "{{trans('lang.customer_cancelled')}}";
                                childData.title = "{{trans('lang.customer_cancelled_notification')}}";
                            }else if (childData.type == "dinein_rejected") {
                                childData.notificationType = "{{trans('lang.dinein_rejected')}}";
                                childData.title = "{{trans('lang.dinein_rejected_notification')}}";
                            }

                            var date = '';
                            var time = '';
                            if (childData.hasOwnProperty("createdAt") && childData.createdAt != '') {
                                try {
                                    date = childData.createdAt.toDate().toDateString();
                                    time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                                } catch (err) {

                                }
                            }

                            var createdAt = date + '<br> ' + time;

                            if (searchValue) {

                                if (
                                    (childData.service_type && childData.service_type.toLowerCase().toString().includes(searchValue)) ||
                                    (childData.notificationType && childData.notificationType.toLowerCase().toString().includes(searchValue)) ||
                                    (childData.subject && childData.subject.toLowerCase().toString().includes(searchValue)) ||
                                    (childData.message && childData.message.toLowerCase().toString().includes(searchValue)) ||
                                    (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1)) {
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
                                } catch (err) {}
                            }

                            if (orderDirection === 'asc') {
                                return (aValue > bValue) ? 1 : -1;
                            } else {
                                return (aValue < bValue) ? 1 : -1;
                            }
                        });

                        const totalRecords = filteredRecords.length;
                        $('.notification_count').text(totalRecords);
                        const paginatedRecords = filteredRecords.slice(start, start + length);

                        await Promise.all(paginatedRecords.map(async (childData) => {
                            var getData = await buildHTML(childData);
                            records.push(getData);
                        }));
                        
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: totalRecords, // Total number of records in Firestore
                            recordsFiltered: totalRecords, // Number of records after filtering (if any)
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
                order: [
                    [4, 'asc']
                ],
                columnDefs: [

                    {
                        orderable: false,
                        targets: 5
                    },
                ],
                "language": datatableLang,
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

        function buildHTML(val) {

            var html = [];
            var number = [];
            newdate = '';
            var id = val.id;
            var route1 = '{{ route('dynamic-notification.save', ':id') }}';
            route1 = route1.replace(":id", id);
            html.push('<td>' + val.service_type + '</td>');
            html.push('<td>' + val.notificationType + '</td>');
            html.push('<td>' + val.subject + '</td>');
            html.push('<td>' + val.message + '</td>');

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

            html.push('<span class="action-btn"><a data-toggle="tooltip" title="' + val.title + '"><i class="text-dark fs-12 fa-solid fa fa-info"  aria-describedby="tippy-3"></i></a><a href="' + route1 + '" data-toggle="tooltip" title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a></span>');


            return html;
        }
    </script>
@endsection
