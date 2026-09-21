@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.service_group_create')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('service-groups') !!}">{{trans('lang.service_group_plural')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.service_group_create')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend><i class="mr-3 mdi mdi-view-grid"></i>{{trans('lang.service_group_plural')}}</legend>

                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{trans('lang.service_group_name')}}</label>
                            <div class="col-7">
                                <input type="text" class="form-control" id="name">
                                <div class="form-text text-muted">{{trans('lang.service_group_name_help')}}</div>
                                <div id="error_name" class="err"></div>
                            </div>
                        </div>

                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{trans('lang.service_group_order')}}</label>
                            <div class="col-7">
                                <input type="number" min="0" class="form-control" id="order" value="10">
                                <div class="form-text text-muted">{{trans('lang.service_group_order_help')}}</div>
                            </div>
                        </div>

                        <div class="form-group row width-100">
                            <div class="form-check width-100">
                                <input type="checkbox" id="publish" checked>
                                <label class="control-label" for="publish">{{trans('lang.status')}}</label>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary save-group-btn"><i class="fa fa-save"></i> {{trans('lang.save')}}</button>
            <a href="{!! route('service-groups') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var database = firebase.firestore();

    $('.save-group-btn').click(async function () {
        $('.err').html('');

        var name = $('#name').val().trim();
        var order = parseInt($('#order').val()) || 0;
        var publish = $('#publish').is(':checked');

        if (name === '') {
            $('#error_name').html("{{ trans('lang.service_group_name_error') }}");
            return false;
        }

        jQuery("#overlay").show();

        var snapshots = await database.collection('service_groups').get();
        var duplicate = snapshots.docs.some(function (doc) {
            return (doc.data().name || '').toLowerCase() === name.toLowerCase();
        });

        if (duplicate) {
            jQuery("#overlay").hide();
            $('#error_name').html("{{ trans('lang.service_group_name_duplicate') }}");
            return false;
        }

        /* The key is generated once and never changes. Sections store it, so a
         * key derived from the name would detach every service in the group
         * the first time somebody renamed it. */
        var id = database.collection('tmp').doc().id;

        await database.collection('service_groups').doc(id).set({
            'id': id,
            'name': name,
            'order': order,
            'publish': publish,
            'createdAt': firebase.firestore.FieldValue.serverTimestamp()
        });

        window.location.href = '{{ route('service-groups') }}';
    });
</script>
@endsection
