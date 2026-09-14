@extends('layouts.app')



@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.currency_table')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.currency_table')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/currency.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.currency_table')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.currency_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.currency_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('currencies.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.currency_create')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                         <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                 
                                <th>{{trans('lang.country')}}</th>

                                <th>{{trans('lang.currency_name')}}</th>



                                <th>{{trans('lang.currency_symbol')}}</th>



                                <th>{{trans('lang.currency_code')}}</th>

                                <th>{{trans('lang.symbole_at_right')}}</th>

                                <th>{{trans('lang.active')}}</th>

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

    var ref = database.collection('currencies');



    var append_list = '';

    var user_permissions = '<?php echo @session('user_permissions') ?>';



    user_permissions = Object.values(JSON.parse(user_permissions));



    var checkDeletePermission = false;



    if ($.inArray('currency.delete', user_permissions) >= 0) {

        checkDeletePermission = true;

    }

    $(document).ready(function() {



        var inx = parseInt(offest) * parseInt(pagesize);

        jQuery("#data-table_processing").show();



        append_list = document.getElementById('append_list1');

        append_list.innerHTML = '';

        ref.get().then(async function(snapshots) {

            html = '';

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

            var table = $('#example24').DataTable({



                order: [],

                columnDefs: [{

                        targets: 4,

                        type: 'date',

                        render: function(data) {

                            return data;

                        }

                    },

                    {

                        orderable: false,

                        targets: [2, 4, 5, 6]

                    },

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

        html = html + '<tr>';



        var id = val.id;

        var route1 = '{{route("currencies.edit",":id")}}';

        route1 = route1.replace(':id', id);

        if (val.country != undefined) {

            country = val.country;

        } else {

            country = '';

        }

        html = html + '<td>' + country + '</td>';

        html = html + '<td>' + val.name + '</td>';

        html = html + '<td>' + val.symbol + '</td>';

        html = html + '<td>' + val.code + '</td>';

        if (val.symbolAtRight) {

            html = html + '<td><span class="badge badge-success">{{trans("lang.yes")}}</span></td>';

        } else {

            html = html + '<td><span class="badge badge-danger">{{trans("lang.no")}}</span></td>';

        }



        if (val.isActive) {

            html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';

        } else {

            html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';

        }



        html = html + '<td><span class="action-btn"><a href="' + route1 + '" class="do_not_edit" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';

        if (checkDeletePermission) {

       

        html=html+'<a id="' + val.id + '" name="currency-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a></span></td>';

    }

        html = html + '</tr>';

        return html;

    }



    $(document).on("click", "input[name='isSwitch']", function(e) {

        var ischeck = $(this).is(':checked');

        var id = this.id;

        if (ischeck) {

            database.collection('currencies').doc(id).update({

                'isActive': true

            }).then(function(result) {});



            database.collection('currencies').where('isActive', "==", true).get().then(function(snapshots) {

                var activeCurrency = snapshots.docs[0].data();

                var activeCurrencyId = activeCurrency.id;

                database.collection('currencies').doc(activeCurrencyId).update({

                    'isActive': false

                });



                $("#append_list1 tr").each(function() {

                    $(this).find(".switch #" + activeCurrencyId).prop('checked', false);

                });

            });

        } else {

            database.collection('currencies').where('isActive', "==", true).get().then(function(snapshots) {

                var activeCurrency = snapshots.docs[0].data();

                var activeCurrencyId = activeCurrency.id;

                if (snapshots.docs.length == 1 && activeCurrencyId == id) {

                    alert('Can not disable all currency');

                    $("#" + id).prop('checked', true);

                    return false;

                } else {

                    database.collection('currencies').doc(id).update({

                        'isActive': false

                    }).then(function(result) {});

                }

            });

        }

    });



    $(document).on("click", "a[name='currency-delete']", function(e) {

        var id = this.id;

        database.collection('currencies').doc(id).delete().then(function() {

            window.location.reload();

        });

    });

</script>



@endsection