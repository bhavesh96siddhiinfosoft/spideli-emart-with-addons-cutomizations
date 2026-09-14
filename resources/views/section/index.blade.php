@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{trans('lang.section_plural')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.section_table')}}</li>
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
                            <span class="icon mr-3"><img src="{{ asset('images/section_image.png') }}"></span>
                            <h3 class="mb-0">{{trans('lang.section_plural')}}</h3>
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
                        <h3 class="text-dark-2 mb-2 h4">{{trans('lang.section_table')}}</h3>
                        <p class="mb-0 text-dark-2">{{trans('lang.section_table_text')}}</p>
                    </div>
                    <div class="card-header-right d-flex align-items-center">
                        <div class="card-header-btn mr-3"> 
                            <a class="btn-primary btn rounded-full" href="{!! route('section.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.section_create')}}</a>
                        </div>
                    </div>                
                    </div>
                    <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <table id="sectionTable"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">
                                    <thead>
                                    <tr>
                                        <th>Sort</th>
                                        <th>{{trans('lang.section_info')}}</th>
                                        <th>{{trans('lang.service_type')}}</th>
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
        var ref = database.collection('sections').orderBy('order');
        var append_list = '';
        var placeholderImage = '';
        var user_permissions = '<?php echo @session('user_permissions') ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkDeletePermission = false;
        if ($.inArray('section.service.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }

        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function (snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        });

        $(document).ready(function () {

            jQuery("#data-table_processing").show();
            
            append_list = document.getElementById('append_list1');
            append_list.innerHTML = '';
            ref.get().then(async function (snapshots) {
                html = '';
                if (snapshots.docs.length > 0) {
                    $('.total_count').text(snapshots.docs.length); 
                }else{
                    $('.total_count').text(0);                 
                }
                html = await buildHTML(snapshots);
                if (html != '') {
                    append_list.innerHTML = html;
                }
                jQuery("#data-table_processing").hide();
                const table = $('#sectionTable').DataTable({
                    ordering: false,
                    order: [[0, "asc"]],
                    columnDefs: [
                        {
                            orderable: false,
                            targets: [2, 3]
                        },
                    ],
                    "language": datatableLang,
                    responsive: true
                });
                table.on('search.dt', function() {
                    var filteredCount = table.rows({ search: 'applied' }).count();
                    $('.total_count').text(filteredCount);  // Update count
                });
            });
            $(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });
            
            $('#append_list1').sortable({
                handle: '.drag-handle',
                helper: fixWidthHelper,
                update: function (event, ui) {
                    updateOrder();
                }
            }).disableSelection();
        });

        function fixWidthHelper(e, ui) {
            ui.children().each(function () {
                $(this).width($(this).width());
            });
            return ui;
        }
        
        function updateOrder() {
            $('#data-table_processing').show();
            var order = 1;
            var promises = [];
            $('#append_list1 tr').each(function () {
                var id = $(this).attr('data-id');
                promises.push(
                    database.collection('sections').doc(id).update({ order: order })
                );
                order++;
            });
            Promise.all(promises).then(() => {
                $('#data-table_processing').hide();
            });
        }

        async function buildHTML(snapshots) {
            var html = '';
            await Promise.all(snapshots.docs.map(async (listval) => {
                var val = listval.data();
                if (val.title != '') {
                    var getData = await getListData(val);
                    html += getData;
                }
            }));
            return html;
        }
        
        async function getListData(val) {
            var html = '';
            html = html + '<tr data-id="' + val.id + '">';
            html += '<td class="drag-handle" style="cursor:move"><i class="mdi mdi-menu"></i></td>';
            newdate = '';
            var id = val.id;
            var vendorUserId = val.author;
            var route1 = '{{route("section.edit", ":id")}}';
            route1 = route1.replace(':id', id);
            if (val.sectionImage != '') {
                if (val.sectionImage) {
                    photo = val.sectionImage;
                } else {
                    photo = placeholderImage;
                }
                html = html + '<td><img alt="" width="100%" style="width:70px;height:70px;" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image"> <span data-url="' + route1 + '" class="redirecttopage"><a href="' + route1 + '">' + val.name + '</a></span></td>';
            } else {
                html = html + '<td><img alt="" width="100%" style="width:70px;height:70px;" src="' + placeholderImage + '" alt="image"> <span data-url="' + route1 + '" class="redirecttopage"><a href="' + route1 + '">' + val.name + '</a></span></td>';
            }
            html = html + '<td data-url="' + route1 + '" class="redirecttopage">' + val.serviceType + '</td>';
            if (val.isActive) {
                html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';
            } else {
                html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isSwitch"><span class="slider round"></span></label></td>';
            }
            html = html + '<td><span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
            if (checkDeletePermission) {
                html = html + '<a id="' + val.id + '" name="section-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a>';
            }
            html = html + '</span></td>';
            html = html + '</tr>';
            return html;
        }

        $(document).on("click", "input[name='isSwitch']", function (e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            if (ischeck) {
                database.collection('sections').doc(id).update({'isActive': true}).then(function (result) {
                });
            } else {
                database.collection('sections').doc(id).update({'isActive': false}).then(function (result) {
                });
            }
        });

        $(document).on("click", "a[name='section-delete']", async function (e) {
            var id = this.id;
            var all_delete_alert = '{{trans("lang.all_delete_alert")}}';
            if (confirm(all_delete_alert)) {
                jQuery("#data-table_processing").show();
                deleteDocumentWithImage('sections',id,'sectionImage')
                .then(() => {
                    return deleteAllSectionData(id);
                })
                .then(result => {
                    setTimeout(function () {
                        window.location.reload();
                    }, 7000);
                })
                .catch(error => {
                    console.error("Error occurred:", error);
                });
            }
        });

        async function deleteAllSectionData(sectionId) {
            await database.collection('banner_items').where('sectionId', '==', sectionId).get().then(async function (bannersnapshots) {
                if (bannersnapshots.docs.length > 0) {
                    for (const temData of bannersnapshots.docs) {
                        await deleteDocumentWithImage('banner_items',temData.id,'photo');
                    }
                }
            });
            await database.collection('subscription_plans').where('sectionId', '==', sectionId).get().then(async function (bannersnapshots) {
                if (bannersnapshots.docs.length > 0) {
                    for (const temData of bannersnapshots.docs) {
                        await deleteDocumentWithImage('subscription_plans',temData.id);
                    }
                }
            });
            await database.collection('coupons').where('section_id', '==', sectionId).get().then(async function (couponssnapshots) {
                if (couponssnapshots.docs.length > 0) {
                    for (const temData of couponssnapshots.docs) {
                        var item_data = temData.data();
                        await deleteDocumentWithImage('coupons',item_data.id,'image');
                    }
                }
            });
            await database.collection('favorite_item').where('section_id', '==', sectionId).get().then(async function (favitemsnapshots) {
                if (favitemsnapshots.docs.length > 0) {
                    favitemsnapshots.docs.forEach((val) => {
                        var item_data = val.data();
                        database.collection('favorite_item').doc(item_data.id).delete().then(function () {
                        });
                    });
                }
            });
            await database.collection('favorite_vendor').where('section_id', '==', sectionId).get().then(async function (favvendorsnapshots) {
                if (favvendorsnapshots.docs.length > 0) {
                    favvendorsnapshots.docs.forEach((val) => {
                        var item_data = val.data();
                        database.collection('favorite_vendor').doc(item_data.id).delete().then(function () {
                        });
                    });
                }
            });
            await database.collection('brands').where('sectionId', '==', sectionId).get().then(async function (brandsnapshots) {
                if (brandsnapshots.docs.length > 0) {
                    for (const temData of brandsnapshots.docs) {
                        var item_data = temData.data();
                        await deleteDocumentWithImage('brands',item_data.id,'photo');
                    }
                }
            });
            await database.collection('vendor_categories').where('section_id', '==', sectionId).get().then(async function (vendorcatsnapshots) {
                if (vendorcatsnapshots.docs.length > 0) {
                    for (const temData of vendorcatsnapshots.docs) {
                        var item_data = temData.data();
                        await deleteDocumentWithImage('vendor_categories',item_data.id,'photo');
                    }
                }
            });
            await database.collection('vendor_products').where('section_id', '==', sectionId).get().then(async function (vendorproductsanpshots) {
                if (vendorproductsanpshots.docs.length > 0) {
                    for (const temData of vendorproductsanpshots.docs) {
                        var item_data = temData.data();
                        await deleteDocumentWithImage('vendor_products',item_data.id,'photo','photos');
                    }
                }
            });
            await database.collection('vendors').where('section_id', '==', sectionId).get().then(async function (vendorsnapshots) {
                if (vendorsnapshots.docs.length > 0) {
                    for (const temData of vendorsnapshots.docs) {
                        var item_data = temData.data();
                        var vendorID = item_data.id;
                            const snaps = await database.collection('vendors').where('id', '==', vendorID).get();
                            await deleteDocumentWithImage('vendors',snaps.docs[0].id,'photo','photos','authorProfilePic');
                        database.collection('vendors').doc(item_data.id).delete().then(async function () {
                            await database.collection('order_transactions').where('vendorId', '==', vendorID).get().then(async function (ordertransactionsanpshots) {
                                if (ordertransactionsanpshots.docs.length > 0) {
                                    ordertransactionsanpshots.docs.forEach((val) => {
                                        var item_data = val.data();
                                        database.collection('order_transactions').doc(item_data.id).delete().then(function () {
                                        });
                                    });
                                }
                            });
                            await database.collection('payouts').where('vendorID', '==', vendorID).get().then(async function (payoutssanpshots) {
                                if (payoutssanpshots.docs.length > 0) {
                                    payoutssanpshots.docs.forEach((val) => {
                                        var item_data = val.data();
                                        database.collection('payouts').doc(item_data.id).delete().then(function () {
                                        });
                                    });
                                }
                            });
                            await database.collection('users').where('vendorID', '==', vendorID).get().then(async function (userssanpshots) {
                                if (userssanpshots.docs.length > 0) {
                                    var projectId = '<?php echo env('FIREBASE_PROJECT_ID') ?>';
                                    userssanpshots.docs.forEach((val) => {
                                        var item_data = val.data();
                                        var dataObject = {
                                            "data": {
                                                "uid": item_data.id
                                            }
                                        };
                                        jQuery.ajax({
                                            url: 'https://us-central1-' + projectId + '.cloudfunctions.net/deleteUser',
                                            method: 'POST',
                                            contentType: "application/json; charset=utf-8",
                                            data: JSON.stringify(dataObject),
                                            success: function (data) {
                                                console.log('Delete user success:', data.result);
                                                database.collection('users').doc(item_data.id).delete().then(function () {
                                                });
                                            },
                                            error: function (xhr, status, error) {
                                                var responseText = JSON.parse(xhr.responseText);
                                                console.log('Delete user error:', responseText.error);
                                            }
                                        });
                                    });
                                }
                            });
                            await database.collection('vendor_orders').where('vendorID', '==', vendorID).get().then(async function (vendorordersanpshots) {
                                if (vendorordersanpshots.docs.length > 0) {
                                    vendorordersanpshots.docs.forEach((val) => {
                                        var item_data = val.data();
                                        database.collection('vendor_orders').doc(item_data.id).delete().then(function () {
                                        });
                                    });
                                }
                            });
                            await database.collection('vendor_products').where('vendorID', '==', vendorID).get().then(async function (vendorproductsanpshots) {
                                if (vendorproductsanpshots.docs.length > 0) {
                                    for (const temData of vendorproductsanpshots.docs) {
                                        var item_data = temData.data();
                                        await deleteDocumentWithImage('vendor_products',item_data.id,'photo','photos');
                                    }
                                }
                            });
                        });
                    }
                }
            });
            //Delete ondemand service data
            await database.collection('provider_categories').where('sectionId', '==', sectionId).get().then(async function (snapshots) {
                if (snapshots.docs.length > 0) {
                    for (const temData of snapshots.docs) {
                        var item_data = temData.data();
                        await deleteDocumentWithImage('provider_categories',item_data.id,'image');
                    }
                }
            });
            await database.collection('provider_orders').where('sectionId', '==', sectionId).get().then(async function (snapshots) {
                if (snapshots.docs.length > 0) {
                    snapshots.docs.forEach((val) => {
                        var item_data = val.data();
                        database.collection('provider_orders').doc(item_data.id).delete().then(function () {
                        });
                    });
                }
            });
            await database.collection('providers_coupons').where('sectionId', '==', sectionId).get().then(async function (snapshots) {
                if (snapshots.docs.length > 0) {
                    for (const temData of snapshots.docs) {
                        var item_data = temData.data();
                        await deleteDocumentWithImage('providers_coupons',item_data.id,'image');
                    }
                }
            });
            await database.collection('providers_services').where('sectionId', '==', sectionId).get().then(async function (snapshots) {
                if (snapshots.docs.length > 0) {
                    for (const temData of snapshots.docs) {
                        var item_data = temData.data();
                        await deleteDocumentWithImage('providers_services',item_data.id,'','photos');
                    }
                }
            });
            await database.collection('favorite_provider').where('section_id', '==', sectionId).get().then(async function (snapshots) {
                if (snapshots.docs.length > 0) {
                    snapshots.docs.forEach((val) => {
                        var item_data = val.data();
                        database.collection('favorite_provider').doc(item_data.id).delete();
                    });
                }
            });
            await database.collection('favorite_service').where('section_id', '==', sectionId).get().then(async function (snapshots) {
                if (snapshots.docs.length > 0) {
                    snapshots.docs.forEach((val) => {
                        var item_data = val.data();
                        database.collection('favorite_service').doc(item_data.id).delete();
                    });
                }
            });
        }

    </script>

@endsection