@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.destination')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.destination_table')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/zone.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.destination')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.destination_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.destination_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">                    
                        <a class="btn-primary btn rounded-full" href="{!! route('destinations.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.destination_create')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="destinationTable"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <?php if (in_array('destinations.delete', json_decode(@session('user_permissions'),true))) { ?>
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll"
                                    class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php } ?>
                                    <th>{{trans('lang.location_info')}}</th>
                                    
                                    <th>{{trans('lang.item_publish')}}</th>
                                    <th>{{trans('lang.actions')}}</th>
                                </tr>
                                </thead>  
                                <tbody id="append_destinations">
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
    if ($.inArray('destinations.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }    
    var database = firebase.firestore();

    var offest = 1;
    var pagesize = 10;
    var end = null;
    var endarray = [];
    var start = null;
    var user_number = [];

    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function(snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    });

    var refData = database.collection('popular_destinations');
    if(section_id){
        refData = refData.where('sectionId', '==', section_id);
    }
    
    var append_list = '';

    $(document).ready(function() {
        pagesizes = getCookie('pagesizes');

        var inx = parseInt(offest) * parseInt(pagesize);

        jQuery("#data-table_processing").show();


        append_list = document.getElementById('append_destinations');

        append_list.innerHTML = '';

        refData.get().then(async function(snapshots) {
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
                if (snapshots.docs.length < pagesize) {
                    jQuery("#data-table_paginate").hide();
                }
            }            
            const table = $('#destinationTable').DataTable({
                order: (checkDeletePermission==true) ? [[1, 'asc']] : [[0,'asc']],
                columnDefs: [
                    {
                        orderable: false,
                        targets: (checkDeletePermission==true) ? [0, 2, 3] : [1, 2]
                    }
                ],
               "language": datatableLang,
                responsive: true
            });

            table.on('search.dt', function() {
                var filteredCount = table.rows({ search: 'applied' }).count();
                $('.total_count').text(filteredCount);
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

            var route1 = '{{route("destinations.edit",":id")}}';

            route1 = route1.replace(':id', id);
            if(checkDeletePermission){
            html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id +
                '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label></td>';
            }
            if (val.image != '') {
                html = html + '<td><img alt="" width="100%" style="width:70px;height:70px;" src="' + val.image +
                    '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">  <a href="' + route1 + '" class="left_space">' + val.title + '</a></td>';

            } else {

                html = html + '<td><img alt="" width="100%" style="width:70px;height:70px;" src="' + placeholderImage + '" alt="image">  <a href="' + route1 + '" class="left_space">' + val.title + '</a></td>';
            }

          

            if (val.is_publish) {
                html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id +
                    '" name="isActive"><span class="slider round"></span></label></td>';
            } else {
                html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id +
                    '" name="isActive"><span class="slider round"></span></label></td>';
            }

           html = html + '<td><span class="vendor-action-btn action-btn"><a href="' + route1 +'" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
           if(checkDeletePermission){
           html=html+'<a id="' + val.id +'" name="vendor-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
           }
           html=html+'</span></td>';


            html = html + '</tr>';

        return html;

    }

    $(document).on("click", "input[name='isActive']", function(e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        if (ischeck) {
            database.collection('popular_destinations').doc(id).update({
                'is_publish': true
            }).then(function(result) {

            });
        } else {
            database.collection('popular_destinations').doc(id).update({
                'is_publish': false
            }).then(function(result) {

            });
        }
    });

    async function getSectionName(sectionId) {

        var refData = await database.collection('sections').where("id", "==", sectionId).get().then(async function(
            snapshots) {

            var data = snapshots.docs[0].data();

            $('.sectionName_' + sectionId).html(data.name);


        });

    }

   

    $(document).on("click", "a[name='vendor-delete']",async  function(e) {
        jQuery("#data-table_processing").show();
        var id = this.id;
        await deleteDocumentWithImage('popular_destinations',id,'image');
        window.location.reload();
    });

    $("#is_active").click(function() {
        $("#destinationTable .is_open").prop('checked', $(this).prop('checked'));

    });

    $("#deleteAll").click(function() {
        if ($('#destinationTable .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                $('#destinationTable .is_open:checked').each(async function() {
                    var dataId = $(this).attr('dataId');
                    await deleteDocumentWithImage('popular_destinations',dataId,'image');
                    window.location.reload();
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });

    function clickpage(value) {
        setCookie('pagesizes', value, 30);
        window.location.reload();
    }
</script>

@endsection