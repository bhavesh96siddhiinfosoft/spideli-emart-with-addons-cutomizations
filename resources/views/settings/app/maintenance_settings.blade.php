@extends('layouts.app')
@section('content')
	<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.maintenance_mode_settings')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.maintenance_mode_settings')}}</li>
            </ol>
        </div>
    </div>
        <div class="card-body">
      	  <div class="row vendor_payout_create">
            <div class="vendor_payout_create-inner">
              <fieldset>
                <legend>{{trans('lang.maintenance_mode_settings')}}</legend>             
                    <div class="form-check width-100">
                    <input type="checkbox" class="form-check-inline" id="isMaintenanceModeForCustomer">
                        <label class="col-5 control-label" for="isMaintenanceModeForCustomer">{{ trans('lang.enable_maintenance_mode_for_customer')}}</label>
                    </div>
                    <div class="form-check width-100">
                        <input type="checkbox" class="form-check-inline" id="isMaintenanceModeForDriver">
                        <label class="col-5 control-label"
                            for="isMaintenanceModeForDriver">{{ trans('lang.enable_maintenance_mode_for_driver')}}</label>
                    </div>
                    <div class="form-check width-100">
                        <input type="checkbox" class="form-check-inline" id="isMaintenanceModeForProvider">
                        <label class="col-5 control-label"
                            for="isMaintenanceModeForProvider">{{ trans('lang.enable_maintenance_mode_for_provider')}}</label>
                    </div>
                    <div class="form-check width-100">
                        <input type="checkbox" class="form-check-inline" id="isMaintenanceModeForVendor">
                        <label class="col-5 control-label"
                            for="isMaintenanceModeForVendor">{{ trans('lang.enable_maintenance_mode_for_vendor')}}</label>
                    </div>
                    <div class="form-check width-100">
                        <input type="checkbox" class="form-check-inline" id="isMaintenanceModeForWorker">
                        <label class="col-5 control-label"
                            for="isMaintenanceModeForWorker">{{ trans('lang.enable_maintenance_mode_for_worker')}}</label>
                    </div>
              </fieldset>
            </div>
          </div>
          <div class="form-group col-12 text-center">
            <button type="button" class="btn btn-primary edit-setting-btn" ><i class="fa fa-save"></i> {{trans('lang.save')}}</button>
            <a href="{{url('/dashboard')}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
          </div>
        </div>
 @endsection
@section('scripts')
<script>
    var database = firebase.firestore();
    var ref = database.collection('settings').doc("maintenance_settings");
    $(document).ready(function(){
        jQuery("#data-table_processing").show();
        ref.get().then( async function(snapshots){
          var documentVerification = snapshots.data();
          if(documentVerification == undefined){
              database.collection('settings').doc('maintenance_settings').set({});
          }
          try{
                if(documentVerification.isMaintenanceModeForCustomer){
                    $("#isMaintenanceModeForCustomer").prop('checked',true);
                }
                if(documentVerification.isMaintenanceModeForDriver){
                    $("#isMaintenanceModeForDriver").prop('checked',true);
                }
                if(documentVerification.isMaintenanceModeForProvider){
                    $("#isMaintenanceModeForProvider").prop('checked',true);
                }
                if(documentVerification.isMaintenanceModeForVendor){
                    $("#isMaintenanceModeForVendor").prop('checked',true);
                }
                if(documentVerification.isMaintenanceModeForWorker){
                    $("#isMaintenanceModeForWorker").prop('checked',true);
                }
             
          }catch (error){
          }
          jQuery("#data-table_processing").hide();
        })
        $(".edit-setting-btn").click(function(){
        jQuery("#data-table_processing").show();
          var enableCust = $("#isMaintenanceModeForCustomer").is(":checked");
          var enableDriver = $("#isMaintenanceModeForDriver").is(":checked");
          var enableProvider = $("#isMaintenanceModeForProvider").is(":checked");
          var enableVendor = $("#isMaintenanceModeForVendor").is(":checked");
          var enableWorker = $("#isMaintenanceModeForWorker").is(":checked");
          database.collection('settings').doc("maintenance_settings").update({            
            'isMaintenanceModeForCustomer':enableCust,
            'isMaintenanceModeForDriver':enableDriver,
            'isMaintenanceModeForProvider':enableProvider,
            'isMaintenanceModeForVendor':enableVendor,
            'isMaintenanceModeForWorker':enableWorker,            
          }).then(function(result) {
              window.location.href = '{{ url("settings/app/maintenance")}}';
          });
        })
    })
</script>
@endsection
