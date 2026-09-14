@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.on_board_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.on_board_table')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/onboarding.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.on_board_plural')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.on_board_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.on_board_table_text')}}</p>
                   </div>             
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="userTable" class="display  table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                    <th>{{trans('lang.onboarding_info')}}</th>
                                    <th>{{trans('lang.description')}}</th>
                                    <th>{{trans('lang.app_screen')}}</th>
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
    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })
    var ref = database.collection('on_boarding');

    var append_list = '';

    $(document).ready(function () {

        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        jQuery("#data-table_processing").show();
        const table = $('#userTable').DataTable({
            pageLength: 10,
            processing: false,
            serverSide: true,
            responsive: true,
            ajax: async function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;

                const orderableColumns = ['image','title','description','type',''];

                const orderByField = orderableColumns[orderColumnIndex];

                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }

                try {
                    const querySnapshot = await ref.get();
                    if (querySnapshot.empty) {
                        $('.total_count').text(0);
                        $('#data-table_processing').hide();
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                        return;
                    }

                    let records = [];
                    let filteredRecords = [];

                    await Promise.all(querySnapshot.docs.map(async (doc) => {
                        let childData = doc.data();
                        childData.id = doc.id;

                        if (searchValue) {
                            if (
                                (childData.title && childData.title.toLowerCase().includes(searchValue)) ||
                                (childData.description && childData.description.toLowerCase().includes(searchValue)) ||
                                (childData.type && childData.type.toLowerCase().includes(searchValue))
                            ) {
                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    }));

                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase().trim() : '';
                        let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase().trim() : '';
                        if (orderDirection === 'asc') {
                            return (aValue > bValue) ? 1 : -1;
                        } else {
                            return (aValue < bValue) ? 1 : -1;
                        }
                    });

                    const totalRecords = filteredRecords.length;
                    $('.total_count').text(totalRecords);
                    const paginatedRecords = filteredRecords.slice(start, start + length);

                    const formattedRecords = await Promise.all(paginatedRecords.map(async (childData) => {
                        return await buildHTML(childData);
                    }));
                        $(function () {
                                $('[data-toggle="tooltip"]').tooltip();
                            });
                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords,
                        recordsFiltered: totalRecords,
                        data: formattedRecords
                    });

                } catch (error) {
                    console.error("Error fetching data from Firestore:", error);
                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: []
                    });
                }
            },
            order: [2, 'desc'],
            columnDefs: [
                { orderable: false, targets: [3] },
            ],
           "language": datatableLang,
        });
        function debounce(func, wait) {
            let timeout;
            const context = this;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }
        $('#search-input').on('input', debounce(function () {
            const searchValue = $(this).val();
            if (searchValue.length >= 3) {
                $('#data-table_processing').show();
                table.search(searchValue).draw();
            } else if (searchValue.length === 0) {
                $('#data-table_processing').show();
                table.search('').draw();
            }
        }, 300));

    });

    function buildHTML(val) {
        var html = [];
        newdate = '';
        var id = val.id;
        var route1 = '{{route("on-board.save",":id")}}';
        route1 = route1.replace(':id', id);

        if (val.image == '' || val.image == null) {
            html.push('<td><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image"><a href="' + route1 + '" class="onboard-edit left_space">' + val.title + '</a></td>');
        } else {
            html.push('<td><img class="rounded" style="width:50px" src="' + val.image + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image"><a href="' + route1 + '" class="onboard-edit left_space">' + val.title + '</a></td>');
        }
        html.push('<td>' + val.description + '</td>');
        if (val.type == "provider") {
            var type = "{{trans('lang.provider_app')}}";
        }else if (val.type == "worker") {
            var type ="{{trans('lang.worker_app')}}";
        }else if (val.type == "customer") {
            var type ="{{trans('lang.customer_app')}}";
        }else if (val.type == "driver") {
            var type ="{{trans('lang.driver_app')}}";
        }else if (val.type == "store") {
            var type ="{{trans('lang.store_app')}}";
        }
        html.push('<td>' + type + '</td>');
        html.push('<span class="action-btn"><a href="' + route1 + '" class="onboard-edit" data-toggle="tooltip" title="{{trans("lang.edit")}}"><i class="mdi mdi-lead-pencil"></i></a></span>');

        return html;
    }

</script>

@endsection