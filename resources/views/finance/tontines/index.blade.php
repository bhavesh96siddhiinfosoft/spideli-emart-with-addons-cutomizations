@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $fmt = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d') : ''; };
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Tontines'), 'tab' => 'tontines'])
    <div class="container-fluid">

        @if ($payouts || $disputes)
        <div class="card border mb-4">
            <div class="card-header border-0">
                <h3 class="text-dark-2 mb-1 h4">{{ __('Waiting for the panel') }} <span class="counter ml-2">{{ count($payouts) + count($disputes) }}</span></h3>
                <p class="mb-0 text-dark-2">{{ __('Payouts whose beneficiary is the tontine\'s only admin (nobody else can approve them in the app), and open disputes.') }}</p>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead><tr><th>{{ __('Tontine') }}</th><th>{{ __('Waiting') }}</th><th>{{ __('Who') }}</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($payouts as $p)
                        @php $d = $p['data']; @endphp
                        <tr><td>{{ $tontines[$d['tontineId'] ?? '']['name'] ?? '' }}</td>
                            <td>{{ __('Payout of cycle') }} {{ $d['cycleNumber'] ?? '' }}</td>
                            <td>{{ $names[$d['beneficiaryUserId'] ?? ''] ?? '' }}</td>
                            <td><a class="btn btn-sm btn-primary" href="{{ route('finance.tontines.show', $d['tontineId'] ?? '') }}">{{ __('Open') }}</a></td></tr>
                    @endforeach
                    @foreach ($disputes as $x)
                        @php $d = $x['data']; @endphp
                        <tr><td>{{ $tontines[$d['tontineId'] ?? '']['name'] ?? '' }}</td>
                            <td>{{ __('Dispute') }}: {{ $d['subject'] ?? '' }} <span class="finance-help">({{ $d['status'] ?? '' }})</span></td>
                            <td>{{ $names[$d['raisedBy'] ?? ''] ?? '' }}</td>
                            <td><a class="btn btn-sm btn-primary" href="{{ route('finance.tontines.show', $d['tontineId'] ?? '') }}">{{ __('Open') }}</a></td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="card border mb-4">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="text-dark-2 mb-1 h4">{{ __('Tontines') }}</h3>
                    <p class="mb-0 text-dark-2">{{ __('Groups that pay in each cycle; one member collects the pot per cycle.') }}</p>
                </div>
                <form method="GET" class="form-inline">
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        @foreach (['active' => __('Active'), 'forming' => __('Forming'), 'suspended' => __('Suspended'), 'completed' => __('Completed'), 'all' => __('All')] as $k => $label)
                            <option value="{{ $k }}" {{ $status === $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="financeTontinesTable">
                        <thead><tr><th>{{ __('Tontine') }}</th><th>{{ __('Contribution') }}</th><th>{{ __('Members') }}</th><th>{{ __('Cycle') }}</th><th>{{ __('Next due') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($rows as $r)
                            @php $d = $r['data']; @endphp
                            <tr>
                                <td>{{ $d['name'] ?? '' }} @if (!empty($d['demo']))<span class="badge badge-secondary">demo</span>@endif</td>
                                <td class="finance-amount">{{ Money::format((int)($d['contributionAmount'] ?? 0), $d['currency'] ?? '') }}<div class="finance-help">{{ $d['frequency'] ?? '' }}</div></td>
                                <td>{{ count((array)($d['memberIds'] ?? [])) }} / {{ $d['membersLimit'] ?? '' }}</td>
                                <td>{{ $d['currentCycle'] ?? 0 }}</td>
                                <td>{{ $fmt($d['nextDueAt'] ?? null) }}</td>
                                <td>{{ $d['status'] ?? '' }}</td>
                                <td><a class="btn btn-sm btn-primary" href="{{ route('finance.tontines.show', $r['id']) }}">{{ __('Open') }}</a></td>
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
<script>$(function () { $('#financeTontinesTable').DataTable({ pageLength: 25, language: typeof datatableLang !== 'undefined' ? datatableLang : {} }); });</script>
@endsection
