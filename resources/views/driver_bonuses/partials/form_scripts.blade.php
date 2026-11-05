{{--
    Point 104 - shared behaviour of the bonus rule create / edit screens.
    `bonusRuleId` is declared by the including view.
--}}
<script>
    var database = firebase.firestore();
    var BONUS_SERVICES = ['cab', 'delivery', 'parcel', 'rental'];
    var bonusCurrency = null;

    async function loadBonusRegionOptions(selected) {
        var $select = $('#region_id');
        var regions = await getPublishedRegions();
        $select.empty().append($('<option></option>').attr('value', '').text('-'));
        regions.forEach(function (region) {
            $select.append($('<option></option>').attr('value', region.id).text(region.name));
        });
        /* A rule may hold a region later unpublished: kept, so saving does not clear it. */
        if (selected && !$select.find('option[value="' + selected + '"]').length) {
            $select.append($('<option></option>').attr('value', selected).text(selected));
        }
        $select.val(selected || '');
        $select.select2({width: '100%'});
        await showBonusCurrency();
    }

    /* Amounts are in the region's own currency - shown beside the bonus column. */
    async function showBonusCurrency() {
        var regionId = $('#region_id').val();
        bonusCurrency = regionId ? await getCurrencyForRegion(regionId) : null;
        $('.bonus-currency').text(bonusCurrency && bonusCurrency.symbol ? '(' + bonusCurrency.symbol + ')' : '');
        refreshTargetHead();
    }

    $(document).on('change', '#region_id', showBonusCurrency);
    $(document).on('change', '#measure', refreshTargetHead);

    function refreshTargetHead() {
        var byValue = $('#measure').val() === 'value';
        var symbol = bonusCurrency && bonusCurrency.symbol ? ' (' + bonusCurrency.symbol + ')' : '';
        $('#tier_target_head').text(byValue
            ? {!! json_encode(trans('lang.driver_bonus_target_value')) !!} + symbol
            : {!! json_encode(trans('lang.driver_bonus_target_trips')) !!});
        $('.tier-target').attr('step', byValue ? 'any' : '1');
    }

    function addTierRow(target, bonus) {
        var row = '<tr class="tier-row">'
            + '<td><input type="number" min="0" class="form-control tier-target"></td>'
            + '<td><input type="number" step="any" min="0" class="form-control tier-bonus"></td>'
            + '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-tier">{{ trans('lang.delete') }}</button></td>'
            + '</tr>';
        var $row = $(row);
        $row.find('.tier-target').val(target === undefined || target === null ? '' : target);
        $row.find('.tier-bonus').val(bonus === undefined || bonus === null ? '' : bonus);
        $('#tier_rows').append($row);
        refreshTargetHead();
    }

    $(document).on('click', '#add_tier', function () { addTierRow(); });
    $(document).on('click', '.remove-tier', function () { $(this).closest('tr').remove(); });

    function fillBonusRuleForm(rule) {
        $('#rule_name').val(rule.name || '');
        $('#period').val(rule.period === 'monthly' ? 'monthly' : 'weekly');
        $('#measure').val(rule.measure === 'value' ? 'value' : 'trips');
        BONUS_SERVICES.forEach(function (svc) {
            $('#svc_' + svc).prop('checked', Array.isArray(rule.services) && rule.services.indexOf(svc) >= 0);
        });
        $('#tier_rows').empty();
        (Array.isArray(rule.tiers) ? rule.tiers : []).forEach(function (t) { addTierRow(t.target, t.bonus); });
        if (!$('#tier_rows tr').length) {
            addTierRow();
        }
        $('#min_rating').val(rule.minRating === null || rule.minRating === undefined ? '' : rule.minRating);
        $('#max_cancellation').val(rule.maxCancellationRate === null || rule.maxCancellationRate === undefined ? '' : rule.maxCancellationRate);
        $('#min_level').val(rule.minLevel === 'silver' || rule.minLevel === 'gold' ? rule.minLevel : '');
        $('#publish').prop('checked', rule.publish !== false);
        refreshTargetHead();
    }

    function bonusError(id, message) {
        $('#' + id).html(message);
        window.scrollTo(0, 0);
        return null;
    }

    /* Returns the rule to save, or null after showing why it cannot be saved. */
    function readBonusRuleForm() {
        $('.err').html('');

        var name = $('#rule_name').val().trim();
        var regionId = $('#region_id').val();
        var measure = $('#measure').val() === 'value' ? 'value' : 'trips';
        var services = BONUS_SERVICES.filter(function (svc) { return $('#svc_' + svc).is(':checked'); });

        if (name === '') {
            return bonusError('error_name', {!! json_encode(trans('lang.driver_bonus_error_name')) !!});
        }
        if (!regionId) {
            return bonusError('error_region', {!! json_encode(trans('lang.driver_bonus_error_region')) !!});
        }
        if (!services.length) {
            return bonusError('error_services', {!! json_encode(trans('lang.driver_bonus_error_services')) !!});
        }

        /* Each step: a target above 0 (a whole number of trips) and a bonus
         * above 0. Saved in ascending order; no two steps on the same target. */
        var tiers = [];
        var bad = false;
        $('#tier_rows .tier-row').each(function () {
            var targetText = String($(this).find('.tier-target').val()).trim();
            var bonusText = String($(this).find('.tier-bonus').val()).trim();
            if (targetText === '' && bonusText === '') {
                return;
            }
            var target = parseFloat(targetText);
            var bonus = parseFloat(bonusText);
            if (isNaN(target) || target <= 0 || (measure === 'trips' && Math.floor(target) !== target) || isNaN(bonus) || bonus <= 0) {
                bad = true;
                return;
            }
            tiers.push({'target': target, 'bonus': bonus});
        });
        if (bad || !tiers.length) {
            return bonusError('error_tiers', {!! json_encode(trans('lang.driver_bonus_error_tiers')) !!});
        }
        tiers.sort(function (a, b) { return a.target - b.target; });
        for (var i = 1; i < tiers.length; i++) {
            if (tiers[i].target === tiers[i - 1].target) {
                return bonusError('error_tiers', {!! json_encode(trans('lang.driver_bonus_error_tiers_same')) !!});
            }
        }

        var minRating = null;
        var ratingText = String($('#min_rating').val()).trim();
        if (ratingText !== '') {
            minRating = parseFloat(ratingText);
            if (isNaN(minRating) || minRating < 0 || minRating > 5) {
                return bonusError('error_min_rating', {!! json_encode(trans('lang.driver_bonus_error_rating')) !!});
            }
        }

        var maxCancellation = null;
        var cancelText = String($('#max_cancellation').val()).trim();
        if (cancelText !== '') {
            maxCancellation = parseFloat(cancelText);
            if (isNaN(maxCancellation) || maxCancellation < 0 || maxCancellation > 100) {
                return bonusError('error_max_cancellation', {!! json_encode(trans('lang.driver_bonus_error_cancellation')) !!});
            }
        }

        var minLevel = $('#min_level').val();

        return {
            'name': name,
            'regionId': regionId,
            'services': services,
            'period': $('#period').val() === 'monthly' ? 'monthly' : 'weekly',
            'measure': measure,
            'tiers': tiers,
            'minRating': minRating,
            'maxCancellationRate': maxCancellation,
            'minLevel': (minLevel === 'silver' || minLevel === 'gold') ? minLevel : null,
            'publish': $('#publish').is(':checked')
        };
    }
</script>
