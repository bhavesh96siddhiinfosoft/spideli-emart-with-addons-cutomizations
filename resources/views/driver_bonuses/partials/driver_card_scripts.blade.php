{{--
    Point 104 - fills driver_card. Call loadDriverBonusCard(driverId, driver)
    once the page has the driver's users/{id} data.
--}}
<script>
    async function loadDriverBonusCard(driverId, driver) {
        var db = firebase.firestore();
        var LEVELS = {
            bronze: {!! json_encode(trans('lang.driver_level_bronze')) !!},
            silver: {!! json_encode(trans('lang.driver_level_silver')) !!},
            gold: {!! json_encode(trans('lang.driver_level_gold')) !!}
        };
        var STATUS = {
            paid: {!! json_encode(trans('lang.driver_bonus_status_paid')) !!},
            capped: {!! json_encode(trans('lang.driver_bonus_status_capped')) !!}
        };
        var REASON = {
            rating: {!! json_encode(trans('lang.driver_bonus_reason_rating')) !!},
            cancellation: {!! json_encode(trans('lang.driver_bonus_reason_cancellation')) !!},
            level: {!! json_encode(trans('lang.driver_bonus_reason_level')) !!},
            company_missing: {!! json_encode(trans('lang.driver_bonus_reason_company_missing')) !!}
        };
        function esc(v) {
            return $('<div></div>').text(v === undefined || v === null ? '' : v).html();
        }

        try {
            /* The level: Bronze until the bonus job sets another. */
            var level = (driver && LEVELS[driver.driverLevel]) ? driver.driverLevel : 'bronze';
            $('#driver_level_badge').removeClass('level-bronze level-silver level-gold').addClass('level-' + level).text(LEVELS[level]);
            if (driver && driver.driverLevelMonth) {
                $('#driver_level_since').text({!! json_encode(trans('lang.driver_level_from_month')) !!}.replace(':month', driver.driverLevelMonth));
            }

            /* Amounts in the driver's region's currency. */
            var currency = await getCurrencyForRegion((driver && driver.regionId) || '');
            var digits = currency && currency.decimal_degits ? parseInt(currency.decimal_degits, 10) : 0;
            var money = function (amount) {
                var text = Number(amount || 0).toFixed(digits);
                if (!currency || !currency.symbol) {
                    return text;
                }
                return currency.symbolAtRight ? text + currency.symbol : currency.symbol + text;
            };

            /* This week's and month's progress, refreshed daily by the job. */
            var progressDoc = await db.collection('driver_bonus_progress').doc(driverId).get();
            var rules = progressDoc.exists && Array.isArray(progressDoc.data().rules) ? progressDoc.data().rules : [];
            if (!rules.length) {
                $('#driver_bonus_progress').html(esc({!! json_encode(trans('lang.driver_bonus_no_progress')) !!}));
            } else {
                var html = '';
                rules.forEach(function (r) {
                    var byValue = r.measure === 'value';
                    var done = Number(r.done || 0);
                    var next = r.nextTarget === null || r.nextTarget === undefined ? null : Number(r.nextTarget);
                    var pct = next ? Math.min(100, Math.round(done / next * 100)) : 100;
                    var doneText = byValue ? money(done) : done + ' {{ trans('lang.driver_bonus_trips_short') }}';
                    var nextText = next === null
                        ? {!! json_encode(trans('lang.driver_bonus_top_reached')) !!}
                        : {!! json_encode(trans('lang.driver_bonus_next_step')) !!}
                            .replace(':target', byValue ? money(next) : next + ' {{ trans('lang.driver_bonus_trips_short') }}')
                            .replace(':bonus', money(r.nextBonus));
                    var reason = r.eligible === false && REASON[r.reason]
                        ? ' <span class="badge badge-danger">' + esc(REASON[r.reason]) + '</span>' : '';
                    html += '<div class="driver-bonus-progress-row">'
                        + '<div class="d-flex justify-content-between"><strong class="text-dark-2">' + esc(r.name) + '</strong><small>' + esc(r.periodKey) + '</small></div>'
                        + '<div class="progress"><div class="progress-bar" style="width:' + pct + '%"></div></div>'
                        + '<small class="text-dark-2">' + esc(doneText) + ' · ' + esc(nextText) + '</small>' + reason
                        + '</div>';
                });
                $('#driver_bonus_progress').removeClass('text-muted').html(html);
            }

            /* Bonuses received: what was paid, newest period first. */
            var resultsSnap = await db.collection('driver_bonus_results').where('driverId', '==', driverId).get();
            var paid = resultsSnap.docs.map(function (d) { return d.data(); })
                .filter(function (r) { return (r.status === 'paid' || r.status === 'capped') && Number(r.bonus) > 0; })
                .sort(function (a, b) { return String(b.periodKey || '').localeCompare(String(a.periodKey || '')); });
            if (!paid.length) {
                $('#driver_bonus_received').html(esc({!! json_encode(trans('lang.driver_bonus_none_received')) !!}));
                $('#driver_bonus_total').text('');
            } else {
                var total = paid.reduce(function (s, r) { return s + Number(r.bonus || 0); }, 0);
                $('#driver_bonus_total').text({!! json_encode(trans('lang.driver_bonus_total')) !!}.replace(':amount', money(total)));
                var rows = paid.slice(0, 10).map(function (r) {
                    return '<tr><td>' + esc(r.periodKey) + '</td><td>' + esc(r.ruleName) + '</td><td><strong>' + esc(money(r.bonus)) + '</strong></td><td>' + esc(STATUS[r.status] || r.status)
                        + (r.paidToCompany ? '<br><small class="text-muted">' + esc({!! json_encode(trans('lang.driver_bonus_paid_to_company')) !!}) + '</small>' : '') + '</td></tr>';
                }).join('');
                $('#driver_bonus_received').removeClass('text-muted').html(
                    '<table class="table table-bordered driver-bonus-received-table mb-0"><thead><tr>'
                    + '<th>{{ trans('lang.driver_bonus_period') }}</th><th>{{ trans('lang.driver_bonus_rule') }}</th>'
                    + '<th>{{ trans('lang.driver_bonus_amount') }}</th><th>{{ trans('lang.status') }}</th></tr></thead><tbody>'
                    + rows + '</tbody></table>'
                    + (paid.length > 10 ? '<small class="text-muted">' + esc({!! json_encode(trans('lang.driver_bonus_latest_ten')) !!}) + '</small>' : ''));
            }
        } catch (e) {
            console.error('driver bonus card', e);
            $('#driver_bonus_progress, #driver_bonus_received').html(esc({!! json_encode(trans('lang.driver_bonus_load_error')) !!}));
        }
    }
</script>
