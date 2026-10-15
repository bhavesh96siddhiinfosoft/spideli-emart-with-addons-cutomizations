@extends('layouts.app')
@section('content')
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Finance settings'), 'tab' => 'settings'])
    <div class="container-fluid">

        @if (!$regions)
            <div class="alert alert-warning">
                {{ __('No region is enabled for Finance yet. Tick "Finance" on a region in Region Management.') }}
                <a href="{{ route('region') }}">{{ __('Region Management') }}</a>
            </div>
        @endif

        <div class="card border mb-4">
            <div class="card-header border-0">
                <h3 class="text-dark-2 mb-1 h4">{{ __('Modules by region') }}</h3>
                <p class="mb-0 text-dark-2">{{ __('Finance settings are kept per region. A module whose settings are missing or incomplete in a region shows "not available yet" to that region\'s customers.') }}</p>
            </div>
            <div class="card-body">
                @if ($regions)
                <div class="table-responsive">
                    <table class="table table-bordered finance-matrix">
                        <thead>
                            <tr>
                                <th>{{ __('Module') }}</th>
                                @foreach ($regions as $id => $r)
                                    <th>{{ $r['name'] }}<div class="finance-help finance-mono">{{ $r['currency'] ?: __('no currency') }}</div></th>
                                @endforeach
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($modules as $doc => $title)
                            <tr>
                                <td>{{ __($title) }}</td>
                                @foreach ($regions as $id => $r)
                                    @php $m = $matrix[$id][$doc]; @endphp
                                    <td>
                                        <a href="{{ route('finance.settings.edit', ['doc' => $doc, 'regions' => [$id]]) }}" class="finance-cell-link">
                                            @if (!$m['exists'])
                                                <span class="finance-badge-no">{{ __('Not set') }}</span>
                                            @elseif (!$m['missing'])
                                                <span class="finance-badge-ok">{{ __('Offered') }}</span>
                                            @else
                                                <span class="finance-badge-no">{{ __('Not available') }}</span>
                                            @endif
                                        </a>
                                        @if ($m['exists'] && $m['missing'])
                                            <div class="finance-help">{{ __('Missing:') }} {{ implode(', ', array_map('__', $m['missing'])) }}</div>
                                        @endif
                                        @foreach ($m['warnings'] as $w)
                                            <div class="finance-help text-warning">{{ __($w) }}</div>
                                        @endforeach
                                    </td>
                                @endforeach
                                <td><a class="btn btn-sm btn-primary" href="{{ route('finance.settings.edit', ['doc' => $doc, 'regions' => array_keys($regions)]) }}">{{ $canEdit ? __('Edit') : __('View') }}</a></td>
                            </tr>
                        @endforeach
                        @if ($canEdit)
                            <tr>
                                <td class="finance-help">{{ __('Start a region from the app\'s current settings (only modules it does not have yet)') }}</td>
                                @foreach ($regions as $id => $r)
                                    <td>
                                        @if (collect($matrix[$id])->contains(function ($m) { return !$m['exists']; }))
                                            <form method="POST" action="{{ route('finance.regions.seed', $id) }}" onsubmit="return confirm('{{ __('Copy the app\'s current settings into the modules this region does not have yet?') }}')">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-primary">{{ __('Copy app settings') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                @endforeach
                                <td></td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card border mb-4">
                    <div class="card-header border-0">
                        <h3 class="text-dark-2 mb-1 h4">{{ __('Platform accounts') }}</h3>
                        <p class="mb-0 text-dark-2">{{ __('Spideli\'s clearing account, one per currency of the regions enabled for Finance (regions with the same currency share it). Customers cannot deposit or pay by gateway in a currency without one.') }}</p>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead><tr><th>{{ __('Account') }}</th><th>{{ __('Balance') }}</th><th>{{ __('Status') }}</th></tr></thead>
                            <tbody>
                            @forelse ($platform as $a)
                                <tr>
                                    <td class="finance-mono">{{ $a['id'] }}</td>
                                    <td class="finance-amount">{{ \App\Finance\Money::format((int)($a['data']['balance'] ?? 0), $a['data']['currency'] ?? '') }}</td>
                                    <td>{{ $a['data']['status'] ?? '' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">{{ __('No platform account yet.') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                        @if ($canCreateAccount)
                            @foreach ($missingAccounts as $c)
                                <form method="POST" action="{{ route('finance.platform-accounts.store') }}" class="d-inline-block mr-2 mt-2"
                                      onsubmit="return confirm('{{ __('Create this platform account?') }} platform_{{ $c }}')">
                                    @csrf
                                    <input type="hidden" name="currency" value="{{ $c }}">
                                    <button class="btn btn-primary">{{ __('Create platform account') }} <span class="finance-mono">platform_{{ $c }}</span></button>
                                </form>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border mb-4">
                    <div class="card-header border-0">
                        <h3 class="text-dark-2 mb-1 h4">{{ __('Go-live checklist') }}</h3>
                        <p class="mb-0 text-dark-2">{{ __('What the panel must have created before customers use Finance.') }}</p>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                        @foreach ($goLive as $g)
                            <li class="mb-2">
                                @if ($g['ok'])<span class="finance-badge-ok">✓</span>@else<span class="finance-badge-no">✗</span>@endif
                                <span class="finance-mono ml-2">{{ $g['label'] }}</span>
                            </li>
                        @endforeach
                        </ul>
                    </div>
                </div>

                <div class="card border mb-4">
                    <div class="card-header border-0">
                        <h3 class="text-dark-2 mb-1 h4">{{ __('Second approver') }}</h3>
                        <p class="mb-0 text-dark-2">{{ __('Reversals and manual adjustments of at least this amount wait for a second admin. A region left blank always needs one.') }}</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('finance.four-eyes.update') }}">
                            @csrf
                            <table class="finance-money">
                                @foreach ($regions as $id => $r)
                                    <tr>
                                        <td>{{ $r['name'] }}</td>
                                        <td><input type="text" inputmode="decimal" class="form-control" name="threshold[{{ $id }}]"
                                                   value="{{ isset($thresholds[$id][$r['currency']]) ? \App\Finance\Money::toMajor($thresholds[$id][$r['currency']], $r['currency']) : '' }}" {{ $canEdit && $r['currency'] ? '' : 'disabled' }}></td>
                                        <td class="finance-mono">{{ $r['currency'] }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            @if ($canEdit && $regions)
                                <button class="btn btn-primary mt-2">{{ __('Save') }}</button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
