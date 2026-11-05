<script type="text/javascript">
    /* Point 103 - the region's traffic pricing (see region/partials/traffic_pricing).
     *
     * fillRegionTrafficPricing(saved) fills the section; regionTrafficPricingForSave()
     * returns the map to save, or null when a value is wrong (the error is shown
     * under the section and scrolled to). A region never saved has no
     * trafficPricing: it is off, and every service is ticked ready for when the
     * admin switches it on. */
    var TRAFFIC_SERVICES = ['cab', 'parcel', 'delivery'];
    var TRAFFIC_DAY_NAMES = {!! json_encode(explode(',', trans('lang.traffic_days_short'))) !!};
    var trafficZones = [];
    var trafficRowSeq = 0;   // unique ids: the theme draws each checkbox on its label (for=)

    function trafficEscape(value) {
        return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
    }

    function trafficHourRow(hour) {
        hour = hour || { days: [1, 2, 3, 4, 5], start: '', end: '', multiplier: '' };
        var row = ++trafficRowSeq;
        var days = TRAFFIC_DAY_NAMES.map(function (name, i) {
            var day = i + 1;
            var id = 'traffic_day_' + row + '_' + day;
            return '<span class="d-inline-block mr-3"><input type="checkbox" class="traffic-day" id="' + id + '" value="' + day + '"'
                + ((hour.days || []).indexOf(day) !== -1 ? ' checked' : '') + '><label for="' + id + '">' + trafficEscape(name) + '</label></span>';
        }).join('');
        return '<div class="traffic-hour border rounded p-2 mb-2">'
            + '<div class="mb-2">' + days + '</div>'
            + '<div class="d-flex flex-wrap align-items-center">'
            + '<label class="mb-0 mr-1">{{ trans('lang.traffic_from') }}</label><input type="time" class="form-control form-control-sm w-auto mr-3 traffic-start" value="' + trafficEscape(hour.start) + '">'
            + '<label class="mb-0 mr-1">{{ trans('lang.traffic_to') }}</label><input type="time" class="form-control form-control-sm w-auto mr-3 traffic-end" value="' + trafficEscape(hour.end) + '">'
            + '<label class="mb-0 mr-1">{{ trans('lang.traffic_multiplier') }}</label><input type="number" step="any" min="1" class="form-control form-control-sm mr-3 traffic-multiplier" style="width:90px" placeholder="1.2" value="' + trafficEscape(hour.multiplier) + '">'
            + '<button type="button" class="btn btn-sm btn-outline-danger traffic-remove">{{ trans('lang.delete') }}</button>'
            + '</div></div>';
    }

    function trafficZoneRow(zone) {
        zone = zone || { zoneId: '', multiplier: '' };
        var options = '<option value="">{{ trans('lang.traffic_choose_zone') }}</option>' + trafficZones.map(function (z) {
            return '<option value="' + trafficEscape(z.id) + '"' + (z.id === zone.zoneId ? ' selected' : '') + '>' + trafficEscape(z.name || z.id) + '</option>';
        }).join('');
        return '<div class="traffic-zone d-flex flex-wrap align-items-center border rounded p-2 mb-2">'
            + '<select class="form-control form-control-sm w-auto mr-3 traffic-zone-id">' + options + '</select>'
            + '<label class="mb-0 mr-1">{{ trans('lang.traffic_multiplier') }}</label><input type="number" step="any" min="1" class="form-control form-control-sm mr-3 traffic-multiplier" style="width:90px" placeholder="1.25" value="' + trafficEscape(zone.multiplier) + '">'
            + '<button type="button" class="btn btn-sm btn-outline-danger traffic-remove">{{ trans('lang.delete') }}</button>'
            + '</div>';
    }

    async function fillRegionTrafficPricing(saved) {
        var snapshots = await database.collection('zone').get();
        trafficZones = snapshots.docs.map(function (doc) { return Object.assign({ id: doc.id }, doc.data()); })
            .sort(function (a, b) { return (a.name || '').localeCompare(b.name || ''); });

        var tp = saved && typeof saved === 'object' && !Array.isArray(saved) ? saved : null;
        $('#traffic_enabled').prop('checked', !!(tp && tp.enabled === true));
        var services = tp && Array.isArray(tp.services) ? tp.services : TRAFFIC_SERVICES;
        $('.traffic-service').each(function () {
            $(this).prop('checked', services.indexOf($(this).val()) !== -1);
        });
        $('#traffic_cap_percent').val(tp && tp.capPercent !== null && tp.capPercent !== undefined ? tp.capPercent : '');
        $('#traffic_busy_hours').html((tp && Array.isArray(tp.busyHours) ? tp.busyHours : []).map(trafficHourRow).join(''));
        $('#traffic_busy_zones').html((tp && Array.isArray(tp.busyZones) ? tp.busyZones : []).map(trafficZoneRow).join(''));
    }

    $(document).on('click', '#traffic_add_hour', function () {
        $('#traffic_busy_hours').append(trafficHourRow(null));
    });
    $(document).on('click', '#traffic_add_zone', function () {
        $('#traffic_busy_zones').append(trafficZoneRow(null));
    });
    $(document).on('click', '#region_traffic_pricing .traffic-remove', function () {
        $(this).closest('.traffic-hour, .traffic-zone').remove();
    });

    function regionTrafficPricingForSave() {
        var errorBox = $('#error_traffic');
        errorBox.html('');
        var fail = function (message) {
            errorBox.html(message);
            document.getElementById('region_traffic_pricing').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return null;
        };
        var multiplierOk = function (m) { return isFinite(m) && m >= 1 && m <= 10; };
        var timeOk = function (t) { return /^([01]\d|2[0-3]):[0-5]\d$/.test(t); };

        var enabled = $('#traffic_enabled').is(':checked');
        var services = $('.traffic-service:checked').map(function () { return $(this).val(); }).get();
        if (enabled && services.length === 0) {
            return fail("{{ trans('lang.traffic_error_services') }}");
        }

        var capRaw = String($('#traffic_cap_percent').val() || '').trim();
        var capPercent = null;
        if (capRaw !== '') {
            capPercent = Number(capRaw);
            if (!isFinite(capPercent) || capPercent < 0 || capPercent > 1000) {
                return fail("{{ trans('lang.traffic_error_cap') }}");
            }
        }

        var busyHours = [];
        var hourError = null;
        $('#traffic_busy_hours .traffic-hour').each(function () {
            var days = $(this).find('.traffic-day:checked').map(function () { return parseInt($(this).val(), 10); }).get();
            var start = $(this).find('.traffic-start').val();
            var end = $(this).find('.traffic-end').val();
            var multiplier = Number($(this).find('.traffic-multiplier').val());
            if (!days.length || !timeOk(start) || !timeOk(end) || start === end || !multiplierOk(multiplier)) {
                hourError = hourError || "{{ trans('lang.traffic_error_hour') }}";
                return;
            }
            busyHours.push({ days: days, start: start, end: end, multiplier: multiplier });
        });
        if (hourError) {
            return fail(hourError);
        }

        var busyZones = [];
        var seen = {};
        var zoneError = null;
        $('#traffic_busy_zones .traffic-zone').each(function () {
            var zoneId = $(this).find('.traffic-zone-id').val();
            var multiplier = Number($(this).find('.traffic-multiplier').val());
            if (!zoneId || seen[zoneId] || !multiplierOk(multiplier)) {
                zoneError = zoneError || "{{ trans('lang.traffic_error_zone') }}";
                return;
            }
            seen[zoneId] = true;
            busyZones.push({ zoneId: zoneId, multiplier: multiplier });
        });
        if (zoneError) {
            return fail(zoneError);
        }

        return { enabled: enabled, services: services, capPercent: capPercent, busyHours: busyHours, busyZones: busyZones };
    }
</script>
