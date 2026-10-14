@extends('layouts.app')

@section('content')
	<div class="page-wrapper">
    <div class="row page-titles">

        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.admin_commission')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.admin_commission')}}</li>
            </ol>
        </div>
    </div>

        <div class="card-body">
          <div class="row vendor_payout_create">
            <div class="vendor_payout_create-inner"> 
              <fieldset>
                <legend>{{trans('lang.admin_commission')}}</legend>
                    
                    <div class="form-check width-100">
                      <input type="checkbox" class="form-check-inline" onclick="ShowHideDiv()" id="enable_commission">
                        <label class="col-5 control-label" for="enable_commission">{{ trans('lang.enable_adminCommission')}}</label>
                    </div>

                    <div class="form-group row width-50">
                        <label class="col-4 control-label">{{ trans('lang.commission_type')}}</label>
                        <div class="col-7">
                          <select class="form-control commission_type" id="commission_type">
                            <option value="Percent">{{trans('lang.coupon_percent')}}</option>
                            <option value="Fixed">{{trans('lang.coupon_fixed')}}</option>
                          </select>
                        </div>
                    </div>

                    <div class="form-group row width-50" id="how_much_div"style="display:none">
                      <label class="col-4 control-label">{{ trans('lang.admin_commission')}}</label>
                      <div class="col-7">
                        <input type="number" class="form-control commission_fix">
                      </div>
                    </div>

              </fieldset>

              <fieldset>
                <legend>{{trans('lang.delivery_commission')}}</legend>

                    <div class="form-check width-100">
                      <input type="checkbox" class="form-check-inline" onclick="ShowHideDeliveryDiv()" id="enable_delivery_commission">
                        <label class="col-5 control-label" for="enable_delivery_commission">{{ trans('lang.enable_delivery_commission')}}</label>
                      <div class="form-text text-muted pl-4">{{ trans('lang.delivery_commission_help')}}</div>
                    </div>

                    <div class="form-group row width-50">
                        <label class="col-4 control-label">{{ trans('lang.commission_type')}}</label>
                        <div class="col-7">
                          <select class="form-control" id="delivery_commission_type">
                            <option value="Percent">{{trans('lang.coupon_percent')}}</option>
                            <option value="Fixed">{{trans('lang.coupon_fixed')}}</option>
                          </select>
                        </div>
                    </div>

                    <div class="form-group row width-50" id="delivery_how_much_div" style="display:none">
                      <label class="col-4 control-label">{{ trans('lang.delivery_commission')}}</label>
                      <div class="col-7">
                        <input type="number" step="any" class="form-control" id="delivery_commission_value">
                      </div>
                    </div>

              </fieldset>

              {{-- Parcel and mail (app developer's PARCEL-MANAGEMENT-FLOW.md 1.2):
                   the apps add the section commission ON TOP of a delivery
                   company's price only when this is on. Stored in
                   settings/ParcelPricing, which the apps read. --}}
              <fieldset>
                <legend>{{ trans('lang.parcel_commission_legend') }}</legend>
                    <div class="form-check width-100">
                      <input type="checkbox" class="form-check-inline" id="parcel_commission_as_extra">
                        <label class="col-8 control-label" for="parcel_commission_as_extra">{{ trans('lang.parcel_commission_as_extra') }}</label>
                      <div class="form-text text-muted pl-4">{{ trans('lang.parcel_commission_as_extra_help') }}</div>
                    </div>
              </fieldset>

            </div>
          </div>

          <div class="form-group col-12 text-center">
            <button type="button" class="btn btn-primary save_admin_commission" ><i class="fa fa-save"></i> {{trans('lang.save')}}</button>
            <a href="{{url('/dashboard')}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
          </div>
        </div>    


 @endsection

@section('scripts')

<script type="text/javascript">
    
    var database = firebase.firestore();
    var ref = database.collection('settings').doc("AdminCommission");
    var ref_deliverycharge = database.collection('settings').doc("DeliveryCharge");
    var ref_parcel_pricing = database.collection('settings').doc("ParcelPricing");

    var photo = "";
    $(document).ready(function(){
        jQuery("#data-table_processing").show();
        ref.get().then( async function(snapshots){
          /* 14 Oct: settings/AdminCommission does not exist until it is first
             saved - read it as empty instead of failing on undefined. */
          var adminCommissionSettings = (snapshots.exists && snapshots.data()) || {};
          jQuery("#data-table_processing").hide();
            if(adminCommissionSettings.isEnabled){
                $("#enable_commission").prop('checked',true);
                $("#how_much_div").show();
            }
          $(".commission_fix").val(adminCommissionSettings.fix_commission);
          $("#commission_type").val(adminCommissionSettings.commissionType);

            /* Commission taken on the delivery leg, kept alongside the order
             * commission so the apps read both in one go. */
            if (adminCommissionSettings.deliveryCommissionEnabled) {
                $("#enable_delivery_commission").prop('checked', true);
                $("#delivery_how_much_div").show();
            }
            $("#delivery_commission_value").val(adminCommissionSettings.deliveryCommissionValue);
            $("#delivery_commission_type").val(adminCommissionSettings.deliveryCommissionType || 'Percent');
        })
        /* Missing document = off, which is what the apps assume. */
        ref_parcel_pricing.get().then(function (snap) {
            $("#parcel_commission_as_extra").prop('checked', !!(snap.exists && snap.data().commissionAsExtra === true));
        });
        jQuery("#data-table_processing").hide();

        $(".save_admin_commission").click(function(){
          var checkboxValue = $("#enable_commission").is(":checked");

          var commission_type = $("#commission_type").val();
          /* parseFloat: 2.5 (%) stayed 2 with parseInt; an empty box saved NaN. */
          var howmuch = parseFloat($(".commission_fix").val());
          if (isNaN(howmuch)) { howmuch = 0; }
            var deliveryEnabled = $("#enable_delivery_commission").is(":checked");
            var deliveryValueRaw = $("#delivery_commission_value").val();
            var deliveryValue = (deliveryValueRaw === '') ? null : parseFloat(deliveryValueRaw);

            /* set + merge, not update: update() fails with "No document to
               update" while the document does not exist yet (live, 14 Oct). */
            database.collection('settings').doc("AdminCommission").set({
                'isEnabled':checkboxValue,
                'fix_commission':howmuch,
                'commissionType':commission_type,
                'deliveryCommissionEnabled': deliveryEnabled,
                'deliveryCommissionType': $("#delivery_commission_type").val(),
                'deliveryCommissionValue': deliveryValue
            }, { merge: true }).then(function () {
                /* merge: the document may not exist yet, and other parcel
                   settings may be added to it later. */
                return ref_parcel_pricing.set({
                    'commissionAsExtra': $("#parcel_commission_as_extra").is(":checked")
                }, { merge: true });
            }).then(function(result) {
                      window.location.href = '{{ url("settings/app/adminCommission")}}';
              });
        })
    })

    function ShowHideDeliveryDiv(){
      if($("#enable_delivery_commission").is(":checked")){
        $("#delivery_how_much_div").show();
      }else{
        $("#delivery_how_much_div").hide();
      }
    }

    function ShowHideDiv(){
      var checkboxValue = $("#enable_commission").is(":checked");
      if(checkboxValue){
      $("#how_much_div").show();
    }else{
      $("#how_much_div").hide();
    }

  }
 
</script>

@endsection