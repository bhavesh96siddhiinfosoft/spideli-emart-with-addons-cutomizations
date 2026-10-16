@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $day = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d') : ''; };
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Investment opportunities'), 'tab' => 'opportunities'])
    <div class="container-fluid">
        <div class="card border mb-4">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <p class="mb-0 text-dark-2">{{ __('Projects looking for investors, shown in the app\'s Opportunities. Only for projects whose financing was reviewed and approved. Investors mark their interest; you share each one with the project owner.') }}</p>
                @if ($canManage)<a class="btn btn-primary text-nowrap ml-3" href="{{ route('finance.financing.opportunities.create') }}"><i class="mdi mdi-plus mr-1"></i>{{ __('New opportunity') }}</a>@endif
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped mb-0" id="financeOppTable">
                    <thead><tr><th>{{ __('Opportunity') }}</th><th>{{ __('Type') }}</th><th>{{ __('Raised / target') }}</th><th>{{ __('Closes') }}</th><th>{{ __('Status') }}</th><th>{{ __('Published') }}</th><th>{{ __('Interested') }}</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($rows as $r)
                        @php $d = $r['data']; $cur = $d['currency'] ?? ''; @endphp
                        <tr>
                            <td>{{ $d['title'] ?? '' }}<div class="finance-help">{{ $d['ownerName'] ?? '' }} · {{ $d['sector'] ?? '' }} · {{ $d['country'] ?? '' }}</div></td>
                            <td>{{ $d['type'] ?? '' }}</td>
                            <td class="finance-amount">{{ Money::format((int)($d['raisedAmount'] ?? 0), $cur) }} / {{ Money::format((int)($d['targetAmount'] ?? 0), $cur) }}</td>
                            <td>{{ $day($d['closingDate'] ?? null) }}</td>
                            <td>{{ $d['status'] ?? '' }}</td>
                            <td>@if (!empty($d['published']))<span class="finance-badge-ok">{{ __('Yes') }}</span>@else<span class="finance-badge-no">{{ __('No') }}</span>@endif</td>
                            <td>{{ $interested[$r['id']] ?? 0 }}</td>
                            <td><a class="btn btn-sm btn-primary" href="{{ route('finance.financing.opportunities.show', $r['id']) }}">{{ __('Open') }}</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>$(function () { $('#financeOppTable').DataTable({ pageLength: 25, order: [], language: typeof datatableLang !== 'undefined' ? datatableLang : {} }); });</script>
@endsection
