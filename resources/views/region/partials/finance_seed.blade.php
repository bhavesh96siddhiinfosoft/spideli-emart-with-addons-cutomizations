{{-- Spideli Finance: when Finance is switched on for a region, the region's
     modules it has no settings for yet start from the app's current
     platform_settings values (FinanceController@seedRegion). Never overwrites. --}}
<script>
    var FINANCE_SEED_FAILED = {!! json_encode(trans('lang.region_finance_seed_failed')) !!};

    function financeSeedRegion(regionId) {
        return fetch('{{ url('region') }}/' + encodeURIComponent(regionId) + '/finance-seed', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        }).then(function (r) {
            return r.json().catch(function () { return { ok: false }; });
        }).then(function (res) {
            if (!res.ok) {
                // Not fatal: Finance settings has a "Copy app settings" button for this.
                alert(FINANCE_SEED_FAILED + (res.message ? '\n' + res.message : ''));
            }
            return res;
        }).catch(function () {
            alert(FINANCE_SEED_FAILED);
        });
    }
</script>
