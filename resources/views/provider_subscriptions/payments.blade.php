@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.provider_subscription_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.provider_subscription_payments') }}</li>
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
                            <h3 class="mb-0">{{ trans('lang.provider_subscription_payments') }}</h3>
                            <span class="counter ml-3 payment_count"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('provider_subscriptions.partials.tabs', ['active' => 'payments'])

        {{-- #114: paid at a gateway but not recorded (provider_subscription_checkouts, status paid_not_credited).
             Shown only when there are some, so the admin can complete them by hand. --}}
        <div class="table-list unrecorded_box" style="display:none;">
            <div class="row">
                <div class="col-12">
                    <div class="card border border-danger">
                        <div class="card-header border-0">
                            <div class="card-header-title">
                                <h3 class="text-danger mb-2 h4">{{ trans('lang.provider_payment_unrecorded') }} <span class="badge badge-danger unrecorded_count"></span></h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.provider_payment_unrecorded_text') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="providerUnrecordedTable" class="display nowrap table table-hover table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.provider_subscription_provider') }}</th>
                                            <th>{{ trans('lang.provider_subscriber_name') }}</th>
                                            <th>{{ trans('lang.provider_plan_title') }}</th>
                                            <th>{{ trans('lang.provider_payment_amount') }}</th>
                                            <th>{{ trans('lang.provider_payment_method') }}</th>
                                            <th>{{ trans('lang.provider_payment_reference') }}</th>
                                            <th>{{ trans('lang.provider_payment_reason') }}</th>
                                            <th>{{ trans('lang.date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_unrecorded"></tbody>
                                </table>
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
                        <div class="card-header border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.provider_subscription_payments') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.provider_subscription_payments_text') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="providerPaymentTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.provider_subscription_provider') }}</th>
                                            <th>{{ trans('lang.provider_subscriber_name') }}</th>
                                            <th>{{ trans('lang.provider_plan_title') }}</th>
                                            <th>{{ trans('lang.provider_payment_amount') }}</th>
                                            <th>{{ trans('lang.provider_payment_commission') }}</th>
                                            <th>{{ trans('lang.provider_payment_earning') }}</th>
                                            <th>{{ trans('lang.provider_payment_method') }}</th>
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
@include('provider_subscriptions.partials.common_js')
<script type="text/javascript">
    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        loadPayments().catch(function (e) { console.error(e); jQuery("#data-table_processing").hide(); })
            .then(function () { return loadUnrecorded(); })
            .catch(function (e) { console.error(e); });
    });

    /* #114: purchases paid at a gateway that the website could not record (amount mismatch or a failed write).
     * Read-only: the admin checks the gateway reference and completes it by hand. */
    async function loadUnrecorded() {
        var snapshots = await database.collection('provider_subscription_checkouts').where('status', '==', 'paid_not_credited').get();
        var rows = snapshots.docs.map(function (doc) { return doc.data(); }).filter(inActiveRegion);
        if (!rows.length) {
            return;
        }
        await spdLoadNames(rows.map(function (r) { return r.providerId; }), providerNames);
        await spdLoadNames(rows.map(function (r) { return r.customerId; }), customerNames);
        rows.sort(function (a, b) { return toTime(b.createdAt) - toTime(a.createdAt); });
        $('.unrecorded_count').text(rows.length);
        $('#append_unrecorded').html(rows.map(buildUnrecordedRow).join(''));
        $('.unrecorded_box').show();
    }

    function buildUnrecordedRow(c) {
        var plan = c.plan || {};
        return '<tr>' +
            '<td>' + spdEsc(providerNames[c.providerId] || '') + '</td>' +
            '<td>' + spdEsc(customerNames[c.customerId] || '') + '</td>' +
            '<td>' + spdEsc(plan.title || '') + '</td>' +
            '<td>' + spdEsc(formatPrice(c.amount)) + '</td>' +
            '<td>' + spdEsc(c.method || '') + '</td>' +
            '<td>' + spdEsc(c.paymentReference || '') + '<br><small class="text-muted">' + spdEsc(c.id || '') + '</small></td>' +
            '<td>' + spdEsc(c.failureReason || '') + '</td>' +
            '<td>' + formatDate(c.createdAt) + '</td>' +
            '</tr>';
    }

    async function loadPayments() {
        await spdLoadCurrency();
        var snapshots = await database.collection('provider_subscription_payments').get();
        var rows = snapshots.docs.map(function (doc) { return doc.data(); }).filter(inActiveRegion);
        await spdLoadNames(rows.map(function (r) { return r.providerId; }), providerNames);
        await spdLoadNames(rows.map(function (r) { return r.customerId; }), customerNames);
        rows.sort(function (a, b) { return toTime(b.createdAt) - toTime(a.createdAt); });

        $('.payment_count').text(rows.length);
        $('#append_list1').html(rows.map(buildRow).join(''));
        $('#providerPaymentTable').DataTable({order: [[7, 'desc']], "language": datatableLang, responsive: true});
        jQuery("#data-table_processing").hide();
    }

    // Commission and earning are shown as recorded on the payment, never recalculated from today's rate.
    function buildRow(payment) {
        var earning = spdEsc(formatPrice(payment.providerEarning));
        if (payment.walletCredited === true) {
            earning += ' <span class="badge badge-success">' + spdEsc(@json(trans('lang.provider_payment_credited'))) + '</span>';
        }
        return '<tr>' +
            '<td>' + spdEsc(providerNames[payment.providerId] || '') + '</td>' +
            '<td>' + spdEsc(customerNames[payment.customerId] || '') + '</td>' +
            '<td>' + spdEsc(payment.planTitle || '') + '</td>' +
            '<td>' + spdEsc(formatPrice(payment.amount)) + '</td>' +
            '<td>' + spdEsc(formatPrice(payment.adminCommission)) + '</td>' +
            '<td>' + earning + '</td>' +
            '<td>' + spdEsc(payment.payment_method || '') + '</td>' +
            '<td data-order="' + toTime(payment.createdAt) + '">' + formatDate(payment.createdAt) + '</td>' +
            '</tr>';
    }
</script>
@endsection
