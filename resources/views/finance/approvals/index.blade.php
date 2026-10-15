@extends('layouts.app')
@section('content')
@php use App\Finance\Money; @endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Approvals'), 'tab' => 'approvals'])
    <div class="container-fluid">
        <div class="card border mb-4">
            <div class="card-header border-0">
                <h3 class="text-dark-2 mb-1 h4">{{ __('Waiting for a second admin') }}</h3>
                <p class="mb-0 text-dark-2">{{ __('Reversals and manual adjustments at or above the threshold. The admin who asked cannot approve their own request.') }}</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr><th>#</th><th>{{ __('Date') }}</th><th>{{ __('Region') }}</th><th>{{ __('Action') }}</th><th>{{ __('Details') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Reason') }}</th><th>{{ __('Asked by') }}</th><th>{{ __('Status') }}</th><th></th></tr>
                        </thead>
                        <tbody>
                        @forelse ($rows as $r)
                            @php $p = json_decode($r->payload, true) ?: []; $res = $r->result ? json_decode($r->result, true) : null; @endphp
                            <tr>
                                <td>{{ $r->id }}</td>
                                <td class="finance-mono">{{ $r->created_at }}</td>
                                <td>{{ $r->region_id ? ($regionNames[$r->region_id] ?? $r->region_id) : '—' }}</td>
                                <td>{{ $r->action }}</td>
                                <td class="finance-mono">
                                    @if ($r->action === 'reversal')
                                        <a href="{{ route('finance.ledger.show', $p['txId'] ?? '') }}">{{ $p['reference'] ?: ($p['txId'] ?? '') }}</a>
                                    @else
                                        {{ ($p['direction'] ?? '') === 'credit' ? __('into') : __('out of') }} wallet · {{ $p['userId'] ?? '' }}
                                    @endif
                                </td>
                                <td class="finance-amount">{{ Money::format((int)$r->amount, $r->currency) }}</td>
                                <td>{{ $r->reason }}</td>
                                <td>{{ $r->requested_by_email }}</td>
                                <td>
                                    {{ $r->status }}
                                    @if ($r->decided_by_email)<div class="finance-help">{{ $r->decided_by_email }} · {{ $r->decided_at }}</div>@endif
                                    @if ($r->decision_note)<div class="finance-help">{{ $r->decision_note }}</div>@endif
                                    @if ($res && !empty($res['references']))<div class="finance-help finance-mono">{{ implode(', ', $res['references']) }}</div>@endif
                                    @if ($res && !empty($res['error']))<div class="finance-field-error">{{ $res['error'] }}</div>@endif
                                </td>
                                <td style="min-width:220px">
                                    @if ($r->status === 'pending' && $canApprove && (int)$r->requested_by !== (int)$me)
                                        <form method="POST" action="{{ route('finance.approvals.approve', $r->id) }}" class="mb-1" onsubmit="return confirm('{{ __('Approve and execute now?') }}')">
                                            @csrf
                                            <input type="text" name="note" class="form-control form-control-sm mb-1" placeholder="{{ __('Note (optional)') }}">
                                            <button class="btn btn-sm btn-success">{{ __('Approve') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('finance.approvals.reject', $r->id) }}" onsubmit="return confirm('{{ __('Reject this request?') }}')">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger">{{ __('Reject') }}</button>
                                        </form>
                                    @elseif ($r->status === 'pending' && (int)$r->requested_by === (int)$me)
                                        <span class="finance-help">{{ __('Waiting for another admin.') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-muted">{{ __('Nothing waiting.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
