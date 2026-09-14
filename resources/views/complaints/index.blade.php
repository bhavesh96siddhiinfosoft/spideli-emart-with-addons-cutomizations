@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.complaints') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.complaints') }}</li>
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
                                <span class="icon mr-3"><img src="{{ asset('images/faq.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.complaints') }}</h3>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.complaints') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.complaint_table_text') }}</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive m-t-10">
                                    <table id="example24"
                                        class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                        cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('lang.order_id') }}</th>
                                                <th>{{ trans('lang.title') }}</th>
                                                <th>{{ trans('lang.description') }}</th>
                                                <th>{{ trans('lang.item_review_user_id') }}</th>
                                                <th>{{ trans('lang.driver') }}</th>
                                                <th>{{ trans('lang.status') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
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
        var ref = database.collection('complaints');
        var placeholderImage = '';
        var append_list = '';

        $(document).ready(function() {

            var inx = parseInt(offest) * parseInt(pagesize);
            jQuery("#data-table_processing").show();


            append_list = document.getElementById('append_list1');
            append_list.innerHTML = '';
            ref.get().then(async function(snapshots) {
                html = '';

                html = await buildHTML(snapshots);
                $(function() {
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
                            targets: 6,
                            type: 'date',
                            render: function(data) {
                                return data;
                            }
                        },
                        {
                            orderable: false,
                            targets: [0, 3, 4]
                        },
                    ],
                    order: [0, "asc"],
                    "language": datatableLang,
                    responsive: true,
                });
                table.on('search.dt', function() {
                    var filteredCount = table.rows({
                        search: 'applied'
                    }).count();
                    $('.total_count').text(filteredCount); // Update count
                });
            });

        });


        async function buildHTML(snapshots) {
            var html = '';
            var totalCount = 0;
       ;
            // We'll collect valid complaints after checking ride's sectionId
            const validComplaints = [];

            await Promise.all(snapshots.docs.map(async (listval) => {
                var val = listval.data();
                var id = listval.id;
                var currentSectionId = getCookie('section_id');
                if (!val.orderId) {
                    return; // skip if no orderId
                }

                try {
                    // Fetch the ride (order) to get its sectionId
                    var rideSnap = await database.collection('rides').doc(val.orderId).get();

                    if (rideSnap.exists) {
                        var rideData = rideSnap.data();
                        var rideSectionId = rideData.sectionId ? rideData.sectionId.toString() : null;

                        // Compare with current admin's section_id
                        if (currentSectionId && rideSectionId === currentSectionId) {
                            validComplaints.push({
                                data: val,
                                id: id
                            });
                            totalCount++;
                        }
                    }
                    // If ride doesn't exist or no section match → skip (not pushed)
                } catch (err) {
                    console.error("Error fetching ride for complaint:", val.orderId, err);
                }
            }));
            if (snapshots.docs.length > 0) {
                $('.total_count').text(totalCount);

            } else {
                $('.total_count').text(0);
            }
            for (let item of validComplaints) {
                let val = item.data;
                let getData = await getListData(val, item.id); // pass id too
                html += getData;
            }
            return html;
        }

        async function getListData(val) {
            var html = '';
            var count = 0;
            html = html + '<tr>';
            newdate = '';
            var id = val.id;
            var routeEdit = '{{ route('complaints.edit', ':id') }}';
            routeEdit = routeEdit.replace(':id', id);

            var driverId = val.driverId;

            var user_id = val.customerId;
            var route1 = '{{ route('rides.edit', ':id') }}';
            route1 = route1.replace(':id', val.orderId);
            var route2 = '{{ route('users.view', ':id') }}';
            route2 = route2.replace(':id', user_id);

            var diverRoute = '{{ route('drivers.view', ':id') }}';
            diverRoute = diverRoute.replace(':id', driverId);

            html = html + '<td><a href="' + route1 +
                '" class="redirecttopage" data-toggle="tooltip" data-bs-original-title="' + val.orderId + '">' + (val.id
                    .length > 8 ? val.orderId.substring(0, 8) + '...' : val.orderId) + '</a></td>';

            html = html + '<td>' + val.title + '</td>';
            html = html + '<td>' + val.description + '</td>';

            html = html + '<td><a href="' + route2 + '" class="redirecttopage">' + val.customerName + '</a></td>';
            html = html + '<td><a href="' + diverRoute + '" class="redirecttopage">' + val.driverName + '</a></td>';
            if (val.status == "Resolved") {
                html = html + '<td><span class="badge badge-success">' + val.status + '</span></td>';
            } else {
                html = html + '<td><span class="badge badge-primary">' + val.status + '</span></td>';
            }

            html = html + '<td><span class="action-btn"><a href="' + routeEdit +
                '" data-toggle="tooltip" title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';

            <?php if(in_array('complaints.delete', json_decode(@session('user_permissions'),true))){?>

            html = html + '<a id="' + val.id +
                '" name="carModel-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a></span>';
            <?php }?>
            html = html + '</td>';
            html = html + '</tr>';
            count = count + 1;

            return html;
        }

        $(document.body).on('click', '.redirecttopage', function() {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        $(document).on("click", "a[name='carModel-delete']", function(e) {
            var id = this.id;
            database.collection('complaints').doc(id).delete().then(function() {
                window.location.reload();
            });
        });
    </script>
@endsection
