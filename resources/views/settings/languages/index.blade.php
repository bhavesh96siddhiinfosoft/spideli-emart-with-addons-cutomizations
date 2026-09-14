@extends('layouts.app')



@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.languages')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.languages')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/language.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.languages')}}</h3>
                        <span class="counter ml-3 total_count"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                        <div class="select-box pl-3">
                           
                        </div>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.languages')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.language_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('settings.app.languages.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.language_create')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                         <table id="example24" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>

                                <th>{{trans('lang.language_info')}}</th>

                                <th>{{trans('lang.slug')}}</th>

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

    var user_number = [];

    var languages = [];



    var placeholderImageData = '';

    var placeholderImage = '';



    var placeholder = database.collection('settings').doc('placeHolderImage');



    var ref = database.collection('settings').doc('languages');

    var append_list = '';



    var user_permissions = '<?php echo @session('user_permissions') ?>';



    user_permissions = Object.values(JSON.parse(user_permissions));



    var checkDeletePermission = false;



    if ($.inArray('language.delete', user_permissions) >= 0) {

        checkDeletePermission = true;

    }



    $(document).ready(function() {





        $(document.body).on('click', '.redirecttopage', function() {

            var url = $(this).attr('data-url');

            window.location.href = url;

        });



        var inx = parseInt(offest) * parseInt(pagesize);

        jQuery("#data-table_processing").show();







        append_list = document.getElementById('append_list1');

        append_list.innerHTML = '';

        placeholder.get().then(async function(snapshotsimage) {

            placeholderImageData = snapshotsimage.data();

            placeholderImage = placeholderImageData.image;



            ref.get().then(async function(snapshots) {

                html = '';
                snapshots = snapshots.data();
                if (snapshots) {
                    snapshots = snapshots.list;
                    languages = snapshots;
                    html = await buildHTML(snapshots);
                    if (html != '') {
                        append_list.innerHTML = html;
                    }
                }

                var table = $('#example24').DataTable({

                    order: [],

                    columnDefs: [



                        {

                            orderable: false,

                            targets: [1, 2, 3]

                        },

                    ],

                    order: [

                        ['0', 'asc']

                    ],

                    "language": datatableLang,


                    responsive: true

                });
                table.on('search.dt', function() {
                    var filteredCount = table.rows({ search: 'applied' }).count();
                    $('.total_count').text(filteredCount);  // Update count
                });

                $(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                });

                jQuery("#data-table_processing").hide();

            });

        });









    });



    function buildHTML(snapshots) {

        var html = '';

        var alldata = [];

        var number = [];

        $('.total_count').text(snapshots.length); 

        if (snapshots.length) {

            snapshots.forEach((listval) => {

                var datas = listval;

                datas.id = listval.id;

                alldata.push(datas);

            });

            var count = 0;

            alldata.forEach((listval) => {

                var val = listval;

                html = html + '<tr>';

                newdate = '';

                var id = val.slug;

                var route1 = '{{route("settings.app.languages.edit",":id")}}';

                route1 = route1.replace(':id', id);

                if (val.flag == '' || val.flag == undefined) {



                    html = html + '<td><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"><a href="' + route1 + '" class="left_space">' + val.title + '</a></td>';



                } else {

                    

                    if(val.flag){

                        photo=val.flag;

                    }else{

                        photo=placeholderImage;

                    }

                    html = html + '<td><img class="rounded" style="width:50px" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image"><a href="' + route1 + '" class="left_space">' + val.title + '</a></td>';



                }



                html = html + '<td>' + val.slug + '</td>';



                if (val.isActive) {

                    html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.slug + '" name="isSwitch"><span class="slider round"></span></label></td>';

                } else {

                    html = html + '<td><label class="switch"><input type="checkbox" id="' + val.slug + '" name="isSwitch"><span class="slider round"></span></label></td>';

                }





                html = html + '<td><span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';

                if (checkDeletePermission) {



                    html = html + '<a id="' + val.slug + '" class="delete-btn" name="lang-delete" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a></span></td>';

                }



                html = html + '</tr>';

                count = count + 1;

            });

        }

        return html;

    }



    $(document).on("click", "input[name='isSwitch']", function(e) {

        var ischeck = $(this).is(':checked');

        var id = this.id;

        var language_key = '';

        ref.get().then(async function(snapshots) {



            snapshots = snapshots.data();

            snapshots = snapshots.list;

            if (snapshots.length) {

                languages = snapshots;

            }

            for (var key in snapshots) {

                if (snapshots[key]['slug'] == id) {

                    language_key = key;

                }

            }

            if (ischeck) {

                languages[language_key]['isActive'] = true;



                database.collection('settings').doc('languages').update({

                    'list': languages

                }).then(function(result) {});

            } else {

                languages[language_key]['isActive'] = false;

                database.collection('settings').doc('languages').update({

                    'list': languages

                }).then(function(result) {});

            }

        });

    });



    $(document).on("click", "a[name='lang-delete']", function(e) {

        var id = this.id;

        var newlanguage = [];

        languages.forEach((language) => {

            if (id != language.slug) {

                delete language.id;

                newlanguage.push(language);

            }

        });

        jQuery("#data-table_processing").show();

        database.collection('settings').doc('languages').update({

            'list': newlanguage

        }).then(function(result) {

            jQuery("#data-table_processing").hide();

            window.location.href = '{{ route("settings.app.languages") }}';

        });



    });

</script>



@endsection