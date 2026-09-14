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
                <li class="breadcrumb-item"><a href="{!! route('carriers') !!}">{{ trans('lang.carrier_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.carrier_create') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="error_top" style="display:none"></div>

                @include('carriers.partials.form')

                <div class="form-group col-12 text-center btm-btn">
                    <button type="button" class="btn btn-primary save-carrier-btn">
                        <i class="fa fa-save"></i> {{ trans('lang.save') }}
                    </button>
                    <a href="{!! route('carriers') !!}" class="btn btn-default">
                        <i class="fa fa-undo"></i>{{ trans('lang.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();
    var carrierId = database.collection('tmp').doc().id;
</script>
@include('carriers.partials.form_scripts')
<script type="text/javascript">
    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        initCarrierForm(null).then(function () {
            jQuery("#data-table_processing").hide();
        });
    });

    $('.save-carrier-btn').click(function () {
        saveCarrier(carrierId, false);
    });
</script>
@endsection
