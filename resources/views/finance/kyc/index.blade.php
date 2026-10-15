@extends('layouts.app')
@section('content')
@php $fmt = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i') : ''; }; @endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('KYC review'), 'tab' => 'kyc'])
    <div class="container-fluid">
        <div class="card border mb-4">
            <div class="card-header border-0">
                <h3 class="text-dark-2 mb-1 h4">{{ __('Waiting for review') }} <span class="counter ml-2">{{ count($rows) }}</span></h3>
                <p class="mb-0 text-dark-2">{{ __('Identity verification applications, oldest first. Opening one keeps it for you while you work on it.') }}</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead><tr><th>{{ __('Submitted') }}</th><th>{{ __('Waiting') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Level') }}</th><th>{{ __('Account type') }}</th><th>{{ __('Revision') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                        <tbody>
                        @forelse ($rows as $r)
                            @php $d = $r['data']; $at = $d['submittedAt'] ?? $d['createdAt'] ?? null; @endphp
                            <tr>
                                <td>{{ $fmt($at) }}</td>
                                <td>{{ $at instanceof \DateTimeInterface ? \Carbon\Carbon::instance($at)->diffForHumans(null, true) : '' }}</td>
                                <td>{{ $names[$d['userId'] ?? ''] ?? ($d['personal']['fullName'] ?? $d['userId'] ?? '') }}</td>
                                <td>{{ $d['level'] ?? '' }}</td>
                                <td>{{ $d['accountType'] ?? '' }}</td>
                                <td>{{ $d['revision'] ?? 1 }}</td>
                                <td>{{ $d['status'] ?? '' }}
                                    @if (isset($locks['kyc_applications/' . $r['id']]))<div class="finance-help">{{ __('with') }} {{ $locks['kyc_applications/' . $r['id']] }}</div>@endif</td>
                                <td><a class="btn btn-sm btn-primary" href="{{ route('finance.kyc.show', $r['id']) }}">{{ __('Review') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-muted">{{ __('Nothing waiting.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
