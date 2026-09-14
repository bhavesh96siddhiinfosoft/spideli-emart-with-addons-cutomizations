@extends('layouts.app')


@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
         <div class="col-md-5 align-self-center">
            <div class="d-flex top-title-section justify-content-between">
                <div class="d-flex top-title-left align-self-center">
                    <span class="icon mr-3"><img src="{{ asset('images/users.png') }}"></span>
                    <h3 class="mb-0">{{trans('lang.order_review')}} <span class="page-title"></span></h3>
                    <span class="counter ml-3 total_count"></span>
                </div>
            </div>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.order_review_table')}}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
       <div class="table-list">
       <div class="row">
           <div class="col-12">

            <?php if($id!=''){ ?>
                <div class="menu-tab">
                    <ul>                       
                         <li>
                            <a href="{{route('stores.view',$id)}}"><i class="ri-list-indefinite"></i>{{trans('lang.tab_basic')}}</a>
                        </li>
                        <li>
                            <a href="{{route('vendors.items',$id)}}"><i class="ri-shopping-basket-fill"></i>{{trans('lang.tab_items')}}</a>
                        </li>
                        <li>
                            <a href="{{route('vendors.orders',$id)}}"><i class="ri-shopping-bag-line"></i>{{trans('lang.tab_orders')}}</a>
                        </li>
                        <li class="active">
                            <a href="{{route('vendors.reviews',$id)}}"><i class="ri-shield-star-fill"></i>{{trans('lang.tab_reviews')}}</a>
                        </li>
                        <li>
                            <a href="{{route('vendors.coupons',$id)}}"><i class="ri-discount-percent-fill"></i>{{trans('lang.tab_promos')}}</a>
                        <li>
                            <a href="{{route('vendors.payout',$id)}}"><i class="ri-bank-card-line"></i>{{trans('lang.tab_payouts')}}</a>
                        </li>
                        <li>
                            <a href="{{route('payoutRequests.vendor.view',$id)}}"><i class="ri-refund-line"></i>{{trans('lang.tab_payout_request')}}</a>
                        </li>
                        <li>
                            <a class="wallet_transaction"><i class="ri-wallet-line"></i>{{trans('lang.wallet_transaction')}}</a>
                        </li>

                        <li class="dine_in_future" style="display:none;">
                            <a href="{{route('vendors.booktable',$id)}}"><i class="ri-restaurant-line"></i>{{trans('lang.dine_in_booking_history')}}</a>
                        </li>
                        <?php
                        $subscription =  route("subscription.subscriptionPlanHistory", ":id");
                        $subscription =  str_replace(":id", "storeID=" . $id, $subscription);
                        ?>
                        <li>
                            <a href="{{ $subscription }}"><i class="ri-chat-history-fill"></i>{{trans('lang.subscription_history')}}</a>
                        </li>
                        <li>
                            <a href="{{ route('restaurants.advertisements', $id) }}"><i class="mdi mdi-newspaper"></i>{{ trans('lang.advertisement_plural') }}</a>
                        </li>
                         @php
                                    $sectionType = $_COOKIE['service_type'] ?? ''; 
                                    
                                @endphp
                                <?php if($sectionType == 'ecommerce-service'){ ?>
                               
                                <?php }else{ ?>
                                <li class="">
                                    <a href="{{ route('restaurants.deliveryman', $id) }}"><i class="ri-riding-fill"></i>{{ trans('lang.deliveryman') }}</a>
                                </li>
                                    <?php }?>
                    </ul>
                </div>
            <?php } ?>
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.order_review_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.order_review_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                       
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
                                    <th>{{trans('lang.order_id')}}</th>
                                    <th class="address-list">{{ trans('lang.order_review')}}</th>
                                    <th>{{ trans('lang.item_review_rate')}}</th>
                                    <th>{{ trans('lang.item_review_user_id')}}</th>
                                    <?php if($id ==''){ ?>

                                        <th>{{trans('lang.vendor')}}</th>

                                    <?php }?>
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
    var offest=1;
    var pagesize=10;
    var pagesizes = 0;
    var end = null;
    var endarray=[];
    var start = null;
    var user_number = [];
    var vendorID="{{$id}}";                                     

    <?php if($id!=''){ ?>

    var ref = database.collection('items_review').where('VendorId','==','<?php echo $id; ?>');

        getStoreNameFunction('<?php echo $id; ?>');

    <?php }else{ ?>

    var ref = database.collection('items_review');

    <?php } ?>

    var append_list = '';

    $(document).ready(function() {

        $(document.body).on('click', '.redirecttopage' ,function(){

            var url=$(this).attr('data-url');

            window.location.href = url;

        });
        jQuery("#data-table_processing").show();

        append_list = document.getElementById('append_list1');

        append_list.innerHTML='';

        pagesizes = getCookie('pagesizes');

        if(pagesizes !=0){

            $('.pageSize option[value='+pagesizes+']').attr('selected','selected');

                ref.limit(pagesizes).get().then( async function(snapshots){

                html='';
                if (snapshots.docs.length > 0) {
                    $('.total_count').text(snapshots.docs.length); 
                    
                }
                else
                {
                    $('.total_count').text(0);                 
                }

                html=buildHTML(snapshots);
                    $(function () {
                                    $('[data-toggle="tooltip"]').tooltip();
                                });

                jQuery("#data-table_processing").hide();

                if(html!=''){

                    append_list.innerHTML=html;

                    start = snapshots.docs[snapshots.docs.length - 1];

                    endarray.push(snapshots.docs[0]);

                    if(snapshots.docs.length<pagesizes){

                        jQuery("#data-table_paginate").hide();

                    }
                }

                const table = $('#example24').DataTable({                    

                    order: [],

                    columnDefs: [

                        {orderable: false, targets: [1, 2, 4]},

                    ],

                   "language": datatableLang,

                    responsive: true,

                });
                table.on('search.dt', function() {
                    var filteredCount = table.rows({ search: 'applied' }).count();
                    $('.total_count').text(filteredCount);  // Update count
                });

            });

        }else{

            ref.limit(pagesize).get().then( async function(snapshots){

            html='';
            if (snapshots.docs.length > 0) {
                $('.total_count').text(snapshots.docs.length); 
                
            }
            else
            {
                $('.total_count').text(0);                 
            }

            html=buildHTML(snapshots);
            $(function () {
                                $('[data-toggle="tooltip"]').tooltip();
                            });
            jQuery("#data-table_processing").hide();

                if(html!=''){

                    append_list.innerHTML=html;

                    start = snapshots.docs[snapshots.docs.length - 1];

                    endarray.push(snapshots.docs[0]);

                    if(snapshots.docs.length<pagesize){

                        jQuery("#data-table_paginate").hide();

                    }

                }

                const table = $('#example24').DataTable({                    

                    order: [],

                    columnDefs: [

                        {orderable: false, targets: [0, 1, 2, 4]},

                    ],

                   "language": datatableLang,

                    responsive: true,

                });
                table.on('search.dt', function() {
                    var filteredCount = table.rows({ search: 'applied' }).count();
                    $('.total_count').text(filteredCount);  // Update count
                });
            });

        }

    });



async function getStoreNameFunction(vendorId){

    var vendorName = '';

        await database.collection('vendors').where('id', '==', vendorId).get().then(async function (snapshots) {

            var vendorData = snapshots.docs[0].data();

            vendorName = vendorData.title;

            $(".page-title").text(' - '+vendorName);



            if(vendorData.dine_in_active==true){

                $(".dine_in_future").show();

            }
            var wallet_route = "{{route('users.walletstransaction','id')}}";

            $(".wallet_transaction").attr("href", wallet_route.replace('id', 'storeID='+vendorData.author));

            if (vendorData.section_id) {
                let sectionSnap = await database.collection('sections').doc(vendorData.section_id).get();
                if (sectionSnap.exists) {
                    let sectionData = sectionSnap.data();
                    if (sectionData.dine_in_active === true) {
                        $(".dine_in_future").show();
                    }
                }
            }

        });

    return vendorName;

}



   function buildHTML(snapshots){

    var html='';

    var alldata=[];

    var number= [];

    snapshots.docs.forEach((listval) => {

        var datas=listval.data();

        datas.id=listval.id;

        alldata.push(datas);

    });



    var count = 0;

    alldata.forEach((listval) => {



            var val=listval;

            html=html+'<tr>';

            newdate='';

            var reviewId = val.Id;

            var route1 =  '{{route("orderReview.edit",":id")}}';

            route1 = route1.replace(':id', reviewId);



            var route_orderid =  '{{route("orders.review",":oid")}}';

            route_orderid = route_orderid.replace(':oid', val.orderid);



            var route_user =  '{{route("users.edit",":id")}}';

            route_user = route_user.replace(':id', val.CustomerId);



            var route_vendors =  '{{route("stores.view",":id")}}';

            route_vendors = route_vendors.replace(':id', val.VendorId);



            <?php if($id!=''){ ?>

                route1 =route1+'?eid={{$id}}';

                route_orderid =route_orderid+'?eid={{$id}}';

            <?php }?>



            html=html+'<td data-url="'+route_orderid+'" class="redirecttopage">'+val.orderid+'</td>';



            html=html+'<td class="address-list">'+val.comment+'</td>';



            

            html=html + '<td><ul class="rating" data-rating="'+val.rating+'"><li class="rating__item"></li><li class="rating__item"></li><li class="rating__item"></li><li class="rating__item"></li><li class="rating__item"></li></ul></td>';



            const user_name=userName(val.CustomerId);

            html=html+'<td class="name_'+val.CustomerId+' redirecttopage" data-url="'+route_user+'"></td>';



            <?php if($id ==''){ ?>

                const vendor_name = vendorName(val.VendorId);

                html=html+'<td class="item_'+val.VendorId+' redirecttopage" data-url="'+route_vendors+'"></td>';

            <?php }?>





            html=html+'<td class="action-btn"><a href="'+route1+'" data-toggle="tooltip" title="{{trans('lang.edit')}}"><i class="mdi mdi-lead-pencil"></i></a><a id="'+val.Id+'" name="item-review-delete" class="do_not_delete" href="javascript:void(0)" data-toggle="tooltip" title="{{trans('lang.delete')}}"><i class="mdi mdi-delete"></i></a></td>';



            html=html+'</tr>';

            count =count +1;

        });

        return html;

}



function prev(){

    

    if(endarray.length==1){

        return false;

    }

    

    end=endarray[endarray.length-2];



  if(end!=undefined || end!=null){

    jQuery("#data-table_processing").show();

            if(jQuery("#selected_search").val()=='order_id' && jQuery("#search").val().trim()!=''){



            listener=ref.orderBy('Id').limit(pagesize).startAt(jQuery("#search").val()).endAt(jQuery("#search").val()+'\uf8ff').startAt(end).get();



        }else{

            listener = ref.startAt(end).limit(pagesize).get();

        }



        listener.then((snapshots) => {

        html='';

        html=buildHTML(snapshots);

        jQuery("#data-table_processing").hide();

        if(html!=''){

            append_list.innerHTML=html;

            start = snapshots.docs[snapshots.docs.length - 1];

            endarray.splice(endarray.indexOf(endarray[endarray.length-1]),1);



            if(snapshots.docs.length < pagesize){



                jQuery("#users_table_previous_btn").hide();

            }



        }

    });

  }

}



function next(){



  if(start!=undefined || start!=null){



    jQuery("#data-table_processing").hide();



        if(jQuery("#selected_search").val()=='order_id' && jQuery("#search").val().trim()!=''){



            listener=ref.orderBy('Id').limit(pagesize).startAt(jQuery("#search").val()).endAt(jQuery("#search").val()+'\uf8ff').startAfter(start).get();



            }else{

            listener = ref.startAfter(start).limit(pagesize).get();

        }

        listener.then((snapshots) => {



            html='';

            html=buildHTML(snapshots);
             $(function () {
                                $('[data-toggle="tooltip"]').tooltip();
                            });

            jQuery("#data-table_processing").hide();

            if(html!=''){

                append_list.innerHTML=html;

                start = snapshots.docs[snapshots.docs.length - 1];



                if(endarray.indexOf(snapshots.docs[0])!=-1){

                    endarray.splice(endarray.indexOf(snapshots.docs[0]),1);

                }

                endarray.push(snapshots.docs[0]);

            }

        });

    }

}

function clickpage(value) {

    setCookie('pagesizes', value, 30);

    location.reload();

}



async function userName(userID) {

    var userName='';

    await database.collection('users').where("id","==",userID).get().then( async function(snapshotss){



        if(snapshotss.docs[0]){

            var user = snapshotss.docs[0].data();

            userName = user.firstName+" "+user.lastName;

            jQuery(".name_"+userID).html(userName);



        }else{

            jQuery(".name_"+userID).html('');



        }

    });

    return userName;

}


async function vendorName(vendorID) {

    var vendorName ='';

    await database.collection('vendors').where("id","==",vendorID).get().then( async function(snapshotss){



                if(snapshotss.docs[0]){

                    var vendor = snapshotss.docs[0].data();

                    vendorName = vendor.title;



                    jQuery(".item_"+vendorID).html(vendorName);



                }else{

                    jQuery(".item_"+vendorID).html('');



                }

    });

    return vendorName;

}

$(document).on("click","a[name='item-review-delete']", function (e) {

    var id = this.id;

     database.collection('items_review').doc(id).delete().then(function(result) {

        window.location.href = '{{ route("orderReview")}}';

    })

});
</script>

@endsection