@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.parcelcategory_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.parcelcategory_plural')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/parcel.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.parcelcategory_plural')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.parcelcategory_plural')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.parcelcategory_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('parcelCategory.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.parcel_create')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                <?php if (in_array('parcel.categories.delete', json_decode(@session('user_permissions'),true))) { ?>
                                    <th class="delete-all">
                                        <input type="checkbox" id="is_active">
                                        <label class="col-3 control-label" for="is_active">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i
                                                        class="fa fa-trash"></i> {{trans('lang.all')}}</a>
                                        </label>
                                    </th>
                                    <?php }?>
                                    <th>{{trans('lang.category_info')}}</th>
                                    <th>{{trans('lang.item_publish')}}</th>
                                    <th>{{trans('lang.set_order')}}</th>
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
        var user_permissions = '<?php echo @session('user_permissions') ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkDeletePermission = false;
        if ($.inArray('parcel.categories.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }
        var database = firebase.firestore();
        var offest = 1;
        var pagesize = 10;
        var end = null;
        var endarray = [];
        var start = null;
        var user_number = [];
        var ref = database.collection('parcel_categories');
        
        if(section_id){
            ref = ref.where('sectionId', '==', section_id);
        }
        
        var alldriver = database.collection('users').where("role", "==", "driver");
        var placeholderImage = '';
        var append_list = '';
        $(document).ready(function () {
            var inx = parseInt(offest) * parseInt(pagesize);
            jQuery("#data-table_processing").show();
            var placeholder = database.collection('settings').doc('placeHolderImage');
            placeholder.get().then(async function (snapshotsimage) {
                var placeholderImageData = snapshotsimage.data();
                placeholderImage = placeholderImageData.image;
            })
            append_list = document.getElementById('append_list1');
            append_list.innerHTML = '';
            ref.get().then(async function (snapshots) {
                html = '';
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
                    start = snapshots.docs[snapshots.docs.length - 1];
                    endarray.push(snapshots.docs[0]);
                    if (snapshots.docs.length < pagesize) {
                        jQuery("#data-table_paginate").hide();
                    }
                }
                var table = $('#example24').DataTable({
                    order: [],
                    columnDefs: [{
                        targets: (checkDeletePermission==true) ? 4 :3,
                        type: 'date',
                        render: function (data) {
                            return data;
                        }
                    },
                        {orderable: false, targets: (checkDeletePermission==true) ? [0, 2, 4] : [0,1,3]},
                    ],
                    order: (checkDeletePermission==true) ? [1, "asc"] : [0,"asc"],
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
            await Promise.all(snapshots.docs.map(async (listval) => {
                var val = listval.data();
                var getData = await getListData(val);
                html += getData;
            }));
            return html;
        }
        async function getListData(val) {
            var html = '';
            var alldata = [];
            var number = [];
            var count = 0;
            html = html + '<tr>';
            newdate = '';
            var id = val.id;
            var route1 = '{{route("parcelCategory.edit",":id")}}';
            route1 = route1.replace(':id', id);
            if(checkDeletePermission){
            html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label></td>';
            }
            if (val.image != '') {
                html = html + '<td><img class="rounded" style="width:50px" src="' + val.image + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"><span class="left_space"><a href="'+route1+'" class="redirecttopage" >'+ val.title +'</a></span></td>';
            } else {
                html = html + '<td><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image"><span class="left_space"><a href="'+route1+'" class="redirecttopage" >'+ val.title +'</a></span></td>';
            }
            if (val.publish) {
                html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';
            } else {
                html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';
            }
            var set_order = 0;
            if (val.hasOwnProperty('set_order') && val.set_order && val.set_order != ''){
                set_order = val.set_order;
            }
            html = html + '<td>' + set_order + ' </td>';
            html = html + '<td><span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
            if(checkDeletePermission){
             html=html+'<a id="' + val.id + '" name="parcel-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a></span>';
            }
            html=html+'</td>';
            html = html + '</tr>';
            count = count + 1;
            return html;
        }
        $(document).on("click", "input[name='isSwitch']", function (e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            if (ischeck) {
                database.collection('parcel_categories').doc(id).update({'publish': true}).then(function (result) {
                });
            } else {
                database.collection('parcel_categories').doc(id).update({'publish': false}).then(function (result) {
                });
            }
        });
        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });
        $(document).on("click", "a[name='parcel-delete']", async function (e) {
            var id = this.id;
            await deleteDocumentWithImage('parcel_categories',id,'image');
            window.location.reload();
        });
        $("#is_active").click(function () {
            $("#example24 .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function () {
            if ($('#example24 .is_open:checked').length) {
                if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                    jQuery("#data-table_processing").show();
                    $('#example24 .is_open:checked').each(async function () {
                        var dataId = $(this).attr('dataId');
                        await deleteDocumentWithImage('parcel_categories',dataId,'image');
                        window.location.reload();
                    });
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });
    </script>
@endsection
