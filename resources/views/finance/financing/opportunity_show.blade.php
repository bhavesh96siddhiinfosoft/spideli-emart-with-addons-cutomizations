@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $tz = new DateTimeZone(config('app.timezone'));
    $day = function ($v, $time = false) use ($tz) { return $v instanceof \DateTimeInterface ? $v->setTimezone($tz)->format($time ? 'Y-m-d H:i' : 'Y-m-d') : ''; };
    $cur = $o['currency'] ?? '';
    $yes = array_values(array_filter($interests, function ($i) { return !empty($i['data']['interested']); }));
    $no = count($interests) - count($yes);
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => $o['title'] ?? __('Opportunity'), 'tab' => 'opportunities'])
    <div class="container-fluid">
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
            <fieldset>
                <legend><i class="mr-3 mdi mdi-lightbulb"></i>{{ __('Opportunity') }}
                    @if ($canManage)<a class="btn btn-sm btn-outline-primary float-right" href="{{ route('finance.financing.opportunities.edit', $id) }}"><i class="mdi mdi-pencil"></i> {{ __('Edit') }}</a>@endif</legend>
                @foreach ([
                    __('Owner (private)') => $names[$ownerId] ?? ($ownerId ?: '—'),
                    __('Owner name shown') => $o['ownerName'] ?? '',
                    __('Type / sector / country') => ($o['type'] ?? '') . ' · ' . ($o['sector'] ?? '') . ' · ' . ($o['country'] ?? ''),
                    __('Minimum ticket') => Money::format((int)($o['minTicket'] ?? 0), $cur),
                    __('Raised / target') => Money::format((int)($o['raisedAmount'] ?? 0), $cur) . ' / ' . Money::format((int)($o['targetAmount'] ?? 0), $cur),
                    __('Horizon / risk') => (int)($o['horizonMonths'] ?? 0) . ' ' . __('months') . ' · ' . ($o['riskLevel'] ?? '') . '/7',
                    __('Target return') => isset($o['targetReturnPercent']) ? $o['targetReturnPercent'] . '%' : '',
                    __('Closes') => $day($o['closingDate'] ?? null),
                    __('Status') => ($o['status'] ?? '') . ' · ' . (!empty($o['published']) ? __('published') : __('not published')),
                ] as $label => $value)
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ $label }}</label><div class="col-7"><span class="form-control-plaintext">{{ trim($value, ' ·/') !== '' ? $value : '—' }}</span></div></div>
                @endforeach
                <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Summary') }}</label><div class="col-7"><span class="form-control-plaintext" style="white-space:pre-line">{{ $o['summary'] ?? '—' }}</span></div></div>
                @if ($canManage && !empty($o['published']) && ($o['status'] ?? '') === 'open')
                <div class="form-group row width-100"><div class="col-7">
                    <form method="POST" action="{{ route('finance.financing.opportunities.action', [$id, 'notify-matches']) }}">@csrf
                        <button class="btn btn-sm btn-outline-primary">{{ __('Notify matching investors') }}</button>
                        <span class="finance-help ml-1">{{ __('Investors whose preferences match (score at or above the region\'s minimum). Each one is told only once; done on publishing too.') }}</span></form>
                </div></div>
                @endif
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-account-heart"></i>{{ __('Interested investors') }} <span class="counter ml-2">{{ count($yes) }}</span></legend>
                <div class="form-group row width-100"><div class="col-7">
                    <p class="finance-help">{{ __('The investor agreed that their name and e-mail are shared with the project owner. Share each one with the button; the owner gets a notification. No money is committed.') }} @if ($no){{ __('Not for them:') }} {{ $no }}.@endif</p>
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Name') }}</th><th>{{ __('E-mail') }}</th><th>{{ __('Message') }}</th><th>{{ __('Shared') }}</th></tr></thead>
                        <tbody>
                        @forelse ($yes as $i)
                            @php $d = $i['data']; $uid = $d['userId'] ?? ''; $s = $shares[$uid] ?? null; @endphp
                            <tr><td>{{ $day($d['updatedAt'] ?? $d['createdAt'] ?? null) }}</td><td>{{ $d['name'] ?? '' }}</td><td>{{ $d['email'] ?? '' }}</td><td>{{ $d['message'] ?? '' }}</td>
                                <td>@if ($s){{ __('Yes') }}, {{ $day($s['sharedAt'] ?? null) }}
                                    @elseif ($canManage && $ownerId)<form method="POST" action="{{ route('finance.financing.opportunities.action', [$id, 'share']) }}" onsubmit="return confirm('{{ __('Share this investor with the project owner?') }}')">@csrf
                                        <input type="hidden" name="userId" value="{{ $uid }}"><button class="btn btn-sm btn-success">{{ __('Share with the owner') }}</button></form>
                                    @else — @endif</td></tr>
                        @empty
                            <tr><td colspan="5" class="text-muted">{{ __('Nobody yet.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div></div>
            </fieldset>
        </div></div>
        </div></div>
    </div>
</div>
@endsection
