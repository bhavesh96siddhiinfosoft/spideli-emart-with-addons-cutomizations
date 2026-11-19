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
                <li class="breadcrumb-item active">{{ trans('lang.provider_subscription_plans') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><i class="mdi mdi-ticket-account" style="font-size:28px;"></i></span>
                            <h3 class="mb-0">{{ trans('lang.provider_subscription_plans') }}</h3>
                            <span class="counter ml-3 plan_count"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('provider_subscriptions.partials.tabs', ['active' => 'plans'])

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.provider_subscription_plans') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.provider_subscription_plans_text') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="providerPlanTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.provider_subscription_provider') }}</th>
                                            <th>{{ trans('lang.provider_plan_title') }}</th>
                                            <th>{{ trans('lang.provider_plan_price') }}</th>
                                            <th>{{ trans('lang.provider_plan_period') }}</th>
                                            <th>{{ trans('lang.provider_plan_enabled') }}</th>
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
        loadPlans().catch(function (e) { console.error(e); jQuery("#data-table_processing").hide(); });
    });

    async function loadPlans() {
        await spdLoadCurrency();
        var snapshots = await database.collection('provider_subscription_plans').get();
        var rows = snapshots.docs.map(function (doc) { return doc.data(); }).filter(inActiveRegion);
        await spdLoadNames(rows.map(function (r) { return r.providerId; }), providerNames);
        rows.sort(function (a, b) {
            return (providerNames[a.providerId] || '').localeCompare(providerNames[b.providerId] || '');
        });

        $('.plan_count').text(rows.length);
        $('#append_list1').html(rows.map(buildRow).join(''));
        $('#providerPlanTable').DataTable({order: [[0, 'asc']], "language": datatableLang, responsive: true});
        jQuery("#data-table_processing").hide();
    }

    function buildRow(plan) {
        var enabled = plan.isEnable === true ?
            '<span class="badge badge-success">' + spdEsc(@json(trans('lang.provider_plan_enabled'))) + '</span>' :
            '<span class="badge badge-danger">&mdash;</span>';
        return '<tr>' +
            '<td>' + spdEsc(providerNames[plan.providerId] || '') + '</td>' +
            '<td>' + spdEsc(plan.title || '') + '</td>' +
            '<td>' + spdEsc(formatPrice(plan.price)) + '</td>' +
            '<td>' + spdEsc(spdPeriod(plan.expiryDay)) + '</td>' +
            '<td>' + enabled + '</td>' +
            '</tr>';
    }
</script>
@endsection
