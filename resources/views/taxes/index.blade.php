@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.tax_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.tax_plural')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/tax.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.tax_plural')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.tax_plural')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.taxes_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('tax.create') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.tax_create')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="taxTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                <?php if (in_array('tax.delete', json_decode(@session('user_permissions'),true))) { ?>
                                <th class="delete-all"><input type="checkbox" id="is_active"><label
                                            class="col-3 control-label" for="is_active"><a id="deleteAll"
                                                                                        class="do_not_delete"
                                                                                        href="javascript:void(0)"><i
                                                    class="fa fa-trash"></i> {{trans('lang.all')}}</a></label>
                                </th>
                                <?php } ?>
                                <th>{{trans('lang.title')}}</th>
                                <th>{{trans('lang.country')}}</th>
                                <th>{{trans('lang.type')}}</th>
                                <th>{{trans('lang.tax_value')}}</th>
                                <th>{{trans('lang.tax_scope')}}</th>
                                <th>{{trans('lang.status')}}</th>
                                <th>{{trans('lang.actions')}}</th>
                                </tr>
                                </thead>
                                <tbody id="append_list1">
                                </tbody>
                            </table>
                             <div class="form-text text-muted mt-2">
                                {{ trans("lang.admin_vendor_tax_help") }}
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
    var ref = database.collection('tax').orderBy('title');
    var section_id = getCookie('section_id') || null;
    var ref = database.collection('tax').orderBy('title');
    if (section_id) {
        ref = ref.where('sectionId', '==', section_id);
    }

    var refCurrency = regionCurrencyRef();
    var decimal_degits = 0;
    var symbolAtRight = false;
    var currentCurrency = '';
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        decimal_degits = currencyData.decimal_degits;
        if (currencyData.symbolAtRight) {
            symbolAtRight = true;
        }
    });
    var user_permissions = '<?php echo @session('user_permissions') ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('tax.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
    var append_list = '';
    var deleteMsg = "{{trans('lang.delete_alert')}}";
    var deleteSelectedRecordMsg = "{{trans('lang.selected_delete_alert')}}";
    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        append_list = document.getElementById('append_list1');
        append_list.innerHTML = '';
        ref.get().then(async function (snapshots) {
            
            var html = '';
            var totalFiltered = snapshots.docs.length;
             $('.total_count').text(totalFiltered);
            html = await buildHTML(snapshots);
            $(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });
            jQuery("#data-table_processing").hide();
            if (html != '') {
                append_list.innerHTML = html;
            }
            var table = $('#taxTable').DataTable({
                order: [],
                columnDefs: [{
                    targets: (checkDeletePermission==true)? 5 : 4,
                         type: 'date',
                        render: function(data) {
                            return data;
                        }
                    },
                    {
                        orderable: false,
                        targets: (checkDeletePermission==true) ? [0, 6, 7] : [0, 5, 6]  
                    },
                ],
                order: [1,"asc"],
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
        var route1 = '{{route("tax.edit",":id")}}';
        route1 = route1.replace(':id', id);
        var trroute1 = '';
        trroute1 = trroute1.replace(':id', id);
        <?php if (in_array('tax.delete', json_decode(@session('user_permissions'),true))) { ?>
        html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
            'for="is_open_' + id + '" ></label></td>';
            <?php } ?>
        html = html + '<td><a href="' + route1 + '">' + val.title + '</a></td>';
        html = html + '<td>' + (val.country != null ? val.country : '') + '</td>';
        var type = val.type;
        html = html + '<td>' + (type.charAt(0).toUpperCase()) + type.slice(1) + '</td>';
        if (val.type == "fix") {
            var amount = parseFloat(val.tax);
            if (symbolAtRight) {
                html += '<td>' + amount.toFixed(decimal_degits) + currentCurrency + '</td>';
            } else {
                html += '<td>' + currentCurrency + amount.toFixed(decimal_degits) + '</td>';
            }
        } else {
            html = html + '<td>' + val.tax + '%</td>';
        }

        let scopeLabels = {
            order: "{{trans('lang.order_wise_tax')}}",
            product: "{{trans('lang.product_wise_tax')}}",
            packaging: "{{trans('lang.packaging_wise_tax')}}",
            delivery: "{{trans('lang.delivery_wise_tax')}}",
            platform: "{{trans('lang.platform_wise_tax')}}",
            admin_commission: "{{trans('lang.admin_commission_tax')}}",
            vendor_subscription: "{{trans('lang.vendor_subscription_tax')}}",
        };
        html = html + '<td>' + (scopeLabels[val.scope] ?? '') + '</td>';

        if (val.enable) {
            html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isActive"><span class="slider round"></span></label></td>';
        } else {
            html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isActive"><span class="slider round"></span></label></td>';
        }
        html = html + '<td><span class="action-btn"><a href="' + route1 + '" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>';
        if (checkDeletePermission) {
            html=html+'<a id="' + val.id + '" class="delete-btn" name="tax-delete" href="javascript:void(0)" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i class="mdi mdi-delete"></i></a></span></td>';
        }
        html = html + '</tr>';
        return html;
    }
    $("#is_active").click(function () {
        $("#taxTable .is_open").prop('checked', $(this).prop('checked'));
    });
    $("#deleteAll").click(function () {
        if ($('#taxTable .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#overlay").show();
                $('#taxTable .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');
                    database.collection('tax').doc(dataId).delete().then(function () {
                        window.location.reload();
                    });
                });
            } else {
                return false;
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
    $(document).on("click", "input[name='isActive']", function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        if (ischeck) {
            database.collection('tax').doc(id).update({
                'enable': true
            }).then(function (result) {
            });
        } else {
            database.collection('tax').doc(id).update({
                'enable': false
            }).then(function (result) {
            });
        }
    });
    $(document).on("click", "a[name='tax-delete']", function (e) {
        var id = this.id;
        jQuery("#overlay").show();
        database.collection('tax').doc(id).delete().then(function (result) {
            window.location.href = '{{ url()->current() }}';
        });
    });
</script>
@endsection