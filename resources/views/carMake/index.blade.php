@extends('layouts.app')



@section('content')


<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.car_make')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.car_make')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/car.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.car_make')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.car_make')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.carmake_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('carMake.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.add_car_make')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                         <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                      
                                    <th>{{trans('lang.name')}}</th>
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

    var ref = database.collection('car_make');

    var placeholderImage = '';

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

            var table = $('#example24').DataTable({



                order: [],

                columnDefs: [

                    {orderable: false, targets: [1, 2]},

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

        var route1 = '{{route("carMake.edit",":id")}}';

        route1 = route1.replace(':id', id);



        html = html + '<td data-url="' + route1 + '" class="redirecttopage">' + val.name + '</td>';



        if (val.isActive) {

            html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';

        } else {

            html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';

        }



        html = html + '<td><span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans("lang.edit") }}"><i class="mdi mdi-lead-pencil"></i></a>';

        <?php if(in_array('make.delete', json_decode(@session('user_permissions'),true))){?>



        html = html + '<a id="' + val.id + '" name="carMake-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans("lang.delete") }}"><i class="mdi mdi-delete"></i></a></span>';

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

            database.collection('car_make').doc(id).update({'isActive': true}).then(function (result) {

            });

        } else {

            database.collection('car_make').doc(id).update({'isActive': false}).then(function (result) {

            });

        }



    });



    $(document.body).on('click', '.redirecttopage', function () {

        var url = $(this).attr('data-url');

        window.location.href = url;

    });





    $(document).on("click", "a[name='carMake-delete']", function (e) {

        var id = this.id;

        database.collection('car_make').doc(id).delete().then(function () {

            window.location.reload();

        });

    });





</script>



@endsection

