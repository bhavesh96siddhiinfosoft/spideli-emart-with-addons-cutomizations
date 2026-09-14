@extends('layouts.app')

@section('content')

    <div class="page-wrapper">

        <div class="row page-titles">

            <div class="col-md-5 align-self-center">

                <h3 class="text-themecolor">{{ trans('lang.business_model_settings') }}</h3>

            </div>

            <div class="col-md-7 align-self-center">

                <ol class="breadcrumb">

                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>

                    <li class="breadcrumb-item active">{{ trans('lang.business_model_settings') }}</li>

                </ol>

            </div>

        </div>

        <div class="card-body">

            <div class="row vendor_payout_create">

                <div class="vendor_payout_create-inner">

                    <fieldset>

                        <legend><i class="mr-3 mdi mdi-shopping"></i>{{ trans('lang.subscription_based_model_settings') }}

                        </legend>

                        <div class="form-group row mt-1 ">

                            <div class="form-group row mt-1 ">

                                <div class="col-12 switch-box">

                                    <div class="switch-box-inner">

                                        <label class=" control-label">{{ trans('lang.subscription_based_model') }}</label>

                                        <label class="switch"> <input type="checkbox" name="subscription_model"

                                                id="subscription_model"><span class="slider round"></span></label>

                                        <i class="text-dark fs-12 fa-solid fa fa-info" data-toggle="tooltip"

                                            title="{{ trans('lang.subscription_tooltip') }}" aria-describedby="tippy-3"></i>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </fieldset>



                    <fieldset>

                        <legend><i class="mr-3 mdi mdi-shopping"></i>{{ trans('lang.bulk_update') }}</legend>

                        <div class="form-group row width-100">

                            <label class="col-3 control-label">{{ trans('lang.select_section') }} <i

                                    class="text-dark fs-12 fa-solid fa fa-info" data-toggle="tooltip"

                                    title="{{ trans('lang.bulk_update_commission_tooltip') }}"

                                    aria-describedby="tippy-3"></i>

                            </label>

                            <div class="col-7">

                                <select class="form-control section" id="section">

                                    <option value="">{{ trans('lang.select_section') }}</option>

                                </select>

                            </div>



                        </div>

                        <div class="form-group row width-100">

                            <label class="col-3 control-label">{{ trans('lang.select_user') }}</label>

                            <div class="col-7 selected-user">

                                <select id="food_restaurant_type" class="form-control" required>

                                    <option value="all">{{ trans('lang.all_user') }}</option>

                                    <option value="custom">{{ trans('lang.custom') }}</option>

                                </select>

                                <select id="food_restaurant" style="display:none" multiple class="form-control mt-3"

                                    required>

                                </select>

                            </div>

                        </div>



                        <div class="form-group row width-50">

                            <label class="col-4 control-label">{{ trans('lang.commission_type') }}</label>

                            <div class="col-7">

                                <select class="form-control bulk_commission_type" id="bulk_commission_type">

                                    <option value="percentage">{{ trans('lang.coupon_percent') }}</option>

                                    <option value="fixed">{{ trans('lang.coupon_fixed') }}</option>

                                </select>

                            </div>

                        </div>

                        <div class="form-group row width-50">

                            <label class="col-4 control-label">{{ trans('lang.admin_commission') }}</label>

                            <div class="col-7">

                                <input type="number" value="0" class="form-control bulk_commission_fix">

                            </div>

                        </div>

                        <div class="form-group col-12 text-center">

                            <div class="col-12">

                                <button type="button" id="bulk_update_btn" class="btn btn-primary edit-setting-btn"><i

                                        class="fa fa-save"></i> {{ trans('lang.bulk_update') }}</button>

                            </div>

                        </div>

                    </fieldset>

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

            var database = firebase.firestore();

            var restaurant = database.collection('settings').doc("vendor");

            var sections = database.collection('sections').where('serviceTypeFlag', 'in', ['delivery-service',

                'ondemand-service', 'ecommerce-service'

            ]);



            $(document).ready(function() {

                sections.get().then(async function(snapshots) {

                    snapshots.docs.forEach((listval) => {

                        var data = listval.data();

                        var $optgroup = $('#section').find("optgroup[label='" + data.serviceType +

                            "']");

                        if ($optgroup.length === 0) {

                            $optgroup = $("<optgroup></optgroup>").attr("label", data.serviceType);

                            $('#section').append($optgroup);

                        }

                        $optgroup.append(

                            $("<option></option>")

                            .attr("value", data.id)

                            .attr("servicetype", data.serviceType)

                            .text(data.name)

                        );



                    });

                });



                $('#food_restaurant_type').on('change', function() {

                    if ($('#food_restaurant_type').val() === 'custom') {

                        $('#food_restaurant').show();

                        $('#food_restaurant').select2({

                            placeholder: "{{ trans('lang.select_user') }}",

                            allowClear: true,

                            width: '100%',

                            dropdownAutoWidth: true

                        });

                    } else {

                        $('#food_restaurant').hide();

                        $('#food_restaurant').select2('destroy');

                    }

                });

                $('#section').on('change', async function() {



                    var sectionId = $('#section').val();

                    var serviceType = $('#section option:selected').attr('servicetype');



                    $('#food_restaurant').empty();

                    

                    if (serviceType == "On Demand Service") {

                        database.collection('users').where('role', '==', 'provider').where('section_id', '==', sectionId).orderBy('firstName',

                            'asc').get().then(async function(

                            snapshots) {

                            snapshots.docs.forEach((listval) => {

                                var data = listval.data();

                                $('#food_restaurant').append($("<option></option>")

                                    .attr("value", data.id)

                                    .text(data.firstName + ' ' + data.lastName));

                            });

                        });

                    } else {

                        database.collection('vendors').where('section_id', '==', sectionId).orderBy('title',

                            'asc').get().then(async function(

                            snapshots) {

                            snapshots.docs.forEach((listval) => {

                                var data = listval.data();

                                $('#food_restaurant').append($("<option></option>")

                                    .attr("value", data.id)

                                    .text(data.title));

                            })

                        });

                    }



                });



                jQuery("#data-table_processing").show();



                restaurant.get().then(async function(snapshots) {

                    var restaurantdata = snapshots.data();

                    if (restaurantdata == undefined) {

                        database.collection('settings').doc('vendor').set({});

                    }

                    try {

                        if (restaurantdata.subscription_model) {

                            $("#subscription_model").prop('checked', true);

                        }

                    } catch (error) {}

                    jQuery("#data-table_processing").hide();

                })



                $(document).on("click", "input[name='subscription_model']", function(e) {



                    var subscription_model = $("#subscription_model").is(":checked");

                    var userConfirmed = confirm(subscription_model ?

                        "{{ trans('lang.enable_subscription_plan_confirm_alert') }}" :

                        "{{ trans('lang.disable_subscription_plan_confirm_alert') }}");

                    if (!userConfirmed) {

                        $(this).prop("checked", !subscription_model);

                        return;

                    }

                    database.collection('settings').doc("vendor").update({

                        'subscription_model': subscription_model,

                    });

                    if (subscription_model) {

                        Swal.fire('Update Complete!', `Subscription model enabled.`, 'success');

                    } else {

                        Swal.fire('Update Complete!', `Subscription model disabled.`, 'success');

                    }

                });





                $('#bulk_update_btn').on('click', async function() {



                    const commissionType = $("#bulk_commission_type").val();

                    const fixCommission = parseFloat($(".bulk_commission_fix").val());

                    const isEnabled = true;

                    const adminCommission = {

                        'commission': fixCommission,

                        'enable': isEnabled,

                        'type': commissionType,

                    };



                    const foodRestaurantType = $('#food_restaurant_type').val();

                    const selectedIds = $('#food_restaurant').val() || [];

                    const sectionId = $('#section').val();

                    var serviceType = $('#section option:selected').attr('servicetype');

                    if (sectionId == '') {

                        Swal.fire('Please select section!', '', 'warning');

                        return false;

                    }

                    try {

                        let total = 0,

                            processed = 0;



                        const getVendors = async () => {

                            if (foodRestaurantType === 'all' && serviceType !=

                                'On Demand Service') {

                                return await database.collection('vendors').where('section_id',

                                    '==', sectionId).get();

                            } else if (foodRestaurantType === 'all' && serviceType ==

                                'On Demand Service') {

                                return await database.collection('users').where('role', '==',

                                    'provider').where('section_id','==', sectionId).get();

                            } else {

                                const chunks = [];

                                for (let i = 0; i < selectedIds.length; i += 10) {

                                    chunks.push(selectedIds.slice(i, i + 10));

                                }

                                if (serviceType == 'On Demand Service') {

                                    const snapshots = await Promise.all(chunks.map(chunk =>

                                        database.collection('users').where('id', 'in',

                                            chunk)

                                        .get()

                                    ));

                                    return snapshots.flatMap(snapshot => snapshot.docs);

                                } else {

                                    const snapshots = await Promise.all(chunks.map(chunk =>

                                        database.collection('vendors').where('id', 'in',

                                            chunk)

                                        .get()

                                    ));

                                    return snapshots.flatMap(snapshot => snapshot.docs);

                                }





                            }

                        };



                        const vendorsSnapshot = await getVendors();

                        total = vendorsSnapshot.length;



                        if (total > 0) {

                            Swal.fire({

                                title: 'Processing...',

                                text: '0% Complete',

                                allowOutsideClick: false,

                                onBeforeOpen: () => Swal.showLoading()

                            });



                            for (const doc of vendorsSnapshot) {

                                await doc.ref.update({

                                    "adminCommission": adminCommission

                                });

                                processed++;

                                Swal.update({

                                    text: `${Math.round((processed/total)*100)}% Complete`

                                });

                            }



                            Swal.fire('Update Complete!', `${total} users updated.`, 'success');

                        } else {

                            Swal.fire('No vendors selected or found!', '', 'warning');

                        }

                    } catch (error) {

                        Swal.fire('Error', 'An error occurred during the update process.', 'error');

                        console.error('Error:', error);

                    }

                });





            })



            function ShowHideDiv() {

                var checkboxValue = $("#enable_commission").is(":checked");

                if (checkboxValue) {

                    $(".admin_commision_detail").show();

                } else {

                    $(".admin_commision_detail").hide();

                }

            }

        </script>

    @endsection

