@extends('layouts.app')
@section('content')
@php
    use App\Finance\Support;
    $tz = new DateTimeZone(config('app.timezone'));
    $fmt = function ($v) use ($tz) { return $v instanceof \DateTimeInterface ? $v->setTimezone($tz)->format('Y-m-d H:i') : ''; };
    $left = function ($s) {
        if (!$s) { return ''; }
        if ($s['met'] !== null) { return $s['met'] ? __('met') : __('late'); }
        $h = abs($s['left']) / 3600;
        $text = $h >= 48 ? round($h / 24) . ' ' . __('days') : round($h, 1) . ' h';
        return $s['late'] ? __('late by') . ' ' . $text : $text . ' ' . __('left');
    };
    $tabs = ['active' => __('Open'), 'resolved' => __('Resolved'), 'closed' => __('Closed')];
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Support tickets'), 'tab' => 'tickets'])
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="mb-0 text-dark-2">{{ __('Staff-only tickets for a customer\'s problem (a chat, a complaint, a failed operation). Time to resolve:') }}
                @foreach (Support::PRIORITIES as $p){{ __(\App\Finance\Support::priorityLabel($p)) }} {{ Support::slaHours($p) }} h{{ $loop->last ? '.' : ', ' }}@endforeach</p>
            @if ($canManage)<a class="btn btn-primary text-nowrap ml-3" href="{{ route('finance.tickets.create') }}"><i class="mdi mdi-plus mr-1"></i>{{ __('Open a ticket') }}</a>@endif
        </div>
        <ul class="nav nav-tabs mb-3">
            @foreach ($tabs as $k => $label)
                <li class="nav-item"><a class="nav-link {{ $tab === $k && !$mine ? 'active' : '' }}" href="{{ route('finance.tickets', ['tab' => $k]) }}">{{ $label }}</a></li>
            @endforeach
            <li class="nav-item"><a class="nav-link {{ $mine ? 'active' : '' }}" href="{{ route('finance.tickets', ['tab' => 'active', 'mine' => 1]) }}">{{ __('Assigned to me') }}</a></li>
        </ul>
        <div class="card border mb-4"><div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="financeTicketsTable">
                    <thead><tr><th>{{ __('Opened') }}</th><th>{{ __('Subject') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Category') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Assigned to') }}</th><th>{{ __('Status') }}</th><th>{{ __('Time to resolve') }}</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($rows as $r)
                        @php $d = $r['data']; $s = Support::sla($d, $now); @endphp
                        <tr>
                            <td data-order="{{ ($d['createdAt'] ?? null) instanceof \DateTimeInterface ? $d['createdAt']->getTimestamp() : 0 }}">{{ $fmt($d['createdAt'] ?? null) }}</td>
                            <td>{{ $d['subject'] ?? '' }}@if (!empty($d['relatedRef']))<div class="finance-help finance-mono">{{ $d['relatedRef'] }}</div>@endif</td>
                            <td>{{ $names[$d['userId'] ?? ''] ?? ($d['userId'] ?? '') }}</td>
                            <td>{{ __(ucfirst($d['category'] ?? '')) }}</td>
                            <td>{{ __(\App\Finance\Support::priorityLabel($d['priority'] ?? '')) }}</td>
                            <td>{{ $d['assignedToEmail'] ?? '' }}</td>
                            <td>{{ $d['status'] ?? '' }}</td>
                            <td data-order="{{ $s ? $s['due']->getTimestamp() : 0 }}">@if ($s && ($s['late'] || $s['met'] === false))<span class="finance-badge-no">{{ $left($s) }}</span>@elseif ($s)<span class="finance-badge-ok">{{ $left($s) }}</span>@endif</td>
                            <td><a class="btn btn-sm btn-primary" href="{{ route('finance.tickets.show', $r['id']) }}">{{ __('Open') }}</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
</div>
@endsection
@section('scripts')
<script>$(function () { $('#financeTicketsTable').DataTable({ pageLength: 25, order: [], language: typeof datatableLang !== 'undefined' ? datatableLang : {} }); });</script>
@endsection
