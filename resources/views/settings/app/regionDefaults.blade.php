@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.region_defaults_settings') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.region_defaults_settings') }}</li>
            </ol>
        </div>
    </div>

    <div class="card-body">

        <div class="alert alert-info">
            {{ trans('lang.region_defaults_intro') }}
        </div>

        <div class="row vendor_payout_create">
            <div class="vendor_payout_create-inner">
                <fieldset>
                    <legend><i class="mr-3 mdi mdi-earth"></i>{{ trans('lang.region_defaults_settings') }}</legend>

                    <div class="form-group row width-50">
                        <label class="col-4 control-label">{{ trans('lang.region_defaults_default_region') }}</label>
                        <div class="col-7">
                            <select id="default_region_id" class="form-control">
                                <option value="">{{ trans('lang.region_defaults_none') }}</option>
                            </select>
                            <div class="form-text text-muted">{{ trans('lang.region_defaults_default_region_help') }}</div>
                            <div id="error_default_region" class="err"></div>
                        </div>
                    </div>

                    <div class="form-group row width-100">
                        <div class="form-text text-muted">
                            <i class="mdi mdi-information-outline mr-1"></i>{{ trans('lang.region_defaults_unpublished_help') }}
                            <a href="{{ route('region') }}">{{ trans('lang.region') }}</a>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>

        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary save-region-defaults-btn">
                <i class="fa fa-save"></i> {{ trans('lang.save') }}
            </button>
            <a href="{{ url('/dashboard') }}" class="btn btn-default">
                <i class="fa fa-undo"></i>{{ trans('lang.cancel') }}
            </a>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();
    var ref = database.collection('settings').doc('RegionDefaults');

    /* The fallback region for a visitor the customer web panel cannot place.
     *
     * That panel resolves a region from the delivery zone, then from the
     * country. A visitor in a country with no region configured resolves to
     * nothing, which is a safe outcome - prices fall back to the globally
     * active currency and nothing is region-filtered - but it is a poor one,
     * because there is no region picker for them to correct it with.
     *
     * Leaving this empty keeps exactly that behaviour, so the setting is
     * opt-in and changes nothing until it is filled in. */
    var regionsList = [];

    $(document).ready(function () {
        jQuery("#data-table_processing").show();

        database.collection('regions').get().then(async function (snapshots) {

            regionsList = snapshots.docs.map(function (doc) {
                var data = doc.data();
                data.id = data.id || doc.id;
                return data;
            }).filter(function (region) {
                /* Only a published region can be a default: the web panel
                 * refuses an unpublished one, so offering it here would be
                 * offering a setting that silently does nothing. */
                return region.publish !== false;
            });

            regionsList.sort(function (a, b) {
                return (a.name || '').localeCompare(b.name || '');
            });

            var options = '';
            regionsList.forEach(function (region) {
                options += '<option value="' + region.id + '">' + region.name +
                    (region.code ? ' (' + region.code + ')' : '') + '</option>';
            });
            $('#default_region_id').append(options);

            var snapshot = await ref.get();
            var settings = snapshot.exists ? snapshot.data() : null;

            if (settings && settings.defaultRegionId) {
                /* A region that has since been deleted or unpublished leaves
                 * the picker on "none" rather than showing a stale name. */
                if ($('#default_region_id option[value="' + settings.defaultRegionId + '"]').length) {
                    $('#default_region_id').val(settings.defaultRegionId);
                }
            }

            jQuery("#data-table_processing").hide();
        });
    });

    $('.save-region-defaults-btn').click(function () {
        $('.err').html('');

        var defaultRegionId = $('#default_region_id').val() || '';

        jQuery("#overlay").show();

        /* merge, so this screen never overwrites anything else that comes to
         * live in the same document. */
        database.collection('settings').doc('RegionDefaults').set({
            'defaultRegionId': defaultRegionId
        }, {merge: true}).then(function () {
            window.location.href = '{{ url("settings/app/regionDefaults") }}';
        });
    });
</script>
@endsection
