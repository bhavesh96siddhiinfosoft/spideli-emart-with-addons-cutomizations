@extends('layouts.app')
@section('content')
@php
    use App\Finance\Money;
    $tz = new DateTimeZone(config('app.timezone'));
    $day = function ($v, $time = false) use ($tz) { return $v instanceof \DateTimeInterface ? $v->setTimezone($tz)->format($time ? 'Y-m-d H:i' : 'Y-m-d') : ''; };
    $cur = $a['currency'] ?? '';
    $status = $a['status'] ?? '';
    $docOpen = in_array($status, ['submitted', 'under_review', 'additional_info_required'], true);
    $missing = array_keys(array_filter($required, function ($s) { return $s !== 'accepted'; }));
    $p = $project ?? [];
@endphp
<div class="page-wrapper finance-page">
    @include('finance._top', ['pageTitle' => __('Financing') . ': ' . ($a['projectName'] ?? ''), 'tab' => 'financing'])
    <div class="container-fluid">
        @if ($lockedBy)<div class="alert alert-warning">{{ __('Another admin is working on this application:') }} {{ $lockedBy }}</div>@endif
        <div class="card"><div class="card-body">
        <div class="row vendor_payout_create"><div class="vendor_payout_create-inner">
            <fieldset>
                <legend><i class="mr-3 mdi mdi-briefcase"></i>{{ __('Application') }}</legend>
                @foreach ([
                    __('Owner') => $names[$a['ownerId'] ?? ''] ?? ($a['ownerId'] ?? ''),
                    __('Status') => $status . (!empty($a['reason']) ? ' - ' . $a['reason'] : '') . (!empty($a['demo']) ? ' (demo)' : ''),
                    __('Financing type') => __(ucfirst($a['financingType'] ?? '')),
                    __('Amount asked') => Money::format((int)($a['amount'] ?? 0), $cur) . (!empty($a['durationMonths']) ? ' · ' . $a['durationMonths'] . ' ' . __('months') : ''),
                    __('Submitted') => $day($a['submittedAt'] ?? null),
                    __('Approved') => isset($a['approvedAmount']) ? Money::format((int)$a['approvedAmount'], $cur) . ' · ' . ($a['institutionName'] ?? '') : '',
                    __('Funded') => isset($a['fundedAmount']) ? Money::format((int)$a['fundedAmount'], $cur) . ' · ' . $day($a['fundedAt'] ?? null) : '',
                ] as $label => $value)
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ $label }}</label><div class="col-7"><span class="form-control-plaintext">{{ $value !== '' ? $value : '—' }}</span></div></div>
                @endforeach
                <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Use of the funds') }}</label><div class="col-7"><span class="form-control-plaintext" style="white-space:pre-line">{{ $a['useOfFunds'] ?? '' }}</span></div></div>
                @if (!empty($a['ownerMessage']))<div class="form-group row width-100"><label class="col-3 control-label">{{ __('Owner\'s message') }}</label><div class="col-7"><span class="form-control-plaintext" style="white-space:pre-line">{{ $a['ownerMessage'] }}</span></div></div>@endif
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-store"></i>{{ __('Project') }}</legend>
                @foreach ([__('Name') => $p['name'] ?? ($a['projectName'] ?? ''), __('Sector') => $p['sector'] ?? '', __('Stage') => $p['stage'] ?? '', __('Place') => trim(($p['city'] ?? '') . ', ' . ($p['country'] ?? ''), ', '),
                    __('Employees') => $p['employees'] ?? '', __('Founded') => $p['foundedYear'] ?? '', __('Website') => $p['website'] ?? ''] as $label => $value)
                    <div class="form-group row width-50"><label class="col-3 control-label">{{ $label }}</label><div class="col-7"><span class="form-control-plaintext">{{ (string)$value !== '' ? $value : '—' }}</span></div></div>
                @endforeach
                <div class="form-group row width-100"><label class="col-3 control-label">{{ __('Description') }}</label><div class="col-7"><span class="form-control-plaintext" style="white-space:pre-line">{{ $p['description'] ?? '—' }}</span></div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-file-document-multiple"></i>{{ __('Documents') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    @if ($required)
                        <p class="mb-2">{{ __('Required for this type:') }}
                            @foreach ($required as $type => $s)<span class="mr-2 {{ $s === 'accepted' ? 'finance-badge-ok' : 'finance-badge-no' }}">{{ $type }}: {{ $s }}</span>@endforeach</p>
                    @endif
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>{{ __('Type') }}</th><th>{{ __('Name') }}</th><th>{{ __('Uploaded') }}</th><th>{{ __('Status') }}</th>@if ($canManage && $docOpen)<th style="width:40%">{{ __('Decision') }}</th>@endif</tr></thead>
                        <tbody>
                        @forelse ($documents as $doc)
                            @php $d = $doc['data']; @endphp
                            <tr><td>{{ $d['type'] ?? '' }}</td>
                                <td><a href="{{ route('finance.financing.document', $doc['id']) }}" target="_blank" rel="noopener">{{ $d['name'] ?? '' }}</a><div class="finance-help">{{ $d['contentType'] ?? '' }} · {{ number_format(((int)($d['size'] ?? 0)) / 1024, 0) }} KB</div></td>
                                <td>{{ $day($d['uploadedAt'] ?? null) }}</td>
                                <td>{{ $d['status'] ?? 'received' }}@if (!empty($d['note']))<div class="finance-help">{{ $d['note'] }}</div>@endif</td>
                                @if ($canManage && $docOpen)<td>
                                    <form method="POST" action="{{ route('finance.financing.action', [$id, 'document']) }}" class="d-flex">@csrf
                                        <input type="hidden" name="documentId" value="{{ $doc['id'] }}">
                                        <input type="text" name="reason" class="form-control form-control-sm mr-1" placeholder="{{ __('What to fix (to reject)') }}">
                                        <button name="decision" value="accept" class="btn btn-sm btn-success mr-1">{{ __('Accept') }}</button>
                                        <button name="decision" value="reject" class="btn btn-sm btn-outline-danger">{{ __('Reject') }}</button></form>
                                </td>@endif</tr>
                        @empty
                            <tr><td colspan="5" class="text-muted">{{ __('No document uploaded.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    <div class="finance-help mt-1">{{ __('Each link is valid 5 minutes.') }}</div>
                </div></div>
            </fieldset>

            @if ($canManage && in_array($status, ['submitted', 'under_review', 'additional_info_required', 'approved', 'funded'], true))
            <fieldset>
                <legend><i class="mr-3 mdi mdi-gavel"></i>{{ __('Decision') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <p class="finance-help">{{ __('The note of each step is shown to the owner. Internal notes (below) are not.') }}</p>
                    @if ($status === 'submitted')
                        <form method="POST" action="{{ route('finance.financing.action', [$id, 'review']) }}" class="d-flex mb-3">@csrf
                            <input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('Note for the owner (optional)') }}">
                            <button class="btn btn-primary text-nowrap">{{ __('Start the review') }}</button></form>
                    @endif
                    @if (in_array($status, ['submitted', 'under_review'], true))
                        <form method="POST" action="{{ route('finance.financing.action', [$id, 'request-info']) }}" class="d-flex mb-3">@csrf
                            <input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('What the owner must add or change') }}" required>
                            <button class="btn btn-outline-primary text-nowrap">{{ __('Ask for information') }}</button></form>
                        <form method="POST" action="{{ route('finance.financing.action', [$id, 'approve']) }}" class="mb-3" onsubmit="return confirm('{{ __('Approve this application?') }}')">@csrf
                            <div class="d-flex">
                                <div class="finance-amount-box mr-2" style="margin-bottom:0;min-width:180px"><span class="finance-symbol">{{ $cur }}</span>
                                    <input type="text" inputmode="decimal" name="approvedAmount" class="form-control" style="padding-left:48px" value="{{ old('approvedAmount', Money::toMajor((int)($a['amount'] ?? 0), $cur)) }}" required></div>
                                <input type="text" name="institutionName" class="form-control mr-2" placeholder="{{ __('Financing institution') }}" value="{{ old('institutionName') }}" required>
                                <input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('Note for the owner (optional)') }}">
                                <button class="btn btn-success text-nowrap">{{ __('Approve') }}</button>
                            </div>
                            @if ($missing)<div class="finance-field-error">{{ __('Every required document must be accepted first:') }} {{ implode(', ', $missing) }}</div>@endif
                        </form>
                    @endif
                    @if ($status === 'additional_info_required')
                        <p class="finance-help">{{ __('Waiting for the owner to complete the application and submit it again.') }}</p>
                    @endif
                    @if (in_array($status, ['submitted', 'under_review', 'additional_info_required'], true))
                        <form method="POST" action="{{ route('finance.financing.action', [$id, 'reject']) }}" class="d-flex mb-3" onsubmit="return confirm('{{ __('Reject this application?') }}')">@csrf
                            <input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('Reason for the owner') }}" required>
                            <button class="btn btn-outline-danger text-nowrap">{{ __('Reject') }}</button></form>
                    @endif
                    @if ($status === 'approved')
                        <form method="POST" action="{{ route('finance.financing.action', [$id, 'fund']) }}" class="d-flex mb-3" onsubmit="return confirm('{{ __('Record this funding?') }}')">@csrf
                            <div class="finance-amount-box mr-2" style="margin-bottom:0;min-width:180px"><span class="finance-symbol">{{ $cur }}</span>
                                <input type="text" inputmode="decimal" name="fundedAmount" class="form-control" style="padding-left:48px" value="{{ old('fundedAmount', Money::toMajor((int)($a['approvedAmount'] ?? 0), $cur)) }}" required></div>
                            <input type="date" name="fundedAt" class="form-control mr-2" value="{{ old('fundedAt', (new DateTimeImmutable())->setTimezone($tz)->format('Y-m-d')) }}" required>
                            <input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('Note for the owner (optional)') }}">
                            <button class="btn btn-success text-nowrap">{{ __('Record the funding') }}</button></form>
                        <p class="finance-help">{{ __('The institution pays the owner; nothing moves in Spideli. This records it and tells the owner.') }}</p>
                    @endif
                    @if (in_array($status, ['approved', 'funded'], true))
                        <form method="POST" action="{{ route('finance.financing.action', [$id, 'close']) }}" class="d-flex" onsubmit="return confirm('{{ __('Close this application?') }}')">@csrf
                            <input type="text" name="reason" class="form-control mr-2" placeholder="{{ __('Note for the owner (optional)') }}">
                            <button class="btn btn-outline-secondary text-nowrap">{{ __('Close') }}</button></form>
                    @endif
                </div></div>
            </fieldset>
            @endif

            <fieldset>
                <legend><i class="mr-3 mdi mdi-history"></i>{{ __('Steps (seen by the owner)') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('From') }}</th><th>{{ __('To') }}</th><th>{{ __('Note') }}</th></tr></thead>
                        <tbody>
                        @forelse ($reviews as $r)
                            <tr><td>{{ $day($r['data']['at'] ?? null, true) }}</td><td>{{ $r['data']['fromStatus'] ?? '' }}</td><td>{{ $r['data']['toStatus'] ?? '' }}</td><td>{{ $r['data']['publicNote'] ?? '' }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">{{ __('Nothing yet.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div></div>
            </fieldset>

            <fieldset>
                <legend><i class="mr-3 mdi mdi-lock"></i>{{ __('Internal notes (staff only)') }}</legend>
                <div class="form-group row width-100"><div class="col-7">
                    @foreach ($notes as $n)
                        <div class="mb-2"><strong>{{ $n['data']['authorEmail'] ?? $n['data']['authorId'] ?? '' }}</strong> <span class="finance-help">{{ $day($n['data']['at'] ?? null, true) }}</span>
                            <div style="white-space:pre-line">{{ $n['data']['text'] ?? '' }}</div></div>
                    @endforeach
                    @if ($canManage)
                        <form method="POST" action="{{ route('finance.financing.action', [$id, 'note']) }}" class="d-flex">@csrf
                            <textarea name="reason" class="form-control mr-2" rows="2" placeholder="{{ __('Never shown to the owner') }}" required></textarea>
                            <button class="btn btn-outline-primary text-nowrap">{{ __('Add the note') }}</button></form>
                    @endif
                </div></div>
            </fieldset>
        </div></div>
        </div></div>
    </div>
</div>
@endsection
