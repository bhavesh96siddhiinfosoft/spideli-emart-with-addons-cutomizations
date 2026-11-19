<script>
    // #112: "By the hour" / "By the day" packages. A daily package keeps the same fields as an hourly one,
    // read per day: baseFare = price of one day, includedDistance = km included per day, includedHours = 24,
    // extraMinuteFare = late return per minute. The booking multiplies them by the number of days.
    var SPD_RENTAL_TEXT = {
        hourly: {
            baseFare: [@json(trans('lang.package_basefare_price')), @json(trans('lang.enter_package_basefare_price'))],
            includedDistance: [@json(trans('lang.package_included_distance')), @json(trans('lang.enter_package_included_distance'))],
            extraMinuteFare: [@json(trans('lang.package_extra_minute_fare')), @json(trans('lang.enter_package_extra_minute_fare'))]
        },
        daily: {
            baseFare: [@json(trans('lang.rental_day_price')), @json(trans('lang.rental_day_price_help'))],
            includedDistance: [@json(trans('lang.rental_day_km')), @json(trans('lang.rental_day_km_help'))],
            extraMinuteFare: [@json(trans('lang.rental_late_minute_fare')), @json(trans('lang.rental_late_minute_fare_help'))]
        }
    };
    var spdRentalHourlyHours = '';

    function spideliRentalPackageType() {
        return $('#packageType').val() === 'daily' ? 'daily' : 'hourly';
    }

    function spideliRentalTypeUI() {
        var type = spideliRentalPackageType();
        $.each(SPD_RENTAL_TEXT[type], function (field, text) {
            var group = $('#' + field).closest('.form-group');
            group.find('label.control-label').first().text(text[0]);
            group.find('.form-text').first().text(text[1]);
        });
        var hoursGroup = $('#includedHours').closest('.form-group');
        if (type === 'daily') {
            if ($('#includedHours').val() !== '24') {
                spdRentalHourlyHours = $('#includedHours').val();
            }
            $('#includedHours').val('24');
            hoursGroup.hide();
            $('.spd-rental-daily-only').show();
        } else {
            if ($('#includedHours').val() === '24' && spdRentalHourlyHours !== '') {
                $('#includedHours').val(spdRentalHourlyHours);
            }
            hoursGroup.show();
            $('.spd-rental-daily-only').hide();
        }
    }

    $(document).on('change', '#packageType', spideliRentalTypeUI);

    function spdRentalIsWholeNumber(value) {
        return /^\d+$/.test(String(value).trim());
    }

    function spdRentalIsAmount(value) {
        var text = String(value).trim();
        return text !== '' && isFinite(text) && parseFloat(text) >= 0;
    }

    // Returns an error message, or '' when the daily fields are valid (always '' for an hourly package).
    function spideliRentalTypeCheck() {
        if (spideliRentalPackageType() !== 'daily') {
            return '';
        }
        var amounts = ['#baseFare', '#includedDistance', '#extraKmFare', '#extraMinuteFare'];
        for (var i = 0; i < amounts.length; i++) {
            if (!spdRentalIsAmount($(amounts[i]).val())) {
                return @json(trans('lang.rental_day_value_error'));
            }
        }
        var minDays = $('#minDays').val(), maxDays = $('#maxDays').val();
        if (!spdRentalIsWholeNumber(minDays) || !spdRentalIsWholeNumber(maxDays)) {
            return @json(trans('lang.rental_days_error'));
        }
        minDays = parseInt(minDays, 10);
        maxDays = parseInt(maxDays, 10);
        if (minDays < 1 || maxDays < minDays || maxDays > 365) {
            return @json(trans('lang.rental_days_error'));
        }
        return '';
    }

    // Fields to save with the package. forUpdate: remove the day limits when a package goes back to hourly.
    function spideliRentalTypeFields(forUpdate) {
        if (spideliRentalPackageType() === 'daily') {
            return {
                'packageType': 'daily',
                'includedHours': '24',
                'minDays': String(parseInt($('#minDays').val(), 10)),
                'maxDays': String(parseInt($('#maxDays').val(), 10))
            };
        }
        var fields = {'packageType': 'hourly'};
        if (forUpdate) {
            fields.minDays = firebase.firestore.FieldValue.delete();
            fields.maxDays = firebase.firestore.FieldValue.delete();
        }
        return fields;
    }

    // Edit page: show a saved package.
    function spideliRentalTypeLoad(packageData) {
        var type = (packageData && packageData.packageType === 'daily') ? 'daily' : 'hourly';
        $('#packageType').val(type);
        if (type === 'daily') {
            $('#minDays').val(packageData.minDays || '1');
            $('#maxDays').val(packageData.maxDays || '30');
        }
        spideliRentalTypeUI();
    }
</script>
