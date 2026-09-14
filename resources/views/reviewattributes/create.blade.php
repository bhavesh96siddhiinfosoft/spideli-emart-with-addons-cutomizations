@extends('layouts.app')

@section('content')
<div class="page-wrapper">
  
  <div class="row page-titles">
    <div class="col-md-5 align-self-center">
      <h3 class="text-themecolor">{{trans('lang.reviewattribute_plural')}}</h3>
    </div>

    <div class="col-md-7 align-self-center">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
        <li class="breadcrumb-item"><a
            href="{!! route('reviewattributes') !!}">{{trans('lang.reviewattribute_plural')}}</a></li>
        <li class="breadcrumb-item active">{{trans('lang.reviewattribute_create')}}</li>
      </ol>
    </div>
  </div>

  <div class="card-body">    
    <div class="error_top" style="display:none"></div>
    <div class="row vendor_payout_create">
      <div class="vendor_payout_create-inner">
        <fieldset>
          <legend>{{trans('lang.reviewattribute_create')}}</legend>
          <div class="form-group row width-100">
            <label class="col-3 control-label">{{trans('lang.reviewattribute_name')}}</label>
            <div class="col-7">
              <input type="text" class="form-control cat-name">
              <div class="form-text text-muted">{{ trans("lang.reviewattribute_name_help") }} </div>
            </div>
          </div>

        </fieldset>
      </div>

    </div>
  </div>
  <div class="form-group col-12 text-center btm-btn">
    <button type="button" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i>{{trans('lang.save')}}</button>
    <a href="{!! route('reviewattributes') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
  </div>
</div>

@endsection

@section('scripts')

<script type="text/javascript">

  var database = firebase.firestore();
  var ref = database.collection('review_attributes');
  var photo = "";
  var id_reviewattribute = "<?php echo uniqid(); ?>";
  var reviewattribute_length = 1;


  $(document).ready(function () {
    jQuery("#data-table_processing").show();
    ref.get().then(async function (snapshots) {
      reviewattribute_length = snapshots.size + 1;
      jQuery("#data-table_processing").hide();
    })

    $(".save-form-btn").click(function () {
      var title = $(".cat-name").val();

      if (title == '') {

        $(".error_top").show();
        $(".error_top").html("");
        $(".error_top").append("<p>{{trans('lang.enter_cat_title_error')}}</p>");
        window.scrollTo(0, 0);
      } else {

        database.collection('review_attributes').doc(id_reviewattribute).set({ 'id': id_reviewattribute, 'title': title }).then(function (result) {
          window.location.href = '{{ route("reviewattributes")}}';
        });

      }

    });


  });

</script>
@endsection