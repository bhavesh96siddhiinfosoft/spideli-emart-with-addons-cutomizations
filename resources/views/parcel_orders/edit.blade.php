@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">

        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.parcel_plural')}} {{trans('lang.order_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{{route('parcel_orders')}}">{{trans('lang.parcel_plural')}}
                        {{trans('lang.order_plural')}}</a></li>
                <li class="breadcrumb-item">{{trans('lang.order_edit')}}</li>
            </ol>
        </div>
    </div>

    <div class="card-body">

        <?php if (in_array('parcel.orders.print', json_decode(@session('user_permissions'),true))) { ?>

            <div class="text-right print-btn non-printable">
                <button type="button" class="fa fa-print" onclick="PrintElem('printableArea')"></button>
            </div>
        <?php } ?>

        <div class="order_detail printableArea" id="order_detail">
            <div class="order_detail-top">
                <div class="row">
                    <div class="order_edit-genrl col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-header-title">{{trans('lang.general_details')}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="order_detail-top-box">

                                    <div class="form-group row widt-100 gendetail-col " id="div_parcel_category">
                                        <label class="col-12 control-label"><strong>{{trans('lang.parcel_category')}}:
                                            </strong><span id="parcel_type"></span></label>
                                    </div>

                                    {{-- Point 41: how this parcel's price was reached. Filled from the
                                         order's own priceBreakdown and fields; hidden for orders placed
                                         before the app recorded them. See docs/app-spec-parcel-pricing.md. --}}
                                    <div id="div_parcel_pricing" style="display:none;">
                                        <div class="form-group row widt-100 gendetail-col" data-pricing-row="scope">
                                            <label class="col-12 control-label"><strong>{{ trans('lang.parcel_scope') }}: </strong><span id="parcel_scope"></span></label>
                                        </div>
                                        <div class="form-group row widt-100 gendetail-col" data-pricing-row="source">
                                            <label class="col-12 control-label"><strong>{{ trans('lang.parcel_price_source') }}: </strong><span id="parcel_price_source"></span></label>
                                        </div>
                                        <div class="form-group row widt-100 gendetail-col" data-pricing-row="weight">
                                            <label class="col-12 control-label"><strong>{{ trans('lang.parcel_weight_detail') }}: </strong><span id="parcel_weight_detail"></span></label>
                                        </div>
                                        <div class="form-group row widt-100 gendetail-col" data-pricing-row="dimensions">
                                            <label class="col-12 control-label"><strong>{{ trans('lang.parcel_dimensions') }}: </strong><span id="parcel_dimensions"></span></label>
                                        </div>
                                        <div class="form-group row widt-100 gendetail-col" data-pricing-row="collection">
                                            <label class="col-12 control-label"><strong>{{ trans('lang.parcel_collection') }}: </strong><span id="parcel_collection"></span></label>
                                        </div>
                                        <div class="form-group row widt-100 gendetail-col" data-pricing-row="delivery">
                                            <label class="col-12 control-label"><strong>{{ trans('lang.parcel_delivery') }}: </strong><span id="parcel_delivery"></span></label>
                                        </div>
                                    </div>

                                    <div class="form-group row widt-100 gendetail-col">
                                        <label class="col-12 control-label"><strong>{{trans('lang.date_created')}}
                                                : </strong><span id="createdAt"></span></label>

                                    </div>

                                    <div class="form-group row widt-100 gendetail-col payment_method">
                                        <label class="col-12 control-label"><strong>{{trans('lang.payment_methods')}}
                                                : </strong>
                                            <span id="payment_method"></span></label>

                                    </div>

                                    <div class="form-group row width-100 non-printable">
                                        <label class="col-3 control-label"><strong></strong>{{trans('lang.status')}}
                                            :</strong></label>
                                        <div class="col-7">
                                            {{-- 13 Oct (app developer's PARCEL-MANAGEMENT-FLOW.md 2.3): the status is
     moved by the apps - dispatch, the company owner, the driver. The old
     free list (copied from food orders) let the admin set statuses the apps
     never use, and skip the driver's completion and wallet credit. The
     admin now only CANCELS, under Actions on the right. --}}
                                            <span id="order_status_text" class="font-weight-bold"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="order-items-list mt-4">
                            <div class="card">
                                <div class="card-body">
                                    <table cellpadding="0" cellspacing="0" class="table table-striped table-valign-middle">

                                        <thead>
                                            <tr>

                                                <th>{{trans('lang.parcel_weight')}}</th>
                                                <th>{{trans('lang.item_review_rate')}}</th>
                                                <th>{{trans('lang.parcel_distance')}}</th>
                                                <th>{{trans('lang.total')}}</th>
                                            </tr>

                                        </thead>

                                        <tbody id="order_products">

                                        </tbody>
                                    </table>
                                    <div class="form-group row width-50">

                                       <label class="col-12 control-label"><strong>{{ trans('lang.parcel_image') }}</strong></label>

                                        <div class="placeholder_img_thumb user_image">
                                            <img class="rounded" style="width:50px" src="" alt="Parcel_image" id="parcel_image">
                                            <span id="no-image-text" style="display:none; color:gray;">{{ trans('lang.parcel_images_not_found') }}</span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="order-data-row order-totals-items">
                            <div class="card">
                                <div class="card-body">
                                    <table class="order-totals">

                                        <tbody id="order_products_total">

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>



                    </div>

                    <div class="order_edit-genrl col-md-5">
                        {{-- The parcel's tracking QR - the same code as on the
                             customer's receipt, opening the public tracking page. --}}
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-header-title">{{ trans('lang.parcel_tracking_qr') }}</h4>
                            </div>
                            <div class="card-body text-center">
                                <div id="parcel_track_qr" class="d-inline-block p-2 bg-white"></div>
                                <div class="mt-2 non-printable">
                                    <a id="parcel_track_link" href="#" target="_blank" rel="noopener" class="small text-break"></a>
                                </div>
                            </div>
                        </div>
                        {{-- What the apps record on a parcel as it moves (app developer's
                             PARCEL-MANAGEMENT-FLOW.md, 13 Oct): codes, the delivery company
                             and its owner, who was offered it and who refused it, the proof
                             of delivery, a cancellation, and the dated history. Read-only. --}}
                        <div class="card" id="parcel_flow_card">
                            <div class="card-header">
                                <h4 class="card-header-title">{{ trans('lang.parcel_flow_title') }}</h4>
                            </div>
                            <div class="card-body">
                                <div id="parcel_flow_owner_warning" class="alert alert-warning py-2 small" style="display:none;">{{ trans('lang.parcel_flow_no_owner') }}</div>
                                <table class="table table-sm mb-2 small parcel-flow-table"><tbody id="parcel_flow_rows"></tbody></table>
                                <div id="parcel_flow_rejections_wrap" style="display:none;">
                                    <h6 class="font-weight-bold mt-3">{{ trans('lang.parcel_flow_rejections') }}</h6>
                                    <ul class="list-unstyled small mb-0" id="parcel_flow_rejections"></ul>
                                </div>
                                <div id="parcel_flow_history_wrap" style="display:none;">
                                    <h6 class="font-weight-bold mt-3">{{ trans('lang.parcel_flow_history') }}</h6>
                                    <ul class="list-unstyled small mb-0" id="parcel_flow_history"></ul>
                                </div>
                                {{-- Steps only the admin / pickup point can record, and the
                                     price of a quote. Each shows only when it applies. --}}
                                <div id="parcel_actions" class="non-printable" style="display:none;">
                                    <h6 class="font-weight-bold mt-3">{{ trans('lang.parcel_actions') }}</h6>
                                    <div class="parcel-action-error alert alert-danger py-2 small" style="display:none;"></div>

                                    <div id="parcel_action_origin" class="mb-2" style="display:none;">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="parcel_mark_origin">
                                            <i class="fa fa-map-marker mr-1"></i>{{ trans('lang.parcel_mark_origin') }}
                                        </button>
                                        <div class="form-text text-muted small">{{ trans('lang.parcel_mark_origin_help') }}</div>
                                    </div>

                                    <div id="parcel_action_collect" class="mb-2" style="display:none;">
                                        <label class="small font-weight-bold mb-1" for="parcel_collect_code">{{ trans('lang.parcel_collect_code') }}</label>
                                        <div class="d-flex">
                                            <input type="text" class="form-control form-control-sm mr-2" id="parcel_collect_code" maxlength="10" autocomplete="off">
                                            <button type="button" class="btn btn-primary btn-sm text-nowrap" id="parcel_mark_collected">{{ trans('lang.parcel_mark_collected') }}</button>
                                        </div>
                                        <div class="form-text text-muted small">{{ trans('lang.parcel_mark_collected_help') }}</div>
                                    </div>

                                    <div id="parcel_action_cancel" class="mb-2 border-top pt-2" style="display:none;">
                                        <label class="small font-weight-bold mb-1" for="parcel_cancel_reason">{{ trans('lang.parcel_cancel_reason') }}</label>
                                        <textarea class="form-control form-control-sm mb-2" id="parcel_cancel_reason" rows="2"></textarea>
                                        <div id="parcel_cancel_refund_wrap" style="display:none;">
                                            <label class="small font-weight-bold mb-1" for="parcel_cancel_refund">{{ trans('lang.parcel_cancel_refund') }} <span class="parcel-cancel-currency text-muted"></span></label>
                                            <input type="number" min="0" step="any" class="form-control form-control-sm mb-1" id="parcel_cancel_refund">
                                            <div class="form-text text-muted small mb-2" id="parcel_cancel_refund_source"></div>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm" id="parcel_cancel_btn"><i class="fa fa-ban mr-1"></i>{{ trans('lang.parcel_cancel') }}</button>
                                        <div class="form-text text-muted small">{{ trans('lang.parcel_cancel_help') }}</div>
                                    </div>

                                    <div id="parcel_action_quote" class="mb-2" style="display:none;">
                                        <label class="small font-weight-bold mb-1" for="parcel_quote_price">{{ trans('lang.parcel_quote_price') }} <span id="parcel_quote_currency" class="text-muted"></span></label>
                                        <div class="d-flex">
                                            <input type="number" min="0" step="any" class="form-control form-control-sm mr-2" id="parcel_quote_price">
                                            <button type="button" class="btn btn-primary btn-sm text-nowrap" id="parcel_save_quote">{{ trans('lang.parcel_save_quote') }}</button>
                                        </div>
                                        <div class="form-text text-muted small">{{ trans('lang.parcel_quote_help') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-header-title">{{ trans('lang.sender_details')}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="address order_detail-top-box">

                                    <div class="form-group row widt-100 gendetail-col ">
                                        <label class="col-12 control-label"><strong>{{trans('lang.sender_name')}}:</strong>
                                            <span id="sender_name"></span></label>

                                    </div>
                                    <div class="form-group row widt-100 gendetail-col ">
                                        <label class="col-12 control-label"><strong>{{trans('lang.sender_address')}}
                                                :</strong>
                                            <span id="sender_address"></span></label>

                                    </div>
                                    <div class="form-group row widt-100 gendetail-col ">
                                        <label class="col-12 control-label"><strong>{{trans('lang.date')}}:</strong>
                                            <span id="sender_datetime"></span></label>

                                    </div>

                                    <p><strong>{{trans('lang.phone')}}:</strong>
                                        <span id="sender_phone"></span>
                                    </p>
                                    <p><strong>{{trans('lang.note')}}:</strong>
                                        <span id="sender_note"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h4 class="card-header-title">{{ trans('lang.receiver_details')}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="address order_detail-top-box">
                                    <div class="form-group row widt-100 gendetail-col ">
                                        <label class="col-12 control-label"><strong>{{trans('lang.receiver_name')}}
                                                :</strong>
                                            <span id="receiver_name"></span></label>

                                    </div>
                                    <div class="form-group row widt-100 gendetail-col ">
                                        <label class="col-12 control-label"><strong>{{trans('lang.receiver_address')}}
                                                :</strong>
                                            <span id="receiver_address"></span></label>

                                    </div>
                                    <div class="form-group row widt-100 gendetail-col ">
                                        <label class="col-12 control-label"><strong>{{trans('lang.date')}}:</strong>
                                            <span id="receiver_datetime"></span></label>

                                    </div>


                                    <p><strong>{{trans('lang.phone')}}:</strong>
                                        <span id="receiver_phone"></span>
                                    </p>
                                    <p><strong>{{trans('lang.note')}}:</strong>
                                        <span id="receiver_note"></span>
                                    </p>
                                    <p><strong>{{trans('lang.receiver_sms_notification')}}:</strong>
                                        <span id="receiver_sms_status"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="order-deta-btm-right driver_details_hide mt-4">

                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-header-title">{{ trans('lang.driver_detail')}}</h4>
                                </div>

                                <div class="card-body">

                                    <div class="address order_detail-top-box">
                                        <p><strong>{{trans('lang.driver_name')}}:</strong>
                                            <span id="driver_firstName"></span> <span id="driver_lastName"></span><br>
                                        </p>
                                        <p><strong>{{trans('lang.email_address')}}:</strong>
                                            <span id="driver_email"></span>
                                        </p>
                                        <p><strong>{{trans('lang.phone')}}:</strong>
                                            <span id="driver_phone"></span>
                                        </p>
                                        {{-- <p id="para_carName"><strong>{{trans('lang.car_name')}}:</strong>
                                            <span id="driver_carName"></span>
                                        </p>
                                        <p><strong>{{trans('lang.car_number')}}:</strong>
                                            <span id="driver_carNumber"></span>
                                        </p> --}}
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="order_detail-review col-md-12 mt-4 non-printable">
                        <div class="row">
                            <div class="rental-review col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-header-title">{{trans('lang.customer_reviews')}}</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="review-inner">
                                            <div id="customers_rating_and_review">

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

    </div>

</div>

</div>

</div>

</div>

@endsection

@section('style')

@section('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script type="text/javascript">
    var id_rendom = "<?php echo uniqid(); ?>";
    var adminCommission = 0;
    var id = "<?php echo $id; ?>";
    var fcmToken = '';

    /* The parcel's tracking QR (Document 1). The tracking page is on the
     * customer WEBSITE, at /track-parcel/{id}.
     *
     * The link is the ORDER'S OWN qrValue - the exact link the app (since
     * 5 Oct) and the website (since 12 Oct) put in the customer's QR - so the
     * admin's code is the customer's code. Guessing the website from this
     * page's address was wrong wherever the admin and the website are on
     * different hosts or ports (13 Oct: a 404 on the local copy). Only an
     * older parcel without a link falls back to WEBSITE_URL, then this page's
     * own origin (live, the admin is spideli.com/admin). */
    var parcelTrackBase = "{{ rtrim((string) env('WEBSITE_URL', ''), '/') }}" || window.location.origin;

    function parcelTrackUrlFor(order) {
        var qr = order && typeof order.qrValue === 'string' ? order.qrValue.trim() : '';
        if (/^https?:\/\//i.test(qr)) {
            return qr;
        }
        return parcelTrackBase + '/track-parcel/' + id;
    }

    /* ---- The parcel's flow (codes, company, offers, proof, history) ------ */
    var parcelFlowNames = {};

    async function parcelFlowName(uid) {
        if (!uid) {
            return '';
        }
        if (parcelFlowNames[uid] === undefined) {
            parcelFlowNames[uid] = '';
            try {
                var snap = await database.collection('users').doc(uid).get();
                if (snap.exists) {
                    var u = snap.data();
                    parcelFlowNames[uid] = ((u.firstName || '') + ' ' + (u.lastName || '')).trim() || u.companyName || u.email || '';
                }
            } catch (err) {
                console.error('user could not be read', err);
            }
        }
        return parcelFlowNames[uid] || uid;
    }

    function parcelFlowDate(value) {
        if (!value) {
            return '';
        }
        var d = (typeof value.toDate === 'function') ? value.toDate() : new Date(value);
        return isNaN(d.getTime()) ? '' : d.toLocaleString();
    }

    function parcelFlowRow(label, value, isHtml) {
        if (value === undefined || value === null || value === '') {
            return;
        }
        var $td = $('<td>');
        if (isHtml) {
            $td.html(value);
        } else {
            $td.text(value);
        }
        $('#parcel_flow_rows').append($('<tr>').append($('<th class="text-nowrap pr-3">').text(label), $td));
    }

    async function renderParcelFlow(order) {
        $('#parcel_flow_rows, #parcel_flow_rejections, #parcel_flow_history').empty();

        parcelFlowRow("{{ trans('lang.parcel_flow_tracking_number') }}", order.trackingNumber);
        parcelFlowRow("{{ trans('lang.parcel_flow_receiver_code') }}", order.pickupCode);
        parcelFlowRow("{{ trans('lang.parcel_flow_step') }}", order.parcelStatus);

        if (order.carrierId || order.carrierName) {
            parcelFlowRow("{{ trans('lang.parcel_flow_carrier') }}", order.carrierName || order.carrierId);
            var ownerId = order.carrierOwnerId || order.companyOwnerId || '';
            if (ownerId) {
                var ownerName = await parcelFlowName(ownerId);
                var ownerUrl = "{{ route('owners.view', ':id') }}".replace(':id', ownerId);
                parcelFlowRow("{{ trans('lang.parcel_flow_owner') }}", $('<a target="_blank">').attr('href', ownerUrl).text(ownerName).prop('outerHTML'), true);
            }
            /* A company parcel with no owner reaches nobody: the owner's app
               finds its parcels by this id. */
            $('#parcel_flow_owner_warning').toggle(!ownerId);
        } else {
            parcelFlowRow("{{ trans('lang.parcel_flow_carrier') }}", "{{ trans('lang.parcel_flow_spideli') }}");
        }

        if (order.assignedBy) {
            parcelFlowRow("{{ trans('lang.parcel_flow_assigned_by') }}",
                (await parcelFlowName(order.assignedBy)) + (order.assignedAt ? ' - ' + parcelFlowDate(order.assignedAt) : ''));
        }
        parcelFlowRow("{{ trans('lang.parcel_flow_offered_at') }}", parcelFlowDate(order.dispatchedAt));

        if (order.deliveryProof) {
            var proof = order.deliveryProof;
            var proofText = (proof.type === 'otp' ? "{{ trans('lang.parcel_flow_proof_code') }}" : "{{ trans('lang.parcel_flow_proof_photo') }}") +
                (proof.at ? ' - ' + parcelFlowDate(proof.at) : '');
            var proofHtml = $('<span>').text(proofText).prop('outerHTML');
            if (proof.photoUrl) {
                proofHtml += ' <a target="_blank" rel="noopener" href="' + encodeURI(proof.photoUrl) + '">{{ trans('lang.view') }}</a>';
            }
            parcelFlowRow("{{ trans('lang.parcel_flow_proof') }}", proofHtml, true);
        }

        if (order.cancelledAt || order.cancelReason) {
            var who = order.cancelledByName || (order.cancelledBy ? await parcelFlowName(order.cancelledBy) : '');
            parcelFlowRow("{{ trans('lang.parcel_flow_cancelled') }}",
                [who, order.cancelReason, parcelFlowDate(order.cancelledAt)].filter(Boolean).join(' - '));
        }

        var rejections = Array.isArray(order.driverRejections) ? order.driverRejections : [];
        for (var i = 0; i < rejections.length; i++) {
            var r = rejections[i] || {};
            var name = await parcelFlowName(r.driverId);
            $('#parcel_flow_rejections').append($('<li class="mb-1">').text(
                [name, r.reason || r.code, parcelFlowDate(r.at)].filter(Boolean).join(' - ')));
        }
        $('#parcel_flow_rejections_wrap').toggle(rejections.length > 0);

        var events = (Array.isArray(order.trackingEvents) ? order.trackingEvents.slice() : []).filter(function (e) { return e && e.status; });
        var time = function (e) {
            var v = e.at;
            var d = v && typeof v.toDate === 'function' ? v.toDate() : (v ? new Date(v) : null);
            return d && !isNaN(d.getTime()) ? d.getTime() : 0;
        };
        events.sort(function (a, b) { return time(b) - time(a); });
        events.forEach(function (e) {
            $('#parcel_flow_history').append($('<li class="mb-1">').append(
                $('<strong>').text(e.status),
                $('<span class="text-muted">').text(' - ' + parcelFlowDate(e.at) + (e.role ? ' (' + e.role + ')' : '') + (e.note ? ' - ' + e.note : ''))
            ));
        });
        $('#parcel_flow_history_wrap').toggle(events.length > 0);

        renderParcelActions(order);
    }

    /* ---- Admin / pickup-point steps and the quote price --------------------
     * From the app developer's PARCEL-MANAGEMENT-FLOW.md 2.3 and 9.3 (M10):
     * "At origin pickup point" (the sender dropped the parcel at the point) and
     * "Delivered" at the destination point (the receiver collected it, with the
     * receiver code) are recorded only by the admin / pickup point; a "Quote
     * Requested" parcel waits for the admin's price (manualPrice), which the
     * customer then pays in the app. Steps are ranked as in the apps and never
     * written backwards. */
    var PARCEL_STEP_RANK = {
        'Created': 0, 'Paid': 1, 'Waiting drop-off': 1, 'Collected': 2, 'At origin pickup point': 2,
        'In transit': 3, 'Arrived destination': 4, 'Out for delivery': 5, 'At destination pickup point': 5,
        'Delivered': 6, 'Returned': 7, 'Cancelled': 7
    };
    var PARCEL_CLOSED = ['Order Cancelled', 'Order Rejected', 'Quote Requested'];

    function parcelStepRank(step) {
        return PARCEL_STEP_RANK.hasOwnProperty(step) ? PARCEL_STEP_RANK[step] : 0;
    }

    function parcelCanMarkOrigin(o) {
        return o.pickupMethod === 'pickup_point' && PARCEL_CLOSED.indexOf(o.status) === -1 &&
            o.status !== 'Order Completed' && parcelStepRank(o.parcelStatus) < 2;
    }

    function parcelCanMarkCollected(o) {
        return o.deliveryMethod === 'pickup_point' && o.parcelStatus === 'At destination pickup point';
    }

    function parcelCanQuote(o) {
        return o.status === 'Quote Requested';
    }

    /* Cancel - the states the app developer's flow lets the customer or the
     * admin cancel from (2.3): before a driver has accepted. */
    var PARCEL_CANCELLABLE = ['Quote Requested', 'Order Placed', 'Driver Pending', 'Driver Rejected'];

    function parcelCanCancel(o) {
        return PARCEL_CANCELLABLE.indexOf(o.status) !== -1;
    }

    /* Paid = the customer's money was taken at booking: not cash, not "receiver
       pays", and not a quote that was never paid. */
    function parcelWasPaid(o) {
        return o.status !== 'Quote Requested' && o.payment_method !== 'cod' && o.paymentCollectByReceiver !== true;
    }

    var parcelRefundSuggested = null;

    /* What to refund: what was actually taken from the customer's wallet for
       this order (the app's "Parcel Amount debited" rows), else the total the
       order recorded. The admin sees it and can correct it. */
    async function parcelSuggestRefund(o) {
        try {
            var rows = await database.collection('wallet').where('order_id', '==', id).get();
            var debited = 0, refunded = 0;
            rows.docs.forEach(function (d) {
                var w = d.data(), amt = parseFloat(w.amount) || 0;
                if (w.isTopUp === true) { refunded += amt; } else { debited += amt; }
            });
            if (refunded > 0) {
                return { amount: 0, source: "{{ trans('lang.parcel_cancel_already_refunded') }}" };
            }
            if (debited > 0) {
                return { amount: debited, source: "{{ trans('lang.parcel_cancel_from_wallet') }}" };
            }
        } catch (err) {
            console.error('wallet rows could not be read', err);
        }
        var total = o.priceBreakdown && parseFloat(o.priceBreakdown.total);
        if (total > 0) {
            return { amount: total, source: "{{ trans('lang.parcel_cancel_from_total') }}" };
        }
        return { amount: '', source: "{{ trans('lang.parcel_cancel_enter_amount') }}" };
    }

    function renderParcelActions(order) {
        var cancel = parcelCanCancel(order);
        $('#parcel_action_cancel').toggle(cancel);
        if (cancel) {
            var paid = parcelWasPaid(order);
            $('#parcel_cancel_refund_wrap').toggle(paid);
            $('.parcel-cancel-currency').text(currentCurrency ? '(' + currentCurrency + ')' : '');
            if (paid) {
                parcelSuggestRefund(order).then(function (r) {
                    parcelRefundSuggested = r;
                    $('#parcel_cancel_refund').val(r.amount);
                    $('#parcel_cancel_refund_source').text(r.source);
                });
            }
        }
        var origin = parcelCanMarkOrigin(order), collect = parcelCanMarkCollected(order), quote = parcelCanQuote(order);
        $('#parcel_action_origin').toggle(origin);
        $('#parcel_action_collect').toggle(collect);
        $('#parcel_action_quote').toggle(quote);
        if (quote) {
            $('#parcel_quote_price').val(order.manualPrice !== undefined && order.manualPrice !== null ? order.manualPrice : '');
            $('#parcel_quote_currency').text(currentCurrency ? '(' + currentCurrency + ')' : '');
        }
        $('.parcel-action-error').hide();
        $('#parcel_actions').toggle(origin || collect || quote || cancel);
    }

    /* Re-reads the parcel in a transaction, so a step a driver recorded a
       moment ago is never overwritten. `check` returns '' or the refusal. */
    async function parcelAdminStep(check, fields, event) {
        var docRef = database.collection('parcel_orders').doc(id);
        await database.runTransaction(async function (tx) {
            var snap = await tx.get(docRef);
            if (!snap.exists) {
                throw new Error("{{ trans('lang.parcel_action_changed') }}");
            }
            var problem = check(snap.data());
            if (problem) {
                throw new Error(problem);
            }
            var update = Object.assign({}, fields);
            if (event) {
                update.parcelStatus = event.status;
                update.trackingEvents = firebase.firestore.FieldValue.arrayUnion(Object.assign({
                    at: firebase.firestore.Timestamp.now(), by: 'admin', role: 'admin'
                }, event));
            }
            tx.update(docRef, update);
        });
    }

    function parcelActionFailed(err) {
        $('.parcel-action-error').text(err && err.message ? err.message : String(err)).show();
    }

    $(document).on('click', '#parcel_mark_origin', async function () {
        if (!confirm("{{ trans('lang.parcel_mark_origin') }}?")) {
            return;
        }
        var $b = $(this).prop('disabled', true);
        try {
            await parcelAdminStep(function (o) {
                return parcelCanMarkOrigin(o) ? '' : "{{ trans('lang.parcel_action_changed') }}";
            }, { atOriginPointAt: firebase.firestore.FieldValue.serverTimestamp() },
               { status: 'At origin pickup point', note: "{{ trans('lang.parcel_mark_origin') }}" });
            window.location.reload();
        } catch (err) {
            parcelActionFailed(err);
            $b.prop('disabled', false);
        }
    });

    $(document).on('click', '#parcel_mark_collected', async function () {
        var code = $.trim($('#parcel_collect_code').val());
        var $b = $(this).prop('disabled', true);
        try {
            await parcelAdminStep(function (o) {
                if (!parcelCanMarkCollected(o)) {
                    return "{{ trans('lang.parcel_action_changed') }}";
                }
                /* The receiver code is the proof, as at a home delivery. A
                   parcel without one (booked before codes existed) needs none. */
                if (o.pickupCode && String(o.pickupCode) !== code) {
                    return "{{ trans('lang.parcel_collect_wrong_code') }}";
                }
                return '';
            }, { collectedAtPointAt: firebase.firestore.FieldValue.serverTimestamp() },
               { status: 'Delivered', note: "{{ trans('lang.parcel_mark_collected') }}" });
            window.location.reload();
        } catch (err) {
            parcelActionFailed(err);
            $b.prop('disabled', false);
        }
    });

    /* The refund as the customer reads it: in the order's currency. */
    function parcelMoney(amount) {
        var n = (parseFloat(amount) || 0).toFixed(decimal_degits || 0);
        return currencyAtRight ? n + currentCurrency : currentCurrency + n;
    }

    function parcelPush(token, title, message) {
        if (!token) {
            return;
        }
        $.ajax({
            type: 'POST', url: "{{ route('advertisement.sendnotification') }}",
            data: { _token: "{{ csrf_token() }}", fcm: token, title: title, message: message }
        });
    }

    async function parcelTokenOf(uid) {
        if (!uid) {
            return '';
        }
        try {
            var snap = await database.collection('users').doc(uid).get();
            return snap.exists ? (snap.data().fcmToken || '') : '';
        } catch (err) {
            return '';
        }
    }

    $(document).on('click', '#parcel_cancel_btn', async function () {
        var reason = $.trim($('#parcel_cancel_reason').val());
        if (!reason) {
            parcelActionFailed({ message: "{{ trans('lang.parcel_cancel_reason_required') }}" });
            return;
        }
        var refundShown = $('#parcel_cancel_refund_wrap').is(':visible');
        var refund = refundShown ? parseFloat($('#parcel_cancel_refund').val()) : 0;
        if (refundShown && (isNaN(refund) || refund < 0)) {
            parcelActionFailed({ message: "{{ trans('lang.parcel_cancel_refund_invalid') }}" });
            return;
        }
        if (!confirm("{{ trans('lang.parcel_cancel_confirm') }}" + (refund > 0 ? ' ' + "{{ trans('lang.parcel_cancel_refund') }}" + ': ' + refund : ''))) {
            return;
        }
        var $b = $(this).prop('disabled', true);
        var docRef = database.collection('parcel_orders').doc(id);
        var done = null;
        try {
            await database.runTransaction(async function (tx) {
                var snap = await tx.get(docRef);
                var o = snap.exists ? snap.data() : null;
                if (!o || !parcelCanCancel(o)) {
                    throw new Error("{{ trans('lang.parcel_action_changed') }}");
                }
                var update = {
                    status: 'Order Cancelled',
                    parcelStatus: 'Cancelled',
                    cancelReason: reason,
                    cancelReasonCode: 'admin',
                    cancelledBy: 'admin',
                    cancelledByName: "{{ trans('lang.parcel_cancel_by_admin') }}",
                    cancelledAt: firebase.firestore.FieldValue.serverTimestamp(),
                    cancelAction: 'cancelled',
                    driverId: null,
                    driverID: null,
                    trackingEvents: firebase.firestore.FieldValue.arrayUnion({
                        status: 'Cancelled', at: firebase.firestore.Timestamp.now(), by: 'admin', role: 'admin', note: reason
                    })
                };
                /* An offered driver must lose the offer, as on a customer cancel. */
                if (o.status === 'Driver Pending' && o.driverId) {
                    tx.update(database.collection('users').doc(o.driverId), {
                        orderRequestData: firebase.firestore.FieldValue.arrayRemove(id)
                    });
                }
                /* The refund, in the same shape as the app's own refund, and in
                   the same transaction: never cancelled without it. */
                if (refund > 0 && parcelWasPaid(o) && o.authorID) {
                    var walletId = database.collection('wallet').doc().id;
                    tx.set(database.collection('wallet').doc(walletId), {
                        id: walletId, user_id: o.authorID, order_id: id, amount: refund, isTopUp: true,
                        payment_method: 'wallet', payment_status: 'success', transactionUser: 'customer',
                        note: 'Refund for cancelled parcel order', regionId: o.regionId || '',
                        date: firebase.firestore.FieldValue.serverTimestamp()
                    });
                    tx.update(database.collection('users').doc(o.authorID), {
                        wallet_amount: firebase.firestore.FieldValue.increment(refund)
                    });
                    update.refundAmount = refund;
                }
                tx.update(docRef, update);
                done = o;
            });
        } catch (err) {
            parcelActionFailed(err);
            $b.prop('disabled', false);
            return;
        }

        /* Tell the people who were waiting on it. Best effort - the cancel and
           the refund are already saved. */
        var label = done.trackingNumber || id;
        var msg = "{{ trans('lang.parcel_cancel_push_message') }}".replace(':parcel', label).replace(':reason', reason);
        parcelPush(await parcelTokenOf(done.authorID), "{{ trans('lang.parcel_cancel_push_title') }}",
            msg + (refund > 0 ? ' ' + "{{ trans('lang.parcel_cancel_push_refund') }}".replace(':amount', parcelMoney(refund)) : ''));
        parcelPush(await parcelTokenOf(done.carrierOwnerId || done.companyOwnerId), "{{ trans('lang.parcel_cancel_push_title') }}", msg);
        if (done.status === 'Driver Pending') {
            parcelPush(await parcelTokenOf(done.driverId), "{{ trans('lang.parcel_cancel_push_title') }}", msg);
        }
        setTimeout(function () { window.location.reload(); }, 800);
    });

    $(document).on('click', '#parcel_save_quote', async function () {
        var raw = $('#parcel_quote_price').val();
        var price = parseFloat(raw);
        if (raw === '' || isNaN(price) || price <= 0) {
            parcelActionFailed({ message: "{{ trans('lang.parcel_quote_invalid') }}" });
            return;
        }
        var $b = $(this).prop('disabled', true);
        try {
            await parcelAdminStep(function (o) {
                return parcelCanQuote(o) ? '' : "{{ trans('lang.parcel_action_changed') }}";
            }, { manualPrice: price, quotedAt: firebase.firestore.FieldValue.serverTimestamp() }, null);
            window.location.reload();
        } catch (err) {
            parcelActionFailed(err);
            $b.prop('disabled', false);
        }
    });

    function drawParcelTrackQr(url) {
        $('#parcel_track_link').attr('href', url).text(url);
        $('#parcel_track_qr').empty();
        try {
            new QRCode(document.getElementById('parcel_track_qr'), {
                text: url, width: 160, height: 160, correctLevel: QRCode.CorrectLevel.M
            });
        } catch (err) {
            console.error('tracking QR could not be drawn', err);
        }
    }
    var old_order_status = '';
    var payment_shared = false;
    var vendorname = '';
    var vendorId = '';
    
    var deliveryChargeVal = 0;
    var tip_amount_val = 0;
    var tip_amount = 0;
    var total_price_val = 0;
    var adminCommission_val = 0;
    var database = firebase.firestore();
    var ref = database.collection('parcel_orders').where("id", "==", id);
    var page_size = 5;
    var refUserReview = database.collection('items_review').where('orderid', '==', id);
    var append_procucts_list = '';
    var append_procucts_total = '';
    var total_price = 0;
    var currentCurrency = '';
    var currencyAtRight = false;
    var refCurrency = regionCurrencyRef();
    var orderPreviousStatus = '';
    var orderPaymentMethod = '';
    var orderCustomerId = '';
    var orderPaytableAmount = 0;
    var orderTakeAwayOption = false;
    var manfcmTokenVendor = '';
    var manname = '';
    var decimal_degits = 0;
    var vendorAuthor = '';
    var currencyData = '';
    refCurrency.get().then(async function(snapshots) {
        currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;
        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });


    var geoFirestore = new GeoFirestore(database);
    var place_image = '';
    var ref_place = database.collection('settings').doc("placeHolderImage");
    ref_place.get().then(async function(snapshots) {
        var placeHolderImage = snapshots.data();
        place_image = placeHolderImage.image;
    });

    var user_permissions = '<?php echo @session('user_permissions') ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkPrintPermission = false;
    if ($.inArray('parcel.orders.print', user_permissions) >= 0) {
        checkPrintPermission = true;
    }

    let taxBreakdownGrouped = {
        order: {},
        platform: {}
    };

    $(document).ready(function() {

        //hide this status for admin
        $('#order_placed').hide();
        $('#driver_pending').hide();
        $('#driver_rejected').hide();
        $('#order_shipped').hide();
        $('#in_transit').hide();
        $('#order_completed').hide();

        var alovelaceDocumentRef = database.collection('parcel_orders').doc();
        if (alovelaceDocumentRef.id) {
            id_rendom = alovelaceDocumentRef.id;
        }

        $(document.body).on('click', '.redirecttopage', function() {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        jQuery("#data-table_processing").show();

        ref.get().then(async function(snapshots) {
            var order = snapshots.docs[0].data();
            drawParcelTrackQr(parcelTrackUrlFor(order));

            /* Every amount on this screen is in THE ORDER'S region currency.
             * It used to take the currency of whichever region the admin was
             * browsing, so an India parcel read in FCFA while working in
             * Cameroun. Set before anything below formats a price. */
            var orderCurrency = await currencyOfOrder(order);
            if (orderCurrency) {
                currencyData = orderCurrency;
                currentCurrency = orderCurrency.symbol;
                currencyAtRight = orderCurrency.symbolAtRight;
                decimal_degits = (orderCurrency.decimal_degits !== undefined && orderCurrency.decimal_degits !== null)
                    ? orderCurrency.decimal_degits : decimal_degits;
            }

            /* After the order's own currency is set - the quote price shows it. */
            renderParcelFlow(order).catch(function (err) { console.error('parcel flow could not be shown', err); });
            getUserReview(order);
            append_procucts_list = document.getElementById('order_products');
            append_procucts_list.innerHTML = '';


            append_procucts_total = document.getElementById('order_products_total');
            append_procucts_total.innerHTML = '';

            $("#sender_name").text(order.sender.name);
            $("#sender_address").text(order.sender.address);
            $("#sender_note").text(order.note ? order.note : '');
            $("#receiver_note").text(order.receiverNote ? order.receiverNote : '');
          if (Array.isArray(order.parcelImages) && order.parcelImages.length > 0 && order.parcelImages[0].trim() !== '') {
                $("#parcel_image").attr("src", order.parcelImages[0]).show();
                $("#no-image-text").hide();
            } else {
                $("#parcel_image").hide();
                $("#no-image-text").text("Parcel Images Not Found").show();
            }
           
            var date = "";
            var time = "";
            if (order.senderPickupDateTime) {
                date = order.senderPickupDateTime.toDate().toDateString();
                time = order.senderPickupDateTime.toDate().toLocaleTimeString();
            }
            $("#sender_datetime").text(date + " " + time);


            let cleanedPhone = order.sender.phone.replace(/[()\s]/g, '').replace(/^(\+)?/, '+');


            if (cleanedPhone.startsWith('+')) {
                $("#sender_phone").text(EditPhoneNumber(cleanedPhone));
            } else {
                $("#sender_phone").text(EditPhoneNumber('+' + cleanedPhone));
            }

            $("#receiver_name").text(order.receiver.name);

            $("#receiver_address").text(order.receiver.address);
            var date = "";
            var time = "";

            if (order.receiverPickupDateTime) {
                date = order.receiverPickupDateTime.toDate().toDateString();
                time = order.receiverPickupDateTime.toDate().toLocaleTimeString();

            }
            $("#receiver_datetime").text(date + " " + time);

            let cleanedReceiverPhone = order.receiver.phone.replace(/[()\s]/g, '').replace(/^(\+)?/, '+');

            if (cleanedReceiverPhone.startsWith('+')) {
                $("#receiver_phone").text(EditPhoneNumber(cleanedReceiverPhone));
            } else {
                $("#receiver_phone").text(EditPhoneNumber('+' + cleanedReceiverPhone));
            }

            if (order.sendReceiverSms) {
                let smsSentBadge = (order.smsSent && order.smsSent.placed) ? ' ({{ trans('lang.sms_sent') }})' : ' ({{ trans('lang.sms_not_sent') }})';
                let smsAmount = parseFloat(order.smsCharge || 50);
                $('#receiver_sms_status').html('<span class="badge badge-success">{{ trans('lang.yes') }} (+' + formatCurrency(smsAmount, currencyData) + ')</span>' + smsSentBadge);
            } else {
                $('#receiver_sms_status').html('<span class="badge badge-secondary">{{ trans('lang.no') }}</span>');
            }



            if (order.createdAt) {
                var date1 = order.createdAt.toDate().toDateString();
                var date = new Date(date1);
                var dd = String(date.getDate()).padStart(2, '0');
                var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
                var yyyy = date.getFullYear();
                var createdAt_val = yyyy + '-' + mm + '-' + dd;
                var time = order.createdAt.toDate().toLocaleTimeString('en-US');

                $('#createdAt').text(createdAt_val + ' ' + time);
            }

            var payment_method = '';
            if (order.payment_method) {

                if (order.payment_method == "stripe") {
                    image = '{{asset("images/payment/stripe.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "xendit") {
                    image = '{{asset("images/payment/xendit.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "midtrans") {
                    image = '{{asset("images/payment/midtrans.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"  width="30%" height="30%">';

                } else if (order.payment_method == "orangepay") {
                    image = '{{asset("images/payment/orangepay.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "cod") {
                    image = '{{asset("images/payment/cashondelivery.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"  width="30%" height="30%">';

                } else if (order.payment_method == "razorpay") {
                    image = '{{asset("images/payment/razorepay.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'"  width="30%" height="30%">';

                } else if (order.payment_method == "paypal") {
                    image = '{{asset("images/payment/paypal.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "payfast") {
                    image = '{{asset("images/payment/payfast.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "paystack") {
                    image = '{{asset("images/payment/paystack.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "flutterwave") {
                    image = '{{asset("images/payment/flutter_wave.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "mercadoPago" || order.payment_method == "mercado pago" || order.payment_method == "mercadopago") {
                    image = '{{asset("images/payment/marcado_pago.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "wallet") {
                    image = '{{asset("images/payment/foodie_wallet.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%" >';

                } else if (order.payment_method == "paytm") {
                    image = '{{asset("images/payment/paytm.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "cancelled order payment") {
                    image = '{{asset("images/payment/cancel_order.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';

                } else if (order.payment_method == "refund amount") {
                    image = '{{asset("images/payment/refund_amount.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';
                } else if (order.payment_method == "referral amount") {
                    image = '{{asset("images/payment/reffral_amount.png")}}';
                    payment_method = '<img alt="image" src="' + image + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" width="30%" height="30%">';
                } else {
                    payment_method = order.payment_method;
                }
            }
            $('#payment_method').html('<span>' + payment_method + '</span>');

            if (order.driver != '' && order.driver != undefined) {
              
                $('#driver_email').html('<a href="mailto:' + order.driver.email + '">' + shortEmail(order.driver.email) + '</a>');
                $('#driver_firstName').text(order.driver.firstName);
                $('#driver_lastName').text(order.driver.lastName);
                if (order.driver.phoneNumber.includes('+')) {
                    $('#driver_phone').text('+' + EditPhoneNumber(order.driver.phoneNumber.slice(1)));
                } else {
                    $('#driver_phone').text(EditPhoneNumber(order.driver.phoneNumber));
                }

            } else {

                $('.driver_details_hide').empty();
            }

            fcmToken = order.author.fcmToken;
            customername = order.author.firstName;
            old_order_status = order.status;

            if (order.payment_shared != undefined) {
                payment_shared = order.payment_shared;
            }
            var productsListHTML = buildHTMLParcelList(order);
            var productstotalHTML = buildParcelTotal(order);
            renderParcelPricingDetails(order);

            if (productsListHTML != '') {
                append_procucts_list.innerHTML = productsListHTML;
            }
            if (productstotalHTML != '') {
                append_procucts_total.innerHTML = productstotalHTML;
            }
            orderPreviousStatus = order.status;
            if (order.hasOwnProperty('payment_method')) {
                orderPaymentMethod = order.payment_method;
            }

            $("#order_status_text").text(order.status || '');

            jQuery("#data-table_processing").hide();
        })

        $(".edit-form-btn").click(async function() {


            var clientName = $(".client_name").val();
            var orderStatus = $("#order_status").val();
            if (old_order_status != orderStatus) {

                if (orderStatus == "Order Completed") {
                    manfcmTokenVendor = fcmToken;
                    manname = customername;
                } else {
                    manfcmTokenVendor = fcmToken;
                    manname = vendorname;
                }

                database.collection('parcel_orders').doc(id).update({
                    'status': orderStatus
                }).then(async function(result) {
                    if (orderStatus != orderPreviousStatus && payment_shared == false) {
                        if (orderStatus == 'Order Completed') {
                            await database.collection('parcel_orders').doc(id).update({
                                'payment_shared': true
                            }).then(async function(result) {});
                        }


                        await $.ajax({
                            type: 'POST',
                            url: "<?php echo route('order-status-notification'); ?>",
                            data: {
                                _token: '<?php echo csrf_token() ?>',
                                'fcm': manfcmTokenVendor,
                                'vendorname': manname,
                                'orderStatus': orderStatus
                            },
                            success: function(data) {

                                if (orderPreviousStatus != 'Order Rejected' && orderPreviousStatus != 'Driver Rejected' && orderPaymentMethod != 'cod' && orderTakeAwayOption == false) {
                                    if (orderStatus == 'Order Rejected' || orderStatus == 'Driver Rejected') {

                                    } else {

                                        window.location.href = '{{ route("parcel_orders")}}';
                                    }
                                } else {
                                    window.location.href = '{{ route("parcel_orders")}}';
                                }

                            }
                        });

                    } else {

                        $.ajax({
                            type: 'POST',
                            url: "<?php echo route('order-status-notification'); ?>",
                            data: {
                                _token: '<?php echo csrf_token() ?>',
                                'fcm': manfcmTokenVendor,
                                'vendorname': manname,
                                'orderStatus': orderStatus
                            },
                            success: function(data) {

                                if (orderPreviousStatus != 'Order Rejected' && orderPreviousStatus != 'Driver Rejected' && orderPaymentMethod != 'cod' && orderTakeAwayOption == false) {
                                    if (orderStatus == 'Order Rejected' || orderStatus == 'Driver Rejected') {
                                        var walletId = "<?php echo uniqid(); ?>";
                                        var canceldateNew = new Date();
                                        var orderCancelDate = new Date(canceldateNew.setHours(23, 59, 59, 999));
                                        database.collection('wallet').doc(walletId).set({
                                            'amount': parseFloat(orderPaytableAmount),
                                            'date': orderStatus,
                                            'id': walletId,
                                            'payment_status': 'success',
                                            'user_id': orderCustomerId,
                                            'payment_method': 'Cancelled Order Payment'
                                        }).then(function(result) {
                                            window.location.href = '{{ route("orders")}}';
                                        })
                                    } else {

                                        window.location.href = '{{ route("orders")}}';
                                    }
                                } else {

                                    window.location.href = '{{ route("orders")}}';
                                }

                            }
                        });

                    }


                });
            }
        })

    })

    function buildHTMLParcelList(snapshotsParcel) {
        var html = '';
        
        html = html + '<tr>';

        html = html + '<td class="order-product"><div class="order-product-box">';

        html = html + '</div><div class="orders-tracking"><h6>' + snapshotsParcel.parcelWeight + '</h6><div class="orders-tracking-item-details">';

        html = html + '</div></div></td>';
        
        var parcelWeightCharge = "";
        var subTotal = "";
        if (currencyAtRight) {
            parcelWeightCharge = parseFloat(snapshotsParcel.parcelWeightCharge).toFixed(decimal_degits) + "" + currentCurrency;
            subTotal = parseFloat(snapshotsParcel.subTotal).toFixed(decimal_degits) + "" + currentCurrency;
        } else {
            parcelWeightCharge = currentCurrency + "" + parseFloat(snapshotsParcel.parcelWeightCharge).toFixed(decimal_degits);
            subTotal = currentCurrency + "" + parseFloat(snapshotsParcel.subTotal).toFixed(decimal_degits);
        }

        if (snapshotsParcel.hasOwnProperty('parcelType') && snapshotsParcel.parcelType != '') {
            $('#parcel_type').text(snapshotsParcel.parcelType);
        } else {
            $('#div_parcel_category').hide();
        }

        html = html + '<td>' + parcelWeightCharge + '</td><td> ' + parseFloat(snapshotsParcel.distance).toFixed(decimal_degits) + ' Km' + '</td><td>  ' + subTotal + '</td>';

        html = html + '</tr>';

        return html;
    }

    /* ---- Point 41: reading the parcel pricing off an order -----------------
     * Every value is optional: orders placed before the app recorded them
     * simply show nothing extra. Numbers may arrive as strings. */
    function parcelNumber(value) {
        var number = parseFloat(value);
        return isNaN(number) ? 0 : number;
    }

    function parcelPriceExtras(order) {
        var breakdown = (order && order.priceBreakdown) || {};
        return {
            options: parcelNumber(breakdown.options),
            pickupCharge: breakdown.pickupCharge,
            deliveryCharge: breakdown.deliveryCharge,
            fixedTax: parcelNumber(breakdown.fixedTax),
            extraKgCharge: parcelNumber(breakdown.extraKgCharge),
            commission: parcelNumber(breakdown.commission),
            scope: order ? order.scope : '',
            pickupMethod: order ? order.pickupMethod : '',
            deliveryMethod: order ? order.deliveryMethod : ''
        };
    }

    function parcelExtrasRows(extras) {
        var html = '';

        if (extras.extraKgCharge > 0) {
            html += '<tr><td class="label">{{ trans('lang.parcel_extra_kg_charge') }}</td><td id="greenColor">+' +
                formatCurrency(extras.extraKgCharge, currencyData) + '</td></tr>';
        }

        if (extras.options > 0) {
            html += '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.parcel_options_charge') }}</span></td></tr>';

            /* The two parts, when the app recorded them separately. */
            var pickupLabel = extras.pickupMethod === 'pickup_point'
                ? "{{ trans('lang.region_parcel_point_dropoff_charge') }}"
                : "{{ trans('lang.region_parcel_home_pickup_charge') }}";
            var deliveryLabel = extras.deliveryMethod === 'pickup_point'
                ? "{{ trans('lang.region_parcel_point_collection_charge') }}"
                : "{{ trans('lang.region_parcel_home_delivery_charge') }}";

            if (extras.pickupCharge !== undefined || extras.deliveryCharge !== undefined) {
                html += '<tr><td class="label">' + pickupLabel + '</td><td id="greenColor">+' +
                    formatCurrency(parcelNumber(extras.pickupCharge), currencyData) + '</td></tr>';
                html += '<tr><td class="label">' + deliveryLabel + '</td><td id="greenColor">+' +
                    formatCurrency(parcelNumber(extras.deliveryCharge), currencyData) + '</td></tr>';
            } else {
                html += '<tr><td class="label">{{ trans('lang.parcel_options_charge') }}</td><td id="greenColor">+' +
                    formatCurrency(extras.options, currencyData) + '</td></tr>';
            }
        }

        if (extras.fixedTax > 0) {
            /* 'international' accepted too, in case any order says so. */
            var fixedLabel = (extras.scope === 'intercountry' || extras.scope === 'international')
                ? "{{ trans('lang.region_parcel_intercountry_fixed_tax') }}"
                : "{{ trans('lang.region_parcel_intercity_fixed_tax') }}";
            html += '<tr><td class="seprater" colspan="2"><hr><span>' + fixedLabel + '</span></td></tr>';
            html += '<tr><td class="label">' + fixedLabel + '</td><td id="greenColor">+' +
                formatCurrency(extras.fixedTax, currencyData) + '</td></tr>';
        }

        /* W8: commission on a delivery company's price, untaxed. */
        if (extras.commission > 0) {
            html += '<tr><td class="label">{{ trans('lang.parcel_commission_line') }}</td><td id="greenColor">+' +
                formatCurrency(extras.commission, currencyData) + '</td></tr>';
        }

        return html;
    }

    function parcelPricingText(map, value) {
        return (value && map[value]) ? map[value] : (value || '');
    }

    async function parcelPickupPointName(pointId) {
        if (!pointId) {
            return '';
        }
        try {
            var doc = await database.collection('pickup_points').doc(pointId).get();
            if (!doc.exists) {
                return '';
            }
            var point = doc.data() || {};
            return [point.name, point.quarter, point.town].filter(Boolean).join(', ');
        } catch (err) {
            console.error('pickup point could not be read', pointId, err);
            return '';
        }
    }

    /* Fills the "how this price was reached" block. Each line shows only
     * when the order carries what it needs; the block hides entirely for
     * orders with none of it. */
    async function renderParcelPricingDetails(order) {
        var breakdown = order.priceBreakdown || {};
        var shown = 0;

        function line(row, text) {
            var $row = $('#div_parcel_pricing [data-pricing-row="' + row + '"]');
            if (text === '' || text === null || text === undefined) {
                $row.hide();
                return;
            }
            $row.show().find('span').last().text(text);
            shown++;
        }

        line('scope', parcelPricingText({
            city: "{{ trans('lang.carrier_scope_city') }}",
            intercity: "{{ trans('lang.carrier_scope_intercity') }}",
            intercountry: "{{ trans('lang.carrier_scope_intercountry') }}",
            international: "{{ trans('lang.carrier_scope_intercountry') }}"
        }, order.scope));

        line('source', parcelPricingText({
            'default': "{{ trans('lang.parcel_source_default') }}",
            rate_card: "{{ trans('lang.parcel_source_carrier') }}" + (order.carrierName ? ' - ' + order.carrierName : ''),
            carrier: "{{ trans('lang.parcel_source_carrier') }}" + (order.carrierName ? ' - ' + order.carrierName : ''),
            route_table: "{{ trans('lang.parcel_source_rate_table') }}"
        }, breakdown.source));

        var weight = '';
        if (breakdown.chargeableKg !== undefined) {
            weight = "{{ trans('lang.parcel_weight_breakdown') }}"
                .replace(':actual', parcelNumber(breakdown.actualKg))
                .replace(':size', parcelNumber(breakdown.volumetricKg))
                .replace(':charged', parcelNumber(breakdown.chargeableKg));
            if (breakdown.volumetricDivisor) {
                weight += ' - ' + "{{ trans('lang.parcel_size_divisor_note') }}".replace(':divisor', breakdown.volumetricDivisor);
            }
        }
        line('weight', weight);

        var dims = order.dimensions || {};
        line('dimensions', (dims.l && dims.w && dims.h) ? (dims.l + ' x ' + dims.w + ' x ' + dims.h + ' cm') : '');

        var dropoffName = await parcelPickupPointName(order.dropoffPointId);
        var collectName = await parcelPickupPointName(order.pickupPointId);

        line('collection', order.pickupMethod === 'pickup_point'
            ? "{{ trans('lang.parcel_method_point_dropoff') }}" + (dropoffName ? ' - ' + dropoffName : '')
            : (order.pickupMethod === 'home' ? "{{ trans('lang.parcel_method_home_pickup') }}" : ''));

        line('delivery', order.deliveryMethod === 'pickup_point'
            ? "{{ trans('lang.parcel_method_point_collection') }}" + (collectName ? ' - ' + collectName : '')
            : (order.deliveryMethod === 'home' ? "{{ trans('lang.parcel_method_home_delivery') }}" : ''));

        $('#div_parcel_pricing').toggle(shown > 0);
    }

    function buildParcelTotal(orderData) {

        var html = '';
    
        let adminCommission =  parseFloat(orderData.adminCommission || 0);;
        let adminCommissionType = orderData.adminCommissionType;
        let order_subtotal = parseFloat(orderData.subTotal || 0);
        let total_discount = 0;
        let total_tax_amount = 0;

        // Discount
        let discount = parseFloat(orderData.discount || 0);
        total_discount = isNaN(discount) ? 0 : discount;

        // Order tax
        let orderTaxable = Math.max(0, order_subtotal - total_discount);
        let orderCombinedTax = 0;
        (orderData.taxSetting || []).forEach(tax => {
            if (tax.enable) {
                let taxAmount = tax.type === "percentage"
                    ? (parseFloat(tax.tax) / 100) * orderTaxable
                    : parseFloat(tax.tax);

                total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                orderCombinedTax += parseFloat(taxAmount);
            }
        });
        taxBreakdownGrouped.order[''] = orderCombinedTax;

        // Extra charges
        let platformFee = parseFloat(orderData.platformFee || 0);

        // Extra taxes
        [
            {key: 'platform', amount: platformFee, taxes: orderData.platformTax || []},
        ].forEach(scope => {
            scope.taxes?.forEach(tax => {
                if (tax.enable) {
                    let taxAmount = 0;
                    if(scope.amount > 0){
                        taxAmount = tax.type === "percentage"
                        ? (parseFloat(tax.tax) / 100) * scope.amount
                        : parseFloat(tax.tax);
                    }
                    total_tax_amount += isNaN(taxAmount) ? 0 : taxAmount;
                    taxBreakdownGrouped[scope.key][tax.title] = (taxBreakdownGrouped[scope.key][tax.title] || 0) + parseFloat(taxAmount);
                }
            });
        });

        // SMS notification charge
        let smsCharge = 0;
        if (orderData.sendReceiverSms) {
            smsCharge = parseFloat(orderData.smsCharge !== undefined ? orderData.smsCharge : 50);
            if (isNaN(smsCharge)) smsCharge = 50;
        }

        /* Point 41: the charges the app keeps OUTSIDE subTotal - the
         * door / pickup-point options, the inter-city or international fixed
         * charge, and any extra-weight charge. Orders placed before these
         * existed have none, so their total is unchanged. Untaxed: the order
         * tax above is on subTotal, as it always was. */
        var parcelExtras = parcelPriceExtras(orderData);

        // Final price
        let order_total = (order_subtotal - total_discount) + platformFee + total_tax_amount + smsCharge
            + parcelExtras.options + parcelExtras.fixedTax + parcelExtras.extraKgCharge + parcelExtras.commission;
        
        html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.sub_total') }}</span></td></tr>';
        html = html +
            '<tr class="final-rate"><td class="label">{{ trans('lang.sub_total') }}</td><td class="sub_total" style="color:green">(' +
            formatCurrency(order_subtotal, currencyData) + ')</td></tr>';

        html = html +
            '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.discount') }}</span></td></tr>';
        
        let couponCode_html = '';
        if (discount > 0) {
            let discountLabel = "(" + orderData.discountLabel + "%)";
            couponCode_html = '</br><small>{{ trans('lang.coupon_codes') }} :' + discountLabel + '</small>';
        }
        html = html + '<tr><td class="label">{{ trans('lang.discount') }}' + couponCode_html +
            '</td><td class="discount text-danger">(-' + formatCurrency(total_discount, currencyData) + ')</td></tr>';

        html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.platform_charge') }}</span></td></tr>';
        html = html +'<tr><td class="label">{{ trans('lang.platform_charge') }}</td><td class="platform_charge " id="greenColor">+' +
                formatCurrency(platformFee, currencyData) + '</td></tr>';

        html = html + parcelExtrasRows(parcelExtras);

        if (orderData.sendReceiverSms && smsCharge > 0) {
            html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.receiver_sms_notification') }}</span></td></tr>';
            html = html + '<tr><td class="label">{{ trans('lang.receiver_sms_notification') }}</td><td class="sms_charge" id="greenColor">+' +
                formatCurrency(smsCharge, currencyData) + '</td></tr>';
        }

        html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.tax_calculation') }}</span></td></tr>';
        html = html + renderTaxSection('order', 'Tax on Order Total');
        html = html + renderTaxSection('platform', 'Tax on Platform Fee');
        html = html +'<tr><td class="label"><strong>{{ trans('lang.total_tax') }}</strong></td><td class="total_tax " id="greenColor"><strong>+' +
        formatCurrency(total_tax_amount, currencyData) + '</strong></td></tr>';
        
        html += '<tr><td class="seprater" colspan="2"><hr></td></tr>';
        
        html = html +
            '<tr class="grand-total"><td class="label">{{ trans('lang.total_amount') }}</td><td class="total_price_val " id="greenColor">' +
            formatCurrency(order_total, currencyData) + '</td></tr>';

        if (adminCommission > 0) {
            let adminCommHtml = "";
            let adminCommission_val = 0;
            if (adminCommissionType === "percentage") {
                adminCommHtml = "(" + adminCommission + "%)";
            }
            let commissionBase = (discount != 0 && discount != '') ? orderTaxable : order_subtotal;
            if (adminCommissionType === "percentage") {
                adminCommission_val = (commissionBase * adminCommission) / 100;
            } else {
                adminCommission_val = parseFloat(adminCommission);
            }
            html = html + '<tr><td class="label"><small>{{ trans('lang.admin_commission') }} ' + adminCommHtml +
            '</small> </td><td style="color:red"><small>( ' +  formatCurrency(adminCommission_val, currencyData) + ' )</small></td></tr>';
        }

        return html;
    }


    function PrintElem(elem) {
        var css = '@page { size: portrait; }',
            head = document.head || document.getElementsByTagName('head')[0],
            style = document.createElement('style');

        style.type = 'text/css';
        style.media = 'print';

        if (style.styleSheet) {
            style.styleSheet.cssText = css;
        } else {
            style.appendChild(document.createTextNode(css));
        }

        head.appendChild(style);
        var printContents = $('.printableArea').html();
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;

    }



    function getUserReview(Order) {
        refUserReview.limit(page_size).get().then(async function(userreviewsnapshot) {
            var reviewHTML = '';
            reviewHTML = buildRatingsAndReviewsHTML(Order, userreviewsnapshot);
            if (userreviewsnapshot.docs.length > 0) {
                jQuery("#customers_rating_and_review").append(reviewHTML);
            } else {
                jQuery("#customers_rating_and_review").html('<h4>No Reviews Found</h4>');
            }
        });
    }

    function buildRatingsAndReviewsHTML(Order, userreviewsnapshot) {
        var allreviewdata = [];
        var reviewhtml = '';
        userreviewsnapshot.docs.forEach((listval) => {
            var reviewDatas = listval.data();
            reviewDatas.id = listval.id;
            allreviewdata.push(reviewDatas);
        });
        reviewhtml += '<div class="user-ratings">';
        allreviewdata.forEach((listval) => {
            var val = listval;
            var review_user_view = '{{route("users.view",":id")}}';
            review_user_view = review_user_view.replace(':id', val.CustomerId);
            rating = val.rating;
            reviewhtml = reviewhtml + '<div class="reviews-members py-3 border mb-3"><div class="media">';
            reviewhtml = reviewhtml + '<a href="' + review_user_view + '"><img alt="#" src="' + val.profile + '" onerror="this.onerror=null;this.src=\'' + place_image + '\'" class=" img-circle img-size-32 mr-2" style="width:60px;height:60px"></a>';
            reviewhtml = reviewhtml + '<div class="media-body d-flex"><div class="reviews-members-header"><h6 class="mb-0"><a class="text-dark" href="' + review_user_view + '">' + val.uname + '</a></h6><div class="star-rating"><div class="d-inline-block" style="font-size: 14px;">';
            reviewhtml = reviewhtml + ' <ul class="rating" data-rating="' + rating + '">';
            reviewhtml = reviewhtml + '<li class="rating__item"></li>';
            reviewhtml = reviewhtml + '<li class="rating__item"></li>';
            reviewhtml = reviewhtml + '<li class="rating__item"></li>';
            reviewhtml = reviewhtml + '<li class="rating__item"></li>';
            reviewhtml = reviewhtml + '<li class="rating__item"></li>';
            reviewhtml = reviewhtml + '</ul>';
            reviewhtml = reviewhtml + '</div></div>';
            reviewhtml = reviewhtml + '</div>';
            reviewhtml = reviewhtml + '<div class="review-date ml-auto">';
            if (val.createdAt != null && val.createdAt != "") {
                var review_date = val.createdAt.toDate().toLocaleDateString('en', {
                    year: "numeric",
                    month: "short",
                    day: "numeric"
                });
                reviewhtml = reviewhtml + '<span>' + review_date + '</span>';
            }
            reviewhtml = reviewhtml + '</div>';
            reviewhtml = reviewhtml + '</div></div><div class="reviews-members-body w-100"><p class="mb-2">' + val.comment + '</p></div>';
            reviewhtml += '</div>';
            reviewhtml += '</div>';


        });

        reviewhtml += '</div>';

        return reviewhtml;
    }

    function renderTaxSection(section, labelSuffix) {
        let html = '';
        if (!taxBreakdownGrouped[section]) return '';
        for (let title in taxBreakdownGrouped[section]) {
            let taxlabel = title;
            let taxAmount = parseFloat(taxBreakdownGrouped[section][title]);
            html = html + '<tr><td class="label">' + taxlabel + " " + labelSuffix + '</td><td class="tax_amount" id="greenColor">+' + formatCurrency(taxAmount, currencyData) + '</td></tr>';
        }
        return html;
    }
</script>

@endsection