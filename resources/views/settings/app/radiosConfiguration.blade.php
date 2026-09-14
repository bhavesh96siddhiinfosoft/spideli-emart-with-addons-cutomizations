@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.radios_configuration') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.radios_configuration') }}</li>
                </ol>
            </div>
        </div>
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{ trans('lang.radios_configuration') }}</legend>
                        <div class="form-group row width-50">
                            <label class="col-4 control-label">{{ trans('lang.distance_type') }}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <select name="distance_type" id="distance_type" class="form-control">
                                        <option value="Km">{{ trans('lang.km') }}</option>
                                        <option value="Miles">{{ trans('lang.miles') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-4 control-label">{{ trans('lang.driver_nearby_radios') }}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control driver_nearby_radios" required>
                                    <span class="set_distance_type">{{ trans('lang.miles') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-4 control-label">{{ trans('lang.driverOrderAcceptRejectDuration') }}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control driverOrderAcceptRejectDuration" required>
                                    <span>{{ trans('lang.second') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-4 control-label">{{ trans('lang.store') }} {{ trans('lang.orderAutoCancelDuration') }}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control orderAutoCancelDuration" required>
                                    <span>{{ trans('lang.minutes') }}</span>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>{{ trans('lang.cab_service_settings') }}</legend>
                        <div class="form-check width-100">
                            <input type="checkbox" class="enableOTPTripStart" id="enableOTPTripStart">
                            <label class="col-3 control-label" for="enable_stripe">{{ trans('lang.enableOTPTripStart') }}</label>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-4 control-label">{{ trans('lang.driver_nearby_radios') }}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control cab_driver_nearby_radios" required>
                                    <span class="set_distance_type">{{ trans('lang.miles') }}</span>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>{{ trans('lang.parcel_service_settings') }}</legend>
                        <div class="form-group row width-50">
                            <label class="col-4 control-label">{{ trans('lang.driver_nearby_radios') }}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control parcel_driver_nearby_radios" required>
                                    <span class="set_distance_type">{{ trans('lang.miles') }}</span>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                     <fieldset>
                        <legend>{{ trans('lang.rental_service_settings') }}</legend>
                        <div class="form-check width-50">
                            <input type="checkbox" class="enableOtpForRental" id="enableOtpForRental">
                            <label class="col-3 control-label" for="enableOtpForRental">{{ trans('lang.enable_otp_for_rental') }}</label>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-4 control-label">{{ trans('lang.driver_nearby_radios') }}</label>
                            <div class="col-7">
                                <div class="control-inner">
                                    <input type="number" class="form-control rental_driver_nearby_radios" required>
                                    <span class="set_distance_type">{{ trans('lang.miles') }}</span>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
            <a href="{{ url('/dashboard') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        var database = firebase.firestore();
        var refDriver = database.collection('settings').doc("DriverNearBy");
        $(document).ready(function() {
            jQuery("#data-table_processing").show();
            refDriver.get().then(async function(snapshots) {
                var radios = snapshots.data();
                $(".driver_nearby_radios").val(radios.driverRadios);
                $(".cab_driver_nearby_radios").val(radios.cabRadius);
                $(".parcel_driver_nearby_radios").val(radios.parcelRadius);
                $(".rental_driver_nearby_radios").val(radios.rentalRadius);
                $(".driverOrderAcceptRejectDuration").val(radios.driverOrderAcceptRejectDuration);
                if (radios.enableOTPTripStart) {
                    $(".enableOTPTripStart").prop('checked', true);
                }
                if (radios.enableOTPTripStartForRental) {
                    $(".enableOtpForRental").prop('checked', true);
                }
                if (radios.hasOwnProperty('distanceType')) {
                    $("#distance_type").val(radios.distanceType);
                    $(".set_distance_type").text(radios.distanceType);
                }
                $(".orderAutoCancelDuration").val(radios.orderAutoCancelDuration);
            })
            jQuery("#data-table_processing").hide();
        })
        $("#distance_type").change(function() {
            $(".set_distance_type").text($(this).val());
            $('.vendor_near_by').next('span').text(unit);
        });
        $(".edit-setting-btn").click(function() {
            var driverNearBy = $(".driver_nearby_radios").val();
            var cabDriverNearBy = $(".cab_driver_nearby_radios").val();
            var parcelDriverNearBy = $(".parcel_driver_nearby_radios").val();
            var rentalDriverNearBy = $(".rental_driver_nearby_radios").val();
            var enableOTPTripStart = $(".enableOTPTripStart").is(":checked");
            var enableOtpForRental = $(".enableOtpForRental").is(":checked");
            var driverOrderAcceptRejectDuration = $(".driverOrderAcceptRejectDuration").val();
            var distance_type = $("#distance_type").val();
            var orderAutoCancelDuration = $(".orderAutoCancelDuration").val();
            if (driverNearBy == '' || cabDriverNearBy == '' || parcelDriverNearBy == '' || rentalDriverNearBy == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{ trans('lang.enter_driver_nearby_radios_error') }}</p>");
            } else if (driverOrderAcceptRejectDuration == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{ trans('lang.driverOrderAcceptRejectDuration_error') }}</p>");
            } else if (orderAutoCancelDuration == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{ trans('lang.orderAutoCancelDuration_error') }}</p>");
            } else {
                database.collection('settings').doc("DriverNearBy").update({
                    'distanceType': distance_type,
                    'driverRadios': driverNearBy,
                    'driverOrderAcceptRejectDuration': Number(driverOrderAcceptRejectDuration),
                    'enableOTPTripStart': enableOTPTripStart,
                    'enableOTPTripStartForRental': enableOtpForRental,
                    'orderAutoCancelDuration':Number(orderAutoCancelDuration),
                    'cabRadius': cabDriverNearBy,
                    'parcelRadius': parcelDriverNearBy,
                    'rentalRadius': rentalDriverNearBy,
                }).then(function(result) {
                    window.location.href = '{{ url()->current() }}';
                });
            }
        })
    </script>
@endsection
