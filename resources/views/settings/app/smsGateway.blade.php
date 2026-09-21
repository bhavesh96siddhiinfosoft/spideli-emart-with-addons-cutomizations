@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.sms_gateway') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.sms_gateway') }}</li>
            </ol>
        </div>
    </div>

    <div class="card-body">

        <div class="alert alert-info">
            {{ trans('lang.sms_gateway_intro') }}
        </div>

        <div class="error_top" style="display:none"></div>
        <div class="success_top" style="display:none"></div>

        <div class="row vendor_payout_create">
            <div class="vendor_payout_create-inner">
                <fieldset>
                    <legend><i class="mr-3 mdi mdi-message-text"></i>{{ trans('lang.sms_gateway') }}</legend>

                    <div class="form-group row width-100">
                        <div class="form-check width-100">
                            <input type="checkbox" id="is_enabled">
                            <label class="control-label" for="is_enabled">{{ trans('lang.sms_enable') }}</label>
                        </div>
                        <div class="form-text text-muted pl-4">{{ trans('lang.sms_enable_help') }}</div>
                    </div>

                    <div class="form-group row width-100">
                        <label class="col-4 control-label">{{ trans('lang.sms_api_key') }}</label>
                        <div class="col-7">
                            <input type="password" class="form-control" id="api_key">
                            <div class="form-text text-muted">{{ trans('lang.sms_api_key_help') }}</div>
                            <div id="error_api_key" class="err"></div>
                        </div>
                    </div>

                    <div class="form-group row width-100">
                        <label class="col-4 control-label">{{ trans('lang.sms_sender_id') }}</label>
                        <div class="col-7">
                            <input type="text" class="form-control" id="sender_id" maxlength="11">
                            <div class="form-text text-muted">{{ trans('lang.sms_sender_id_help') }}</div>
                            <div id="error_sender_id" class="err"></div>
                        </div>
                    </div>

                    <div class="form-group row width-100">
                        <label class="col-4 control-label">{{ trans('lang.sms_api_url') }}</label>
                        <div class="col-7">
                            <input type="text" class="form-control" id="api_url" placeholder="https://obitsms.com/api/v2">
                            <div class="form-text text-muted">{{ trans('lang.sms_api_url_help') }}</div>
                        </div>
                    </div>

                    <div class="form-group row width-100">
                        <label class="col-4 control-label">{{ trans('lang.sms_balance') }}</label>
                        <div class="col-7">
                            <span id="sms_balance" class="mr-3">-</span>
                            <a href="javascript:void(0)" id="check_balance" class="btn btn-default btn-sm">{{ trans('lang.sms_balance_check') }}</a>
                        </div>
                    </div>

                    <div class="form-group row width-100">
                        <div class="alert alert-warning w-100">
                            <i class="mdi mdi-alert mr-2"></i>{{ trans('lang.sms_credentials_warning') }}
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend><i class="mr-3 mdi mdi-send"></i>{{ trans('lang.sms_test') }}</legend>

                    <div class="form-group row width-100">
                        <div class="form-text text-muted">{{ trans('lang.sms_test_help') }}</div>
                    </div>

                    <div class="form-group row width-100">
                        <label class="col-4 control-label">{{ trans('lang.sms_test_number') }}</label>
                        <div class="col-7">
                            <input type="text" class="form-control" id="test_number" placeholder="237674937152">
                            <div class="form-text text-muted">{{ trans('lang.sms_test_number_help') }}</div>
                            <div id="error_test_number" class="err"></div>
                        </div>
                    </div>

                    <div class="form-group row width-100">
                        <label class="col-4 control-label">{{ trans('lang.sms_test_message') }}</label>
                        <div class="col-7">
                            <textarea class="form-control" id="test_message" rows="3" maxlength="320">Spideli test message.</textarea>
                            <div class="form-text text-muted"><span id="test_message_count">0</span>/160</div>
                            <div id="error_test_message" class="err"></div>
                        </div>
                    </div>

                    <div class="form-group row width-100">
                        <div class="col-4"></div>
                        <div class="col-7">
                            <button type="button" class="btn btn-primary" id="send_test"><i class="mdi mdi-send mr-2"></i>{{ trans('lang.sms_test_send') }}</button>
                        </div>
                    </div>

                    <div class="form-group row width-100">
                        <div class="col-12">
                            <div id="test_result" style="display:none"></div>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>

    <div class="form-group col-12 text-center btm-btn">
        <button type="button" class="btn btn-primary save-sms-btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
        <a href="{{ url('/dashboard') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var database = firebase.firestore();
    var settingsRef = database.collection('settings').doc('SMSGateway');

    /* The test sends through the server, which reads the key from the database
     * itself. Unsaved changes on screen are therefore NOT used - hence the
     * warning when the form is dirty. */
    var savedSnapshot = '';
    var testRoute = '{{ route('settings.app.smsGateway.test') }}';
    var balanceRoute = '{{ route('settings.app.smsGateway.balance') }}';

    function currentSnapshot() {
        return [
            $('#is_enabled').is(':checked') ? '1' : '0',
            $('#api_key').val().trim(),
            $('#sender_id').val().trim(),
            $('#api_url').val().trim()
        ].join('|');
    }

    $(document).ready(async function () {
        jQuery("#overlay").show();

        var doc = await settingsRef.get();

        if (doc.exists) {
            var data = doc.data();

            $('#is_enabled').prop('checked', data.isEnabled === true);
            $('#api_key').val(data.apiKey || '');
            $('#sender_id').val(data.senderId || '');
            $('#api_url').val(data.apiUrl || '');
        }

        savedSnapshot = currentSnapshot();
        updateCount();
        jQuery("#overlay").hide();
    });

    function updateCount() {
        $('#test_message_count').text($('#test_message').val().length);
    }

    $('#test_message').on('input', updateCount);

    $('.save-sms-btn').click(async function () {
        $('.err').html('');
        $('.error_top').hide().html('');
        $('.success_top').hide().html('');

        var isEnabled = $('#is_enabled').is(':checked');
        var apiKey = $('#api_key').val().trim();
        var senderId = $('#sender_id').val().trim();

        /* Only checked when SMS is switched on, so the credentials can be
         * cleared and the feature left off without the form objecting. */
        if (isEnabled && apiKey === '') {
            $('#error_api_key').html("{{ trans('lang.sms_api_key_error') }}");
            window.scrollTo(0, 0);
            return false;
        }

        /* The provider truncates anything longer, so the sender a customer
         * sees would not be the one entered here. */
        if (isEnabled && senderId.length > 11) {
            $('#error_sender_id').html("{{ trans('lang.sms_sender_id_error') }}");
            window.scrollTo(0, 0);
            return false;
        }

        jQuery("#overlay").show();

        await settingsRef.set({
            'isEnabled': isEnabled,
            'apiKey': apiKey,
            'senderId': senderId,
            'apiUrl': $('#api_url').val().trim(),
            'updatedAt': firebase.firestore.FieldValue.serverTimestamp()
        }, {merge: true});

        savedSnapshot = currentSnapshot();

        jQuery("#overlay").hide();
        $('.success_top').show().html('<p>{{ trans('lang.sms_saved') }}</p>');
        window.scrollTo(0, 0);
    });

    $('#check_balance').click(function () {
        $('#sms_balance').text('...');

        $.get(balanceRoute, function (response) {
            $('#sms_balance').text(response.success && response.solde !== null ?
                response.solde :
                "{{ trans('lang.sms_balance_unavailable') }}");
        }).fail(function () {
            $('#sms_balance').text("{{ trans('lang.sms_balance_unavailable') }}");
        });
    });

    $('#send_test').click(function () {
        $('.err').html('');
        $('#test_result').hide().html('');

        var number = $('#test_number').val().trim();
        var message = $('#test_message').val().trim();

        if (number === '') {
            $('#error_test_number').html("{{ trans('lang.sms_test_number_error') }}");
            return false;
        }

        if (message === '') {
            $('#error_test_message').html("{{ trans('lang.sms_test_message_error') }}");
            return false;
        }

        if (currentSnapshot() !== savedSnapshot) {
            $('#test_result').show()
                .attr('class', 'alert alert-warning')
                .html("{{ trans('lang.sms_test_save_first') }}");
            return false;
        }

        var $button = $('#send_test');
        $button.prop('disabled', true);

        $.post(testRoute, {
            _token: '{{ csrf_token() }}',
            destination: number,
            message: message
        }, function (response) {
            $button.prop('disabled', false);

            if (response.success) {
                $('#test_result').show()
                    .attr('class', 'alert alert-success')
                    .text("{{ trans('lang.sms_test_sent') }}");
                return;
            }

            /* The documented failures are worth saying plainly - "901" on its
             * own does not tell an admin to top up the account. */
            var explained = {
                901: "{{ trans('lang.sms_code_901') }}",
                902: "{{ trans('lang.sms_code_902') }}",
                903: "{{ trans('lang.sms_code_903') }}"
            };
            var text = explained[response.code] || response.message ||
                "{{ trans('lang.sms_provider_unreadable') }}";

            $('#test_result').show().attr('class', 'alert alert-danger').text(text);
        }).fail(function (xhr) {
            $button.prop('disabled', false);
            $('#test_result').show()
                .attr('class', 'alert alert-danger')
                .text("{{ trans('lang.sms_provider_unreachable') }}" + ' (' + xhr.status + ')');
        });
    });
</script>
@endsection
