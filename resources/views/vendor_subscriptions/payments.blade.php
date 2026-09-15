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
                <li class="breadcrumb-item active">{{ trans('lang.vendor_subscription_payments') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><i class="mdi mdi-cash-multiple" style="font-size:28px;"></i></span>
                            <h3 class="mb-0">{{ trans('lang.vendor_subscription_payments') }}</h3>
                            <span class="counter ml-3 payment_count"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('vendor_subscriptions.partials.tabs', ['active' => 'payments'])

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.vendor_subscription_payments') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.vendor_subscription_payments_text') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="vendorPaymentTable"
                                    class="display nowrap table table-hover table-striped table-bordered"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.vendor_subscription_store') }}</th>
                                            <th>{{ trans('lang.vendor_subscriber_name') }}</th>
                                            <th>{{ trans('lang.vendor_payment_amount') }}</th>
                                            <th>{{ trans('lang.vendor_payment_commission') }}</th>
                                            <th>{{ trans('lang.vendor_payment_earning') }}</th>
                                            <th>{{ trans('lang.vendor_payment_method') }}</th>
                                            <th>{{ trans('lang.date') }}</th>
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
    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;

    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        loadPayments();
    });

    async function loadPayments() {
        var currency = await regionCurrencyRef().limit(1).get();
        if (currency.docs.length > 0) {
            var currencyData = currency.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        }

        var vendors = await database.collection('vendors').get();
        vendors.docs.forEach(function (doc) {
            storeNames[doc.id] = doc.data().title || '';
        });

        var snapshots = await database.collection('vendor_subscription_payments').get();

        var rows = [];
        snapshots.docs.forEach(function (doc) {
            var payment = doc.data();
            if (!inActiveRegion(payment)) {
                return;
            }
            rows.push(payment);
        });

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
            return toTime(b.createdAt) - toTime(a.createdAt);
        });

        $('.payment_count').text(rows.length);
        $('#append_list1').html(rows.length ? rows.map(buildRow).join('') : '');

        $('#vendorPaymentTable').DataTable({
            order: [[6, 'desc']],
            "language": datatableLang,
            responsive: true
        });

        jQuery("#data-table_processing").hide();
    }

    function buildRow(payment) {
        /* Commission and earning are shown as recorded on the payment, not
         * recalculated from the store's current rate. Changing the platform's
         * cut must never rewrite what an old payment earned. */
        return '<tr>' +
            '<td>' + (storeNames[payment.vendorID] || '') + '</td>' +
            '<td>' + (customerNames[payment.customerId] || '') + '</td>' +
            '<td>' + formatPrice(payment.amount) + '</td>' +
            '<td>' + formatPrice(payment.adminCommission) + '</td>' +
            '<td>' + formatPrice(payment.vendorEarning) + '</td>' +
            '<td>' + (payment.payment_method || '') + '</td>' +
            '<td>' + formatDate(payment.createdAt) + '</td>' +
            '</tr>';
    }

    function inActiveRegion(record) {
        var active = getActiveRegionId();
        if (!active) {
            return true;
        }
        return record.regionId === active;
    }

    function formatPrice(value) {
        var amount = parseFloat(value || 0).toFixed(decimal_degits);
        return currencyAtRight ? amount + '' + currentCurrency : currentCurrency + '' + amount;
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
