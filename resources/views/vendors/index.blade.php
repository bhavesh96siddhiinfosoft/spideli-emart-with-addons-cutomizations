@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">
                @if (request()->is('vendors/approved'))
                    @php $type = 'approved'; @endphp
                    {{ trans('lang.approved_vendors') }}
                @elseif(request()->is('vendors/pending'))
                    @php $type = 'pending'; @endphp
                    {{ trans('lang.approval_pending_vendors') }}
                @else
                    @php $type = 'all'; @endphp
                    {{ trans('lang.all_vendors') }}
                @endif
            </h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.vendor_list')}}</li>
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
                        <h3 class="mb-0">{{trans('lang.vendor_list')}}</h3>
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
                        <h3 class="text-dark-2 mb-2 h4">{{trans('lang.vendor_list')}}</h3>
                        <p class="mb-0 text-dark-2">{{trans('lang.vendor_table_text')}}</p>
                   </div>

                    <div class="card-header-right d-flex align-items-center">
                        <div class="card-header-btn mr-3">                   
                            <a class="btn-primary btn rounded-full" href="{!! route('vendors.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.createe_vendor')}}</a>
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
                                    
                                    <?php if (($type == "approved" && in_array('approve.vendors.delete', json_decode(@session('user_permissions'), true))) 
                                    || ($type == "pending" && in_array('pending.vendors.delete', json_decode(@session('user_permissions'), true))) 
                                    || ($type == "all" && in_array('vendors.delete', json_decode(@session('user_permissions'), true)))) { ?>

                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll"
                                    class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php } ?>
                                    <th>{{trans('lang.vendor_info')}}</th>
                                    <th>{{trans('lang.store')}}</th>
                                    <th>{{trans('lang.contact_info')}}</th>
                                    <th>{{trans('lang.current_plan')}}</th>
                                    <th>{{trans('lang.expiry_date')}}</th>
                                    <th>{{trans('lang.date')}}</th>
                                    <th>{{trans('lang.active')}}</th>
                                    <?php if (($type == "approved" && in_array('approve.vendors.delete', json_decode(@session('user_permissions'), true))) 
                                    || ($type == "pending" && in_array('pending.vendors.delete', json_decode(@session('user_permissions'), true))) 
                                    || ($type == "all" && in_array('vendors.delete', json_decode(@session('user_permissions'), true)))) { ?>
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

    var section_id = getCookie('section_id') || '';
    var database = firebase.firestore();
    var type = "{{ $type }}";

    var user_permissions = '<?php echo @session('user_permissions')?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    var checkChatPermission = false;

    if (
        (type == 'pending' && $.inArray('pending.vendors.delete', user_permissions) >= 0) ||
        (type == 'approved' && $.inArray('approve.vendors.delete', user_permissions) >= 0) ||
        (type == 'all' && $.inArray('vendors.delete', user_permissions) >= 0)
    ) {
        checkDeletePermission = true;
    }
    if ($.inArray('vendors.chat', user_permissions) >= 0)
    {
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
        ref = regionScoped(database.collection('users').where("role", "==", "vendor"));
        if(section_id){
            ref = ref.where('sectionId', 'in', [section_id, '']);
        }
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
    
    var ref = regionScoped(database.collection('users').where("role", "==", "vendor"));
    if(section_id){
        ref = ref.where('sectionId', 'in', [section_id, '']);
    }
    /* Deliberately not ordered in the query: Firestore drops documents that do
     * not carry the ordered field, which hid every vendor without a createdAt.
     * The rows are fetched in full and sorted in the browser further down,
     * where a missing date is handled, so ordering here only lost data. */

    var placeholderImage = '';


$(document).ready(function () {

    $(document.body).on('click', '.redirecttopage', function () {
        var url = $(this).attr('data-url');
        window.location.href = url;
    });

    jQuery("#data-table_processing").show();
    $('body').tooltip({
        selector: '[data-toggle="tooltip"]'
    });


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
            { key: 'name', header: "{{trans('lang.vendor_info')}}" },
            { key: 'storeName', header: "{{trans('lang.store')}}" }, 
            { key: 'contactInfo', header: "{{trans('lang.contact_info')}}" }, 
            { key: 'activePlanName', header: "{{trans('lang.active_subscription_plan')}}" },    
            { key: 'exportExpiryDate', header: "{{trans('lang.plan_expire_at')}}" },  
            { key: 'active', header: "{{trans('lang.active')}}" },
            { key: 'createdAt', header: "{{trans('lang.date')}}" },
        ],
        fileName: "{{trans('lang.vendor_list')}}",
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

          const orderableColumns = (checkDeletePermission) ? ['', 'name', 'storeName', 'contactInfo', 'subscription_plan.name', 'subscriptionExpiryDate', 'createdAt', '', ''] : ['name', 'storeName', 'contactInfo', 'subscription_plan.name', 'subscriptionExpiryDate', 'createdAt', '', ''];

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

                    let sid = childData.sectionId;
                    if (
                        section_id && !(
                            sid === section_id ||
                            sid === null ||
                            sid === '' ||
                            sid === undefined
                        )
                    ) {                       
                        return;
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
                    if(childData.hasOwnProperty("subscriptionExpiryDate")) {
                        try {
                            date=childData.subscriptionExpiryDate.toDate().toDateString();
                            time=childData.subscriptionExpiryDate.toDate().toLocaleTimeString('en-US');
                        } catch(err) {
                        }
                    }

                    childData.vendorData = childData.vendorID ? await getUserStoreInfo(childData.vendorID) : ''
                    childData.expiryDate=date+' '+time;
                    childData.phone = (childData.phoneNumber != '' && childData.phoneNumber != null && childData.phoneNumber.slice(0, 1) == '+') ? childData.phoneNumber.slice(1) : childData.phoneNumber;     
                    childData.maskedPhone = EditPhoneNumber(childData.phone);                       
                    childData.hasPlusSign = childData.phoneNumber.startsWith('+');                        
                    childData.exportPhone = childData.hasPlusSign ? `+${childData.maskedPhone}` : childData.maskedPhone;
                    childData.contactInfo = shortEmail(childData.email) + '<br>' + (childData.hasPlusSign ? `+${childData.maskedPhone}` : childData.maskedPhone);
                    if (childData.subscriptionExpiryDate) {
                        childData.exportExpiryDate =
                            childData.subscriptionExpiryDate.toDate().toLocaleDateString() + ' ' +
                            childData.subscriptionExpiryDate.toDate().toLocaleTimeString('en-US');
                    } else {
                        childData.exportExpiryDate = '-';
                    }

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
                        var createdAt = date + ' ' + time;
                        if (
                            (childData.name && childData.name.toString().toLowerCase().includes(searchValue)) ||
                            (childData.vendorData && childData.vendorData.title && childData.vendorData.title.toString().toLowerCase().includes(searchValue)) ||
                            (childData.email && childData.email.toString().toLowerCase().includes(searchValue)) ||
                            (childData.expiryDate&&childData.expiryDate.toString().toLowerCase().indexOf(searchValue)>-1)||
                            (childData.hasOwnProperty('activePlanName')&&childData.activePlanName.toLowerCase().toString().includes(searchValue))||
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

                   if (orderByField === 'contactInfo') {
                        aValue = a.contactInfo ? a.contactInfo.toString().toLowerCase().trim() : '';
                        bValue = b.contactInfo ? b.contactInfo.toString().toLowerCase().trim() : '';
                    } else if (orderByField === 'subscriptionExpiryDate') {

                        aValue=a[orderByField]? new Date(a[orderByField].toDate()).getTime():0;
                        bValue=b[orderByField]? new Date(b[orderByField].toDate()).getTime():0;

                    } else if (orderByField === 'createdAt' && a[orderByField] != '' && b[orderByField] != '' && a[orderByField] != null && b[orderByField] != null) {

                        /*  aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                            bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0; */
                        function normalizeDate(v) {
                            if (!v) return 0;                             
                            if (typeof v.toDate === "function") {
                                return new Date(v.toDate()).getTime();  
                            }
                            if (v instanceof Date) {
                                return v.getTime();                    
                            }
                            if (typeof v === "string" || typeof v === "number") {
                                return new Date(v).getTime();         
                            }
                            return 0;  
                        }

                    aValue = normalizeDate(a[orderByField]);
                    bValue = normalizeDate(b[orderByField]);

                    }  else{

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
        order: (checkDeletePermission) ? [6, 'desc'] : [5, 'desc'],
        columnDefs: [
            {
                // Changed: Updated non-orderable columns
                orderable: false,
                targets: (checkDeletePermission) ? [0, 7, 8] : [6, 7],
            },
            {
                type: 'date',
                render: function(data) {
                    return data;
                },
                // Changed: Updated target index for date column
                targets: (checkDeletePermission) ? [6] : [5],
            }
        ],
        "language": datatableLang,

        dom: 'lfrtipB',
        buttons: [
            {
                extend: 'collection',
                text: '<i class="mdi mdi-cloud-download"></i> {{trans('lang.export_as')}}',
                className: 'btn btn-info',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '{{ trans('lang.export_excel') }}',
                        action: function (e, dt, button, config) {
                            exportData(dt, 'excel',fieldConfig);
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '{{ trans('lang.export_pdf') }}',
                        action: function (e, dt, button, config) {
                            exportData(dt, 'pdf',fieldConfig);
                        }
                    },   
                    {
                        extend: 'csvHtml5',
                        text: '{{ trans('lang.export_csv') }}',
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
            //document verification status icon add new code 
async function getDocumentStatusIcon(driverId) {
    const docSnap = await database.collection('documents_verify').doc(driverId).get();

    if (!docSnap.exists) return '';                     // no verification record → no icon

    const docs = docSnap.data().documents || [];

    // Count approved / rejected
    const approved = docs.filter(d => d.status === 'approved').length;
    const rejected = docs.filter(d => d.status === 'rejected').length;
    const total   = docs.length;

    // Both approved?
    if (approved === total && total > 0) {
        return '<i class="mdi mdi-verified verified-icon" data-toggle="tooltip" data-bs-original-title="{{trans('lang.verified')}}"></i>';
    }

    // Any rejected?
    if (rejected > 0) {
        return '<i class="mdi mdi-close-circle unverified-icon" data-toggle="tooltip" data-bs-original-title="{{trans('lang.rejected')}}" style="color:red;"></i>';
    }

    // Both uploaded (or pending) → no icon
    return '';
}
async function buildHTML(val) {
    var html = [];

    var id = val.id;

    var route1 = '';
    var route1 =  '{{route("vendors.edit", ":id")}}';
    route1 = route1.replace(':id', val.id);    
    
    var vendorEdit = '{{route("vendors.edit", ":id")}}';
    vendorEdit = vendorEdit.replace(':id', val.id);

    var vendorView = '';
    if(val.vendorData){
        vendorView = '{{ route('stores.view', ':id') }}';
        vendorView = vendorView.replace(':id', val.vendorData.id);
    }
    
    var trroute1 = '{{route("users.walletstransaction", ":id")}}';
    trroute1 = trroute1.replace(':id', id);
    if(checkDeletePermission){
    html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '" data-vendorid="'+val.vendorID+'"><label class="col-3 control-label"\n' +
        'for="is_open_' + id + '" ></label></td>');
    }
    
    var verified = await getDocumentStatusIcon(val.id);

    if(val.isAutoVerify === true){
        verified += ' <i class="mdi mdi-check-circle verified-icon" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.auto_approved') }}"></i>';
    }

    if (val.profilePictureURL == '') {

        html.push('<img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">  <a id="userName_' + id + '"  href="'+vendorEdit+'" class="redirecttopage left_space">' + val.firstName + ' ' + val.lastName + '</a>' + verified);
    } else {
        if(val.profilePictureURL){
            photo=val.profilePictureURL;
        }else{
            photo=placeholderImage;
        }
        html.push('<img class="rounded" style="width:50px" src="' + photo + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">  <a id="userName_' + id + '"  href="'+vendorEdit+'" class="redirecttopage left_space">' + val.firstName + ' ' + val.lastName + '</a>' + verified);
    }

    if(val.vendorData){
        html.push('<a href="'+vendorView+'" class="redirecttopage left_space">' + val.vendorData.title + '</a>');
    }else{
        html.push('');
    }
    
    html.push(val.contactInfo);

    if(val.hasOwnProperty('subscription_plan') && val.subscription_plan && val.subscription_plan.name) {
        html.push(val.subscription_plan.name);
    } else {
        html.push('');
    }

   
    if(val.hasOwnProperty('subscription_plan') && val.subscription_plan) {
        if(val.expiryDate != ' '){
            html.push(val.expiryDate);
        }else{
            html.push('{{trans("lang.unlimited")}}');
        }
    } else {
        html.push('');
    }
    
    var date = '';
    var time = '';
    if (val.hasOwnProperty("createdAt")) {
        try {
            date = val.createdAt.toDate().toDateString();
            time = val.createdAt.toDate().toLocaleTimeString('en-US');
        } catch (err) {

        }
        html.push('<td class="dt-time"><span class="wrap-word">' + date + '<br> ' + time + '</span></td>');
    } else {
        html.push('');
    }

    if (val.active) {
        html.push('<label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isActive"><span class="slider round"></span></label>');
    } else {
        html.push('<label class="switch"><input type="checkbox" id="' + val.id + '" name="isActive"><span class="slider round"></span></label>');
    }
    var chatViewRoute = "{{ route('vendors.chat', ':id') }}".replace(':id', val.id);
    var unreadHtml = '';
    var action='<span class="action-btn">';

    if(val.isAutoVerify !== true){
        var document_list_view = "{{ route('vendors.document', ':id') }}";
        document_list_view = document_list_view.replace(':id', val.id);
        action+='<a href="' + document_list_view + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.document') }}"><i class="fa fa-file"></i></a>';
    }

    var planRoute="{{route('subscription.subscriptionPlanHistory',':id')}}";
    planRoute=planRoute.replace(':id',val.id);
    if(val.hasOwnProperty('subscription_plan')) {
        action+='<a id="'+val.id+'"  href="'+planRoute+'" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.subscription_plans') }}"><i class="mdi mdi-crown"></i></a>';
    }
    action+='<a id="'+val.id+'"  href="'+route1+'" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
    if(checkDeletePermission) {
        action=action+'<a id="'+val.id+'" data-vendorid="'+val.vendorID+'" class="delete-btn" name="user-delete" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
    }
    if(checkChatPermission){
    action = action + '<a href="' + chatViewRoute + '" class="chat-message" style="position: relative; display: inline-block;">' +
                '<i class="mdi mdi-wechat mdi-24px"></i>' + unreadHtml +
                '</a>';
    }
    action=action+'</span>';
    html.push(action);
    return html;
}


async function getUserStoreInfo(vendorId) {
    let vendorRef = await database.collection('vendors').doc(vendorId).get();
    let vendorData = vendorRef.data();
    return vendorData;
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

    if(vendorId != '' && vendorId != null){
       await deleteDocumentWithImage('vendors',vendorId,'photo',['vendorMenuPhotos','photos']);
        await database.collection('vendor_products').where('vendorID','==',vendorId).get().then(async function (snapshotsItem) {
             if (snapshotsItem.docs.length > 0) {
                for (const listval of snapshotsItem.docs) {
                    await deleteDocumentWithImage('vendor_products', listval.id, 'photo', 'photos');
                }
             }
        })
        await database.collection('story').where('vendorID', '==', vendorId).get().then(async function (snapshotsItem) {
                if (snapshotsItem.docs.length > 0) {
                    for (const temData of snapshotsItem.docs) {
                        await deleteDocumentWithImage('story', temData.id,'videoThumbnail','videoUrl');
                    }
                }
            });
        await database.collection('favorite_vendor').where('store_id','==',vendorId).get().then(async function (snapshotsItem) {
             if (snapshotsItem.docs.length > 0) {
            snapshotsItem.docs.forEach((temData) => {
                var item_data = temData.data();

                database.collection('favorite_vendor').doc(item_data.id).delete().then(function () {

                });
            });
        }
        })
    }

      //delete vendor from mysql
      database.collection('settings').doc("Version").get().then(function(snapshot) {
            var settingData=snapshot.data();
            if(settingData&&settingData.storeUrl) {
                var siteurl=settingData.storeUrl+"/api/delete-user";
                var dataObject={"uuid": userId};
                jQuery.ajax({
                    url: siteurl,
                    method: 'POST',
                    contentType: "application/json; charset=utf-8",
                    data: JSON.stringify(dataObject),
                    success: function(data) {
                        console.log('Delete user from sql success:',data);
                    },
                    error: function(error) {
                        console.log('Delete user from sql error:',error.responseJSON.message);
                    }
                });
            }
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
        database.collection('users').doc(id).update({'active': true}).then(function (result) {
        });
    } else {
        database.collection('users').doc(id).update({'active': false}).then(function (result) {
        });
    }

});

</script>

@endsection
