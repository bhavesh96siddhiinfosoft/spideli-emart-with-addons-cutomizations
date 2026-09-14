@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
      <div class="col-md-5 align-self-center">
        <h3 class="text-themecolor">{{trans('lang.parcelcategory_plural')}}</h3>
      </div>
      <div class="col-md-7 align-self-center">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
          <li class="breadcrumb-item"><a href="{!! route('parcelCategory') !!}">{{trans('lang.parcelcategory_plural')}}</a></li>
        </ol>
      </div>
    </div>
    <div class="card-body">
      <div class="error_top"></div>
      <div class="row vendor_payout_create">
        <div class="vendor_payout_create-inner">
          <fieldset>
            <legend>{{trans('lang.parcel_category')}}</legend>
            <div class="form-group row width-50">
              <label class="col-3 control-label">{{trans('lang.title')}}</label>
              <div class="col-7">
                <input type="text" class="form-control title" id="title">
              </div>
            </div>
            <div class="form-group row width-50">
              <label class="col-3 control-label">{{trans('lang.set_order')}}</label>
              <div class="col-7">
                <input type="number" class="form-control set_order" id="set_order">
              </div>
            </div>
            <div class="form-group row width-50">
              <label class="col-3 control-label">{{trans('lang.photo')}}</label>
              <input type="file" onChange="handleFileSelect(event)" class="col-7">
              <div id="uploding_image"></div>
              <div class="placeholder_img_thumb user_image"></div>
            </div>
            <div class="form-check width-100">
              <input type="checkbox" id="publish" name="publish">
              <label class="col-4 control-label" for="publish">{{ trans('lang.is_publish')}}</label><br>
            </div>
          </fieldset>
        </div>
      </div>
    </div>
    <div class="form-group col-12 text-center btm-btn">
      <button type="button" class="btn btn-primary save-setting-btn"><i class="fa fa-save"></i> {{
          trans('lang.save')}}</button>
      <a href="{!! route('parcelCategory') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
          trans('lang.cancel')}}</a>
    </div>
  </div>
  @endsection
  @section('scripts')
  <script type="text/javascript">
    
    var database = firebase.firestore();
    var photo = "";
    var fileName = "";
    var storageRef = firebase.storage().ref('images');
    var id = "<?php echo uniqid(); ?>";
     
    $(".save-setting-btn").click(function () {
      var title = $('#title').val();
      var set_order = parseInt($("#set_order").val());
      var publish = $("#publish").is(":checked");
      var section = getCookie('section_id') || '';
      if (title == '') {
        $(".error_top").show();
        $(".error_top").html("");
        $(".error_top").append("<p>{{trans('lang.enter_parcel_title_error')}}</p>");
        window.scrollTo(0, 0);
      } else if (isNaN(set_order)) {
        $(".error_top").show();
        $(".error_top").html("");
        $(".error_top").append("<p>{{trans('lang.enter_parcel_set_order')}}</p>");
        window.scrollTo(0, 0);
      }  else {
        jQuery("#data-table_processing").show();
        storeImageData().then(IMG => {
          database.collection('parcel_categories').doc(id).set({
            'id': id,
            'title': title,
            'set_order': set_order,
            'publish': publish,
            'image': IMG,
            'sectionId' : section
          }).then(function (result) {
            window.location.href = '{{ route("parcelCategory")}}';
          });
        }).catch(function (error) {
          jQuery("#data-table_processing").hide();
          $(".error_top").show();
          $(".error_top").html("");
          $(".error_top").append("<p>" + error + "</p>");
        });
      }
    })
    async function storeImageData() {
        var newPhoto = '';
        try {
                if (photo != '') {
                    photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")
                    var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', { contentType: 'image/jpg' });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto = downloadURL;
                    photo = downloadURL;
                }
            } catch (error) {
                console.log("ERR ===", error);
            }
            return newPhoto;
        }
    function handleFileSelect(evt) {
      var f = evt.target.files[0];
      var reader = new FileReader();
      reader.onload = (function (theFile) {
        return function (e) {
          var filePayload = e.target.result;
          var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
          var val = f.name;
          var ext = val.split('.')[1];
          var docName = val.split('fakepath')[1];
          var filename = (f.name).replace(/C:\\fakepath\\/i, '')
          var timestamp = Number(new Date());
          var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
          fileName = filename;
          photo = filePayload;
          $(".user_image").empty();
          $(".user_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
        };
      })(f);
      reader.readAsDataURL(f);
    }
  </script>
  @endsection