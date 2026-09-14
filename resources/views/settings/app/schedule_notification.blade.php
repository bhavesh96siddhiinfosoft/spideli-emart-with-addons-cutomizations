@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.schedule_order_notification_title')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.schedule_order_notification_title')}}</li>
            </ol>
        </div>
    </div>
    <div class="card-body">
        <div class="error_top" style="display:none"></div>

        <div class="row vendor_payout_create">
            <div class="vendor_payout_create-inner">

                <fieldset>
                    <legend><i class="mr-3 mdi mdi-shopping"></i>{{trans('lang.schedule_order_notification_title')}}</legend>
                    <div class="form-group row width-50">
                        <label class="col-4 control-label">{{ trans('lang.time')}}</label>
                        <div class="col-7">
                            <input type="number" placeholder="{{trans('lang.enter_time')}}" id="notify_time" class="form-control time">
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-4 control-label">{{ trans('lang.time_unit')}}</label>
                        <div class="col-7">
                            <select class="form-control time_unit" id="time_unit">
                                <option value="day" selected>{{trans('lang.days')}}</option>
                                <option value="hour">{{trans('lang.hours')}}</option>
                                <option value="minute">{{trans('lang.minutes')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-text text-muted ml-3">
                       {{trans("lang.schedule_order_notify_and_accept_time_note")}} <span class="time"></span>  {{trans("lang.restaurant_can_accept_the_order_before")}}  <span class="time"></span>     
                    </div>
                                       
                </fieldset>
                <div class="form-group col-12 text-center">
                    <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i>
                        {{trans('lang.save')}}</button>
                    <a href="{{url('/dashboard')}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                </div>

            </div>
        </div>
    </div>
    <style>
        .select2.select2-container {
            width: 100% !important;
            position: static;
            margin-top: 1rem;
        }
    </style>
    @endsection
    @section('scripts')
    <script>
        var database=firebase.firestore();
        var ref=database.collection('settings').doc("scheduleOrderNotification");
       
        $(document).ready(function() {
            jQuery("#overlay").show();
           ref.get().then(async function(snapshots) {
                var data=snapshots.data();
                if(data==undefined) {
                    database.collection('settings').doc('scheduleOrderNotification').set({'notifyTime': '','timeUnit':''});
                }
                try {
                    $('#notify_time').val(data.notifyTime);
                    $('#time_unit').val(data.timeUnit); 
                    $('.time').html(data.notifyTime+' '+data.timeUnit);                   
                } catch(error) {
                }
                jQuery("#overlay").hide();
            })
            

            $(".edit-setting-btn").click(function() {
                var time=$("#notify_time").val();
                var timeUnit=$('#time_unit').val();
                
                if(time==''){
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.enter_time')}}</p>");
                    window.scrollTo(0,0);
                }else{
                    database.collection('settings').doc("scheduleOrderNotification").update({'notifyTime': time,'timeUnit':timeUnit}).then(function(result) {
                        window.location.reload();
                    });
                }
               
            })

        })
    
    </script>
    @endsection