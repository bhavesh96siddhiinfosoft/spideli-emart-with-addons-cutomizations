@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $fmt = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d') : ''; };
    $cur = $t['currency'] ?? '';
    $status = $t['status'] ?? '';
    $active = array_values(array_filter($members, function ($m) { return in_array($m['data']['status'] ?? '', ['active', 'restricted'], true); }));
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => $t['name'] ?? __('Tontine'), 'tab' => 'tontines'])
    <div class="container-fluid">
        @if ($lockedBy)<div class="alert alert-warning">{{ __('Another admin is working on this tontine:') }} {{ $lockedBy }}</div>@endif
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">

            <fieldset>
                <legend><i class="mr-3 mdi mdi-account-group"></i>{{ __('Tontine') }}</legend>
                @foreach ([
                    __('Name') => ($t['name'] ?? '') . (!empty($t['demo']) ? ' (demo)' : ''),
                    __('Status') => $status,
                    __('Contribution') => Money::format((int)($t['contributionAmount'] ?? 0), $cur) . ' · ' . ($t['frequency'] ?? ''),
                    __('Members') => count((array)($t['memberIds'] ?? [])) . ' / ' . ($t['membersLimit'] ?? ''),
                    __('Current cycle') => $t['currentCycle'] ?? 0,
                    __('Next due') => $fmt($t['nextDueAt'] ?? null),
                    __('Payout order') => $t['payoutOrderMethod'] ?? '',
                    __('Pot') => $pot ? Money::format((int)($pot['data']['balance'] ?? 0), $cur) : __('no account'),
                ] as $label => $value)
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ $label }}</label><div class="col-7"><span class="form-control-plaintext">{{ $value !== '' ? $value : '—' }}</span></div></div>
                @endforeach
                @if (!empty($t['rules']))<div class="form-group row width-100"><label class="col-3 control-label">{{ __('Rules') }}</label><div class="col-7"><span class="form-control-plaintext">{{ $t['rules'] }}</span></div></div>@endif
                <div class="form-group row width-100"><div class="col-7">
                    @if ($flags)
                        <div class="alert alert-danger mb-0"><strong>{{ __('Audit:') }}</strong><ul class="mb-0">@foreach ($flags as $f)<li>{{ $f }}</li>@endforeach</ul></div>
                    @else
                        <div class="alert alert-success mb-0">{{ __('Audit: every cycle\'s collected amount and the pot balance add up.') }}</div>
                    @endif
                </div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-calendar-check"></i>{{ __('Cycles and contributions') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>{{ __('Cycle') }}</th><th>{{ __('Due') }}</th><th>{{ __('Beneficiary') }}</th><th>{{ __('Collected') }}</th><th>{{ __('Status') }}</th>
                            @foreach ($active as $m)<th class="text-center">{{ $names[$m['data']['userId'] ?? ''] ?? ($m['data']['displayName'] ?? '') }}</th>@endforeach</tr></thead>
                        <tbody>
                        @forelse ($cycles as $c)
                            @php $d = $c['data']; $late = ($d['dueDate'] ?? null) instanceof \DateTimeInterface && $d['dueDate'] < new \DateTimeImmutable(); @endphp
                            <tr>
                                <td>{{ $d['number'] ?? '' }}</td>
                                <td>{{ $fmt($d['dueDate'] ?? null) }}</td>
                                <td>{{ $names[$d['beneficiaryUserId'] ?? ''] ?? '' }}</td>
                                <td class="finance-amount">{{ Money::format((int)($d['collectedAmount'] ?? 0), $cur) }}<div class="finance-help">/ {{ Money::format((int)($d['expectedAmount'] ?? 0), $cur) }}</div></td>
                                <td>{{ $d['status'] ?? '' }}</td>
                                @foreach ($active as $m)
                                    @php $uid = $m['data']['userId'] ?? ''; @endphp
                                    <td class="text-center">
                                        @if (isset($paidBy[$c['id']][$uid]))<span class="finance-badge-ok">{{ __('paid') }}</span>
                                        @elseif (in_array($d['status'] ?? '', ['collecting', 'payout_pending'], true))<span class="{{ $late ? 'finance-badge-no' : 'finance-help' }}">{{ $late ? __('overdue') : __('not yet') }}</span>
                                        @else<span class="finance-help">—</span>@endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted">{{ __('No cycle yet (the tontine has not started).') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    </div>
                    <div class="finance-help mt-1">{{ __('"Overdue" is shown, never written: the member can still pay. The daily job sends the overdue notice.') }}</div>
                </div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-account-multiple"></i>{{ __('Members') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>{{ __('Position') }}</th><th>{{ __('Member') }}</th><th>{{ __('Role') }}</th><th>{{ __('Status') }}</th></tr></thead>
                        <tbody>
                        @foreach ($members as $m)
                            @php $d = $m['data']; @endphp
                            <tr><td>{{ $d['payoutPosition'] ?? '' }}</td>
                                <td><a href="{{ route('finance.customers.show', $d['userId'] ?? '') }}">{{ $names[$d['userId'] ?? ''] ?? ($d['displayName'] ?? '') }}</a></td>
                                <td>{{ $d['role'] ?? '' }}</td><td>{{ $d['status'] ?? '' }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div></div>
            </fieldset>

            @if ($canManage && $pending)
            <fieldset>
                <legend><i class="mr-3 mdi mdi-cash-check"></i>{{ __('Payout waiting for the panel') }}</legend>
                @foreach ($pending as $p)
                    @php $d = $p['data']; @endphp
                    <div class="form-group row width-100"><div class="col-7">
                        <p>{{ __('Cycle') }} {{ $d['cycleNumber'] ?? '' }}: {{ $names[$d['beneficiaryUserId'] ?? ''] ?? '' }} {{ __('is the beneficiary and the only admin, so nobody else can approve it. Approving pays the cycle\'s collected amount from the pot to their wallet and moves the tontine to the next cycle.') }}</p>
                        <form method="POST" action="{{ route('finance.tontines.action', [$id, 'approve-payout']) }}" onsubmit="return confirm('{{ __('Approve and pay this payout?') }}')">@csrf
                            <input type="hidden" name="payoutId" value="{{ $p['id'] }}"><button class="btn btn-success">{{ __('Approve and pay') }}</button></form>
                    </div></div>
                @endforeach
            </fieldset>
            @endif

            <fieldset>
                <legend><i class="mr-3 mdi mdi-alert-circle"></i>{{ __('Disputes') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    @forelse ($disputes as $x)
                        @php $d = $x['data']; $openD = in_array($d['status'] ?? '', ['open', 'under_review'], true); @endphp
                        <div class="border rounded p-2 mb-2">
                            <strong>{{ $d['subject'] ?? '' }}</strong> <span class="finance-help">· {{ $d['status'] ?? '' }} · {{ $fmt($d['createdAt'] ?? null) }}</span>
                            <div>{{ $d['description'] ?? '' }}</div>
                            <div class="finance-help">{{ __('Raised by') }} {{ $names[$d['raisedBy'] ?? ''] ?? '' }}@if (!empty($d['againstUserId'])) · {{ __('against') }} {{ $names[$d['againstUserId']] ?? $d['againstUserId'] }}@endif</div>
                            @if (!empty($d['resolution']))<div class="mt-1"><em>{{ $d['resolution'] }}</em></div>@endif
                            @if ($canManage && $openD)
                                <form method="POST" action="{{ route('finance.tontines.action', [$id, 'dispute']) }}" class="mt-2">@csrf
                                    <input type="hidden" name="disputeId" value="{{ $x['id'] }}">
                                    @if (($d['status'] ?? '') === 'open')<button name="decision" value="review" class="btn btn-sm btn-primary mb-2">{{ __('Start review') }}</button>@endif
                                    <textarea name="reason" class="form-control mb-1" rows="2" placeholder="{{ __('Resolution (the members see it)') }}"></textarea>
                                    @if (!empty($d['againstUserId']))
                                        <div class="form-check"><input type="checkbox" id="rm-{{ $x['id'] }}" name="restrictMember" value="1"><label class="control-label" for="rm-{{ $x['id'] }}">{{ __('Restrict that member in this tontine (when resolved)') }}</label></div>
                                    @endif
                                    <button name="decision" value="resolved" class="btn btn-sm btn-success">{{ __('Resolve') }}</button>
                                    <button name="decision" value="rejected" class="btn btn-sm btn-outline-danger">{{ __('Reject') }}</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="finance-help mb-0">{{ __('No dispute.') }}</p>
                    @endforelse
                </div></div>
            </fieldset>

            @if ($canManage && in_array($status, ['active', 'suspended'], true))
            <fieldset>
                <legend><i class="mr-3 mdi mdi-pause-circle"></i>{{ $status === 'active' ? __('Suspend') : __('Resume') }}</legend>
                <form method="POST" action="{{ route('finance.tontines.action', [$id, $status === 'active' ? 'suspend' : 'resume']) }}" onsubmit="return confirm('{{ $status === 'active' ? __('Suspend this tontine?') : __('Resume this tontine?') }}')">@csrf
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Reason') }}<span class="required-field"></span></label><div class="col-7"><input type="text" name="reason" class="form-control" required></div></div>
                    <div class="form-group row width-50"><div class="col-7 pt-4"><button class="btn {{ $status === 'active' ? 'btn-danger' : 'btn-primary' }}">{{ $status === 'active' ? __('Suspend') : __('Resume') }}</button></div></div>
                </form>
            </fieldset>
            @endif

        </div></div>
        <div class="form-group col-12 text-center btm-btn">
            <a href="{{ route('finance.tontines') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ __('Back') }}</a>
        </div>
        </div></div>
    </div>
</div>
@endsection
