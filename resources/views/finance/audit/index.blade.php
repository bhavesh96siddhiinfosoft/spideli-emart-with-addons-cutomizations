@extends('layouts.app')
@section('content')
@php use App\Finance\Value; @endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Audit log'), 'tab' => 'audit'])
    <div class="container-fluid">
        <div class="card border mb-4">
            <div class="card-header border-0">
                <h3 class="text-dark-2 mb-1 h4">{{ __('Audit log') }}</h3>
                <p class="mb-0 text-dark-2">{{ __('Every finance write made by the panel. Entries are never edited or deleted.') }}</p>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('finance.audit') }}" class="row">
                    <div class="col-md-2 form-group"><label>{{ __('From') }}</label><input type="date" name="from" class="form-control" value="{{ $from }}"></div>
                    <div class="col-md-2 form-group"><label>{{ __('To') }}</label><input type="date" name="to" class="form-control" value="{{ $to }}"></div>
                    <div class="col-md-5 form-group"><label>{{ __('Search') }}</label><input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="{{ __('admin e-mail, action, document, customer id…') }}"></div>
                    <div class="col-md-3 form-group d-flex align-items-end"><button class="btn btn-primary">{{ __('Filter') }}</button></div>
                </form>
                @if ($error)<div class="alert alert-danger">{{ $error }}</div>@endif
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="financeAuditTable">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Admin') }}</th><th>{{ __('Action') }}</th><th>{{ __('Document') }}</th><th>{{ __('Reason') }}</th><th>{{ __('Change') }}</th><th>IP</th></tr></thead>
                        <tbody>
                        @foreach ($rows as $r)
                            @php $d = $r['data']; $at = $d['at'] ?? null; @endphp
                            <tr>
                                <td class="finance-mono" data-order="{{ $at instanceof \DateTimeInterface ? $at->getTimestamp() : 0 }}">{{ $at instanceof \DateTimeInterface ? $at->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i:s') : '' }}</td>
                                <td>{{ $d['adminEmail'] ?? '' }}<div class="finance-help">{{ $d['role'] ?? '' }}</div></td>
                                <td class="finance-mono">{{ $d['action'] ?? '' }}</td>
                                <td class="finance-mono">{{ ($d['collection'] ?? '') . '/' . ($d['docId'] ?? '') }}</td>
                                <td>{{ $d['reason'] ?? '' }}</td>
                                <td>
                                    <details>
                                        <summary>{{ __('Show') }}</summary>
                                        <div class="finance-help">{{ __('Before') }}</div>
                                        <pre class="finance-mono" style="white-space:pre-wrap;max-width:520px">{{ json_encode(Value::plain($d['before'] ?? null), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                        <div class="finance-help">{{ __('After') }}</div>
                                        <pre class="finance-mono" style="white-space:pre-wrap;max-width:520px">{{ json_encode(Value::plain($d['after'] ?? null), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                </td>
                                <td class="finance-mono">{{ $d['ip'] ?? '' }}</td>
                            </tr>
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
<script>
    $(function () {
        $('#financeAuditTable').DataTable({ order: [[0, 'desc']], pageLength: 25, searching: false,
            language: typeof datatableLang !== 'undefined' ? datatableLang : {} });
    });
</script>
@endsection
