@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.adv_prices') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.adv_prices') }}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="error_top" style="display:none"></div>
        <div class="success_top" style="display:none"></div>
        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.adv_prices') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.adv_prices_text') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="advPricesTable">
                                    <thead>
                                        <tr>
                                            <th style="width:30%">{{ trans('lang.adv_zone') }}</th>
                                            <th>{{ trans('lang.adv_price_per_day') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="adv_prices_rows"></tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-primary save_adv_prices"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
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
    var advRegions = {}, advCurrencies = {}, advPrices = {}, advZones = [];

    // Zone and region names come from Firestore: escaped (quotes too, they go into attributes).
    function advEsc(value) {
        return String(value == null ? '' : value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    // The regions a zone serves: regionIds, or the older single regionId.
    function advZoneRegions(zone) {
        var ids = Array.isArray(zone.regionIds) ? zone.regionIds.slice() : [];
        if (!ids.length && zone.regionId) {
            ids.push(zone.regionId);
        }
        return ids.filter(function (id, i) { return id && ids.indexOf(id) === i && advRegions[id]; });
    }

    function advCurrencyOf(regionId) {
        var region = advRegions[regionId] || {};
        return advCurrencies[region.currencyId] || {};
    }

    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        loadAdvPrices().catch(function (e) { showAdvError(String(e)); });
    });

    async function loadAdvPrices() {
        var [zones, regions, currencies, prices] = await Promise.all([
            database.collection('zone').get(), database.collection('regions').get(),
            database.collection('currencies').get(), database.collection('advertisement_prices').get()
        ]);
        regions.docs.forEach(function (d) { advRegions[d.id] = d.data(); });
        currencies.docs.forEach(function (d) { advCurrencies[d.id] = d.data(); });
        prices.docs.forEach(function (d) { advPrices[d.id] = d.data().prices || {}; });
        advZones = zones.docs.map(function (d) { var z = d.data(); z.id = z.id || d.id; return z; });
        advZones.sort(function (a, b) { return String(a.name || '').localeCompare(String(b.name || '')); });
        $('#adv_prices_rows').html(advZones.map(buildAdvPriceRow).join(''));
        jQuery("#data-table_processing").hide();
    }

    function buildAdvPriceRow(zone) {
        var html = '<tr><td><strong>' + advEsc(zone.name) + '</strong>' +
            (zone.publish === false ? ' <span class="badge badge-secondary">' + advEsc(@json(trans('lang.adv_zone_hidden'))) + '</span>' : '') + '</td><td>';
        var regionIds = advZoneRegions(zone).filter(function (id) { return regionIdInScope(id); });
        if (!regionIds.length) {
            return html + '<span class="text-muted">' + advEsc(@json(trans('lang.adv_zone_no_region'))) + '</span></td></tr>';
        }
        regionIds.forEach(function (rid) {
            var cur = advCurrencyOf(rid);
            var value = (advPrices[zone.id] || {})[rid];
            html += '<div class="d-flex align-items-center mb-2">' +
                '<label class="mb-0 mr-2" style="min-width:200px">' + advEsc(advRegions[rid].name || rid) + ' (' + advEsc(cur.code || '?') + ')</label>' +
                '<input type="number" min="0" step="any" class="form-control adv_price" style="max-width:180px" data-zone="' + advEsc(zone.id) + '" data-region="' + advEsc(rid) + '"' +
                ' value="' + (typeof value === 'number' ? advEsc(value) : '') + '" placeholder="' + advEsc(@json(trans('lang.adv_price_not_set'))) + '">' +
                '<span class="ml-2 text-muted">' + advEsc(cur.symbol || '') + ' ' + advEsc(@json(trans('lang.adv_per_day'))) + '</span></div>';
        });
        return html + '</td></tr>';
    }

    // Reads the inputs: {zoneId: {regionId: number|null}}, or {error}. Empty = no price (cannot be paid online).
    function readAdvPrices() {
        var out = {}, error = '';
        $('.adv_price').each(function () {
            var zone = $(this).attr('data-zone'), region = $(this).attr('data-region');
            var text = String($(this).val() || '').trim();
            var value = null;
            if (text !== '') {
                var cur = advCurrencyOf(region);
                var decimals = parseInt(cur.decimal_degits, 10);
                value = parseFloat(text);
                if (!isFinite(text) || !(value >= 0)) {
                    error = @json(trans('lang.adv_price_error'));
                    return false;
                }
                value = Math.round(value * Math.pow(10, isNaN(decimals) ? 2 : decimals)) / Math.pow(10, isNaN(decimals) ? 2 : decimals);
            }
            (out[zone] = out[zone] || {})[region] = value;
        });
        return error ? {error: error} : {prices: out};
    }

    $('.save_adv_prices').click(async function () {
        $('.error_top, .success_top').hide();
        var read = readAdvPrices();
        if (read.error) {
            showAdvError(read.error);
            return;
        }
        jQuery("#data-table_processing").show();
        try {
            var batch = database.batch();
            Object.keys(read.prices).forEach(function (zoneId) {
                var zone = advZones.find(function (z) { return z.id === zoneId; }) || {};
                var prices = {};
                Object.keys(read.prices[zoneId]).forEach(function (rid) {
                    // merge: a region-bound admin only touches their regions; an emptied box removes the price
                    prices[rid] = read.prices[zoneId][rid] === null ? firebase.firestore.FieldValue.delete() : read.prices[zoneId][rid];
                });
                batch.set(database.collection('advertisement_prices').doc(zoneId), {
                    'zoneId': zoneId, 'zoneName': zone.name || '', 'prices': prices,
                    'updatedAt': firebase.firestore.FieldValue.serverTimestamp()
                }, {merge: true});
            });
            await batch.commit();
            jQuery("#data-table_processing").hide();
            // the panel's own saved message: a <p> inside .success_top (white card, green edge)
            $('.success_top').html('').append($('<p>').text(@json(trans('lang.adv_prices_saved')))).show();
            window.scrollTo(0, 0);
        } catch (e) {
            showAdvError(String(e));
        }
    });

    function showAdvError(message) {
        jQuery("#data-table_processing").hide();
        $('.error_top').show().html('').append($('<p>').text(message));
        window.scrollTo(0, 0);
    }
</script>
@endsection
