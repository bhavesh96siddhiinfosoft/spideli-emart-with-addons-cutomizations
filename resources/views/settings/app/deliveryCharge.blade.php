@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.deliveryCharge')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.deliveryCharge')}}</li>
                </ol>
            </div>
        </div>
        <div class="card-body">

            <div class="alert alert-info" id="scope_notice" style="display:none;"></div>

            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.deliveryCharge')}}</legend>

                        <div class="form-check width-100" id="use_global_row" style="display:none;">
                            <input type="checkbox" class="form-check-inline" id="use_global">
                            <label class="col-5 control-label" for="use_global">{{ trans('lang.delivery_charge_use_global')}}</label>
                            <div class="form-text text-muted pl-4">{{ trans('lang.delivery_charge_use_global_help')}}</div>
                        </div>

                        <div class="form-check width-100">
                            <input type="checkbox" class="form-check-inline" id="vendor_can_modify">
                            <label class="col-5 control-label" for="vendor_can_modify">{{ trans('lang.vendor_can_modify')}}</label>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.delivery_charges_per')}} <span class="distance-type"></span></label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="delivery_charges_per_km">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.minimum_delivery_charges')}}</label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="minimum_delivery_charges">
                            </div>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.minimum_delivery_charges_within')}} <span class="distance-type"></span></label>
                            <div class="col-7">
                                <input type="number" class="form-control" id="minimum_delivery_charges_within_km">
                            </div>
                        </div>
                        <div class="form-text text-muted pl-4">
                            <strong>{{ trans('lang.delivery_charges_note')}}</strong><br>
                            - <b>{{ trans('lang.vendor_can_modify')}}</b> {{ trans('lang.vendor_can_modify_help')}}<br>
                            - <b>{{ trans('lang.delivery_charges_per')}}</b> {{ trans('lang.delivery_charges_per_help')}}<br>
                            - <b>{{ trans('lang.minimum_delivery_charges')}}</b> {{ trans('lang.minimum_delivery_charges_help')}}<br>
                            - <b>{{ trans('lang.minimum_delivery_charges_within')}}</b> {{ trans('lang.minimum_delivery_charges_within_help')}}
                        </div>
                    </fieldset>
                </div>
            </div>

            <div class="table-responsive m-t-10" id="region_overview_box" style="display:none;">
                <h4 class="mb-2">{{ trans('lang.delivery_charge_by_region')}}</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('lang.region_name')}}</th>
                            <th>{{ trans('lang.delivery_charges_per')}}</th>
                            <th>{{ trans('lang.minimum_delivery_charges')}}</th>
                            <th>{{ trans('lang.minimum_delivery_charges_within')}}</th>
                            <th>{{ trans('lang.status')}}</th>
                        </tr>
                    </thead>
                    <tbody id="region_overview_rows"></tbody>
                </table>
            </div>

            <div class="form-group col-12 text-center">
                <button type="button" class="btn btn-primary edit-setting-btn"><i
                            class="fa fa-save"></i> {{trans('lang.save')}}</button>
                <a href="{{url('/dashboard')}}" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
            </div>
        </div>
@endsection
@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();
    var ref_deliverycharge = database.collection('settings').doc("DeliveryCharge");
    var driverNearBy = database.collection('settings').doc("DriverNearBy");

    /* The global figures stay at the top level of settings/DeliveryCharge, and
     * per-region figures live in a `regions` map keyed by region id:
     *
     *   {
     *     vendor_can_modify: false,          <- global default, unchanged
     *     delivery_charges_per_km: 10,
     *     minimum_delivery_charges: 20,
     *     minimum_delivery_charges_within_km: 2,
     *     regions: {
     *       "<regionId>": { ...the same four fields... }
     *     }
     *   }
     *
     * A region with no entry falls back to the global figures, so the apps keep
     * working unchanged until they are taught to read the map. */
    var deliveryChargeSettings = {};

    $(document).ready(function () {
        jQuery("#data-table_processing").show();

        ref_deliverycharge.get().then(async function (snapshots_charge) {
            deliveryChargeSettings = snapshots_charge.data();

            if (deliveryChargeSettings == undefined) {
                deliveryChargeSettings = {
                    'vendor_can_modify': '',
                    'delivery_charges_per_km': '',
                    'minimum_delivery_charges': '',
                    'minimum_delivery_charges_within_km': ''
                };
                await database.collection('settings').doc('DeliveryCharge').set(deliveryChargeSettings);
            }

            renderForm();
            await renderRegionOverview();

            jQuery("#data-table_processing").hide();
        });

        driverNearBy.get().then(async function (snapshots) {
            var driverNearByData = snapshots.data();
            $(".distance-type").text(driverNearByData.distanceType);
        })
    });

    /* The figures for the region being worked in, or the global ones when no
     * region is selected. */
    function chargesForActiveRegion() {
        var regionId = getActiveRegionId();
        if (regionId && deliveryChargeSettings.regions && deliveryChargeSettings.regions[regionId]) {
            return deliveryChargeSettings.regions[regionId];
        }
        return null;
    }

    function renderForm() {
        var regionId = getActiveRegionId();
        var override = chargesForActiveRegion();
        var values = override ? override : deliveryChargeSettings;

        if (regionId) {
            $('#use_global_row').show();
            $('#use_global').prop('checked', override == null);
            $('#scope_notice').show().text(
                override == null
                    ? "{{ trans('lang.delivery_charge_scope_inherited') }}"
                    : "{{ trans('lang.delivery_charge_scope_region') }}"
            );
        } else {
            $('#use_global_row').hide();
            $('#scope_notice').show().text("{{ trans('lang.delivery_charge_scope_global') }}");
        }

        $("#vendor_can_modify").prop('checked', values.vendor_can_modify ? true : false);
        $("#delivery_charges_per_km").val(values.delivery_charges_per_km);
        $("#minimum_delivery_charges").val(values.minimum_delivery_charges);
        $("#minimum_delivery_charges_within_km").val(values.minimum_delivery_charges_within_km);

        toggleFields();
    }

    /* Ticking "use the global charge" leaves nothing to edit for this region. */
    function toggleFields() {
        var disabled = getActiveRegionId() != '' && $('#use_global').is(':checked');
        $('#vendor_can_modify, #delivery_charges_per_km, #minimum_delivery_charges, #minimum_delivery_charges_within_km')
            .prop('disabled', disabled);
    }

    $(document).on('change', '#use_global', function () {
        if ($(this).is(':checked')) {
            $("#vendor_can_modify").prop('checked', deliveryChargeSettings.vendor_can_modify ? true : false);
            $("#delivery_charges_per_km").val(deliveryChargeSettings.delivery_charges_per_km);
            $("#minimum_delivery_charges").val(deliveryChargeSettings.minimum_delivery_charges);
            $("#minimum_delivery_charges_within_km").val(deliveryChargeSettings.minimum_delivery_charges_within_km);
        }
        toggleFields();
    });

    /* A read-only summary so an admin can see every region's charge at a glance
     * without switching between them. */
    async function renderRegionOverview() {
        var regions = await getPublishedRegions(false);
        if (regions.length == 0) {
            return;
        }

        var overrides = deliveryChargeSettings.regions || {};
        var html = '';

        for (var i = 0; i < regions.length; i++) {
            var region = regions[i];
            var o = overrides[region.id];
            var v = o ? o : deliveryChargeSettings;
            html += '<tr>';
            html += '<td>' + region.name + '</td>';
            html += '<td>' + (v.delivery_charges_per_km !== undefined ? v.delivery_charges_per_km : '') + '</td>';
            html += '<td>' + (v.minimum_delivery_charges !== undefined ? v.minimum_delivery_charges : '') + '</td>';
            html += '<td>' + (v.minimum_delivery_charges_within_km !== undefined ? v.minimum_delivery_charges_within_km : '') + '</td>';
            html += '<td>' + (o ? "{{ trans('lang.delivery_charge_own') }}" : "{{ trans('lang.delivery_charge_inherited') }}") + '</td>';
            html += '</tr>';
        }

        $('#region_overview_rows').html(html);
        $('#region_overview_box').show();
    }

    $(".edit-setting-btn").click(function () {
        var regionId = getActiveRegionId();
        var payload = {
            'vendor_can_modify': $("#vendor_can_modify").is(":checked"),
            'delivery_charges_per_km': parseInt($("#delivery_charges_per_km").val()),
            'minimum_delivery_charges': parseInt($("#minimum_delivery_charges").val()),
            'minimum_delivery_charges_within_km': parseInt($("#minimum_delivery_charges_within_km").val())
        };

        var update = {};

        if (regionId == '') {
            /* No region selected: the global figures are being edited. */
            update = payload;
        } else if ($('#use_global').is(':checked')) {
            /* Drop this region's override so it follows the global figures. */
            update['regions.' + regionId] = firebase.firestore.FieldValue.delete();
        } else {
            update['regions.' + regionId] = payload;
        }

        database.collection('settings').doc("DeliveryCharge").update(update).then(function (result) {
            window.location.href = '{{ url("settings/app/deliveryCharge")}}';
        });
    });
</script>
@endsection
