@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.gift_card_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.gift_card_plural')}}</li>
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
                        <span class="icon mr-3"><img src="{{ asset('images/coupon.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.gift_card_plural')}}</h3>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.gift_card_plural')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.gift_card_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <a class="btn-primary btn rounded-full" href="{!! route('gift-card.save') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.create_gift_card')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                         <table id="giftCardTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                      
                                    <?php if (in_array('gift-card.delete', json_decode(@session('user_permissions'),true))) { ?>
                      
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label
                                            class="col-3 control-label" for="is_active">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i
                                                    class="fa fa-trash"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php }?>               
                                    <th>{{trans('lang.giftcard_info')}}</th>
                                    <th>{{trans('lang.expires_in')}}</th>
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
    var user_permissions = '<?php echo @session('user_permissions') ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;

    if ($.inArray('gift-card.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
    var database = firebase.firestore();
    var offest = 1;
    var pagesize = 10;
    var ref = database.collection('gift_cards');
    var append_list = '';
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })


    $(document).ready(function () {
        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        jQuery("#data-table_processing").show();

        append_list = document.getElementById('append_list1');
        append_list.innerHTML = '';
        ref.get().then(async function (snapshots) {
            var html = '';

            html = await buildHTML(snapshots);
             $(function () {
                                $('[data-toggle="tooltip"]').tooltip();
                            });

            if (html != '') {
                append_list.innerHTML = html;
            }

            var table = $('#giftCardTable').DataTable({
                    order: [],
                    columnDefs: [
                        { orderable: false, targets: (checkDeletePermission==true) ? [0,3,4] : [0,2,3] },

                    ],
                    "language": datatableLang,
                    responsive: true
                });
                table.on('search.dt', function() {
                    var filteredCount = table.rows({ search: 'applied' }).count();
                    $('.total_count').text(filteredCount);  // Update count
                });


            jQuery("#data-table_processing").hide();
        });
        

    });


    async function buildHTML(snapshots) {
        var html = '';
        if (snapshots.docs.length > 0) {
            $('.total_count').text(snapshots.docs.length); 
            
        }
        else
        {
            $('.total_count').text(0); 
        }
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
        newdate = '';

        var id = val.id;
        var route1 = '{{route("gift-card.edit",":id")}}';
        route1 = route1.replace(':id', id);
        if(checkDeletePermission){
        html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
            'for="is_open_' + id + '" ></label></td>';
        }
        if (val.image != '') {
            html = html + '<td><img class="rounded" style="width:50px" src="' + val.image + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'"><a href="' + route1 + '" class="left_space redirecttopage">' + val.title + '</a></td>';
        } else {
            html = html + '<td><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image"><a href="' + route1 + '" class="left_space redirecttopage">' + val.title + '</a></td>';
        }
        
        html = html + '<td>'+val.expiryDay+' Days</td>';
        if (val.isEnable) {
            html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isActive"><span class="slider round"></span></label></td>';
        } else {
            html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isActive"><span class="slider round"></span></label></td>';
        }
        html = html + '<td><span class="action-btn"><a href="' + route1 + '" class="link-td" data-toggle="tooltip" title="{{trans("lang.edit")}}"><i class="mdi mdi-lead-pencil"></i></a>';
        if(checkDeletePermission){
        html=html+'<a id="' + val.id + '" name="giftcard-delete" href="javascript:void(0)" class="delete-btn" data-toggle="tooltip" title="{{trans("lang.delete")}}"><i class="mdi mdi-delete"></i></a></span>';
        }
        html=html+'</td>';

        html = html + '</tr>';

        return html;
    }

    $(document).on("click", "input[name='isActive']", function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        if (ischeck) {
            database.collection('gift_cards').doc(id).update({ 'isEnable': true }).then(function (result) {

            });
        } else {
            database.collection('gift_cards').doc(id).update({ 'isEnable': false }).then(function (result) {

            });
        }

    });

    $(document).on("click", "a[name='giftcard-delete']", async function (e) {
        var id = this.id;

        await deleteDocumentWithImage('gift_cards',id,'image');
        window.location.href = '{{ url()->current() }}';
    });


    $("#is_active").click(function () {
        $("#giftCardTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function () {
        if ($('#giftCardTable .is_open:checked').length) {

            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                $('#giftCardTable .is_open:checked').each(async function () {
                    var dataId = $(this).attr('dataId');
                    await deleteDocumentWithImage('gift_cards',dataId,'image');

                    database.collection('gift_cards').doc(dataId).delete().then(function () {
                        setTimeout(function () {
                            window.location.reload();
                        }, 2000);

                    });
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });

</script>


@endsection