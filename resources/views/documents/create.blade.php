@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.document_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('documents') !!}">{{trans('lang.document_plural')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.document_create')}}</li>
            </ol>
        </div>
    </div>
    <div class="card-body">
        <div class="error_top" style="display:none"></div>
        <div class="row vendor_payout_create">
            <div class="vendor_payout_create-inner">
                <fieldset>
                    <legend>{{trans('lang.document_create')}}</legend>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.title')}}</label>
                        <div class="col-7">
                            <input type="text" type="text" class="form-control title">
                            <div class="form-text text-muted">{{ trans("lang.document_title_help") }}</div>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">{{trans('lang.document_for')}}</label>
                        <div class="col-7">
                            <select id="document_for" class="form-control">
                                <option value="vendor">{{trans('lang.document_vendor')}}</option>
                                <option value="driver">{{trans('lang.document_driver')}}</option>
                                <option value="owner">{{trans('lang.document_owner')}}</option>
                                <option value="provider">{{trans('lang.document_provider')}}</option>
                                <option value="worker">{{trans('lang.document_worker')}}</option>
                            </select>
                            <div class="form-text text-muted">{{ trans("lang.select_document_for") }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <div class="form-check">
                            <input type="checkbox" class="frontside" id="frontside">
                            <label class="col-3 control-label" for="frontside">{{trans('lang.frontside')}}</label>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <div class="form-check">
                            <input type="checkbox" class="backside" id="backside">
                            <label class="col-3 control-label" for="backside">{{trans('lang.backside')}}</label>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <div class="form-check">
                            <input type="checkbox" class="enable" id="enable">
                            <label class="col-3 control-label" for="enable">{{trans('lang.enable')}}</label>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
    <div class="form-group col-12 text-center btm-btn">
        <button type="button" class="btn btn-primary save-setting-btn"><i class="fa fa-save"></i> {{
            trans('lang.save')}}
        </button>
        <a href="{!! route('documents') !!}" class="btn btn-default"><i
                class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
    </div>
</div>
</div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>
<link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
<script>
    var database = firebase.firestore();
    var alldriver = database.collection('users').where('role', '==', 'driver');
    var allvendor = database.collection('users').where('role', '==', 'vendor');
    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        var id = database.collection("tmp").doc().id;
        $(".save-setting-btn").click(function () {
            var title = $(".title").val();
            var document_for = $("#document_for").val();
            var isEnabled = $(".enable").is(":checked");
            var forntend = $(".frontside").is(":checked");
            var backend = $(".backside").is(":checked");
            if (title == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.document_title_help')}}</p>");
                window.scrollTo(0, 0);
                return;
            } else if (forntend == false && backend == false) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.check_atleast_one_side_of_document_from_front_or_back')}}</p>");
                window.scrollTo(0, 0);
                return;
            }
            else {
                jQuery("#data-table_processing").show();
                database.collection('documents').doc(id).set({
                    'title': title,
                    'type': document_for,
                    'frontSide': forntend,
                    'backSide': backend,
                    'enable': isEnabled,
                    'id': id,
                }).then(async function (result) {
                    /* Re-check the holders of this document type: adding or
                     * enabling a document changes who is fully verified. */
                    var target = documentTargetFor(document_for);
                    var enableDocIds = await getDocId(document_for);
                    var holders = await target.query.get();
                    if (holders.docs.length > 0) {
                        await userDocVerification(enableDocIds, holders);
                    }
                    jQuery("#data-table_processing").hide();
                    window.location.href = '{{ route("documents")}}';
                });
            }
        });
        jQuery("#data-table_processing").hide();
    });

    /* Where each document type's holders live, and what to update when their
     * documents are all approved. Workers are not users - they sit in their own
     * collection - so the target collection is part of the mapping rather than
     * assumed. */
    function documentTargetFor(type) {
        if (type == 'driver') {
            return {collection: 'users', query: database.collection('users').where('role', '==', 'driver'), activates: true};
        }
        if (type == 'owner') {
            return {collection: 'users', query: database.collection('users').where('role', '==', 'driver').where('isOwner', '==', true), activates: false};
        }
        if (type == 'provider') {
            return {collection: 'users', query: database.collection('users').where('role', '==', 'provider'), activates: false};
        }
        if (type == 'worker') {
            return {collection: 'providers_workers', query: database.collection('providers_workers'), activates: false};
        }
        return {collection: 'users', query: database.collection('users').where('role', '==', 'vendor'), activates: false};
    }

    async function getDocId(type) {
        var enableDocIds = [];
        await database.collection('documents').where('type', '==', type).where('enable', "==", true).get().then(async function (snapshots) {
            await snapshots.forEach((doc) => {
                enableDocIds.push(doc.data().id);
            });
        });
        return enableDocIds;
    }
    async function userDocVerification(enableDocIds, snapshots) {
        var isCompleted = false;
        var document_for = $("#document_for").val();
        var target = documentTargetFor(document_for);

        /* Writes the verified flag to whichever collection holds this type.
         * Only drivers are activated or deactivated by their documents; the
         * other types keep their active state under the admin's control. */
        async function markVerified(holderId, verified) {
            var payload = {'isDocumentVerify': verified};
            if (target.activates) {
                payload.isActive = verified;
            }
            await database.collection(target.collection).doc(holderId).update(payload);
        }

        await Promise.all(snapshots.docs.map(async (holder) => {
            await database.collection('documents_verify').doc(holder.id).get().then(async function (docrefSnapshot) {
                var data = docrefSnapshot.data();
                if (data && data.documents && data.documents.length > 0) {
                    var approved = data.documents
                        .filter(function (doc) { return doc.status == 'approved'; })
                        .map(function (doc) { return doc.documentId; });

                    /* Verified only when every enabled document is approved. */
                    var allApproved = enableDocIds.every(function (docId) {
                        return approved.indexOf(docId) !== -1;
                    });
                    await markVerified(holder.id, allApproved);
                } else {
                    await markVerified(holder.id, false);
                }
            });
            isCompleted = true;
        }));
        return isCompleted;
    }
</script>
@endsection
