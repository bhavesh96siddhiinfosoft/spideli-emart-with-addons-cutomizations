@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.notifications')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.send_notification')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/notification.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.notifications')}}</h3>
                        <span class="counter ml-3 notification_count"></span>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.send_notification')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.notifications_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! url('notification/send') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.create_notificaion')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="notificationTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <?php if (in_array('notification.delete', json_decode(@session('user_permissions'), true))) { ?>
                                            <th class="delete-all"><input type="checkbox" id="is_active">
                                                <label class="col-3 control-label" for="is_active">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                        <i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a>
                                                    </label>
                                            </th>
                                        <?php } ?>
                                        <th>{{trans('lang.notification_subject')}}</th>

                                        <th>{{trans('lang.notification_message')}}</th>

                                        <th>{{trans('lang.date_created')}}</th>
                                        <?php if (in_array('notification.delete', json_decode(@session('user_permissions'),true))) { ?>

                                        <th>{{trans('lang.actions')}}</th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody id="append_restaurants">
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

        var user_permissions = '<?php echo @session('user_permissions') ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkDeletePermission = false;
        if ($.inArray('notification.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }

        var database = firebase.firestore();
        var offest = 1;
        var pagesize = 10;
        var pagesizes = 0;
        var end = null;
        var endarray = [];
        var start = null;
        var user_number = [];
        var refData = database.collection('notifications');
        var ref = refData.orderBy('createdAt', 'desc');
        

        $(document).ready(function () {

            const table = $('#notificationTable').DataTable({
                pageLength: 10, // Number of rows per page
                processing: false, // Show processing indicator
                serverSide: true, // Enable server-side processing
                responsive: true,
                ajax: async function (data, callback, settings) {
                    const start = data.start;
                    const length = data.length;
                    const searchValue = data.search.value.toLowerCase();
                    const orderColumnIndex = data.order[0].column;
                    const orderDirection = data.order[0].dir;
                    var orderableColumns = (checkDeletePermission) ? ['', 'subject','message','createdAt', ''] : ['subject','message','createdAt', '']; // Ensure this matches the actual column names
                    const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                    if (searchValue.length >= 3 || searchValue.length === 0) {
                        $('#data-table_processing').show();
                    }
                    await ref.get().then(async function (querySnapshot) {
                        if (querySnapshot.empty) {
                            $('.notification_count').text(0); 
                            $('#data-table_processing').hide(); // Hide loader
                            callback({
                                draw: data.draw,
                                recordsTotal: 0,
                                recordsFiltered: 0,
                                data: [] // No data
                            });
                            return;
                        }

                        let records = [];
                        let filteredRecords = [];
                        

                        await Promise.all(querySnapshot.docs.map(async (doc) => {
                            let childData = doc.data();
                            childData.id = doc.id; // Ensure the document ID is included in the data    

                            var date = '';
                            var time = '';
                            if (childData.hasOwnProperty("createdAt") && childData.createdAt != '') {
                                try {
                                    date = childData.createdAt.toDate().toDateString();
                                    time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                                } catch (err) {

                                }
                            }

                            var createdAt = date + '<br> ' + time ;          
                        
                            if (searchValue) {

                                if (
                                    (childData.subject && childData.subject.toLowerCase().toString().includes(searchValue)) ||
                                    (childData.message && childData.message.toLowerCase().toString().includes(searchValue)) ||
                                    (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1)) {
                                    filteredRecords.push(childData);
                                }
                            } else {
                                filteredRecords.push(childData);
                            }
                        }));

                        filteredRecords.sort((a, b) => {
                            let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase().trim() : '';
                            let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase().trim() : '';

                             if (orderByField === 'createdAt' && a[orderByField] != '' && b[orderByField] != '') {
                                try {
                                    aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                                    bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                                } catch (err) {
                                }
                            }      
                        
                            if (orderDirection === 'asc') {
                                return (aValue > bValue) ? 1 : -1;
                            } else {
                                return (aValue < bValue) ? 1 : -1;
                            }
                        });

                        const totalRecords = filteredRecords.length;
                        $('.notification_count').text(totalRecords); 
                        const paginatedRecords = filteredRecords.slice(start, start + length);

                        await Promise.all(paginatedRecords.map(async (childData) => {
                            var getData = await buildHTML(childData);
                            records.push(getData);
                        }));
                        $(function () {
                                $('[data-toggle="tooltip"]').tooltip();
                            });
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: totalRecords, // Total number of records in Firestore
                            recordsFiltered: totalRecords, // Number of records after filtering (if any)
                            data: records // The actual data to display in the table
                        });
                    }).catch(function (error) {
                        console.error("Error fetching data from Firestore:", error);
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: [] // No data due to error
                        });
                    });
                },
                order: (checkDeletePermission) ? [[3, 'asc']] : [[2, 'asc']],
                columnDefs: [
                    
                    { orderable: false, targets: (checkDeletePermission) ? [0,4] : [0,4] },
                ],
               "language": datatableLang,
            });

            function debounce(func, wait) {
                let timeout;
                const context = this;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), wait);
                };
            }
        });

        $("#is_active").click(function () {
            $("#notificationTable .is_open").prop('checked', $(this).prop('checked'));

        });

        $("#deleteAll").click(function () {
            if ($('#notificationTable .is_open:checked').length) {
                if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                    jQuery("#data-table_processing").show();
                    $('#notificationTable .is_open:checked').each(function () {
                        var dataId = $(this).attr('dataId');
                        database.collection('notifications').doc(dataId).delete().then(function () {
                            setTimeout(function () {
                                window.location.reload();
                            }, 5000)
                        });
                    });
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });

        async function buildHTML(val) {

            var html = [];
            var number = [];
            var count = 0;
            newdate = '';
            var id = val.id;
            <?php if (in_array('notification.delete', json_decode(@session('user_permissions'),true))) { ?>

            html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label></td>');

            <?php }?>
            html.push('<td>' + val.subject + '</td>');

            html.push('<td>' + val.message + '</td>');

            var date = '';
            var time = '';
            if (val.hasOwnProperty("createdAt")) {
                try {
                    date = val.createdAt.toDate().toDateString();
                    time = val.createdAt.toDate().toLocaleTimeString('en-US');
                } catch (err) {
                }
                html.push('<td class="dt-time">' + date + ' ' + time + '</td>');
            } else {
                html.push('<td></td>');
            }

            <?php if (in_array('notification.delete', json_decode(@session('user_permissions'),true))) { ?>

            html.push('<span class="action-btn"><a id="' + val.id + '" name="notifications-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" title="{{ trans("lang.delete") }}"><i class="mdi mdi-delete"></i></a></span>');

            <?php }?>
            count = count + 1;
            return html;
        }

        $(document).on("click", "a[name='notifications-delete']", function (e) {
            var id = this.id;
            jQuery("#data-table_processing").show();
            database.collection('notifications').doc(id).delete().then(function () {
                window.location.reload();
            });
        });

    </script>

    @endsection
