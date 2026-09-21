@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.pickup_point_edit') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('pickup-points') !!}">{{ trans('lang.pickup_point_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.pickup_point_edit') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            @include('pickup_points.partials.form')
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary save-point-btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
            <a href="{!! route('pickup-points') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('pickup_points.partials.form_scripts')
<script>
    var pickupPointId = '{{ $id }}';

    $(document).ready(async function () {
        jQuery("#overlay").show();

        initCountrySelector();

        var doc = await database.collection('pickup_points').doc(pickupPointId).get();

        if (!doc.exists) {
            window.location.href = '{{ route('pickup-points') }}';
            return;
        }

        var point = doc.data();

        /* A point outside the region the admin is working in must not be
         * reachable by editing the url. */
        if (!isInActiveRegion(point)) {
            window.location.href = '{{ route('pickup-points') }}';
            return;
        }

        $('#name').val(point.name || '');
        $('#quarter').val(point.quarter || '');
        $('#town').val(point.town || '');
        $('#phone').val(point.phone || '');
        $('#publish').prop('checked', point.publish === true);

        if (point.location) {
            $('#latitude').val(point.location.latitude);
            $('#longitude').val(point.location.longitude);
        }

        if (point.countryCode) {
            $('#country_selector').val(String(point.countryCode).replace('+', '').trim()).trigger('change');
        }

        await loadRegionOptions(point.regionId || '');

        jQuery("#overlay").hide();
    });

    $('.save-point-btn').click(async function () {
        var point = readPickupPointForm();

        if (point === null) {
            return false;
        }

        jQuery("#overlay").show();

        if (await isDuplicateName(point.name, point.regionId, pickupPointId)) {
            jQuery("#overlay").hide();
            $('#error_name').html("{{ trans('lang.pickup_point_name_duplicate') }}");
            window.scrollTo(0, 0);
            return false;
        }

        point.updatedAt = firebase.firestore.FieldValue.serverTimestamp();

        await database.collection('pickup_points').doc(pickupPointId).update(point);

        window.location.href = '{{ route('pickup-points') }}';
    });
</script>
@endsection
