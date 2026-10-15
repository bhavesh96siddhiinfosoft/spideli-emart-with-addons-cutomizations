@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $fmtDate = function ($v) { return $v instanceof \DateTimeInterface ? $v->setTimezone(new DateTimeZone(config('app.timezone')))->format('Y-m-d H:i') : ''; };
    $restr = $u['financeRestriction'] ?? null;
    $name = trim(($u['firstName'] ?? '') . ' ' . ($u['lastName'] ?? ''));
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => $name ?: $uid, 'tab' => 'customers'])
    <div class="container-fluid">
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">

            <fieldset>
                <legend><i class="mr-3 mdi mdi-account"></i>{{ __('Customer') }}</legend>
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Name') }}</label><div class="col-7"><span class="form-control-plaintext">{{ $name ?: '—' }}</span></div></div>
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('E-mail') }}</label><div class="col-7"><span class="form-control-plaintext">{{ $u['email'] ?? '—' }}</span></div></div>
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Phone') }}</label><div class="col-7"><span class="form-control-plaintext finance-amount">{{ $u['phoneNumber'] ?? '—' }}</span></div></div>
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Regions') }}</label><div class="col-7"><span class="form-control-plaintext">{{ implode(', ', array_map(function ($id) use ($regionNames) { return $regionNames[$id] ?? $id; }, (array)($u['regionIds'] ?? []))) ?: '—' }}</span></div></div>
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Verification') }}</label><div class="col-7"><span class="form-control-plaintext">
                    @if (!empty($u['kycLevel']))<span class="finance-badge-ok">{{ $u['kycLevel'] }}</span>@else{{ $u['kycStatus'] ?? __('not verified') }}@endif</span></div></div>
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Id') }}</label><div class="col-7"><span class="form-control-plaintext finance-mono">{{ $uid }}</span></div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-block-helper"></i>{{ __('Restriction') }}</legend>
                @if ($restr)
                    <div class="form-group row width-100"><div class="col-7">
                        <div class="alert alert-danger mb-2">
                            <strong>{{ __('Restricted') }}:</strong> {{ implode(', ', (array)($restr['scopes'] ?? ['all'])) }}
                            · {{ $restr['reason'] ?? '' }} · {{ $fmtDate($restr['since'] ?? null) }} · <span class="finance-mono">{{ $restr['by'] ?? '' }}</span>
                        </div>
                        @if ($canRestrict)
                            <form method="POST" action="{{ route('finance.customers.lift', $uid) }}" onsubmit="return confirm('{{ __('Lift the restriction?') }}')">
                                @csrf
                                <input type="text" name="reason" class="form-control mb-2" placeholder="{{ __('Why it is lifted') }}" required>
                                <button class="btn btn-primary">{{ __('Lift restriction') }}</button>
                            </form>
                        @endif
                    </div></div>
                @elseif ($canRestrict)
                    <form method="POST" action="{{ route('finance.customers.restrict', $uid) }}" onsubmit="return confirm('{{ __('Restrict this customer?') }}')">
                        @csrf
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ __('Blocks') }}<span class="required-field"></span></label>
                            <div class="col-7">
                                <select name="scopes[]" multiple class="form-control finance-select2">
                                    @foreach ($scopes as $s)<option value="{{ $s }}" {{ $s === 'all' ? 'selected' : '' }}>{{ $s }}</option>@endforeach
                                </select>
                                <div class="form-text text-muted">{{ __('"all" blocks every new Finance action. Receiving money, contributions and repayments are never blocked.') }}</div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{ __('Reason') }}<span class="required-field"></span></label>
                            <div class="col-7"><input type="text" name="reason" class="form-control" required>
                                <div class="form-text text-muted">{{ __('After a documented review, never automatically for one failed payment.') }}</div></div>
                        </div>
                        <div class="form-group row width-100"><div class="col-7"><button class="btn btn-danger">{{ __('Restrict') }}</button></div></div>
                    </form>
                @else
                    <div class="form-group row width-100"><div class="col-7 finance-help">{{ __('Not restricted.') }}</div></div>
                @endif
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-wallet"></i>{{ __('Wallets') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered mb-0">
                        <thead><tr><th>{{ __('Account') }}</th><th>{{ __('Balance') }}</th><th>{{ __('Status') }}</th></tr></thead>
                        <tbody>
                        @forelse ($wallets as $w)
                            <tr><td class="finance-mono">{{ $w['id'] }}</td><td class="finance-amount">{{ Money::format((int)($w['data']['balance'] ?? 0), $w['data']['currency'] ?? '') }}</td><td>{{ $w['data']['status'] ?? '' }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">{{ __('No wallet yet.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-account-check"></i>{{ __('Identity verification') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Level') }}</th><th>{{ __('Revision') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                        <tbody>
                        @forelse ($kyc as $k)
                            <tr><td>{{ $fmtDate($k['data']['submittedAt'] ?? $k['data']['createdAt'] ?? null) }}</td><td>{{ $k['data']['level'] ?? '' }}</td><td>{{ $k['data']['revision'] ?? '' }}</td><td>{{ $k['data']['status'] ?? '' }}</td>
                                <td><a href="{{ route('finance.kyc.show', $k['id']) }}">{{ __('Open') }}</a></td></tr>
                        @empty
                            <tr><td colspan="5" class="text-muted">{{ __('No application.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div></div>
            </fieldset>

            @foreach ([
                ['title' => __('Ledger (latest 50)'), 'icon' => 'mdi-book-open', 'rows' => $ledger, 'cols' => 'ledger'],
                ['title' => __('Add money, withdrawals, conversions'), 'icon' => 'mdi-cash', 'rows' => $operations, 'cols' => 'ops'],
                ['title' => __('Transfers'), 'icon' => 'mdi-bank-transfer', 'rows' => $transfers, 'cols' => 'transfers'],
                ['title' => __('Bill payments'), 'icon' => 'mdi-receipt', 'rows' => $bills, 'cols' => 'bills'],
            ] as $block)
                <fieldset>
                    <legend><i class="mr-3 mdi {{ $block['icon'] }}"></i>{{ $block['title'] }}</legend>
                    <div class="form-group row width-100"><div class="col-7">
                        <table class="table table-bordered table-sm mb-0">
                            <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Reference') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th></tr></thead>
                            <tbody>
                            @forelse ($block['rows'] as $r)
                                @php
                                    $d = $r['data'];
                                    $amount = $block['cols'] === 'ledger' ? (int)($d['lines'][0]['amount'] ?? 0) : (int)($d['total'] ?? $d['amount'] ?? 0);
                                    $type = $block['cols'] === 'transfers' ? ($d['channel'] ?? '') . (($d['senderId'] ?? '') === $uid ? ' ↑' : ' ↓')
                                          : ($block['cols'] === 'bills' ? ($d['billerName'] ?? '') : ($d['type'] ?? ''));
                                @endphp
                                <tr>
                                    <td>{{ $fmtDate($d['createdAt'] ?? null) }}</td>
                                    <td class="finance-mono">
                                        @if ($block['cols'] === 'ledger')<a href="{{ route('finance.ledger.show', $r['id']) }}">{{ $d['reference'] ?? $r['id'] }}</a>@else{{ $d['reference'] ?? $r['id'] }}@endif
                                    </td>
                                    <td>{{ $type }}</td>
                                    <td class="finance-amount">{{ Money::format($amount, $d['currency'] ?? '') }}</td>
                                    <td>{{ $d['status'] ?? '' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">{{ __('Nothing yet.') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div></div>
                </fieldset>
            @endforeach

        </div></div>
        </div></div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(function () { $('.finance-select2').select2({ width: '100%' }); });
</script>
@endsection
