@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.parcel_weight')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>

                <li class="breadcrumb-item active">{{trans('lang.parcel_weight')}}</li>

            </ol>

        </div>

        <div>

        </div>

    </div>


    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-body">
                        <div class="resttab-sec">

                            <div class="error_top"></div>
                            <div class="row vendor_payout_create">
                                <div class="vendor_payout_create-inner">
                                    <fieldset>
                                        <legend>{{trans('lang.parcel_weight')}}</legend>

                                        <div class="form-group row">

                                            <div class="special_offer_div">

                                                <div class="form-group row">
                                                    <label class="col-12 control-label"
                                                           style="color:red;font-size:15px;">{{trans('lang.edit_button_save_note')}} </label>
                                                </div>
                                                <?php if (in_array('parcel.weight.create', json_decode(@session('user_permissions'),true))) { ?>
                                                    <div class="form-group row">

                                                        <div class="col-12">
                                                            <button type="button"
                                                                    class="btn btn-primary add_more_sunday"
                                                                    onclick="addMoreButton()">{{trans('lang.add_more')}}
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                <div class="parcel_weight" style="display:none">
                                                    <table class="booking-table" id="parcel_weight_table">
                                                        <tr>
                                                            <th style="width:50%"><label class="col-3 control-label">{{trans('lang.title')}}</label>
                                                            </th>
                                                            <th style="width:40%"><label class="col-3 control-label">{{trans('lang.delivery_charge')}}</label>
                                                            </th>

                                                            <?php if (in_array('parcel.weight.edit', json_decode(@session('user_permissions'),true)) || in_array('parcel.weight.delete', json_decode(@session('user_permissions'),true))) { ?>

                                                                <th style="width:20%"><label
                                                                            class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                                </th>
                                                            <?php } ?>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                    </fieldset>

                                    <div class="form-group col-12 text-center btm-btn">
                                        <button type="button" class="btn btn-primary  save-form-btn"><i
                                                    class="fa fa-save"></i> {{trans('lang.save')}}
                                        </button>
                                        <a href="{!! route('parcel_weight') !!}" class="btn btn-default"><i
                                                    class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @endsection

    @section('scripts')

    <script type="text/javascript">

        var database = firebase.firestore();
        var arrayParcelWeight = [];

        var parcel_weight = database.collection('parcel_weight');
        var editCount = 0;

        $(document).ready(function () {


            parcel_weight.get().then(async function (snapshots) {

                snapshots.docs.forEach((listval) => {
                    var parcel_weight_data = listval.data();
					
                    var object = {
                        'id': parcel_weight_data.id,
                        'title': parcel_weight_data.title,
                        'delivery_charge': parcel_weight_data.delivery_charge
                    };

                    arrayParcelWeight.push(object);


                    $(".parcel_weight").show();

                    var html = '<tr>' +
                        '<td style="width:40%"><input type="text" value="' + parcel_weight_data.title + '" class="form-control" id="title_' + parcel_weight_data.id + '" onchange="replaceText(`' + parcel_weight_data.id + '`)"></td>' +
                        '<td style="width:40%"><input type="text" value="' + parcel_weight_data.delivery_charge + '" class="form-control" id="price_' + parcel_weight_data.id + '" onchange="replaceText(`' + parcel_weight_data.id + '`)"></td>';

                    <?php if (in_array('parcel.weight.edit', json_decode(@session('user_permissions'),true)) || in_array('parcel.weight.delete', json_decode(@session('user_permissions'),true))) { ?>

                    html += '<td class="action-btn" style="width:20%">';
                    <?php if (in_array('parcel.weight.edit', json_decode(@session('user_permissions'),true))){?>
                    html += '<span class="edit-form-btn"><button type="button" class="btn btn-primary edit_' + parcel_weight_data.id + '" onclick="editData(`' + editCount + '`,`' + parcel_weight_data.id + '`)"><i class="fa fa-edit"></i></button>&nbsp;&nbsp</span>';
                    <?php }
                    if (in_array('parcel.weight.delete', json_decode(@session('user_permissions'),true))){
                    ?>
                    html += '<span class="delete-btn"><button type="button" class="btn btn-primary delete_' + parcel_weight_data.id + '" onclick="deleteData(`' + parcel_weight_data.id + '`)"><i class="fa fa-trash"></i></button></span>';
                    <?php }?>
                    html += '</td>';
                    <?php }?>
                    html += '</tr>';

                    $('#parcel_weight_table tr:last').after(html);
                    editCount++;
                    $('#data-table_processing').hide();
                });

            });
        });


        function replaceText(id) {
            $('.edit_' + id).html("<i class='fa fa-save'></i>");
        }


        var countAddButton = 1;

        function addMoreButton() {
            count = countAddButton;
            $(".parcel_weight").show();

            $('#parcel_weight_table tr:last').after('<tr>' +
                '<td><input type="text" class="form-control" id="title_' + count + '"></td>' +
                '<td><input type="text" class="form-control" id="price_' + count + '"></td>' +
                '<td class="action-btn">' +
                '<button type="button" class="btn btn-primary save_' + count + '" onclick="saveData(' + count + ')">Save</button>' +
                '</td></tr>');
            countAddButton++;
        }

        function saveData(count) {
            var title = $("#title_" + count).val();
            var price = $("#price_" + count).val();
			
            if (title == "") {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.parcel_title_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (price == "") {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.parcel_price_error')}}</p>");
                window.scrollTo(0, 0);
            } else {
                $(".error_top").hide();
                $(".error_top").html("");
                var alovelaceDocumentRef = database.collection('vendor_orders').doc();
				
                if (alovelaceDocumentRef.id) {
                    id_rendom = alovelaceDocumentRef.id;
                }
                var object = {
                    'id': id_rendom,
                    'title': title,
                    'delivery_charge': price
                };


              
				
				arrayParcelWeight.push(object);
				
                $(".save_" + count).hide();
                $("#title_" + count).attr('disabled', "true");
                $("#price_" + count).attr('disabled', "true");

            }
        }

        function editData(count, actionId) {
            if (is_disable_delete == 1) {
                alert(doNotUpdateAlert);
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
			
            var title = $("#title_" + actionId).val();
            var price = $("#price_" + actionId).val();
			
            if (title == "") {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.parcel_title_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (price == "") {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.parcel_price_error')}}</p>");
                window.scrollTo(0, 0);
            } else {
                $(".error_top").hide();
                $(".error_top").html("");
                var object = {
                    'id': actionId,
                    'title': title,
                    'delivery_charge': price
                };
				
					database.collection('parcel_weight').doc(actionId).update({
						 'id': actionId,
						 'title': title,
						 'delivery_charge': price
					});
                arrayParcelWeight[count] = object;
				
            }

        }

        function deleteData(actionId) {
            if (is_disable_delete == 1) {
                alert(doNotDeleteAlert);
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
			
            database.collection('parcel_weight').doc(actionId).delete().then(function (result) {
                window.location.href = '{{ url()->current() }}';
            });
        }

        $(document).on('click', '.save-form-btn', function () {
            for (var i = 0; i < arrayParcelWeight.length; i++) {
                database.collection('parcel_weight').doc(arrayParcelWeight[i]['id']).set({
                    'id': arrayParcelWeight[i]['id'],
                    'title': arrayParcelWeight[i]['title'],
                    'delivery_charge': arrayParcelWeight[i]['delivery_charge']
                }).then(function (result) {
                    window.location.href = '{{ route("parcel_weight")}}';
                });
            }
        });

    </script>

    @endsection
