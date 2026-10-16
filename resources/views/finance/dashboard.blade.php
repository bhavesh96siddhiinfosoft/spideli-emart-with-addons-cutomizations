@extends('layouts.app')
@section('content')
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Finance dashboard'), 'tab' => 'dashboard'])
    <div class="container-fluid">

        @if ($stale !== null && ($stale['pairs'] === 0 || $stale['old'] > 0))
            <div class="alert alert-warning">
                @if ($stale['pairs'] === 0){{ __('No exchange rate is posted: customers cannot convert currencies.') }}
                @else{{ __('Exchange rates too old for the app (over') }} {{ $stale['maxAge'] }} h): {{ $stale['old'] }} / {{ $stale['pairs'] }}.@endif
                <a href="{{ route('finance.content', ['tab' => 'rates']) }}">{{ __('Post a rate') }}</a>
            </div>
        @endif

        @forelse ($queues as $area => $tiles)
            <h4 class="h5 text-dark-2 mt-2 mb-2">{{ $area }}</h4>
            <div class="row finance-tiles mb-3">
                @foreach ($tiles as $t)
                    @php $hot = $t['urgent'] && (int)$t['n'] > 0; @endphp
                    <div class="col-xl-3 col-lg-4 col-sm-6 mb-3">
                        <a class="card border h-100 finance-tile {{ $hot ? 'finance-tile-hot' : '' }}" href="{{ $t['url'] }}">
                            <div class="card-body">
                                <div class="finance-tile-n">{{ $t['n'] === null ? '—' : $t['n'] }}</div>
                                <div class="finance-tile-label">{{ $t['label'] }}</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="alert alert-info">{{ __('Your role has no Finance queue.') }}</div>
        @endforelse

        @if ($figures)
            <h4 class="h5 text-dark-2 mt-2 mb-2">{{ __('Key figures') }}
                @if ($canReports)<a class="btn btn-sm btn-link" href="{{ route('finance.reports') }}">{{ __('All reports') }} →</a>@endif</h4>
            <div class="row">
                @foreach ($figures as $key => $r)
                    <div class="col-lg-6 mb-3">
                        <div class="card border h-100"><div class="card-body">
                            <h5 class="text-dark-2">{{ $key === 'contributions' ? __('Contributions this month') : $r['title'] }}</h5>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead><tr>@foreach ($r['columns'] as $c)<th>{{ $c }}</th>@endforeach</tr></thead>
                                    <tbody>
                                    @forelse ($r['rows'] as $row)
                                        <tr>@foreach ($row as $i => $v)<td class="{{ $i > 0 ? 'finance-amount' : '' }}">{{ $v === null ? '—' : $v }}</td>@endforeach</tr>
                                    @empty
                                        <tr><td colspan="{{ count($r['columns']) }}" class="text-muted">{{ __('Nothing yet.') }}</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div></div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
<style>
    .finance-tile { text-decoration: none !important; color: inherit; transition: box-shadow .15s; }
    .finance-tile:hover { box-shadow: 0 2px 10px rgba(0,0,0,.08); }
    .finance-tile-n { font-size: 30px; font-weight: 700; line-height: 1.1; color: #263238; }
    .finance-tile-label { color: #67757c; margin-top: 4px; }
    .finance-tile-hot { border-color: #f0ad4e !important; }
    .finance-tile-hot .finance-tile-n { color: #c77700; }
</style>
@endsection
