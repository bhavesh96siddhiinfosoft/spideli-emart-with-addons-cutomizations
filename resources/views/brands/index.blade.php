@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.brand')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.brand_table')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/brand.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.brand')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.brand_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.brand_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">                   
                        <a class="btn-primary btn rounded-full" href="{!! route('brands.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.brand_create')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="brandTable"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <?php if (in_array('brands.delete', json_decode(@session('user_permissions'),true))) { ?>
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll"
                                    class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php } ?>  
                                    <th>{{trans('lang.brand_info')}}</th>
                                    <th>{{trans('lang.item')}}</th>
                                    <th>{{trans('lang.item_publish')}}</th>
                                    <th>{{trans('lang.actions')}}</th>
                                </tr>
                                </thead>  
                                <tbody id="append_vendors">
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
    if ($.inArray('brands.delete', user_permissions) >= 0) {
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
    var ref = database.collection('brands');

    if(section_id){
        ref = ref.where('sectionId', '==', section_id);
    }

    var append_list = '';
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function(snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    });

    $(document).ready(function() {
        var inx = parseInt(offest) * parseInt(pagesizes);
        jQuery("#data-table_processing").show();
        append_list = document.getElementById('append_vendors');
        append_list.innerHTML = '';
        ref.get().then(async function(snapshots) {
            var html = '';
            if (snapshots.docs.length > 0) {
                $('.total_count').text(snapshots.docs.length); 
                
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
                if (snapshots.docs.length < pagesizes) {
                    jQuery("#data-table_paginate").hide();
                }
            }

            $('#brandTable').DataTable({
                order: [],
                columnDefs: [
                    {
                        orderable: false,
                        targets: (checkDeletePermission==true) ? [0, 3, 4] : [2,3]
                    },
                ],
                order: (checkDeletePermission==true) ? [1, 'asc'] : [0,'asc'],
               "language": datatableLang,
                responsive: true
            });
        });
    })

    async function buildHTML(snapshots) {
        var html = '';
        await Promise.all(snapshots.docs.map(async (listval) => {
            var val = listval.data();
            let result = user_number.filter(obj => {
                return obj.id == val.author;
            })

            if (result.length > 0) {
                val.phoneNumber = result[0].phoneNumber;
                val.isActive = result[0].isActive;
            } else {
                val.phoneNumber = '';
                val.isActive = false;
            }
            var getData = await getListData(val);
            html += getData;

        }));
        return html;
    }

    async function getListData(val) {
        var html = '';
        html = html + '<tr>';
        newdate = '';
        var id = val.id;
        var route1 = '{{route("brands.edit",":id")}}';
        route1 = route1.replace(':id', id);
        if(checkDeletePermission){
            html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' + 'for="is_open_' + id + '" ></label></td>';

        }
        if (val.photo != '') {
            html = html + '<td><img alt="" width="100%" style="width:70px;height:70px;" src="' + val.photo + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">  <a href="' + route1 + '" class="left_space">' + val.title + '</a></td>';

        } else {
            html = html + '<td><img alt="" width="100%" style="width:70px;height:70px;" src="' + placeholderImage + '" alt="image">  <a href="' + route1 + '" class="left_space">' + val.title + '</a></td>';
        }
        var section = await getSectionName(val.sectionId);
        
        var total= await getProductTotal(val.id);
        var brandId = val.id;
        var url = '{{url("items?brandID=id")}}';
        url = url.replace("id", brandId);
        html = html + '<td ><a href="' + url + '">'+total+'</a></td>';
        if (val.is_publish) {
            html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';

        } else {
            html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';

        }
        html = html + '<td><span class="vendor-action-btn action-btn"><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';

        if(checkDeletePermission){
            html= html+'<a id="' + val.id + '" name="vendor-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';

        }
        html=html+'</span></td>';
        html = html + '</tr>';
        return html;
    }

    /* toggal publish action code start*/

    $(document).on("click", "input[name='isSwitch']", function(e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        if (ischeck) {
            database.collection('brands').doc(id).update({
                'is_publish': true

            }).then(function(result) {

            });

        } else {
            database.collection('brands').doc(id).update({
                'is_publish': false

            }).then(function(result) {

            });
        }
    });

    /*toggal publish action code end*/
    async function getSectionName(sectionId) {
        var sectionName = '';

        if (sectionId != '') {

            await database.collection('sections').where("id", "==", sectionId).get().then(async function (snapshots) {



                if (snapshots.docs.length) {

                    var data = snapshots.docs[0].data();

                    sectionName = data.name;

                }

            });

        }
        return sectionName;

    }
    async function getProductTotal(id) {

        var Product_total ='';

       await database.collection('vendor_products').where('brandID', '==', id).get().then(async function(productSnapshots) {
            Product_total = productSnapshots.docs.length;
        });
        return Product_total;

    }

    $(document).on("click", "a[name='vendor-delete']", async function(e) {
        var id = this.id;
        await deleteDocumentWithImage('brands',id,'photo');
        window.location.reload();

    });

    $("#is_active").click(function() {
        $("#brandTable .is_open").prop('checked', $(this).prop('checked'));

    });

    $("#deleteAll").click(function() {
        if ($('#brandTable .is_open:checked').length) {

            if (confirm("{{trans('lang.selected_delete_alert')}}")) {

                jQuery("#data-table_processing").show();

                $('#brandTable .is_open:checked').each(async function() {

                    var dataId = $(this).attr('dataId');

                    await deleteDocumentWithImage('brands',dataId,'photo');

                    window.location.reload();

                });
            }
        }

    else {
            alert("{{trans('lang.select_delete_alert')}}");
        }

    });

    function clickpage(value) {
        setCookie('pagesizes', value, 30);
        location.reload();
    }

</script>

@endsection

