{{--
    Points 90 / 91 - can this driver's phone receive push notifications?
    Included by drivers/view and fleet_drivers/view with:
      $pushRoute  'drivers' or 'fleet.drivers' (the route name prefix of the page)
      $driverId   the driver's users/{id}
    The status is checked on the server without sending anything; "Send test
    notification" sends one real notification to the driver's phone.
--}}
<div class="row mt-4" id="driver_push_card">
    <div class="col-12">
        <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3 flex-wrap">
                <div class="card-header-title">
                    <h3 class="text-dark-2 mb-0 h4"><i class="mdi mdi-bell-ring mr-2"></i>{{ trans('lang.driver_push_title') }}</h3>
                </div>
                <div class="d-flex align-items-center">
                    <span id="driver_push_badge" class="badge badge-secondary mr-3">{{ trans('lang.driver_push_checking') }}</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="driver_push_test_btn" disabled>
                        <i class="mdi mdi-send mr-1"></i>{{ trans('lang.driver_push_send_test') }}
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="mb-1 text-dark-2" id="driver_push_text"></p>
                <small class="text-muted">{{ trans('lang.driver_push_help') }}</small>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var statusUrl = {!! json_encode(route($pushRoute . '.push.status', $driverId)) !!};
        var testUrl = {!! json_encode(route($pushRoute . '.push.test', $driverId)) !!};
        var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var badge = document.getElementById('driver_push_badge');
        var text = document.getElementById('driver_push_text');
        var btn = document.getElementById('driver_push_test_btn');
        var LABEL = {
            working: ['badge-success', {!! json_encode(trans('lang.driver_push_working')) !!}, {!! json_encode(trans('lang.driver_push_working_text')) !!}],
            no_token: ['badge-danger', {!! json_encode(trans('lang.driver_push_no_token')) !!}, {!! json_encode(trans('lang.driver_push_no_token_text')) !!}],
            expired: ['badge-danger', {!! json_encode(trans('lang.driver_push_expired')) !!}, {!! json_encode(trans('lang.driver_push_expired_text')) !!}],
            unknown: ['badge-warning', {!! json_encode(trans('lang.driver_push_unknown')) !!}, {!! json_encode(trans('lang.driver_push_unknown_text')) !!}]
        };
        function show(result, extra) {
            var l = LABEL[result && result.status] || LABEL.unknown;
            badge.className = 'badge mr-3 ' + l[0];
            badge.textContent = l[1];
            text.textContent = l[2] + (extra ? ' ' + extra : '');
            btn.disabled = !(result && result.status === 'working');
        }
        function call(url, method) {
            return fetch(url, {
                method: method,
                credentials: 'same-origin',
                headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}
            }).then(function (r) { return r.json(); });
        }
        call(statusUrl, 'GET').then(function (r) { show(r); }).catch(function () { show({status: 'unknown'}); });
        btn.addEventListener('click', function () {
            btn.disabled = true;
            call(testUrl, 'POST').then(function (r) {
                show(r, r.status === 'working' ? {!! json_encode(trans('lang.driver_push_test_sent')) !!} : '');
            }).catch(function () { show({status: 'unknown'}); });
        });
    });
</script>
