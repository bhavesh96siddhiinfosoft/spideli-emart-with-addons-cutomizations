@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.carrier_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                @if (\App\Http\Controllers\CarrierController::LIST_ENABLED)
                    <li class="breadcrumb-item"><a href="{!! route('carriers') !!}">{{ trans('lang.carrier_plural') }}</a></li>
                @else
                    @php
                        $backRoute = route('owners');
                        if (!empty($back) && $back === 'approved') {
                            $backRoute = route('owners.approved');
                        } elseif (!empty($back) && $back === 'pending') {
                            $backRoute = route('owners.pending');
                        }
                    @endphp
                    <li class="breadcrumb-item"><a href="{!! $backRoute !!}">{{ trans('lang.owner_plural') }}</a></li>
                @endif
                <li class="breadcrumb-item active">{{ trans('lang.carrier_edit') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="error_top" style="display:none"></div>

                <div id="owner_bound_note" class="alert alert-info" style="display:none;"></div>

                @include('carriers.partials.form')

                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary save-carrier-btn">
                        <i class="fa fa-save"></i> {{ trans('lang.save') }}
                    </button>
                    @php
                        $cancelRoute = route('carriers');
                        if (!\App\Http\Controllers\CarrierController::LIST_ENABLED || !empty($fromCompany)) {
                            if (!empty($back) && $back === 'approved') {
                                $cancelRoute = route('owners.approved');
                            } elseif (!empty($back) && $back === 'pending') {
                                $cancelRoute = route('owners.pending');
                            } elseif (!empty($back) && $back === 'owner_view' && !empty($fromCompany)) {
                                $cancelRoute = route('owners.view', $fromCompany) . '#carrier';
                            } else {
                                $cancelRoute = route('owners');
                            }
                        }
                    @endphp
                    <a href="{!! $cancelRoute !!}" class="btn btn-default">
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
    var carrierId = '{{ $id }}';
    var fromCompanyId = "<?php echo addslashes($fromCompany ?? ''); ?>";
    var backParam = "<?php echo addslashes($back ?? ''); ?>";
</script>
@include('carriers.partials.form_scripts')
<script type="text/javascript">
    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        database.collection('delivery_carriers').doc(carrierId).get().then(async function (snapshot) {
            var carrier = snapshot.data();
            if (!carrier) {
                jQuery("#data-table_processing").hide();
                if (fromCompanyId !== '') {
                    try {
                        await database.collection('users').doc(fromCompanyId).update({
                            carrierId: firebase.firestore.FieldValue.delete()
                        });
                    } catch (e) {
                        console.error('could not clear stale carrierId on owner', e);
                    }
                    var createUrl = '{{ route("carriers.create") }}?fromCompany=' + encodeURIComponent(fromCompanyId) + '&back=' + encodeURIComponent(backParam);
                    $('.error_top').show().html('<p>{{ trans("lang.carrier_not_found_for_owner") }} <a href="' + createUrl + '" class="btn btn-sm btn-primary ml-2">{{ trans("lang.carrier_create_for_owner") }}</a></p>');
                    $('.card-body form, .btm-btn').hide();
                    return;
                }
                var fallbackUrl = '{{ route("owners") }}';
                @if (\App\Http\Controllers\CarrierController::LIST_ENABLED)
                    fallbackUrl = '{{ route("carriers") }}';
                @endif
                window.location.href = fallbackUrl;
                return;
            }

            var ownerId = carrier.ownerId || fromCompanyId;
            if (ownerId) {
                currentCarrierOwnerId = ownerId;
                try {
                    var ownerSnap = await database.collection('users').doc(ownerId).get();
                    if (ownerSnap.exists) {
                        var ownerData = ownerSnap.data() || {};
                        var ownerName = ownerData.companyName || ((ownerData.firstName || '') + ' ' + (ownerData.lastName || '')).trim() || ownerId;
                        var ownerViewUrl = '{{ route("owners.view", ":id") }}'.replace(':id', encodeURIComponent(ownerId));
                        $('#owner_bound_note').html('<strong>{{ trans("lang.carrier_bound_to_owner") }}</strong> <a href="' + ownerViewUrl + '" class="alert-link font-weight-bold ml-1"><i class="mdi mdi-account-box"></i> ' + $('<div>').text(ownerName).html() + '</a>').show();
                        renderOwnerDetails(ownerData, ownerId);
                    }
                } catch (err) {
                    console.warn('could not load owner info for carrier', err);
                }
            }

            await initCarrierForm(carrier);
            jQuery("#data-table_processing").hide();
        });
    });

    $('.save-carrier-btn').click(function () {
        saveCarrier(carrierId, true);
    });
</script>
@endsection
