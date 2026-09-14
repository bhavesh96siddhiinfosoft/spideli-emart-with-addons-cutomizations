@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">
                @if (request()->is('owners/approved'))
                    @php $type = 'approved'; @endphp
                    {{ trans('lang.approved_owners') }}
                @elseif(request()->is('owners/pending'))
                    @php $type = 'pending'; @endphp
                    {{ trans('lang.approval_pending_owners') }}
                @else
                    @php $type = 'all'; @endphp
                    {{ trans('lang.all_owners') }}
                @endif
            </h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.owners_list')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/vendor.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.owners_list')}}</h3>
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
                        <h3 class="text-dark-2 mb-2 h4">{{trans('lang.owners_list')}}</h3>
                        <p class="mb-0 text-dark-2">{{trans('lang.owners_table_text')}}</p>
                   </div>

                  <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">
                        <a class="btn-primary btn rounded-full" href="{!! route('owners.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.create_owner') }}</a>
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
                                    
                                    <?php if (($type == "approved" && in_array('approve.owner.delete', json_decode(@session('user_permissions'), true))) 
                                    || ($type == "pending" && in_array('pending.owner.delete', json_decode(@session('user_permissions'), true))) 
                                    || ($type == "all" && in_array('owner.delete', json_decode(@session('user_permissions'), true)))) { ?>

                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll"
                                    class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php } ?>
                                    <th>{{trans('lang.owner_info')}}</th>
                                   <th>{{trans('lang.contact_info')}}</th>
                                    <th>{{trans('lang.date')}}</th>
                                    <th>{{trans('lang.active')}}</th>
                                    <?php if (($type == "approved" && in_array('approve.owner.delete', json_decode(@session('user_permissions'), true))) 
                                    || ($type == "pending" && in_array('pending.owner.delete', json_decode(@session('user_permissions'), true))) 
                                    || ($type == "all" && in_array('owner.delete', json_decode(@session('user_permissions'), true)))) { ?>
                                        <th>{{trans('lang.actions')}}</th>
                                    <?php }?>
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
    var type = "{{ $type }}";

    var user_permissions = '<?php echo @session('user_permissions')?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    
    if (
        (type == 'pending' && $.inArray('pending.owner.delete', user_permissions) >= 0) ||
        (type == 'approved' && $.inArray('approve.owner.delete', user_permissions) >= 0) ||
        (type == 'all' && $.inArray('owner.delete', user_permissions) >= 0)
    ) {
        checkDeletePermission = true;
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
    
    var ref = database.collection('users').where("role", "==", "driver").where('isOwner','==',true).orderBy('createdAt', 'desc');
    
    $('.filteredRecords').change(async function() {
        var status = $('.status_selector').val();
        var daterangepicker = $('#daterange').data('daterangepicker');
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

    var placeholderImage = '';

$(document).ready(function () {

    $(document.body).on('click', '.redirecttopage', function () {
        var url = $(this).attr('data-url');
        window.location.href = url;
    });

    jQuery("#data-table_processing").show();

    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function (snapshotsimage) {
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
            { key: 'name', header: "{{trans('lang.owner_info')}}" },
            { 
                    key: 'contact_info', 
                    header: "{{trans('lang.contact_info')}}",
                    cell: row => `${row.email}<br>${row.hasPlusSign ? `+${row.maskedPhone}` : row.maskedPhone}`
                },
            { key: 'active', header: "{{trans('lang.active')}}" },
            { key: 'createdAt', header: "{{trans('lang.date')}}" },
        ],
        
        fileName: "{{trans('lang.owners_list')}}",
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

            const orderableColumns = (checkDeletePermission) ? ['', 'name', 'contact_info', 'createdAt', 'active', ''] : ['name', 'contact_info', 'createdAt', 'active', ''];

            const orderByField = orderableColumns[orderColumnIndex]; 

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

                    const isDocVerified = childData.isDocumentVerify === true;
                    const isAutoVerified = childData.isAutoVerify === true;
                    if (type === 'pending') {
                        if (isDocVerified || isAutoVerified) {
                            return;
                        }
                    }
                    if (type === 'approved') {
                            if (!isDocVerified && !isAutoVerified) {
                            return;
                        }
                    }

                    childData.id = doc.id; // Ensure the document ID is included in the data
                    childData.name = childData.firstName + ' ' + childData.lastName;
                    if(childData.hasOwnProperty('subscription_plan') && childData.subscription_plan && childData.subscription_plan.name) {
                        childData.activePlanName = childData.subscription_plan.name;
                    }else {
                        childData.activePlanName = '';
                    }
                    var date='';
                    var time='';
                   
                    childData.phone = (childData.phoneNumber != '' && childData.phoneNumber != null && childData.phoneNumber.slice(0, 1) == '+') ? childData.phoneNumber.slice(1) : childData.phoneNumber;     
                    childData.maskedPhone = EditPhoneNumber(childData.phone);                       
                    childData.hasPlusSign = childData.phoneNumber.startsWith('+');                        
                    childData.exportPhone = childData.hasPlusSign ? `+${childData.maskedPhone}` : childData.maskedPhone;
                    childData.contact_info = `${childData.email} ${childData.phoneNumber}`;
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
                            (childData.email && childData.email.toString().toLowerCase().includes(searchValue)) ||
                            (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1) || (childData.phoneNumber && childData.phoneNumber.toString().toLowerCase().includes(searchValue))
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

                    if (orderByField === 'createdAt' && a[orderByField] != '' && b[orderByField] != '' && a[orderByField] != null && b[orderByField] != null) {

                        aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                        bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                    }
                    else{
                        aValue = a[orderByField] ? a[orderByField].toString().toLowerCase().trim() : '';
                        bValue = b[orderByField] ? b[orderByField].toString().toLowerCase().trim() : ''
                    }


                    if(orderByField==='subscriptionExpiryDate') {
                        aValue=a[orderByField]? new Date(a[orderByField].toDate()).getTime():0;
                        bValue=b[orderByField]? new Date(b[orderByField].toDate()).getTime():0;
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
                    // Updated targets to exclude removed columns
                    targets: (checkDeletePermission) ? [0, 4, 5] : [3, 4],
                },
                {
                    type: 'date',
                    render: function(data) {
                        return data;
                    },
                    // Updated target to new createdAt column index
                    targets: (checkDeletePermission) ? [2] : [2],
                }
            ],
       "language": datatableLang,
        dom: 'lfrtipB',
        buttons: [
            {
                extend: 'collection',
                text: '<i class="mdi mdi-cloud-download"></i>{{ trans('lang.export_as') }}',
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
            $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete','new-password').val('');
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

    var id = val.id;

    var route1 = '';
    var route1 =  '{{route("owners.edit", ":id")}}';
    route1 = route1.replace(':id', val.id);
    var ownerView = '{{ route("owners.view", ':id') }}';
    ownerView = ownerView.replace(':id', id);
    
    var checkIsStore = getUserStoreInfo(id);

    var trroute1 = '{{route("walletTransaction.owner", ":id")}}';
    trroute1 = trroute1.replace(':id', id);
   
    // Add checkbox for delete if permission exists
    if(checkDeletePermission){
        html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '" data-vendorid="'+val.vendorID+'"><label class="col-3 control-label"\n' +'for="is_open_' + id + '" ></label></td>');
    }

    var verified = await getDocumentStatusIcon(val.id);
    if(val.isAutoVerify === true){
        verified += ' <i class="mdi mdi-check-circle verified-icon" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.auto_approved') }}"></i>';
    }

    if (val.profilePictureURL == '') {

        html.push('<img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">  <a id="userName_' + id + '"  href="'+ownerView+'" class="redirecttopage left_space">' + val.firstName + ' ' + val.lastName + '</a>' + verified);
    } else {
        if(val.profilePictureURL){
            photo=val.profilePictureURL;
        }else{
            photo=placeholderImage;
        }
        html.push('<img class="rounded" style="width:50px" src="' + photo + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">  <a id="userName_' + id + '"  href="'+ownerView+'" class="redirecttopage left_space">' + val.firstName + ' ' + val.lastName + '</a>' + verified);
    }

    // Contact Info column (email and phone combined)
    var contactInfo = shortEmail(val.email) + '<br>' + (val.phoneNumber.includes('+') ? '+' + EditPhoneNumber(val.phoneNumber.slice(1)) : EditPhoneNumber(val.phoneNumber));
    html.push('<td>' + contactInfo + '</td>');

    // Date column
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

     var document_list_view = '{{ route("owners.document", ':id') }}';
    document_list_view = document_list_view.replace(':id', id);
    
    if (val.active) {
        html.push('<label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isActive"><span class="slider round"></span></label>');
    } else {
        html.push('<label class="switch"><input type="checkbox" id="' + val.id + '" name="isActive"><span class="slider round"></span></label>');
    }
  
    var action='<span class="action-btn">';
    if(val.isAutoVerify !== true){
        action += '<a href="' + document_list_view + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.document_plural') }}"><i class="fa fa-file"></i></a>';
    }
    
    action = action + '<a href="' + ownerView + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view') }}"><i class="mdi mdi-eye"></i></a>';

    var planRoute="{{route('subscription.subscriptionPlanHistory',':id')}}";
    planRoute=planRoute.replace(':id',val.vendorID);
    if(val.hasOwnProperty('subscription_plan')) {
        action+='<a id="'+val.id+'"  href="'+planRoute+'" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view') }}"><i class="mdi mdi-crown"></i></a>';
    }
    action+='<a id="'+val.id+'"  href="'+route1+'" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
    if(checkDeletePermission) {
        action=action+'<a id="'+val.id+'" data-vendorid="'+val.vendorID+'" class="delete-btn" name="user-delete" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
    }
    action=action+'</span>';
    html.push(action);
    return html;
}

async function getDocumentStatusIcon(driverId) {
    const docSnap = await database.collection('documents_verify').doc(driverId).get();
    if (!docSnap.exists) return '';

    const docs = docSnap.data().documents || [];

    const approved = docs.filter(d => d.status === 'approved').length;
    const rejected = docs.filter(d => d.status === 'rejected').length;
    const total   = docs.length;

    if (approved === total && total > 0) {
        return '<i class="mdi mdi-verified verified-icon" data-toggle="tooltip" data-bs-original-title="Verified"></i>';
    }
    if (rejected > 0) {
        return '<i class="mdi mdi-close-circle unverified-icon" data-toggle="tooltip" data-bs-original-title="Rejected" style="color:red;"></i>';
    }
    return '';
}

async function getUserStoreInfo(userId) {

    await database.collection('vendors').where('author', '==', userId).get().then(async function (restaurantSnapshots) {

        if (restaurantSnapshots.docs.length > 0) {

            var restaurantId = restaurantSnapshots.docs[0].data();
            restaurantId = restaurantId.id;

            var restaurantView = '{{route("owners.edit", ":id")}}';
            restaurantView = restaurantView.replace(':id', restaurantId);

            $('#userName_' + userId).attr('data-url', restaurantView);
        }
    });
}

$("#is_active").click(function () {
    $("#userTable .is_open").prop('checked', $(this).prop('checked'));

});

$("#deleteAll").click(function () {
    if ($('#userTable .is_open:checked').length) {
        if (confirm("{{trans('lang.selected_delete_alert')}}")) {
            jQuery("#data-table_processing").show();
            $('#userTable .is_open:checked').each(function () {
                var dataId = $(this).attr('dataId');
                var VendorId = $(this).attr('data-vendorid');
                deleteDocumentWithImage('users', dataId, 'profilePictureURL')
                .then(() => {
                    return deleteUserData(dataId, VendorId);
                })
                .then(result => {
                    setTimeout(function () {
                        window.location.reload();
                    }, 7000);
                })
                .catch(error => {
                    console.error("Error occurred:", error);
                });
            });

        }
    } else {
        alert("{{trans('lang.select_delete_alert')}}");
    }
});

async function deleteUserData(userId,vendorId) {

    await database.collection('wallet').where('user_id', '==', userId).get().then(async function (snapshotsItem) {

        if (snapshotsItem.docs.length > 0) {
            snapshotsItem.docs.forEach((temData) => {
                var item_data = temData.data();

                database.collection('wallet').doc(item_data.id).delete().then(function () {

                });
            });
        }
    });

    const driversSnapshot = await database.collection('users').where('ownerId', '==', userId).get();
    driversSnapshot.forEach((doc) => {
        deleteDocumentWithImage('users', doc.id, 'profilePictureURL');
    });


    //delete user from authentication
    var dataObject = {"data": {"uid": userId}};
    var projectId = '<?php echo env('FIREBASE_PROJECT_ID') ?>';
    jQuery.ajax({
        url: 'https://us-central1-' + projectId + '.cloudfunctions.net/deleteUser',
        method: 'POST',
        contentType: "application/json; charset=utf-8",
        data: JSON.stringify(dataObject),
        success: function (data) {
            console.log('Delete user success:', data.result);
        },
        error: function (xhr, status, error) {
            var responseText = JSON.parse(xhr.responseText);
            console.log('Delete user error:', responseText.error);
        }
    });
}


$(document).on("click", "a[name='user-delete']", function (e) {
    var id = this.id;
    var VendorId = $(this).attr('data-vendorid');
    jQuery("#data-table_processing").show();
    deleteDocumentWithImage('users', id, 'profilePictureURL')
    .then(() => {
        return deleteUserData(id, VendorId);
    })
    .then(result => {
        setTimeout(function () {
            window.location.reload();
        }, 7000);
    })
    .catch(error => {
        console.error("Error occurred:", error);
    });
});

$(document).on("click", "input[name='isActive']", function (e) {
    var ischeck = $(this).is(':checked');
    var id = this.id;
    if (ischeck) {
        database.collection('users').doc(id).update({'active': true});
    } else {
        database.collection('users').doc(id).update({'active': false});
    }
});

</script>

@endsection
