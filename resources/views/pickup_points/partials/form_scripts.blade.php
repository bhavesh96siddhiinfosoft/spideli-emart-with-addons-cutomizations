{{--
    Shared behaviour for the pickup point create and edit screens.

    `pickupPointId` is declared by the including view: a fresh id on create, the
    record's id on edit.
--}}
<script>
    var database = firebase.firestore();

    /* Populated from `regions`, published only. A point belongs to one region,
     * so this is a single select rather than the multi-select partial used by
     * carriers and payment methods. */
    async function loadRegionOptions(selected) {
        var $select = $('#region_id');
        var regions = await getPublishedRegions();

        $select.empty().append($('<option></option>').attr('value', '').text('-'));

        regions.forEach(function (region) {
            $select.append($('<option></option>').attr('value', region.id).text(region.name));
        });

        /* A point may hold a region that was later unpublished or deleted.
         * Kept as an option so saving does not silently clear it. */
        if (selected && !$select.find('option[value="' + selected + '"]').length) {
            $select.append($('<option></option>').attr('value', selected).text(selected));
        }

        $select.val(selected || '');
        $select.select2({width: '100%'});
    }

    /* Flags and the dialling code, the same way the carrier form does it.
     * No `width` option here: the theme positions this picker absolutely
     * inside .phone-box and sets its width itself. */
    function initCountrySelector() {
        jQuery("#country_selector").select2({
            templateResult: pickupCountryOption,
            templateSelection: pickupCountrySelection,
            placeholder: "{{ trans('lang.select_country_code') }}",
            allowClear: true
        });
    }

    function pickupFlagUrl(state) {
        var baseUrl = "<?php echo URL::to('/'); ?>/flags/120/";

        return baseUrl + '/' + pickupCountryFlags[state.element.value].toLowerCase() + '.png';
    }

    function pickupCountryOption(state) {
        if (!state.id) {
            return state.text;
        }

        return $('<span><img src="' + pickupFlagUrl(state) + '" class="img-flag" /> ' + state.text + '</span>');
    }

    function pickupCountrySelection(state) {
        if (!state.id) {
            return state.text;
        }

        var $state = $('<span><img class="img-flag" /> <span></span></span>');
        $state.find("span").text(state.text);
        $state.find("img").attr("src", pickupFlagUrl(state));

        return $state;
    }

    /* Digits only, matching the carrier and driver forms. */
    $(document).on('keypress', '#phone', function (e) {
        var chr = String.fromCharCode(e.which);

        if ("0123456789".indexOf(chr) < 0) {
            return false;
        }
    });

    /* Returns null when the form is not fit to save, having already shown why.
     * Every check is here rather than split across the two screens. */
    function readPickupPointForm() {
        $('.err').html('');

        var name = $('#name').val().trim();
        var quarter = $('#quarter').val().trim();
        var town = $('#town').val().trim();
        var regionId = $('#region_id').val();
        var phone = $('#phone').val().trim();
        var latitude = $('#latitude').val();
        var longitude = $('#longitude').val();

        if (name === '') {
            $('#error_name').html("{{ trans('lang.pickup_point_name_error') }}");
            window.scrollTo(0, 0);
            return null;
        }

        if (quarter === '') {
            $('#error_quarter').html("{{ trans('lang.pickup_point_quarter_error') }}");
            window.scrollTo(0, 0);
            return null;
        }

        if (town === '') {
            $('#error_town').html("{{ trans('lang.pickup_point_town_error') }}");
            window.scrollTo(0, 0);
            return null;
        }

        if (!regionId) {
            $('#error_region').html("{{ trans('lang.pickup_point_region_error') }}");
            window.scrollTo(0, 0);
            return null;
        }

        if (phone === '') {
            $('#error_phone').html("{{ trans('lang.pickup_point_phone_error') }}");
            window.scrollTo(0, 0);
            return null;
        }

        /* A blank location is allowed - the client may not have coordinates for
         * every relay. A wrong one is not, because the app orders points by
         * distance and a bad value puts a Douala relay in the ocean. */
        var lat = null;
        var lng = null;

        if (latitude !== '' || longitude !== '') {
            lat = parseFloat(latitude);
            lng = parseFloat(longitude);

            if (isNaN(lat) || lat < -90 || lat > 90) {
                $('#error_latitude').html("{{ trans('lang.pickup_point_latitude_error') }}");
                window.scrollTo(0, 0);
                return null;
            }

            if (isNaN(lng) || lng < -180 || lng > 180) {
                $('#error_longitude').html("{{ trans('lang.pickup_point_longitude_error') }}");
                window.scrollTo(0, 0);
                return null;
            }
        }

        return {
            'name': name,
            'quarter': quarter,
            'town': town,
            'regionId': regionId,
            'countryCode': $('#country_selector').val() ? '+' + $('#country_selector').val() : '',
            'phone': phone,
            'location': (lat === null) ? null : {'latitude': lat, 'longitude': lng},
            'publish': $('#publish').is(':checked')
        };
    }

    /* Two points may share a name in different towns, but not within the same
     * region - that is a duplicate entry, not a second location. */
    async function isDuplicateName(name, regionId, ignoreId) {
        var snapshots = await database.collection('pickup_points').get();

        return snapshots.docs.some(function (doc) {
            var point = doc.data();

            return point.id !== ignoreId &&
                point.regionId === regionId &&
                (point.name || '').toLowerCase() === name.toLowerCase();
        });
    }
</script>
