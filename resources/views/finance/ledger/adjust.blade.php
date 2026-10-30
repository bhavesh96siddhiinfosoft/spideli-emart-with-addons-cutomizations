@extends('layouts.app')
@section('content')
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Manual adjustment'), 'tab' => 'ledger'])
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                @if (!$regions)
                    <div class="alert alert-warning">{{ __('No region is enabled for Finance yet. Tick "Finance" on a region in Region Management.') }}</div>
                @else
                <form method="POST" action="{{ route('finance.adjust.store') }}" onsubmit="return confirm('{{ __('Post this adjustment?') }}')">
                    @csrf
                    {{-- Opened from a customer's page (point 93): go back there afterwards. --}}
                    @if (old('from', request('from')) === 'customer')
                        <input type="hidden" name="from" value="customer">
                    @endif
                    <div class="row vendor_payout_create">
                        <div class="vendor_payout_create-inner">
                            <fieldset>
                                <legend><i class="mr-3 mdi mdi-scale-balance"></i>{{ __('Manual adjustment') }}</legend>
                                <div class="form-group row width-100 mb-3">
                                    <div class="form-text text-muted">{{ __('Moves money between a customer\'s wallet and Spideli\'s platform account, as a posting of type manual_adjustment. A wallet never goes below 0.') }}</div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="adj-region">{{ __('Region') }}<span class="required-field"></span></label>
                                    <div class="col-7">
                                        <select name="region" id="adj-region" class="form-control finance-select2" required>
                                            @foreach ($regions as $id => $r)
                                                <option value="{{ $id }}" data-currency="{{ $currencySymbols[$r['currency']] ?? $r['currency'] }}" {{ old('region', request('region', $_COOKIE['region_id'] ?? '')) === $id ? 'selected' : '' }}>{{ $r['name'] }} ({{ $r['currency'] }})</option>
                                            @endforeach
                                        </select>
                                        <div class="form-text text-muted">{{ __('The amount is in the region\'s currency, and the customer must belong to the region.') }}</div>
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="adj-user">{{ __('Customer') }}<span class="required-field"></span></label>
                                    <div class="col-7">
                                        <input type="text" id="adj-user" name="user" class="form-control" value="{{ old('user', request('user')) }}" required placeholder="{{ __('id, e-mail or phone') }}">
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ __('Direction') }}<span class="required-field"></span></label>
                                    <div class="col-7">
                                        <div class="form-check">
                                            <input type="radio" id="adj-credit" name="direction" value="credit" {{ old('direction', request('direction', 'credit')) === 'credit' ? 'checked' : '' }}>
                                            <label class="control-label" for="adj-credit">{{ __('Into the wallet (platform → wallet)') }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" id="adj-debit" name="direction" value="debit" {{ old('direction', request('direction')) === 'debit' ? 'checked' : '' }}>
                                            <label class="control-label" for="adj-debit">{{ __('Out of the wallet (wallet → platform)') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label" for="adj-amount">{{ __('Amount') }}<span class="required-field"></span></label>
                                    <div class="col-7">
                                        <div class="finance-amount-box">
                                            <span class="finance-symbol" id="adj-currency"></span>
                                            <input type="text" inputmode="decimal" id="adj-amount" name="amount" class="form-control" value="{{ old('amount') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row width-100">
                                    <label class="col-3 control-label" for="adj-reason">{{ __('Reason') }}<span class="required-field"></span></label>
                                    <div class="col-7">
                                        <textarea id="adj-reason" name="reason" class="form-control" rows="3" required>{{ old('reason') }}</textarea>
                                        <div class="form-text text-muted">{{ __('At or above the region\'s second-approver threshold, the adjustment waits in Approvals for another admin.') }}</div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Post adjustment') }}</button>
                        <a href="{{ (old('from', request('from')) === 'customer' && old('user', request('user'))) ? route('finance.customers.show', old('user', request('user'))) : route('finance.ledger') }}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
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
        var $r = $('#adj-region');
        $r.select2({ width: '100%' });
        function showCurrency() {
            var sym = String($r.find(':selected').data('currency') || '');
            $('#adj-currency').text(sym);
            $('#adj-amount').css('padding-left', 'calc(20px + ' + sym.length + 'ch)');
        }
        $r.on('change', showCurrency);
        showCurrency();
    });
</script>
@endsection
