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
                <li class="breadcrumb-item active">{{ trans('lang.carrier_create') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="error_top" style="display:none"></div>

                {{-- Shown only when the screen was opened from a registered
                     company, so it is clear why the fields are already filled
                     and what still has to be decided. --}}
                <div id="company_source_note" class="alert alert-info" style="display:none;">
                    {{ trans('lang.carrier_from_company_note') }}
                </div>

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
                            } elseif (!empty($back) && $back === 'owner_view') {
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
    var carrierId = database.collection('tmp').doc().id;

    /* Set when this screen was opened from a registered company on the carrier
     * list - bug report 02 point 19. Empty for an ordinary new carrier. */
    var fromCompanyId = "<?php echo addslashes($fromCompany); ?>";
    var backParam = "<?php echo addslashes($back ?? ''); ?>";
</script>
@include('carriers.partials.form_scripts')
<script type="text/javascript">
    /* A company driver's record, mapped into the shape initCarrierForm()
     * already understands, so the existing form fills itself in and no field
     * is duplicated here.
     *
     * The rate card is left empty on purpose - see CarrierController. */
    function carrierFromCompanyDriver(driver) {
        return {
            name: driver.companyName || ((driver.firstName || '') + ' ' + (driver.lastName || '')).trim(),
            code: '',
            phone: driver.phoneNumber || '',
            email: driver.email || '',
            countryCode: driver.countryCode || '',
            operatingLicence: driver.operatingLicence || '',
            commercialRegister: driver.commercialRegister || '',
            uniqueIdNumber: driver.uniqueIdNumber || '',
            operatingLicenceFile: driver.operatingLicenceFile || '',
            commercialRegisterFile: driver.commercialRegisterFile || '',
            uniqueIdNumberFile: driver.uniqueIdNumberFile || '',
            photo: driver.profilePictureURL || '',
            regionIds: driver.regionId ? [driver.regionId] : [],
            /* Not verified by arriving. An administrator decides. */
            isVerified: false,
            publish: false
        };
    }

    $(document).ready(async function () {
        jQuery("#data-table_processing").show();

        var prefill = null;

        if (fromCompanyId !== '') {
            try {
                var snapshot = await database.collection('users').doc(fromCompanyId).get();

                if (snapshot.exists) {
                    var userData = snapshot.data() || {};
                    if (userData.isOwner !== true) {
                        console.warn('user is not an owner; starting an empty carrier', fromCompanyId);
                        fromCompanyId = '';
                    } else if (userData.carrierId && String(userData.carrierId).trim() !== '') {
                        /* Owner already has carrierId -> redirect to edit screen */
                        var editUrl = '{{ route("carriers.edit", ":id") }}'.replace(':id', encodeURIComponent(userData.carrierId)) + '?fromCompany=' + encodeURIComponent(fromCompanyId) + '&back=' + encodeURIComponent(backParam);
                        window.location.replace(editUrl);
                        return;
                    } else {
                        /* Check if back-link failed: carrier exists with ownerId == fromCompanyId */
                        var carrierSnap = await database.collection('delivery_carriers').where('ownerId', '==', fromCompanyId).limit(1).get();
                        if (!carrierSnap.empty) {
                            var existingCarrierId = carrierSnap.docs[0].id;
                            try {
                                await database.collection('users').doc(fromCompanyId).update({
                                    carrierId: existingCarrierId
                                });
                            } catch (e) {
                                console.error('Failed to repair owner carrierId', e);
                            }
                            var editUrl = '{{ route("carriers.edit", ":id") }}'.replace(':id', encodeURIComponent(existingCarrierId)) + '?fromCompany=' + encodeURIComponent(fromCompanyId) + '&back=' + encodeURIComponent(backParam);
                            window.location.replace(editUrl);
                            return;
                        }

                        prefill = carrierFromCompanyDriver(userData);
                        $('#company_source_note').show();
                        renderOwnerDetails(userData, fromCompanyId);
                    }
                } else {
                    /* The link was built from a company that has since gone.
                     * An empty form is still usable, so say so and carry on. */
                    console.warn('company driver not found; starting an empty carrier', fromCompanyId);
                    fromCompanyId = '';
                }
            } catch (err) {
                console.error('company driver could not be read', err);
                fromCompanyId = '';
            }
        }

        await initCarrierForm(prefill);
        jQuery("#data-table_processing").hide();
    });

    $('.save-carrier-btn').click(function () {
        saveCarrier(carrierId, false, fromCompanyId);
    });
</script>
@endsection
