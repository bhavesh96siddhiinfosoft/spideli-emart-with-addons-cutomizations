<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?> dir="rtl" <?php } ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/spideli-circle.png') }}">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <!-- Styles -->
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?>
        <link href="{{asset('assets/plugins/bootstrap/css/bootstrap-rtl.min.css')}}" rel="stylesheet">
    <?php } ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.min.css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    
    <?php if (str_replace('_', '-', app()->getLocale()) == 'ar' || @$_COOKIE['is_rtl'] == 'true') { ?>
        <link href="{{asset('css/style_rtl.css')}}" rel="stylesheet">
    <?php } ?>
    
    <link href="{{ asset('css/icons/font-awesome/css/font-awesome.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/toast-master/css/jquery.toast.css')}}" rel="stylesheet">
    <link href="{{ asset('css/colors/blue.css') }}" rel="stylesheet">
    <link href="{{ asset('css/chosen.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <!-- Datatable css -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/dist/css/select2.min.css')}}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw/dist/leaflet.draw.css" />
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="{{ asset('css/toastr.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <script>
        function setCookie(cname, cvalue, exdays) {
            const d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            let expires = "expires=" + d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }

        function getCookie(cname) {
            let name = cname + "=";
            let ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }
        let admin_panel_color = getCookie("admin_panel_color");
        if (admin_panel_color) {
            document.documentElement.style.setProperty('--admin-panel-color', admin_panel_color);
        }
    </script>

    <!-- @yield('style') -->
     
     <style>
        :root {
            --admin-panel-color: "#000000";
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>

<body>
    <div id="app" class="fix-header fix-sidebar card-no-border">
        <div id="main-wrapper">
            <div id="data-table_processing" class="page-overlay" style="display:none;">
                <div class="overlay-text">
                    <img src="{{asset('images/spinner.gif')}}">
                </div>
            </div>
            <header class="topbar non-printable">
                <nav class="navbar top-navbar navbar-expand-md navbar-light">
                    @include('layouts.header')
                </nav>
            </header>
            <aside class="left-sidebar non-printable">
                <div class="scroll-sidebar">
                    @include('layouts.menu')
                </div>
            </aside>
        </div>
        <main class="py-4">
            @yield('content')
        </main>
    </div>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw/dist/leaflet.draw.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-editable/0.7.3/leaflet.editable.min.js"></script>
    <script src="https://unpkg.com/leaflet-draw@0.4.14/dist/leaflet.draw-src.js"></script>
    <script src="https://unpkg.com/leaflet-geojson-layer/src/leaflet.geojson.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/jquery.slimscroll.js') }}"></script>
    <script src="{{ asset('js/waves.js') }}"></script>
    <script src="{{ asset('js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('assets/plugins/sticky-kit-master/dist/sticky-kit.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sparkline/jquery.sparkline.min.js')}}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/summernote/summernote-bs4.js')}}"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-storage-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>
    <script src="https://unpkg.com/geofirestore@5.2.0/dist/geofirestore.js"></script>
    <script src="https://cdn.firebase.com/libs/geofire/5.0.1/geofire.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/chosen.jquery.js') }}"></script>
    <script src="{{ asset('js/jquery.resizeImg.js') }}"></script>
    <script src="{{ asset('js/bootstrap-tagsinput.js') }}"></script>
    <script src="{{ asset('js/crypto-js.js') }}"></script>
    <script src="{{ asset('js/jquery.cookie.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('js/jquery.masking.js') }}"></script>
    <script src="{{ asset('js/toastr.min.js') }}"></script>
    
    <!-- Datatable script -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
    <script type="text/javascript"src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script type="text/javascript">
        jQuery(window).scroll(function () {
            var scroll = jQuery(window).scrollTop();
            if (scroll <= 60) {
                jQuery("body").removeClass("sticky");
            } else {
                jQuery("body").addClass("sticky");
            }
        });
        const datatableLang = {
            "decimal":        "",
            "emptyTable":     "{{ trans('lang.no_record_found') }}",
            "info":           "{{ trans('lang.datatable_info') }}", 
            "infoEmpty":      "{{ trans('lang.datatable_info_empty') }}", 
            "infoFiltered":   "{{ trans('lang.datatable_info_filtered') }}", 
            "lengthMenu":     "{{ trans('lang.datatable_length_menu') }}",
            "loadingRecords": "{{ trans('lang.loading') }}",
            "processing":     "{{ trans('lang.processing') }}",
            "search":         "{{ trans('lang.search') }}",
            "zeroRecords":    "{{ trans('lang.no_record_found') }}",
            "paginate": {
                "first":      "{{ trans('lang.first') }}",
                "last":       "{{ trans('lang.last') }}",
                "next":       "{{ trans('lang.next') }}",
                "previous":   "{{ trans('lang.previous') }}"
            },
            "aria": {
                "sortAscending":  ": {{ trans('lang.sort_asc') }}",
                "sortDescending": ": {{ trans('lang.sort_desc') }}"
            }
        };
    </script>
    <script type="text/javascript">

        var languages_list_main = [];
        var database = firebase.firestore();
        var geoFirestore = new GeoFirestore(database);
        var createdAtman = firebase.firestore.Timestamp.fromDate(new Date());
        var createdAt = { _nanoseconds: createdAtman.nanoseconds, _seconds: createdAtman.seconds };
        var mapType = 'ONLINE';

        /* ------------------------------------------------------------------
         * Multi-Region scope
         * ------------------------------------------------------------------
         * The admin picks a region with the header switcher, which stores it in
         * the `region_id` cookie, mirroring the existing `service_type` /
         * `section_id` section-switcher pattern.
         *
         * The cookie is never trusted on its own. AppServiceProvider resolves
         * it against the regions the admin is actually assigned to and hands
         * the result down as `admin_active_region`, so editing the cookie by
         * hand cannot widen an admin's access. `allowedRegionIds` is empty for
         * an unbound admin, who may work in every region.
         *
         * Usage in a list view:
         *     var ref = regionScoped(database.collection('vendors'));
         *
         * Usage before a write:
         *     database.collection('vendors').doc(id).set(withRegion(payload));
         * ------------------------------------------------------------------ */
        var REGION_COOKIE = 'region_id';
        var REGION_FIELD = 'regionId';
        var allowedRegionIds = {!! json_encode($admin_user_regions) !!};
        var activeRegionId = '{{ $admin_active_region }}';
        var activeRegionData = null;

        /* An admin assigned to one or more regions cannot step outside them,
         * and so is never offered the "All Regions" view. */
        function isRegionBound() {
            return allowedRegionIds.length > 0;
        }

        function isRegionAllowed(regionId) {
            if (!isRegionBound()) {
                return true;
            }
            return allowedRegionIds.indexOf(regionId) !== -1;
        }

        function getActiveRegionId() {
            return activeRegionId || '';
        }

        function hasRegionScope() {
            return getActiveRegionId() !== '';
        }

        /* Apply the active region filter to a Firestore collection/query.
         * Returns the query untouched when no region is selected, so existing
         * behaviour is preserved for an "All Regions" admin. */
        function regionScoped(query, field) {
            var regionId = getActiveRegionId();
            if (!regionId) {
                return query;
            }
            return query.where(field || REGION_FIELD, '==', regionId);
        }

        /* Stamp the active region onto a payload before writing it.
         * An explicit value already present on the payload always wins. */
        function withRegion(data, field) {
            data = data || {};
            var key = field || REGION_FIELD;
            var regionId = getActiveRegionId();
            if (regionId && (data[key] === undefined || data[key] === null || data[key] === '')) {
                data[key] = regionId;
            }
            return data;
        }

        /* ---- Multi-region records ------------------------------------------
         * Some records legitimately belong to several regions at once. A
         * customer who has ordered in Cameroon and in France must appear to the
         * admin of both, so they carry a `regionIds` array rather than a single
         * `regionId`, and are matched with array-contains.
         *
         *     var ref = regionScopedAny(database.collection('users')...);
         * ------------------------------------------------------------------ */
        var REGION_FIELD_MANY = 'regionIds';

        function regionScopedAny(query, field) {
            var regionId = getActiveRegionId();
            if (!regionId) {
                return query;
            }
            return query.where(field || REGION_FIELD_MANY, 'array-contains', regionId);
        }

        /* Guard for a record fetched outside a scoped query, for the same
         * many-regions shape. */
        function isInAnyActiveRegion(data, field) {
            var regionId = getActiveRegionId();
            if (!regionId) {
                return true;
            }
            if (!data) {
                return false;
            }
            var list = data[field || REGION_FIELD_MANY];
            return Array.isArray(list) && list.indexOf(regionId) !== -1;
        }

        /* Narrows an already-fetched snapshot to the active region and returns
         * the surviving docs.
         *
         * Dashboard statistics fetch whole result sets and count them in the
         * browser, so filtering here costs no extra reads — and unlike adding
         * regionId to each of those queries, it needs no composite indexes.
         * Firestore allows 200 per database and the statistics screens alone
         * would need over a hundred.
         *
         *     let orders = regionDocs(ordersSnapshot);
         *     let total  = orders.length;
         */
        function regionDocs(snapshot, field) {
            var docs = (snapshot && snapshot.docs) ? snapshot.docs : [];
            if (!getActiveRegionId()) {
                return docs;
            }
            return docs.filter(function (doc) {
                return isInActiveRegion(doc.data(), field);
            });
        }

        /* Client-side guard for documents fetched outside a scoped query
         * (e.g. a direct doc().get() on an edit screen). */
        function isInActiveRegion(data, field) {
            var regionId = getActiveRegionId();
            if (!regionId) {
                return true;
            }
            if (!data) {
                return false;
            }
            return data[field || REGION_FIELD] === regionId;
        }

        async function getActiveRegion() {
            var regionId = getActiveRegionId();
            if (!regionId) {
                return null;
            }
            if (activeRegionData && activeRegionData.id === regionId) {
                return activeRegionData;
            }
            var snapshot = await database.collection('regions').doc(regionId).get();
            activeRegionData = snapshot.exists ? snapshot.data() : null;
            return activeRegionData;
        }

        /* Resolves the currency to display amounts in.
         *
         * A region may name its own currency; otherwise the globally active
         * currency is used, which is the behaviour the panel had before regions
         * existed.
         *
         * Two ways in:
         *   regionCurrencyRef()        - the region the admin is working in.
         *                                Used by list and dashboard screens.
         *   recordCurrencyRef(regionId) - the region a record belongs to. Used
         *                                where one record's money is shown, so
         *                                a French order reads in euros even
         *                                while viewing All Regions. The stored
         *                                amount is only a number; the currency
         *                                comes from the record's region, and
         *                                showing the wrong symbol misstates
         *                                money rather than just looking odd. */
        var regionCurrencyCache = {};

        async function getCurrencyForRegion(regionId) {
            var key = regionId ? regionId : '__global__';
            if (regionCurrencyCache[key] !== undefined) {
                return regionCurrencyCache[key];
            }

            var currency = null;

            if (regionId) {
                var regionSnapshot = await database.collection('regions').doc(regionId).get();
                var region = regionSnapshot.exists ? regionSnapshot.data() : null;
                if (region && region.currencyId) {
                    var snapshot = await database.collection('currencies').doc(region.currencyId).get();
                    if (snapshot.exists) {
                        currency = snapshot.data();
                    }
                }
            }

            if (currency == null) {
                /* Queries Firestore directly on purpose: routing this through
                 * the refs below would call back into this function. */
                var fallback = await database.collection('currencies').where('isActive', '==', true).get();
                if (fallback.docs.length > 0) {
                    currency = fallback.docs[0].data();
                }
            }

            regionCurrencyCache[key] = currency;
            return currency;
        }

        async function getRegionCurrency() {
            return await getCurrencyForRegion(getActiveRegionId());
        }

        /* ---- Delivery management -------------------------------------------
         * Manual driver assignment, the record of who was assigned to what and
         * when, and re-sending a notification a driver never acted on.
         *
         * Every assignment action is written to `order_assignment_history` so
         * the trail survives later reassignment - the order document only ever
         * holds the driver currently on the job.
         * ------------------------------------------------------------------ */
        var DELIVERY_HISTORY_COLLECTION = 'order_assignment_history';

        /* Records one action against an order. Never throws into the caller:
         * failing to write the audit row must not lose the assignment itself. */
        async function logDeliveryAction(entry) {
            try {
                var id = database.collection('tmp').doc().id;
                await database.collection(DELIVERY_HISTORY_COLLECTION).doc(id).set({
                    'id': id,
                    'orderId': entry.orderId || '',
                    'orderType': entry.orderType || 'vendor_orders',
                    'driverId': entry.driverId || '',
                    'driverName': entry.driverName || '',
                    'action': entry.action || '',
                    'orderStatus': entry.orderStatus || '',
                    'note': entry.note || '',
                    'adminName': '{{ addslashes(Auth::user()->name ?? "") }}',
                    'regionId': entry.regionId || getActiveRegionId(),
                    'createdAt': firebase.firestore.FieldValue.serverTimestamp()
                });
                return true;
            } catch (e) {
                console.error('Could not record the delivery action', e);
                return false;
            }
        }

        /* Pushes a notification to one driver. Returns false when the driver has
         * no device token rather than pretending it was delivered. */
        function sendDriverNotification(fcmToken, title, message, payload) {
            if (!fcmToken) {
                return $.Deferred().resolve({success: false, message: 'no-token'}).promise();
            }
            return $.ajax({
                url: "{{ route('send-notification') }}",
                type: 'POST',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'fcm': fcmToken,
                    'title': title,
                    'message': message,
                    'payload': JSON.stringify(payload || {})
                }
            });
        }

        /* ---- Region assignment control -------------------------------------
         * Used by settings screens where one record belongs to several regions
         * at once, such as a payment gateway offered in more than one country.
         * The regions live in a `regionIds` array on the record.
         *
         *     await loadRegionAssignment(settingsData);   // fill + preselect
         *     ...update({ 'regionIds': getRegionAssignment() });
         * ------------------------------------------------------------------ */
        /* The published regions this admin may use, sorted by name.
         *
         * Regions are a short list, so they are filtered and sorted here rather
         * than in the query. Combining where('publish') with orderBy('name')
         * would demand a composite index for a handful of documents. */
        async function getPublishedRegions(respectPermission) {
            var snapshots = await database.collection('regions').get();

            var regions = [];
            snapshots.docs.forEach(function (doc) {
                var region = doc.data();
                if (region.publish !== true) {
                    return;
                }
                if (respectPermission !== false && !isRegionAllowed(region.id)) {
                    return;
                }
                regions.push(region);
            });

            regions.sort(function (a, b) {
                return (a.name || '').localeCompare(b.name || '');
            });

            return regions;
        }

        async function loadRegionAssignment(data, selector) {
            var $select = $(selector || '#region_assignment');
            if (!$select.length) {
                return;
            }

            $select.empty();

            var regions = await getPublishedRegions();

            regions.forEach(function (region) {
                $select.append($('<option></option>').attr('value', region.id).text(region.name));
            });

            var assigned = (data && data.regionIds) ? data.regionIds : [];
            $select.val(assigned);

            /* Chosen hides the native select and draws its own box. Calling
             * .show() again on an already-initialised select would reveal the
             * original alongside it, which is what a second call used to do. */
            if ($select.data('chosen')) {
                $select.trigger('chosen:updated');
            } else {
                $select.show().chosen({
                    "placeholder_text": "{{ trans('lang.select_region') }}"
                });
                $select.trigger('chosen:updated');
            }
        }

        function getRegionAssignment(selector) {
            var $select = $(selector || '#region_assignment');
            if (!$select.length) {
                return [];
            }
            return $select.val() || [];
        }

        /* A stand-in for
         *     regionCurrencyRef()
         * that resolves the region's currency instead of the globally active
         * one, falling back to the global currency when the region names none.
         *
         * It mimics the shape the screens already expect - .limit() chaining
         * and a .get() yielding { docs: [ { data() } ] } - so a display screen
         * only swaps the query expression and keeps its own logic untouched. */
        /* ---- Per-record currency -------------------------------------------
         * regionCurrencyRef() above answers "what currency is the admin
         * currently looking at", which is the top-bar region - and falls back to
         * the global currency under All Regions.
         *
         * A list that spans regions needs the currency of each *row's* owner
         * instead: a Cameroon store's prices read in FCFA whichever region the
         * admin has selected. These resolve that owner, and all of them go
         * through getCurrencyForRegion(), so the existing cache still applies.
         * ------------------------------------------------------------------ */
        var ownerRegionCache = {};

        /* A store names its region directly. */
        async function regionOfStore(vendorId) {
            if (!vendorId) {
                return null;
            }
            var key = 'store:' + vendorId;
            if (ownerRegionCache[key] !== undefined) {
                return ownerRegionCache[key];
            }

            var regionId = null;
            try {
                var snapshot = await database.collection('vendors').doc(vendorId).get();
                if (snapshot.exists) {
                    regionId = snapshot.data().regionId || null;
                }
            } catch (err) {
                console.error("Error resolving a store's region:", err);
            }

            ownerRegionCache[key] = regionId;
            return regionId;
        }

        /* Vendors, providers and drivers carry a single regionId. A customer can
         * have traded in several, so `regionIds` is an array - the first is used,
         * which is the one the backfill recorded first. */
        async function regionOfUser(userId) {
            if (!userId) {
                return null;
            }
            var key = 'user:' + userId;
            if (ownerRegionCache[key] !== undefined) {
                return ownerRegionCache[key];
            }

            var regionId = null;
            try {
                var snapshot = await database.collection('users').doc(userId).get();
                if (snapshot.exists) {
                    var data = snapshot.data();
                    regionId = data.regionId || null;
                    if (!regionId && Array.isArray(data.regionIds) && data.regionIds.length > 0) {
                        regionId = data.regionIds[0];
                    }
                }
            } catch (err) {
                console.error("Error resolving a user's region:", err);
            }

            ownerRegionCache[key] = regionId;
            return regionId;
        }

        /* The currency symbol and decimals for one store, ready to format with.
         * Falls back to the global currency, so a record with no region still
         * renders. */
        async function currencyOfStore(vendorId) {
            return await getCurrencyForRegion(await regionOfStore(vendorId));
        }

        async function currencyOfUser(userId) {
            return await getCurrencyForRegion(await regionOfUser(userId));
        }

        /* The currency an order should be read in.
         *
         * Its own regionId wins: an order is history, and it must keep reading
         * in the currency it was charged in even if its store later moves
         * region. This is what orders/edit and orders/print already do through
         * recordCurrencyRef(); the lists now agree with them.
         *
         * Orders written before regions existed carry no regionId, so those
         * fall back to the store's current region. */
        async function currencyOfOrder(order) {
            if (order && order.regionId) {
                return await getCurrencyForRegion(order.regionId);
            }

            return await currencyOfStore(order ? order.vendorID : null);
        }

        /* The currency a payment or transaction should be read in.
         *
         * Same rule as an order: money that has moved is history, so the
         * record's own regionId wins and the figure keeps reading in the
         * currency it was paid in. Records written before regions existed fall
         * back to the region of whoever they belong to - a store for a vendor
         * payout, a user for a driver payout or a wallet transaction. */
        async function currencyOfPayment(record, owner) {
            if (record && record.regionId) {
                return await getCurrencyForRegion(record.regionId);
            }

            if (owner && owner.vendorId) {
                return await currencyOfStore(owner.vendorId);
            }

            return await currencyOfUser(owner ? owner.userId : null);
        }

        /* Formats an amount in a given currency, honouring symbolAtRight and the
         * currency's own decimal places. */
        function formatInCurrency(amount, currency) {
            if (!currency) {
                return parseFloat(amount || 0).toFixed(2);
            }

            var decimals = (currency.decimal_degits !== undefined && currency.decimal_degits !== null)
                ? currency.decimal_degits : 2;
            var value = parseFloat(amount || 0).toFixed(decimals);

            return currency.symbolAtRight ? value + '' + currency.symbol : currency.symbol + '' + value;
        }

        function regionCurrencyRef() {
            return {
                limit: function () { return this; },
                orderBy: function () { return this; },
                get: async function () {
                    var currency = await getRegionCurrency();
                    if (!currency) {
                        return {docs: [], empty: true, size: 0};
                    }
                    return {
                        docs: [{
                            id: currency.id,
                            data: function () { return currency; }
                        }],
                        empty: false,
                        size: 1
                    };
                }
            };
        }

        /* Same query shape as regionCurrencyRef(), but pinned to the region a
         * record belongs to rather than the one the admin is viewing. */
        function recordCurrencyRef(regionId) {
            return {
                limit: function () { return this; },
                orderBy: function () { return this; },
                get: async function () {
                    var currency = await getCurrencyForRegion(regionId || getActiveRegionId());
                    if (!currency) {
                        return {docs: [], empty: true, size: 0};
                    }
                    return {
                        docs: [{
                            id: currency.id,
                            data: function () { return currency; }
                        }],
                        empty: false,
                        size: 1
                    };
                }
            };
        }

        function setActiveRegion(regionId) {
            regionId = regionId || '';
            if (regionId != '' && !isRegionAllowed(regionId)) {
                return false;
            }
            if (regionId == '' && isRegionBound()) {
                return false;
            }
            setCookie(REGION_COOKIE, regionId, 1);
            activeRegionId = regionId;
            activeRegionData = null;
            regionCurrencyCache = {};
            return true;
        }


        var sosInitialized = false; 
        database.collection('SOS').onSnapshot((snapshot) => {
            if (!sosInitialized) {               
                sosInitialized = true;
                return;
            }

            snapshot.docChanges().forEach((change) => {
                if (change.type === "added") {
                    var data = change.doc.data();
                    Swal.fire({
                        icon: 'warning',
                        title: 'SOS Alert!',
                        html: `New SOS initiated<br>`,
                        confirmButtonText: 'View Details',
                        confirmButtonColor: '#d33',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '/sos/edit/' + change.doc.id;
                        }
                    });
                }
            });
        });
        
        var ref = database.collection('settings').doc("globalSettings");
        ref.get().then(async function (snapshots) {
            var globalSettings = snapshots.data();
            $("#app_name").html(globalSettings.applicationName);
            $("#logo_web").attr('src', globalSettings.appLogo);
            document.documentElement.style.setProperty('--admin-panel-color', globalSettings.admin_panel_color);
        });
        
        var placeholderImage = '';
        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function (snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })
        
        /* Populate the header region switcher and react to changes. A bound
         * admin only ever sees the regions they are assigned to, and loses the
         * "All Regions" entry. */
        async function loadRegionSwitcher() {
            var $dropdown = $('#region_dropdown');
            if (!$dropdown.length) {
                return;
            }
            if (isRegionBound()) {
                $dropdown.find('option[value=""]').remove();
            }
            var regions = await getPublishedRegions();
            regions.forEach(function (region) {
                $dropdown.append($('<option></option>').attr('value', region.id).text(region.name));
            });
            $dropdown.val(getActiveRegionId());
        }

        $(document).on('change', '#region_dropdown', function () {
            if (setActiveRegion($(this).val())) {
                window.location.reload();
            } else {
                $(this).val(getActiveRegionId());
            }
        });

        $(document).ready(async function () {
            loadRegionSwitcher();
            getServiceSections();
            $(document).on('click', '.service-list-box', function() {
                let sectionUrl = $(this).data('section-url');
                let sectionId = $(this).data('section-id');
                let sectionType = $(this).data('section-type');
                if(sectionId && sectionType){
                    setCookie('section_id', sectionId, 1);
                    setCookie('service_type', sectionType, 1);
                }
                window.location.href = sectionUrl;
                /*window.location.reload();*/
            });
        });
        
        var langcount = 0;
        var languages_list = database.collection('settings').doc('languages');
        languages_list.get().then(async function (snapshotslang) {
            snapshotslang = snapshotslang.data();
            if (snapshotslang != undefined) {
                snapshotslang = snapshotslang.list;
                languages_list_main = snapshotslang;
                snapshotslang.forEach((data) => {
                    if (data.isActive == true) {
                        langcount++;
                        $('#language_dropdown').append($("<option></option>").attr("value", data.slug).text(data.title));
                    }
                });
                if (langcount > 1) {
                    $("#language_dropdown_box").css('visibility', 'visible');
                }
                <?php if (session()->get('locale')) { ?>
                    $("#language_dropdown").val("<?php    echo session()->get('locale'); ?>");
                <?php } ?>
            }
        });

        var url = "{{ route('changeLang') }}";
        $(".changeLang").change(function () {
            var slug = $(this).val();
            languages_list_main.forEach((data) => {
                if (slug == data.slug) {
                    if (data.is_rtl == undefined) {
                        setCookie('is_rtl', 'false', 365);
                    } else {
                        setCookie('is_rtl', data.is_rtl.toString(), 365);
                    }
                    window.location.href = url + "?lang=" + slug;
                }
            });
        });

        var version = database.collection('settings').doc("Version");
        version.get().then(async function (snapshots) {
            var version_data = snapshots.data();
            if (version_data == undefined) {
                database.collection('settings').doc('Version').set({});
            }
            try {
                $('.web_version').html("V:" + version_data.web_version);
            } catch (error) {
            }
        });
        
        async function sendEmail(url, subject, message, recipients) {
            var checkFlag = false;
            await $.ajax({
                type: 'POST',
                data: {
                    subject: subject,
                    message: btoa(message),
                    recipients: recipients
                },
                url: url,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    checkFlag = true;
                },
                error: function (xhr, status, error) {
                    checkFlag = true;
                }
            });
            return checkFlag;
        }

        database.collection('settings').doc('DriverNearBy').get().then(async function (snapshots) {
            var data = snapshots.data();
            if (data && data.selectedMapType && data.selectedMapType == "osm") {
                mapType = "OFFLINE"
            }
        });
        
        async function getServiceSections() {
            let ref = database.collection('sections').where('isActive', '==', true).orderBy('order');
            const sectionsSnapshot = await ref.get();
            const sectionsContainer = document.getElementById('sections_header');
            sectionsContainer.innerHTML = await buildServiceSectionsHTML(sectionsSnapshot);
        }
        
        async function buildServiceSectionsHTML(snapshot) {
            let html = '';
            var addSectionRoute = "{{ route('section.create') }}";
            var idSecActive = getCookie('section_id') || '';
            var typeSecActive = getCookie('service_type') || '';
            snapshot.docs.forEach(doc => {
                var data = doc.data();
                /* A service not offered in the region being worked in is not
                 * shown. An empty regionIds means offered everywhere, so
                 * sections created before regions existed still appear. */
                if (!isInAnyActiveRegion(data) && (data.regionIds || []).length > 0) {
                    return;
                }
                var sectionName = data.name || 'Unnamed Section';
                var sectionDescription = data.description || '';
                var sectionImage = data.sectionImage || placeholderImage;
                var sectionId = doc.id;
                var sectionRoute = `{{ route('dashboard') }}/${sectionId}/${data.serviceTypeFlag}`;
                var isSelected = (sectionId === idSecActive && (data.serviceTypeFlag || '') === typeSecActive);
                var selectedClass = isSelected ? 'selected-section' : '';
                if (isSelected) {
                    $('#activeSectionLogo').attr('src', sectionImage || placeholderImage);
                    $('#activeSectionName').text(sectionName);
                }
                html += `
                <div class="col-md-4">
                    <div class="service-list-box ${selectedClass}" data-section-url="${sectionRoute}" data-section-id="${data.id}" data-section-type="${data.serviceTypeFlag}">
                        <img src="${sectionImage}" onerror="this.onerror=null;this.src='${placeholderImage}'">
                        <h3>${sectionName}</h3>
                        <p>${sectionDescription}</p>
                    </div>
                </div>
                `;
            });
            html += `
                <div class="col-md-12">
                    <div class="service-list-box" data-section-url="${addSectionRoute}" data-section-id="" data-section-type="">
                        <img src="{{ asset('images/add_more.png') }}">
                        <h3>{{trans('lang.add_more')}}</h3>
                        <p>{{trans('lang.expand_by_adding_new_modules_as_your_business_grows')}}</p>
                    </div>
                </div>
                `;
            html = `<div class="dropdown-service-list"><div class="row">${html}</div></div>`;
            return html;
        }

        async function loadGoogleMapsScript() {
            var googleMapKeySnapshotsHeader = await database.collection('settings').doc("googleMapKey").get();
            var placeholderImageHeaderData = googleMapKeySnapshotsHeader.data();
            googleMapKey = placeholderImageHeaderData.key;
            const script = document.createElement('script');
            if (mapType == "OFFLINE") {
                script.src = "https://unpkg.com/leaflet@1.7.1/dist/leaflet.js";
                script.src = "https://unpkg.com/leaflet-draw/dist/leaflet.draw.js";
                script.src = "https://cdnjs.cloudflare.com/ajax/libs/leaflet-editable/0.7.3/leaflet.editable.min.js";
                script.src = "https://unpkg.com/leaflet-draw@0.4.14/dist/leaflet.draw-src.js";
                script.src = "https://unpkg.com/leaflet-ajax/dist/leaflet.ajax.min.js";
                script.src = "https://unpkg.com/leaflet-geojson-layer/src/leaflet.geojson.js";
                script.src = "https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js";
            } else {
                // script.src = "https://maps.googleapis.com/maps/api/js?key=" + googleMapKey + "&libraries=places,drawing";
                script.src = "https://maps.googleapis.com/maps/api/js?key=" + googleMapKey + "&libraries=places,drawing&v=quarterly";
                script.async = true;
                script.defer = true;
            }
            script.onload = function () {
                navigator.geolocation.getCurrentPosition(GeolocationSuccessCallback, GeolocationErrorCallback);
                if (typeof window['InitializeGodsEyeMap'] === 'function') {
                    InitializeGodsEyeMap();
                }

                if (typeof window['initMap'] === 'function') {
                    window['initMap']();
                }
            };
            document.head.appendChild(script);
        }

        const GeolocationSuccessCallback = (position) => {
            if (position.coords != undefined) {
                default_latitude = position.coords.latitude
                default_longitude = position.coords.longitude
                setCookie('default_latitude', default_latitude, 365);
                setCookie('default_longitude', default_longitude, 365);
            }
        };
        const GeolocationErrorCallback = (error) => {
            console.log('Error: You denied for your default Geolocation', error.message);
            setCookie('default_latitude', '23.022505', 365);
            setCookie('default_longitude', '72.571365', 365);
        };

        loadGoogleMapsScript();
        
        database.collection('settings').doc("notification_setting").get().then(async function (snapshots) {
            var data = snapshots.data();
            serviceJson = data.serviceJson;
            if (serviceJson != '' && serviceJson != null) {
                $.ajax({
                    type: 'POST',
                    data: {
                        serviceJson: btoa(serviceJson),
                    },
                    url: "{{ route('store-firebase-service') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        checkFlag = true;
                    }
                });
            }
        });

        //On delete item delete image also from bucket general code
        const deleteDocumentWithImage = async (collection, id, singleImageField, arrayImageField, profileImageField, carProofPictureURL, driverProofPictureURL) => {
            // Reference to the Firestore document
            const docRef = database.collection(collection).doc(id);
            try {
                const doc = await docRef.get();
                if (!doc.exists) {
                    console.log("No document found for deletion");
                    return;
                }
                const data = doc.data();
                // Handle single image deletion
                // Deleting single image field
                if (singleImageField) {
                    if (Array.isArray(singleImageField)) {
                        for (const field of singleImageField) {
                            const imageUrl = data[field];
                            if (imageUrl) await deleteImageFromBucket(imageUrl);
                        }
                    } else {
                        const imageUrl = data[singleImageField];
                        if (imageUrl) await deleteImageFromBucket(imageUrl);
                    }
                }
                // Deleting array image field
                if (arrayImageField) {
                    if (Array.isArray(arrayImageField)) {
                        for (const field of arrayImageField) {
                            const arrayImages = data[field];
                            if (arrayImages && Array.isArray(arrayImages)) {
                                for (const imageUrl of arrayImages) {
                                    if (imageUrl) await deleteImageFromBucket(imageUrl);
                                }
                            }
                        }
                    } else {
                        const arrayImages = data[arrayImageField];
                        if (arrayImages && Array.isArray(arrayImages)) {
                            for (const imageUrl of arrayImages) {
                                if (imageUrl) await deleteImageFromBucket(imageUrl);
                            }
                        }
                    }
                }
                // Handle variant images deletion
                const item_attribute = data.item_attribute || {};  // Access item_attribute
                const variants = item_attribute.variants || [];    // Access variants array inside item_attribute
                if (variants.length > 0) {
                    for (let i = 0; i < variants.length; i++) {
                        const variantImageUrl = variants[i].variant_image;
                        if (variantImageUrl) {
                            await deleteImageFromBucket(variantImageUrl);
                        }
                    }
                }
                // Handle profile_file_name image deletion
                const profile_file_name = data[profileImageField] || '';  // profile image field
                if (profile_file_name) {
                    await deleteImageFromBucket(profile_file_name);
                }
                // Handle carproof_file_name image deletion
                const carproof_file_name = data[carProofPictureURL] || '';  // carproof image field
                if (carproof_file_name) {
                    await deleteImageFromBucket(carproof_file_name);
                }
                // Handle driverproof_file_name image deletion
                const driverproof_file_name = data[driverProofPictureURL] || '';  // driverproof image field
                if (driverproof_file_name) {
                    await deleteImageFromBucket(driverproof_file_name);
                }
                // Optionally delete the Firestore document after image deletion
                await docRef.delete();
                console.log("Document and images deleted successfully.");
            } catch (error) {
                console.error("Error deleting document and images:", error);
            }
        };

        const deleteImageFromBucket = async (imageUrl) => {
            try {
                const storageRef = firebase.storage().ref();
                // Check if the imageUrl is a full URL or just a child path
                let oldImageUrlRef;
                if (imageUrl.includes('https://')) {
                    // Full URL
                    oldImageUrlRef = storageRef.storage.refFromURL(imageUrl);
                } else {
                    // Child path, use ref instead of refFromURL
                    oldImageUrlRef = storageRef.storage.ref(imageUrl);
                }
                var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                var imageBucket = oldImageUrlRef.bucket;
                // Check if the bucket name matches
                if (imageBucket === envBucket) {
                    // Delete the image
                    await oldImageUrlRef.delete();
                    console.log("Image deleted successfully.");
                }
            } catch (error) {
            }
        };

        function exportData(dt, format, config) {
            const {
                columns,
                fileName = 'Export',
            } = config;
            const filteredRecords = dt.ajax.json().filteredData;
            const fieldTypes = {};
            const dataMapper = (record) => {
                return columns.map((col) => {
                    const value = record[col.key];
                    if (!fieldTypes[col.key]) {
                        if (value === true || value === false) {
                            fieldTypes[col.key] = 'boolean';
                        } else if (value && typeof value === 'object' && value.seconds) {
                            fieldTypes[col.key] = 'date';
                        } else if (typeof value === 'number') {
                            fieldTypes[col.key] = 'number';
                        } else if (typeof value === 'string') {
                            fieldTypes[col.key] = 'string';
                        } else {
                            fieldTypes[col.key] = 'string';
                        }
                    }
                    switch (fieldTypes[col.key]) {
                        case 'boolean':
                            return value ? 'Yes' : 'No';
                       /*  case 'date':
                            return value ? new Date(value.seconds * 1000).toLocaleString() : '-'; */
                        case 'date':
                            return value?.toDate ? value.toDate().toLocaleString() :
                                (value.seconds ? new Date(value.seconds * 1000).toLocaleString() : '-');
                        case 'number':
                            return typeof value === 'number' ? value : 0;
                        case 'string':
                        default:
                            return value || '-';
                    }
                });
            };
            const tableData = filteredRecords.map(dataMapper);
            const data = [columns.map(col => col.header), ...tableData];
            const columnWidths = columns.map((_, colIndex) =>
                Math.max(...data.map(row => row[colIndex]?.toString().length || 0))
            );
            if (format === 'csv') {
                const csv = data.map(row => row.map(cell => {
                    if (typeof cell === 'string' && (cell.includes(',') || cell.includes('\n') || cell.includes('"'))) {
                        return `"${cell.replace(/"/g, '""')}"`;
                    }
                    return cell;
                }).join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                saveAs(blob, `${fileName}.csv`);
            } else if (format === 'excel') {
                const ws = XLSX.utils.aoa_to_sheet(data, { cellDates: true });
                ws['!cols'] = columnWidths.map(width => ({ wch: Math.min(width + 5, 30) }));
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Data');
                XLSX.writeFile(wb, `${fileName}.xlsx`);
            } else if (format === 'pdf') {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('l', 'mm', 'a4');
                doc.setFontSize(12);
                doc.text(fileName, 14, 16);
                doc.autoTable({
                    head: [columns.map(col => col.header)],
                    body: tableData,
                    startY: 20,
                    theme: 'striped',
                    styles: {
                        cellPadding: 1,
                        fontSize: 8,
                        overflow: 'linebreak',
                    },
                    columnStyles: {
                        0: { cellWidth: 'auto' },
                    },
                    margin: { top: 30, bottom: 30 },
                    pageBreak: 'auto',
                });
                doc.save(`${fileName}.pdf`);
            } else {
                console.error('Unsupported format');
            }
        }

        function showError(msg) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append(`<p>${msg}</p>`);
            window.scrollTo(0, 0);
            return false;
        }

        function encodeGeohash(latitude, longitude, precision = 10) {

            const BASE32 = "0123456789bcdefghjkmnpqrstuvwxyz";
            let idx = 0;
            let bit = 0;
            let even = true;
            let geohash = "";

            let latMin = -90, latMax = 90;
            let lonMin = -180, lonMax = 180;

            while (geohash.length < precision) {
                if (even) {
                    let mid = (lonMin + lonMax) / 2;
                    if (longitude > mid) {
                        idx = idx * 2 + 1;
                        lonMin = mid;
                    } else {
                        idx = idx * 2;
                        lonMax = mid;
                    }
                } else {
                    let mid = (latMin + latMax) / 2;
                    if (latitude > mid) {
                        idx = idx * 2 + 1;
                        latMin = mid;
                    } else {
                        idx = idx * 2;
                        latMax = mid;
                    }
                }
                even = !even;

                if (++bit == 5) {
                    geohash += BASE32.charAt(idx);
                    bit = 0;
                    idx = 0;
                }
            }

            return geohash;
        }

        function showProcessing() {
            Swal.fire({
                title: window.translations.showProcessingTitle,
                text: window.translations.showProcessingText,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        function showSuccessMessage(message) {
            Swal.fire({
                icon: 'success',
                title: window.translations.successMessageTitle,
                text: message,
                showConfirmButton: false,
                timer: 2000,
            });
        }

        function showErrorMessage(message) {
            Swal.fire({
                icon: 'error',
                title: window.translations.errorMessageTitle,
                text: message,
                showConfirmButton: true
            });
        }

        window.translations = {
            showProcessingTitle:"{{trans('lang.please_wait')}}",
            showProcessingText:"{{trans('lang.processing_your_request')}}",
            errorMessageTitle:"{{trans('lang.error')}}",
            warningMessageTitle:"{{trans('lang.warning')}}",
            successMessageTitle:"{{trans('lang.success')}}",
        };

        async function getUserZoneId(address_lng,address_lat){
            var zone_id = null;
            var zone_list = [];
            var snapshots = await database.collection('zone').where("publish","==",true).get();
            if(snapshots.docs.length > 0){
                snapshots.docs.forEach((snapshot) => {
                    var zone_data = snapshot.data();
                    zone_list.push(zone_data);
                });   
            }
            if(zone_list.length > 0){
                for (i = 0; i < zone_list.length; i++) {
                    var zone = zone_list[i];
                    var vertices_x = [];
                    var vertices_y = [];
                    for (j = 0; j < zone.area.length; j++) {
                        var geopoint = zone.area[j];
                        vertices_x.push(geopoint.longitude);
                        vertices_y.push(geopoint.latitude);
                    }
                    var points_polygon = (vertices_x.length)-1; 
                    if(is_in_polygon(points_polygon, vertices_x, vertices_y, address_lng, address_lat)){
                        zone_id = zone.id;
                        break; 
                    }
                }
            }
            return zone_id;
        }

        function is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y){
            $i = $j = $c = $point = 0;
            for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
                $point = $i;
                if( $point === $points_polygon )
                    $point = 0;
                if ( (($vertices_y[$point]  >  $latitude_y !== ($vertices_y[$j] > $latitude_y)) && ($longitude_x < ($vertices_x[$j] - $vertices_x[$point]) * ($latitude_y - $vertices_y[$point]) / ($vertices_y[$j] - $vertices_y[$point]) + $vertices_x[$point]) ) )
                    $c = !$c;
            }
            return $c;
        }

        const distanceRadius = (lat1, lon1, lat2, lon2) => {
            if ((lat1 === lat2) && (lon1 === lon2)) {
                return 0;
            }
            else {
                var radlat1 = Math.PI * lat1/180;
                var radlat2 = Math.PI * lat2/180;
                var theta = lon1-lon2;
                var radtheta = Math.PI * theta/180;
                var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
                if (dist > 1) {
                    dist = 1;
                }
                dist = Math.acos(dist);
                dist = dist * 180/Math.PI;
                dist = dist * 60 * 1.1515;
                dist = dist * 1.60934; // Convert to kilometers
                
                return dist;
            }
        }

        function formatCurrency(amount, currency = {}) {
            const symbol = currency.symbol || '';
            const decimals = currency.decimal_degits ?? 2;
            const symbolAtRight = Boolean(currency.symbolAtRight);
            const formatted = parseFloat(amount).toFixed(decimals);
            return symbolAtRight
                ? formatted + ' ' + symbol
                : symbol + formatted;
        }
        
        async function getCountryFromLatLng(lat, lng) {
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`;
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            return data?.address?.country || '';
        }

        async function sendNotification(fcmToken = '', title, body, payload = null) {            
            var checkFlag = false;
            var sendNotificationUrl = "{{ route('send-notification') }}";
            if (fcmToken !== '') {
                await $.ajax({
                    type: 'POST',
                    url: sendNotificationUrl,
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        'fcm': fcmToken,
                        'title': title,
                        'message': body,
                        'payload': JSON.stringify(payload)
                    },
                    success: function (data) {
                        checkFlag = true;
                    },
                    error: function (error) {
                        checkFlag = true;
                    }
                });
            } else {
                checkFlag = true;
            }

            return checkFlag;
        }

        /* ---- The vendor balance ----
         *
         * A store earns and spends its own money, so the balance lives on the
         * `vendors` document. The account copy on `users` is kept moving with it
         * because the customer app still credits and reads that one, and the app
         * is outside both repos.
         *
         * Both sides move by the same delta. The account total is deliberately
         * NOT recomputed as the sum of its stores: store balances start at zero,
         * so a sum would wipe out everything a vendor earned before their stores
         * had balances of their own.
         *
         * This is the same helper as in the store panel's layout - keep the two
         * in step. */
        function toAmount(value) {
            const amount = parseFloat(value);

            return isNaN(amount) ? 0 : amount;
        }

        async function storeWalletAmount(storeId) {
            if (!storeId) {
                return 0;
            }

            const snapshot = await firebase.firestore().collection('vendors').doc(storeId).get();

            return snapshot.exists ? toAmount(snapshot.data().wallet_amount) : 0;
        }

        /* The groups a service can appear under on the customer home screen.
         * Managed in Settings > Service Groups, so the list is read from
         * Firestore rather than fixed in the form.
         *
         * A group's `id` is what a section stores. It never changes, so
         * renaming a group cannot detach the services in it. */
        async function loadServiceGroups(selected) {
            var $select = $('#service_group');

            if (!$select.length) {
                return;
            }

            var snapshots = await firebase.firestore().collection('service_groups').get();
            var groups = snapshots.docs.map(function (doc) {
                return doc.data();
            }).filter(function (group) {
                return group.publish !== false;
            });

            /* Sorted here rather than with orderBy: Firestore drops documents
             * that lack the field being ordered by. */
            groups.sort(function (a, b) {
                return (parseInt(a.order) || 0) - (parseInt(b.order) || 0);
            });

            groups.forEach(function (group) {
                $select.append($('<option></option>').attr('value', group.id).text(group.name));
            });

            /* A section may hold a group that was later unpublished or
             * deleted. Kept as an option so saving the section does not
             * silently clear it. */
            if (selected && !$select.find('option[value="' + selected + '"]').length) {
                $select.append($('<option></option>').attr('value', selected).text(selected));
            }

            $select.val(selected || '');
            $select.select2({
                width: '100%',
                placeholder: "{{ trans('lang.service_group_none') }}"
            });
        }

        /* Group id -> name, for screens that only display it. */
        async function serviceGroupNames() {
            var names = {};
            var snapshots = await firebase.firestore().collection('service_groups').get();

            snapshots.docs.forEach(function (doc) {
                var group = doc.data();
                names[group.id] = group.name;
            });

            return names;
        }

        /* A delivery zone may serve several regions at once - one "Worldwide"
         * zone can be offered by Cameroon and France together.
         *
         * `regionIds` is the truth. `regionId` is the old single-value field,
         * still written for anything not yet updated (the apps, the store
         * panel), and read here so zones saved before the change keep working
         * until they are next saved. */
        function zoneRegionIds(zone) {
            if (!zone) {
                return [];
            }
            if (Array.isArray(zone.regionIds)) {
                return zone.regionIds;
            }
            return zone.regionId ? [zone.regionId] : [];
        }

        /* Records carry ONE region, so a zone serving several cannot decide it
         * on its own. The region being worked in settles it when the zone
         * serves it; a zone serving exactly one region still decides by itself;
         * anything else is genuinely ambiguous and returns ''. */
        function regionForZone(zone) {
            var regionIds = zoneRegionIds(zone);
            var active = getActiveRegionId();

            if (active && regionIds.indexOf(active) !== -1) {
                return active;
            }
            if (regionIds.length === 1) {
                return regionIds[0];
            }
            return active || '';
        }

        /* A payout row names one party and carries no region of its own: a
         * vendor payout names a store, a provider payout names a provider, a
         * driver payout names a driver. The party already carries `regionId`,
         * so the region is resolved here rather than stamped onto every payout
         * - which would need a backfill AND a change in both panels that write
         * payouts.
         *
         * Both collections are keyed by document id, but their documents also
         * carry an `id` field, and the payout rows use that. Both are mapped so
         * either spelling resolves.
         *
         * Cached for the life of the page: these lists are fetched whole and
         * paged in the browser, so this runs once per screen. */
        var PARTY_REGIONS = null;

        async function partyRegionMap() {
            if (PARTY_REGIONS) {
                return PARTY_REGIONS;
            }

            var db = firebase.firestore();
            var map = {};

            function remember(doc) {
                var data = doc.data();

                if (!data.regionId) {
                    return;
                }

                map[doc.id] = data.regionId;

                if (data.id) {
                    map[data.id] = data.regionId;
                }
            }

            var stores = await db.collection('vendors').get();
            stores.docs.forEach(remember);

            var people = await db.collection('users').get();
            people.docs.forEach(remember);

            PARTY_REGIONS = map;

            return map;
        }

        /* True when a payout belongs in the region being worked in. `partyId`
         * is the row's vendorID or driverID.
         *
         * A payout whose party has no region yet is hidden while a region is
         * selected, exactly as an unplaced order is - not silently shown. */
        function payoutInActiveRegion(map, partyId) {
            var active = getActiveRegionId();

            if (!active) {
                return true;
            }

            return !!partyId && map[partyId] === active;
        }

        /* A store's owner is named on the store document, in `author`.
         *
         * Finding them with `users where vendorID == <storeId>` asks a different
         * question - "whose SELECTED store is this?" - which was right only while
         * a vendor could hold one store. On a vendor with several it returns
         * whoever happens to have that store open, or nobody at all.
         *
         * Returns the owner's user data, or null when the store or the owner
         * cannot be found. Callers must handle null: the old queries failed
         * silently, and this one must not. */
        async function storeOwnerData(storeId) {
            if (!storeId) {
                return null;
            }

            const db = firebase.firestore();
            const storeSnapshot = await db.collection('vendors').doc(storeId).get();
            const ownerId = storeSnapshot.exists ? (storeSnapshot.data().author || '') : '';

            if (!ownerId) {
                return null;
            }

            const ownerSnapshot = await db.collection('users').doc(ownerId).get();

            return ownerSnapshot.exists ? ownerSnapshot.data() : null;
        }

        /* `delta` is signed: positive credits, negative debits. The owner is
         * looked up from the store when it is not already known. */
        async function applyVendorWalletDelta(storeId, ownerUserId, delta) {
            const db = firebase.firestore();
            const amount = toAmount(delta);

            if (amount === 0) {
                return;
            }

            if (storeId) {
                try {
                    const storeRef = db.collection('vendors').doc(storeId);
                    const snapshot = await storeRef.get();

                    if (snapshot.exists) {
                        /* The store names its owner, so a caller that knows
                         * only the store still moves both balances. */
                        if (!ownerUserId) {
                            ownerUserId = snapshot.data().author || '';
                        }

                        await storeRef.update({
                            'wallet_amount': Number((toAmount(snapshot.data().wallet_amount) + amount).toFixed(2))
                        });
                    }
                } catch (err) {
                    console.error("Could not update the store balance:", err);
                }
            }

            if (ownerUserId) {
                try {
                    const userRef = db.collection('users').doc(ownerUserId);
                    const snapshot = await userRef.get();

                    if (snapshot.exists) {
                        await userRef.update({
                            'wallet_amount': Number((toAmount(snapshot.data().wallet_amount) + amount).toFixed(2))
                        });
                    }
                } catch (err) {
                    console.error("Could not update the account balance:", err);
                }
            }
        }

    </script>

    @yield('scripts')

</body>
</html>