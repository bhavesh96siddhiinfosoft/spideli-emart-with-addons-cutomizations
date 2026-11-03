<script>
    /* ---- Multi-day jobs with tasks: the admin's view (client point 88) ------
     *
     * Read only, as the client asked: the job's tasks, their worker, date,
     * price, status, payment, files and comments, and the progress and
     * totals. The provider manages the tasks (provider panel); the customer
     * comments and pays (website). Spec: docs/APP-SPEC-MULTI-DAY-TASKS.md.
     * ------------------------------------------------------------------ */
    var adminTasks = { bookingId: null, booking: null, tasks: [], workers: {}, comments: {} };
    var adminTaskText = {
        progress: {!! json_encode(trans('lang.task_progress')) !!},
        total: {!! json_encode(trans('lang.task_totals')) !!},
        none: {!! json_encode(trans('lang.task_none_admin')) !!},
        pending: {!! json_encode(trans('lang.task_status_pending')) !!},
        inProgress: {!! json_encode(trans('lang.task_status_in_progress')) !!},
        done: {!! json_encode(trans('lang.task_status_done')) !!},
        paid: {!! json_encode(trans('lang.task_paid')) !!},
        unpaid: {!! json_encode(trans('lang.task_unpaid')) !!},
        noWorker: {!! json_encode(trans('lang.task_no_worker')) !!},
        comments: {!! json_encode(trans('lang.task_comments')) !!},
        noComments: {!! json_encode(trans('lang.task_no_comments')) !!},
        payEnd: {!! json_encode(trans('lang.task_payment_end_info')) !!},
        payPerTask: {!! json_encode(trans('lang.task_payment_per_task_info')) !!},
        doneBy: {!! json_encode(trans('lang.task_done_on')) !!},
        roles: {
            customer: {!! json_encode(trans('lang.task_role_customer')) !!},
            provider: {!! json_encode(trans('lang.task_role_provider')) !!},
            worker: {!! json_encode(trans('lang.task_role_worker')) !!}
        }
    };

    function adminTaskEscape(text) {
        return String(text == null ? '' : text).replace(/[&<>"']/g, function (ch) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
        });
    }

    function adminTaskMoney(amount) {
        return formatCurrency(parseFloat(amount) || 0, currencyData);
    }

    function adminTaskDate(value, withTime) {
        if (!value || typeof value.toDate !== 'function') {
            return '';
        }
        return withTime ? value.toDate().toLocaleString() : value.toDate().toLocaleDateString();
    }

    /* Called once the booking and its service are loaded. */
    function initAdminTasks(order, service) {
        if (!order || !(order.hasTasks === true || (service && service.tasksEnabled === true))) {
            return;
        }
        adminTasks.bookingId = order.id;
        adminTasks.booking = order;
        $('#booking_tasks').show();
        var ref = database.collection('provider_orders').doc(order.id);
        ref.onSnapshot(function (snap) {
            if (snap.exists) {
                adminTasks.booking = snap.data();
                renderAdminTasks();
            }
        });
        ref.collection('tasks').orderBy('order').onSnapshot(function (snap) {
            adminTasks.tasks = snap.docs.map(function (doc) { return Object.assign({ id: doc.id }, doc.data()); });
            adminTasks.tasks.forEach(function (t) {
                if (t.workerId && adminTasks.workers[t.workerId] === undefined) {
                    adminTasks.workers[t.workerId] = '';
                    database.collection('providers_workers').doc(t.workerId).get().then(function (w) {
                        adminTasks.workers[t.workerId] = w.exists ? ((w.data().firstName || '') + ' ' + (w.data().lastName || '')).trim() : '';
                        renderAdminTasks();
                    }).catch(function () {});
                }
            });
            renderAdminTasks();
        }, function (err) {
            $('#tasks_error').text(err.message || String(err)).show();
        });
    }

    function renderAdminTasks() {
        var booking = adminTasks.booking || {};
        var tasks = adminTasks.tasks;
        var done = tasks.filter(function (t) { return t.status === 'done'; }).length;
        var total = tasks.reduce(function (s, t) { return s + (parseFloat(t.price) || 0); }, 0);
        var paid = tasks.filter(function (t) { return t.paid === true; }).reduce(function (s, t) { return s + (parseFloat(t.price) || 0); }, 0);
        $('#tasks_progress').text(tasks.length ? adminTaskText.progress.replace(':done', done).replace(':count', tasks.length) : '');
        $('#tasks_payment_mode').text(booking.taskPayment === 'per_task' ? adminTaskText.payPerTask : adminTaskText.payEnd);
        $('#tasks_total').text(tasks.length ? adminTaskText.total.replace(':total', adminTaskMoney(total)).replace(':paid', adminTaskMoney(paid))
            .replace(':due', adminTaskMoney(Math.max(0, total - paid))) : '');
        if (!tasks.length) {
            $('#tasks_list').html('<p class="text-muted mb-0">' + adminTaskEscape(adminTaskText.none) + '</p>');
            return;
        }
        var html = '';
        tasks.forEach(function (t) {
            var statusText = t.status === 'done' ? adminTaskText.done : (t.status === 'in_progress' ? adminTaskText.inProgress : adminTaskText.pending);
            var statusClass = t.status === 'done' ? 'badge-success' : (t.status === 'in_progress' ? 'badge-info' : 'badge-secondary');
            var worker = t.workerId ? (adminTasks.workers[t.workerId] || t.workerId) : adminTaskText.noWorker;
            var files = (Array.isArray(t.files) ? t.files : []).map(function (f) {
                return '<a href="' + adminTaskEscape(f.url) + '" target="_blank" rel="noopener" class="mr-3"><i class="fa ' + (f.type === 'image' ? 'fa-image' : 'fa-file') + '"></i> ' + adminTaskEscape(f.name || 'file') + '</a>';
            }).join('');
            var doneOn = t.status === 'done' && t.doneAt ? adminTaskText.doneBy.replace(':date', adminTaskDate(t.doneAt, true)) : '';
            html += '<div class="border rounded p-3 mb-3 admin-task" data-id="' + adminTaskEscape(t.id) + '">'
                + '<div class="d-flex flex-wrap align-items-start">'
                + '<div class="mr-3"><strong>' + adminTaskEscape(t.title) + '</strong>'
                + '<div class="small text-muted">' + adminTaskEscape(adminTaskDate(t.date)) + ' &middot; ' + adminTaskEscape(worker)
                + (doneOn ? ' &middot; ' + adminTaskEscape(doneOn) : '') + '</div></div>'
                + '<div class="ml-auto text-nowrap"><strong>' + adminTaskEscape(adminTaskMoney(t.price)) + '</strong> '
                + '<span class="badge ' + statusClass + '">' + adminTaskEscape(statusText) + '</span> '
                + '<span class="badge ' + (t.paid === true ? 'badge-success' : 'badge-light') + '">' + adminTaskEscape(t.paid === true ? adminTaskText.paid : adminTaskText.unpaid) + '</span></div>'
                + '</div>'
                + (t.description ? '<p class="mt-2 mb-1" style="white-space:pre-line">' + adminTaskEscape(t.description) + '</p>' : '')
                + (files ? '<div class="mt-1">' + files + '</div>' : '')
                + '<button type="button" class="btn btn-sm btn-link px-0 admin-task-comments-toggle">' + adminTaskEscape(adminTaskText.comments) + '</button>'
                + '<div class="admin-task-comment-list small" style="display:none"></div>'
                + '</div>';
        });
        $('#tasks_list').html(html);
        Object.keys(adminTasks.comments).forEach(function (taskId) {
            var box = $('.admin-task[data-id="' + taskId + '"] .admin-task-comment-list');
            if (box.length) {
                box.show();
                renderAdminTaskComments(taskId);
            }
        });
    }

    $(document).on('click', '.admin-task-comments-toggle', function () {
        var taskId = $(this).closest('.admin-task').data('id');
        var box = $(this).closest('.admin-task').find('.admin-task-comment-list');
        if (adminTasks.comments[taskId]) {
            adminTasks.comments[taskId].unsubscribe();
            delete adminTasks.comments[taskId];
            box.hide();
            return;
        }
        box.show();
        var entry = { list: [] };
        entry.unsubscribe = database.collection('provider_orders').doc(adminTasks.bookingId).collection('tasks').doc(taskId)
            .collection('comments').orderBy('createdAt').onSnapshot(function (snap) {
                entry.list = snap.docs.map(function (doc) { return doc.data(); });
                renderAdminTaskComments(taskId);
            });
        adminTasks.comments[taskId] = entry;
    });

    function renderAdminTaskComments(taskId) {
        var entry = adminTasks.comments[taskId];
        var box = $('.admin-task[data-id="' + taskId + '"] .admin-task-comment-list');
        if (!entry || !box.length) {
            return;
        }
        box.html(entry.list.length ? entry.list.map(function (c) {
            return '<div class="mb-1"><strong>' + adminTaskEscape(adminTaskText.roles[c.role] || c.role || '') + '</strong> '
                + '<span class="text-muted">' + adminTaskEscape(adminTaskDate(c.createdAt, true)) + '</span><br>' + adminTaskEscape(c.text) + '</div>';
        }).join('') : '<span class="text-muted">' + adminTaskEscape(adminTaskText.noComments) + '</span>');
    }
</script>
