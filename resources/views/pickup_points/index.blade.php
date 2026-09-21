@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.pickup_point_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.pickup_point_plural') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.pickup_point_plural') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.pickup_point_help') }}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a class="btn-primary btn rounded-full" href="{!! route('pickup-points.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.pickup_point_create') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="error_top" style="display:none"></div>
                            <div class="table-responsive m-t-10">
                                <table id="pickupPointTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.pickup_point_name') }}</th>
                                            <th>{{ trans('lang.pickup_point_quarter') }}</th>
                                            <th>{{ trans('lang.pickup_point_town') }}</th>
                                            <th>{{ trans('lang.pickup_point_phone') }}</th>
                                            <th>{{ trans('lang.pickup_point_region') }}</th>
                                            <th>{{ trans('lang.pickup_point_parcels') }}</th>
                                            <th>{{ trans('lang.status') }}</th>
                                            <th>{{ trans('lang.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_list"></tbody>
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
<script>
    var database = firebase.firestore();
    var user_permissions = '<?php echo @session('user_permissions'); ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = ($.inArray('pickup-point.delete', user_permissions) >= 0);

    $(document).ready(function () {
        loadPoints();
    });

    async function loadPoints() {
        jQuery("#data-table_processing").show();

        var snapshots = await database.collection('pickup_points').get();

        /* Filtered in memory rather than with a scoped query, so a point saved
         * before regions existed - or with a region since deleted - is still
         * visible to an unbound admin instead of vanishing. */
        var points = regionDocs(snapshots).map(function (doc) {
            return doc.data();
        });

        points.sort(function (a, b) {
            return (a.name || '').localeCompare(b.name || '');
        });

        var regionNames = await pickupRegionNames();
        var counts = await parcelCountsPerPoint();
        var html = '';

        points.forEach(function (point) {
            html += buildRow(point, regionNames, counts[point.id] || 0);
        });

        $('#append_list').html(html || '<tr><td colspan="8">{{ trans('lang.pickup_point_empty') }}</td></tr>');
        jQuery("#data-table_processing").hide();
    }

    async function pickupRegionNames() {
        var names = {};
        var snapshots = await database.collection('regions').get();

        snapshots.docs.forEach(function (doc) {
            var region = doc.data();
            names[region.id] = region.name;
        });

        return names;
    }

    /* Spideli_upgrade.docx #21 - what each point has received. Counted from
     * `pickupPointId` on a parcel order. Nothing writes that field yet, so
     * every count is zero until the app does. */
    async function parcelCountsPerPoint() {
        var counts = {};
        var snapshots = await database.collection('parcel_orders').get();

        snapshots.docs.forEach(function (doc) {
            var pointId = doc.data().pickupPointId;

            if (pointId) {
                counts[pointId] = (counts[pointId] || 0) + 1;
            }
        });

        return counts;
    }

    function buildRow(point, regionNames, parcelCount) {
        var editRoute = '{{ route('pickup-points.edit', ':id') }}'.replace(':id', point.id);
        var parcelsRoute = '{{ route('pickup-points.parcels', ':id') }}'.replace(':id', point.id);
        var phone = (point.countryCode ? point.countryCode + ' ' : '') + (point.phone || '');
        var html = '<tr>';

        html += '<td><a href="' + editRoute + '">' + escapeHtml(point.name) + '</a></td>';
        html += '<td>' + escapeHtml(point.quarter) + '</td>';
        html += '<td>' + escapeHtml(point.town) + '</td>';
        html += '<td>' + escapeHtml(phone.trim()) + '</td>';
        html += '<td>' + escapeHtml(regionNames[point.regionId] || '-') + '</td>';
        html += '<td><a href="' + parcelsRoute + '">' + parcelCount + '</a></td>';
        html += point.publish ?
            '<td><span class="badge badge-success">{{ trans('lang.active') }}</span></td>' :
            '<td><span class="badge badge-danger">{{ trans('lang.in_active') }}</span></td>';

        html += '<td><span class="action-btn"><a href="' + editRoute + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';

        if (checkDeletePermission) {
            html += '<a href="javascript:void(0)" class="delete-btn" data-id="' + point.id + '" data-parcels="' + parcelCount + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
        }

        html += '</span></td></tr>';

        return html;
    }

    function escapeHtml(value) {
        return $('<div></div>').text(value === undefined || value === null ? '' : value).html();
    }

    $(document).on('click', '.delete-btn', function () {
        var id = $(this).data('id');
        var parcels = parseInt($(this).data('parcels')) || 0;

        /* Deleting a point parcels were sent to would leave those orders
         * pointing at nothing, and lose the history #21 asks us to show. */
        if (parcels > 0) {
            $('.error_top').show().html('<p>{{ trans('lang.pickup_point_delete_in_use') }}</p>');
            window.scrollTo(0, 0);
            return;
        }

        if (!confirm("{{ trans('lang.pickup_point_delete_message') }}")) {
            return;
        }

        database.collection('pickup_points').doc(id).delete().then(function () {
            loadPoints();
        });
    });
</script>
@endsection
