@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.user_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.user_table')}}</li>
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
                            <span class="icon mr-3"><img src="{{ asset('images/users.png') }}"></span>
                            <h3 class="mb-0">{{trans('lang.user_plural')}}</h3>
                            <span class="counter ml-3 total_count"></span>
                        </div>                    
                        <div class="d-flex top-title-right align-self-center">
                                <div class="select-box pl-3">
                                    <select class="form-control status_selector filteredRecords">
                                        <option value="">{{trans("lang.status")}}</option>
                                        <option value="active"  >{{trans("lang.active")}}</option>
                                        <option value="inactive"  >{{trans("lang.in_active")}}</option>
                                    </select>
                                </div>
                                <div class="select-box pl-3">
                                    <div id="daterange"><i class="fa fa-calendar"></i>&nbsp;
                                        <span></span>&nbsp; <i class="fa fa-caret-down"></i>
                                    </div>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.user_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.users_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('users.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.user_create')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="userTable"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <?php if (in_array('users.delete', json_decode(@session('user_permissions'),true))) { ?>
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll"
                                    class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php } ?>
                                    <th>{{trans('lang.user_info')}}</th>
                                    <th>{{trans('lang.contact_info')}}</th>
                                    <th>{{trans('lang.date')}}</th>
                                    <th>{{trans('lang.active')}}</th>
                                    <th>{{trans('lang.actions')}}</th>
                                </tr>
                                </thead>  
                                <tbody id="append_list1">
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
    /* A customer belongs to every region they have ordered in, so they are
     * matched on the regionIds array rather than a single region.
     *
     * Deliberately not ordered in the query: Firestore drops documents that do
     * not carry the ordered field, which hid every customer without a
     * createdAt. The rows are fetched in full and sorted in the browser further
     * down, where a missing date is handled, so ordering here only lost data. */
    var ref = regionScopedAny(database.collection('users').where("role", "in", ["customer"]));
    var user_permissions = '<?php echo @session('user_permissions') ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    var checkChatPermission = false;
    if ($.inArray('users.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
    if ($.inArray('users.chat', user_permissions) >= 0) {
        checkChatPermission = true;
    }
    $('.status_selector').select2({
        placeholder: '{{trans("lang.status")}}',  
        minimumResultsForSearch: Infinity,
        allowClear: true 
    });
    
    $('select').on("select2:unselecting", function(e) {
        var self = $(this);
        setTimeout(function() {
            self.select2('close');
        }, 0);
    });
    
    function setDate() {
        $('#daterange span').html('{{trans("lang.select_range")}}');
        $('#daterange').daterangepicker({
            autoUpdateInput: false, 
        }, function (start, end) {
            $('#daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $('.filteredRecords').trigger('change'); 
        });
        $('#daterange').on('apply.daterangepicker', function (ev, picker) {
            $('#daterange span').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
            $('.filteredRecords').trigger('change');
        });
        $('#daterange').on('cancel.daterangepicker', function (ev, picker) {
            $('#daterange span').html('{{trans("lang.select_range")}}');
            $('.filteredRecords').trigger('change'); 
        });
    }

    setDate(); 

    $('.filteredRecords').change(async function() {
        var status = $('.status_selector').val();
        var daterangepicker = $('#daterange').data('daterangepicker');
        ref = regionScopedAny(database.collection('users').where("role", "in", ["customer"]));
        if ($('#daterange span').html() != '{{trans("lang.select_range")}}' && daterangepicker) {
            var from = moment(daterangepicker.startDate).toDate();
            var to = moment(daterangepicker.endDate).toDate();
            if (from && to) { 
                var fromDate = firebase.firestore.Timestamp.fromDate(new Date(from));
                ref = ref.where('createdAt', '>=', fromDate);
                var toDate = firebase.firestore.Timestamp.fromDate(new Date(to));
                ref = ref.where('createdAt', '<=', toDate);
            }
        }
        if (status) {
            ref = (status == "active") ? ref.where('active', '==', true) : ref.where('active', '==', false);
        }
        $('#userTable').DataTable().ajax.reload();
    });

    $(document).ready(function() {
        $(document.body).on('click', '.redirecttopage', function() {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });
        jQuery("#data-table_processing").show();
        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })
        $(document).on('click', '.dt-button-collection .dt-button', function () {
            $('.dt-button-collection').hide();
            $('.dt-button-background').hide();
        });
        $(document).on('click', function (event) {
            if (!$(event.target).closest('.dt-button-collection, .dt-buttons').length) {
                $('.dt-button-collection').hide();
                $('.dt-button-background').hide();
            }
        });
        var fieldConfig = {
            columns: [
                { key: 'name', header: "{{trans('lang.user_info')}}" },
               { 
                    key: 'contactInfo', 
                    header: "{{trans('lang.contact_info')}}",
                    cell: row => `${shortEmail(row.email)}<br>${row.hasPlusSign ? `+${row.maskedPhone}` : row.maskedPhone}`
                },
                { key: 'active', header: "{{trans('lang.active')}}" },
                { key: 'createdAt', header: "{{trans('lang.date')}}" },
            ],
            fileName: "{{trans('lang.user_table')}}",
        };
        const table = $('#userTable').DataTable({
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
                const orderableColumns = (checkDeletePermission) ? ['', 'name', 'contactInfo', 'createdAt', '', ''] : ['name', 'contactInfo', 'createdAt', '', ''];
                const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }
                await ref.get().then(async function (querySnapshot) {
                    if (querySnapshot.empty) {
                        $('.total_count').text(0);
                        console.error("No data found in Firestore.");
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
                        childData.name = childData.firstName + ' ' + childData.lastName;                                 
                        childData.phone = (childData.phoneNumber != '' && childData.phoneNumber != null && childData.phoneNumber.slice(0, 1) == '+') ? childData.phoneNumber.slice(1) : childData.phoneNumber;     
                        childData.maskedPhone = EditPhoneNumber(childData.phone);                       
                        childData.hasPlusSign = childData.phoneNumber.startsWith('+');                        
                        childData.exportPhone = childData.hasPlusSign ? `+${childData.maskedPhone}` : childData.maskedPhone;
                        childData.contactInfo = `${childData.email} ${childData.phoneNumber}`;
                        if (searchValue) {
                            var date = '';
                            var time = '';
                            if (childData.hasOwnProperty("createdAt")) {
                                try {
                                    date = childData.createdAt.toDate().toDateString();
                                    time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                                } catch (err) {
                                }
                            }
                            var createdAt = date + '<br> ' + time;
                            if (
                                (childData.name && childData.name.toString().toLowerCase().includes(searchValue)) ||
                                (childData.email && childData.email.toString().toLowerCase().includes(searchValue))
                                || (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1) || (childData.phoneNumber && childData.phoneNumber.toString().toLowerCase().includes(searchValue))
                            ) {
                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    }));
                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ;
                        let bValue = b[orderByField] ;                       
                        if (orderByField === 'createdAt') {
                            aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                            bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                        }else{
                            aValue = a[orderByField] ? a[orderByField].toString().toLowerCase().trim() : '';
                            bValue = b[orderByField] ? b[orderByField].toString().toLowerCase().trim() : ''
                        }
                        if (orderDirection === 'asc') {
                            return (aValue > bValue) ? 1 : -1;
                        } else {
                            return (aValue < bValue) ? 1 : -1;
                        }
                    });
                    const totalRecords = filteredRecords.length;
                    $('.total_count').text(totalRecords); 
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
                        filteredData: filteredRecords,
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
            order: (checkDeletePermission) ? [3, 'desc'] : [2, 'desc'],
            columnDefs: [
                {
                    orderable: false,
                    targets: (checkDeletePermission) ? [0, 3, 4, 5] : [0, 2, 3, 4],
                },
                {
                    type: 'date',
                    render: function(data) {
                        return data;
                    },
                    targets: (checkDeletePermission) ? [3] : [2],
                }
            ],
            "language": datatableLang,

            dom: 'lfrtipB',
            buttons: [
                {
                    extend: 'collection',
                    text: '<i class="mdi mdi-cloud-download"></i> {{trans("lang.export_as")}}',
                    className: 'btn btn-info',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '{{trans("lang.export_excel")}}',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'excel',fieldConfig);
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '{{trans("lang.export_pdf")}}',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'pdf',fieldConfig);
                            }
                        },   
                        {
                            extend: 'csvHtml5',
                            text: '{{trans("lang.export_csv")}}',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'csv',fieldConfig);
                            }
                        }
                    ]
                }
            ],
            initComplete: function() {
                $(".dataTables_filter").append($(".dt-buttons").detach());
                $('.dataTables_filter input').attr('placeholder', '{{trans("lang.search_here")}}').attr('autocomplete','new-password').val('');
                $('.dataTables_filter label').contents().filter(function() {
                    return this.nodeType === 3; 
                }).remove();
            }
        });
        function debounce(func, wait) {
            let timeout;
            const context = this;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }
        $('#search-input').on('input', debounce(function () {
            const searchValue = $(this).val();
            if (searchValue.length >= 3) {
                $('#data-table_processing').show();
                table.search(searchValue).draw();
            } else if (searchValue.length === 0) {
                $('#data-table_processing').show();
                table.search('').draw();
            }
        }, 300));
    });

    async function buildHTML(val) {
        var html = [];
        newdate = '';
        var id = val.id;
        var route1 = '{{route("users.edit",":id")}}';
        route1 = route1.replace(':id', id);
        var user_view = '{{route("users.view",":id")}}';
        user_view = user_view.replace(':id', id);
        var trroute1 = '{{route("users.walletstransaction",":id")}}';
        trroute1 = trroute1.replace(':id', id);
        if (checkDeletePermission) {
        html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
            'for="is_open_' + id + '" ></label></td>');
        }
       if (!val.profilePictureURL || val.profilePictureURL.trim() === '') {
            html.push('<td><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image"><a href="' + user_view + '" class="redirecttopage left_space">' + val.firstName + ' ' + val.lastName + '</a></td>');
        } else {
            html.push('<td><img class="rounded" style="width:50px" src="' + val.profilePictureURL + '"  onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"  alt="image"> <a href="' + user_view + '" class="redirecttopage left_space">' + val.firstName + ' ' + val.lastName + '</a></td>');
        }
       html.push('<td>' + shortEmail(val.email) + '<br>' + (val.phoneNumber && val.phoneNumber.includes('+') ? '+' + EditPhoneNumber(val.phoneNumber.slice(1)) : EditPhoneNumber(val.phoneNumber)) + '</td>');
        var date = '';
        var time = '';
        if (val.hasOwnProperty("createdAt")) {
            try {
                date = val.createdAt.toDate().toDateString();
                time = val.createdAt.toDate().toLocaleTimeString('en-US');
            } catch (err) {
            }
            html.push('<td class="dt-time">' + date + '<br> ' + time + '</td>');
        } else {
            html.push('');
        }
        if (val.active) {
            html.push('<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isActive"><span class="slider round"></span></label></td>');
        } else {
            html.push('<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isActive"><span class="slider round"></span></label></td>');
        }
        var actionHtml = '';
        var chatViewRoute = "{{ route('users.chat', ':id') }}".replace(':id', val.id);
        let unreadHtml = '';
        actionHtml = actionHtml + '<span class="action-btn">';
        actionHtml = actionHtml+'<a href="' + trroute1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.wallet_transaction') }}"><i class="mdi mdi-wallet" ></i></a>';
        actionHtml = actionHtml + '<a href="' + user_view + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view') }}"><i class="mdi mdi-eye"></i></a><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
        if (checkDeletePermission) {
            actionHtml = actionHtml+'<a id="' + val.id + '" class="delete-btn" name="user-delete" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
        }
        if(checkChatPermission){
            actionHtml = actionHtml + '<a href="' + chatViewRoute + '" class="chat-message" style="position: relative; display: inline-block;">' +
                            '<i class="mdi mdi-wechat mdi-24px"></i>' + unreadHtml +
                            '</a>';
        }
        actionHtml = actionHtml + '</span>';
        html.push(actionHtml);
        return html;
    }
    $("#is_active").click(function() {
        $("#userTable .is_open").prop('checked', $(this).prop('checked'));
    });
    $("#deleteAll").click(function() {
        if ($('#userTable .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                $('#userTable .is_open:checked').each(async function() {
                    var dataId = $(this).attr('dataId');
                    await deleteDocumentWithImage('users', dataId, 'profilePictureURL');
                        const getStoreName = deleteUserData(dataId);
                        setTimeout(function() {
                            window.location.reload();
                        }, 7000);
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
    async function deleteUserData(userId) {
        await database.collection('wallet').where('user_id', '==', userId).get().then(async function(snapshotsItem) {
            if (snapshotsItem.docs.length > 0) {
                snapshotsItem.docs.forEach((temData) => {
                    var item_data = temData.data();
                    database.collection('wallet').doc(item_data.id).delete().then(function() {
                    });
                });
            }
        });
          //delete user from mysql
          await database.collection('settings').doc("Version").get().then(function (snapshot) {
            var settingData = snapshot.data();
            if (settingData && settingData.websiteUrl){
                var siteurl = settingData.websiteUrl + "/api/delete-user"; 
                var dataObject = { "uuid": userId };         
                jQuery.ajax({
                    url: siteurl, 
                    method: 'POST',
                    contentType: "application/json; charset=utf-8",
                    data: JSON.stringify(dataObject),
                    success: function (data) {
                        console.log('Delete user from sql success:', data);
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            }
        });
        //delete user from authentication    
        var dataObject = {
            "data": {
                "uid": userId
            }
        };
        var projectId = '<?php echo env('FIREBASE_PROJECT_ID') ?>';
        jQuery.ajax({
            url: 'https://us-central1-' + projectId + '.cloudfunctions.net/deleteUser',
            method: 'POST',
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify(dataObject),
            success: function(data) {
                console.log('Delete user success:', data.result);
            },
            error: function(xhr, status, error) {
                var responseText = JSON.parse(xhr.responseText);
                console.log('Delete user from sql error:', error.responseJSON.message);
            }
        });
    }
    $(document).on("click", "a[name='user-delete']", async function(e) {
        var id = this.id;
        jQuery("#data-table_processing").show();
            await deleteDocumentWithImage('users', id, 'profilePictureURL');
            const getStoreName = deleteUserData(id);
            setTimeout(function() {
                window.location.reload();
            }, 7000);
    });
    $(document).on("click", "input[name='isActive']", function(e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        if (ischeck) {
            database.collection('users').doc(id).update({
                'active': true
            }).then(function(result) {});
        } else {
            database.collection('users').doc(id).update({
                'active': false
            }).then(function(result) {});
        }
    });
</script>
@endsection