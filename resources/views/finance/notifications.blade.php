@extends('layouts.app')
@section('content')
@php
    use App\Finance\NotificationTypes;
    $categories = [null => __('Always sent'), 'payments' => __('Payments'), 'groups' => __('Groups'), 'loans' => __('Loans'),
        'investments' => __('Investments'), 'insurance' => __('Insurance'), 'news' => __('News')];
    $by = ['panel' => __('This panel'), 'app' => __('Customer apps / website'), 'cards' => __('Virtual cards (not built yet)')];
    $allTypes = [];
    foreach ($rows as $list) { foreach ($list as $type => $r) { $allTypes[] = $type; } }
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Finance notifications'), 'tab' => 'notifications'])
    <div class="container-fluid">
        <div class="card border mb-3"><div class="card-body">
            <p class="mb-2">{{ __('Every decision of the panel goes to the customer\'s Notification Center and as a push. The critical ones also go by SMS (withdrawals, transfers, bill codes, card security, overdue payments), and receipts and alerts by e-mail, as each customer chose in the app (Finance > Profile > Notifications). Without a choice: SMS and e-mail for payments only. Security notices and answers to the customer\'s own requests (identity, financing, support) always go.') }}</p>
            <div>
                <span class="mr-3">{{ __('SMS') }}:
                    @if (!$smsOn)<span class="finance-badge-no">{{ __('off (FINANCE_NOTIFY_SMS)') }}</span>
                    @elseif ($smsReady)<span class="finance-badge-ok">{{ __('on') }}</span>
                    @else<span class="finance-badge-no">{{ __('the SMS gateway is off') }}</span> <a href="{{ route('settings.app.smsGateway') }}">{{ __('SMS gateway settings') }}</a>@endif</span>
                <span class="mr-3">{{ __('E-mail') }}:
                    @if (!$emailOn)<span class="finance-badge-no">{{ __('off (FINANCE_NOTIFY_EMAIL)') }}</span>
                    @elseif ($mailReady)<span class="finance-badge-ok">{{ __('on') }}</span>
                    @else<span class="finance-badge-no">{{ __('the panel\'s mail is not set up') }}</span>@endif</span>
                <span>{{ __('Templates missing') }}: <span class="{{ $missing ? 'finance-badge-no' : 'finance-badge-ok' }}">{{ $missing }}</span></span>
            </div>
            @if ($canManage)
            <div class="d-flex flex-wrap mt-3">
                @if ($missing)
                <form method="POST" action="{{ route('finance.notifications.create-missing') }}" class="mr-3 mb-2" onsubmit="return confirm('{{ __('Create the missing templates with the default English text?') }}')">@csrf
                    <button class="btn btn-primary"><i class="mdi mdi-plus"></i> {{ __('Create missing templates') }} ({{ $missing }})</button>
                </form>
                @endif
                <form method="POST" action="{{ route('finance.notifications.test') }}" class="d-flex flex-wrap align-items-center mb-2">@csrf
                    <select name="type" class="form-control mr-2 mb-1" style="max-width:260px">
                        @foreach ($allTypes as $t)<option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach
                    </select>
                    <input type="text" name="phone" class="form-control mr-2 mb-1" style="max-width:170px" placeholder="{{ __('Phone, e.g. 237652365869') }}" value="{{ old('phone') }}">
                    <input type="email" name="email" class="form-control mr-2 mb-1" style="max-width:220px" placeholder="{{ __('E-mail') }}" value="{{ old('email') }}">
                    <button class="btn btn-outline-primary mb-1">{{ __('Send a test') }}</button>
                </form>
            </div>
            <p class="finance-help mb-0">{{ __('A test fills the template with sample values and sends it to the number / address typed here only; nothing is written for any customer.') }}</p>
            @endif
        </div></div>

        @php $canEditTemplates = in_array('dynamic-notification.save', json_decode(session('user_permissions') ?? '[]', true) ?: [], true); @endphp
        <div class="card border mb-4">
            <div class="card-header border-0">
                <h3 class="text-dark-2 mb-1 h4">{{ __('Notification types') }}</h3>
                <p class="mb-0 text-dark-2">{{ __('Every Spideli Finance notification, with its channels and its template. Search by module, type or text.') }}</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0" id="financeNotificationsTable">
                    <thead><tr><th>{{ __('Module') }}</th><th>{{ __('Type') }}</th><th>{{ __('Sent by') }}</th><th>{{ __('Customer choice') }}</th><th>{{ __('Channels') }}</th><th>{{ __('Template') }}</th><th>{{ trans('lang.actions') }}</th></tr></thead>
                    <tbody>
                    @foreach (NotificationTypes::MODULES as $module => $label)
                    @foreach ($rows[$module] ?? [] as $type => $r)
                        <tr>
                            <td>{{ __($label) }}</td>
                            <td class="finance-mono">{{ $type }}</td>
                            <td>{{ $by[$r['by']] }}</td>
                            <td>{{ $categories[$r['category']] }}</td>
                            <td class="text-nowrap">@if ($r['by'] === 'app')<span class="finance-help">{{ __('sent by the apps') }}</span>@else
                                {{ __('Push') }}@if ($r['sms']) · {{ __('SMS') }}@endif @if ($r['email']) · {{ __('E-mail') }}@endif @endif</td>
                            <td>
                                @if ($r['template'])
                                    <strong>{{ $r['template']['subject'] }}</strong>
                                    <div class="finance-help">{{ $r['template']['message'] }}</div>
                                    @if ($r['unknown'])<div class="finance-field-error">{{ __('Uses') }} {{ '{' . implode('}, {', $r['unknown']) . '}' }}, {{ __('which the panel does not fill. It can use:') }} {{ $r['fills'] ? '{' . implode('}, {', $r['fills']) . '}' : __('no placeholder') }}</div>@endif
                                @else
                                    <span class="finance-badge-no">{{ __('missing') }}</span>
                                    <div class="finance-help">{{ __('Default:') }} {{ $r['subject'] }} — {{ $r['message'] }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($r['template'] && $canEditTemplates)
                                    <span class="action-btn"><a href="{{ route('dynamic-notification.save', $r['template']['id']) }}" data-toggle="tooltip" title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a></span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>$(function () { $('#financeNotificationsTable').DataTable({ pageLength: 25, order: [], columnDefs: [{ orderable: false, targets: [5, 6] }], language: typeof datatableLang !== 'undefined' ? datatableLang : {} }); });</script>
@endsection
