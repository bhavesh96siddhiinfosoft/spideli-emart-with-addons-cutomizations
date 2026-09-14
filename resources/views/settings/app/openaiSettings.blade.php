@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.openai_settings') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.openai_settings') }}</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div class="error_top"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.openai_settings') }}</legend>
                        <div class="form-check width-100">
                            <input type="checkbox" class="form-check-inline" id="status">
                            <label class="col-5 control-label" for="status">{{ trans('lang.openai_status') }}</label>
                        </div>
                        <div class="form-group row width-100">
                            <label class="col-4 control-label">{{ trans('lang.openai_api_key') }} </label>
                            <div class="col-7">
                                <input type="password" class="form-control" id="api_key">
                                <div class="form-text text-muted">
                                  {{ trans('lang.openai_help') }}
                              </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="form-group col-12 text-center">
                <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
                <a href="{{ url('/dashboard') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
            </div>
        </div>
    @endsection

    @section('scripts')

        <script>

            var database = firebase.firestore();
            var ref_openai_settings = database.collection('settings').doc("openai_settings");

            $(document).ready(function() {

                jQuery("#data-table_processing").show();
              
                ref_openai_settings.get().then(async function(snapshot) {

                    var openai_setting = snapshot.data();
                    
                    if (openai_setting == undefined) {
                        database.collection('settings').doc('openai_settings').set({
                            'status': '',
                            'api_key': '',
                        });
                    }
                    
                    jQuery("#data-table_processing").hide();
                    
                    $("#status").prop('checked', openai_setting.status);
                    $("#api_key").val(openai_setting.api_key);
                });

                $(".edit-setting-btn").click(function() {

                    var status = $("#status").is(":checked");
                    var api_key = $('#api_key').val();
                    
                    if (api_key == '') {

                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>{{ trans('lang.openai_api_key_error') }} </p>");
                        window.scrollTo(0, 0);

                    } else {
                        database.collection('settings').doc("openai_settings").update({
                            'status': status,
                            'api_key': api_key,
                        }).then(function(result) {
                            window.location.href = '{{ url('settings/app/openai-settings') }}';
                        });
                    }
                })
            })

        </script>

  @endsection