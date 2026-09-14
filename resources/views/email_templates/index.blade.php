@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.email_templates')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.email_templates_table')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>
    <div class="container-fluid">
       <div class="admin-top-section"> 
        <div class="row">
            <div class="col-12">
                <div class="d-flex top-title-section pb-4 justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/email.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.email_templates')}}</h3>
                        <span class="counter ml-3 total_count"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                        <div class="select-box pl-3">
                        </div>
                    </div>
                </div>
            </div>
        </div> 
       </div>
       <div class="table-list">
       <div class="row">
           <div class="col-12">
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.email_templates_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.email_templates_table_text')}}</p>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="emailTemplatesTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                        <th>{{trans('lang.type')}}</th>
                                        <th>{{trans('lang.subject')}}</th>
                                        <th>{{trans('lang.actions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="emailTemplatesTbody">
                                    </tbody>
                            </table>
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
        var refData = database.collection('email_templates').orderBy('createdAt', 'desc');
        var append_list = '';

        $(document).ready(function () {

            jQuery("#data-table_processing").show();

            append_list = document.getElementById('emailTemplatesTbody');
            append_list.innerHTML = '';
            refData.get().then(async function (snapshots) {
                var html = '';
                if (snapshots.docs.length > 0) {
                    $('.total_count').text(snapshots.docs.length); 
                    html = await buildHTML(snapshots);
                }
                else
                {
                    $('.total_count').text(0); 
                }
                html = await buildHTML(snapshots);
                 $(function () {
                                $('[data-toggle="tooltip"]').tooltip();
                            });
                jQuery("#data-table_processing").hide();
                if (html != '') {
                    append_list.innerHTML = html;
                    $('[data-toggle="tooltip"]').tooltip();
                }

                var table =$('#emailTemplatesTable').DataTable({
                    order: [],
                    columnDefs: [
                        {orderable: false, targets: [2]},
                    ],
                    order: [0,"asc"],
                    "language": datatableLang,
                    responsive: true
                });
                table.on('search.dt', function() {
                    var filteredCount = table.rows({ search: 'applied' }).count();
                    $('.total_count').text(filteredCount);  // Update count
                });
            });

        });

        $("#is_active").click(function () {
            $("#emailTemplatesTable .is_open").prop('checked', $(this).prop('checked'));
        });

        $("#deleteAll").click(function () {
            if ($('#emailTemplatesTable .is_open:checked').length) {
                if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                    jQuery("#data-table_processing").show();
                    $('#emailTemplatesTable .is_open:checked').each(function () {
                        var dataId = $(this).attr('dataId');

                        database.collection('email_templates').doc(dataId).delete().then(function () {

                            window.location.reload();
                        });

                    });

                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });


        function buildHTML(snapshots) {

            var html = '';
            var number = [];
            var count = 0;
            snapshots.docs.forEach(async (listval) => {
                var listval = listval.data();

                var data = listval;
                data.id = listval.id;
                html = html + '<tr>';
                newdate = '';
                var id = data.id;
                var route1 = '{{route("email-templates.save",":id")}}';
                route1 = route1.replace(":id", id);

                var type = '';

                if (data.type == "new_order_placed") {
                    type = "{{trans('lang.new_order_placed')}}";

                } else if (data.type == "new_vendor_signup") {
                    type = "{{trans('lang.new_vendor_signup')}}";
                } else if (data.type == "payout_request") {
                    type = "{{trans('lang.payout_request')}}";
                } else if (data.type == "payout_request_status") {
                    type = "{{trans('lang.payout_request_status')}}";

                } else if (data.type == "wallet_topup") {
                    type = "{{trans('lang.wallet_topup')}}";
                }else if (data.type == "new_ride_book") {
                    type = "{{trans('lang.new_ride_book')}}";
                }else if (data.type == "new_parcel_book") {
                    type = "{{trans('lang.new_parcel_book')}}";
                }else if (data.type == "new_car_book") {
                    type = "{{trans('lang.new_car_book')}}";
                }else if (data.type == "new_ondemand_book") {
                    type = "{{trans('lang.new_ondemand_book')}}";
                }


                html = html + '<td>' + type + '</td>';
                html = html + '<td>' + data.subject + '</td>';

                html = html + '<td><span class="action-btn">' +
                    '<a href="' + route1 + '" data-toggle="tooltip" title="{{trans("lang.edit")}}"><i class="mdi mdi-lead-pencil"></i></a></span></td>';

                html = html + '</tr>';
                count = count + 1;
            });
            return html;
        }

        $(document).on("click", "a[name='notifications-delete']", function (e) {
            var id = this.id;
            database.collection('email_templates').doc(id).delete().then(function () {
                window.location.reload();
            });
        });
    </script>


@endsection