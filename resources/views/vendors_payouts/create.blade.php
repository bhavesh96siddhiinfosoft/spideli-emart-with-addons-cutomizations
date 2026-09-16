@extends('layouts.app')

@section('content')

<?php if ($id == 'create') {

    $id = '';

} ?>

<div class="page-wrapper">

    <div class="row page-titles">


        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.vendors_payout_plural')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>

                <?php if ($id != '') { ?>

                    <li class="breadcrumb-item"><a
                                href="{{ route('vendors.payout', ['id' => $id]) }}">{{trans('lang.vendors_payout_table')}}</a>
                    </li>

                <?php } ?>

                <li class="breadcrumb-item">{{trans('lang.vendors_payout_create')}}</li>

            </ol>

        </div>

    </div>


    <div class="card-body">

        <div class="error_top"></div>

        <div class="row vendor_payout_create">

            <div class="vendor_payout_create-inner">

                <fieldset>

                    <legend>{{trans('lang.vendors_payout_create')}}</legend>

                    <?php if ($id == '') { ?>

                        <div class="form-group row width-100">

                            <label class="col-3 control-label">{{ trans('lang.select_vendor')}}</label>

                            <div class="col-7">

                                <select id="select_vendor" class="form-control">

                                    <option value="">{{ trans('lang.select_vendor') }}</option>

                                </select>

                                <div class="form-text text-muted">

                                    {{ trans("lang.select_vendor") }}

                                </div>

                            </div>

                        </div>

                    <?php } ?>

                    <div class="form-group row width-100">

                        <label class="col-3 control-label">{{trans('lang.vendors_payout_amount')}}</label>

                        <div class="col-7">

                            <input type="number" class="form-control payout_amount">

                            <div class="form-text text-muted">

                                {{ trans("lang.vendors_payout_amount_placeholder") }}

                            </div>

                        </div>

                    </div>


                    <div class="form-group row width-100">

                        <label class="col-3 control-label">{{ trans('lang.vendors_payout_note')}}</label>

                        <div class="col-7">

                            <textarea type="text" rows="8" class="form-control payout_note"></textarea>

                        </div>

                    </div>


                </fieldset>

            </div>

        </div>

    </div>


    <div class="form-group col-12 text-center btm-btn">

        <button type="button" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i>
            {{trans('lang.save')}}
        </button>

        <?php if ($id != '') { ?>

            <a href="{{route('vendors.payout',$id)}}" class="btn btn-default"><i
                        class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>

        <?php } else { ?>

            <a href="{!! route('payoutRequests.vendor.disbursement') !!}" class="btn btn-default"><i
                        class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>

        <?php } ?> 

    </div>

</div>

</div>

</div>


@endsection

@section('scripts')

<script type="text/javascript">

    var section_id = getCookie('section_id') || '';

    var vendors = [];

    var database = firebase.firestore();

    var email_templates = database.collection('email_templates').where('type', '==', 'payout_request');

    var emailTemplatesData = null;

    var adminEmail = '';

    var emailSetting = database.collection('settings').doc('emailSetting');

    var userName = '';
    var userContact = '';

    async function remainingPriceOLD(vendorID) {

        var paid_price = 0;

        var total_price = 0;

        var remaining = 0;

        var adminCommission = 0;

        var commission = 0;

        await database.collection('payouts').where('vendorID', '==', vendorID).get().then(async function (payoutSnapshots) {

            payoutSnapshots.docs.forEach((payout) => {

                var payoutData = payout.data();

                paid_price = parseFloat(paid_price) + parseFloat(payoutData.amount);

            })


            await database.collection('vendor_orders').where('vendor.id', '==', vendorID).where("status", "in", ["Order Completed"]).get().then(async function (orderSnapshots) {

                orderSnapshots.docs.forEach((order) => {

                    var orderData = order.data();

                    var productTotalmain = 0;

                    orderData.products.forEach((product) => {

                        var extras_price = 0;

                        if (product.price && product.quantity != 0) {

                            if (product.extras_price != undefined) {

                                extras_price = parseFloat(product.extras_price) * parseInt(product.quantity);

                            }

                            var productTotal = (parseFloat(product.price) * parseFloat(product.quantity)) + parseFloat(extras_price);


                            total_price = parseFloat(total_price) + parseFloat(productTotal);

                            productTotalmain = parseFloat(productTotalmain) + parseFloat(productTotal);

                        }

                    })

                    if (orderData.adminCommission != undefined && orderData.adminCommissionType != undefined) {

                        if (orderData.adminCommissionType == "Percent") {

                            commission = (productTotalmain * parseFloat(orderData.adminCommission)) / 100;


                        } else {

                            commission = parseFloat(orderData.adminCommission);

                        }

                    } else if (orderData.adminCommission != undefined) {

                        commission = parseFloat(orderData.adminCommission);

                    }


                })


                if (adminCommission != undefined) {

                    total_price = parseFloat(total_price) - parseFloat(commission);

                }


                remaining = parseFloat(total_price) - parseFloat(paid_price);

            });

        });

        return remaining;

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

    $(document).ready(function () {

        $("#data-table_processing").show();

        email_templates.get().then(async function (snapshots) {
            emailTemplatesData = snapshots.docs[0].data();

        });


        emailSetting.get().then(async function (snapshots) {
            var emailSettingData = snapshots.data();

            adminEmail = emailSettingData.userName;
        });

        database.collection('vendors').where('section_id', '==', section_id).get().then(async function (snapshots) {

            snapshots.docs.forEach((listval) => {

                var data = listval.data();
                vendors.push(data);
                $('#select_vendor').append($("<option></option>")

                    .attr("value", data.id)

                    .text(data.title));

            })

        });

        $("#data-table_processing").hide();

        var payoutId = "<?php echo uniqid(); ?>";

        $(".save-form-btn").click(async function () {

            <?php if ($id == '') { ?>

            var vendorID = $("#select_vendor").val();

            <?php } else { ?>

            var vendorID = "<?php echo $id; ?>";

            <?php } ?>

            var vendorUserId = await getVendorId(vendorID);
            var vendorEmail = await getVendorEmail(vendorID);

            var remaining = await remainingPrice(vendorID);

            if (remaining > 0) {

                // var amount = parseFloat($(".payout_amount").val());
                var amount = $(".payout_amount").val();

                var note = $(".payout_note").val();

                var date = new Date(Date.now());

                if (vendorID != '' && $(".payout_amount").val() != '') {

                    database.collection('payouts').doc(payoutId).set({
                        'vendorID': vendorID,
                        'amount': amount,
                        'adminNote': note,
                        'id': payoutId,
                        'paidDate': date,
                        'paymentStatus': 'Success',
                        'withdrawMethod':'bank',
                        'note':''
                    }).then(function () {

                        price = remaining - amount;

                        /* Taken off the store the payout is for, and off the
                         * account it rolls up into. */
                        database.collection('vendors').doc(vendorID).get().then(function (storeSnapshot) {
                            if (storeSnapshot.exists) {
                                userdata = storeSnapshot.data();
                                applyVendorWalletDelta(vendorID, userdata.author || '', -parseFloat(amount)).then(async function (result) {
                                    if (currencyAtRight) {
                                        amount = parseInt(amount).toFixed(decimal_degits) + "" + currentCurrency;
                                    } else {
                                        amount = currentCurrency + "" + parseInt(amount).toFixed(decimal_degits);
                                    }

                                    var formattedDate = new Date();
                                    var month = formattedDate.getMonth() + 1;
                                    var day = formattedDate.getDate();
                                    var year = formattedDate.getFullYear();

                                    month = month < 10 ? '0' + month : month;
                                    day = day < 10 ? '0' + day : day;

                                    formattedDate = day + '-' + month + '-' + year;

                                    var subject = emailTemplatesData.subject;
                                    subject = subject.replace(/{userid}/g, vendorUserId);

                                    emailTemplatesData.subject = subject;

                                    var message = emailTemplatesData.message;
                                    message = message.replace(/{userid}/g, vendorUserId);
                                    message = message.replace(/{date}/g, formattedDate);
                                    message = message.replace(/{amount}/g, amount);
                                    message = message.replace(/{payoutrequestid}/g, payoutId);
                                    message = message.replace(/{username}/g, userName);
                                    message = message.replace(/{usercontactinfo}/g, userContact);

                                    emailTemplatesData.message = message;

                                    var url = "{{url('send-email')}}";
                                    if(vendorEmail != '' && vendorEmail != null){
                                    var sendEmailStatus = await sendEmail(url, emailTemplatesData.subject, emailTemplatesData.message, [adminEmail, vendorEmail]);

                                    if (sendEmailStatus) {
                                        <?php if ($id == '') { ?>
                                        window.location.href = "{{route("payoutRequests.vendor.disbursement")}}";
                                        <?php } else { ?>
                                        window.location.href = '{{route('vendors.payout',$id)}}';
                                        <?php } ?>
                                     }
                                    }else{
                                        <?php if ($id == '') { ?>
                                        window.location.href = "{{route("payoutRequests.vendor.disbursement")}}";
                                        <?php } else { ?>
                                        window.location.href = '{{route('vendors.payout',$id)}}';
                                        <?php } ?>
                                    }

                                });

                            }
                        });

                    })

                } else {

                    $(".error_top").show();

                    $(".error_top").html("");

                    $(window).scrollTop(0);

                    $(".error_top").append("<p>{{trans('lang.please_enter_details')}}</p>");

                }

            } else {

                $(".error_top").show();

                $(window).scrollTop(0);

                $(".error_top").html("");

                $(".error_top").append("<p>{{trans('lang.vendor_insufficient_payment_error')}}</p>");

            }

        })

    })

    /* A payout is drawn on the store that earned the money. This used to look a
     * user up BY `vendorID`, which on an account with several stores returns
     * whoever happens to have that store selected. */
    async function remainingPrice(vendorID) {
        return await storeWalletAmount(vendorID);
    }

    async function getVendorId(vendorUser) {
        var userId = '';

        await database.collection('vendors').where('id', "==", vendorUser).get().then(async function (vendorSnapshots) {
            var vendorData = vendorSnapshots.docs[0].data();
            userId = vendorData.author;

        });

        return userId;
    }

    async function getVendorEmail(vendorUser) {
        var userEmail = '';

        await database.collection('users').where('vendorID', "==", vendorUser).where('role','==','vendor').get().then(async function (vendorSnapshots) {

            if (vendorSnapshots.docs[0]) {
                var vendorData = vendorSnapshots.docs[0].data();
                userEmail = vendorData.email;

                userName = vendorData.firstName + " " + vendorData.lastName;
                userContact = vendorData.phoneNumber;
            }

        });

        return userEmail;
    }

</script>


@endsection