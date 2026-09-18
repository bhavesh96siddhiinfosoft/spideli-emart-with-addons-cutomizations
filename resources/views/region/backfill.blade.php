@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.region_backfill') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('region') !!}">{{ trans('lang.region_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.region_backfill') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">

                <div class="alert alert-warning">
                    {{ trans('lang.region_backfill_help') }}
                </div>

                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">
                        <fieldset>
                            <legend>{{ trans('lang.region_backfill') }}</legend>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.region_backfill_default') }}<span class="required-field"></span></label>
                                <div class="col-7">
                                    <select class="form-control" id="default_region">
                                        <option value="">{{ trans('lang.select_region') }}</option>
                                    </select>
                                    <div class="form-text text-muted">{{ trans('lang.region_backfill_default_help') }}</div>
                                    <div id="error_region" class="err"></div>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{ trans('lang.region_backfill_collections') }}</label>
                                <div class="col-7" id="collection_list"></div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-default" id="dry_run_btn">
                        <i class="fa fa-search"></i> {{ trans('lang.region_backfill_dry_run') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="apply_btn">
                        <i class="fa fa-save"></i> {{ trans('lang.region_backfill_apply') }}
                    </button>
                    <a href="{!! route('region') !!}" class="btn btn-default">
                        <i class="fa fa-undo"></i>{{ trans('lang.cancel') }}
                    </a>
                </div>

                <div id="ambiguous_zones" class="alert alert-warning m-t-10" style="display:none;"></div>
                <div class="table-responsive m-t-10" id="result_box" style="display:none;">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ trans('lang.region_backfill_collection') }}</th>
                                <th>{{ trans('lang.region_backfill_total') }}</th>
                                <th>{{ trans('lang.region_backfill_missing') }}</th>
                                <th>{{ trans('lang.region_backfill_from_zone') }}</th>
                                <th>{{ trans('lang.region_backfill_from_store') }}</th>
                                <th>{{ trans('lang.region_backfill_from_default') }}</th>
                                <th>{{ trans('lang.region_backfill_written') }}</th>
                            </tr>
                        </thead>
                        <tbody id="result_rows"></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();

    /* Firestore writes are capped at 500 operations per batch. */
    var BATCH_LIMIT = 500;

    /* Collections to stamp with regionId, in dependency order: a record that
     * inherits from a parent must be listed after that parent, because the
     * parent maps are reloaded between collections.
     *
     *   zoneField   - field holding a zone id, used to derive the region
     *   roleFilter  - only rows with this `role` value are touched
     *   parentField - field holding the id of the record it belongs to
     *   parentType  - which parent map to look that id up in
     *
     * Later phases extend this list rather than changing the logic. */
    var BACKFILL_COLLECTIONS = [
        {key: 'vendors', label: 'Stores (vendors)', zoneField: 'zoneId'},
        {key: 'users_vendor', collection: 'users', label: 'Vendor users', zoneField: 'zoneId', roleFilter: 'vendor', parentField: 'vendorID', parentType: 'vendor'},
        {key: 'users_driver', collection: 'users', label: 'Driver users', zoneField: 'zoneId', roleFilter: 'driver'},
        {key: 'users_provider', collection: 'users', label: 'Service providers', zoneField: 'zoneId', roleFilter: 'provider'},
        {key: 'providers_workers', label: 'Provider workers', parentField: 'providerId', parentType: 'provider'},
        {key: 'providers_services', label: 'Provider services', parentField: 'author', parentType: 'provider'},
        {key: 'vendor_orders', label: 'Orders', zoneField: 'zoneId', parentField: 'vendorID', parentType: 'vendor'},
        {key: 'users_customer', collection: 'users', label: 'Customers', roleFilter: 'customer', manyRegions: true}
    ];

    /* customer id -> the set of regions they have ordered in. */
    var customerToRegions = {};

    var zoneToRegion = {};
    var ambiguousZones = [];
    var parentToRegion = {vendor: {}, provider: {}};
    var parentToZone = {vendor: {}, provider: {}};

    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        buildCollectionList();
        loadRegions().then(function () {
            jQuery("#data-table_processing").hide();
        });
    });

    function buildCollectionList() {
        var html = '';
        for (var i = 0; i < BACKFILL_COLLECTIONS.length; i++) {
            var item = BACKFILL_COLLECTIONS[i];
            var checked = 'checked';
            html += '<div class="form-check">';
            html += '<input type="checkbox" class="backfill_collection" id="chk_' + item.key + '" value="' + item.key + '" ' + checked + '>';
            html += '<label class="control-label2" for="chk_' + item.key + '">' + item.label + '</label>';
            html += '</div>';
        }
        $('#collection_list').html(html);
    }

    async function loadRegions() {
        var regions = await getPublishedRegions();
        regions.forEach(function (region) {
            $('#default_region').append($('<option></option>').attr('value', region.id).text(region.name));
        });
    }

    /* zone -> region, so a record carrying a zone lands in the right region
     * instead of falling back to the default.
     *
     * A zone serving SEVERAL regions cannot say which one a record belongs to,
     * so it is left out of this map entirely. Those records fall through to
     * their parent, and then to the default region, rather than being assigned
     * to whichever region happened to come first. */
    async function loadZoneMap() {
        zoneToRegion = {};
        ambiguousZones = [];
        var snapshots = await database.collection('zone').get();
        snapshots.docs.forEach(function (doc) {
            var zone = doc.data();
            var regionIds = zoneRegionIds(zone);

            if (regionIds.length === 1) {
                zoneToRegion[doc.id] = regionIds[0];
            } else if (regionIds.length > 1) {
                ambiguousZones.push(zone.name || doc.id);
            }
        });
    }

    /* Maps for records that belong to a parent rather than owning a zone of
     * their own: vendor users and orders hang off a store, workers and services
     * hang off a service provider. The parent's own region wins, so a parent
     * moved to another region by hand takes its children with it. */
    async function loadParentMaps() {
        parentToRegion = {vendor: {}, provider: {}};
        parentToZone = {vendor: {}, provider: {}};

        var vendors = await database.collection('vendors').get();
        vendors.docs.forEach(function (doc) {
            var vendor = doc.data();
            if (vendor.zoneId) {
                parentToZone.vendor[doc.id] = vendor.zoneId;
            }
            if (vendor.regionId) {
                parentToRegion.vendor[doc.id] = vendor.regionId;
            }
        });

        /* Customers are placed by where they have actually traded, so their
         * regions are gathered from their own order history. */
        customerToRegions = {};
        var orders = await database.collection('vendor_orders').get();
        orders.docs.forEach(function (doc) {
            var order = doc.data();
            if (!order.authorID || !order.regionId) {
                return;
            }
            if (!customerToRegions[order.authorID]) {
                customerToRegions[order.authorID] = [];
            }
            if (customerToRegions[order.authorID].indexOf(order.regionId) === -1) {
                customerToRegions[order.authorID].push(order.regionId);
            }
        });

        var providers = await database.collection('users').where('role', '==', 'provider').get();
        providers.docs.forEach(function (doc) {
            var provider = doc.data();
            if (provider.zoneId) {
                parentToZone.provider[doc.id] = provider.zoneId;
            }
            if (provider.regionId) {
                parentToRegion.provider[doc.id] = provider.regionId;
            }
        });
    }

    function getConfig(key) {
        for (var i = 0; i < BACKFILL_COLLECTIONS.length; i++) {
            if (BACKFILL_COLLECTIONS[i].key == key) {
                return BACKFILL_COLLECTIONS[i];
            }
        }
        return null;
    }

    function selectedKeys() {
        var keys = [];
        $('.backfill_collection:checked').each(function () {
            keys.push($(this).val());
        });
        return keys;
    }

    /* Works out the region for one document. Returns null when the document
     * already carries a region and must be left alone.
     *
     * Order of preference:
     *   1. its own delivery zone's region
     *   2. its parent's region        (store, or service provider)
     *   3. its parent's zone's region
     *   4. the default region
     */
    function resolveRegion(config, data, defaultRegion) {
        /* Records that can sit in several regions carry a regionIds array
         * instead, filled from where they have actually traded. */
        if (config.manyRegions) {
            if (Array.isArray(data.regionIds) && data.regionIds.length > 0) {
                return null;
            }
            var traded = customerToRegions[data.id] || [];
            if (traded.length > 0) {
                return {regionIds: traded.slice(), source: 'parent'};
            }
            return {regionIds: [defaultRegion], source: 'default'};
        }

        if (data.regionId != undefined && data.regionId != null && data.regionId != '') {
            return null;
        }

        var zoneId = config.zoneField ? data[config.zoneField] : '';

        if (zoneId && zoneToRegion[zoneId]) {
            return {regionId: zoneToRegion[zoneId], source: 'zone'};
        }

        if (config.parentField && config.parentType) {
            var parentId = data[config.parentField];
            if (parentId) {
                var byRegion = parentToRegion[config.parentType] || {};
                if (byRegion[parentId]) {
                    return {regionId: byRegion[parentId], source: 'parent'};
                }
                var byZone = parentToZone[config.parentType] || {};
                var parentZone = byZone[parentId];
                if (parentZone && zoneToRegion[parentZone]) {
                    return {regionId: zoneToRegion[parentZone], source: 'parent'};
                }
            }
        }

        return {regionId: defaultRegion, source: 'default'};
    }

    async function runBackfill(apply) {
        $('#error_region').html('');
        var defaultRegion = $('#default_region').val();
        var keys = selectedKeys();

        if (!defaultRegion) {
            $('#error_region').html("{{ trans('lang.region_backfill_default_error') }}");
            return;
        }
        if (keys.length == 0) {
            alert("{{ trans('lang.region_backfill_collection_error') }}");
            return;
        }
        if (apply && !confirm("{{ trans('lang.region_backfill_confirm') }}")) {
            return;
        }

        jQuery("#overlay").show();
        $('#result_rows').html('');
        $('#result_box').show();

        await loadZoneMap();

        /* A zone serving several regions cannot place a record, so those
         * records fall through to their parent or the default. Said out loud,
         * because otherwise the numbers look wrong for no visible reason. */
        if (ambiguousZones.length) {
            $('#ambiguous_zones').show().html(
                "{{ trans('lang.region_backfill_ambiguous_zones') }} " +
                ambiguousZones.join(', '));
        } else {
            $('#ambiguous_zones').hide().html('');
        }

        for (var i = 0; i < keys.length; i++) {
            /* Reloaded per collection so that stores stamped earlier in this
             * same run are already visible to the records that hang off them. */
            await loadParentMaps();

            var config = getConfig(keys[i]);
            var result = await backfillCollection(config, defaultRegion, apply);
            appendResultRow(config, result);
        }

        jQuery("#overlay").hide();
    }

    async function backfillCollection(config, defaultRegion, apply) {
        var collectionName = config.collection ? config.collection : config.key;
        var query = database.collection(collectionName);
        if (config.roleFilter) {
            query = query.where('role', '==', config.roleFilter);
        }

        /* Firestore cannot query for "field is missing", so every row is read
         * and filtered here. */
        var snapshots = await query.get();

        var result = {total: 0, missing: 0, fromZone: 0, fromStore: 0, fromDefault: 0, written: 0};
        var pending = [];

        snapshots.docs.forEach(function (doc) {
            result.total++;
            var resolved = resolveRegion(config, doc.data(), defaultRegion);
            if (resolved == null) {
                return;
            }
            result.missing++;
            if (resolved.source == 'zone') {
                result.fromZone++;
            } else if (resolved.source == 'parent') {
                result.fromStore++;
            } else {
                result.fromDefault++;
            }
            if (config.manyRegions) {
                pending.push({ref: doc.ref, payload: {'regionIds': resolved.regionIds}});
            } else {
                pending.push({ref: doc.ref, payload: {'regionId': resolved.regionId}});
            }
        });

        if (!apply) {
            return result;
        }

        for (var i = 0; i < pending.length; i += BATCH_LIMIT) {
            var batch = database.batch();
            var slice = pending.slice(i, i + BATCH_LIMIT);
            for (var j = 0; j < slice.length; j++) {
                batch.update(slice[j].ref, slice[j].payload);
            }
            await batch.commit();
            result.written += slice.length;
        }

        return result;
    }

    function appendResultRow(config, result) {
        var html = '<tr>';
        html += '<td>' + config.label + '</td>';
        html += '<td>' + result.total + '</td>';
        html += '<td>' + result.missing + '</td>';
        html += '<td>' + result.fromZone + '</td>';
        html += '<td>' + result.fromStore + '</td>';
        html += '<td>' + result.fromDefault + '</td>';
        html += '<td>' + result.written + '</td>';
        html += '</tr>';
        $('#result_rows').append(html);
    }

    $('#dry_run_btn').click(function () {
        runBackfill(false);
    });

    $('#apply_btn').click(function () {
        runBackfill(true);
    });
</script>
@endsection
