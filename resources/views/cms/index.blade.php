@extends('layouts.app')



@section('content')


<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.cms_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.cms_plural')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/cms.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.cms_plural')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.cms_plural')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.cms_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('cms.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.cms_create')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                         <table id="cmsTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                      
                                    <th>{{trans('lang.cms_name')}}</th>
                                    <th>{{trans('lang.cms_slug')}}</th>
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

    var database = firebase.firestore();

    var offest = 1;

    var pagesize = 10;

    var end = null;

    var endarray = [];

    var start = null;

    var user_number = [];

    var ref = database.collection('cms_pages');

    var append_list = '';

    var placeholderImage = '';

    var user_permissions = '<?php echo @session('user_permissions') ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));

    var checkDeletePermission = false;
    if ($.inArray('cms.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }

    $(document).ready(function() {



        var inx = parseInt(offest) * parseInt(pagesize);

        jQuery("#data-table_processing").show();



        append_list = document.getElementById('append_list1');

        append_list.innerHTML = '';





        ref.get().then(async function(snapshots) {

            html = '';

            html = await buildHTML(snapshots);
             $(function () {
                                $('[data-toggle="tooltip"]').tooltip();
                            });

            jQuery("#data-table_processing").hide();

            if (snapshots.docs.length > 0) {
                $('.total_count').text(snapshots.docs.length); 
                html = await buildHTML(snapshots);
            }
            else
            {
                $('.total_count').text(0); 
            }


            if (html != '') {

                append_list.innerHTML = html;

                start = snapshots.docs[snapshots.docs.length - 1];

                endarray.push(snapshots.docs[0]);

                if (snapshots.docs.length < pagesize) {

                    jQuery("#data-table_paginate").hide();

                }
            }
            
            var table = $('#cmsTable').DataTable({



                order: [ ['1', 'asc']],

                columnDefs: [{

                    orderable: false,

                    targets: [2, 3]

                }, ],

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
 


        var id = val.id;

        var route1 = '{{route("cms.edit",":id")}}';

        route1 = route1.replace(':id', id);



        html = html + '<td><a href="' + route1 + '">' + val.name + '</a></td>';



        html = html + '<td>' + val.slug + '</td>';



        if (val.publish) {

            html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';

        } else {

            html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';

        }

        html = html + '<td><span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" title="{{trans("lang.edit")}}"><i class="mdi mdi-lead-pencil"></i></a>';

        if (checkDeletePermission) {

            html = html + '<a id="' + val.id + '" name="category-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" title="{{trans("lang.delete")}}"><i class="mdi mdi-delete"></i></a></span></td>';

        }

        html = html + '</tr>';

        count = count + 1;

        return html;

    }



    $(document).on("click", "input[name='isSwitch']", function(e) {

        var ischeck = $(this).is(':checked');

        var id = this.id;

        if (ischeck) {

            database.collection('cms_pages').doc(id).update({

                'publish': true

            }).then(function(result) {});

        } else {

            database.collection('cms_pages').doc(id).update({

                'publish': false

            }).then(function(result) {});

        }

    });







    $(document).on("click", "a[name='category-delete']", function(e) {

        var id = this.id;

        jQuery("#data-table_processing").show();

        database.collection('cms_pages').doc(id).delete().then(function(result) {

            window.location.reload();

        });

    });

</script>



@endsection