@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            @if($id != '')
            <h3 class="text-themecolor">{{trans('lang.provider_Detail')}} - <span id="providerName"></span></h3>
            @else
            <h3 class="text-themecolor">{{trans('lang.ondemand_plural')}} - {{trans('lang.coupon_plural')}}</h3>
            @endif
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.coupon_plural')}}</li>
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
                                    <li ><a href="{{route('providers.view', $id)}}"><img src="{{ asset('images/provider.png') }}"> {{trans('lang.tab_basic')}}</a>
                                    </li>
                                    <li><a href="{{route('ondemand.services.index', $id)}}"><img src="{{ asset('images/service.png') }}"> {{trans('lang.services')}}</a></li>
                                    <li>
                                    <li><a href="{{route('ondemand.workers.index', $id)}}"><img src="{{ asset('images/worker.png') }}"> {{trans('lang.workers')}}</a></li>
                                    <li>
                                    <li><a href="{{route('ondemand.bookings.index',$id)}}"><img src="{{ asset('images/booking.png') }}"> {{trans('lang.booking_plural')}}</a></li>
                                    <li>
                                    <li class="active"><a href="{{route('ondemand.coupons', $id)}}"><img src="{{ asset('images/coupon.png') }}"> {{trans('lang.coupon_plural')}}</a></li>

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
                                        <a href="{{ $subscription }}"><img src="{{ asset('images/subscription.png') }}">  {{trans('lang.subscription_history')}}</a>
                                    </li>
                                </ul>
                            </div>

                        </div>
                    @endif
                <div class="d-flex top-title-section pb-4 justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/coupon.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.coupon_plural')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.coupon_plural')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.coupon_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                    @if($id=='')
                        <a class="btn-primary btn rounded-full" href="{!! route('ondemand.coupons.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.coupon_create')}}</a>
                    @else
                    <a class="btn-primary btn rounded-full" href="{!! route('ondemand.coupons.create','id='.$id) !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.coupon_create')}}</a>
                    @endif
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="couponTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                <?php if (in_array('ondemand.coupons.delete', json_decode(@session('user_permissions'),true))) { ?>

                                    <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                class="col-3 control-label" for="is_active"
                                        ><a id="deleteAll" class="do_not_delete"
                                            href="javascript:void(0)"><i
                                                        class="fa fa-trash"></i> {{trans('lang.all')}}</a></label></th>
                                        <?php }?>
                                    <th>{{trans('lang.coupon_code')}}</th>
                                    <th>{{trans('lang.coupon_discount')}}</th>
                                    @unless($id != '')
                                    <th>{{trans('lang.provider')}}</th>
                                    @endunless
                                    <th>{{trans('lang.coupon_privacy')}}</th>
                                    <th>{{trans('lang.coupon_expires_at')}}</th>
                                    <th>{{trans('lang.coupon_enabled')}}</th>
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
    var user_permissions = '<?php echo @session('user_permissions') ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;

    if ($.inArray('ondemand.coupons.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
    }

    var database = firebase.firestore();

    var id="{{$id}}";
    if(id!=''){
        $('.tabDiv').show();
        var ref = database.collection('providers_coupons').where('providerId','==',id);
    }else{
        $('.tabDiv').hide();
        var ref = database.collection('providers_coupons');
    }

    if(section_id){
        ref = ref.where('sectionId', '==', section_id);
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

    var append_list = '';

    $(document).ready(function () {
        if(id!=''){
            var wallet_route = "{{route('users.walletstransaction','id')}}";
            $(".wallet_transaction").attr("href", wallet_route.replace('id', 'providerID='+id));

            getProviderNameForFilter(id);
        }
        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        jQuery("#data-table_processing").show();

        const table = $('#couponTable').DataTable({
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
                var orderableColumns = (checkDeletePermission) ? ['', 'code', 'discount', 'providerName', 'privacy', 'expiresAt', '', ''] : ['code', 'discount',  'providerName', 'privacy', 'expiresAt', '', '']; // Ensure this matches the actual column names
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
                    var sectionNames = {};
                    const sectionDocs = await database.collection('sections').where('isActive', '==', true).orderBy('order').get();
                    sectionDocs.forEach(doc => {
                        sectionNames[doc.id] = doc.data().name;
                    });
                    var providerNames = {};
                    const storeDocs = await database.collection('users').get();
                    storeDocs.forEach(doc => {
                        providerNames[doc.id] = doc.data().firstName + ' ' + doc.data().lastName;
                    });
                    await Promise.all(querySnapshot.docs.map(async (doc) => {
                        let childData = doc.data();
                        if (childData.hasOwnProperty("providerId")) {
                            childData.providerName = providerNames[childData.providerId] || '';
                        } else {
                            childData.providerName = '';
                        }

                        if (childData.hasOwnProperty("sectionId")) {
                            childData.section = sectionNames[childData.sectionId] || '';
                        } else {
                            childData.section = '';
                        }
                        childData.privacy = (childData.isPublic) ? '{{trans("lang.public")}}' : '{{trans("lang.private")}}';
                        childData.id = doc.id; // Ensure the document ID is included in the data

                        if (searchValue) {
                            var date = '';
                            var time = '';
                            if (childData.hasOwnProperty("expiresAt")) {
                                try {
                                    date = childData.expiresAt.toDate().toDateString();
                                    time = childData.expiresAt.toDate().toLocaleTimeString('en-US');
                                } catch (err) {
                                }
                            }
                            var expireAt = date + '<br> ' + time;
                            if (
                                (childData.code && childData.code.toLowerCase().toString().includes(searchValue)) ||
                               
                                (expireAt && expireAt.toString().toLowerCase().indexOf(searchValue) > -1) ||
                                (childData.discount && childData.discount.toString().toLowerCase().includes(searchValue)) ||
                                (childData.providerName && childData.providerName.toString().toLowerCase().includes(searchValue)) ||
                                (childData.privacy && childData.privacy.toString().toLowerCase().includes(searchValue))

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

                        if (orderByField === 'expiresAt') {
                            try {
                                aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                                bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                            } catch (err) {

                            }
                        }
                        if(orderByField === 'discount') {
                            aValue = a[orderByField] ? parseInt(a[orderByField]) : 0;
                            bValue = b[orderByField] ? parseInt(b[orderByField]) : 0;

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
            ? (show ? 5 : 4)  // Checkbox exists → Title is col 2 or 1
            : (show ? 4 : 3);  // No checkbox → Title is col 1 or 0
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

    async function buildHTML(val) {

        var html = [];
        var count = 0;
        var discount_price = '';

        if (currencyAtRight) {
            if (val.discountType == 'Percentage') {
                discount_price = val.discount + "%";
            } else {
                discount_price = parseFloat(val.discount).toFixed(decimal_degits) + "" + currentCurrency;
            }
        } else {
            if (val.discountType == 'Percentage') {
                discount_price = val.discount + "%";
            } else {
                discount_price = currentCurrency + "" + parseFloat(val.discount).toFixed(decimal_degits);
            }
        }

        var id = val.id;
        var route1 = '{{route("ondemand.coupons.edit",":id")}}';
        var idOfProviderDetailPage="<?php echo $id; ?>";
        if(idOfProviderDetailPage!=''){
            route1 = route1.replace(':id', val.id+"?id="+idOfProviderDetailPage);
        }else{
            route1 = route1.replace(':id', id);
        }

        if(checkDeletePermission){
            html.push('<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
            'for="is_open_' + id + '" ></label></td>');
        }
        html.push('<a  data-url="' + route1 + '" href="'+route1+'" class="redirecttopage">' + val.code + '</a>');
        html.push(discount_price);

       $("#providerName").text(val.providerName);
        @if($id == '')
        if (val.hasOwnProperty("providerId")) {
            var providerView = '{{route("providers.view",":id")}}';
            providerView = providerView.replace(':id', val.providerId);
             if(val.providerName==""){
                providerView="javascript:void(0)";
                providerName="{{trans('lang.unknown')}}"
            }
            html.push('<td><a href="' + providerView + '">' + val.providerName + '</a></td>');
        } else {
            html.push('<td></td>');
        }
        @endif

        if (val.hasOwnProperty('isPublic') && val.isPublic) {
            html.push('<td class="success"><span class="badge badge-success py-2 px-3">{{trans("lang.public")}}</sapn></td>');
        } else {
            html.push('<td class="danger"><span class="badge badge-danger py-2 px-3">{{trans("lang.private")}}</sapn></td>');
        }
        var date = '';
        var time = '';
        if (val.hasOwnProperty("expiresAt")) {
            try {
                date = val.expiresAt.toDate().toDateString();
                time = val.expiresAt.toDate().toLocaleTimeString('en-US');
            } catch (err) {

            }
            html.push('<td class="dt-time">' + date + '<br> ' + time + '</td>');
        } else {
            html.push('<td></td>');
        }
        if (val.isEnabled) {
            html.push('<label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isActive"><span class="slider round"></span></label>');
        } else {
            html.push('<label class="switch"><input type="checkbox" id="' + val.id + '" name="isActive"><span class="slider round"></span></label>');
        }
        var actionHtml = '';
        actionHtml += '<span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" title="{{trans("lang.edit")}}"><i class="mdi mdi-lead-pencil"></i></a>';
        if(checkDeletePermission){
            actionHtml += '<a id="' + val.id + '" name="coupon_delete_btn" class="delete-btn" href="javascript:void(0)" data-toggle="tooltip" title="{{trans("lang.delete")}}"><i class="mdi mdi-delete"></i></a>';
        }
        actionHtml += '</span>';

        html.push(actionHtml);

        return html;
    }

    $(document).on("click", "input[name='isActive']", function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        var isEnabled = ischeck ? true : false;
        database.collection('providers_coupons').doc(id).update({
            'isEnabled': isEnabled
        });
    });


    $("#is_active").click(function () {
        $("#couponTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function () {
        if ($('#couponTable .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                $('#couponTable .is_open:checked').each(async function () {
                    var dataId = $(this).attr('dataId');
                    await deleteDocumentWithImage('providers_coupons',dataId,'image');
                    window.location.reload();
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });

    $(document).on("click", "a[name='coupon_delete_btn']",async  function (e) {
        var id = this.id;
        await deleteDocumentWithImage('providers_coupons',id,'image');
        jQuery("#data-table_processing").show();
        window.location = "{{! url()->current() }}";
    });
    async function getProviderNameForFilter(providerId){
        await database.collection('users').where('id', '==', providerId).get().then(async function (snapshots) {
            var providerData = snapshots.docs[0].data();
            providerName = providerData.firstName+' '+providerData.lastName;
            $('.PageTitle').html("{{trans('lang.coupon_plural')}} - " + providerName);
        });

}

</script>

@endsection