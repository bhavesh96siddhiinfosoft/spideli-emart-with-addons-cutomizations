@extends('layouts.app')

@section('content')
<div class="page-wrapper">
  <div class="row page-titles">

    <div class="col-md-5 align-self-center">
      <h3 class="text-themecolor">{{trans('lang.rides')}} <span class="itemTitle"></span></h3>
    </div>
    <div class="col-md-7 align-self-center">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
        <li class="breadcrumb-item"><a href="{!! route('rides') !!}">{{trans('lang.rides')}}</a></li>
        <li class="breadcrumb-item active">{{trans('lang.ride_detail')}}</li>
      </ol>
    </div>

  </div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-12">

        <div class="row vendor_payout_create">
          <div class="vendor_payout_create-inner">
            <fieldset>
              <legend>{{trans('lang.ride_detail')}}</legend>
              <div class="form-group row width-50">
                <label class="col-3 control-label">{{trans('lang.driver_plural')}}</label>
                <div class="col-7" class="driver_name">
                  <span class="driver_name" id="driver_name"></span>
                </div>
              </div>

              <div class="form-group row width-50">
                <label class="col-3 control-label">{{trans('lang.order_user_id')}}</label>
                <div class="col-7">
                  <span class="client_name"></span>
                </div>
              </div>


              <div class="form-group row width-50">
                <label class="col-3 control-label">{{trans('lang.address')}}</label>
                <div class="col-7">
                  <span class="address"></span>
                </div>
              </div>
              <div class="form-group row width-50">
                <label class="col-3 control-label">{{trans('lang.status')}}</label>
                <div class="col-7">
                  <span class="status"></span>
                </div>
              </div>
            </fieldset>

          </div>
          <div class="form-group col-12 text-center btm-btn">
            <a href="{!! route('rides') !!}" class="btn btn-default"><i
                class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
          </div>

        </div>
      </div>
    </div>

    @endsection

    @section('scripts')

    <script type="text/javascript">

      var id = "<?php echo $id; ?>";
      var database = firebase.firestore();
      var ref = database.collection('rides').where("id", "==", id);
      var photo = "";
      var vendorOwnerId = "";
      var vendorOwnerOnline = false;



      $(document).ready(async function () {
        jQuery("#data-table_processing").show();
        ref.get().then(async function (snapshots) {
          var ride = snapshots.docs[0].data();
          $(".driver_name").text(ride.driver.firstName);


          $(".client_name").text(ride.author.firstName);

          $(".address").text(ride.destinationLocationName);
          $(".status").text(ride.status);


          jQuery("#data-table_processing").hide();

        })

      })
    </script>
    @endsection