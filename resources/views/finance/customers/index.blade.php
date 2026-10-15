@extends('layouts.app')
@section('content')
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Finance customers'), 'tab' => 'customers'])
    <div class="container-fluid">
        <div class="card border mb-4">
            <div class="card-header border-0">
                <h3 class="text-dark-2 mb-1 h4">{{ __('Finance customers') }}</h3>
                <p class="mb-0 text-dark-2">{{ __('Customers who use Spideli Finance: their verification, wallets, money movements and restrictions.') }}</p>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('finance.customers') }}" class="row">
                    <div class="col-md-4 form-group"><label>{{ __('Search') }}</label>
                        <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="{{ __('id, e-mail or phone') }}"></div>
                    <div class="col-md-3 form-group"><label>{{ __('Show') }}</label>
                        <select name="filter" class="form-control">
                            @foreach (['verified' => __('Verified'), 'pending' => __('Verification pending'), 'restricted' => __('Restricted')] as $k => $label)
                                <option value="{{ $k }}" {{ $filter === $k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group"><label>{{ __('Region') }}</label>
                        <select name="region" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($regions as $id => $r)<option value="{{ $id }}" {{ $region === $id ? 'selected' : '' }}>{{ $r['name'] }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group d-flex align-items-end"><button class="btn btn-primary">{{ __('Filter') }}</button></div>
                </form>
                @if ($q !== '')<p class="finance-help">{{ __('Search ignores the "Show" filter.') }}</p>@endif
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="financeCustomersTable">
                        <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Regions') }}</th><th>{{ __('Verification') }}</th><th>{{ __('Restriction') }}</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($rows as $r)
                            @php $d = $r['data']; $restr = $d['financeRestriction'] ?? null; @endphp
                            <tr>
                                <td>{{ trim(($d['firstName'] ?? '') . ' ' . ($d['lastName'] ?? '')) }}<div class="finance-help">{{ $d['email'] ?? '' }}</div></td>
                                <td class="finance-amount">{{ $d['phoneNumber'] ?? '' }}</td>
                                <td>{{ implode(', ', array_map(function ($id) use ($regionNames) { return $regionNames[$id] ?? $id; }, (array)($d['regionIds'] ?? []))) }}</td>
                                <td>
                                    @if (!empty($d['kycLevel']))<span class="finance-badge-ok">{{ $d['kycLevel'] }}</span>
                                    @else<span class="finance-help">{{ $d['kycStatus'] ?? __('not verified') }}</span>@endif
                                </td>
                                <td>@if ($restr)<span class="finance-badge-no">{{ implode(', ', (array)($restr['scopes'] ?? ['all'])) }}</span>@endif</td>
                                <td><a class="btn btn-sm btn-primary" href="{{ route('finance.customers.show', $r['id']) }}">{{ __('Open') }}</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(function () { $('#financeCustomersTable').DataTable({ pageLength: 25, language: typeof datatableLang !== 'undefined' ? datatableLang : {} }); });
</script>
@endsection
