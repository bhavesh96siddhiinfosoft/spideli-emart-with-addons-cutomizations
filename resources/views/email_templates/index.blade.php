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

        /* The templates this panel expects to exist.
         *
         * The Type field on the edit screen is read-only and there is no
         * "create template" screen - the original nine were seeded with the
         * product. So a new template has to be created here, once, or the
         * client has no way to reach it and the email never goes out.
         *
         * Idempotent: a type that already exists is left exactly as it is,
         * wording included. Opening this screen never overwrites anything the
         * client has edited.
         */
        var EXPECTED_TEMPLATES = [
            {
                type: 'subscription_purchased',
                subject: 'Your subscription is confirmed - {planname}',
                message: '<p>Hello {username},</p>' +
                    '<p>Thank you. Your subscription is now active.</p>' +
                    '<p><strong>Plan:</strong> {planname}<br>' +
                    '<strong>Store:</strong> {storename}<br>' +
                    '<strong>Amount paid:</strong> {price}<br>' +
                    '<strong>Paid with:</strong> {paymentmethod}<br>' +
                    '<strong>Valid until:</strong> {expirydate}</p>' +
                    '<p>You can see this subscription at any time under Subscriptions in your account.</p>',
                isSendToAdmin: false
            },
            {
                type: 'subscription_purchased_admin',
                subject: 'New subscription purchased - {planname}',
                message: '<p>A customer has bought a subscription.</p>' +
                    '<p><strong>Customer:</strong> {username} ({customeremail})<br>' +
                    '<strong>Plan:</strong> {planname}<br>' +
                    '<strong>Store:</strong> {storename}<br>' +
                    '<strong>Amount:</strong> {price}<br>' +
                    '<strong>Paid with:</strong> {paymentmethod}<br>' +
                    '<strong>Valid until:</strong> {expirydate}<br>' +
                    '<strong>Purchased on:</strong> {date}</p>',
                isSendToAdmin: true
            }
        ];

        /* {storename} is empty for the platform's own order-history plan,
         * which no store sells. That is why the templates read as they do -
         * an empty line rather than a wrong one. */
        async function ensureExpectedTemplates() {
            try {
                for (var i = 0; i < EXPECTED_TEMPLATES.length; i++) {
                    var wanted = EXPECTED_TEMPLATES[i];

                    var existing = await database.collection('email_templates')
                        .where('type', '==', wanted.type).limit(1).get();

                    if (!existing.empty) {
                        continue;
                    }

                    var id = database.collection('tmp').doc().id;
                    await database.collection('email_templates').doc(id).set({
                        'id': id,
                        'subject': wanted.subject,
                        'message': wanted.message,
                        'type': wanted.type,
                        'isSendToAdmin': wanted.isSendToAdmin,
                        'createdAt': firebase.firestore.FieldValue.serverTimestamp()
                    });
                }
            } catch (err) {
                /* Never blocks the list. A panel that will not open because a
                 * template could not be created is worse than a missing
                 * template. */
                console.error('expected email templates could not be created', err);
            }
        }

        $(document).ready(async function () {

            jQuery("#data-table_processing").show();

            append_list = document.getElementById('emailTemplatesTbody');
            append_list.innerHTML = '';

            /* Creates anything missing before the list is drawn, so the two
             * subscription templates appear the first time this screen is
             * opened after the update. */
            await ensureExpectedTemplates();

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
                }else if (data.type == "subscription_purchased") {
                    type = "{{trans('lang.subscription_purchased')}}";
                }else if (data.type == "subscription_purchased_admin") {
                    type = "{{trans('lang.subscription_purchased_admin')}}";
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