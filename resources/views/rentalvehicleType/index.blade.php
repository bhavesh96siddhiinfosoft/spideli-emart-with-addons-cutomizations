@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.rental_vehicle_type')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.rental_vehicle_type')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/vehicle_type.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.rental_vehicle_type')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.rental_vehicle_type')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.rental_vehicle_type_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('rentalvehicleType.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.add_rental_vehicle_type')}}</a>
                     </div>
                   </div>            
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                         <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                    <th>{{trans('lang.vehicle_info')}}</th>
                                    <th>{{trans('lang.status')}}</th>
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
    var section_id = getCookie('section_id') || null;
    var database = firebase.firestore();
    var offest = 1;
    var pagesize = 10;
    var end = null;
    var endarray = [];
    var start = null;
    var user_number = [];
    var ref = database.collection('rental_vehicle_type');
    if(section_id){
        ref = ref.where('sectionId', '==', section_id);
    }
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })
    var append_list = '';
    $(document).ready(function () {
        var inx = parseInt(offest) * parseInt(pagesize);
        jQuery("#data-table_processing").show();
        append_list = document.getElementById('append_list1');
        append_list.innerHTML = '';
        ref.get().then(async function (snapshots) {
            html = '';
            html = await buildHTML(snapshots);
            $(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });
            jQuery("#data-table_processing").hide();
            if (html != '') {
                append_list.innerHTML = html;
                start = snapshots.docs[snapshots.docs.length - 1];
                endarray.push(snapshots.docs[0]);
                if (snapshots.docs.length < pagesize) {
                    jQuery("#data-table_paginate").hide();
                }
            }
            var table = $('#example24').DataTable({
                order: [],
                columnDefs: [
                    {orderable: false, targets: [ 1, 2]},
                ],
                order: [0, "asc"],
                "language": datatableLang,

                responsive: true,
            });
            table.on('search.dt', function() {
                var filteredCount = table.rows({ search: 'applied' }).count();
                $('.total_count').text(filteredCount);  // Update count
            });
        });
    });
    async function buildHTML(snapshots) {
        var html = '';
        if (snapshots.docs.length > 0) {
            $('.total_count').text(snapshots.docs.length); 
        }
        else
        {
            $('.total_count').text(0); 
        }
        await Promise.all(snapshots.docs.map(async (listval) => {
            var val = listval.data();
            var getData = await getListData(val);
            html += getData;
        }));
        return html;
    }
    async function getListData(val) {
        var html = '';
        var count = 0;
        html = html + '<tr>';
        newdate = '';
        var id = val.id;
        var route1 = '{{route("rentalvehicleType.edit",":id")}}';
        route1 = route1.replace(':id', id);
        var photo = val.rental_vehicle_icon;
        if (photo != '') {
            html = html + '<td><img class="rounded" style="width:50px" src="' + photo + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"><a href="' + route1 + '" class="left_space redirecttopage">' + val.name + '</a></td>';
        } else {
            html = html + '<td><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image"><a href="' + route1 + '" class="left_space redirecttopage">' + val.name + '</a></td>';
        }
        if (val.isActive) {
            html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';
        } else {
            html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';
        }
        html = html + '<td><span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
        <?php if(in_array('rental-vehicle-type.delete', json_decode(@session('user_permissions'),true))){?>
        html = html + '<a id="' + val.id + '" name="vehicleType-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a></span>';
        <?php }?>
        html = html + '</td>';
        html = html + '</tr>';
        count = count + 1;
        return html;
    }
    $(document).on("click", "input[name='isSwitch']", function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        if (ischeck) {
            database.collection('rental_vehicle_type').doc(id).update({'isActive': true}).then(function (result) {
            });
        } else {
            database.collection('rental_vehicle_type').doc(id).update({'isActive': false}).then(function (result) {
            });
        }
    });
    $(document.body).on('click', '.redirecttopage', function () {
        var url = $(this).attr('data-url');
        window.location.href = url;
    });
    $(document).on("click", "a[name='vehicleType-delete']", async function (e) {
        var id = this.id;
        await deleteDocumentWithImage('rental_vehicle_type',id,'rental_vehicle_icon');
        window.location.reload();
    });
</script>
@endsection
