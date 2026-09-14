@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.sos')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.sos')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/SOS.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.sos')}}</h3>
                        <span class="counter ml-3 total_count"></span>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.sos')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.sos_table_text')}}</p>
                   </div>             
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                         <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                      <th>{{trans('lang.order_id')}}</th>
                                        <th>{{trans('lang.sos_id')}}</th>
                                        <th>{{trans('lang.order_user_id')}}</th>
                                        <th class="driverClass">{{trans('lang.driver_plural')}}</th>
                                        <th>{{trans('lang.address')}}</th>
                                        <th>{{trans('lang.status')}}</th>
                                        <th>{{trans('lang.actions')}}</th>
                                    </tr>
                                </thead>
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
    var section_id = getCookie('section_id');
    var offest = 1;
    var pagesize = 10;
    var end = null;
    var endarray = [];
    var start = null;
    var user_number = [];
    var ref = database.collection('SOS');
    var placeholderImage = '';
    var rideRef = database.collection('rides');

    $(document).ready(function () {

        var inx = parseInt(offest) * parseInt(pagesize);
        jQuery("#data-table_processing").show();

        jQuery("#data-table_processing").show();
        const table = $('#example24').DataTable({
            pageLength: 10,
            processing: false,
            serverSide: true,
            responsive: true,
            ajax: async function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;

                const orderableColumns = ['id','userName','driverName','address','status',''];

                const orderByField = orderableColumns[orderColumnIndex];

                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }

                try {
                    const querySnapshot = await ref.get();
                    if (querySnapshot.empty) {
                        $('.total_count').text(0); 
                        $('#data-table_processing').hide();
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                        return;
                    }

                    let records = [];
                    let filteredRecords = [];
                    let userNames = {};
                    let driverNames = {};
                    let destinationLocationNames = {};
                    const rideDocs = await rideRef.get();
                    rideDocs.forEach(doc => {
                        userNames[doc.id] = doc.data().hasOwnProperty('author')   ? doc.data().author.firstName + ' ' + doc.data().author.lastName : 'N/A';
                        driverNames[doc.id] = doc.data().hasOwnProperty('driver') ? doc.data().driver.firstName + ' ' + doc.data().driver.lastName : 'N/A';
                        destinationLocationNames[doc.id] = doc.data().destinationLocationName;
                    });

                    await Promise.all(querySnapshot.docs.map(async (doc) => {
                        let childData = doc.data();
                      
                        if (childData.orderId) {
                            childData.id = doc.id;
                            var rideData = await rideDetails(childData.orderId);
                            if (!rideData) {
                                return; // Skip to the next iteration
                            }
                            if (rideData.sectionId != section_id) return;
                            
                            childData.userid = rideData.author && rideData.author.id ? rideData.author.id : '';
                            childData.driverid = rideData.driver && rideData.driver.id ? rideData.driver.id : '';

                            var userName = rideData.author && rideData.author.firstName ? rideData.author.firstName : '';
                            var driverName = rideData.driver && rideData.driver.firstName ? rideData.driver.firstName : '';
                            var address = rideData.destinationLocationName ? rideData.destinationLocationName : '';

                            childData.userName = userName;
                            childData.driverName = driverName;
                            childData.address = address;

                            if (searchValue) {
                                if (
                                    (childData.id && childData.id.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.status && childData.status.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.userName && childData.userName.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.driverName && childData.driverName.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.address && childData.address.toString().toLowerCase().includes(searchValue))
                                ) {
                                    filteredRecords.push(childData);
                                }
                            } else {
                                filteredRecords.push(childData);
                            }
                        }

                    }));

                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase().trim() : '';
                        let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase().trim() : '';
                        if (orderDirection === 'asc') {
                            return (aValue > bValue) ? 1 : -1;
                        } else {
                            return (aValue < bValue) ? 1 : -1;
                        }
                    });

                    const totalRecords = filteredRecords.length;
                    $('.total_count').text(totalRecords); 
                    const paginatedRecords = filteredRecords.slice(start, start + length);

                    const formattedRecords = await Promise.all(paginatedRecords.map(async (childData) => {
                        return await buildHTML(childData);
                    }));
                    $(function () {
                        $('[data-toggle="tooltip"]').tooltip();
                    });
                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords,
                        recordsFiltered: totalRecords,
                        data: formattedRecords
                    });

                } catch (error) {
                    console.error("Error fetching data from Firestore:", error);
                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: []
                    });
                }
            },
            order: [1, 'desc'],
            columnDefs: [
                {orderable: false, targets: [6]},
            ],
            "language": datatableLang,

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
        if (val.address){

        var id = val.id;
        var route1 = '{{route("sos.edit",":id")}}';
        route1 = route1.replace(':id', id);

        var trroute1 = '{{route("rides.edit",":id")}}';
        trroute1 = trroute1.replace(':id', val.orderId);

        }
        html.push('<a href="' + trroute1 + '" data-toggle="tooltip" data-bs-original-title="' + val.orderId + '">' + (val.id.length > 8 ? val.orderId.substring(0, 8) + '...' : val.orderId) + '</a>');

        html.push('<a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="' + val.id + '">' + (val.id.length > 8 ? val.id.substring(0, 8) + '...' : val.id) + '</a>');
        
        if(val.userid!=''){
            var route2 = '{{route("users.view",":id")}}';
            route2 = route2.replace(':id', val.userid);
            html.push('<a href="' + route2 + '">'+val.userName+ '</a>');
        }else{
            html.push('');
        }

        if(val.driverid!=''){
            var route3 = '{{route("drivers.view",":id")}}';
            route3 = route3.replace(':id', val.driverid);
            html.push('<a href="' + route3 + '">'+val.driverName+ '</a>');
        }else{
            html.push('');
        }
        
     
        html.push(val.address);

        if (val.status == "Completed") {
            html.push('<span class="badge badge-success">' + val.status + '</span>');
        } else if (val.status == "Processing") {
            html.push('<span class="badge badge-info">' + val.status + '</span>');
        } else {
            html.push('<span class="badge badge-primary">' + val.status + '</span>');
        }

        var action = '';
        action = action + '<span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
        <?php if(in_array('sos.rides.delete', json_decode(@session('user_permissions'),true))){?>
        action = action + '<a id="' + val.id + '" name="carModel-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
        <?php }?>
        action = action + '</span>';
        html.push(action);
        
        return html;
    }

     async function rideDetails(ride) {

        var rideDetails = await database.collection('rides').doc(ride).get();
        if (rideDetails.data()) {
            return rideDetails.data();
        } else {
            return '';
        }
    }

    $(document.body).on('click', '.redirecttopage', function () {
        var url = $(this).attr('data-url');
        window.location.href = url;
    });

    $(document).on("click", "a[name='carModel-delete']", function (e) {
        var id = this.id;
        database.collection('SOS').doc(id).delete().then(function () {
            window.location.reload();
        });
    });

   

</script>

@endsection