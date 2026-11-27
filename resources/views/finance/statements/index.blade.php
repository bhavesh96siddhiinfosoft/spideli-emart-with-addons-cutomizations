@extends('layouts.app')
{{-- F22: Finance > Statement payments: the older months of history customers paid to unlock. --}}
@section('content')
@php
    use App\Finance\Money;
    $fmt = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i') : ''; };
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Statement payments'), 'tab' => 'statements'])
    <div class="container-fluid">
        @if ($error)
            <div class="alert alert-danger">{{ $error }}</div>
        @endif
        <div class="card border mb-4">
            <div class="card-header border-0">
                <h3 class="text-dark-2 mb-1 h4">{{ __('Statement payments') }} <span class="counter ml-2">{{ count($payments) }}</span></h3>
                <p class="mb-0 text-dark-2">{{ __('Older months of history that customers paid to unlock, newest first. The free days and the price per month are set per region in Finance > Settings > Wallet.') }}</p>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('finance.statements') }}" class="form-inline mb-3">
                    <label class="mr-2" for="statements_region">{{ __('Region') }}</label>
                    <select id="statements_region" name="region" class="form-control mr-2">
                        <option value="">{{ __('All regions') }}</option>
                        @foreach ($regions as $id => $name)
                            <option value="{{ $id }}" {{ $region === $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                </form>
                @if ($totals)
                    <p class="mb-3">{{ __('Total') }}:
                        @foreach ($totals as $cur => $sum)<strong class="finance-amount mr-3">{{ Money::format((int)$sum, $cur) }}</strong>@endforeach
                    </p>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead><tr><th>{{ __('Paid on') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Region') }}</th><th>{{ __('Months unlocked') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Reference') }}</th></tr></thead>
                        <tbody>
                        @forelse ($payments as $p)
                            <tr>
                                <td>{{ $fmt($p['createdAt']) }}</td>
                                <td>@if ($p['userId'] !== '')<a href="{{ route('finance.customers.show', $p['userId']) }}">{{ $names[$p['userId']] ?? $p['userId'] }}</a>@endif</td>
                                <td>{{ $regions[$p['regionId']] ?? $p['regionId'] }}</td>
                                <td>{{ implode(', ', $p['months']) }} <span class="finance-help">({{ count($p['months']) }})</span></td>
                                <td class="finance-amount" data-order="{{ (int)$p['amount'] }}">{{ Money::format((int)$p['amount'], $p['currency']) }}</td>
                                <td>@if ($p['ledgerTxId'] !== '')<a href="{{ route('finance.ledger.show', $p['ledgerTxId']) }}">{{ $p['reference'] !== '' ? $p['reference'] : $p['ledgerTxId'] }}</a>@else{{ $p['reference'] }}@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted">{{ __('No statement payments yet.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
