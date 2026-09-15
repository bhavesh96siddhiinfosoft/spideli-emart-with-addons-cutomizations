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
                <li class="breadcrumb-item active">{{ trans('lang.vendor_subscription_plans') }}</li>
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
                            <h3 class="mb-0">{{ trans('lang.vendor_subscription_plans') }}</h3>
                            <span class="counter ml-3 plan_count"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('vendor_subscriptions.partials.tabs', ['active' => 'plans'])

        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.vendor_subscription_plans') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.vendor_subscription_plans_text') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="vendorPlanTable"
                                    class="display nowrap table table-hover table-striped table-bordered"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.vendor_subscription_store') }}</th>
                                            <th>{{ trans('lang.vendor_plan_title') }}</th>
                                            <th>{{ trans('lang.vendor_plan_price') }}</th>
                                            <th>{{ trans('lang.vendor_plan_period') }}</th>
                                            <th>{{ trans('lang.vendor_plan_enabled') }}</th>
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
    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;

    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        loadPlans();
    });

    async function loadPlans() {
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

        var snapshots = await database.collection('vendor_subscription_plans').get();

        var rows = [];
        snapshots.docs.forEach(function (doc) {
            var plan = doc.data();
            if (!inActiveRegion(plan)) {
                return;
            }
            rows.push(plan);
        });

        rows.sort(function (a, b) {
            return (storeNames[a.vendorID] || '').localeCompare(storeNames[b.vendorID] || '');
        });

        $('.plan_count').text(rows.length);
        $('#append_list1').html(rows.length ? rows.map(buildRow).join('') : '');

        $('#vendorPlanTable').DataTable({
            order: [[0, 'asc']],
            "language": datatableLang,
            responsive: true
        });

        jQuery("#data-table_processing").hide();
    }

    function buildRow(plan) {
        var period = String(plan.expiryDay) === '365' ?
            "{{ trans('lang.vendor_plan_period_annual') }}" :
            "{{ trans('lang.vendor_plan_period_monthly') }}";

        var enabled = plan.isEnable === true ?
            '<span class="badge badge-success">{{ trans('lang.vendor_plan_enabled') }}</span>' :
            '<span class="badge badge-danger">&mdash;</span>';

        return '<tr>' +
            '<td>' + (storeNames[plan.vendorID] || '') + '</td>' +
            '<td>' + (plan.title || '') + '</td>' +
            '<td>' + formatPrice(plan.price) + '</td>' +
            '<td>' + period + '</td>' +
            '<td>' + enabled + '</td>' +
            '</tr>';
    }

    /* A plan belongs to one store, so it carries a single regionId rather than
     * the regionIds array used by records that span several regions. */
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
</script>
@endsection
