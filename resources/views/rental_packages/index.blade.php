@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor restaurantTitle">{{ trans('lang.rental_packages') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ trans('lang.rental_packages') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="admin-top-section">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex top-title-section pb-4 justify-content-between">
                            <div class="d-flex top-title-left align-items-center">
                                <span class="icon mr-3"><img src="{{ asset('images/subscription.png') }}"></span>
                                <h3 class="mb-0">{{ trans('lang.rental_packages') }}</h3>
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
                                    <h3 class="text-dark-2 mb-2 h4">{{ trans('lang.rental_packages') }}</h3>
                                    <p class="mb-0 text-dark-2">{{ trans('lang.rental_packages_text') }}</p>
                                </div>
                                <div class="card-header-right d-flex align-items-center">
                                    <div class="card-header-btn mr-3">
                                        <a class="btn-primary btn rounded-full" href="{!! route('rental-package.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{ trans('lang.create_rental_package') }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">
                                    {{ trans('lang.processing') }}
                                </div>
                                <div class="table-responsive m-t-10">
                                    <table id="rentalPackagesTable" class="display nowrap table table-hover table-striped table-bordered table table-striped dataTable no-footer dtr-inline collapsed" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                @if (in_array('rental-package.delete', json_decode(@session('user_permissions'), true)))
                                                <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active">
                                                        <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i>
                                                            {{ trans('lang.all') }}</a></label>
                                                </th>
                                                @endif
                                                <th>{{ trans('lang.package_name') }}</th>
                                                <th>{{ trans('lang.package_basefare_price') }}</th>
                                                <th>{{ trans('lang.vehicle_type') }}</th>
                                                <th>{{ trans('lang.published') }}</th>
                                                <th>{{ trans('lang.actions') }}</th>
                                            </tr>
                                        </thead>
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

    <script>
        
        var database = firebase.firestore();
        var section_id = getCookie('section_id') || '';        
        var ref = database.collection('rental_packages');

        var user_permissions = '<?php echo @session('user_permissions'); ?>';
        user_permissions = Object.values(JSON.parse(user_permissions));
        var checkDeletePermission = false;
        if ($.inArray('rental-package.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }
        var currentCurrency = '';
        var currencyAtRight = false;
        var decimal_degits = 0;
        var refCurrency = regionCurrencyRef();
        refCurrency.get().then(async function(snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            decimal_degits = currencyData.decimal_degits;
        });
        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function(snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })
        if(section_id){
            ref = ref.where('sectionId','==',section_id);
        }

        $(document).ready(async function() {
           
            $(document.body).on('click', '.redirecttopage', function() {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });

            //jQuery("#data-table_processing").show();

            const table = $('#rentalPackagesTable').DataTable({
                pageLength: 10, // Number of rows per page
                processing: false, // Show processing indicator
                serverSide: true, // Enable server-side processing
                responsive: true,
                ajax: function(data, callback, settings) {
                    const start = data.start;
                    const length = data.length;
                    const searchValue = data.search.value.toLowerCase();
                    const orderColumnIndex = data.order[0].column;
                    const orderDirection = data.order[0].dir;
                    const orderableColumns = (checkDeletePermission) ? ['', 'name', 'baseFare', 'vehicleTypeId', '', '', ] : ['name', 'baseFare', 'vehicleTypeId', '', '']; 
                    const orderByField = orderableColumns[orderColumnIndex]; 
                    if (searchValue.length >= 3 || searchValue.length === 0) {
                        $('#data-table_processing').show();
                    }
                    ref.get().then(async function(querySnapshot) {
                        if (querySnapshot.empty) {
                            $(".total_count").text(0);
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
                        await Promise.all(querySnapshot.docs.map(async (doc) => {
                            let childData = doc.data();
                             childData.id = doc.id;
                             childData.vehicleTypeName = await getVehicleTypeName(childData.vehicleTypeId);
                            if (searchValue) {
                                if (
                                    (childData.name && childData.name.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.baseFare && childData.baseFare.toString().toLowerCase().includes(searchValue)) ||
                                    (childData.vehicleTypeName && childData.vehicleTypeName.toString().toLowerCase().includes(searchValue))
                                ) {
                                    filteredRecords.push(childData);
                                }
                            } else {
                                filteredRecords.push(childData);
                            }

                        }));
                        filteredRecords.sort((a, b) => {
                            let aValue = a[orderByField] ? a[orderByField]
                                .toString().toLowerCase() : '';
                            let bValue = b[orderByField] ? b[orderByField]
                                .toString().toLowerCase() : '';
                            if (orderByField === 'baseFare') {
                                aValue = a[orderByField] ? parseInt(a[
                                    orderByField]) : 0;
                                bValue = b[orderByField] ? parseInt(b[
                                    orderByField]) : 0;
                            }
                            if (orderDirection === 'asc') {
                                return (aValue > bValue) ? 1 : -1;
                            } else {
                                return (aValue < bValue) ? 1 : -1;
                            }
                        });
                        const totalRecords = filteredRecords.length;
                        $(".total_count").text(totalRecords);
                        const paginatedRecords = filteredRecords.slice(start, start +
                            length);

                        await Promise.all(paginatedRecords.map(async (childData) => {
                            var getData = await buildHTML(childData);
                            records.push({
                                html: getData,
                                isCommissionPlan: childData
                                    .isCommissionPlan
                            });
                        }));

                        records.sort((a, b) => b.isCommissionPlan - a.isCommissionPlan);
                        const htmlRecords = records.map(record => record.html);
                        $(function () {
                                $('[data-toggle="tooltip"]').tooltip();
                            });
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: totalRecords, // Total number of records in Firestore
                            recordsFiltered: totalRecords, // Number of records after filtering (if any)
                            data: htmlRecords // The actual data to display in the table
                        });
                    }).catch(function(error) {
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
                order: (checkDeletePermission) ? [1, 'asc'] : [0, 'asc'],
                columnDefs: [{
                    orderable: false,
                    targets: (checkDeletePermission) ? [0, 4, 5] : [3, 4]
                }, ],
                "language": datatableLang,

            });

            function debounce(func, wait) {
                let timeout;
                const context = this;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), wait);
                };
            }

            $('#search-input').on('input', debounce(function() {
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

        async function buildHTML(childData) {
            var row = [];
            var id = childData.id;
            var route1 = '{{ route('rental-package.edit', ':id') }}';
            route1 = route1.replace(':id', id);
            var route2 = '';
            
            if(checkDeletePermission){
                row.push(`
                    <td class="delete-all">
                        <input type="checkbox" id="is_open_${id}" class="is_open" dataId="${id}">
                        <label class="col-3 control-label" for="is_open_${id}"></label>
                    </td>
                `);
            }

            row.push(`<td><a href="${route1}" id="${childData.id}">${childData.name}</a></td>`);

            row.push(
                currencyAtRight ?
                parseFloat(childData.baseFare).toFixed(decimal_degits) + currentCurrency :
                currentCurrency + parseFloat(childData.baseFare).toFixed(decimal_degits)
            );

            row.push(childData.vehicleTypeName);
            
            row.push(childData.published ?
                `<label class = "switch" ><input type = "checkbox" checked id = "${childData.id}" name="isActive" ><span class = "slider round" > </span> </label>` :
                `<label class="switch"><input type="checkbox" id="${childData.id}" name="isActive"><span class="slider round"></span></label>`
            );

            row.push(`
                <span class="action-btn">
                    <a href="${route1}" class="link-td" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>
                    ${checkDeletePermission ? `
                        <a id="${childData.id}" class="link-td delete-btn direct-click-btn" name="plan-delete" href="javascript:void(0)" data-toggle="tooltip" data-bs-original>
                            <i class="mdi mdi-delete"></i>
                        </a>
                    ` : ''}
                </span>
            `);

            return row;
        }

        $(document).on("click", "input[name='isActive']", async function(e) {
            var ischeck = $(this).is(':checked');
            var sectionId = $(this).attr('data-section');
            var id = this.id;
            if (ischeck) {
                database.collection('rental_packages').doc(id).update({
                    'published': true
                }).then(function(result) {});
            } else {
                database.collection('rental_packages').doc(id).update({
                    'published': false
                }).then(function(result) {});
            }
        });

        $(document).on("click", "a[name='plan-delete']", async function(e) {
            var id = this.id;
            await deleteDocumentWithImage('rental_packages',id,'');
            database.collection('rental_packages').doc(id).delete().then(async function(result) {
                window.location.reload();
            })
        });

        $("#is_active").click(function() {
            $("#rentalPackagesTable .is_open").prop('checked', $(this).prop('checked'));
        });
        
        $("#deleteAll").click(function() {
            if ($('#rentalPackagesTable .is_open:checked').length) {
                if (confirm("{{ trans('lang.selected_delete_alert') }}")) {
                    jQuery("#data-table_processing").show();
                    $('#rentalPackagesTable .is_open:checked').each(async function() {
                        var dataId = $(this).attr('dataId');
                        await deleteDocumentWithImage('rental_packages',dataId,'');
                        database.collection('rental_packages').doc(dataId).delete().then(
                            async function(result) {
                                window.location.reload();
                            })

                    });
                }
            } else {
                alert("{{ trans('lang.select_delete_alert') }}");
            }
        });

        async function getVehicleTypeName(id) {
            if(!id) return '';
            var vehicleType = await database.collection('rental_vehicle_type').doc(id).get();
            var data = vehicleType.data();
            return data.name;
        }

    </script>
@endsection
