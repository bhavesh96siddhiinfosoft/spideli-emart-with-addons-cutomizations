@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.region_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.region_table')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/zone.png') }}"></span>
                            <h3 class="mb-0">{{trans('lang.region_table')}}</h3>
                            <span class="counter ml-3 region_count"></span>
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
                                <h3 class="text-dark-2 mb-2 h4">{{trans('lang.region_table')}}</h3>
                                <p class="mb-0 text-dark-2">{{trans('lang.region_table_text')}}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <?php if (in_array('region.backfill', json_decode(@session('user_permissions'),true))) { ?>
                                <div class="card-header-btn mr-3">
                                    <a class="btn btn-default rounded-full" href="{!! route('region.backfill') !!}"><i class="mdi mdi-refresh mr-2"></i>{{trans('lang.region_backfill')}}</a>
                                </div>
                                <?php } ?>
                                <div class="card-header-btn mr-3">
                                    <a class="btn-primary btn rounded-full" href="{!! route('region.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.region_create')}}</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="regionTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <?php if (in_array('region.delete', json_decode(@session('user_permissions'),true))) { ?>
                                            <th class="delete-all"><input type="checkbox" id="is_active">
                                                <label class="col-3 control-label" for="is_active">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a>
                                                </label>
                                            </th>
                                            <?php } ?>
                                            <th>{{trans('lang.region_name')}}</th>
                                            <th>{{trans('lang.region_code')}}</th>
                                            <th>{{trans('lang.region_country_code')}}</th>
                                            <th>{{trans('lang.status')}}</th>
                                            <th>{{trans('lang.actions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_list1">
                                    </tbody>
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
    var ref = database.collection('regions');
    var append_list = '';
    var user_permissions = '<?php echo @session("user_permissions")?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('region.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }

    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        append_list = document.getElementById('append_list1');
        append_list.innerHTML = '';
        ref.orderBy('name').get().then(async function (snapshots) {
            var html = '';
            if (snapshots.docs.length > 0) {
                $('.region_count').text(snapshots.docs.length);
                html = await buildHTML(snapshots);
            }
            if (html != '') {
                append_list.innerHTML = html;
            }
            if (checkDeletePermission) {
                $('#regionTable').DataTable({
                    order: [[1, 'asc']],
                    columnDefs: [{orderable: false, targets: [0, 4, 5]}],
                    "language": datatableLang,
                    responsive: true
                });
            } else {
                $('#regionTable').DataTable({
                    order: [[0, 'asc']],
                    columnDefs: [{orderable: false, targets: [3, 4]}],
                    "language": datatableLang,
                    responsive: true
                });
            }
            $(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });
            jQuery("#data-table_processing").hide();
        });
    });

    /* Each row is collected into an array and joined at the end. Appending to a
     * shared string inside concurrent callbacks loses rows: every callback
     * reads the string before any of them resolves, so only the last write
     * survives. Promise.all also keeps the rows in document order. */
    async function buildHTML(snapshots) {
        var rows = await Promise.all(snapshots.docs.map(function (listval) {
            return getListData(listval.data());
        }));
        return rows.join('');
    }

    async function getListData(val) {
        var html = '';
        var id = val.id;
        var route1 = '{{route("region.edit",":id")}}';
        route1 = route1.replace(':id', id);
        html = html + '<tr>';
        if (checkDeletePermission) {
            html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label" for="is_open_' + id + '"></label></td>';
        }
        html = html + '<td><a href="' + route1 + '">' + (val.name || '') + '</a></td>';
        html = html + '<td>' + (val.code || '') + '</td>';
        html = html + '<td>' + (val.countryName || val.countryCode || '') + '</td>';
        if (val.publish) {
            html = html + '<td><label class="switch"><input type="checkbox" checked id="' + id + '" name="isSwitch"><span class="slider round"></span></label></td>';
        } else {
            html = html + '<td><label class="switch"><input type="checkbox" id="' + id + '" name="isSwitch"><span class="slider round"></span></label></td>';
        }
        html = html + '<td class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans("lang.edit") }}"><i class="mdi mdi-lead-pencil"></i></a>';
        if (checkDeletePermission) {
            html = html + '<a id="' + id + '" name="region-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans("lang.delete") }}"><i class="mdi mdi-delete"></i></a>';
        }
        html = html + '</td>';
        html = html + '</tr>';
        return html;
    }

    $("#is_active").click(function () {
        $("#regionTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function () {
        if ($('#regionTable .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#overlay").show();
                var ids = $('#regionTable .is_open:checked').map(function () {
                    return $(this).attr('dataId');
                }).get();
                Promise.all(ids.map(deleteRegion)).then(function () {
                    window.location.reload();
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });

    $(document).on("click", "input[name='isSwitch']", function (e) {
        var ischeck = $(this).is(':checked');
        database.collection('regions').doc(this.id).update({'publish': ischeck});
    });

    $(document).on("click", "a[name='region-delete']", function (e) {
        var id = this.id;
        if (confirm("{{trans('lang.region_delete_message')}}")) {
            jQuery("#overlay").show();
            deleteRegion(id).then(function () {
                window.location.reload();
            });
        }
    });

    /* Detach the region from its zones before deleting it, so no zone is left
     * pointing at a region that no longer exists. */
    async function deleteRegion(regionId) {
        var zoneSnapshots = await database.collection('zone').where('regionId', '==', regionId).get();
        await Promise.all(zoneSnapshots.docs.map(function (doc) {
            return database.collection('zone').doc(doc.id).update({'regionId': ''});
        }));
        await database.collection('regions').doc(regionId).delete();
        if (getActiveRegionId() === regionId) {
            setActiveRegion('');
        }
    }
</script>
@endsection
