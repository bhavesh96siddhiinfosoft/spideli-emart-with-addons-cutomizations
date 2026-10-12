<script type="text/javascript">
    /* Point 41 - the region's parcel / mail pricing defaults.
     *
     * Saved as regions/{id}.parcelPricing; the keys below are the contract in
     * docs/app-spec-parcel-pricing.md and must not be renamed.
     *
     * `suggested` is what a NEW region starts with. It is never written unless
     * the admin saves the form - the app must not assume these numbers.
     *
     * The divisor is the one value where blank means something: no divisor,
     * size is not used at all. So blank (or 0) saves null. Every charge saves
     * a number, blank meaning 0. */
    var REGION_PARCEL_FIELDS = [
        {id: 'parcel_volumetric_divisor',      key: 'volumetricDivisor',     suggested: 5000, blankIsNull: true},
        {id: 'parcel_home_pickup_charge',      key: 'homePickupCharge',      suggested: 0},
        {id: 'parcel_point_dropoff_charge',    key: 'pointDropoffCharge',    suggested: 0},
        {id: 'parcel_home_delivery_charge',    key: 'homeDeliveryCharge',    suggested: 0},
        {id: 'parcel_point_collection_charge', key: 'pointCollectionCharge', suggested: 0},
        {id: 'parcel_intercity_fixed_tax',     key: 'intercityFixedTax',     suggested: 5000},
        {id: 'parcel_intercountry_fixed_tax',  key: 'intercountryFixedTax', suggested: 5000}
    ];

    /* Every charge here is in THIS REGION'S currency - the one chosen in the
     * Currency box. Shown beside each money field so "500" is never read as
     * the wrong currency. No conversion happens anywhere. */
    function updateRegionParcelCurrency() {
        var symbol = $('#currency_id option:selected').attr('data-symbol') || '';
        $('.region-parcel-currency').text(symbol ? '(' + symbol + ')' : '');
    }

    $(document).on('change', '#currency_id', updateRegionParcelCurrency);

    /* `pricing` is the region's saved parcelPricing map, or nothing.
     *
     * Nothing saved -> show the suggested values AND say they are not live yet.
     * Saved -> show exactly what is stored; a divisor stored as null shows
     * blank, which is what it means. */
    function fillRegionParcelPricing(pricing) {
        var saved = !!pricing && typeof pricing === 'object' && !Array.isArray(pricing);

        REGION_PARCEL_FIELDS.forEach(function (field) {
            var value;
            if (!saved) {
                value = field.suggested;
            } else if (pricing[field.key] === null || pricing[field.key] === undefined) {
                value = '';
            } else {
                value = pricing[field.key];
            }
            $('#' + field.id).val(value);
        });

        $('#region_parcel_pricing_unsaved').toggle(!saved);
        updateRegionParcelCurrency();
    }

    /* The map to save, or null when a value is wrong - in which case each
     * problem is shown beside its own field and the first is scrolled to. This
     * block sits below the fold, so an error shown elsewhere would read as the
     * Save button doing nothing. */
    function regionParcelPricingForSave() {
        var out = {};
        var firstError = null;

        REGION_PARCEL_FIELDS.forEach(function (field) {
            var errorBox = $('#error_' + field.id);
            errorBox.html('');

            var raw = $('#' + field.id).val();
            raw = (raw === null || raw === undefined) ? '' : String(raw).trim();

            if (raw === '') {
                out[field.key] = field.blankIsNull ? null : 0;
                return;
            }

            var number = Number(raw);
            if (!isFinite(number) || number < 0) {
                errorBox.html("{{ trans('lang.region_parcel_value_error') }}");
                firstError = firstError || field.id;
                return;
            }

            out[field.key] = (field.blankIsNull && number === 0) ? null : number;
        });

        if (firstError) {
            document.getElementById(firstError).scrollIntoView({behavior: 'smooth', block: 'center'});
            return null;
        }

        return out;
    }
</script>
