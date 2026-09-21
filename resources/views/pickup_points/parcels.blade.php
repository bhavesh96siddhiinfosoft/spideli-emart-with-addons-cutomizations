@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.pickup_point_parcels_at') }} <span id="point_name"></span></h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('pickup-points') !!}">{{ trans('lang.pickup_point_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.pickup_point_parcels') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-body">
                            <div class="alert alert-info" id="empty_note" style="display:none">
                                {{ trans('lang.pickup_point_no_parcels') }}
                            </div>
                            <div class="table-responsive m-t-10">
                                <table id="parcelTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('lang.order_id') }}</th>
                                            <th>{{ trans('lang.sender_details') }}</th>
                                            <th>{{ trans('lang.receiver_details') }}</th>
                                            <th>{{ trans('lang.status') }}</th>
                                            <th>{{ trans('lang.date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="append_list"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="form-group col-12 text-center btm-btn">
                            <a href="{!! route('pickup-points') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
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
    var pickupPointId = '{{ $id }}';

    $(document).ready(async function () {
        jQuery("#data-table_processing").show();

        var doc = await database.collection('pickup_points').doc(pickupPointId).get();

        if (!doc.exists || !isInActiveRegion(doc.data())) {
            window.location.href = '{{ route('pickup-points') }}';
            return;
        }

        $('#point_name').text(doc.data().name || '');

        /* One equality filter, so no composite index is needed. */
        var snapshots = await database.collection('parcel_orders')
            .where('pickupPointId', '==', pickupPointId).get();

        var html = '';

        snapshots.docs.forEach(function (parcelDoc) {
            html += buildRow(parcelDoc.data());
        });

        if (html === '') {
            $('#empty_note').show();
        }

        $('#append_list').html(html);
        jQuery("#data-table_processing").hide();
    });

    function buildRow(parcel) {
        var sender = parcel.sender || {};
        var receiver = parcel.receiver || {};
        var html = '<tr>';

        html += '<td>' + escapeHtml(parcel.id) + '</td>';
        html += '<td>' + escapeHtml(sender.name) + '<br><small>' + escapeHtml(sender.phone) + '</small></td>';
        html += '<td>' + escapeHtml(receiver.name) + '<br><small>' + escapeHtml(receiver.phone) + '</small></td>';
        html += '<td>' + escapeHtml(parcel.status) + '</td>';
        html += '<td>' + formatDate(parcel.createdAt) + '</td>';
        html += '</tr>';

        return html;
    }

    function formatDate(value) {
        if (value && value.toDate) {
            return value.toDate().toDateString();
        }

        return '-';
    }

    function escapeHtml(value) {
        return $('<div></div>').text(value === undefined || value === null ? '' : value).html();
    }
</script>
@endsection
