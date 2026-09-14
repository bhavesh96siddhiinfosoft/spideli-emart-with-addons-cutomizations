@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.reviewattribute_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.reviewattribute_table')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/attribute.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.reviewattribute_plural')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.reviewattribute_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.attribute_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">                     
                        <a class="btn-primary btn rounded-full" href="{!! route('reviewattributes.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.reviewattribute_create')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="example24"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th>{{trans('lang.reviewattribute_name')}}</th>
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
    
    var user_permissions = '<?php echo @session('user_permissions') ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;

    if ($.inArray('review.attributes.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
    var database = firebase.firestore();
    var offest = 1;
    var pagesize = 10;
    var end = null;
    var endarray = [];
    var start = null;
    var user_number = [];

    var ref = database.collection('review_attributes');
    var append_list = '';

    $(document).ready(function () {

        var inx = parseInt(offest) * parseInt(pagesize);
        jQuery("#data-table_processing").show();

        append_list = document.getElementById('append_list1');
        append_list.innerHTML = '';
        ref.get().then(async function (snapshots) {
            html = '';
            if (snapshots.docs.length > 0) {
                $('.total_count').text(snapshots.docs.length); 
                
            }
            else
            {
                $('.total_count').text(0);                 
            }
            html = buildHTML(snapshots);
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
            $('#example24').DataTable({
                
                order: [],
                columnDefs: [
                    {orderable: false, targets: [1]},
                ],
                order: [0,"asc"],
                "language": datatableLang,

                responsive: true,
            });
        });

    });

    function buildHTML(snapshots) {
        var html = '';
        var alldata = [];
        var number = [];
        snapshots.docs.forEach((listval) => {
            var datas = listval.data();
            alldata.push(datas);
        });

        var count = 0;
        alldata.forEach((listval) => {

            var val = listval;

            html = html + '<tr>';
            newdate = '';

            var id = val.id;
            var route1 = '{{route("reviewattributes.edit",":id")}}';
            route1 = route1.replace(':id', id);

            html = html + '<td>' + val.title + '</td>';
            html = html + '<td><span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
            if(checkDeletePermission){
                html=html+'<a id="' + val.id + '" name="reviewattribute-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
            }
            html=html+'</td></span>';


            html = html + '</tr>';
            count = count + 1;
        });
        return html;
    }

   
    $(document).on("click", "a[name='reviewattribute-delete']", function (e) {        
        var id = this.id;        
        database.collection('review_attributes').doc(id).delete().then(function (result) {
            window.location.href = '{{ route("reviewattributes")}}';
        });
    });

</script>

@endsection