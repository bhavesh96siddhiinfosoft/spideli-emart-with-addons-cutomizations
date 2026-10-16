@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    use App\Finance\Investments;
    $f = $fund ?? [];
    $cur = $f['currency'] ?? old('currency', $currencies[0] ?? '');
    $v = function ($key, $default = '') use ($f) { return old($key, $f[$key] ?? $default); };
    $money = function ($key) use ($f, $cur) { return old($key, isset($f[$key]) ? Money::toMajor($f[$key], $cur) : ''); };
    $assetLabels = ['money_market' => __('Money market'), 'bonds' => __('Bonds'), 'equities' => __('Equities'), 'real_estate' => __('Real estate'),
        'agriculture' => __('Agriculture'), 'infrastructure' => __('Infrastructure'), 'mixed' => __('Mixed')];
    $statusLabels = ['open' => __('Open: customers can subscribe'), 'closed' => __('Closed: no new subscriptions'), 'suspended' => __('Suspended: no subscriptions, no redemptions')];
    $check = function ($key, $stored) { return old('_sent') ? (bool)old($key) : (bool)$stored; };
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => $id ? ($f['name'] ?? __('Fund')) : __('New fund'), 'tab' => 'investments'])
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                @if (!$id && !$currencies)
                    <div class="alert alert-warning">{{ __('No region is enabled for Finance yet. Tick "Finance" on a region in Region Management.') }}</div>
                @else
                <form method="POST" enctype="multipart/form-data" action="{{ $id ? route('finance.investments.update', $id) : route('finance.investments.store') }}">
                    @csrf
                    <input type="hidden" name="_sent" value="1">
                    <div class="row vendor_payout_create">
                        <div class="vendor_payout_create-inner">

                            <fieldset>
                                <legend><i class="mr-3 mdi mdi-chart-line"></i>{{ __('Fund') }}</legend>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="fund-name">{{ __('Name') }}<span class="required-field"></span></label>
                                    <div class="col-7"><input type="text" id="fund-name" name="name" class="form-control" maxlength="120" value="{{ $v('name') }}" required></div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="fund-manager">{{ __('Fund manager') }}</label>
                                    <div class="col-7"><input type="text" id="fund-manager" name="manager" class="form-control" value="{{ $v('manager') }}"></div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="fund-asset">{{ __('Asset class') }}<span class="required-field"></span></label>
                                    <div class="col-7">
                                        <select id="fund-asset" name="assetClass" class="form-control finance-select2" required>
                                            @foreach ($assetLabels as $k => $label)
                                                <option value="{{ $k }}" {{ $v('assetClass') === $k ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="fund-risk">{{ __('Risk level') }}<span class="required-field"></span></label>
                                    <div class="col-7">
                                        <select id="fund-risk" name="riskLevel" class="form-control finance-select2" required>
                                            @for ($i = 1; $i <= 7; $i++)
                                                <option value="{{ $i }}" {{ (int)$v('riskLevel', 3) === $i ? 'selected' : '' }}>{{ $i }} / 7{{ $i === 1 ? ' - ' . __('lowest') : ($i === 7 ? ' - ' . __('highest') : '') }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="fund-currency">{{ __('Currency') }}<span class="required-field"></span></label>
                                    <div class="col-7">
                                        @if ($id)
                                            <input type="text" class="form-control" value="{{ $cur }}" disabled>
                                            <div class="form-text text-muted">{{ __('A fund\'s currency cannot change once it exists.') }}</div>
                                        @else
                                            <select id="fund-currency" name="currency" class="form-control finance-select2" required>
                                                @foreach ($currencies as $c)
                                                    <option value="{{ $c }}" data-symbol="{{ $symbols[$c] ?? $c }}" {{ $cur === $c ? 'selected' : '' }}>{{ $c }}</option>
                                                @endforeach
                                            </select>
                                            <div class="form-text text-muted">{{ __('The investment currencies of the finance regions (Finance settings > Investments).') }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="fund-order">{{ __('Display order') }}</label>
                                    <div class="col-7"><input type="number" id="fund-order" name="order" class="form-control" value="{{ $v('order', 0) }}"></div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="fund-status">{{ __('Status') }}<span class="required-field"></span></label>
                                    <div class="col-7">
                                        <select id="fund-status" name="status" class="form-control finance-select2">
                                            @foreach ($statusLabels as $k => $label)
                                                <option value="{{ $k }}" {{ $v('status', 'open') === $k ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ __('Published') }}</label>
                                    <div class="col-7">
                                        <div class="form-check">
                                            <input type="checkbox" id="fund-published" name="published" value="1" {{ $check('published', $f['published'] ?? false) ? 'checked' : '' }}>
                                            <label class="control-label" for="fund-published">{{ __('Shown to customers in the app') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-3 control-label" for="fund-description">{{ __('Description') }}</label>
                                    <div class="col-7"><textarea id="fund-description" name="description" class="form-control" rows="3">{{ $v('description') }}</textarea></div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-3 control-label" for="fund-objective">{{ __('Objective') }}</label>
                                    <div class="col-7"><textarea id="fund-objective" name="objective" class="form-control" rows="2">{{ $v('objective') }}</textarea></div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-3 control-label" for="fund-eligibility">{{ __('Who can invest') }}</label>
                                    <div class="col-7"><textarea id="fund-eligibility" name="eligibility" class="form-control" rows="2">{{ $v('eligibility') }}</textarea></div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend><i class="mr-3 mdi mdi-cash"></i>{{ __('Amounts and unit price') }}</legend>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="fund-min">{{ __('Minimum investment') }}</label>
                                    <div class="col-7"><div class="finance-amount-box"><span class="finance-symbol fund-symbol">{{ $symbols[$cur] ?? $cur }}</span>
                                        <input type="text" inputmode="decimal" id="fund-min" name="minInvestment" class="form-control" value="{{ $money('minInvestment') }}"></div>
                                        <div class="form-text text-muted">{{ __('The first subscription must be at least this.') }}</div></div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="fund-min-add">{{ __('Minimum top-up') }}</label>
                                    <div class="col-7"><div class="finance-amount-box"><span class="finance-symbol fund-symbol">{{ $symbols[$cur] ?? $cur }}</span>
                                        <input type="text" inputmode="decimal" id="fund-min-add" name="minAdditional" class="form-control" value="{{ $money('minAdditional') }}"></div>
                                        <div class="form-text text-muted">{{ __('For a customer who already holds units. Blank = the minimum investment.') }}</div></div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="fund-nav">{{ __('Unit price') }}@if (!$id)<span class="required-field"></span>@endif</label>
                                    <div class="col-7">
                                        @if ($id)
                                            <input type="text" class="form-control" value="{{ isset($f['navPerUnit']) ? Investments::navText($f['navPerUnit'], $cur) : '' }}" disabled>
                                            <div class="form-text text-muted">{{ __('Add a new unit price on the fund page: it revalues every holding.') }}</div>
                                        @else
                                            <div class="finance-amount-box"><span class="finance-symbol fund-symbol">{{ $symbols[$cur] ?? $cur }}</span>
                                                <input type="text" inputmode="decimal" id="fund-nav" name="navPerUnit" class="form-control" value="{{ old('navPerUnit') }}" required></div>
                                            <div class="form-text text-muted">{{ __('The price of one unit today, up to 4 more decimals than the currency. It is the fund\'s first price in its history.') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend><i class="mr-3 mdi mdi-percent"></i>{{ __('Fees') }}</legend>
                                @foreach (['subscriptionPercent' => [__('Subscription fee'), __('Taken from each subscription before units are bought.')],
                                           'redemptionPercent' => [__('Redemption fee'), __('Taken from each redemption before it is paid.')],
                                           'managementPercent' => [__('Management fee'), __('Yearly, shown to customers; already in the unit price.')],
                                           'performancePercent' => [__('Performance fee'), __('Shown to customers; already in the unit price.')]] as $k => [$label, $help])
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label" for="fund-{{ $k }}">{{ $label }}</label>
                                        <div class="col-7"><div class="finance-amount-box"><span class="finance-symbol">%</span>
                                            <input type="text" inputmode="decimal" id="fund-{{ $k }}" name="{{ $k }}" class="form-control" value="{{ old($k, $f['fees'][$k] ?? '') }}"></div>
                                            <div class="form-text text-muted">{{ $help }}</div></div>
                                    </div>
                                @endforeach
                            </fieldset>

                            <fieldset>
                                <legend><i class="mr-3 mdi mdi-lock-clock"></i>{{ __('Lock-up and redemption') }}</legend>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="fund-lock">{{ __('Lock-up (days)') }}</label>
                                    <div class="col-7"><input type="number" min="0" id="fund-lock" name="lockUpDays" class="form-control" value="{{ $v('lockUpDays', 0) }}">
                                        <div class="form-text text-muted">{{ __('New units cannot be redeemed until this many days after the trade date.') }}</div></div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="fund-settle">{{ __('Settlement (days)') }}</label>
                                    <div class="col-7"><input type="number" min="0" id="fund-settle" name="settlementDays" class="form-control" value="{{ $v('settlementDays', 0) }}">
                                        <div class="form-text text-muted">{{ __('Days between executing a redemption and paying it; shown to the customer.') }}</div></div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ __('Redemption') }}</label>
                                    <div class="col-7">
                                        <div class="form-check">
                                            <input type="checkbox" id="fund-red-allowed" name="redemptionAllowed" value="1" {{ $check('redemptionAllowed', $f['redemption']['allowed'] ?? true) ? 'checked' : '' }}>
                                            <label class="control-label" for="fund-red-allowed">{{ __('Customers can redeem') }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="fund-red-partial" name="redemptionPartial" value="1" {{ $check('redemptionPartial', $f['redemption']['partial'] ?? true) ? 'checked' : '' }}>
                                            <label class="control-label" for="fund-red-partial">{{ __('Part of the holding (otherwise everything only)') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend><i class="mr-3 mdi mdi-trending-up"></i>{{ __('Performance (%)') }}</legend>
                                <div class="form-group row width-100 mb-3"><div class="form-text text-muted">{{ __('Recomputed from the price history each time a unit price is added. Type only the figures the history does not cover (a fund older than its history here).') }}</div></div>
                                @foreach (['ytd' => __('This year'), 'oneYear' => __('1 year'), 'threeYears' => __('3 years'), 'fiveYears' => __('5 years'), 'sinceInception' => __('Since launch')] as $k => $label)
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label" for="perf-{{ $k }}">{{ $label }}</label>
                                        <div class="col-7"><div class="finance-amount-box"><span class="finance-symbol">%</span>
                                            <input type="text" inputmode="decimal" id="perf-{{ $k }}" name="performance[{{ $k }}]" class="form-control" value="{{ old('performance.' . $k, $f['performance'][$k] ?? '') }}"></div></div>
                                    </div>
                                @endforeach
                            </fieldset>

                            <fieldset>
                                <legend><i class="mr-3 mdi mdi-file-sign"></i>{{ __('Terms') }}</legend>
                                <div class="form-group row width-100">
                                    <label class="col-3 control-label" for="fund-terms">{{ __('Terms') }}</label>
                                    <div class="col-7"><textarea id="fund-terms" name="terms" class="form-control" rows="8">{{ $v('terms') }}</textarea>
                                        <div class="form-text text-muted">
                                            @if ($id){{ __('Version') }} {{ (int)($f['termsVersion'] ?? 1) }}. {{ __('Changing the text makes a new version: holders are notified and customers accept the new version before their next subscription.') }}
                                            @else{{ __('Customers sign these terms when they subscribe. Required before publishing.') }}@endif
                                        </div></div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend><i class="mr-3 mdi mdi-file-pdf-box"></i>{{ __('Fund documents') }}</legend>
                                <div class="form-group row width-100"><div class="col-7">
                                    @if (!empty($f['documents']))
                                        <table class="table table-bordered table-sm mb-3">
                                            <thead><tr><th>{{ __('Document') }}</th><th style="width:160px">{{ __('Remove') }}</th></tr></thead>
                                            <tbody>
                                            @foreach ((array)$f['documents'] as $i => $d)
                                                <tr><td><a href="{{ route('finance.investments.document', [$id, $i]) }}" target="_blank" rel="noopener">{{ $d['name'] ?? __('Document') }}</a></td>
                                                    <td><div class="form-check"><input type="checkbox" id="doc-rm-{{ $i }}" name="removeDocuments[]" value="{{ $i }}"><label class="control-label" for="doc-rm-{{ $i }}">{{ __('Remove') }}</label></div></td></tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                    <table class="table table-bordered table-sm mb-2" id="fund-new-docs">
                                        <thead><tr><th>{{ __('Title') }}</th><th>{{ __('PDF file') }}</th></tr></thead>
                                        <tbody>
                                            <tr><td><input type="text" name="documentTitles[]" class="form-control" placeholder="{{ __('e.g. Fund prospectus') }}"></td>
                                                <td><input type="file" name="documents[]" accept="application/pdf,.pdf" class="form-control"></td></tr>
                                        </tbody>
                                    </table>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="fund-add-doc"><i class="mdi mdi-plus"></i> {{ __('Another document') }}</button>
                                    <div class="form-text text-muted">{{ __('PDF only, at most 20 MB each. Stored privately; signed-in customers read them in the app.') }}</div>
                                </div></div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Save') }}</button>
                        <a href="{{ $id ? route('finance.investments.show', $id) : route('finance.investments') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        $('.finance-select2').select2({ width: '100%' });
        $('#fund-currency').on('change', function () {
            $('.fund-symbol').text($(this).find(':selected').data('symbol') || $(this).val());
        });
        $('#fund-add-doc').on('click', function () {
            var $row = $('#fund-new-docs tbody tr:first').clone();
            $row.find('input').val('');
            $('#fund-new-docs tbody').append($row);
        });
    });
</script>
@endsection
