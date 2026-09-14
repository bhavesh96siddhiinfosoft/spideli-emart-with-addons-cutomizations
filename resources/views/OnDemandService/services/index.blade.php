@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.ondemand_plural')}} - {{trans('lang.service_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.service_plural')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>
    <div class="container-fluid">
       <div class="admin-top-section"> 
        <div class="row">
            <div class="col-12">
            @if($id!='')
                        <div class="resttab-sec">

                            <div class="menu-tab tabDiv">
                                <ul>
                                    <li><a href="{{route('providers.view', $id)}}"><img src="{{ asset('images/provider.png') }}"> {{trans('lang.tab_basic')}}</a>
                                    </li>
                                    <li class="active"><a
                                                href="{{route('ondemand.services.index', $id)}}"><img src="{{ asset('images/service.png') }}"> {{trans('lang.services')}}</a>
                                    </li>
                                    <li>
                                    <li><a href="{{route('ondemand.workers.index', $id)}}"><img src="{{ asset('images/worker.png') }}"> {{trans('lang.workers')}}</a>
                                    </li>
                                    <li>
                                    <li>
                                        <a href="{{route('ondemand.bookings.index',$id)}}"><img src="{{ asset('images/booking.png') }}"> {{trans('lang.booking_plural')}}</a>
                                    </li>
                                    <li>
                                    <li><a href="{{route('ondemand.coupons', $id)}}"><img src="{{ asset('images/coupon.png') }}"> {{trans('lang.coupon_plural')}}</a>
                                    </li>
                                    <li>
                                        <a href="{{route('providerPayouts.payout', $id)}}"><img src="{{ asset('images/payment.png') }}"> {{trans('lang.tab_payouts')}}</a>
                                    </li>
                                    <li>
                                        <a href="{{route('payoutRequests.providers', $id)}}"><img src="{{ asset('images/payment.png') }}"> {{trans('lang.tab_payout_request')}}</a>
                                    </li>
                                    <li>
                                        <a href="{{route('users.walletstransaction',$id)}}"
                                           class="wallet_transaction"><img src="{{ asset('images/wallet.png') }}"> {{trans('lang.wallet_transaction')}}</a>
                                    </li>
                                    <?php 
                    
                                    $subscription =  route("subscription.subscriptionPlanHistory", ":id");
                                    $subscription =  str_replace(":id", "providerID=" . $id, $subscription);
                                    ?>
                                    <li> 
                                        <a href="{{ $subscription }}"><img src="{{ asset('images/subscription.png') }}"> {{trans('lang.subscription_history')}}</a>
                                    </li>
                                </ul>
                            </div>

                        </div>
                    @endif
                <div class="d-flex top-title-section pb-4 justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/service.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.service_plural')}}</h3>
                        <span class="counter ml-3 total_count"></span>
                    </div>

                    <div class="d-flex top-title-right align-self-center">

                            <div class="select-box pl-3">
                                <select class="form-control status_selector filteredRecords">
                                    <option value="">{{trans("lang.status")}}</option>
                                    <option value="active"  >{{trans("lang.active")}}</option>
                                    <option value="inactive"  >{{trans("lang.in_active")}}</option>
                                </select>
                            </div>

                            <div class="select-box pl-3">
                                <select class="form-control category_selector">
                                    <option value=""  selected>{{trans("lang.category_plural")}}</option>
                                </select>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.service_plural')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.service_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                    @if($id=='')
                        <a class="btn-primary btn rounded-full" href="{!! route('ondemand.services.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.service_create')}}</a>
                    @else
                    <a class="btn-primary btn rounded-full" href="{!! route('ondemand.services.create','id='.$id) !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.service_create')}}</a>
                    @endif
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="serviceTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                <?php if (in_array('ondemand.services.delete', json_decode(@session('user_permissions'),true))) { ?>
                                        <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                    class="col-3 control-label" for="is_active"
                                            ><a id="deleteAll" class="do_not_delete"
                                                href="javascript:void(0)"><i
                                                            class="fa fa-trash"></i> {{trans('lang.all')}}</a></label>
                                        </th>
                                        <?php } ?>
                                        <th>{{trans('lang.name')}}</th>
                                        <th>{{trans('lang.ondemand_category')}}</th>
                                       @unless($id != '')
                                        <th>{{trans('lang.provider')}}</th>
                                        @endunless
                                        <th>{{trans('lang.price')}}</th>
                                        <th>{{trans('lang.publish')}}</th>
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

        var section_id = getCookie('section_id') || '';
        var id = "{{$id}}";
        var user_permissions = '<?php echo @session('user_permissions') ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkDeletePermission = false;
        if ($.inArray('ondemand.services.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }

        var database = firebase.firestore();
        
        if (id != '') {
            var wallet_route = "{{route('users.walletstransaction','id')}}";
            $(".wallet_transaction").attr("href", wallet_route.replace('id', 'providerID=' + id));
            $('.tabDiv').show();
            var ref = regionScoped(database.collection('providers_services').where('sectionId', '==', section_id).where('author', '==', id)).orderBy('createdAt', 'desc');

        } else {
            $('.tabDiv').show();
            var ref = regionScoped(database.collection('providers_services').where('sectionId', '==', section_id)).orderBy('createdAt', 'desc');

        }       

        var currentCurrency = '';
        var currencyAtRight = false;
        var decimal_degits = 0;

        var refCurrency = regionCurrencyRef();
        refCurrency.get().then(async function (snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;

            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        });

        var ctegoryRef = database.collection('provider_categories');
        var ref_sections = database.collection('sections').where('isActive', '==', true).orderBy('order');
        var refProvider = database.collection('users');

        database.collection('provider_categories').where('sectionId','==',section_id).get().then(async function(snapshots) {
            snapshots.docs.forEach((listval) => {
                var data=listval.data();
                $('.category_selector').append($("<option></option>")
                    .attr("value",data.id)
                    .text(data.title));
            })
        });

        var initialRef=ref;
        $('select').change(async function() {
            var status = $('.status_selector').val();
            var category = $('.category_selector').val();
            refData = initialRef;
        
            if (status) {
                refData = (status == "active") ? refData.where('publish', '==', true) : refData.where('publish', '==', false);
            }
            if (category) {
                refData=refData.where('categoryId','==',category);
            }
            ref=refData;
            $('#serviceTable').DataTable().ajax.reload(); 
        });

        $(document).ready(function () {

            $('.status_selector').select2({
                placeholder: '{{trans("lang.status")}}',  
                minimumResultsForSearch: Infinity,
                allowClear: true 
            });

            $('.category_selector').select2({
                placeholder: "{{trans('lang.category')}}",  
                minimumResultsForSearch: Infinity,
                allowClear: true  
            });

            $('select').on("select2:unselecting", function(e) {
                var self = $(this);
                setTimeout(function() {
                    self.select2('close');
                }, 0);
            });


            jQuery("#data-table_processing").show();
            if (id !== '') {
                getProviderNameForFilter(id);
            }

            var fieldConfig = {
                columns: [
                    { key: 'title', header: "{{ trans('lang.name')}}" }, 
                    { key: 'categoryName', header: "{{ trans('lang.ondemand_category')}}" }, 
                    { key: 'sectionName', header: "{{trans('lang.section')}}" },
                    { key: 'providerName', header: "{{trans('lang.provider')}}" },
                    { key: 'finalPrice', header: "{{trans('lang.price')}}" },
                    
                ],
                fileName: "{{trans('lang.service_table')}}",
            };

            const table = $('#serviceTable').DataTable({
                pageLength: 10, // Number of rows per page
                processing: false, // Show processing indicator
                serverSide: true, // Enable server-side processing
                responsive: true,
                ajax: async function (data, callback, settings) {
                    const start = data.start;
                    const length = data.length;
                    const searchValue = data.search.value.toLowerCase();
                    const orderColumnIndex = data.order[0].column;
                    const orderDirection = data.order[0].dir;
                    
                    let orderableColumns = [];

                    if (checkDeletePermission) {
                        orderableColumns.push(''); 
                    }

                    // Name
                    orderableColumns.push('title');

                    // Category
                    orderableColumns.push('categoryName');

                    // Provider column only when id == ''
                    if (id === '') {
                        orderableColumns.push('providerName');
                    }

                    // Price
                    orderableColumns.push('finalPrice');

                    // Publish + Action (not orderable)
                    orderableColumns.push('');
                    orderableColumns.push('');

                    const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                    if (searchValue.length >= 3 || searchValue.length === 0) {
                        $('#data-table_processing').show();
                    }
                    await ref.get().then(async function (querySnapshot) {
                        if (querySnapshot.empty) {
                            $('.total_count').text(0);
                            console.error("No data found in Firestore.");
                            $('#data-table_processing').hide(); // Hide loader
                            callback({
                                draw: data.draw,
                                recordsTotal: 0,
                                recordsFiltered: 0,
                                data: [] // No data
                            });
                            return;
                        }

                        let records = [];
                        let filteredRecords = [];
                        let sectionNames = {};
                        let categoryName = {};
                        let providerNames = {};
                        // Fetch section names
                        const sectionDocs = await ref_sections.get();
                        sectionDocs.forEach(doc => {
                            sectionNames[doc.id] = doc.data().name;
                        });

                        const categoryDocs = await ctegoryRef.get();
                        categoryDocs.forEach(doc => {
                            categoryName[doc.id] = doc.data().title;
                        });

                        const providerDocs = await refProvider.get();
                        providerDocs.forEach(doc => {
                            providerNames[doc.id] = doc.data().firstName + ' ' + doc.data().lastName;
                        });
                        await Promise.all(querySnapshot.docs.map(async (doc) => {
                            let childData = doc.data();
                            childData.id = doc.id; // Ensure the document ID is included in the data              
                            childData.sectionName = sectionNames[childData.sectionId] || '';
                            childData.categoryName = categoryName[childData.categoryId] || '';
                            if(childData.hasOwnProperty('author')){
                                childData.providerName = providerNames[childData.author] || '';
                            }else{
                                childData.providerName = '';
                            }

                            if(childData.hasOwnProperty('disPrice') && childData.disPrice != '0'){
                                childData.finalPrice = childData.disPrice;
                            }else{
                                childData.finalPrice = childData.price;
                            }
                            if (searchValue) {
                            
                                if (
                                    (childData.title && childData.title.toLowerCase().toString().includes(searchValue)) ||
                                    (childData.categoryName && childData.categoryName.toLowerCase().toString().includes(searchValue)) ||
                                    (childData.sectionName && childData.sectionName.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.providerName && childData.providerName.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.finalPrice && childData.finalPrice.toString().toLowerCase().includes(searchValue))

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
                        
                            if(orderByField === 'finalPrice') {
                                aValue = a[orderByField] ? parseFloat(a[orderByField]) : 0.0;
                                bValue = b[orderByField] ? parseFloat(b[orderByField]) : 0.0;

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

                        await Promise.all(paginatedRecords.map(async (childData) => {
                            var getData = await buildHTML(childData);
                            records.push(getData);
                        }));
                        $(function () {
                            $('[data-toggle="tooltip"]').tooltip();
                        });
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: totalRecords, // Total number of records in Firestore
                            recordsFiltered: totalRecords, // Number of records after filtering (if any)
                            filteredData: filteredRecords,
                            data: records // The actual data to display in the table
                        });
                    }).catch(function (error) {
                        console.error("Error fetching data from Firestore:", error);
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: [] // No data due to error
                        });
                    });
                },
               
                order: (function() {
                    const show = (id === '');
                    const titleColIndex = checkDeletePermission 
                        ? (show ? 2 : 1)  
                        : (show ? 1 : 0);  
                    return [[titleColIndex, 'asc']];
                })(),

                columnDefs: (function() {
                    const show = (id === '');
                    let targets = [];

                    if (checkDeletePermission) {
                        targets.push(0); // Checkbox always non-sortable
                    }

                    const publishCol = checkDeletePermission 
                        ? (show ? 5 : 4)   // Publish column index
                        : (show ? 4 : 3);

                    const actionsCol = publishCol + 1;

                    targets.push(publishCol);  // Publish toggle
                    targets.push(actionsCol);  // Actions

                    return [{ orderable: false, targets: targets }];
                })(),
               "language": datatableLang,
                dom: 'lfrtipB',
                buttons: [
                        {
                            extend: 'collection',
                            text: '<i class="mdi mdi-cloud-download"></i> {{trans('lang.export_as')}}',
                            className: 'btn btn-info',
                            buttons: [
                                {
                                    extend: 'excelHtml5',
                                    text: '{{trans('lang.export_excel')}}',
                                    action: function (e, dt, button, config) {
                                        exportData(dt, 'excel',fieldConfig);
                                    }
                                },
                                {
                                    extend: 'pdfHtml5',
                                    text: '{{trans('lang.export_pdf')}}',
                                    action: function (e, dt, button, config) {
                                        exportData(dt, 'pdf',fieldConfig);
                                    }
                                },   
                                {
                                    extend: 'csvHtml5',
                                    text: '{{trans('lang.export_csv')}}',
                                    action: function (e, dt, button, config) {
                                        exportData(dt, 'csv',fieldConfig);
                                    }
                                }
                            ]
                        }
                ],
                initComplete: function() {
                    $(".dataTables_filter").append($(".dt-buttons").detach());
                    $('.dataTables_filter input').attr('placeholder', 'Search here...').attr('autocomplete','new-password').val('');
                    $('.dataTables_filter label').contents().filter(function() {
                        return this.nodeType === 3; 
                    }).remove();
                }
            });

            function debounce(func, wait) {
                let timeout;
                const context = this;
                return function(...args) {
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
var id = "{{$id}}";
var showProviderColumn = (id === '');
        async function buildHTML(val) {

            var html = [];

            newdate = '';
            var id = val.id;
            var categoryId = val.categoryId;
            var idOfProviderDetailPage = "{{$id}}";
            var route1 = '{{route("ondemand.services.edit",":id")}}';
            var route2 = '{{route("ondemandcategory.edit",":id")}}';
            if (idOfProviderDetailPage != '') {
                route1 = route1.replace(':id', val.id + "?id=" + idOfProviderDetailPage);
            } else {
                route1 = route1.replace(':id', id);
            }

            route2 = route2.replace(':id', categoryId);

            if (checkDeletePermission) {
                html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' + 'for="is_open_' + id + '" ></label></td>'); 
            }

            html.push('<a href="' + route1 + '">' + val.title + '</a>');

            html.push('<a href="' + route2 + '">' + val.categoryName + '</a>');
          
          
            if (val.hasOwnProperty("author")) {
                var providerView = '{{route("providers.view",":id")}}';
                providerView = providerView.replace(':id', val.author);
                
                // if (val.providerName == "") {
                //     providerView = "javascript:void(0)";
                //     providerName = "{{trans('lang.unknown')}}"
                // }
                // html.push('<a href="' + providerView + '">' + val.providerName + '</a>');
                if (showProviderColumn) {
        if (val.author && val.providerName) {
            var provRoute = '{{ route("providers.view", ":id") }}'.replace(':id', val.author);
            html.push('<td><a href="' + provRoute + '">' + val.providerName + '</a></td>');
        } else {
            html.push('<td>-</td>');
        }
    }
            } else {
                html.push('<td></td>');
            }
            
            if (val.disPrice == "0"){
                if (val.priceUnit == "Hourly") {
                    if (currencyAtRight) {
                        html.push('<td data-html="true" data-order="' + val.price + '">' + parseFloat(val.price).toFixed(decimal_degits) + '' + currentCurrency + '/hr</td>');
                    }else {
                        html.push('<td data-html="true" data-order="' + val.price + '">' + currentCurrency + parseFloat(val.price).toFixed(decimal_degits) + '/hr</td>');
                    }
                } else {
                    if (currencyAtRight) {
                        html.push('<td data-html="true" data-order="' + val.price + '">' + parseFloat(val.price).toFixed(decimal_degits) +  '' + currentCurrency + '</td>');
                    }else {
                        html.push('<td data-html="true" data-order="' + val.price + '">' + currentCurrency + parseFloat(val.price).toFixed(decimal_degits) + '</td>');
                    }
                }
            }else {
                if (val.priceUnit == "Hourly") {
                    if (currencyAtRight) {
                        html.push('<td data-html="true" data-order="' + val.disPrice + '">' + parseFloat(val.disPrice).toFixed(decimal_degits) + '' + currentCurrency + '/hr  <s>' + parseFloat(val.price).toFixed(decimal_degits) + '' + currentCurrency + '/hr</s></td>');
                    } else {
                        html.push('<td data-html="true" data-order="' + val.disPrice + '">' + '' + currentCurrency + parseFloat(val.disPrice).toFixed(decimal_degits) + '/hr  <s>' + currentCurrency + '' + parseFloat(val.price).toFixed(decimal_degits) + '/hr</s> </td>');
                    }
                } else {
                    if (currencyAtRight) {
                        html.push('<td data-html="true" data-order="' + val.disPrice + '">' + parseFloat(val.disPrice).toFixed(decimal_degits) + '' + currentCurrency + '  <s>' + parseFloat(val.price).toFixed(decimal_degits) + '' + currentCurrency + '</s></td>');
                    } else {
                        html.push('<td data-html="true" data-order="' + val.disPrice + '">' + '' + currentCurrency + parseFloat(val.disPrice).toFixed(decimal_degits) + ' <s>' + currentCurrency + '' + parseFloat(val.price).toFixed(decimal_degits) + '</s> </td>');
                    }
                }
            }


            if (val.publish) {
                html.push('<label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isActive"><span class="slider round"></span></label>');
            } else {
                html.push('<label class="switch"><input type="checkbox" id="' + val.id + '" name="isActive"><span class="slider round"></span></label>');
            }

            var actionHtml = '';
            actionHtml = actionHtml + '<span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" title="{{trans("lang.edit")}}"><i class="mdi mdi-lead-pencil"></i></a>';
            if (checkDeletePermission) {
                actionHtml = actionHtml + '<a id="' + val.id + '" name="service-delete" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" title="{{trans("lang.delete")}}"><i class="mdi mdi-delete"></i></a>';
            }
            actionHtml = actionHtml + '</span>';
            html.push(actionHtml);
            return html;
        }       

        $(document).on("click", "input[name='isActive']", function (e) {
            var ischeck = $(this).is(':checked');
            var id = this.id;
            var publish = ischeck ? true : false;
            database.collection('providers_services').doc(id).update({
                'publish': publish
            });
        });

        $(document).on("click", "a[name='service-delete']", async function (e) {
            var id = this.id;
            await deleteDocumentWithImage('providers_services',id,'','photos');
            deleteServiceData(id);
            window.location.reload();
        });

        $("#is_active").click(function () {
            $("#serviceTable .is_open").prop('checked', $(this).prop('checked'));
        });

        $("#deleteAll").click(function () {
            if ($('#serviceTable .is_open:checked').length) {
                if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                    jQuery("#data-table_processing").show();
                    $('#serviceTable .is_open:checked').each(async function () {
                        var dataId = $(this).attr('dataId');
                        await deleteDocumentWithImage('providers_services',dataId,'','photos');
                        deleteServiceData(dataId);
                        window.location.reload();
                    });
                }
            } else {
                alert("{{trans('lang.select_delete_alert')}}");
            }
        });

        async function getProviderNameForFilter(providerId) {
            await database.collection('users').where('id', '==', providerId).get().then(async function (snapshots) {
                var providerData = snapshots.docs[0].data();
                providerName = providerData.firstName + ' ' + providerData.lastName;
                $('.PageTitle').html("{{trans('lang.service_plural')}} - " + providerName);
            });

        }
        async function deleteServiceData(serviceId){
            await database.collection('favorite_service').where('service_id', '==', serviceId).get().then(async function(snapshotsItem) {

            if (snapshotsItem.docs.length > 0) {
                snapshotsItem.docs.forEach((temData) => {
                    var item_data = temData.data();

                    database.collection('favorite_service').doc(item_data.id).delete().then(function() {

                    });
                });
            }

        });
        }
    </script>


@endsection