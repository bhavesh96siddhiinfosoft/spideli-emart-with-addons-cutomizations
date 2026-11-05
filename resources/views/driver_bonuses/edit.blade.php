@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.driver_bonus_edit') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('driver-bonuses') !!}">{{ trans('lang.driver_bonus_plural') }}</a></li>
                <li class="breadcrumb-item active">{{ trans('lang.driver_bonus_edit') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card-body">
            <div class="error_top" style="display:none"></div>
            <div class="alert alert-info">{{ trans('lang.driver_bonus_edit_note') }}</div>
            @include('driver_bonuses.partials.form')
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary save-rule-btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
            <a href="{!! route('driver-bonuses') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('driver_bonuses.partials.form_scripts')
<script>
    var bonusRuleId = '{{ $id }}';

    $(document).ready(async function () {
        jQuery("#overlay").show();
        var doc = await database.collection('driver_bonus_rules').doc(bonusRuleId).get();
        if (!doc.exists) {
            window.location.href = '{{ route('driver-bonuses') }}';
            return;
        }
        var rule = doc.data();
        /* A rule outside the region the admin works in is not reachable by url. */
        if (!isInActiveRegion(rule)) {
            window.location.href = '{{ route('driver-bonuses') }}';
            return;
        }
        fillBonusRuleForm(rule);
        await loadBonusRegionOptions(rule.regionId || '');
        jQuery("#overlay").hide();
    });

    $('.save-rule-btn').click(async function () {
        var rule = readBonusRuleForm();
        if (rule === null) {
            return false;
        }
        jQuery("#overlay").show();
        rule.updatedAt = firebase.firestore.FieldValue.serverTimestamp();
        await database.collection('driver_bonus_rules').doc(bonusRuleId).update(rule);
        window.location.href = '{{ route('driver-bonuses') }}';
    });
</script>
@endsection
