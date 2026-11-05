@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.driver_bonus_settings') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('driver-bonuses') !!}">{{ trans('lang.driver_bonus_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.driver_bonus_settings') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <p class="text-muted">{{ trans('lang.driver_bonus_settings_help') }}</p>
            <div id="region_cards"></div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary save-settings-btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
            <a href="{!! route('driver-bonuses') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
        </div>
    </div>
</div>
<style>
    .bonus-region-card { border: 1px solid #e5e5e5; border-radius: 8px; padding: 16px 18px; margin-bottom: 18px; background: #fff; }
    .bonus-region-card h4 { margin: 0 0 12px; }
    .bonus-region-card .bonus-levels th, .bonus-region-card .bonus-levels td { vertical-align: middle; }
    .bonus-region-card .bonus-levels input { min-width: 90px; }
    .bonus-region-card .err { color: #e3342f; }
</style>
@endsection

@section('scripts')
<script>
    var database = firebase.firestore();
    var settingsRef = database.collection('settings').doc('driverBonus');
    var shownRegions = [];

    $(document).ready(async function () {
        jQuery("#overlay").show();
        var doc = await settingsRef.get();
        var saved = (doc.exists && doc.data().regions) ? doc.data().regions : {};
        shownRegions = await getPublishedRegions();
        var html = '';
        for (var i = 0; i < shownRegions.length; i++) {
            var currency = await getCurrencyForRegion(shownRegions[i].id);
            html += regionCard(shownRegions[i], currency);
        }
        $('#region_cards').html(html || '<p>{{ trans('lang.driver_bonus_no_regions') }}</p>');
        shownRegions.forEach(function (region) { fillRegion(region.id, saved[region.id] || {}); });
        jQuery("#overlay").hide();
    });

    function esc(value) {
        return $('<div></div>').text(value === undefined || value === null ? '' : value).html();
    }

    function regionCard(region, currency) {
        var id = region.id;
        var symbol = currency && currency.symbol ? ' (' + esc(currency.symbol) + ')' : '';
        var levelRow = function (level, label) {
            return '<tr><th>' + label + '</th>'
                + '<td><input type="number" min="0" step="1" class="form-control" id="' + level + '_trips_' + id + '"></td>'
                + '<td><input type="number" min="0" max="5" step="0.1" class="form-control" id="' + level + '_rating_' + id + '"></td>'
                + '<td><input type="number" min="0" max="100" step="any" class="form-control" id="' + level + '_cancel_' + id + '"></td>'
                + '<td><input type="number" min="0" max="100" step="any" class="form-control" id="' + level + '_boost_' + id + '"></td></tr>';
        };
        return '<div class="bonus-region-card" data-region="' + esc(id) + '">'
            + '<h4>' + esc(region.name) + symbol + '</h4>'
            + '<div class="form-check mb-3"><input type="checkbox" id="enabled_' + id + '"><label class="control-label" for="enabled_' + id + '">{{ trans('lang.driver_bonus_enabled') }}</label></div>'
            + '<div class="row">'
            + '<div class="col-md-6 form-group"><label>{{ trans('lang.driver_bonus_max_week') }}' + symbol + '</label><input type="number" min="0" step="any" class="form-control" id="max_week_' + id + '"><small class="text-muted">{{ trans('lang.driver_bonus_max_help') }}</small></div>'
            + '<div class="col-md-6 form-group"><label>{{ trans('lang.driver_bonus_max_month') }}' + symbol + '</label><input type="number" min="0" step="any" class="form-control" id="max_month_' + id + '"><small class="text-muted">{{ trans('lang.driver_bonus_max_help') }}</small></div>'
            + '</div>'
            + '<h5 class="mt-2">{{ trans('lang.driver_levels') }}</h5>'
            + '<p class="text-muted mb-2">{{ trans('lang.driver_levels_help') }}</p>'
            + '<div class="table-responsive"><table class="table table-bordered bonus-levels"><thead><tr><th>{{ trans('lang.driver_level') }}</th>'
            + '<th>{{ trans('lang.driver_level_min_trips') }}</th><th>{{ trans('lang.driver_level_min_rating') }}</th>'
            + '<th>{{ trans('lang.driver_level_max_cancellation') }}</th><th>{{ trans('lang.driver_level_boost') }}</th></tr></thead><tbody>'
            /* Bronze: every driver's starting level - no thresholds, only its boost. */
            + '<tr><th>' + {!! json_encode(trans('lang.driver_level_bronze')) !!} + '</th>'
            + '<td colspan="3" class="text-muted">' + {!! json_encode(trans('lang.driver_level_bronze_note')) !!} + '</td>'
            + '<td><input type="number" min="0" max="100" step="any" class="form-control" id="bronze_boost_' + id + '"></td></tr>'
            + levelRow('silver', {!! json_encode(trans('lang.driver_level_silver')) !!})
            + levelRow('gold', {!! json_encode(trans('lang.driver_level_gold')) !!})
            + '</tbody></table></div>'
            + '<div class="err" id="error_' + id + '"></div>'
            + '</div>';
    }

    function show(value) {
        return value === null || value === undefined ? '' : value;
    }

    function fillRegion(id, s) {
        $('#enabled_' + id).prop('checked', s.enabled === true);
        $('#max_week_' + id).val(show(s.maxPerWeek));
        $('#max_month_' + id).val(show(s.maxPerMonth));
        $('#bronze_boost_' + id).val(show(s.levels && s.levels.bronze ? s.levels.bronze.bonusBoost : null));
        ['silver', 'gold'].forEach(function (level) {
            var l = (s.levels && s.levels[level]) || {};
            $('#' + level + '_trips_' + id).val(show(l.minTrips));
            $('#' + level + '_rating_' + id).val(show(l.minRating));
            $('#' + level + '_cancel_' + id).val(show(l.maxCancellationRate));
            $('#' + level + '_boost_' + id).val(show(l.bonusBoost));
        });
    }

    /* A blank box is null ("no limit" / "not used"); otherwise a number in
     * [min, max], or NaN when it is not. */
    function readNumber(selector, min, max, whole) {
        var text = String($(selector).val()).trim();
        if (text === '') {
            return null;
        }
        var n = parseFloat(text);
        if (isNaN(n) || n < min || (max !== null && n > max) || (whole && Math.floor(n) !== n)) {
            return NaN;
        }
        return n;
    }

    function readRegion(id) {
        var out = {
            'enabled': $('#enabled_' + id).is(':checked'),
            'maxPerWeek': readNumber('#max_week_' + id, 0, null, false),
            'maxPerMonth': readNumber('#max_month_' + id, 0, null, false),
            'levels': {}
        };
        out.levels.bronze = {'bonusBoost': readNumber('#bronze_boost_' + id, 0, 100, false)};
        var bad = Number.isNaN(out.maxPerWeek) || Number.isNaN(out.maxPerMonth) || Number.isNaN(out.levels.bronze.bonusBoost);
        ['silver', 'gold'].forEach(function (level) {
            var l = {
                'minTrips': readNumber('#' + level + '_trips_' + id, 0, null, true),
                'minRating': readNumber('#' + level + '_rating_' + id, 0, 5, false),
                'maxCancellationRate': readNumber('#' + level + '_cancel_' + id, 0, 100, false),
                'bonusBoost': readNumber('#' + level + '_boost_' + id, 0, 100, false)
            };
            Object.keys(l).forEach(function (k) { if (Number.isNaN(l[k])) { bad = true; } });
            out.levels[level] = l;
        });
        if (bad) {
            return {'error': {!! json_encode(trans('lang.driver_bonus_settings_error_number')) !!}};
        }
        /* Gold is above Silver: it cannot ask for fewer trips. */
        var s = out.levels.silver.minTrips, g = out.levels.gold.minTrips;
        if (s !== null && g !== null && g < s) {
            return {'error': {!! json_encode(trans('lang.driver_bonus_settings_error_gold')) !!}};
        }
        return {'value': out};
    }

    $('.save-settings-btn').click(async function () {
        $('.err').html('');
        var regions = {};
        for (var i = 0; i < shownRegions.length; i++) {
            var id = shownRegions[i].id;
            var r = readRegion(id);
            if (r.error) {
                $('#error_' + id).html(r.error);
                document.querySelector('[data-region="' + id + '"]').scrollIntoView({block: 'center'});
                return false;
            }
            regions[id] = r.value;
        }
        jQuery("#overlay").show();
        /* Only the regions shown here are written: an admin limited to some
         * regions never touches the others. */
        var update = {'updatedAt': firebase.firestore.FieldValue.serverTimestamp()};
        Object.keys(regions).forEach(function (id) { update['regions.' + id] = regions[id]; });
        var doc = await settingsRef.get();
        if (doc.exists) {
            await settingsRef.update(update);
        } else {
            await settingsRef.set({'regions': regions, 'updatedAt': firebase.firestore.FieldValue.serverTimestamp()});
        }
        jQuery("#overlay").hide();
        window.location.href = '{{ route('driver-bonuses') }}';
    });
</script>
@endsection
