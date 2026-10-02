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
                <li class="breadcrumb-item active">{{ trans('lang.carrier_table') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><i class="mdi mdi-truck" style="font-size:28px;"></i></span>
                            <h3 class="mb-0">{{ trans('lang.carrier_table') }}</h3>
                            <span class="counter ml-3 carrier_count"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.carrier_table') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.carrier_table_text') }}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a class="btn-primary btn rounded-full" href="{!! route('carriers.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.carrier_create') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="carrierTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <?php if (in_array('carrier.delete', json_decode(@session('user_permissions'), true))) { ?>
                                            <th class="delete-all"><input type="checkbox" id="is_active">
                                                <label class="col-3 control-label" for="is_active">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{ trans('lang.all') }}</a>
                                                </label>
                                            </th>
                                            <?php } ?>
                                            <th>{{ trans('lang.carrier_name') }}</th>
                                            <th>{{ trans('lang.carrier_code') }}</th>
                                            <th>{{ trans('lang.carrier_regions') }}</th>
                                            <th>{{ trans('lang.carrier_delivery_time') }}</th>
                                            <th>{{ trans('lang.status') }}</th>
                                            <th>{{ trans('lang.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_list1"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();
    var user_permissions = '<?php echo @session("user_permissions") ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = $.inArray('carrier.delete', user_permissions) >= 0;
    var regionNames = {};
    var placeholderImage = '';

    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        loadCarriers();
    });

    async function loadCarriers() {
        /* Awaited rather than fired alongside the rows: the placeholder has to
         * be known before the first row is drawn, or logo-less carriers render
         * with an empty src. */
        var placeholder = await database.collection('settings').doc('placeHolderImage').get();
        var placeholderData = placeholder.exists ? placeholder.data() : null;
        placeholderImage = placeholderData && placeholderData.image ? placeholderData.image : '';

        var regions = await getPublishedRegions(false);
        regions.forEach(function (r) { regionNames[r.id] = r.name; });

        /* A carrier can serve several regions, so it is matched on the
         * regionIds array. Carriers with no regions are offered everywhere and
         * therefore always listed. */
        var snapshots = await database.collection('delivery_carriers').get();

        var rows = [];
        snapshots.docs.forEach(function (doc) {
            var carrier = doc.data();
            if (!carrierInActiveRegion(carrier)) {
                return;
            }
            rows.push(carrier);
        });

        rows.sort(function (a, b) {
            return (a.name || '').localeCompare(b.name || '');
        });

        $('.carrier_count').text(rows.length);
        $('#append_list1').html(rows.length ? rows.map(buildRow).join('') : '');

        if (checkDeletePermission) {
            $('#carrierTable').DataTable({
                order: [[1, 'asc']],
                columnDefs: [{orderable: false, targets: [0, 3, 5, 6]}],
                "language": datatableLang,
                responsive: true
            });
        } else {
            $('#carrierTable').DataTable({
                order: [[0, 'asc']],
                columnDefs: [{orderable: false, targets: [2, 4, 5]}],
                "language": datatableLang,
                responsive: true
            });
        }

        $('[data-toggle="tooltip"]').tooltip();
        jQuery("#data-table_processing").hide();
    }

    /* Empty regionIds means the carrier operates everywhere. */
    function carrierInActiveRegion(carrier) {
        var active = getActiveRegionId();
        if (!active) {
            return true;
        }
        var list = carrier.regionIds || [];
        return list.length === 0 || list.indexOf(active) !== -1;
    }

    function buildRow(val) {
        var route1 = '{{ route("carriers.edit", ":id") }}'.replace(':id', val.id);
        var names = (val.regionIds || []).map(function (r) { return regionNames[r] || r; });
        var regionLabel = names.length ? names.join(', ') : "{{ trans('lang.carrier_all_regions') }}";

        var time = '';
        if (val.minDeliveryTime || val.maxDeliveryTime) {
            time = (val.minDeliveryTime || '0') + ' - ' + (val.maxDeliveryTime || '0') + ' ' + (val.deliveryTimeUnit || '');
        }

        var html = '<tr>';
        if (checkDeletePermission) {
            html += '<td class="delete-all"><input type="checkbox" id="is_open_' + val.id + '" class="is_open" dataId="' + val.id + '"><label class="col-3 control-label" for="is_open_' + val.id + '"></label></td>';
        }
        /* Falls back to the shared placeholder, and again via onerror if the
         * stored URL is dead - matching the stores list. */
        var photo = val.photo ? val.photo : placeholderImage;
        var logo = '<img class="rounded mr-2" style="width:50px;height:50px;object-fit:cover" src="' + photo +
                   '" data-fallback="' + placeholderImage + '" alt="">';
        html += '<td>' + logo + '<a href="' + route1 + '" class="left_space">' + (val.name || '') + '</a></td>';
        html += '<td>' + (val.code || '') + '</td>';
        html += '<td>' + regionLabel + '</td>';
        html += '<td>' + time + '</td>';
        html += '<td><label class="switch"><input type="checkbox" ' + (val.publish ? 'checked' : '') + ' id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';
        html += '<td class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans("lang.edit") }}"><i class="mdi mdi-lead-pencil"></i></a>';
        if (checkDeletePermission) {
            html += '<a id="' + val.id + '" name="carrier-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans("lang.delete") }}"><i class="mdi mdi-delete"></i></a>';
        }
        html += '</td></tr>';
        return html;
    }

    /* Swaps in the placeholder when a stored logo URL is dead. Bound in the
     * capture phase because the img error event does not bubble, so ordinary
     * delegation would never see it. */
    document.addEventListener('error', function (e) {
        var img = e.target;
        if (img && img.tagName === 'IMG' && img.dataset.fallback && img.src !== img.dataset.fallback) {
            img.src = img.dataset.fallback;
        }
    }, true);

    $("#is_active").click(function () {
        $("#carrierTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function () {
        if (!$('#carrierTable .is_open:checked').length) {
            alert("{{ trans('lang.select_delete_alert') }}");
            return;
        }
        if (!confirm("{{ trans('lang.selected_delete_alert') }}")) {
            return;
        }
        jQuery("#overlay").show();
        var ids = $('#carrierTable .is_open:checked').map(function () { return $(this).attr('dataId'); }).get();
        Promise.all(ids.map(function (i) {
            return database.collection('delivery_carriers').doc(i).delete();
        })).then(function () { window.location.reload(); });
    });

    $(document).on("click", "input[name='isSwitch']", function () {
        database.collection('delivery_carriers').doc(this.id).update({'publish': $(this).is(':checked')});
    });

    $(document).on("click", "a[name='carrier-delete']", function () {
        var id = this.id;
        if (!confirm("{{ trans('lang.carrier_delete_message') }}")) {
            return;
        }
        jQuery("#overlay").show();
        database.collection('delivery_carriers').doc(id).delete().then(function () {
            window.location.reload();
        });
    });
</script>
@endsection
