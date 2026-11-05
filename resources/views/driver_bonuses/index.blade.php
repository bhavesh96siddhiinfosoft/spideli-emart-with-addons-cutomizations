@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.driver_bonus_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.driver_bonus_plural') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0 flex-wrap">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.driver_bonus_plural') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.driver_bonus_help') }}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center flex-wrap">
                                <div class="card-header-btn mr-2 bonus-settings-link" style="display:none">
                                    <a class="btn btn-outline-primary rounded-full" href="{!! route('driver-bonuses.settings') !!}"><i class="mdi mdi-cog mr-2"></i>{{ trans('lang.driver_bonus_settings') }}</a>
                                </div>
                                <div class="card-header-btn mr-2 bonus-results-link" style="display:none">
                                    <a class="btn btn-outline-primary rounded-full" href="{!! route('driver-bonuses.results') !!}"><i class="mdi mdi-cash-multiple mr-2"></i>{{ trans('lang.driver_bonus_results') }}</a>
                                </div>
                                <div class="card-header-btn bonus-create-link" style="display:none">
                                    <a class="btn-primary btn rounded-full" href="{!! route('driver-bonuses.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.driver_bonus_create') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">{{ trans('lang.driver_bonus_not_paid_yet') }}</div>
                            <div class="table-responsive m-t-10">
                                <table class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.driver_bonus_name') }}</th>
                                            <th>{{ trans('lang.region') }}</th>
                                            <th>{{ trans('lang.driver_bonus_period') }}</th>
                                            <th>{{ trans('lang.driver_bonus_services') }}</th>
                                            <th>{{ trans('lang.driver_bonus_tiers') }}</th>
                                            <th>{{ trans('lang.driver_bonus_conditions') }}</th>
                                            <th>{{ trans('lang.status') }}</th>
                                            <th>{{ trans('lang.actions') }}</th>
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
    </div>
</div>
@endsection

@section('scripts')
<script>
    var database = firebase.firestore();
    var user_permissions = Object.values(JSON.parse('<?php echo @session('user_permissions'); ?>'));
    var canDelete = $.inArray('driver-bonus.delete', user_permissions) >= 0;
    var canEdit = $.inArray('driver-bonus.edit', user_permissions) >= 0;
    $('.bonus-create-link').toggle($.inArray('driver-bonus.create', user_permissions) >= 0);
    $('.bonus-settings-link').toggle($.inArray('driver-bonus.settings', user_permissions) >= 0);
    $('.bonus-results-link').toggle($.inArray('driver-bonus.results', user_permissions) >= 0);

    var SERVICE_LABELS = {
        cab: {!! json_encode(trans('lang.driver_bonus_service_cab')) !!},
        delivery: {!! json_encode(trans('lang.driver_bonus_service_delivery')) !!},
        parcel: {!! json_encode(trans('lang.driver_bonus_service_parcel')) !!},
        rental: {!! json_encode(trans('lang.driver_bonus_service_rental')) !!}
    };

    $(document).ready(loadRules);

    async function loadRules() {
        jQuery("#data-table_processing").show();
        var snapshots = await database.collection('driver_bonus_rules').get();
        var rules = regionDocs(snapshots).map(function (doc) { return doc.data(); });
        rules.sort(function (a, b) { return (a.name || '').localeCompare(b.name || ''); });

        var regions = {};
        (await database.collection('regions').get()).docs.forEach(function (doc) { regions[doc.id] = doc.data(); });
        var currencies = {};
        for (var i = 0; i < rules.length; i++) {
            var rid = rules[i].regionId || '';
            if (!(rid in currencies)) {
                currencies[rid] = rid ? await getCurrencyForRegion(rid) : null;
            }
        }

        var html = '';
        rules.forEach(function (rule) { html += buildRow(rule, regions, currencies[rule.regionId || '']); });
        $('#append_list').html(html || '<tr><td colspan="8">{{ trans('lang.driver_bonus_empty') }}</td></tr>');
        jQuery("#data-table_processing").hide();
    }

    function money(amount, currency) {
        var digits = currency && currency.decimal_degits ? parseInt(currency.decimal_degits, 10) : 0;
        var text = Number(amount).toFixed(digits);
        if (!currency || !currency.symbol) {
            return text;
        }
        return currency.symbolAtRight ? text + currency.symbol : currency.symbol + text;
    }

    function esc(value) {
        return $('<div></div>').text(value === undefined || value === null ? '' : value).html();
    }

    function buildRow(rule, regions, currency) {
        var editRoute = '{{ route('driver-bonuses.edit', ':id') }}'.replace(':id', rule.id);
        var period = rule.period === 'monthly' ? {!! json_encode(trans('lang.driver_bonus_monthly')) !!} : {!! json_encode(trans('lang.driver_bonus_weekly')) !!};
        var measure = rule.measure === 'value' ? {!! json_encode(trans('lang.driver_bonus_measure_value')) !!} : {!! json_encode(trans('lang.driver_bonus_measure_trips')) !!};
        var services = (rule.services || []).map(function (s) { return SERVICE_LABELS[s] || s; }).join(', ');
        var tiers = (rule.tiers || []).map(function (t) {
            var target = rule.measure === 'value' ? money(t.target, currency) : t.target + ' ' + {!! json_encode(trans('lang.driver_bonus_trips_short')) !!};
            return esc(target) + ' &rarr; <strong>' + esc(money(t.bonus, currency)) + '</strong>';
        }).join('<br>');
        var conditions = [];
        if (rule.minRating !== null && rule.minRating !== undefined) {
            conditions.push({!! json_encode(trans('lang.driver_bonus_cond_rating')) !!}.replace(':value', rule.minRating));
        }
        if (rule.maxCancellationRate !== null && rule.maxCancellationRate !== undefined) {
            conditions.push({!! json_encode(trans('lang.driver_bonus_cond_cancellation')) !!}.replace(':value', rule.maxCancellationRate));
        }
        if (rule.minLevel === 'silver' || rule.minLevel === 'gold') {
            conditions.push(rule.minLevel === 'gold' ? {!! json_encode(trans('lang.driver_bonus_cond_gold')) !!} : {!! json_encode(trans('lang.driver_bonus_cond_silver')) !!});
        }
        var region = regions[rule.regionId] ? regions[rule.regionId].name : (rule.regionId || '-');

        var html = '<tr>';
        html += '<td>' + (canEdit ? '<a href="' + editRoute + '">' + esc(rule.name) + '</a>' : esc(rule.name)) + '</td>';
        html += '<td>' + esc(region) + '</td>';
        html += '<td>' + esc(period) + '<br><small class="text-muted">' + esc(measure) + '</small></td>';
        html += '<td>' + esc(services) + '</td>';
        html += '<td>' + tiers + '</td>';
        html += '<td>' + (conditions.length ? esc(conditions.join(' · ')) : '-') + '</td>';
        html += rule.publish ? '<td><span class="badge badge-success">{{ trans('lang.active') }}</span></td>'
            : '<td><span class="badge badge-danger">{{ trans('lang.in_active') }}</span></td>';
        html += '<td><span class="action-btn">';
        if (canEdit) {
            html += '<a href="' + editRoute + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
        }
        if (canDelete) {
            html += '<a href="javascript:void(0)" class="delete-rule" data-id="' + esc(rule.id) + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
        }
        html += '</span></td></tr>';
        return html;
    }

    /* Results already paid keep the rule's name, so deleting a rule never
     * loses the history of what was paid under it. */
    $(document).on('click', '.delete-rule', function () {
        if (!confirm({!! json_encode(trans('lang.driver_bonus_delete_confirm')) !!})) {
            return;
        }
        database.collection('driver_bonus_rules').doc($(this).data('id')).delete().then(loadRules);
    });
</script>
@endsection
