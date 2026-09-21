@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.service_group_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.service_group_plural')}}</li>
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
                                <h3 class="text-dark-2 mb-2 h4">{{trans('lang.service_group_plural')}}</h3>
                                <p class="mb-0 text-dark-2">{{trans('lang.service_group_help')}}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="card-header-btn mr-3">
                                    <a class="btn btn-default rounded-full" id="seed_defaults" href="javascript:void(0)"><i class="mdi mdi-refresh mr-2"></i>{{trans('lang.service_group_seed')}}</a>
                                </div>
                                <div class="card-header-btn mr-3">
                                    <a class="btn-primary btn rounded-full" href="{!! route('service-groups.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.service_group_create')}}</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-text text-muted mb-3">{{trans('lang.service_group_seed_help')}}</div>
                            <div class="success_top" style="display:none"></div>
                            <div class="error_top" style="display:none"></div>
                            <div class="table-responsive m-t-10">
                                <table id="serviceGroupTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{trans('lang.service_group_name')}}</th>
                                            <th>{{trans('lang.service_group_key')}}</th>
                                            <th>{{trans('lang.service_group_order')}}</th>
                                            <th>{{trans('lang.service_group_in_use')}}</th>
                                            <th>{{trans('lang.status')}}</th>
                                            <th>{{trans('lang.actions')}}</th>
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
    /* Read the same way as every other list in this panel: a raw PHP echo.
     * Blade compiles directives even inside a JavaScript comment, so naming
     * the one that caused this is itself enough to break the file. */
    var user_permissions = '<?php echo @session('user_permissions'); ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = ($.inArray('service-group.delete', user_permissions) >= 0);

    $(document).ready(function () {
        loadGroups();

        $('#seed_defaults').on('click', function () {
            seedDefaults();
        });
    });

    /* Ordered in memory rather than with orderBy, because Firestore drops
     * documents that lack the field being ordered by and a group created
     * without an order would disappear. */
    async function loadGroups() {
        jQuery("#data-table_processing").show();

        var snapshots = await database.collection('service_groups').get();
        var groups = snapshots.docs.map(function (doc) {
            return doc.data();
        });

        groups.sort(function (a, b) {
            return (parseInt(a.order) || 0) - (parseInt(b.order) || 0);
        });

        var counts = await countServicesPerGroup();
        var html = '';

        groups.forEach(function (group) {
            html += buildRow(group, counts[group.id] || 0);
        });

        $('#append_list').html(html || '<tr><td colspan="6">{{ trans('lang.service_group_empty') }}</td></tr>');
        jQuery("#data-table_processing").hide();
    }

    /* How many services sit in each group, so one in use cannot be deleted
     * out from under them. */
    async function countServicesPerGroup() {
        var counts = {};
        var snapshots = await database.collection('sections').get();

        snapshots.docs.forEach(function (doc) {
            var group = doc.data().serviceGroup;

            if (group) {
                counts[group] = (counts[group] || 0) + 1;
            }
        });

        return counts;
    }

    function buildRow(group, inUse) {
        var editRoute = '{{ route('service-groups.edit', ':id') }}'.replace(':id', group.id);
        var html = '<tr>';

        html += '<td><a href="' + editRoute + '">' + escapeHtml(group.name) + '</a></td>';
        html += '<td><code>' + escapeHtml(group.id) + '</code></td>';
        html += '<td>' + (group.order !== undefined ? group.order : '-') + '</td>';
        html += '<td>' + inUse + '</td>';
        html += group.publish ?
            '<td><span class="badge badge-success">{{ trans('lang.active') }}</span></td>' :
            '<td><span class="badge badge-danger">{{ trans('lang.in_active') }}</span></td>';

        html += '<td><span class="action-btn"><a href="' + editRoute + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';

        if (checkDeletePermission) {
            html += '<a href="javascript:void(0)" class="delete-btn" data-id="' + group.id + '" data-in-use="' + inUse + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
        }

        html += '</span></td></tr>';

        return html;
    }

    function escapeHtml(value) {
        return $('<div></div>').text(value === undefined || value === null ? '' : value).html();
    }

    $(document).on('click', '.delete-btn', function () {
        var id = $(this).data('id');
        var inUse = parseInt($(this).data('in-use')) || 0;

        /* Deleting a group that services still point at would leave them
         * holding a key that resolves to nothing. */
        if (inUse > 0) {
            $('.error_top').show().html('<p>{{ trans('lang.service_group_delete_in_use') }}</p>');
            window.scrollTo(0, 0);
            return;
        }

        if (!confirm("{{ trans('lang.service_group_delete_message') }}")) {
            return;
        }

        database.collection('service_groups').doc(id).delete().then(function () {
            loadGroups();
        });
    });

    /* The five groups from the client document, with the keys the section
     * screens already wrote before groups became editable. Seeding must keep
     * those keys, or every service already grouped would lose its label. */
    async function seedDefaults() {
        var defaults = [
            {id: 'shopping', name: "{{ trans('lang.service_group_shopping') }}", order: 1},
            {id: 'transport', name: "{{ trans('lang.service_group_transport') }}", order: 2},
            {id: 'finance', name: "{{ trans('lang.service_group_finance') }}", order: 3},
            {id: 'on_demand', name: "{{ trans('lang.service_group_on_demand') }}", order: 4},
            {id: 'others', name: "{{ trans('lang.service_group_others') }}", order: 5}
        ];

        jQuery("#data-table_processing").show();
        $('.error_top').hide().html('');

        var existing = await database.collection('service_groups').get();
        var created = 0;

        await Promise.all(defaults.map(async function (group) {
            var doc = await database.collection('service_groups').doc(group.id).get();

            if (doc.exists) {
                return;
            }

            created++;

            return database.collection('service_groups').doc(group.id).set({
                'id': group.id,
                'name': group.name,
                'order': group.order,
                'publish': true,
                'createdAt': firebase.firestore.FieldValue.serverTimestamp()
            });
        }));

        jQuery("#data-table_processing").hide();
        $('.success_top').show().html('<p>' + (created > 0 ?
            "{{ trans('lang.service_group_seed_done') }}" :
            "{{ trans('lang.service_group_seed_skip') }}") + '</p>');
        window.scrollTo(0, 0);

        loadGroups();
    }
</script>
@endsection
