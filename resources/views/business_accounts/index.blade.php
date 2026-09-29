@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.business_account_plural') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.business_account_plural') }}</li>
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
                                <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.business_account_plural') }}</h3>
                                <p class="mb-0 text-dark-2">{{ trans('lang.business_account_help') }}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                                <div class="select-box pl-3">
                                    <select class="form-control" id="status_filter">
                                        <option value="pending">{{ trans('lang.business_account_status_pending') }}</option>
                                        <option value="approved">{{ trans('lang.business_account_status_approved') }}</option>
                                        <option value="rejected">{{ trans('lang.business_account_status_rejected') }}</option>
                                        <option value="" selected>{{ trans('lang.business_account_status_all') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="error_top" style="display:none"></div>
                            <div class="success_top" style="display:none"></div>
                            <div class="table-responsive m-t-10">
                                <table id="businessAccountTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <?php if (in_array('business-account.review', json_decode(@session('user_permissions'), true) ?: [])) { ?>
                                            <th class="delete-all">
                                                <input type="checkbox" id="is_active">
                                                <label class="col-3 control-label" for="is_active">
                                                    <a id="approveAll" class="do_not_delete" href="javascript:void(0)" data-toggle="tooltip" title="{{ trans('lang.business_account_approve_selected') }}" data-bs-original-title="{{ trans('lang.business_account_approve_selected') }}"><i class="mdi mdi-check-circle"></i></a>
                                                    <a id="rejectAll" class="do_not_delete" href="javascript:void(0)" data-toggle="tooltip" title="{{ trans('lang.business_account_reject_selected') }}" data-bs-original-title="{{ trans('lang.business_account_reject_selected') }}"><i class="mdi mdi-close-circle"></i></a>
                                                    {{ trans('lang.all') }}
                                                </label>
                                            </th>
                                            <?php } ?>
                                            <th>{{ trans('lang.business_account_customer') }}</th>
                                            <th>{{ trans('lang.business_account_company') }}</th>
                                            <th>{{ trans('lang.business_account_registration') }}</th>
                                            <th>{{ trans('lang.business_account_document') }}</th>
                                            <th>{{ trans('lang.business_account_submitted') }}</th>
                                            <th>{{ trans('lang.business_account_approved_on') }}</th>
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

<div class="modal fade" id="reasonModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('lang.business_account_reason_title') }}</h5>
            </div>
            <div class="modal-body">
                <ul class="p-0 info-list mb-0">
                    <li class="d-flex align-items-center mb-2">
                        <label class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.business_account_customer') }}</label>
                        <span id="reason_customer"></span>
                    </li>
                    <li class="d-flex align-items-center mb-2">
                        <label class="mb-0 font-wi font-semibold text-dark-2">{{ trans('lang.business_account_company') }}</label>
                        <span id="reason_company"></span>
                    </li>
                </ul>
                <hr class="mt-2 mb-3">
                <label class="mb-1 font-semibold text-dark-2 d-block">{{ trans('lang.business_account_reason') }}</label>
                <p class="mb-0" id="reason_text"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">{{ trans('lang.close') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('lang.business_account_reject_title') }}</h5>
            </div>
            <div class="modal-body">
                <p class="mb-2">{{ trans('lang.business_account_reject_help') }}</p>
                <textarea class="form-control" id="reject_reason" rows="3"></textarea>
                <div class="reject_error text-danger mt-2" style="display:none">{{ trans('lang.business_account_reject_required') }}</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">{{ trans('lang.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="reject_confirm">{{ trans('lang.business_account_reject') }}</button>
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
    var checkReviewPermission = ($.inArray('business-account.review', user_permissions) >= 0);

    var rejectingId = '';
    var rejectingIds = [];

    var placeholderImage = '';
    var placeholderReady = database.collection('settings').doc('placeHolderImage').get()
        .then(function (snapshot) {
            var data = snapshot.data();
            placeholderImage = (data && data.image) ? data.image : '';
        })
        .catch(function () {
            placeholderImage = '';
        });

    $(document).ready(function () {
        loadRequests();

        $('#status_filter').on('change', function () {
            loadRequests();
        });
    });

    async function loadRequests() {
        jQuery("#data-table_processing").show();
        $('.error_top').hide();

        await placeholderReady;

        /* DataTables must be torn down before the rows under it are replaced.
         * Destroying it leaves the markup in place; leaving it alive means the
         * new rows are invisible to sorting, search and paging. */
        if ($.fn.DataTable.isDataTable('#businessAccountTable')) {
            $('#businessAccountTable').DataTable().destroy();
        }

        /* Fetched whole and filtered in the browser rather than with a scoped
         * query. Business requests are few, and this needs no composite index -
         * a query on accountType plus an array-contains on regionIds would. */
        var snapshots = await database.collection('users').where('accountType', '==', 'business').get();

        var wanted = $('#status_filter').val();
        var rows = [];

        snapshots.docs.forEach(function (doc) {
            var user = doc.data();

            /* A customer belongs to every region they have ordered in, so the
             * many-regions guard is the right one here, not isInActiveRegion.
             * A customer who has never ordered carries no regionIds at all and
             * is deliberately kept - hiding a request because the person has
             * not bought anything yet would hide exactly the new signups this
             * screen exists to process. */
            if (!inRegionOrUnplaced(user)) {
                return;
            }

            var profile = user.businessProfile || {};
            var status = profile.status || 'pending';

            if (wanted !== '' && status !== wanted) {
                return;
            }

            rows.push({ user: user, profile: profile, status: status });
        });

        rows.sort(function (a, b) {
            return submittedValue(b.profile) - submittedValue(a.profile);
        });

        var html = '';

        rows.forEach(function (row) {
            html += buildRow(row);
        });

        $('#append_list').html(html || '<tr><td colspan="' + (checkReviewPermission ? 9 : 8) + '">{{ trans('lang.business_account_empty') }}</td></tr>');

        /* Re-initialised AFTER the rows are injected, not on page load. The
         * rows do not exist yet when the page is ready, so a one-off init at
         * startup binds nothing - which is why the markup carried the tooltip
         * attributes and still showed no tooltip. Runs again on every filter
         * change for the same reason. */
        if (rows.length > 0) {
            /* Same client-side setup the carrier and service-group lists use,
             * so this table gets the theme's search box, "show N entries",
             * paging and striping rather than a bare HTML table.
             * Checkbox, document and actions are not sortable. */
            $('#businessAccountTable').DataTable({
                order: [[checkReviewPermission ? 5 : 4, 'desc']],
                columnDefs: [{
                    orderable: false,
                    targets: checkReviewPermission ? [0, 4, 8] : [3, 7]
                }],
                "language": datatableLang,
                responsive: true
            });
        }

        /* Re-initialised AFTER the rows are injected, not on page load. The
         * rows do not exist yet when the page is ready, so a one-off init at
         * startup binds nothing - which is why the markup carried the tooltip
         * attributes and still showed no tooltip. Runs again on every filter
         * change for the same reason. */
        $('[data-toggle="tooltip"]').tooltip('dispose');
        $('[data-toggle="tooltip"]').tooltip();

        jQuery("#data-table_processing").hide();
    }

    /* getActiveRegionId() empty means "All Regions" and everything passes. */
    function inRegionOrUnplaced(user) {
        if (!getActiveRegionId()) {
            return true;
        }

        if (!Array.isArray(user.regionIds) || user.regionIds.length === 0) {
            return true;
        }

        return isInAnyActiveRegion(user);
    }

    /* submittedAt is written by the app as an ISO STRING, not a Firestore
     * Timestamp - confirmed against live data. Both shapes are handled so a
     * later change on the app side cannot break the sort. */
    function submittedValue(profile) {
        var raw = profile.submittedAt;

        if (!raw) {
            return 0;
        }

        if (typeof raw.toDate === 'function') {
            return raw.toDate().getTime();
        }

        var parsed = Date.parse(raw);

        return isNaN(parsed) ? 0 : parsed;
    }

    /* Same two-line shape the users and stores lists use:
     *     Mon Sep 28 2026
     *     7:54:03 PM
     * `dt-time` is the theme's class for that cell. */
    function submittedLabel(profile) {
        var value = submittedValue(profile);

        if (!value) {
            return '-';
        }

        var when = new Date(value);

        return escapeHtml(when.toDateString()) + '<br> ' + escapeHtml(when.toLocaleTimeString('en-US'));
    }

    /* Only for an APPROVED account. reviewedAt is the date of whatever the
     * last decision was, so showing it against a rejected row would label a
     * refusal as an approval.
     *
     * Absent for anything decided before this screen existed - those were
     * never reviewed here, so there is no date to show. */
    function approvedLabel(status, profile) {
        if (status !== 'approved') {
            return '-';
        }

        var raw = profile.reviewedAt;
        var when = 0;

        if (raw && typeof raw.toDate === 'function') {
            when = raw.toDate().getTime();
        } else if (raw) {
            var parsed = Date.parse(raw);
            when = isNaN(parsed) ? 0 : parsed;
        }

        if (!when) {
            return '-';
        }

        var at = new Date(when);

        return escapeHtml(at.toDateString()) + '<br> ' + escapeHtml(at.toLocaleTimeString('en-US'));
    }

    function buildRow(row) {
        var user = row.user;
        var profile = row.profile;
        var name = ((user.firstName || '') + ' ' + (user.lastName || '')).trim();
        var contact = user.email || ((user.countryCode || '') + ' ' + (user.phoneNumber || '')).trim();
        var html = '<tr>';

        if (checkReviewPermission) {
            html += '<td><span class="delete-all"><input type="checkbox" id="ba_' + user.id + '" class="is_open" dataId="' + user.id + '" data-status="' + row.status + '">' +
                '<label class="col-3 control-label" for="ba_' + user.id + '"></label></span></td>';
        }

        var userView = '{{ route('users.view', ':id') }}'.replace(':id', user.id);


        /* Name links to the customer, with the verified tick once approved -
         * the same mark the customers list now shows. Email dropped: it is on
         * the customer's own screen, one click away. */
        html += '<td>' + thumb(user.profilePictureURL) +
            '<a href="' + userView + '" class="redirecttopage left_space">' + escapeHtml(name || '-') + '</a>' +
            verifiedMark(row.status) + '</td>';

        /* The only image a business request carries is the registration
         * document, so that is what is shown beside the company name. */
        html += '<td>' + thumb(profile.documentUrl) + '<span class="left_space">' + escapeHtml(profile.companyName || '-') + '</span></td>';
        html += '<td>' + escapeHtml(profile.registrationNumber || '-') + '</td>';

        if (profile.documentUrl) {
            html += '<td><a class="business-document-link" href="' + attrUrl(profile.documentUrl) + '" target="_blank" rel="noopener noreferrer"' +
                ' data-toggle="tooltip" title="{{ trans('lang.business_account_document_tooltip') }}" data-bs-original-title="{{ trans('lang.business_account_document_tooltip') }}">' +
                '<i class="mdi mdi-file-document"></i> {{ trans('lang.business_account_view_document') }}</a></td>';
        } else {
            html += '<td>-</td>';
        }

        html += '<td class="dt-time">' + submittedLabel(profile) + '</td>';
        html += '<td class="dt-time">' + approvedLabel(row.status, profile) + '</td>';
        html += '<td>' + statusBadge(row.status, profile) + '</td>';
        html += '<td>' + actions(user.id, row.status, profile, name) + '</td>';

        return html + '</tr>';
    }

    function statusBadge(status, profile) {
        if (status === 'approved') {
            return '<span class="badge badge-success">{{ trans('lang.business_account_status_approved') }}</span>';
        }

        if (status === 'rejected') {
            /* The reason is behind the speech-bubble button in Actions rather
             * than printed here - a long reason stretched the row and pushed
             * the table out of shape. */
            return '<span class="badge badge-danger">{{ trans('lang.business_account_status_rejected') }}</span>';
        }

        return '<span class="badge badge-warning">{{ trans('lang.business_account_status_pending') }}</span>';
    }

    function actions(userId, status, profile, customerName) {
        var html = '<span class="action-btn">';

        /* Shown to anyone who can see the list - reading why a request was
         * refused is not a review action. */
        if (status === 'rejected' && profile.rejectionReason) {
            html += '<a href="javascript:void(0)" class="reason-btn chat-message" style="position: relative; display: inline-block;" data-reason="' +
                attrUrl(profile.rejectionReason) + '" data-customer="' + attrUrl(customerName || '') +
                '" data-company="' + attrUrl(profile.companyName || '') + '" data-toggle="tooltip"' +
                ' title="{{ trans('lang.business_account_view_reason') }}"' +
                ' data-bs-original-title="{{ trans('lang.business_account_view_reason') }}">' +
                '<i class="mdi mdi-wechat mdi-24px"></i></a>';
        }

        if (!checkReviewPermission) {
            return html + '</span>';
        }

        /* BOTH `title` and `data-bs-original-title` on purpose.
         * Bootstrap 4 (assets/plugins/bootstrap/js/bootstrap.min.js) supplies
         * the jQuery .tooltip() plugin this panel calls, and it reads the text
         * from `title` - `data-bs-original-title` is Bootstrap 5 naming and BS4
         * never looks at it. A BS4 tooltip with an empty title is silently not
         * shown, which is why attributes alone produced nothing. `title` also
         * leaves the browser's own tooltip as a fallback if the plugin fails. */
        if (status !== 'approved') {
            html += '<a href="javascript:void(0)" class="approve-btn" data-id="' + userId + '" data-toggle="tooltip" title="{{ trans('lang.business_account_approve') }}" data-bs-original-title="{{ trans('lang.business_account_approve') }}"><i class="mdi mdi-check-circle"></i></a>';
        }

        if (status !== 'rejected') {
            html += '<a href="javascript:void(0)" class="reject-btn" data-id="' + userId + '" data-toggle="tooltip" title="{{ trans('lang.business_account_reject') }}" data-bs-original-title="{{ trans('lang.business_account_reject') }}"><i class="mdi mdi-close-circle"></i></a>';
        }

        return html + '</span>';
    }

    /* Falls back to the placeholder the panel already stores, and again in
     * onerror for a URL that is present but broken - a Storage link whose
     * token has been rotated, for instance. */
    function thumb(url) {
        var src = url ? url : placeholderImage;

        if (!src) {
            return '';
        }

        /* Built with DOUBLE-quoted JavaScript strings so the single quotes the
         * onerror handler needs are literal. Escaping them inside a
         * single-quoted string is what broke this the first time - and it broke
         * silently, because the template still compiled. */
        return "<img class=\"rounded business-account-thumb\" src=\"" + attrUrl(src) +
            "\" onerror=\"this.onerror=null;this.src='" + attrUrl(placeholderImage) + "'\" alt=\"image\">";
    }

    function verifiedMark(status) {
        if (status !== 'approved') {
            return '';
        }

        return ' <i class="mdi mdi-verified business-verified" data-toggle="tooltip"' +
            ' title="{{ trans('lang.business_account_verified') }}"' +
            ' data-bs-original-title="{{ trans('lang.business_account_verified') }}"></i>';
    }

    /* A URL for an HTML attribute. NOT encodeURI: these come out of Firebase
     * Storage already percent-encoded, so encodeURI re-encodes the % itself and
     * `images%2Fspideli.png` becomes `images%252Fspideli.png` - a 404, and a
     * broken image with no error in the console.
     *
     * The URL is used as stored; only the characters that would break out of a
     * double-quoted attribute are escaped. */
    function attrUrl(url) {
        if (!url) {
            return '';
        }

        return String(url)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function escapeHtml(value) {
        return $('<div></div>').text(value === undefined || value === null ? '' : value).html();
    }

    /* Written with DOTTED PATHS on purpose. Setting `businessProfile` as a whole
     * map would erase companyName, registrationNumber and documentUrl - the
     * evidence the decision was based on. */
    function statusPayload(status, reason) {
        var payload = {
            'businessProfile.status': status,
            'businessProfile.reviewedAt': firebase.firestore.FieldValue.serverTimestamp(),
            'businessProfile.reviewedBy': '{{ Auth::user()->id ?? '' }}'
        };

        if (status === 'rejected') {
            payload['businessProfile.rejectionReason'] = reason;
        } else {
            /* Clearing it stops an old reason showing against a later approval. */
            payload['businessProfile.rejectionReason'] = firebase.firestore.FieldValue.delete();
        }

        return payload;
    }

    async function setStatus(userId, status, reason) {
        var payload = statusPayload(status, reason);

        try {
            await database.collection('users').doc(userId).update(payload);
            $('.success_top').show().html('<p>{{ trans('lang.business_account_saved') }}</p>');
            window.scrollTo(0, 0);
            loadRequests();
        } catch (error) {
            $('.error_top').show().html('<p>' + escapeHtml(error.message) + '</p>');
            window.scrollTo(0, 0);
        }
    }

    /* Written one at a time and AWAITED, then the list is reloaded once.
     * The panel's older bulk screens use `.each(async ...)`, which fires every
     * write without waiting for any of them - so a failure is invisible and
     * the reload can race the writes. */
    async function setStatusMany(userIds, status, reason) {
        var payload = statusPayload(status, reason);
        var failed = 0;

        jQuery("#data-table_processing").show();
        $('.error_top').hide();
        $('.success_top').hide();

        for (var i = 0; i < userIds.length; i++) {
            try {
                await database.collection('users').doc(userIds[i]).update(payload);
            } catch (error) {
                failed++;
            }
        }

        if (failed > 0) {
            $('.error_top').show().html('<p>' + failed + ' {{ trans('lang.business_account_bulk_failed') }}</p>');
        } else {
            $('.success_top').show().html('<p>' + (userIds.length - failed) + ' {{ trans('lang.business_account_bulk_saved') }}</p>');
        }

        window.scrollTo(0, 0);
        $('#is_active').prop('checked', false);
        loadRequests();
    }

    /* .text() on purpose - the reason is typed by an admin and is shown as
     * written, never parsed as HTML. */
    $(document).on('click', '.reason-btn', function () {
        $('#reason_customer').text($(this).attr('data-customer') || '');
        $('#reason_company').text($(this).attr('data-company') || '');
        $('#reason_text').text($(this).attr('data-reason') || '');
        $('#reasonModal').modal('show');
    });

    /* Closed explicitly. This theme loads Bootstrap 4 AND Bootstrap 5, so
     * neither dismiss attribute can be relied on by itself - the panel's own
     * store modal carries both plus a handler for exactly this reason. */
    $(document).on('click', '#reasonModal [data-dismiss="modal"], #reasonModal [data-bs-dismiss="modal"]', function () {
        $('#reasonModal').modal('hide');
    });

    $(document).on('click', '#rejectModal [data-dismiss="modal"], #rejectModal [data-bs-dismiss="modal"]', function () {
        $('#rejectModal').modal('hide');
    });

    $(document).on('click', '.approve-btn', function () {
        if (!confirm("{{ trans('lang.business_account_approve_message') }}")) {
            return;
        }

        setStatus($(this).data('id'), 'approved', '');
    });

    $(document).on('click', '.reject-btn', function () {
        rejectingId = $(this).data('id');
        rejectingIds = [];
        openRejectModal();
    });

    function openRejectModal() {
        $('#reject_reason').val('');
        $('.reject_error').hide();
        $('#rejectModal').modal('show');
    }

    /* The ids ticked, skipping any already in the state being applied - there
     * is no point spending a write to approve an approved account. */
    function selectedIds(skipStatus) {
        var ids = [];

        $('#businessAccountTable .is_open:checked').each(function () {
            if ($(this).attr('data-status') !== skipStatus) {
                ids.push($(this).attr('dataId'));
            }
        });

        return ids;
    }

    $(document).on('click', '#is_active', function () {
        $('#businessAccountTable .is_open').prop('checked', $(this).prop('checked'));
    });

    $(document).on('click', '#approveAll', async function () {
        var ids = selectedIds('approved');

        if (ids.length === 0) {
            return;
        }

        if (!confirm("{{ trans('lang.business_account_approve_selected_message') }}")) {
            return;
        }

        await setStatusMany(ids, 'approved', '');
    });

    /* Bulk rejection goes through the same modal, so the reason is still
     * required - one reason applied to every account ticked. */
    $(document).on('click', '#rejectAll', function () {
        rejectingIds = selectedIds('rejected');

        if (rejectingIds.length === 0) {
            return;
        }

        rejectingId = '';
        openRejectModal();
    });

    /* Document 2 asks for "rejection with reason", and a customer who is
     * refused with no explanation simply applies again. */
    $(document).on('click', '#reject_confirm', async function () {
        var reason = $.trim($('#reject_reason').val());

        if (reason === '') {
            $('.reject_error').show();
            return;
        }

        $('#rejectModal').modal('hide');

        if (rejectingIds.length > 0) {
            await setStatusMany(rejectingIds, 'rejected', reason);
            rejectingIds = [];
            return;
        }

        setStatus(rejectingId, 'rejected', reason);
    });
</script>
@endsection
