@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor page-title">{{trans('lang.provider_payout_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.provider_payout_plural')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>
    <div class="container-fluid">
       
       <div class="table-list">
       <div class="row">
           <div class="col-12">
                <?php if ($id != '') { ?>
                    <div class="menu-tab tabDiv">
                            <ul>
                                <li ><a href="{{route('providers.view', $id)}}"><img src="{{ asset('images/provider.png') }}"> {{trans('lang.tab_basic')}}</a>
                                </li>
                                <li><a href="{{route('ondemand.services.index', $id)}}"><img src="{{ asset('images/service.png') }}"> {{trans('lang.services')}}</a></li>
                                <li>
                                <li><a href="{{route('ondemand.workers.index', $id)}}"><img src="{{ asset('images/worker.png') }}"> {{trans('lang.workers')}}</a></li>
                                <li>
                                <li><a href="{{route('ondemand.bookings.index',$id)}}"><img src="{{ asset('images/booking.png') }}"> {{trans('lang.booking_plural')}}</a></li>
                                <li>
                                <li><a href="{{route('ondemand.coupons', $id)}}"><img src="{{ asset('images/coupon.png') }}"> {{trans('lang.coupon_plural')}}</a></li>
                                 <li class="active">
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
                <?php } ?>
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0 top-title-section">
                   
                   <div class=" top-title-left align-self-center">
                        
                       <div class="d-flex align-items-center">
                        <span class="icon mr-3"><img src="{{ asset('images/payment.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.provider_payout_plural')}}</h3>
                        <span class="counter ml-3 total_count"></span>
                       </div>
                        <p class="mb-0 text-dark-2">{{trans('lang.provider_payouts_table_text')}}</p>
                    </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <?php if ($id != '') { ?>
                            <a class="btn-primary btn rounded-full" href="{!! route('providerPayouts.create') !!}/{{$id}}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.provider_payout_create')}}</a>
                        <?php } else { ?>
                            <a class="btn-primary btn rounded-full" href="{!! route('providerPayouts.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.provider_payout_create')}}</a>
                        <?php } ?>
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

                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll"
                                    class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                               
                                    <?php if ($id == '') { ?>
                                    <th>{{ trans('lang.provider')}}</th>
                                    <?php } ?>
                                    <th>{{trans('lang.paid_amount')}}</th>
                                    <th>{{trans('lang.date')}}</th>
                                    <th>{{trans('lang.vendors_payout_note')}}</th>
                                    <th>Admin {{trans('lang.vendors_payout_note')}}</th>
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
    var id = '<?php echo $id; ?>';

    var intRegex = /^\d+$/;
    var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;

    <?php if ($id != '') { ?>
        var wallet_route = "{{route('users.walletstransaction','id')}}";
        $(".wallet_transaction").attr("href", wallet_route.replace('id', 'providerID='+id));

    var ref = database.collection('payouts').where('vendorID', '==', '<?php echo $id; ?>').where('paymentStatus', '==', 'Success').where('role','==','provider').orderBy('paidDate', 'desc');
    getProviderName('<?php echo $id; ?>');
    <?php } else { ?>
    var ref = database.collection('payouts').where('paymentStatus', '==', 'Success').where('role','==','provider').orderBy('paidDate', 'desc');
    <?php } ?>

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

    var append_list = '';

    $(document).ready(function () {

        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

      
        jQuery("#data-table_processing").show();

        $(document).on('click', '.dt-button-collection .dt-button', function () {
            $('.dt-button-collection').hide();
            $('.dt-button-background').hide();
        });
        $(document).on('click', function (event) {
            if (!$(event.target).closest('.dt-button-collection, .dt-buttons').length) {
                $('.dt-button-collection').hide();
                $('.dt-button-background').hide();
            }
        });
        var fieldConfig = {
            columns: [
                <?php if ($id == '') { ?>
                    { key: 'title', header: "{{ trans('lang.provider')}}" },
                <?php } ?>
              
                { key: 'amount', header: "{{ trans('lang.total_amount')}}" },
                { key: 'paidDate', header: "{{trans('lang.date')}}" },
                { key: 'note', header: "{{trans('lang.providers_payout_note')}}" },
                { key: 'adminNote', header: "{{trans('lang.adproviders_payout_note')}}" },
            ],
            fileName: "{{ trans('lang.provider')}} {{trans('lang.payment_plural')}}",
        };

          const table = $('#example24').DataTable({
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
                var orderableColumns = ['','title','amount','paidDate','note','adminNote','']; // Ensure this matches the actual column names
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

            const partyRegions = await partyRegionMap();

            await Promise.all(querySnapshot.docs.map(async (doc) => {
                let childData = doc.data();  
                /* A payout carries no region of its own. It belongs to the
                 * service provider it is paid to, and that is where its region
                 * comes from. */
                if (!payoutInActiveRegion(partyRegions, childData.vendorID)) {
                    return;
                }

                childData.id = doc.vendorID; // Ensure the document ID is included in the data
                childData.recid=doc.id;
                const provider = await payoutProvider(childData.vendorID);
                if (!provider) {
                    return;
                }
                childData.title = provider;
                if (searchValue) {
                    var date = '';
                    var time = ''; 
                    if (childData.hasOwnProperty("paidDate")) {
                        try {
                            date = childData.paidDate.toDate().toDateString();
                            time = childData.paidDate.toDate().toLocaleTimeString('en-US');
                        } catch (err) {
                        }
                    }
                    var paidDate = date + '<br> ' + time;
                    if (
                        (childData.title && childData.title.toString().toLowerCase().includes(searchValue)) ||
                        (childData.amount && childData.amount.toString().toLowerCase().includes(searchValue)) ||
                        (paidDate && paidDate.toString().toLowerCase().includes(searchValue)) ||
                        (childData.note && childData.note.toString().toLowerCase().includes(searchValue)) ||
                        (childData.adminNote && childData.adminNote.toString().toLowerCase().includes(searchValue)) 
                    ) {
                        filteredRecords.push(childData);
                    }
                } else {
                    filteredRecords.push(childData);
                }
            }));
  
            filteredRecords.sort((a, b) => {
                let aValue = a[orderByField];
                let bValue = b[orderByField];

                    if (orderByField === 'paidDate' && a[orderByField] != '' && b[orderByField] != '' && a[orderByField] != null && b[orderByField] != null) {
                    try {
                        aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                        bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                    } catch (err) {

                    }
                }
            
                if (orderByField === 'amount') {
                   
                    aValue = a[orderByField] ? parseInt(a[orderByField]) : 0;
                    bValue = b[orderByField] ? parseInt(b[orderByField]) : 0;
                }


                if (orderByField === 'title') {
                    aValue = a[orderByField] ? a[orderByField].toString().toLowerCase() : '';
                    bValue = b[orderByField] ? b[orderByField].toString().toLowerCase() : '';
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
                if(childData.hasOwnProperty('title') || childData.title != null || childData.title!=''){
                    var getData = await buildHTML(childData);
                    records.push(getData);                  
                }
  
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
    <?php if($id == '') { ?>
       columnDefs: [{
            targets: 2,
            type: 'date',
            render: function (data) {
                return data;
            }
        },
            {orderable: false, targets: [0,4,6]},
        ],
        order: [2, "desc"],
    <?php } else { ?>
       columnDefs: [{
            targets: 2,
            type: 'date',
            render: function (data) {
                return data;
            }
        },
            {orderable: false, targets: [0,3,5]},
        ],
        order: [2, "desc"],
    <?php } ?>
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
                $('.dataTables_filter input').attr('placeholder', '{{trans('lang.search_here')}}').attr('autocomplete','new-password').val('');
                $('.dataTables_filter label').contents().filter(function() {
                    return this.nodeType === 3; 
                }).remove();
            }
        });


    function debounce(func, wait) {
        let timeout;
        const context = this;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    });


    function getProviderName(providerId) {
        var providerName = '';
        database.collection('users').where('id', '==', providerId).get().then(function (snapshots) {
            var providerData = snapshots.docs[0].data();
            providerName = providerData.firstName+' '+providerData.lastName;
            $(".providerTitle").text(' - ' + providerName);
           
        });
        return providerName;
    }

    async function buildHTML(val) {
        var html = [];
    
        if (val.title) {

            var amount = '';
            var price = val.amount;

            if (intRegex.test(price) || floatRegex.test(price)) {

                price = parseFloat(price).toFixed(decimal_degits);
            } else {
                price = 0;
            }

            if (currencyAtRight) {
                amount = parseFloat(price).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                amount = currentCurrency + "" + parseFloat(price).toFixed(decimal_degits);
            }
            html.push('<input type="checkbox" id="is_open_' + val.recid + '" class="is_open" dataId="' + val.recid + '"><label class="col-3 control-label"\n' +
            'for="is_open_' + val.recid + '" ></label>');

            <?php if ($id == '') { ?>
            var route = '{{route("providers.view",":id")}}';
            route = route.replace(':id', val.vendorID);
           
            html.push('<td><a href="' + route + '">' + val.title + '</a></td>');
            <?php } ?>
            html.push('<td>' + amount + '</td>');
            var date = val.paidDate.toDate().toDateString();
            var time = val.paidDate.toDate().toLocaleTimeString('en-US');
            html.push('<td>' + date + '<br> ' + time + '</td>');

            if (val.note != undefined && val.note != '') {
                html.push('<td>' + val.note + '</td>');
            } else {
                html.push('<td></td>');
            }
            if (val.adminNote != undefined && val.adminNote != '') {
                html.push('<td>' + val.adminNote + '</td>');
            } else {
                html.push('<td></td>');
            }
              html.push('<span class="action-btn"><a id="' + val.recid + '" class="delete-btn" name="pro_payouts-delete" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a></span>');

          
        }
        return html;
    }


    async function payoutProvider(provider) {
        var payoutProvider = '';

        await database.collection('users').where("id", "==", provider).get().then(async function (snapshotss) {
            if (snapshotss.docs[0]) {
                var provider_data = snapshotss.docs[0].data();
                payoutProvider = provider_data.firstName+' '+provider_data.lastName;

            }
        });
        return payoutProvider;
    }

    $("#is_active").click(function () {
        $("#example24 .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function () {
        if ($('#example24 .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                $('#example24 .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');
                    database.collection('payouts').doc(dataId).delete().then(function () {
                        setTimeout(function () {
                            window.location.reload();
                        }, 5000);
                    });
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });

    $(document).on("click", "a[name='pro_payouts-delete']", function (e) {
        var id = this.id;
        database.collection('payouts').doc(id).delete().then(function () {
            window.location.reload();
        });


    });

</script>


@endsection