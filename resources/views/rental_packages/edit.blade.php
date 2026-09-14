@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.create_rental_package') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('rental-package') }}">{{ trans('lang.rental_packages') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ trans('lang.create_rental_package') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card-body">
                <div class="error_top" style="display:none"></div>
                <div class="success_top" style="display:none"></div>
                    <div class="row vendor_payout_create">
                        <div class="vendor_payout_create-inner">
                            <fieldset>
                                <legend>{{ trans('lang.package_details') }}</legend>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.package_name') }}</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control" id="name" name="name" >
                                        <div class="form-text text-muted">{{ trans('lang.enter_package_name') }}</div>
                                    </div>
                                </div>
                                
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.vehicle_type') }}</label>
                                    <div class="col-7">
                                        <select class="form-control model" name="vehicleTypeId" id="vehicleTypeId" >
                                            <option value="">{{ trans('lang.select_type') }}</option>
                                        </select>
                                        <div class="form-text text-muted">{{ trans('lang.enter_vehicle_type') }}</div>
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-3 control-label">{{ trans('lang.package_description') }}</label>
                                    <div class="col-7">
                                        <textarea class="form-control" id="description" name="description" rows="5" ></textarea>
                                        <div class="form-text text-muted">{{ trans('lang.enter_package_description') }}</div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.package_basefare_price') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control" id="baseFare" name="baseFare" >
                                        <div class="form-text text-muted">{{ trans('lang.enter_package_basefare_price') }}</div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.package_included_hours') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control" id="includedHours" name="includedHours" >
                                        <div class="form-text text-muted">{{ trans('lang.enter_package_included_hours') }}</div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.package_included_distance') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control" id="includedDistance" name="includedDistance" >
                                        <div class="form-text text-muted">{{ trans('lang.enter_package_included_distance') }}</div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.package_extra_km_fare') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control" id="extraKmFare" name="extraKmFare" >
                                        <div class="form-text text-muted">{{ trans('lang.enter_package_extra_km_fare') }}</div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.package_extra_minute_fare') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control" id="extraMinuteFare" name="extraMinuteFare" >
                                        <div class="form-text text-muted">{{ trans('lang.enter_package_extra_minute_fare') }}</div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.package_ordering') }}</label>
                                    <div class="col-7">
                                        <input type="number" class="form-control" id="ordering" name="ordering" >
                                        <div class="form-text text-muted">{{ trans('lang.enter_package_ordering') }}</div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <div class="form-check width-100">
                                        <input type="checkbox" id="published" name="published" >
                                        <label class="control-label" for="published">{{ trans('lang.published') }}</label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="submit" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i>
                            {{ trans('lang.save') }}
                        </button>
                        <a href="{{ url('rental-package') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                    </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js"></script>

<script>

    var packageId = "<?php echo $id; ?>";
    var service_type = getCookie('service_type') || '';        
    var section_id = getCookie('section_id') || '';   
    $(document).ready(async function() {

        jQuery("#data-table_processing").show();

        let rentalSections = await database
            .collection('sections')
            .where('isActive', '==', true)
            .where('serviceTypeFlag', '==', service_type)
            .get();

      
        let vehicleTypes = await database.collection('rental_vehicle_type').where('isActive', '==', true).where('sectionId', '==', section_id).get();
        if(vehicleTypes.docs.length > 0){
            vehicleTypes.docs.forEach((val) => {
                 var data = val.data();
                  $('#vehicleTypeId').append($("<option></option>").attr("value", data.id).text(data.name));
            });
        }

        let packageRes = await database.collection('rental_packages').doc(packageId).get();
        let packageData = packageRes.data();

        $("#name").val(packageData.name);
        $("#vehicleTypeId").val(packageData.vehicleTypeId);
      
        $("#description").val(packageData.description);
        $("#baseFare").val(packageData.baseFare);
        $("#includedHours").val(packageData.includedHours);
        $("#includedDistance").val(packageData.includedDistance);
        $("#extraKmFare").val(packageData.extraKmFare);
        $("#extraMinuteFare").val(packageData.extraMinuteFare);
        $("#ordering").val(packageData.ordering);
        $("#published").prop('checked', packageData.published);

        jQuery("#data-table_processing").hide();
       
          
    });
    async function loadVehicleTypes(sectionId) {
        $('#vehicleTypeId').empty().append(
            $("<option></option>").attr("value", "").text("{{ trans('lang.select_type') }}")
        );

        if (!sectionId) return; 

        let vehicleTypes = await database
            .collection('rental_vehicle_type')
            .where('isActive', '==', true)
            .where('sectionId', '==', sectionId)
            .get();

        if (vehicleTypes.docs.length > 0) {
            vehicleTypes.docs.forEach((val) => {
                var data = val.data();
                $('#vehicleTypeId').append(
                    $("<option></option>").attr("value", data.id).text(data.name)
                );
            });
        }
    }
    
     $(".edit-form-btn").click(async function() {

        var name = $("#name").val();
        var vehicleTypeId = $("#vehicleTypeId").val();
        var description = $("#description").val();
        var baseFare = $('#baseFare').val();
        var includedHours = $('#includedHours').val();
        var includedDistance = $("#includedDistance").val();
        var extraKmFare = $("#extraKmFare").val();
        var extraMinuteFare = $("#extraMinuteFare").val();
        var ordering = $("#ordering").val();
        var published = $("#published").is(":checked");
        var sectionId = getCookie('section_id') || '';
        if (name == "") {
            showError("{{ trans('lang.enter_package_name') }}");
        }else if (vehicleTypeId == "") {
            showError("{{ trans('lang.enter_vehicle_type') }}");
        } else if (description == "") {
            showError("{{ trans('lang.enter_package_description') }}");
        } else if (baseFare == "") {
            showError("{{ trans('lang.enter_package_basefare_price') }}");
        } else if (includedHours == "") {
            showError("{{ trans('lang.enter_package_included_hours') }}");
        } else if (includedDistance == "") {
            showError("{{ trans('lang.enter_package_included_distance') }}");
        } else if (extraKmFare == "") {
            showError("{{ trans('lang.enter_package_extra_km_fare') }}");
        } else if (extraMinuteFare == "") {
            showError("{{ trans('lang.enter_package_extra_minute_fare') }}");
        } else if (ordering == "") {
            showError("{{ trans('lang.enter_package_ordering') }}");
        }else{

            jQuery("#data-table_processing").show();

            database.collection('rental_packages').doc(packageId).update({
                'name': name,
                'vehicleTypeId': vehicleTypeId,
                'description': description,
                'baseFare': baseFare,
                'includedHours': includedHours,
                'includedDistance': includedDistance,
                'extraKmFare': extraKmFare,
                'extraMinuteFare': extraMinuteFare,
                'ordering': ordering,
                'published': published,
                'sectionId':sectionId

            }).then(function(result) {
                window.location.href = "{{ route('rental-package') }}";
            }).catch(function(error) {
                showError(error);
            })
        }
    });
</script>
    
@endsection
