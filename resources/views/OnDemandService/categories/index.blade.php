@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.ondemand_plural')}} - {{trans('lang.category_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.ondemand_plural')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/category.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.ondemand_plural')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.ondemand_plural')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.ondemand_category_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('ondemandcategory.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.category_create')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="categoryTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                <?php if (in_array('ondemand.categories.delete', json_decode(@session('user_permissions'),true))) { ?>
                                <th class="delete-all">
                                    <input type="checkbox" id="is_active">
                                    <label class="col-3 control-label" for="is_active">
                                        <a id="deleteAll" href="javascript:void(0)"><i class="fa fa-trash"></i> {{trans('lang.all')}}</a>
                                    </label>
                                </th>
                                <?php }?>
                                <th>{{trans('lang.category_info')}}</th>
                                <th>{{trans('lang.item_publish')}}</th>
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

        var section_id = getCookie('section_id') || '';
        var user_permissions = '<?php echo @session('user_permissions') ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkDeletePermission = false;
        if ($.inArray('ondemand.categories.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }
        var database = firebase.firestore();
        var pagesize = 10;
        var user_number = [];
        var ref = database.collection('provider_categories');
        if(section_id){
            ref = ref.where('sectionId', '==', section_id);
        }
        var placeholderImage = '';
        var append_list = '';
        $(document).ready(function () {
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
                html = await buildHTML(snapshots);
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                });
                
                jQuery("#data-table_processing").hide();
                if (html != '') {
                    append_list.innerHTML = html;
                    if (snapshots.docs.length < pagesize) {
                        jQuery("#data-table_paginate").hide();
                    }
                }
                var table = $('#categoryTable').DataTable({
                    columnDefs: [{
                        targets: (checkDeletePermission==true) ? 2 :1,
                        type: 'date',
                        render: function (data) {
                            return data;
                        }
                    },
                    {orderable: false, targets: [0,1, 2]},
                    ],
                    order: [],
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
            if (snapshots.docs.length > 0) {
                $('.total_count').text(snapshots.docs.length); 
            }
            else
            {
                $('.total_count').text(0); 
            }
            var categories_data = [];
            snapshots.docs.forEach((listval) => {
                var datas = listval.data();
                datas.id = listval.id;
                categories_data.push(datas);
            });
            var categories_list = [];
            for(var i = 0; i < categories_data.length ; i++){
                if(categories_data[i].level == 0){
                    categories_list.push({
                        'id':categories_data[i].id,
                        'image':categories_data[i].image,
                        'level':categories_data[i].level,
                        'parentCategoryId':categories_data[i].parentCategoryId,
                        'publish':categories_data[i].publish,
                        'title':'<p class="font-weight-bold">'+categories_data[i].title+'</p>',
                    });
                    var children = await getChildCategories(categories_data[i].id, 0, 1);
                    if(children.length > 0){
                        for(var j = 0; j < children.length ; j++){
                            categories_list.push(children[j]);
                        }
                    }
                }
            }
            var html = await getListData(categories_list);
            return html;
        }
        async function getChildCategories(categoryId, level, depth = 0) {
            var snapshots = await database.collection('provider_categories').where("parentCategoryId", "==", categoryId).get();
            var sub_categories_data = [];
            snapshots.docs.forEach((listval) => {
                var datas = listval.data();
                sub_categories_data.push(datas);
            });
            var sub_html = "";
            for(var count = 0; count < depth ; count++){
                sub_html = sub_html + "-";
            }
            var sub_categories_list = [];
            for(var i = 0; i < sub_categories_data.length ; i++){
                sub_categories_list.push({
                    'id':sub_categories_data[i].id,
                    'image':sub_categories_data[i].image,
                    'level':sub_categories_data[i].level,
                    'parentCategoryId':sub_categories_data[i].parentCategoryId,
                    'publish':sub_categories_data[i].publish,
                    'title':sub_html + sub_categories_data[i].title,
                });
            }
            return sub_categories_list;
        }
        async function getListData(categories) {
            var html = '';
            for(var i = 0; i < categories.length ; i++){
                var val = categories[i];
                var id = val.id;
                var route1 = '{{route("ondemandcategory.edit",":id")}}';
                route1 = route1.replace(':id', id);
                if (checkDeletePermission) {
                    html += '<tr><td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                        'for="is_open_' + id + '" ></label></td>';
                }
                if (val.image == '') {
                    html += '<td><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image"><a class="left_space" href="' + route1 + '">' + val.title + '</a></td>';
                } else {
                    html += '<td><img class="rounded" style="width:50px" src="' + val.image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image"><a class="left_space" href="' + route1 + '">' + val.title + '</a></td>';
                }
                if (val.publish) {
                    html += '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';
                } else {
                    html += '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';
                }
                html += '<td><span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" title="{{trans("lang.edit")}}"><i class="mdi mdi-lead-pencil"></i></a>';
                if (checkDeletePermission) {
                    html += '<a id="' + val.id + '" name="category-delete"  class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" title="{{trans("lang.delete")}}"><i class="mdi mdi-delete"></i></a></span>';
                }
                html += '</td></tr>';
            }
            return html;
        }
        $(document).on("click", "input[name='isSwitch']", function(e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            var publish = ischeck ? true : false;
            database.collection('provider_categories').doc(id).update({
                'publish': publish
            });
        });
        $(document).on("click", "a[name='category-delete']", async function (e) {
            var id = this.id;
            await deleteDocumentWithImage('provider_categories',id,'image');
            window.location.reload();
        });
        $("#is_active").click(function () {
            $("#categoryTable .is_open").prop('checked', $(this).prop('checked'));
        });
        $("#deleteAll").click(function () {
            if ($('#categoryTable .is_open:checked').length) {
                if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                    jQuery("#data-table_processing").show();
                    $('#categoryTable .is_open:checked').each(async function () {
                        var dataId = $(this).attr('dataId');
                        await deleteDocumentWithImage('provider_categories',dataId,'image');
                        window.location.reload();
                    });
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });
    </script>
@endsection
