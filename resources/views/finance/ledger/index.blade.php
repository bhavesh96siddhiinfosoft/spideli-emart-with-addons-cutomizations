@extends('layouts.app')
@section('content')
@php use App\Finance\Money; @endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Ledger'), 'tab' => 'ledger'])
    <div class="container-fluid">
        <div class="card border mb-4">
            <div class="card-header border-0 d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="text-dark-2 mb-1 h4">{{ __('Ledger history') }}</h3>
                    <p class="mb-0 text-dark-2">{{ __('Every posting of Spideli Finance. Postings are never edited or deleted: a correction is a reversal or a manual adjustment.') }}</p>
                </div>
                @if ($canAdjust)
                    <a class="btn btn-primary" href="{{ route('finance.adjust.create') }}"><i class="mdi mdi-plus mr-1"></i>{{ __('Manual adjustment') }}</a>
                @endif
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('finance.ledger') }}" class="row">
                    <div class="col-md-2 form-group"><label>{{ __('From') }}</label><input type="date" name="from" class="form-control" value="{{ $filters['from'] }}"></div>
                    <div class="col-md-2 form-group"><label>{{ __('To') }}</label><input type="date" name="to" class="form-control" value="{{ $filters['to'] }}"></div>
                    <div class="col-md-2 form-group"><label>{{ __('Type') }}</label>
                        <select name="type" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($types as $t)<option value="{{ $t }}" {{ $filters['type'] === $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group"><label>{{ __('Status') }}</label>
                        <select name="status" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            @foreach (['posted', 'reversed'] as $s)<option value="{{ $s }}" {{ $filters['status'] === $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group"><label>{{ __('Currency') }}</label>
                        <select name="currency" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($currencyCodes as $code => $label)<option value="{{ $code }}" {{ $filters['currency'] === $code ? 'selected' : '' }}>{{ $code }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group"><label>{{ __('Customer') }}</label><input type="text" name="user" class="form-control" value="{{ $filters['user'] }}" placeholder="{{ __('id, e-mail or phone') }}"></div>
                    <div class="col-md-3 form-group"><label>{{ __('Reference') }}</label><input type="text" name="reference" class="form-control finance-mono" value="{{ $filters['reference'] }}" placeholder="SPF-…"></div>
                    <div class="col-md-3 form-group d-flex align-items-end">
                        <button class="btn btn-primary mr-2">{{ __('Filter') }}</button>
                        <a class="btn btn-light" href="{{ route('finance.ledger') }}">{{ __('Reset') }}</a>
                        <a class="btn btn-outline-info ml-2" href="{{ route('finance.ledger.export', request()->only(['from', 'to', 'type', 'status', 'currency', 'user', 'reference'])) }}"
                           title="{{ __('Every posting matching these filters (up to 20 000), with the raw amounts, fees, accounts and source') }}"><i class="mdi mdi-download"></i> {{ __('Download all (CSV)') }}</a>
                    </div>
                </form>
                @if ($filters['userNote'])<div class="alert alert-warning">{{ $filters['userNote'] }}</div>@endif
                @if ($capped)<div class="alert alert-warning">{{ __('Showing the latest 1000 postings only. Narrow the dates to see the rest.') }}</div>@endif

                <div class="table-responsive">
                    <table id="financeLedgerTable" class="display nowrap table table-hover table-striped table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th><th>{{ __('Reference') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th>
                                <th>{{ __('From') }}</th><th>{{ __('To') }}</th><th>{{ __('Customers') }}</th><th>{{ __('Status') }}</th><th>{{ __('By') }}</th><th>{{ __('Description') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($rows as $r)
                            @php
                                $d = $r['data'];
                                $lines = $d['lines'] ?? [];
                                $at = $d['createdAt'] ?? null;
                                $debit = collect($lines)->firstWhere('direction', 'debit');
                                $credits = collect($lines)->where('direction', 'credit')->pluck('accountId')->implode(', ');
                                $amount = $debit['amount'] ?? (collect($lines)->first()['amount'] ?? 0);
                            @endphp
                            <tr>
                                <td class="finance-mono" data-order="{{ $at instanceof \DateTimeInterface ? $at->getTimestamp() : 0 }}">{{ $at instanceof \DateTimeInterface ? $at->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i') : '' }}</td>
                                <td class="finance-mono"><a href="{{ route('finance.ledger.show', $r['id']) }}">{{ $d['reference'] ?? $r['id'] }}</a>@if (!empty($d['demo'])) <span class="badge badge-secondary">demo</span>@endif</td>
                                <td>{{ $d['type'] ?? '' }}</td>
                                <td class="finance-amount" data-order="{{ (int)$amount }}">{{ Money::format((int)$amount, $d['currency'] ?? '') }}</td>
                                <td class="finance-mono">{{ $debit['accountId'] ?? '' }}</td>
                                <td class="finance-mono">{{ $credits }}</td>
                                <td>{{ collect($d['userIds'] ?? [])->map(function ($u) use ($users) { return $users[$u] ?? $u; })->implode(', ') }}</td>
                                <td>{{ $d['status'] ?? '' }}</td>
                                <td class="finance-mono">{{ $d['createdBy'] ?? '' }}</td>
                                <td>{{ $d['description'] ?? '' }}</td>
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
        $('#financeLedgerTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            language: typeof datatableLang !== 'undefined' ? datatableLang : {},
            dom: 'lfrtipB',
            buttons: [{
                extend: 'collection',
                text: '<i class="mdi mdi-cloud-download"></i> {{ trans('lang.export_as') }}',
                className: 'btn btn-info',
                buttons: [
                    { extend: 'csvHtml5', text: '{{ trans('lang.export_csv') }}', title: 'spideli-ledger-{{ $filters['from'] }}-{{ $filters['to'] }}' },
                    { extend: 'pdfHtml5', text: '{{ trans('lang.export_pdf') }}', orientation: 'landscape', pageSize: 'A4',
                      title: 'Spideli Finance ledger {{ $filters['from'] }} - {{ $filters['to'] }}' }
                ]
            }],
            initComplete: function () {
                $('.dataTables_filter').append($('.dt-buttons').detach());
            }
        });
    });
</script>
@endsection
