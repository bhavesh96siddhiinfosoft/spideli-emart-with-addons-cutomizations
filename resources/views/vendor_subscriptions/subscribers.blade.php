@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.vendor_subscription_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.vendor_subscription_subscribers') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><i class="mdi mdi-account-multiple" style="font-size:28px;"></i></span>
                            <h3 class="mb-0">{{ trans('lang.vendor_subscription_subscribers') }}</h3>
                            <span class="counter ml-3 subscriber_count"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('vendor_subscriptions.partials.tabs', ['active' => 'subscribers'])

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.vendor_subscription_subscribers') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.vendor_subscription_subscribers_text') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="vendorSubscriberTable"
                                    class="display nowrap table table-hover table-striped table-bordered"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.vendor_subscription_store') }}</th>
                                            <th>{{ trans('lang.vendor_subscriber_name') }}</th>
                                            <th>{{ trans('lang.vendor_plan_title') }}</th>
                                            <th>{{ trans('lang.vendor_subscription_start') }}</th>
                                            <th>{{ trans('lang.vendor_subscription_expiry') }}</th>
                                            <th>{{ trans('lang.vendor_subscription_status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_list1"></tbody>
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

    var storeNames = {};
    var customerNames = {};

    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        loadSubscribers();
    });

    async function loadSubscribers() {
        var vendors = await database.collection('vendors').get();
        vendors.docs.forEach(function (doc) {
            storeNames[doc.id] = doc.data().title || '';
        });

        var snapshots = await database.collection('vendor_subscriptions').get();

        var rows = [];
        snapshots.docs.forEach(function (doc) {
            var subscription = doc.data();
            if (!inActiveRegion(subscription)) {
                return;
            }
            rows.push(subscription);
        });

        /* Customers are fetched once each, not once per row: several
         * subscriptions from the same person are common. */
        var customerIds = [];
        rows.forEach(function (r) {
            if (r.customerId && customerIds.indexOf(r.customerId) === -1) {
                customerIds.push(r.customerId);
            }
        });

        await Promise.all(customerIds.map(async function (id) {
            var customer = await database.collection('users').doc(id).get();
            if (customer.exists) {
                var data = customer.data();
                customerNames[id] = ((data.firstName || '') + ' ' + (data.lastName || '')).trim();
            }
        }));

        rows.sort(function (a, b) {
            return toTime(b.startDate) - toTime(a.startDate);
        });

        $('.subscriber_count').text(rows.length);
        $('#append_list1').html(rows.length ? rows.map(buildRow).join('') : '');

        $('#vendorSubscriberTable').DataTable({
            order: [[4, 'desc']],
            "language": datatableLang,
            responsive: true
        });

        jQuery("#data-table_processing").hide();
    }

    function buildRow(subscription) {
        /* The plan name comes from the snapshot stored on the subscription, not
         * from the plan document: a store renaming or deleting a plan must not
         * change what an existing subscriber is shown. */
        var planTitle = (subscription.plan && subscription.plan.title) ? subscription.plan.title : '';

        return '<tr>' +
            '<td>' + (storeNames[subscription.vendorID] || '') + '</td>' +
            '<td>' + (customerNames[subscription.customerId] || '') + '</td>' +
            '<td>' + planTitle + '</td>' +
            '<td>' + formatDate(subscription.startDate) + '</td>' +
            '<td>' + formatDate(subscription.expiryDate) + '</td>' +
            '<td>' + statusBadge(subscription) + '</td>' +
            '</tr>';
    }

    /* A subscription whose expiry has passed reads as expired even if nothing
     * has run to change its stored status - there is no scheduled job, so the
     * date is the truth. The store panel does the same. */
    function statusBadge(subscription) {
        var status = subscription.status || 'active';

        if (status === 'cancelled') {
            return '<span class="badge badge-danger">{{ trans('lang.vendor_subscription_cancelled') }}</span>';
        }

        var expiry = toDate(subscription.expiryDate);
        if (status === 'expired' || (expiry != null && expiry.getTime() < Date.now())) {
            return '<span class="badge badge-warning">{{ trans('lang.vendor_subscription_expired') }}</span>';
        }

        return '<span class="badge badge-success">{{ trans('lang.vendor_subscription_active') }}</span>';
    }

    function inActiveRegion(record) {
        var active = getActiveRegionId();
        if (!active) {
            return true;
        }
        return record.regionId === active;
    }

    function toDate(value) {
        if (!value) {
            return null;
        }
        if (typeof value.toDate === 'function') {
            return value.toDate();
        }
        var parsed = new Date(value);
        return isNaN(parsed.getTime()) ? null : parsed;
    }

    function toTime(value) {
        var date = toDate(value);
        return date == null ? 0 : date.getTime();
    }

    function formatDate(value) {
        var date = toDate(value);
        if (date == null) {
            return '';
        }

        var day = ('0' + date.getDate()).slice(-2);
        var month = ('0' + (date.getMonth() + 1)).slice(-2);

        return day + '-' + month + '-' + date.getFullYear();
    }
</script>
@endsection
