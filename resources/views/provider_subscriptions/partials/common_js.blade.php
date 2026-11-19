{{-- #114: helpers shared by the three provider subscription screens. --}}
<script type="text/javascript">
    var database = firebase.firestore();
    var providerNames = {};
    var customerNames = {};
    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;

    // Every value shown comes from Firestore (plan titles are typed by providers): always escaped.
    function spdEsc(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    async function spdLoadCurrency() {
        var currency = await regionCurrencyRef().limit(1).get();
        if (currency.docs.length > 0) {
            var currencyData = currency.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        }
    }

    function spdPersonName(data) {
        return ((data.firstName || '') + ' ' + (data.lastName || '')).trim();
    }

    // Reads only the users the rows refer to (providers and customers live in users/{id}).
    async function spdLoadNames(ids, target) {
        var unique = ids.filter(function (id, i) { return id && ids.indexOf(id) === i && target[id] === undefined; });
        await Promise.all(unique.map(async function (id) {
            var doc = await database.collection('users').doc(id).get();
            target[id] = doc.exists ? spdPersonName(doc.data()) : '';
        }));
    }

    // A record belongs to the provider's region (a single regionId).
    function inActiveRegion(record) {
        return regionIdInScope(record.regionId);
    }

    function formatPrice(value) {
        var amount = (parseFloat(value || 0) || 0).toFixed(decimal_degits);
        return currencyAtRight ? amount + '' + currentCurrency : currentCurrency + '' + amount;
    }

    function spdPeriod(expiryDay) {
        return String(expiryDay) === '365' ?
            @json(trans('lang.provider_plan_period_annual')) :
            @json(trans('lang.provider_plan_period_monthly'));
    }

    function toDate(value) {
        if (!value) {
            return null;
        }
        if (typeof value.toDate === 'function') {
            return value.toDate();
        }
        var parsed = new Date(value);
        return isNaN(parsed.getTime()) ? null : parsed;
    }

    function toTime(value) {
        var date = toDate(value);
        return date == null ? 0 : date.getTime();
    }

    function formatDate(value) {
        var date = toDate(value);
        if (date == null) {
            return '';
        }
        return ('0' + date.getDate()).slice(-2) + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + date.getFullYear();
    }
</script>
