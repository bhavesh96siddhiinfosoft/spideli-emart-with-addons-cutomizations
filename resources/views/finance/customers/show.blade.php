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
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Sign-in') }}</label><div class="col-7"><span class="form-control-plaintext">
                    @if (is_array($auth))
                        @if ($auth['disabled'])<span class="finance-badge-no">{{ __('blocked') }}</span>@else<span class="finance-badge-ok">{{ __('allowed') }}</span>@endif
                        <span class="finance-help">{{ implode(', ', $auth['providers']) }}@if ($auth['lastSignIn']) · {{ __('last sign-in') }} {{ $fmtDate($auth['lastSignIn']) }}@endif</span>
                    @elseif ($auth === 'missing'){{ __('No sign-in account') }}
                    @else<span class="finance-help">{{ __('Could not be read from Firebase.') }}</span>@endif
                </span></div></div>
                <div class="form-group row width-50"><label class="col-3 control-label">{{ __('Two-step verification') }}</label><div class="col-7"><span class="form-control-plaintext">
                    @if (is_array($auth))
                        @if ($auth['twoStep'])
                            <span class="finance-badge-ok">{{ __('on') }}</span>
                            @foreach ($auth['twoStep'] as $f)<span class="finance-help">{{ $f['kind'] === 'sms' ? __('SMS to') : __('Authenticator') }} {{ $f['hint'] }}@if ($f['enrolledAt']) · {{ __('since') }} {{ $fmtDate($f['enrolledAt']) }}@endif</span>@endforeach
                        @else{{ __('off') }}@endif
                    @else—@endif
                </span>
                @if (is_array($auth) && $auth['twoStep'] && $canResetTwoStep)
                    <form method="POST" action="{{ route('finance.customers.reset-two-step', $uid) }}" class="mt-1"
                          onsubmit="return confirm('{{ __('Remove this customer\'s two-step verification? They will sign in without the code until they turn it on again.') }}')">@csrf
                        <div class="d-flex"><input type="text" name="reason" class="form-control form-control-sm mr-2" required
                               placeholder="{{ __('How was their identity checked? (kept in the audit log)') }}">
                            <button class="btn btn-sm btn-outline-danger text-nowrap">{{ __('Reset two-step') }}</button></div>
                        <div class="finance-help">{{ __('Only for a customer who lost their phone, after checking who is asking (for example a video call with their ID).') }}</div>
                    </form>
                @endif
                </div></div>
            </fieldset>

            @if ($failed)
                <div class="alert alert-warning">{{ __('Could not be read from Firebase:') }} {{ implode(', ', $failed) }}. {{ __('Reload the page to try again.') }}</div>
            @endif

            @if ($tickets !== null)
            <fieldset>
                <legend><i class="mr-3 mdi mdi-lifebuoy"></i>{{ __('Support tickets') }}
                    @if ($canTicket)<a class="btn btn-sm btn-outline-primary float-right" href="{{ route('finance.tickets.create', ['userId' => $uid]) }}"><i class="mdi mdi-plus"></i> {{ __('Open a ticket') }}</a>@endif</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>{{ __('Opened') }}</th><th>{{ __('Subject') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Status') }}</th></tr></thead>
                        <tbody>
                        @forelse ($tickets as $tk)
                            <tr><td>{{ $fmtDate($tk['data']['createdAt'] ?? null) }}</td><td><a href="{{ route('finance.tickets.show', $tk['id']) }}">{{ $tk['data']['subject'] ?? '' }}</a></td>
                                <td>{{ $tk['data']['priority'] ?? '' }}</td><td>{{ $tk['data']['status'] ?? '' }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">{{ __('No ticket.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div></div>
            </fieldset>
            @endif

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

            @php
                $m = function ($v, $cur) { return Money::format((int)($v ?? 0), (string)($cur ?? '')); };
                $link = function ($route, $id, $text) { return ['text' => $text, 'href' => route($route, $id)]; };
                $sections = [];
                if ($goals !== null) {
                    $sections[] = ['title' => __('Savings goals'), 'icon' => 'mdi-piggy-bank', 'head' => [__('Goal'), __('Role'), __('Saved'), __('Target'), __('Status')],
                        'rows' => array_map(function ($r) use ($uid, $m, $link) { $d = $r['data']; return [
                            $link('finance.savings.show', $r['id'], $d['name'] ?? $r['id']),
                            ($d['ownerId'] ?? '') === $uid ? __('owner') : __('member'),
                            ['text' => $m($d['savedAmount'] ?? 0, $d['currency'] ?? ''), 'amount' => true],
                            ['text' => $m($d['targetAmount'] ?? 0, $d['currency'] ?? ''), 'amount' => true],
                            $d['status'] ?? '']; }, $goals)];
                }
                if ($tontines !== null) {
                    $sections[] = ['title' => __('Tontines'), 'icon' => 'mdi-account-group', 'head' => [__('Tontine'), __('Role'), __('Contribution'), __('Cycle'), __('Status')],
                        'rows' => array_map(function ($r) use ($uid, $m, $link) { $d = $r['data']; return [
                            $link('finance.tontines.show', $r['id'], $d['name'] ?? $r['id']),
                            in_array($uid, (array)($d['adminIds'] ?? []), true) ? __('admin') : __('member'),
                            ['text' => $m($d['contributionAmount'] ?? 0, $d['currency'] ?? '') . ' / ' . ($d['frequency'] ?? ''), 'amount' => true],
                            (string)($d['currentCycle'] ?? 0),
                            $d['status'] ?? '']; }, $tontines)];
                }
                if ($loans !== null) {
                    $sections[] = ['title' => __('Loans'), 'icon' => 'mdi-hand-coin', 'head' => [__('Date'), __('Role'), __('Group'), __('Amount'), __('Still to repay'), __('Status')],
                        'rows' => array_map(function ($r) use ($m, $link, $fmtDate) { $d = $r['data']; return [
                            $link('finance.loans.show', $r['id'], $fmtDate($d['createdAt'] ?? null) ?: $r['id']),
                            $r['role'] === 'borrower' ? __('borrower') : __('lender'),
                            $d['groupName'] ?? '',
                            ['text' => $m($d['amount'] ?? 0, $d['currency'] ?? ''), 'amount' => true],
                            ['text' => $m($d['outstandingAmount'] ?? 0, $d['currency'] ?? ''), 'amount' => true],
                            $d['status'] ?? '']; }, $loans)];
                }
                if ($holdings !== null) {
                    $sections[] = ['title' => __('Investments'), 'icon' => 'mdi-chart-line', 'head' => [__('Fund'), __('Units'), __('Invested'), __('Current value'), __('Gains')],
                        'rows' => array_map(function ($r) use ($m, $link) { $d = $r['data']; return [
                            $link('finance.investments.show', $d['fundId'] ?? '', $d['fundName'] ?? ($d['fundId'] ?? '')),
                            ['text' => rtrim(rtrim(number_format((float)($d['units'] ?? 0), 4, '.', ' '), '0'), '.'), 'amount' => true],
                            ['text' => $m($d['investedAmount'] ?? 0, $d['currency'] ?? ''), 'amount' => true],
                            ['text' => $m($d['currentValue'] ?? 0, $d['currency'] ?? ''), 'amount' => true],
                            ['text' => $m($d['unrealizedGains'] ?? 0, $d['currency'] ?? ''), 'amount' => true]]; }, $holdings)];
                }
                if ($policies !== null) {
                    $sections[] = ['title' => __('Insurance policies'), 'icon' => 'mdi-shield-check', 'head' => [__('Product'), __('Policy number'), __('Premium (a year)'), __('Cover ends'), __('Status')],
                        'rows' => array_map(function ($r) use ($m, $link, $fmtDate) { $d = $r['data']; return [
                            $link('finance.insurance.policies.show', $r['id'], $d['productName'] ?? $r['id']),
                            $d['policyNumber'] ?? '—',
                            ['text' => $m($d['premium'] ?? 0, $d['currency'] ?? ''), 'amount' => true],
                            $fmtDate($d['coverEnd'] ?? null),
                            $d['status'] ?? '']; }, $policies)];
                }
                if ($claims !== null) {
                    $sections[] = ['title' => __('Insurance claims'), 'icon' => 'mdi-file-document-alert', 'head' => [__('Date'), __('Product'), __('Claimed'), __('Paid'), __('Status')],
                        'rows' => array_map(function ($r) use ($m, $link, $fmtDate) { $d = $r['data']; return [
                            $link('finance.insurance.claims.show', $r['id'], $fmtDate($d['createdAt'] ?? null) ?: $r['id']),
                            $d['productName'] ?? '',
                            ['text' => $m($d['amountClaimed'] ?? 0, $d['currency'] ?? ''), 'amount' => true],
                            ['text' => isset($d['paidAmount']) ? $m($d['paidAmount'], $d['currency'] ?? '') : '—', 'amount' => true],
                            $d['status'] ?? '']; }, $claims)];
                }
                if ($financing !== null) {
                    $sections[] = ['title' => __('Project financing'), 'icon' => 'mdi-briefcase', 'head' => [__('Project'), __('Type'), __('Amount'), __('Date'), __('Status')],
                        'rows' => array_map(function ($r) use ($m, $link, $fmtDate) { $d = $r['data']; return [
                            $link('finance.financing.show', $r['id'], $d['projectName'] ?? $r['id']),
                            $d['financingType'] ?? '',
                            ['text' => $m($d['amount'] ?? 0, $d['currency'] ?? ''), 'amount' => true],
                            $fmtDate($d['submittedAt'] ?? $d['createdAt'] ?? null),
                            $d['status'] ?? '']; }, $financing)];
                }
                $sections[] = ['title' => __('Virtual cards'), 'icon' => 'mdi-credit-card', 'head' => [__('Card'), __('Number'), __('Balance'), __('Status')],
                    'rows' => array_map(function ($r) use ($m) { $d = $r['data']; return [
                        $d['label'] ?? $r['id'],
                        !empty($d['last4']) ? '•••• ' . $d['last4'] : '—',
                        ['text' => $m($d['balance'] ?? 0, $d['currency'] ?? ''), 'amount' => true],
                        ($d['status'] ?? '') . (!empty($d['frozenBy']) && ($d['status'] ?? '') === 'frozen' ? ' (' . $d['frozenBy'] . ')' : '')]; }, $cards)];
                if ($audit !== null) {
                    $sections[] = ['title' => __('Audit trail (latest 50)'), 'icon' => 'mdi-history', 'head' => [__('When'), __('Admin'), __('Action'), __('Record'), __('Reason')],
                        'rows' => array_map(function ($r) use ($fmtDate) { $d = $r['data']; return [
                            $fmtDate($d['at'] ?? null),
                            $d['adminEmail'] ?? '',
                            ['text' => $d['action'] ?? '', 'mono' => true],
                            ['text' => ($d['collection'] ?? '') . '/' . ($d['docId'] ?? ''), 'mono' => true],
                            $d['reason'] ?? '']; }, $audit)];
                }
            @endphp
            @foreach ($sections as $s)
                <fieldset>
                    <legend><i class="mr-3 mdi {{ $s['icon'] }}"></i>{{ $s['title'] }}</legend>
                    <div class="form-group row width-100"><div class="col-7">
                        <table class="table table-bordered table-sm mb-0">
                            <thead><tr>@foreach ($s['head'] as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
                            <tbody>
                            @forelse ($s['rows'] as $row)
                                <tr>@foreach ($row as $c)
                                    @php $c = is_array($c) ? $c : ['text' => (string)$c]; @endphp
                                    <td class="{{ !empty($c['amount']) ? 'finance-amount' : '' }} {{ !empty($c['mono']) ? 'finance-mono' : '' }}">@if (!empty($c['href']))<a href="{{ $c['href'] }}">{{ $c['text'] }}</a>@else{{ $c['text'] }}@endif</td>
                                @endforeach</tr>
                            @empty
                                <tr><td colspan="{{ count($s['head']) }}" class="text-muted">{{ __('Nothing yet.') }}</td></tr>
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
