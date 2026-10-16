@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    use App\Finance\Investments;
    $tz = new DateTimeZone(config('app.timezone'));
    $fmt = function ($v, $time = true) use ($tz) { return $v instanceof \DateTimeInterface ? $v->setTimezone($tz)->format($time ? 'Y-m-d H:i' : 'Y-m-d') : ''; };
    $cur = $f['currency'] ?? '';
    $nav = (float)($f['navPerUnit'] ?? 0);
    $navText = $nav > 0 ? Investments::navText($nav, $cur) : '—';
    $navDate = $fmt($f['navDate'] ?? null);
    $subFee = (float)($f['fees']['subscriptionPercent'] ?? 0);
    $redFee = (float)($f['fees']['redemptionPercent'] ?? 0);
    $holdingOf = [];
    foreach ($holdings as $h) { $holdingOf[$h['data']['userId'] ?? ''] = $h['data']; }
    $now = new DateTimeImmutable();
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => $f['name'] ?? __('Fund'), 'tab' => 'investments'])
    <div class="container-fluid">
        @if ($lockedBy)<div class="alert alert-warning">{{ __('Another admin is working on this fund:') }} {{ $lockedBy }}</div>@endif
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">

            <fieldset>
                <legend><i class="mr-3 mdi mdi-chart-line"></i>{{ __('Fund') }}
                    @if ($canManage)<a class="btn btn-sm btn-outline-primary float-right" href="{{ route('finance.investments.edit', $id) }}"><i class="mdi mdi-pencil"></i> {{ __('Edit') }}</a>@endif</legend>
                @foreach ([
                    __('Name') => ($f['name'] ?? '') . (!empty($f['demo']) ? ' (demo)' : ''),
                    __('Fund manager') => $f['manager'] ?? '',
                    __('Asset class') => __(ucfirst(str_replace('_', ' ', $f['assetClass'] ?? ''))),
                    __('Risk level') => ($f['riskLevel'] ?? '') . ' / 7',
                    __('Status') => ($f['status'] ?? '') . ' · ' . (!empty($f['published']) ? __('published') : __('not published')),
                    __('Minimum investment') => Money::format((int)($f['minInvestment'] ?? 0), $cur) . ' · ' . __('top-up') . ' ' . Money::format((int)($f['minAdditional'] ?? $f['minInvestment'] ?? 0), $cur),
                    __('Fees') => __('subscription') . ' ' . $subFee . '% · ' . __('redemption') . ' ' . $redFee . '% · ' . __('management') . ' ' . ($f['fees']['managementPercent'] ?? 0) . '% · ' . __('performance') . ' ' . ($f['fees']['performancePercent'] ?? 0) . '%',
                    __('Lock-up / settlement') => (int)($f['lockUpDays'] ?? 0) . ' ' . __('days') . ' / ' . (int)($f['settlementDays'] ?? 0) . ' ' . __('days'),
                    __('Redemption') => empty($f['redemption']['allowed']) ? __('not allowed') : (!empty($f['redemption']['partial']) ? __('full or partial') : __('full only')),
                    __('Terms version') => (int)($f['termsVersion'] ?? 1),
                ] as $label => $value)
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ $label }}</label><div class="col-7"><span class="form-control-plaintext">{{ $value !== '' ? $value : '—' }}</span></div></div>
                @endforeach
                <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Performance') }}</label><div class="col-7"><span class="form-control-plaintext">
                    @forelse ((array)($f['performance'] ?? []) as $k => $p)
                        <span class="mr-3">{{ ['ytd' => __('This year'), 'oneYear' => __('1 year'), 'threeYears' => __('3 years'), 'fiveYears' => __('5 years'), 'sinceInception' => __('Since launch')][$k] ?? $k }}: <strong>{{ $p }}%</strong></span>
                    @empty — @endforelse
                </span></div></div>
                <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Fund documents') }}</label><div class="col-7"><span class="form-control-plaintext">
                    @forelse ((array)($f['documents'] ?? []) as $i => $d)
                        <a class="mr-3" href="{{ route('finance.investments.document', [$id, $i]) }}" target="_blank" rel="noopener"><i class="mdi mdi-file-pdf-box"></i> {{ $d['name'] ?? __('Document') }}</a>
                    @empty — @endforelse
                </span></div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-tag"></i>{{ __('Unit price') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <p class="mb-2">{{ __('Current price') }}: <strong class="finance-amount">{{ $navText }}</strong> · {{ __('dated') }} {{ $navDate ?: '—' }}</p>
                    <p class="finance-help">{{ __('Orders are executed at the current price, with its date as the trade date. Add the day\'s price first, then run the waiting orders.') }}</p>
                    @if ($canManage)
                    <form method="POST" action="{{ route('finance.investments.action', [$id, 'add-price']) }}" class="form-inline mb-3" onsubmit="return confirm('{{ __('Add this unit price and revalue every holding?') }}')">@csrf
                        <input type="date" name="date" class="form-control mr-2 mb-1" value="{{ old('date', $now->setTimezone($tz)->format('Y-m-d')) }}" max="{{ $now->setTimezone($tz)->format('Y-m-d') }}" required>
                        <input type="time" name="time" class="form-control mr-2 mb-1" value="{{ old('time', $now->setTimezone($tz)->format('H:i')) }}">
                        <div class="finance-amount-box mr-2 mb-1" style="margin-bottom:4px"><span class="finance-symbol">{{ $cur }}</span>
                            <input type="text" inputmode="decimal" name="navPerUnit" class="form-control" style="padding-left:48px" placeholder="{{ __('Unit price') }}" value="{{ old('navPerUnit') }}" required></div>
                        <button class="btn btn-primary mb-1">{{ __('Add the price') }}</button>
                    </form>
                    <form method="POST" action="{{ route('finance.investments.action', [$id, 'revalue']) }}" class="d-inline">@csrf
                        <button class="btn btn-sm btn-outline-secondary">{{ __('Revalue the holdings now') }}</button>
                        <span class="finance-help ml-1">{{ __('Also runs every morning (finance:daily), so units become redeemable when their lock-up ends.') }}</span></form>
                    @endif
                    <table class="table table-bordered table-sm mt-3 mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Unit price') }}</th></tr></thead>
                        <tbody>
                        @forelse (array_slice($prices, 0, 15) as $p)
                            <tr><td>{{ $fmt($p['data']['date'] ?? null) }}</td><td class="finance-amount">{{ Investments::navText($p['data']['navPerUnit'] ?? 0, $cur) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-muted">{{ __('No price yet.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    @if (count($prices) > 15)<div class="finance-help">{{ __('Latest 15 of') }} {{ count($prices) }}.</div>@endif
                </div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-cart-arrow-down"></i>{{ __('Waiting subscriptions') }} <span class="counter ml-2">{{ count($waitingSubs) }}</span></legend>
                <div class="form-group row width-100"><div class="col-7">
                    @if ($waitingSubs)
                        <table class="table table-bordered table-sm mb-2">
                            <thead><tr>@if ($canManage)<th style="width:40px"></th>@endif<th>{{ __('Date') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Paid') }}</th><th>{{ __('Fee') }} ({{ $subFee }}%)</th><th>{{ __('Units at the current price') }}</th><th>{{ __('Reference') }}</th>@if ($canManage)<th>{{ __('Refuse') }}</th>@endif</tr></thead>
                            <tbody>
                            @foreach ($waitingSubs as $s)
                                @php $d = $s['data']; $amt = (int)($d['amount'] ?? 0); $fee = (int)round($amt * $subFee / 100); @endphp
                                <tr>
                                    @if ($canManage)<td><div class="form-check"><input type="checkbox" id="sub-{{ $s['id'] }}" name="ids[]" value="{{ $s['id'] }}" form="exec-subs" checked><label class="control-label" for="sub-{{ $s['id'] }}"></label></div></td>@endif
                                    <td>{{ $fmt($d['createdAt'] ?? null) }}</td>
                                    <td>{{ $names[$d['userId'] ?? ''] ?? ($d['userId'] ?? '') }}<div class="finance-help">{{ $d['signatureName'] ?? '' }} · {{ __('terms v') }}{{ $d['termsVersion'] ?? '?' }}</div></td>
                                    <td class="finance-amount">{{ Money::format($amt, $d['currency'] ?? $cur) }}</td>
                                    <td class="finance-amount">{{ Money::format($fee, $cur) }}</td>
                                    <td class="finance-amount">{{ $nav > 0 ? Investments::unitsText(($amt - $fee) / $nav) : '—' }}</td>
                                    <td class="finance-mono">@if (!empty($d['txId']))<a href="{{ route('finance.ledger.show', $d['txId']) }}">{{ $d['reference'] ?? $d['txId'] }}</a>@else{{ $d['reference'] ?? '' }}@endif</td>
                                    @if ($canManage)<td>
                                        <form method="POST" action="{{ route('finance.investments.action', [$id, 'refuse-subscription']) }}" class="d-flex" onsubmit="return confirm('{{ __('Refuse and give the money back to the customer\'s wallet?') }}')">@csrf
                                            <input type="hidden" name="subscriptionId" value="{{ $s['id'] }}">
                                            <input type="text" name="reason" class="form-control form-control-sm mr-1" placeholder="{{ __('Reason for the customer') }}" required>
                                            <button class="btn btn-outline-danger btn-sm">{{ __('Refuse') }}</button></form>
                                    </td>@endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if ($canManage)
                            <form method="POST" id="exec-subs" action="{{ route('finance.investments.action', [$id, 'execute-subscriptions']) }}" onsubmit="return confirm('{{ __('Execute the ticked subscriptions at') }} {{ $navText }}?')">@csrf
                                <button class="btn btn-success" {{ $nav > 0 ? '' : 'disabled' }}>{{ __('Execute the ticked at') }} {{ $navText }} ({{ $navDate }})</button></form>
                            <div class="finance-help mt-1">{{ __('Each one: the fee is kept, the rest buys units, the holding is created or added to and the customer is notified. The money was already paid; nothing is posted.') }}</div>
                        @endif
                    @else
                        <p class="text-muted mb-0">{{ __('Nothing waiting.') }}</p>
                    @endif
                </div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-cash-refund"></i>{{ __('Waiting redemptions') }} <span class="counter ml-2">{{ count($waitingReds) }}</span></legend>
                <div class="form-group row width-100"><div class="col-7">
                    @if ($waitingReds)
                        <table class="table table-bordered table-sm mb-2">
                            <thead><tr>@if ($canManage)<th style="width:40px"></th>@endif<th>{{ __('Date') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Asks') }}</th><th>{{ __('Redeemable now') }}</th><th>{{ __('Reference') }}</th>@if ($canManage)<th>{{ __('Refuse') }}</th>@endif</tr></thead>
                            <tbody>
                            @foreach ($waitingReds as $r)
                                @php $d = $r['data']; $h = $holdingOf[$d['userId'] ?? ''] ?? null; $hv = $h && $nav > 0 ? Investments::valued($h, $nav, $now) : null; @endphp
                                <tr>
                                    @if ($canManage)<td><div class="form-check"><input type="checkbox" id="red-{{ $r['id'] }}" name="ids[]" value="{{ $r['id'] }}" form="exec-reds" checked><label class="control-label" for="red-{{ $r['id'] }}"></label></div></td>@endif
                                    <td>{{ $fmt($d['createdAt'] ?? null) }}</td>
                                    <td>{{ $names[$d['userId'] ?? ''] ?? ($d['userId'] ?? '') }}</td>
                                    <td class="finance-amount">{{ !empty($d['full']) ? __('Everything redeemable') : Money::format((int)($d['amount'] ?? 0), $cur) }}</td>
                                    <td class="finance-amount">@if ($hv){{ Money::format($hv['redeemableValue'], $cur) }}<div class="finance-help">{{ Investments::unitsText($hv['redeemableUnits']) }} / {{ Investments::unitsText($hv['units']) }} {{ __('units') }}@if (($h['lockedUntil'] ?? null) instanceof \DateTimeInterface && $h['lockedUntil'] > $now) · {{ __('locked until') }} {{ $fmt($h['lockedUntil'], false) }}@endif</div>@else — @endif</td>
                                    <td class="finance-mono">{{ $d['reference'] ?? '' }}</td>
                                    @if ($canManage)<td>
                                        <form method="POST" action="{{ route('finance.investments.action', [$id, 'refuse-redemption']) }}" class="d-flex">@csrf
                                            <input type="hidden" name="redemptionId" value="{{ $r['id'] }}">
                                            <input type="text" name="reason" class="form-control form-control-sm mr-1" placeholder="{{ __('Reason for the customer') }}" required>
                                            <button class="btn btn-outline-danger btn-sm">{{ __('Refuse') }}</button></form>
                                    </td>@endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if ($canManage)
                            <form method="POST" id="exec-reds" action="{{ route('finance.investments.action', [$id, 'execute-redemptions']) }}" onsubmit="return confirm('{{ __('Execute the ticked redemptions at') }} {{ $navText }}?')">@csrf
                                <button class="btn btn-success" {{ $nav > 0 ? '' : 'disabled' }}>{{ __('Execute the ticked at') }} {{ $navText }} ({{ $navDate }})</button></form>
                            <div class="finance-help mt-1">{{ __('Each one: the units are sold (capped at what is redeemable), the fee is taken and the holding reduced. The money is paid when you settle it below.') }} {{ __('Redemption fee') }}: {{ $redFee }}%.</div>
                        @endif
                    @else
                        <p class="text-muted mb-0">{{ __('Nothing waiting.') }}</p>
                    @endif
                </div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-bank-transfer-in"></i>{{ __('To settle') }} <span class="counter ml-2">{{ count($settling) }}</span></legend>
                <div class="form-group row width-100"><div class="col-7">
                    @if ($settling)
                        <table class="table table-bordered table-sm mb-2">
                            <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Units') }}</th><th>{{ __('Gross') }}</th><th>{{ __('Fee') }}</th><th>{{ __('To pay') }}</th><th>{{ __('Expected') }}</th>@if ($canManage)<th></th>@endif</tr></thead>
                            <tbody>
                            @foreach ($settling as $r)
                                @php $d = $r['data']; $due = !(($d['expectedSettlementDate'] ?? null) instanceof \DateTimeInterface) || $d['expectedSettlementDate'] <= $now; @endphp
                                <tr>
                                    <td>{{ $names[$d['userId'] ?? ''] ?? ($d['userId'] ?? '') }}</td>
                                    <td class="finance-amount">{{ Investments::unitsText($d['units'] ?? 0) }}</td>
                                    <td class="finance-amount">{{ Money::format((int)($d['grossAmount'] ?? 0), $cur) }}</td>
                                    <td class="finance-amount">{{ Money::format((int)($d['fee'] ?? 0), $cur) }}</td>
                                    <td class="finance-amount"><strong>{{ Money::format((int)($d['netAmount'] ?? 0), $cur) }}</strong></td>
                                    <td>{{ $fmt($d['expectedSettlementDate'] ?? null, false) }} @if ($due)<span class="finance-badge-ok">{{ __('due') }}</span>@endif</td>
                                    @if ($canManage)<td>
                                        <form method="POST" action="{{ route('finance.investments.action', [$id, 'settle']) }}" onsubmit="return confirm('{{ __('Pay this redemption to the customer\'s Finance wallet?') }}')">@csrf
                                            <input type="hidden" name="redemptionId" value="{{ $r['id'] }}"><button class="btn btn-sm btn-success">{{ __('Pay to the wallet') }}</button></form>
                                    </td>@endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if ($canManage)
                            <form method="POST" action="{{ route('finance.investments.action', [$id, 'settle-due']) }}" onsubmit="return confirm('{{ __('Pay every redemption whose settlement date has come?') }}')">@csrf
                                <button class="btn btn-success">{{ __('Pay all that are due') }}</button></form>
                            <div class="finance-help mt-1">{{ __('Posts investment_redemption from the platform account to the customer\'s Finance wallet.') }}</div>
                        @endif
                    @else
                        <p class="text-muted mb-0">{{ __('Nothing to settle.') }}</p>
                    @endif
                </div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-account-cash"></i>{{ __('Holders') }} <span class="counter ml-2">{{ count($holdings) }}</span></legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered table-sm mb-0" id="fundHolders">
                        <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Units') }}</th><th>{{ __('Redeemable') }}</th><th>{{ __('Invested') }}</th><th>{{ __('Value') }}</th><th>{{ __('Unrealized') }}</th><th>{{ __('Realized') }}</th><th>{{ __('Valued') }}</th></tr></thead>
                        <tbody>
                        @foreach ($holdings as $h)
                            @php $d = $h['data']; @endphp
                            <tr>
                                <td>{{ $names[$d['userId'] ?? ''] ?? ($d['userId'] ?? '') }} @if (!empty($d['demo']))<span class="badge badge-secondary">demo</span>@endif</td>
                                <td class="finance-amount">{{ Investments::unitsText($d['units'] ?? 0) }}</td>
                                <td class="finance-amount">{{ Investments::unitsText($d['redeemableUnits'] ?? 0) }}@if (($d['lockedUntil'] ?? null) instanceof \DateTimeInterface && $d['lockedUntil'] > $now)<div class="finance-help">{{ __('locked until') }} {{ $fmt($d['lockedUntil'], false) }}</div>@endif</td>
                                <td class="finance-amount">{{ Money::format((int)($d['investedAmount'] ?? 0), $cur) }}</td>
                                <td class="finance-amount">{{ Money::format((int)($d['currentValue'] ?? 0), $cur) }}</td>
                                <td class="finance-amount">{{ Money::format((int)($d['unrealizedGains'] ?? 0), $cur) }}</td>
                                <td class="finance-amount">{{ Money::format((int)($d['realizedGains'] ?? 0), $cur) }}</td>
                                <td>{{ $fmt($d['valuedAt'] ?? null, false) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-history"></i>{{ __('Orders') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered table-sm mb-0" id="fundOrders">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Type') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Units') }}</th><th>{{ __('Price') }}</th><th>{{ __('Status') }}</th><th>{{ __('Reference') }}</th></tr></thead>
                        <tbody>
                        @foreach ([['rows' => $subs, 'type' => __('Subscription')], ['rows' => $reds, 'type' => __('Redemption')]] as $block)
                            @foreach ($block['rows'] as $o)
                                @php $d = $o['data']; @endphp
                                <tr>
                                    <td data-order="{{ ($d['createdAt'] ?? null) instanceof \DateTimeInterface ? $d['createdAt']->getTimestamp() : 0 }}">{{ $fmt($d['createdAt'] ?? null) }}</td>
                                    <td>{{ $block['type'] }}@if (!empty($d['demo'])) <span class="badge badge-secondary">demo</span>@endif</td>
                                    <td>{{ $names[$d['userId'] ?? ''] ?? ($d['userId'] ?? '') }}</td>
                                    <td class="finance-amount">{{ isset($d['netAmount']) ? Money::format((int)$d['netAmount'], $cur) : (!empty($d['full']) ? __('everything') : Money::format((int)($d['amount'] ?? 0), $cur)) }}</td>
                                    <td class="finance-amount">{{ isset($d['units']) ? Investments::unitsText($d['units']) : '' }}</td>
                                    <td class="finance-amount">{{ isset($d['navPerUnit']) ? Investments::navText($d['navPerUnit'], $cur) : '' }}</td>
                                    <td>{{ $d['status'] ?? '' }}@if (!empty($d['reason']))<div class="finance-help">{{ $d['reason'] }}</div>@endif</td>
                                    <td class="finance-mono">@if (!empty($d['txId']))<a href="{{ route('finance.ledger.show', $d['txId']) }}">{{ $d['reference'] ?? $d['txId'] }}</a>@else{{ $d['reference'] ?? '' }}@endif</td>
                                </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div></div>
            </fieldset>

        </div></div>
        </div></div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(function () {
        var lang = typeof datatableLang !== 'undefined' ? datatableLang : {};
        $('#fundHolders').DataTable({ pageLength: 25, order: [], language: lang });
        $('#fundOrders').DataTable({ pageLength: 25, order: [[0, 'desc']], language: lang });
    });
</script>
@endsection
