@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $fmt = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d') : ''; };
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Collective savings'), 'tab' => 'savings'])
    <div class="container-fluid">

        @if ($requests || $cancellations)
        <div class="card border mb-4">
            <div class="card-header border-0">
                <h3 class="text-dark-2 mb-1 h4">{{ __('Waiting for a decision') }} <span class="counter ml-2">{{ count($requests) + count($cancellations) }}</span></h3>
                <p class="mb-0 text-dark-2">{{ __('Group goals: a member\'s withdrawal request, or the group asking to cancel the goal. Solo goals never need the panel.') }}</p>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Goal') }}</th><th>{{ __('Request') }}</th><th>{{ __('Member') }}</th><th>{{ __('Amount') }}</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($requests as $r)
                        @php $d = $r['data']; @endphp
                        <tr>
                            <td>{{ $fmt($d['createdAt'] ?? null) }}</td>
                            <td>{{ $goalNames[$d['goalId'] ?? ''] ?? ($d['goalId'] ?? '') }}</td>
                            <td>{{ __('Withdrawal') }}@if (!empty($d['reason']))<div class="finance-help">{{ $d['reason'] }}</div>@endif</td>
                            <td>{{ $names[$d['userId'] ?? ''] ?? '' }}</td>
                            <td class="finance-amount">{{ Money::format((int)($d['amount'] ?? 0), $d['currency'] ?? '') }}</td>
                            <td><a class="btn btn-sm btn-primary" href="{{ route('finance.savings.show', $d['goalId'] ?? '') }}">{{ __('Open') }}</a></td>
                        </tr>
                    @endforeach
                    @foreach ($cancellations as $g)
                        @php $d = $g['data']; @endphp
                        <tr>
                            <td>{{ $fmt($d['cancellationRequestedAt'] ?? $d['updatedAt'] ?? null) }}</td>
                            <td>{{ $d['name'] ?? '' }}</td>
                            <td>{{ __('Cancel the goal') }}</td>
                            <td>{{ $names[$d['cancellationRequestedBy'] ?? ($d['ownerId'] ?? '')] ?? '' }}</td>
                            <td class="finance-amount">{{ Money::format((int)($d['savedAmount'] ?? 0), $d['currency'] ?? '') }}</td>
                            <td><a class="btn btn-sm btn-primary" href="{{ route('finance.savings.show', $g['id']) }}">{{ __('Open') }}</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="card border mb-4">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="text-dark-2 mb-1 h4">{{ __('Savings goals') }}</h3>
                    <p class="mb-0 text-dark-2">{{ __('Goals customers save towards, alone or as a group.') }}</p>
                </div>
                <form method="GET" class="form-inline">
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        @foreach (['active' => __('Active'), 'cancellation_requested' => __('Cancellation requested'), 'completed' => __('Completed'), 'cancelled' => __('Cancelled'), 'all' => __('All')] as $k => $label)
                            <option value="{{ $k }}" {{ $status === $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="financeSavingsTable">
                        <thead><tr><th>{{ __('Goal') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Members') }}</th><th>{{ __('Saved') }}</th><th>{{ __('Target') }}</th><th>{{ __('Deadline') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($rows as $r)
                            @php $d = $r['data']; $saved = (int)($d['savedAmount'] ?? 0); $target = (int)($d['targetAmount'] ?? 0); @endphp
                            <tr>
                                <td>{{ $d['name'] ?? '' }} @if (!empty($d['demo']))<span class="badge badge-secondary">demo</span>@endif<div class="finance-help">{{ $d['purpose'] ?? '' }}</div></td>
                                <td>{{ $names[$d['ownerId'] ?? ''] ?? '' }}</td>
                                <td>{{ count((array)($d['memberIds'] ?? [])) }}</td>
                                <td class="finance-amount">{{ Money::format($saved, $d['currency'] ?? '') }}@if ($target)<div class="finance-help">{{ (int)round($saved * 100 / $target) }}%</div>@endif</td>
                                <td class="finance-amount">{{ Money::format($target, $d['currency'] ?? '') }}</td>
                                <td>{{ $fmt($d['deadline'] ?? null) }}</td>
                                <td>{{ $d['status'] ?? '' }}</td>
                                <td><a class="btn btn-sm btn-primary" href="{{ route('finance.savings.show', $r['id']) }}">{{ __('Open') }}</a></td>
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
<script>$(function () { $('#financeSavingsTable').DataTable({ pageLength: 25, language: typeof datatableLang !== 'undefined' ? datatableLang : {} }); });</script>
@endsection
