@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.region_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('region') !!}">{{ trans('lang.region_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.region_edit') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="error_top" style="display:none"></div>
                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">
                        <fieldset>
                            <legend><i class="mr-3 mdi mdi-earth"></i>{{ trans('lang.region_info') }}</legend>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.region_name') }}<span class="required-field"></span></label>
                                <div class="col-7">
                                    <input type="text" class="form-control" id="name">
                                    <div class="form-text text-muted">{{ trans('lang.region_name_help') }}</div>
                                    <div id="error_name" class="err"></div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.region_code') }}<span class="required-field"></span></label>
                                <div class="col-7">
                                    <input type="text" class="form-control" id="code">
                                    <div class="form-text text-muted">{{ trans('lang.region_code_help') }}</div>
                                    <div id="error_code" class="err"></div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.region_country_code') }}<span class="required-field"></span></label>
                                <div class="col-7">
                                    <div id="phone-box" class="country-box position-relative">
                                        <?php
                                        $countries = file_get_contents(public_path('countriesdata.json'));
                                        $countries = json_decode($countries);
                                        $countries = (array) $countries;
                                        $newcountries = array();
                                        $newcountriesjs = array();
                                        foreach ($countries as $keycountry => $valuecountry) {
                                            $newcountries[$valuecountry->code] = $valuecountry;
                                            $newcountriesjs[$valuecountry->countryName] = $valuecountry->code;
                                        }
                                        ?>
                                        <select name="country" id="country" class="form-control">
                                            @foreach($newcountries as $code => $country)
                                                <option value="{{ $country->countryName }}" data-code="{{ $code }}" data-phonecode="+{{ $country->phoneCode }}">
                                                    {{ $country->countryName }} +({{ $country->phoneCode }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text text-muted">{{ trans('lang.region_country_code_help') }}</div>
                                        <div id="error_country" class="err"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.region_currency') }}</label>
                                <div class="col-7">
                                    <select class="form-control" id="currency_id">
                                        <option value="">{{ trans('lang.region_currency') }}</option>
                                    </select>
                                    <div class="form-text text-muted">{{ trans('lang.region_currency_help') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.region_zones') }}</label>
                                <div class="col-7">
                                    <select id="zone_ids" class="form-control chosen-select" multiple="multiple"></select>
                                    <div class="form-text text-muted">{{ trans('lang.region_zones_help') }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <div class="form-check">
                                    <input type="checkbox" class="publish" id="publish">
                                    <label class="col-3 control-label" for="publish">{{ trans('lang.status') }}</label>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary save-region-btn">
                        <i class="fa fa-save"></i> {{ trans('lang.save') }}
                    </button>
                    <a href="{!! route('region') !!}" class="btn btn-default">
                        <i class="fa fa-undo"></i>{{ trans('lang.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();
    var regionId = '{{ $id }}';
    var originalZoneIds = [];

    var newcountriesjs = '<?php echo json_encode($newcountriesjs); ?>';
    var newcountriesjs = JSON.parse(newcountriesjs);

    function formatState(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/'); ?>/flags/120/";
        var $state = $(
            '<span><img src="' + baseUrl + '/' + newcountriesjs[state.element.value].toLowerCase() + '.png" class="img-flag" /> ' + state.text + '</span>'
        );
        return $state;
    }

    function formatState2(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/'); ?>/flags/120/"
        var $state = $(
            '<span><img class="img-flag" /> <span></span></span>'
        );
        $state.find("span").text(state.text);
        $state.find("img").attr("src", baseUrl + "/" + newcountriesjs[state.element.value].toLowerCase() + ".png");
        return $state;
    }

    $(document).ready(function () {
        jQuery("#data-table_processing").show();

        jQuery("#country").select2({
            templateResult: formatState,
            templateSelection: formatState2,
            placeholder: "{{ trans('lang.region_country_code') }}",
            allowClear: true
        });

        loadRegion();
    });

    async function loadRegion() {
        var snapshot = await database.collection('regions').doc(regionId).get();
        var region = snapshot.data();
        if (!region) {
            window.location.href = '{{ route('region') }}';
            return;
        }

        await Promise.all([loadCurrencies(), loadZones(region)]);

        $('#name').val(region.name || '');
        $('#code').val(region.code || '');

        /* The country list is keyed on country name. A stored name only counts
         * if it still matches an option — country names vary between sources
         * ("Ivory Coast" vs "Cote d'Ivoire"), so the ISO code is the reliable
         * fallback rather than leaving the field blank. */
        var selectedCountry = '';
        if (region.countryName && $('#country option[value="' + region.countryName + '"]').length) {
            selectedCountry = region.countryName;
        } else if (region.countryCode) {
            selectedCountry = $('#country option').filter(function () {
                return $(this).data('code') === region.countryCode;
            }).val();
        }
        $('#country').val(selectedCountry || null).trigger('change');

        $('#currency_id').val(region.currencyId || '');

        originalZoneIds = region.zoneIds || [];
        $('#zone_ids').val(originalZoneIds);
        $('#zone_ids').trigger('chosen:updated');

        $('#publish').prop('checked', region.publish === true);

        jQuery("#data-table_processing").hide();
    }

    async function loadCurrencies() {
        var snapshots = await database.collection('currencies').get();
        snapshots.docs.forEach(function (doc) {
            var currency = doc.data();
            $('#currency_id').append($('<option></option>').attr('value', currency.id).text(currency.name + ' (' + currency.symbol + ')'));
        });
    }

    /* Offer unclaimed zones plus the ones already attached to this region. */
    async function loadZones(region) {
        var assigned = region.zoneIds || [];
        var snapshots = await database.collection('zone').get();
        snapshots.docs.forEach(function (doc) {
            var zone = doc.data();
            if (!zone.regionId || zone.regionId === regionId || assigned.indexOf(zone.id) !== -1) {
                $('#zone_ids').append($('<option></option>').attr('value', zone.id).text(zone.name));
            }
        });
        $('#zone_ids').show().chosen({
            "placeholder_text": "{{ trans('lang.region_zones') }}"
        });
        $('#zone_ids').trigger('chosen:updated');
    }

    $('.save-region-btn').click(async function () {
        $('.err').html('');
        var name = $('#name').val().trim();
        var code = $('#code').val().trim().toUpperCase();
        var countryName = $('#country').val();
        var countryCode = $('#country').find(':selected').data('code');
        var currencyId = $('#currency_id').val();
        var zoneIds = $('#zone_ids').val() || [];
        var publish = $('#publish').is(':checked');

        if (name === '') {
            $('#error_name').html("{{ trans('lang.region_name_error') }}");
            return false;
        }
        if (code === '') {
            $('#error_code').html("{{ trans('lang.region_code_error') }}");
            return false;
        }
        if (!countryName || !countryCode) {
            $('#error_country').html("{{ trans('lang.region_country_code_error') }}");
            return false;
        }

        jQuery("#overlay").show();

        var duplicate = await database.collection('regions').where('code', '==', code).get();
        var clash = duplicate.docs.some(function (doc) {
            return doc.id !== regionId;
        });
        if (clash) {
            jQuery("#overlay").hide();
            $('#error_code').html("{{ trans('lang.region_code_duplicate_error') }}");
            return false;
        }

        await database.collection('regions').doc(regionId).update({
            'name': name,
            'code': code,
            'countryCode': countryCode,
            'countryName': countryName,
            'currencyId': currencyId || '',
            'zoneIds': zoneIds,
            'publish': publish,
            'updatedAt': firebase.firestore.FieldValue.serverTimestamp()
        });

        /* Keep the denormalised zone.regionId in step: clear the zones that were
         * removed from this region, stamp the ones that were added. */
        var removed = originalZoneIds.filter(function (zoneId) {
            return zoneIds.indexOf(zoneId) === -1;
        });
        await Promise.all(removed.map(function (zoneId) {
            return database.collection('zone').doc(zoneId).update({'regionId': ''});
        }));
        await Promise.all(zoneIds.map(function (zoneId) {
            return database.collection('zone').doc(zoneId).update({'regionId': regionId});
        }));

        window.location.href = '{{ route('region') }}';
    });
</script>
@endsection
