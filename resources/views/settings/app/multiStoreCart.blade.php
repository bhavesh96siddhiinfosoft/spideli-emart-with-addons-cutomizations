@extends('layouts.app')
{{-- #115: Settings > Multi-store cart. A cart with products from several stores of the same service and region; one
     setting for every region: settings/multiStoreCart = { enabled: bool, maxStores: 2-10, updatedAt }. --}}
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.ms_cart_title') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.ms_cart_title') }}</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div class="error_top ms-cart-error" style="display:none"></div>
            <div class="success_top ms-cart-saved" style="display:none"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.ms_cart_title') }}</legend>
                        <div class="form-check width-100">
                            <input type="checkbox" class="form-check-inline" id="ms_cart_enabled">
                            <label class="col-8 control-label" for="ms_cart_enabled">{{ trans('lang.ms_cart_enable') }}</label>
                            <div class="form-text text-muted pl-4">{{ trans('lang.ms_cart_enable_help') }}</div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.ms_cart_max') }}</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="ms_cart_max" min="2" max="10" step="1">
                                <div class="form-text text-muted">{{ trans('lang.ms_cart_max_help') }}</div>
                            </div>
                        </div>
                        <div class="form-text text-muted pl-4 ms-cart-bound" style="display:none;">{{ trans('lang.ms_cart_region_bound') }}</div>
                    </fieldset>
                </div>
            </div>
            <div class="form-group col-12 text-center">
                <button type="button" class="btn btn-primary" id="ms_cart_save"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
                <a href="{{ url('/dashboard') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();

    /* Off (or missing) = the cart keeps one store, as before. The website and the app read it. One setting for every
     * region, so an admin bound to regions sees it but cannot change it (as the global delivery charge). */
    var MS_CART_DEFAULT_MAX = 3;

    function spideliMsCartLoad() {
        database.collection('settings').doc('multiStoreCart').get().then(function (snap) {
            var d = snap.exists ? (snap.data() || {}) : {};
            var max = parseInt(d.maxStores, 10);
            $('#ms_cart_enabled').prop('checked', d.enabled === true);
            $('#ms_cart_max').val(max >= 2 && max <= 10 ? max : MS_CART_DEFAULT_MAX);
            if (isRegionBound()) {
                $('#ms_cart_enabled, #ms_cart_max, #ms_cart_save').prop('disabled', true);
                $('.ms-cart-bound').show();
            }
        }).catch(function (e) {
            console.error('[multi-store cart]', e);
        });
    }

    $('#ms_cart_save').on('click', function () {
        $('.ms-cart-error, .ms-cart-saved').hide();
        if (isRegionBound()) {
            return;
        }
        var max = Number($('#ms_cart_max').val());
        if (!Number.isInteger(max) || max < 2 || max > 10) {
            $('.ms-cart-error').html('').append($('<p>').text(@json(trans('lang.ms_cart_max_error')))).show();
            return;
        }
        var $btn = $(this).prop('disabled', true);
        database.collection('settings').doc('multiStoreCart').set({
            enabled: $('#ms_cart_enabled').is(':checked'),
            maxStores: max,
            updatedAt: firebase.firestore.FieldValue.serverTimestamp()
        }).then(function () {
            $('.ms-cart-saved').html('').append($('<p>').text(@json(trans('lang.ms_cart_saved')))).show();
        }).catch(function (e) {
            console.error('[multi-store cart]', e);
            $('.ms-cart-error').html('').append($('<p>').text(@json(trans('lang.ms_cart_save_error')))).show();
        }).finally(function () {
            $btn.prop('disabled', false);
        });
    });

    $(document).ready(spideliMsCartLoad);
</script>
@endsection
