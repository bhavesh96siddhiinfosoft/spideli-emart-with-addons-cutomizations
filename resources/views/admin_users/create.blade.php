@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.create_admin')}}</h3>
        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('admin.users') !!}">{{trans('lang.create_admin')}}</a></li>
            </ol>
        </div>

    </div>

    <div class="profile-form">

        <div class="card-body">
                @if (Session::has('message'))
                <div class="alert alert-error error_top"><p>{{Session::get('message')}}</p></div>
                @endif


            <div class="column">
                <form method="post" action="{{ route('admin.users.store') }}">
                    @csrf

                    <div class="row vendor_payout_create">
                        <div class="vendor_payout_create-inner">
                            <fieldset>
                                <legend>{{ trans('lang.profile_details') }}</legend>
                                <div class="form-group row">
                                    <label class="col-5 control-label">{{trans('lang.user_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" class=" col-6 form-control" name="name">
                                        <div class="form-text text-muted">
                                            {{ trans("lang.user_name_help") }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-5 control-label">{{trans('lang.password')}}</label>
                                    <div class="col-7">
                                        <input type="password" class=" col-6 form-control" name="password">
                                        <div class="form-text text-muted">
                                            {{ trans("lang.user_password_help") }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-5 control-label">{{trans('lang.confirm_password')}}</label>
                                    <div class="col-7">
                                        <input type="password" class=" col-6 form-control" name="confirm_password">
                                        <div class="form-text text-muted">
                                            {{ trans("lang.confirm_password_help") }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-5 control-label">{{trans('lang.user_email')}}</label>
                                    <div class="col-7">
                                        <input type="text" class=" col-6 form-control" name="email">
                                        <div class="form-text text-muted">
                                            {{ trans("lang.user_email_help") }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-5 control-label">{{trans('lang.role')}}</label>
                                    <div class="col-7">
                                        <select class=" col-6 form-control" name="role">
									    @foreach($roles as $role)
                                         <option value="{{$role->id}}">{{$role->role_name}}</option>
                                         @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-5 control-label">{{trans('lang.admin_user_regions')}}</label>
                                    <div class="col-7">
                                        <select class=" col-6 form-control" name="regions[]" id="regions" multiple></select>
                                        <div class="form-text text-muted">
                                            {{ trans("lang.admin_user_regions_help") }}
                                        </div>
                                    </div>
                                </div>

                            </fieldset>
                        </div>
                    </div>
            <div class="form-group col-12 text-center btm-btn">
                <button type="submit" class="btn btn-primary  save_user_btn" id="save_user_btn"><i
                        class="fa fa-save"></i> {{ trans('lang.save')}}</button>
                <a href="{!! route('admin.users') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
                    trans('lang.cancel')}}</a>
            </div>
            </form>

        </div>

    </div>

    @endsection

    @section('scripts')
    <script type="text/javascript">
        var database = firebase.firestore();

        $(document).ready(function () {
            $('#regions').select2({placeholder: "{{ trans('lang.select_region') }}"});
            loadRegionOptions();
        });

        function loadRegionOptions() {
            getPublishedRegions(false).then(function (regions) {
                regions.forEach(function (region) {
                    $('#regions').append($('<option></option>').attr('value', region.id).text(region.name));
                });
            });
        }
    </script>
    @endsection