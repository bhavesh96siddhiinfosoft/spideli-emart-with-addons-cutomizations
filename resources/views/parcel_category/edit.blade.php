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
        <li class="breadcrumb-item"><a href="{!! route('drivers') !!}">{{trans('lang.driver_plural')}}</a></li>
        <li class="breadcrumb-item active">{{trans('lang.driver_edit')}}</li>
      </ol>
    </div>
  </div>
  <div>
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
							<label class="col-3 control-label ">{{trans('lang.select_section')}}</label>
							<div class="col-7">
								<select name="section_id" id="section_id" class="form-control">
									<option value="">{{trans('lang.select')}}</option>
								</select>
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
      <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i> {{
        trans('lang.save')}}</button>
      <a href="{!! route('parcelCategory') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
        trans('lang.cancel')}}</a>
    </div>
  </div>
</div>
@endsection
@section('scripts')

<script type="text/javascript">

  var id = "<?php echo $id; ?>";
  var database = firebase.firestore();
  var ref = database.collection('parcel_categories').where("id", "==", id);
  var photo = "";
  var fileName = "";
  var oldFileName = "";
  var storageRef = firebase.storage().ref('images');
  var storage = firebase.storage();
  
  var placeholderImage = '';
  var placeholder = database.collection('settings').doc('placeHolderImage');
  placeholder.get().then(async function (snapshotsimage) {
    var placeholderImageData = snapshotsimage.data();
    placeholderImage = placeholderImageData.image;
  })
  
  $(document).ready(function () {

    var ref_sections = database.collection('sections').where('isActive', '==', true).orderBy('order');
    ref_sections.get().then(async function (snapshots) {
        snapshots.docs.forEach((listval) => {
          var data = listval.data();
          if (data.serviceTypeFlag == "parcel_delivery") {
            $('#section_id').append($("<option></option>")
              .attr("value", data.id)
              .text(data.name));
          }
        })
      })

    jQuery("#data-table_processing").show();
    ref.get().then(async function (snapshots) {
      var parcel = snapshots.docs[0].data();
      $("#title").val(parcel.title);
      $("#set_order").val(parcel.set_order);
      $('#section_id').val(parcel.sectionId).trigger('change');
      if (parcel.publish) {
        $("#publish").prop('checked', true);
      }
      photo = parcel.image;
      if (photo != '') {
        oldFileName = parcel.image;
        $(".user_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">');
      }
      jQuery("#data-table_processing").hide();
    })
    $(".edit-setting-btn").click(function () {
      var title = $('#title').val();
      var set_order = parseInt($("#set_order").val());
      var publish = $("#publish").is(":checked");
      var section = $('#section_id').val();

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
      } else if(section == '') {
        $(".error_top").show();
        $(".error_top").html("");
        $(".error_top").append("<p>{{trans('lang.sectionid_error')}}</p>");
        window.scrollTo(0, 0);
      } else {
        jQuery("#data-table_processing").show();
        storeImageData().then(IMG => {
          database.collection('parcel_categories').doc(id).update({
            'title': title,
            'set_order': set_order,
            'publish': publish,
            'image': IMG,
            'sectionId': section,
          }).then(function (result) {
            window.location.href = '{{ route("parcelCategory")}}';
          });
        }).catch(err => {
          jQuery("#data-table_processing").hide();
          $(".error_top").show();
          $(".error_top").html("");
          $(".error_top").append("<p>" + err + "</p>");
          window.scrollTo(0, 0);
        });
      }
    })
  })
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
        $(".user_image").append('<img class="rounded" style="width:50px" src="' + photo + '"  onerror="this.onerror=null;this.src=\'' + place_image + '\'" alt="image">');
      };
    })(f);
    reader.readAsDataURL(f);
  }
  async function storeImageData() {
    var newPhoto = '';
    try {
      if (oldFileName != "" && photo != oldFileName) {
        var oldImageUrl = await storage.refFromURL(oldFileName);
        imageBucket = oldImageUrl.bucket;
        var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
        if (imageBucket == envBucket) {
          await oldImageUrl.delete().then(() => {
            console.log("Old file deleted!")
          }).catch((error) => {
            console.log("ERR File delete ===", error);
          });
        } else {
          console.log('Bucket not matched');
        }
      }
      if (photo != oldFileName) {
        photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")
        var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', { contentType: 'image/jpg' });
        var downloadURL = await uploadTask.ref.getDownloadURL();
        newPhoto = downloadURL;
        photo = downloadURL;
      } else {
        newPhoto = photo;
      }
    } catch (error) {
      console.log("ERR ===", error);
    }
    return newPhoto;
  }
</script>
@endsection