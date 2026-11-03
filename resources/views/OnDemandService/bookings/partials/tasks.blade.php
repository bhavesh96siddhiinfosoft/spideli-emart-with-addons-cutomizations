{{-- Multi-day jobs with tasks (client point 88, 3 Nov 2026): the admin's
     view of a task job - read only, as the client asked. Filled by
     initAdminTasks() (partials/tasks_script). Spec:
     docs/APP-SPEC-MULTI-DAY-TASKS.md. --}}
<div class="booking-tasks mt-4" id="booking_tasks" style="display:none">
    <div class="card">
        <div class="card-header bg-white d-flex flex-wrap align-items-center">
            <h3 class="mb-0 mr-3">{{ trans('lang.tasks') }}</h3>
            <span class="text-muted" id="tasks_progress"></span>
            <span class="ml-auto text-muted" id="tasks_payment_mode"></span>
        </div>
        <div class="card-body">
            <div class="alert alert-danger" id="tasks_error" style="display:none"></div>
            <div class="text-muted mb-3" id="tasks_total"></div>
            <div id="tasks_list"></div>
        </div>
    </div>
</div>
