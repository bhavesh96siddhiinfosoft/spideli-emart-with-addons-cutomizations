@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.driver_bonus_results') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('driver-bonuses') !!}">{{ trans('lang.driver_bonus_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.driver_bonus_results') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="table-list">
            <div class="card border">
                <div class="card-header border-0">
                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.driver_bonus_results') }}</h3>
                    <p class="mb-0 text-dark-2">{{ trans('lang.driver_bonus_results_help') }}</p>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select id="filter_period" class="form-control">
                                <option value="">{{ trans('lang.driver_bonus_all_periods') }}</option>
                                <option value="weekly">{{ trans('lang.driver_bonus_weekly') }}</option>
                                <option value="monthly">{{ trans('lang.driver_bonus_monthly') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="filter_status" class="form-control">
                                <option value="">{{ trans('lang.driver_bonus_all_statuses') }}</option>
                                <option value="paid">{{ trans('lang.driver_bonus_status_paid') }}</option>
                                <option value="capped">{{ trans('lang.driver_bonus_status_capped') }}</option>
                                <option value="not_reached">{{ trans('lang.driver_bonus_status_not_reached') }}</option>
                                <option value="not_eligible">{{ trans('lang.driver_bonus_status_not_eligible') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" id="filter_driver" class="form-control" placeholder="{{ trans('lang.driver_bonus_search_driver') }}">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="display nowrap table table-hover table-striped table-bordered" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('lang.driver') }}</th>
                                    <th>{{ trans('lang.driver_bonus_rule') }}</th>
                                    <th>{{ trans('lang.driver_bonus_period') }}</th>
                                    <th>{{ trans('lang.driver_bonus_done') }}</th>
                                    <th>{{ trans('lang.driver_level') }}</th>
                                    <th>{{ trans('lang.driver_bonus_amount') }}</th>
                                    <th>{{ trans('lang.status') }}</th>
                                </tr>
                            </thead>
                            <tbody id="append_list"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var database = firebase.firestore();
    var allResults = [];
    var driverNames = {};
    var currencies = {};

    var STATUS = {
        paid: ['badge-success', {!! json_encode(trans('lang.driver_bonus_status_paid')) !!}],
        capped: ['badge-warning', {!! json_encode(trans('lang.driver_bonus_status_capped')) !!}],
        not_reached: ['badge-secondary', {!! json_encode(trans('lang.driver_bonus_status_not_reached')) !!}],
        not_eligible: ['badge-danger', {!! json_encode(trans('lang.driver_bonus_status_not_eligible')) !!}]
    };
    var REASON = {
        rating: {!! json_encode(trans('lang.driver_bonus_reason_rating')) !!},
        cancellation: {!! json_encode(trans('lang.driver_bonus_reason_cancellation')) !!},
        level: {!! json_encode(trans('lang.driver_bonus_reason_level')) !!},
        company_missing: {!! json_encode(trans('lang.driver_bonus_reason_company_missing')) !!}
    };
    var LEVEL = {
        bronze: {!! json_encode(trans('lang.driver_level_bronze')) !!},
        silver: {!! json_encode(trans('lang.driver_level_silver')) !!},
        gold: {!! json_encode(trans('lang.driver_level_gold')) !!}
    };

    $(document).ready(async function () {
        jQuery("#data-table_processing").show();
        var snapshots = await database.collection('driver_bonus_results').get();
        allResults = regionDocs(snapshots).map(function (doc) { return doc.data(); });
        allResults.sort(function (a, b) { return String(b.periodKey || '').localeCompare(String(a.periodKey || '')); });

        var ids = {};
        allResults.forEach(function (r) { if (r.driverId) { ids[r.driverId] = 1; } });
        await Promise.all(Object.keys(ids).map(async function (id) {
            var d = await database.collection('users').doc(id).get();
            driverNames[id] = d.exists ? ((d.data().firstName || '') + ' ' + (d.data().lastName || '')).trim() : id;
        }));
        for (var i = 0; i < allResults.length; i++) {
            var rid = allResults[i].regionId || '';
            if (!(rid in currencies)) {
                currencies[rid] = rid ? await getCurrencyForRegion(rid) : null;
            }
        }
        render();
        jQuery("#data-table_processing").hide();
    });

    $('#filter_period, #filter_status').on('change', render);
    $('#filter_driver').on('input', render);

    function esc(value) {
        return $('<div></div>').text(value === undefined || value === null ? '' : value).html();
    }

    function money(amount, currency) {
        var digits = currency && currency.decimal_degits ? parseInt(currency.decimal_degits, 10) : 0;
        var text = Number(amount || 0).toFixed(digits);
        if (!currency || !currency.symbol) {
            return text;
        }
        return currency.symbolAtRight ? text + currency.symbol : currency.symbol + text;
    }

    function render() {
        var period = $('#filter_period').val();
        var status = $('#filter_status').val();
        var search = $('#filter_driver').val().trim().toLowerCase();
        var html = '';
        allResults.forEach(function (r) {
            var name = driverNames[r.driverId] || r.driverId || '';
            if ((period && r.period !== period) || (status && r.status !== status) || (search && name.toLowerCase().indexOf(search) < 0)) {
                return;
            }
            var currency = currencies[r.regionId || ''];
            var st = STATUS[r.status] || ['badge-secondary', r.status || ''];
            var done = (r.trips || 0) + ' {{ trans('lang.driver_bonus_trips_short') }} · ' + money(r.value, currency);
            var note = r.reason && REASON[r.reason] ? '<br><small class="text-muted">' + esc(REASON[r.reason]) + '</small>' : '';
            if (r.paidToCompany && Number(r.bonus) > 0) {
                note += '<br><small class="text-muted">' + esc({!! json_encode(trans('lang.driver_bonus_paid_to_company')) !!}) + '</small>';
            }
            html += '<tr>'
                + '<td>' + esc(name) + '</td>'
                + '<td>' + esc(r.ruleName) + '</td>'
                + '<td>' + esc(r.periodKey) + '</td>'
                + '<td>' + esc(done) + '</td>'
                + '<td>' + esc(LEVEL[r.level] || r.level || '-') + '</td>'
                + '<td><strong>' + esc(money(r.bonus, currency)) + '</strong></td>'
                + '<td><span class="badge ' + st[0] + '">' + esc(st[1]) + '</span>' + note + '</td>'
                + '</tr>';
        });
        $('#append_list').html(html || '<tr><td colspan="7">{{ trans('lang.driver_bonus_results_empty') }}</td></tr>');
    }
</script>
@endsection
