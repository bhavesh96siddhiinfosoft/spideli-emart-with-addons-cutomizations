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
                <li class="breadcrumb-item active">{{ trans('lang.provider_subscription_subscribers') }}</li>
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
                            <h3 class="mb-0">{{ trans('lang.provider_subscription_subscribers') }}</h3>
                            <span class="counter ml-3 subscriber_count"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('provider_subscriptions.partials.tabs', ['active' => 'subscribers'])

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.provider_subscription_subscribers') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.provider_subscription_subscribers_text') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="providerSubscriberTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.provider_subscription_provider') }}</th>
                                            <th>{{ trans('lang.provider_subscriber_name') }}</th>
                                            <th>{{ trans('lang.provider_plan_title') }}</th>
                                            <th>{{ trans('lang.provider_subscription_start') }}</th>
                                            <th>{{ trans('lang.provider_subscription_expiry') }}</th>
                                            <th>{{ trans('lang.provider_subscription_status') }}</th>
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
        loadSubscribers().catch(function (e) { console.error(e); jQuery("#data-table_processing").hide(); });
    });

    async function loadSubscribers() {
        var snapshots = await database.collection('provider_subscriptions').get();
        var rows = snapshots.docs.map(function (doc) { return doc.data(); }).filter(inActiveRegion);
        await spdLoadNames(rows.map(function (r) { return r.providerId; }), providerNames);
        await spdLoadNames(rows.map(function (r) { return r.customerId; }), customerNames);
        rows.sort(function (a, b) { return toTime(b.startDate) - toTime(a.startDate); });

        $('.subscriber_count').text(rows.length);
        $('#append_list1').html(rows.map(buildRow).join(''));
        $('#providerSubscriberTable').DataTable({order: [[3, 'desc']], "language": datatableLang, responsive: true});
        jQuery("#data-table_processing").hide();
    }

    function buildRow(subscription) {
        // The plan name comes from the snapshot taken at purchase, not from the plan document.
        var planTitle = (subscription.plan && subscription.plan.title) ? subscription.plan.title : '';
        return '<tr>' +
            '<td>' + spdEsc(providerNames[subscription.providerId] || '') + '</td>' +
            '<td>' + spdEsc(customerNames[subscription.customerId] || '') + '</td>' +
            '<td>' + spdEsc(planTitle) + '</td>' +
            '<td data-order="' + toTime(subscription.startDate) + '">' + formatDate(subscription.startDate) + '</td>' +
            '<td data-order="' + toTime(subscription.expiryDate) + '">' + formatDate(subscription.expiryDate) + '</td>' +
            '<td>' + statusBadge(subscription) + '</td>' +
            '</tr>';
    }

    // No scheduled job changes the stored status: a passed expiry date reads as expired.
    function statusBadge(subscription) {
        var status = subscription.status || 'active';
        if (status === 'cancelled') {
            return '<span class="badge badge-danger">' + spdEsc(@json(trans('lang.provider_subscription_cancelled'))) + '</span>';
        }
        var expiry = toDate(subscription.expiryDate);
        if (status === 'expired' || (expiry != null && expiry.getTime() < Date.now())) {
            return '<span class="badge badge-warning">' + spdEsc(@json(trans('lang.provider_subscription_expired'))) + '</span>';
        }
        return '<span class="badge badge-success">' + spdEsc(@json(trans('lang.provider_subscription_active'))) + '</span>';
    }
</script>
@endsection
