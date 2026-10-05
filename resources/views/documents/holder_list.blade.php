{{--
    Reviewing one holder's uploaded documents.

    Client bug report items 24 and 25. Drivers, vendors and owners each have
    their own near-identical copy of this screen; this one is PARAMETERISED BY
    ROLE and serves providers and workers, so a sixth copy is never needed.

    The three existing screens are deliberately untouched - they are live and
    they work - but they can be pointed here later without a rewrite.

    `$role`, `$config` and `$id` come from DocumentReviewController.
--}}
@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.' . $config['title_key']) }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route($config['back_route']) !!}">{{ trans('lang.' . $config['back_key']) }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.' . $config['title_key']) }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                                <li class="nav-item">
                                    <a class="nav-link active holder-name"
                                       href="{!! url()->current() !!}">{{ trans('lang.' . $config['title_key']) }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="error_top" style="display:none"></div>

                            {{-- Shown when the holder has uploaded nothing at all, so the
                                 screen says why it is empty rather than showing a bare
                                 table of "pending". --}}
                            <div id="nothing_uploaded" class="alert alert-info" style="display:none;">
                                {{ trans('lang.document_nothing_uploaded') }}
                            </div>

                            <div class="table-responsive m-t-10 doc-body"></div>

                            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog" role="document" style="max-width: 50%;">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            {{-- Both namings: this theme carries Bootstrap 4 and 5. --}}
                                            <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <embed id="docImage" src="" frameBorder="0" scrolling="auto"
                                                       height="100%" width="100%" style="height: 540px;"></embed>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal" data-bs-dismiss="modal">{{ trans('lang.close') }}</button>
                                            </div>
                                        </div>
                                    </div>
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
    var holderId = "<?php echo addslashes($id); ?>";
    var holderRole = "<?php echo addslashes($role); ?>";
    var holderCollection = "<?php echo addslashes($config['collection']); ?>";
    var documentsControlActive = {{ $config['activates'] ? 'true' : 'false' }};

    var database = firebase.firestore();

    /* The documents this ROLE must supply, and what this holder has sent. */
    var requiredDocsRef = database.collection('documents').where('enable', '==', true).where('type', '==', holderRole);
    var verifyRef = database.collection('documents_verify').doc(holderId);

    var fcmToken = '';

    $(document).ready(async function () {
        jQuery("#data-table_processing").show();

        $('#exampleModal').on('show.bs.modal', function (event) {
            $(this).find('#docImage').attr('src', $(event.relatedTarget).data('image'));
        });

        await loadHolderName();
        await renderDocuments();

        jQuery("#data-table_processing").hide();
    });

    /* WORKERS ARE NOT USERS - they live in their own collection - so the name
     * is read from whichever collection this role uses, and read by DOCUMENT
     * ID rather than by a `where` on a field that may not exist there. */
    async function loadHolderName() {
        try {
            var snapshot = await database.collection(holderCollection).doc(holderId).get();

            if (!snapshot.exists) {
                return;
            }

            var holder = snapshot.data() || {};

            if (holder.fcmToken) {
                fcmToken = holder.fcmToken;
            }

            var name = ((holder.firstName || '') + ' ' + (holder.lastName || '')).trim() || holder.name || '';

            if (name !== '') {
                $('.holder-name').text(name + " - " + "{{ trans('lang.' . $config['title_key']) }}");
            }
        } catch (err) {
            console.error('holder could not be read', err);
        }
    }

    /* Turns a key the app invented into something readable.
     *
     *     commercialRegister        -> Commercial register
     *     uniqueIdNumber            -> Unique id number
     *     worker_identity_document  -> Worker identity document
     *
     * Only used when no document type defines the key, so the admin sees a
     * name rather than a camelCase identifier. */
    function readableDocumentKey(key) {
        var text = String(key === null || key === undefined ? '' : key);

        if (text === '') {
            return '-';
        }

        text = text.replace(/[_-]+/g, ' ')
                   .replace(/([a-z0-9])([A-Z])/g, '$1 $2')
                   .replace(/\s+/g, ' ')
                   .trim()
                   .toLowerCase();

        return text.charAt(0).toUpperCase() + text.slice(1);
    }

    async function renderDocuments() {
        var required = await requiredDocsRef.get();

        /* READ ONCE, not once per row. The driver screen re-reads this same
         * document inside the loop, which is one read per required document. */
        var verifySnapshot = await verifyRef.get();
        var uploaded = (verifySnapshot.exists && Array.isArray(verifySnapshot.data().documents))
            ? verifySnapshot.data().documents
            : [];

        var requiredIds = required.docs.map(function (ele) { return ele.data().id; });

        /* ---- Report 03 point 37 ------------------------------------------
         *
         * *"We were unable to validate Commercial register and Unique
         * identification number documents for the service provider. Only the
         * ID Card and Identity Card documents were created."*
         *
         * Exactly right, and this is why. The loop below walks the DOCUMENT
         * TYPES an admin has defined and looks for a matching upload. A
         * provider has two types defined - "ID Card" and "Indentity Card" -
         * so two rows were drawn, and nothing else could ever appear.
         *
         * But the app uploads under keys of its own that match no type:
         *
         *     commercialRegister        5 uploads, 5 real providers
         *     uniqueIdNumber            5 uploads
         *     worker_identity_document  2 uploads
         *
         * Those were invisible - not pending, not rejected, ABSENT - so there
         * was no way to validate them and no sign they existed.
         *
         * They are now listed after the required ones. The proper fix is for
         * the app to use a real document type id; until it does, an upload
         * nobody can see is worse than one with an awkward name. */
        var extras = uploaded.filter(function (d) {
            return d && d.documentId && requiredIds.indexOf(d.documentId) === -1;
        });

        if (required.docs.length === 0 && extras.length === 0) {
            $('#nothing_uploaded').text("{{ trans('lang.document_none_required') }}").show();
            return;
        }

        if (uploaded.length === 0) {
            $('#nothing_uploaded').show();
        }

        var html = '<table id="documentTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">';
        html += '<thead><tr>';
        html += '<th>{{ trans('lang.name') }}</th>';
        html += '<th>{{ trans('lang.status') }}</th>';
        html += '<th>{{ trans('lang.action') }}</th>';
        html += '</tr></thead><tbody>';

        required.docs.forEach(function (ele) {
            var doc = ele.data();
            var sent = uploaded.filter(function (d) { return d.documentId === doc.id; })[0] || null;
            html += documentRow(doc, sent);
        });

        /* Approve and reject work on these exactly as on the rest: the handler
         * matches on documentId, and that is what `id` carries here. */
        extras.forEach(function (sent) {
            html += documentRow({
                id: sent.documentId,
                title: readableDocumentKey(sent.documentId),
                notConfigured: true
            }, sent);
        });

        html += '</tbody></table>';

        $('.doc-body').html(html);

        $('#documentTable').DataTable({
            order: [[0, 'asc']],
            columnDefs: [{ orderable: false, targets: [1, 2] }],
            "language": datatableLang
        });
    }

    function documentRow(doc, sent) {
        var row = '<tr>';

        /* EVERY ROW EMITS EVERY CELL, whatever is missing - the fault behind
         * bug report items 22 and 23 on the Documents list. */
        row += '<td>' + escapeHtml(doc.title || '-');

        /* Says plainly why this one has no proper name: the app sent a key
         * nobody configured. Without the note it looks like a typo. */
        if (doc.notConfigured) {
            row += ' <span class="badge badge-warning">' +
                   "{{ trans('lang.document_not_configured') }}" + '</span>';
        }

        row += imageLinks(doc, sent) + '</td>';

        var status = sent && sent.status ? sent.status : 'pending';
        row += '<td>' + statusBadge(status) + '</td>';

        row += '<td class="action-btn">' + actions(doc, status) + '</td>';

        return row + '</tr>';
    }

    function imageLinks(doc, sent) {
        if (!sent) {
            return '';
        }

        var out = '';

        if (doc.frontSide && sent.frontImage) {
            out += '&nbsp;<a href="#" class="badge badge-info" data-toggle="modal" data-bs-toggle="modal"' +
                ' data-target="#exampleModal" data-bs-target="#exampleModal" data-image="' + attrUrl(sent.frontImage) + '">' +
                "{{ trans('lang.view_front_image') }}" + '</a>';
        }

        if (doc.backSide && sent.backImage) {
            out += '&nbsp;<a href="#" class="badge badge-info" data-toggle="modal" data-bs-toggle="modal"' +
                ' data-target="#exampleModal" data-bs-target="#exampleModal" data-image="' + attrUrl(sent.backImage) + '">' +
                "{{ trans('lang.view_back_image') }}" + '</a>';
        }

        return out;
    }

    function statusBadge(status) {
        var classes = {
            'approved': 'badge-success',
            'rejected': 'badge-danger',
            'uploaded': 'badge-primary',
            'pending': 'badge-warning'
        };

        return '<span class="badge ' + (classes[status] || 'badge-warning') + ' py-2 px-3">' + escapeHtml(status) + '</span>';
    }

    /* NOTHING TO DECIDE UNTIL SOMETHING IS SENT. "pending" means the holder has
     * not uploaded it, so approving or refusing it would be approving nothing. */
    function actions(doc, status) {
        if (status === 'pending') {
            return '<span class="text-muted">' + "{{ trans('lang.document_awaiting_upload') }}" + '</span>';
        }

        var out = '';

        if (status !== 'approved') {
            out += '<a href="javascript:void(0);" class="btn btn-sm btn-success verify-doc" data-status="approved"' +
                ' data-title="' + attrUrl(doc.title || '') + '" data-id="' + attrUrl(doc.id) + '">' +
                "{{ trans('lang.approve') }}" + '</a>&nbsp;';
        }

        if (status !== 'rejected') {
            out += '<a href="javascript:void(0);" class="btn btn-sm btn-danger verify-doc" data-status="rejected"' +
                ' data-title="' + attrUrl(doc.title || '') + '" data-id="' + attrUrl(doc.id) + '">' +
                "{{ trans('lang.reject') }}" + '</a>';
        }

        return out;
    }

    function escapeHtml(value) {
        return $('<div></div>').text(value === undefined || value === null ? '' : value).html();
    }

    /* A Firebase Storage url ARRIVES ALREADY PERCENT-ENCODED. Only the
     * characters that would break out of an attribute are escaped; running
     * encodeURI over it would turn %2F into %252F and break the link. */
    function attrUrl(value) {
        return String(value === undefined || value === null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    $(document).on('click', '.verify-doc', async function () {
        var status = $(this).attr('data-status');
        var documentId = $(this).attr('data-id');
        var documentTitle = $(this).attr('data-title');

        jQuery("#data-table_processing").show();

        try {
            /* RE-READ BEFORE WRITING. The holder may have re-uploaded while
             * this page sat open, and writing back a stale array would discard
             * it. */
            var snapshot = await verifyRef.get();
            var documents = (snapshot.exists && Array.isArray(snapshot.data().documents))
                ? snapshot.data().documents
                : [];

            var index = documents.findIndex(function (d) { return d.documentId === documentId; });

            if (index === -1) {
                jQuery("#data-table_processing").hide();
                showError("{{ trans('lang.document_no_longer_uploaded') }}");
                return;
            }

            documents[index].status = status;

            await verifyRef.update({ documents: documents });
            await refreshVerifiedFlag();
            await notifyHolder(status, documentTitle);

            window.location.reload();
        } catch (err) {
            console.error('document status could not be saved', err);
            jQuery("#data-table_processing").hide();
            showError("{{ trans('lang.document_save_failed') }}");
        }
    });

    /* The holder counts as verified only when EVERY required document is
     * approved.
     *
     * `documentsControlActive` is false for both roles this screen serves. Only
     * a DRIVER is switched on and off by their documents; doing that to a
     * provider would deactivate somebody the admin never chose to deactivate. */
    async function refreshVerifiedFlag() {
        var required = await requiredDocsRef.get();
        var requiredIds = required.docs.map(function (d) { return d.data().id; });

        var snapshot = await verifyRef.get();
        var approved = (snapshot.exists && Array.isArray(snapshot.data().documents))
            ? snapshot.data().documents.filter(function (d) { return d.status === 'approved'; })
                .map(function (d) { return d.documentId; })
            : [];

        var allApproved = requiredIds.every(function (docId) { return approved.indexOf(docId) !== -1; });

        var payload = { 'isDocumentVerify': allApproved };

        if (documentsControlActive) {
            payload.isActive = allApproved;
        }

        await database.collection(holderCollection).doc(holderId).update(payload);
    }

    /* A holder who has never opened the app has no token. That is not a
     * failure - the decision is still recorded. */
    async function notifyHolder(status, documentTitle) {
        if (!fcmToken) {
            return;
        }

        var title = status === 'approved'
            ? "{{ trans('lang.approved_your_document') }}"
            : "{{ trans('lang.rejected_your_document') }}";

        var message = status === 'approved'
            ? "{{ trans('lang.admin_approved_document') }}" + documentTitle
            : "{{ trans('lang.admin_rejected_document') }}" + documentTitle + ' . ' + "{{ trans('lang.please_submit_again') }}";

        try {
            await $.ajax({
                url: "{{ route('advertisement.sendnotification') }}",
                type: "POST",
                data: { _token: "{{ csrf_token() }}", fcm: fcmToken, title: title, message: message }
            });
        } catch (err) {
            /* The decision is already saved. A failed notification must not
             * make it look as though nothing happened. */
            console.error('notification could not be sent', err);
        }
    }

    function showError(message) {
        $('.error_top').show().html('<p class="text-danger">' + escapeHtml(message) + '</p>');
        window.scrollTo(0, 0);
    }
</script>
@endsection
