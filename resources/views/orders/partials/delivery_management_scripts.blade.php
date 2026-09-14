<script type="text/javascript">
    /* Delivery management: manual assignment, resend, and the audit trail.
     * Expects `id` (the order id) to already be defined by the host page. */
    var dmOrderId = id;
    var dmOrder = null;
    var dmDriverCache = {};

    $(document).ready(function () {
        loadDeliveryPanel();

        $('#dm_assign_btn').on('click', openAssignModal);
        $('#dm_confirm_assign_btn').on('click', confirmAssign);
        $('#dm_resend_btn').on('click', resendToDriver);
    });

    /* The page loads both Bootstrap 4 and 5, so which one owns the modal
     * data-api is not dependable - the older modals on this page use
     * data-dismiss and the newer one uses data-bs-dismiss. Closing it here as
     * well means the X and Cancel work either way. */
    $(document).on('click', '#dmAssignModal [data-dismiss="modal"], #dmAssignModal [data-bs-dismiss="modal"]', function () {
        closeAssignModal();
    });

    function closeAssignModal() {
        var el = document.getElementById('dmAssignModal');
        if (window.bootstrap && window.bootstrap.Modal) {
            var instance = window.bootstrap.Modal.getInstance(el);
            if (instance) {
                instance.hide();
                return;
            }
        }
        $('#dmAssignModal').modal('hide');
    }

    async function loadDeliveryPanel() {
        var snapshot = await database.collection('vendor_orders').doc(dmOrderId).get();
        dmOrder = snapshot.data();
        if (!dmOrder) {
            return;
        }
        renderCurrentDriver();
        await renderHistory();
    }

    function renderCurrentDriver() {
        var driver = dmOrder.driver;
        if (driver && driver.id) {
            $('#dm_current_driver').text((driver.firstName || '') + ' ' + (driver.lastName || ''));
            var contact = [];
            if (driver.email) { contact.push(driver.email); }
            if (driver.phoneNumber) { contact.push(driver.phoneNumber); }
            $('#dm_current_driver_contact').text(contact.join(' · '));
            $('#dm_assign_label').text("{{ trans('lang.reassign_driver') }}");
            $('#dm_resend_btn').show();
        } else {
            $('#dm_current_driver').text("{{ trans('lang.no_driver_assigned') }}");
            $('#dm_current_driver_contact').text('');
            $('#dm_assign_label').text("{{ trans('lang.assign_driver') }}");
            $('#dm_resend_btn').hide();
        }
    }

    /* Active drivers the admin may assign. Scoped to the region being worked
     * in, so a bound admin cannot hand work to another region's driver. */
    async function openAssignModal() {
        $('#dm_driver_error').html('');
        $('#dm_note').val('');
        var $list = $('#dm_driver_list');
        /* Rebuilt on every open, so any previous select2 has to be torn down
         * first or it keeps rendering the stale option list. */
        if ($list.hasClass('select2-hidden-accessible')) {
            $list.select2('destroy');
        }
        $list.empty().append($('<option></option>').attr('value', '').text(''));

        var snapshot = await regionScoped(
            database.collection('users').where('role', '==', 'driver').where('isActive', '==', true)
        ).get();

        dmDriverCache = {};
        snapshot.docs.forEach(function (doc) {
            var driver = doc.data();
            dmDriverCache[driver.id] = driver;

            var label = (driver.firstName || '') + ' ' + (driver.lastName || '');
            /* A driver already carrying another order is shown but flagged, so
             * the admin makes the call rather than the list hiding options. */
            if (driver.inProgressOrderID && driver.inProgressOrderID.length > 0
                && driver.inProgressOrderID.indexOf(dmOrderId) === -1) {
                label += " ({{ trans('lang.occupied') }})";
            }
            if (dmOrder.driver && dmOrder.driver.id === driver.id) {
                label += " ({{ trans('lang.currently_assigned') }})";
            }
            $list.append($('<option></option>').attr('value', driver.id).text(label));
        });

        $('#dmAssignModal').modal('show');

        /* select2 is built only once the modal is on screen. Initialised while
         * the modal is still display:none it has no box to measure, so the
         * control collapses to a few characters wide and the dropdown is
         * positioned against a zero-size element.
         *
         * Which Bootstrap fires the shown event here is not dependable - the
         * page loads both - so listen for it natively and through jQuery, with
         * a timer as a last resort. initDriverSelect2 is idempotent. */
        var modalEl = document.getElementById('dmAssignModal');
        modalEl.addEventListener('shown.bs.modal', initDriverSelect2, {once: true});
        $('#dmAssignModal').one('shown.bs.modal', initDriverSelect2);
        setTimeout(initDriverSelect2, 400);
    }

    function initDriverSelect2() {
        var $list = $('#dm_driver_list');
        if ($list.hasClass('select2-hidden-accessible')) {
            return;
        }
        /* The dropdown is parented to the field's own form-group, not the
         * modal. On <body> it renders behind the backdrop; on the modal it is
         * positioned against a full-viewport fixed box that also scrolls.
         * A small statically-placed wrapper right around the control gives
         * select2 a stable box to measure, so the list always opens directly
         * beneath it. */
        $list.select2({
            placeholder: "{{ trans('lang.select_driver') }}",
            allowClear: true,
            width: '100%',
            dropdownParent: $list.closest('.form-group')
        });
        $list.val('').trigger('change');
    }

    async function confirmAssign() {
        var driverId = $('#dm_driver_list').val();
        if (!driverId) {
            $('#dm_driver_error').html("{{ trans('lang.select_driver') }}");
            return;
        }

        var driver = dmDriverCache[driverId];
        var previous = dmOrder.driver;
        var isReassign = previous && previous.id && previous.id !== driverId;

        jQuery("#overlay").show();

        try {
            /* Release the order from the driver who had it, so their in-progress
             * list does not keep an order they are no longer on. */
            if (isReassign) {
                var prevSnapshot = await database.collection('users').doc(previous.id).get();
                var prevData = prevSnapshot.data();
                if (prevData) {
                    await database.collection('users').doc(previous.id).update({
                        'inProgressOrderID': (prevData.inProgressOrderID || []).filter(function (o) { return o !== dmOrderId; }),
                        'orderRequestData': (prevData.orderRequestData || []).filter(function (o) { return o !== dmOrderId; })
                    });
                }
            }

            var inProgress = driver.inProgressOrderID || [];
            var requests = driver.orderRequestData || [];
            if (inProgress.indexOf(dmOrderId) === -1) { inProgress.push(dmOrderId); }
            if (requests.indexOf(dmOrderId) === -1) { requests.push(dmOrderId); }

            await database.collection('users').doc(driverId).update({
                'inProgressOrderID': inProgress,
                'orderRequestData': requests
            });

            await database.collection('vendor_orders').doc(dmOrderId).update({
                'driverID': driverId,
                'driver': driver
            });

            await logDeliveryAction({
                orderId: dmOrderId,
                driverId: driverId,
                driverName: (driver.firstName || '') + ' ' + (driver.lastName || ''),
                action: isReassign ? 'reassigned' : 'assigned',
                orderStatus: dmOrder.status || '',
                note: $('#dm_note').val(),
                regionId: dmOrder.regionId || ''
            });

            await notifyDriver(driver, "{{ trans('lang.new_order_assigned') }}");

            closeAssignModal();
            jQuery("#overlay").hide();
            window.location.reload();
        } catch (e) {
            jQuery("#overlay").hide();
            console.error(e);
            showFeedback('danger', "{{ trans('lang.assign_driver_failed') }}");
        }
    }

    async function resendToDriver() {
        var driver = dmOrder.driver;
        if (!driver || !driver.id) {
            return;
        }

        jQuery("#overlay").show();

        /* Read the driver again rather than trusting the copy stored on the
         * order: the device token may have changed since assignment, which is
         * the usual reason a driver never received the first notification. */
        var snapshot = await database.collection('users').doc(driver.id).get();
        var fresh = snapshot.data() || driver;

        var sent = await notifyDriver(fresh, "{{ trans('lang.order_reminder') }}");

        await logDeliveryAction({
            orderId: dmOrderId,
            driverId: driver.id,
            driverName: (fresh.firstName || '') + ' ' + (fresh.lastName || ''),
            action: 'notification_resent',
            orderStatus: dmOrder.status || '',
            note: sent ? '' : "{{ trans('lang.driver_has_no_device_token') }}",
            regionId: dmOrder.regionId || ''
        });

        jQuery("#overlay").hide();
        showFeedback(sent ? 'success' : 'warning',
            sent ? "{{ trans('lang.notification_sent') }}" : "{{ trans('lang.driver_has_no_device_token') }}");
        await renderHistory();
    }

    async function notifyDriver(driver, title) {
        if (!driver || !driver.fcmToken) {
            return false;
        }
        try {
            await sendDriverNotification(driver.fcmToken, title,
                "{{ trans('lang.order') }} #" + dmOrderId, {orderId: dmOrderId, type: 'order_assigned'});
            return true;
        } catch (e) {
            console.error('Notification failed', e);
            return false;
        }
    }

    function showFeedback(kind, message) {
        $('#dm_feedback').html('<div class="alert alert-' + kind + ' mb-0">' + message + '</div>');
    }

    var DM_ACTION_LABEL = {
        'assigned': "{{ trans('lang.action_assigned') }}",
        'reassigned': "{{ trans('lang.action_reassigned') }}",
        'notification_resent': "{{ trans('lang.action_notification_resent') }}"
    };

    async function renderHistory() {
        var snapshot = await database.collection(DELIVERY_HISTORY_COLLECTION)
            .where('orderId', '==', dmOrderId).get();

        var rows = snapshot.docs.map(function (doc) { return doc.data(); });

        /* Sorted here rather than in the query: ordering in Firestore would
         * drop any row whose timestamp had not resolved yet, and this list is
         * short. */
        rows.sort(function (a, b) {
            var at = a.createdAt ? a.createdAt.seconds : 0;
            var bt = b.createdAt ? b.createdAt.seconds : 0;
            return bt - at;
        });

        if (rows.length === 0) {
            $('#dm_history_rows').html('<p class="text-muted mb-0">{{ trans('lang.no_record_found') }}</p>');
            return;
        }

        /* Rendered as a stacked list rather than a table: this panel sits in
         * the narrow right-hand column, where five columns would not fit. */
        var html = '<ul class="list-unstyled mb-0">';
        rows.forEach(function (r) {
            var when = r.createdAt ? new Date(r.createdAt.seconds * 1000).toLocaleString() : '';
            html += '<li class="pb-2 mb-2" style="border-bottom:1px solid #eee">';
            html += '<strong>' + (DM_ACTION_LABEL[r.action] || r.action) + '</strong>';
            if (r.driverName) {
                html += ' &mdash; ' + r.driverName;
            }
            html += '<br><small class="text-muted">' + when;
            if (r.orderStatus) {
                html += ' &middot; ' + r.orderStatus;
            }
            if (r.adminName) {
                html += ' &middot; ' + r.adminName;
            }
            html += '</small>';
            if (r.note) {
                html += '<br><small class="text-muted"><em>' + r.note + '</em></small>';
            }
            html += '</li>';
        });
        html += '</ul>';
        $('#dm_history_rows').html(html);
    }
</script>
