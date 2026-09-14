@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.advertisement_requests') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.advertisement_requests') }}</li>
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
                                <h3 class="mb-0">{{ trans('lang.advertisement_requests') }}</h3>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.advertisement_request_table') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.advertisement_table_text') }}</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card-header">
                                    <ul class="nav nav-pills mb-3" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link new_request_list active" data-toggle="pill" href="#new_request_list" role="tab">{{ trans('lang.new_requests') }}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link updated_request_list " data-toggle="pill" href="#updated_request_list" role="tab">{{ trans('lang.update_requests') }}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link canceled_request_list" data-toggle="pill" href="#canceled_request_list" role="tab">{{ trans('lang.canceled_requests') }}</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="table-responsive m-t-10">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="new_request_list" role="tabpanel">
                                            <div class="table-responsive">
                                                <table id="newRequestTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th class="delete-all"><input type="checkbox" id="del_new"><label class="col-3 control-label" for="del_new"><a id="deleteAllNew" class="delete-btn" href="javascript:void(0)"><i class="fa fa-trash"></i> {{ trans('lang.all') }}</a></label></th>
                                                            <th>{{ trans('lang.ads_title') }}</th>
                                                            <th>{{ trans('lang.res_info') }}</th>
                                                            <th> {{ trans('lang.ads_type') }}</th>
                                                            <th> {{ trans('lang.duration') }}</th>
                                                            <th>{{ trans('lang.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="new_request_row"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="updated_request_list" role="tabpanel">
                                            <div class="table-responsive">
                                                <table id="updateRequestTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th class="delete-all"><input type="checkbox" id="del_updated"><label class="col-3 control-label" for="del_updated"><a id="deleteAllUpdated" class="delete-btn" href="javascript:void(0)"><i class="fa fa-trash"></i> {{ trans('lang.all') }}</a></label></th>
                                                            <th>{{ trans('lang.ads_title') }}</th>
                                                            <th>{{ trans('lang.res_info') }}</th>
                                                            <th> {{ trans('lang.ads_type') }}</th>
                                                            <th> {{ trans('lang.duration') }}</th>
                                                            <th>{{ trans('lang.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="update_request_row"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="canceled_request_list" role="tabpanel">
                                            <div class="table-responsive">
                                                <table id="canceledRequestTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th class="delete-all"><input type="checkbox" id="del_canceled"><label class="col-3 control-label" for="del_canceled"><a id="deleteAllCancelled" class="delete-btn" href="javascript:void(0)"><i class="fa fa-trash"></i> {{ trans('lang.all') }}</a></label></th>
                                                            <th>{{ trans('lang.ads_title') }}</th>
                                                            <th>{{ trans('lang.res_info') }}</th>
                                                            <th> {{ trans('lang.ads_type') }}</th>
                                                            <th> {{ trans('lang.duration') }}</th>
                                                            <th>{{ trans('lang.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="canceled_request_row"></tbody>
                                                </table>
                                            </div>
                                        </div>
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
            var newRequestRef = database.collection('advertisements').where('status', '==', 'pending');
            var canceledRequestRef = database.collection('advertisements').where('status', '==', 'canceled');
            var updatedRequestRef = database.collection('advertisements').where('status', '==', 'updated');

            var placeholderImage = '';
            var user_permissions = '<?php echo @session('user_permissions'); ?>';
            user_permissions = Object.values(JSON.parse(user_permissions));
            var checkDeletePermission = false;
            if ($.inArray('advertisements.delete', user_permissions) >= 0) {
                checkDeletePermission = true;
            }

            var section_id = getCookie('section_id') || '';
            
            $(document).ready(function() {
                var placeholder = database.collection('settings').doc('placeHolderImage');
                placeholder.get().then(async function(snapshotsimage) {
                    var placeholderImageData = snapshotsimage.data();
                    placeholderImage = placeholderImageData.image;
                });
                $(document).on('click', '.new_request_list', function() {
                    getNewRequests();
                });
                $(document).on('click', '.canceled_request_list', function() {
                    getCanceledRequests();
                });
                $(document).on('click', '.updated_request_list', function() {
                    getUpdatedRequests();
                });
                getNewRequests();
                function getNewRequests() {
                    var table = $('#newRequestTable').DataTable();
                    table.destroy();
                    const tableName = '#newRequestTable';
                    var refVar = newRequestRef;
                    mainDataTable(tableName, refVar);
                }
                function getUpdatedRequests() {
                    var table = $('#updateRequestTable').DataTable();
                    table.destroy();
                    const tableName = '#updateRequestTable';
                    var refVar = updatedRequestRef;
                    mainDataTable(tableName, refVar);
                }
                function getCanceledRequests() {
                    var table = $('#canceledRequestTable').DataTable();
                    table.destroy();
                    const tableName = '#canceledRequestTable';
                    var refVar = canceledRequestRef;
                    mainDataTable(tableName, refVar);
                }
                function mainDataTable(tableName, refVar) {
                    jQuery("#data-table_processing").show();
                    const table = $(tableName).DataTable({
                        pageLength: 10,
                        processing: false,
                        serverSide: true,
                        responsive: true,
                        ajax: async function(data, callback, settings) {
                            const start = data.start;
                            const length = data.length;
                            const searchValue = data.search.value.toLowerCase();
                            const orderColumnIndex = data.order[0].column;
                            const orderDirection = data.order[0].dir;
                            const orderableColumns = (checkDeletePermission) ? ['', 'title', 'rest_info', 'type', 'duration', ''] : ['title', 'rest_info', 'type', 'duration', '']; // Ensure this matches the actual column names
                            const orderByField = orderableColumns[orderColumnIndex];
                            if (searchValue.length >= 3 || searchValue.length === 0) {
                                $('#data-table_processing').show();
                            }
                            try {
                                const querySnapshot = await refVar.get();
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
                                filteredRecords = [];
                                await Promise.all(querySnapshot.docs.map(async (doc) => {
                                    let childData = doc.data();
                                    childData.id = doc.id;
                                    childData.vendorId = childData.vendorId || childData.vendor_id;
                                    if (childData.vendorId) {
                                        let vendorDetail = await getRestaurant(childData.vendorId);
                                        if (vendorDetail.section_id !== section_id) {
                                            return; 
                                        }
                                        childData.vendorTitle = vendorDetail.title;
                                        childData.vendorImage = vendorDetail.image;
                                        childData.vendorEmail = vendorDetail.email;
                                    } else {
                                        childData.vendorTitle = "-";
                                        childData.vendorImage = "";
                                        childData.vendorEmail = "-";
                                    }
                                    if (searchValue) {
                                        if (
                                            (childData.title && childData.title.toString().toLowerCase().includes(searchValue)) ||
                                            (childData.type && childData.type.toString().toLowerCase().includes(searchValue)) ||
                                            (childData.status && childData.status.toString().toLowerCase().includes(searchValue)) ||
                                            (childData.priority && childData.priority.toString().toLowerCase().includes(searchValue)) ||
                                            (childData.duration && childData.duration.toString().toLowerCase().includes(searchValue)) ||
                                            (childData.vendorTitle && childData.vendorTitle.toString().toLowerCase().includes(searchValue))
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
                                    if (orderByField === "rest_info") {
                                        aValue = a.vendorTitle ? a.vendorTitle.toLowerCase() : '';
                                        bValue = b.vendorTitle ? b.vendorTitle.toLowerCase() : '';
                                    }
                                    if (orderDirection === 'asc') {
                                        return (aValue > bValue) ? 1 : -1;
                                    } else {
                                        return (aValue < bValue) ? 1 : -1;
                                    }
                                });
                                const totalRecords = filteredRecords.length;
                                $('.total_count').text(totalRecords);
                                const paginatedRecords = filteredRecords.slice(start, start + length);
                                
                                $(function () {
                                    $('[data-toggle="tooltip"]').tooltip();
                                });

                                const formattedRecords = await Promise.all(paginatedRecords.map(async (childData) => {
                                    //return await buildHTML(childData);
                                    var id = childData.id;
                                    var route1 = '{{ route('advertisements.edit', ':id') }}';
                                    route1 = route1.replace(':id', id);
                                    var chatRoute = '{{ route('advertisement.chat', ':id') }}';
                                    chatRoute = chatRoute.replace(':id', id);
                                    var advertisementsView = '{{ route('advertisements.view', ':id') }}';
                                    advertisementsView = advertisementsView.replace(':id', id);
                                    const options = {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    };
                                    const startDate = childData.startDate.toDate().toLocaleDateString('en-US', options);
                                    const endDate = childData.endDate.toDate().toLocaleDateString('en-US', options);
                                    if(childData.vendorImage==''){
                                        var vendorImage=placeholderImage
                                    }else{
                                        var vendorImage=childData.vendorImage
                                    }
                                    records.push([
                                        '<td class="delete-all"><input type="checkbox" id="is_open_' + childData.id + '" class="is_open" dataId="' + childData.id + '"><label class="col-3 control-label"\n' + 'for="is_open_' + childData.id + '" ></label></td>',
                                        '<td><a href="' + advertisementsView + '">' + childData.title + '</a></td>',
                                        `<td><img src="${vendorImage}" style="width:50px; height:50px; border-radius:50%;" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">
                             <span>${childData.vendorTitle} <br>${childData.vendorEmail}</span></td>`,
                                        '<td>' +
                                        (childData.type === 'restaurant_promotion' ? '{{trans("lang.restaurant_promotion")}}' :
                                            childData.type === 'video_promotion' ? '{{trans("lang.video_promotion")}}' : childData.type) +
                                        '</td>',
                                        '<td>' + startDate + '-' + endDate + '</td>',
                                        '<span class="action-btn"><a href="'+chatRoute+'"><i class="fa fa-commenting" data-toggle="tooltip" data-bs-original-title="Chat"></i></a><a href="' + advertisementsView + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.view') }}"><i class="mdi mdi-eye"></i></a><a href="' + route1 + '"><i class="mdi mdi-lead-pencil" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"></i></a><?php if(in_array('category.delete', json_decode(@session('user_permissions'),true))){ ?> <a id="' + childData.id + '" name="advertisements-delete" class="delete-btn" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a><?php } ?></span>'
                                    ]);
                                }));
                                $('#data-table_processing').hide();
                                callback({
                                    draw: data.draw,
                                    recordsTotal: totalRecords,
                                    recordsFiltered: totalRecords,
                                    filteredData: filteredRecords,
                                    data: records
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
                        order: (checkDeletePermission) ? [1, 'asc'] : [0, 'asc'],
                        columnDefs: [{
                                orderable: false,
                                targets: (checkDeletePermission) ? [0, 5] : [4]
                            },
                        ],
                        "language": datatableLang,
                        initComplete: function() {
                            $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete', 'new-password').val('');
                            $('.dataTables_filter label').contents().filter(function() {
                                return this.nodeType === 3;
                            }).remove();
                        }
                    });
                }
                async function getRestaurant(vendorid) {
                    if (!vendorid) {
                        return {
                            title: "-",
                            image: "",
                            email: "-",
                            section_id: "",
                        }; // Default values
                    }
                    const vendorRef = database.collection('vendors').where('id', '==', vendorid);
                    const vendorSnapshot = await vendorRef.get();
                    const vendor_userRef = database.collection('users').where('vendorID', '==', vendorid).where('role', '==', 'vendor');
                    const vendor_userSnapshot = await vendor_userRef.get();
                    if (vendorSnapshot.empty) {
                        return {
                            title: "-"
                        }; // Default values if vendor not found
                    }
                    if (vendor_userSnapshot.empty) {
                        return {
                            image: "",
                            email: "-"
                        }; // Default values if vendor not found
                    }
                    let vendorData = {};
                    let vendor_userData = {};
                    vendorSnapshot.forEach((doc) => {
                        vendorData = doc.data();
                    });
                    vendor_userSnapshot.forEach((doc) => {
                        vendor_userData = doc.data();
                    });
                    return {
                        title: vendorData.title || "-",
                        image: vendor_userData.profilePictureURL || "",
                        email: vendor_userData.email || "-",
                        section_id: vendor_userData.sectionId || "-",
                    };
                }
            });
            $("#del_new").click(function() {
                $("#newRequestTable .is_open").prop('checked', $(this).prop('checked'));
            });
            $("#del_updated").click(function() {
                $("#updateRequestTable .is_open").prop('checked', $(this).prop('checked'));
            });
            $("#del_canceled").click(function() {
                $("#canceledRequestTable .is_open").prop('checked', $(this).prop('checked'));
            });
            $("#deleteAllNew").click(async function() {
                if ($('#newRequestTable .is_open:checked').length) {
                    if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                        jQuery("#data-table_processing").show();
                        $('#newRequestTable .is_open:checked').each(async function() {
                            var dataId = $(this).attr('dataId');
                            await checkCopyAndDelete(dataId);
                        });
                    }
                } else {
                    alert("{{ trans('lang.select_delete_alert') }}");
                }
            });
            $("#deleteAllUpdated").click(async function() {
                if ($('#updateRequestTable .is_open:checked').length) {
                    if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                        jQuery("#data-table_processing").show();
                        $('#updateRequestTable .is_open:checked').each(async function() {
                            var dataId = $(this).attr('dataId');
                            await checkCopyAndDelete(dataId);
                        });
                    }
                } else {
                    alert("{{ trans('lang.select_delete_alert') }}");
                }
            });
            $("#deleteAllCancelled").click(async function() {
                if ($('#canceledRequestTable .is_open:checked').length) {
                    if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                        jQuery("#data-table_processing").show();
                        $('#canceledRequestTable .is_open:checked').each(async function() {
                            var dataId = $(this).attr('dataId');
                            await checkCopyAndDelete(dataId);
                        });
                    }
                } else {
                    alert("{{ trans('lang.select_delete_alert') }}");
                }
            });
            async function checkCopyAndDelete(dataId) {
                await database.collection('advertisements').doc(dataId).get().then(async function(snapshots) {
                    var data = snapshots.data();
                    if (data.type == 'video_promotion') {
                        var checkVideoSize = await database.collection('advertisements').where('video', '==', data.video).get();
                        var videoSize = checkVideoSize.size;
                        if (videoSize > 1) {
                            await deleteDocumentWithImage('advertisements', dataId);
                        } else {
                            await deleteDocumentWithImage('advertisements', dataId, ['video']);
                        }
                    } else {
                        var checkprofileSize = await database.collection('advertisements').where('profileImage', '==', data.profileImage).get();
                        var profileSize = checkprofileSize.size;
                        var checkCoverSize = await database.collection('advertisements').where('coverImage', '==', data.coverImage).get();
                        var coverSize = checkCoverSize.size;
                        let fieldsToDelete = [];
                        if (profileSize === 1) {
                            fieldsToDelete.push('profileImage');
                        }
                        if (coverSize === 1) {
                            fieldsToDelete.push('coverImage');
                        }
                        if (fieldsToDelete.length > 0) {
                            await deleteDocumentWithImage('advertisements', dataId, fieldsToDelete);
                        } else {
                            await deleteDocumentWithImage('advertisements', dataId);
                        }
                    }
                })
                window.location.reload();
            }
            $(document).on("click", "a[name='advertisements-delete']", async function(e) {
                var id = this.id;
                jQuery("#data-table_processing").show();
                await database.collection('advertisements').doc(id).get().then(async function(snapshots) {
                    var data = snapshots.data();
                    if (data.type == 'video_promotion') {
                        var checkVideoSize = await database.collection('advertisements').where('video', '==', data.video).get();
                        var videoSize = checkVideoSize.size;
                        if (videoSize > 1) {
                            await deleteDocumentWithImage('advertisements', id);
                        } else {
                            await deleteDocumentWithImage('advertisements', id, ['video']);
                        }
                    } else {
                        var checkprofileSize = await database.collection('advertisements').where('profileImage', '==', data.profileImage).get();
                        var profileSize = checkprofileSize.size;
                        var checkCoverSize = await database.collection('advertisements').where('coverImage', '==', data.coverImage).get();
                        var coverSize = checkCoverSize.size;
                        let fieldsToDelete = [];
                        if (profileSize === 1) {
                            fieldsToDelete.push('profileImage');
                        }
                        if (coverSize === 1) {
                            fieldsToDelete.push('coverImage');
                        }
                        if (fieldsToDelete.length > 0) {
                            await deleteDocumentWithImage('advertisements', id, fieldsToDelete);
                        } else {
                            await deleteDocumentWithImage('advertisements', id);
                        }
                    }
                })
                window.location.href = '{{ route('advertisements') }}';
            });

        </script>
        
    @endsection
