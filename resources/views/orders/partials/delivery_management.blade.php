{{--
    Delivery management panel.

    Sits in the right-hand column beneath Billing and Driver Detail, styled as a
    card to match them. Deliberately independent of the self-delivery status
    flow on the left: that path is tied to the status dropdown and the
    preparation-time modal, and only appears for vendor self-delivery orders.
    This panel lets an admin assign any order to any active driver in the
    region, re-send a notification a driver never acted on, and see the full
    assignment trail.
--}}
{{-- Deliberately NOT using the order_addre-edit class here. When an order has
     no driver, the page runs
         $('.order_addre-edit').removeClass('col-md-4').addClass('col-md-6')
     which would turn this card into a half-width Bootstrap column. --}}
<div class="mt-2 delivery-management-card">
    <div class="card">
        <div class="card-header">
            <h4 class="card-header-title">
                <i class="mdi mdi-truck-delivery mr-2"></i>{{ trans('lang.delivery_management') }}
            </h4>
        </div>
        <div class="card-body">

            {{-- Deliberately not using .address here: that class floats its
                 labels to 35%, which would wrap the driver name under the
                 label in this narrower column. --}}
            <div class="mb-3">
                <div class="text-muted"><small>{{ trans('lang.current_driver') }}</small></div>
                <div id="dm_current_driver">{{ trans('lang.no_driver_assigned') }}</div>
                <div class="text-muted"><small id="dm_current_driver_contact"></small></div>
            </div>

            <div class="mb-2">
                <button type="button" class="btn btn-primary btn-sm" id="dm_assign_btn">
                    <i class="fa fa-user-plus mr-1"></i> <span id="dm_assign_label">{{ trans('lang.assign_driver') }}</span>
                </button>
                <button type="button" class="btn btn-default btn-sm mt-1" id="dm_resend_btn" style="display:none;">
                    <i class="fa fa-bell mr-1"></i> {{ trans('lang.resend_notification') }}
                </button>
            </div>

            <div class="form-text text-muted mb-2">
                <small>{{ trans('lang.resend_notification_help') }}</small>
            </div>

            <div id="dm_feedback" class="mb-2"></div>

            <h5 class="mt-3 mb-2">{{ trans('lang.assignment_history') }}</h5>
            <div id="dm_history_rows">
                <p class="text-muted mb-0">{{ trans('lang.no_record_found') }}</p>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="dmAssignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('lang.assign_driver') }}</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>{{ trans('lang.select_driver') }}</label>
                    <select class="form-control" id="dm_driver_list" style="width:100%"></select>
                    <div class="form-text text-muted">{{ trans('lang.assign_driver_help') }}</div>
                    <div id="dm_driver_error" style="color:red"></div>
                </div>
                <div class="form-group">
                    <label>{{ trans('lang.note') }}</label>
                    <input type="text" class="form-control" id="dm_note" placeholder="{{ trans('lang.assign_note_placeholder') }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal">{{ trans('lang.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="dm_confirm_assign_btn">{{ trans('lang.assign') }}</button>
            </div>
        </div>
    </div>
</div>
