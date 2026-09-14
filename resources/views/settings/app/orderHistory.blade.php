@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.order_history_settings') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.order_history_settings') }}</li>
            </ol>
        </div>
    </div>

    <div class="card-body">

        <div class="alert alert-info">
            {{ trans('lang.order_history_intro') }}
        </div>

        <div class="row vendor_payout_create">
            <div class="vendor_payout_create-inner">
                <fieldset>
                    <legend><i class="mr-3 mdi mdi-history"></i>{{ trans('lang.order_history_settings') }}</legend>

                    <div class="form-check width-100">
                        <input type="checkbox" class="form-check-inline" id="limit_enabled">
                        <label class="col-6 control-label" for="limit_enabled">{{ trans('lang.order_history_limit_enabled') }}</label>
                        <div class="form-text text-muted pl-4">{{ trans('lang.order_history_limit_enabled_help') }}</div>
                    </div>

                    <div class="form-group row width-50" id="free_limit_row">
                        <label class="col-4 control-label">{{ trans('lang.order_history_free_limit') }}</label>
                        <div class="col-7">
                            <input type="number" min="0" class="form-control" id="free_limit">
                            <div class="form-text text-muted">{{ trans('lang.order_history_free_limit_help') }}</div>
                            <div id="error_free_limit" class="err"></div>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>

        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary save-order-history-btn">
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
    var ref = database.collection('settings').doc('OrderHistory');

    /* How many past orders a customer may see without a subscription.
     * Document 1 says five, the upgrade document says eight - which is why it
     * is a setting rather than a constant. Turning the limit off gives every
     * customer their full history for free. */
    var DEFAULT_FREE_LIMIT = 5;

    $(document).ready(function () {
        jQuery("#data-table_processing").show();

        ref.get().then(async function (snapshot) {
            var settings = snapshot.data();

            if (settings == undefined) {
                settings = {
                    'isLimitEnabled': true,
                    'freeOrderLimit': DEFAULT_FREE_LIMIT
                };
                await database.collection('settings').doc('OrderHistory').set(settings);
            }

            $('#limit_enabled').prop('checked', settings.isLimitEnabled !== false);
            $('#free_limit').val(settings.freeOrderLimit !== undefined ? settings.freeOrderLimit : DEFAULT_FREE_LIMIT);

            toggleLimitRow();
            jQuery("#data-table_processing").hide();
        });
    });

    function toggleLimitRow() {
        if ($('#limit_enabled').is(':checked')) {
            $('#free_limit_row').show();
        } else {
            $('#free_limit_row').hide();
        }
    }

    $(document).on('change', '#limit_enabled', toggleLimitRow);

    $('.save-order-history-btn').click(function () {
        $('.err').html('');

        var enabled = $('#limit_enabled').is(':checked');
        var raw = $('#free_limit').val();
        var limit = parseInt(raw);

        if (enabled && (raw === '' || isNaN(limit) || limit < 0)) {
            $('#error_free_limit').html("{{ trans('lang.order_history_free_limit_error') }}");
            return;
        }

        jQuery("#overlay").show();

        database.collection('settings').doc('OrderHistory').set({
            'isLimitEnabled': enabled,
            'freeOrderLimit': enabled ? limit : 0
        }, {merge: true}).then(function () {
            window.location.href = '{{ url("settings/app/orderHistory") }}';
        });
    });
</script>
@endsection
