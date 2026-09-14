@extends('layouts.app')



@section('content')

<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.add_language')}}</h3>

        </div>



        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>

                <li class="breadcrumb-item"><a

                        href="{!! url('settings/app/languages') !!}">{{trans('lang.languages')}}</a></li>

                <li class="breadcrumb-item active">{{trans('lang.add_language')}}</li>

            </ol>

        </div>



        <div class="card-body">


            <div class="error_top"></div>



            <div class="row vendor_payout_create">

                <div class="vendor_payout_create-inner">

                    <fieldset>

                        <div class="form-group row width-50">

                            <label class="col-3 control-label">{{trans('lang.name')}}</label>

                            <div class="col-7">

                                <input type="text" class="form-control title" id="title">

                            </div>

                        </div>



                        <div class="form-group row width-50">

                            <label class="col-3 control-label">{{trans('lang.slug')}}</label>

                            <div class="col-7">

                                <input type="text" class="form-control slug" id="slug">

                                <div class="form-text text-muted">

                                    {{ trans("lang.slug_help") }}

                                </div>

                            </div>

                        </div>

                        <div class="form-group row width-50">

                            <div class="form-check">

                                <input type="checkbox" class="is_active" id="is_active">

                                <label class="col-3 control-label" for="is_active">{{trans('lang.active')}}</label>

                            </div>

                        </div>

                        <div class="form-group row width-50">

                            <div class="form-check">

                                <input type="checkbox" class="is_rtl" id="is_rtl">

                                <label class="col-3 control-label" for="is_rtl">{{trans('lang.is_rtl')}}</label>

                            </div>

                        </div>

                        <div class="form-group row width-50">

                            <label class="col-3 control-label" for="flag">{{trans('lang.flag')}}</label>

                            <div class="col-7">

                                <input type="file" onChange="handleFileSelect(event)" class="flag" id="flag">

                                <div class="placeholder_img_thumb user_image"></div>

                                <div id="uploding_image"></div>

                            </div>

                        </div>



                    </fieldset>

                </div>

            </div>

        </div>



        <div class="form-group col-12 text-center btm-btn">

            <button type="button" class="btn btn-primary  save-setting-btn"><i class="fa fa-save"></i> {{

                trans('lang.save')}}</button>

            <a href="{!! url('settings/app/languages') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{

                trans('lang.cancel')}}</a>

        </div>



    </div>



</div>



@endsection



@section('scripts')



<script type="text/javascript">



    var database = firebase.firestore();

    var ref = database.collection('settings').doc('languages');

    var languages = [];

    var photo = "";

    var fileName = "";

    var storageRef = firebase.storage().ref('images');



    $(document).ready(function () {



        ref.get().then(async function (snapshots) {

            snapshots = snapshots.data();

            if (snapshots == undefined) {

                database.collection('settings').doc('languages').set({ 'list': '' });

            } else {

                snapshots = snapshots.list;

                if (snapshots.length) {

                    languages = snapshots;

                }

            }

        });



    });



    $(".save-setting-btn").click(function () {



        var title = $("#title").val();

        var slug = $("#slug").val();



        var active = $(".is_active").is(":checked");

        var is_rtl = $(".is_rtl").is(":checked");

        var flag = $("#flag").val();



        if (title == '') {

            $(".error_top").show();

            $(".error_top").html("");

            $(".error_top").append("<p>{{trans('lang.name_error')}}</p>");

            window.scrollTo(0, 0);



        } else if (slug == '') {

            $(".error_top").show();

            $(".error_top").html("");

            $(".error_top").append("<p>{{trans('lang.slug_error')}}</p>");

            window.scrollTo(0, 0);

        } else if (flag == '') {

            $(".error_top").show();

            $(".error_top").html("");

            $(".error_top").append("<p>{{trans('lang.flag_error')}}</p>");

            window.scrollTo(0, 0);

        } else {





            jQuery("#data-table_processing").show();

            storeImageData().then(IMG => {

                if (languages.length) {

                    languages.push({ 'title': title, 'slug': slug, 'isActive': active, 'is_rtl': is_rtl, 'flag': IMG });

                } else {

                    languages = [{ 'title': title, 'slug': slug, 'isActive': active, 'is_rtl': is_rtl, 'flag': IMG }];

                }

                database.collection('settings').doc('languages').update({ 'list': languages }).then(function (result) {

                    jQuery("#data-table_processing").hide();

                    window.location.href = '{{ route("settings.app.languages") }}';

                });

            }).catch(function (error) {

                jQuery("#data-table_processing").hide();

                $(".error_top").show();

                $(".error_top").html("");

                $(".error_top").append("<p>" + error + "</p>");



            });

        }





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

                $(".user_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');



            };

        })(f);

        reader.readAsDataURL(f);

    }

    async function storeImageData() {

        var newPhoto = '';

        try {

            photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")

            var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', { contentType: 'image/jpg' });

            var downloadURL = await uploadTask.ref.getDownloadURL();

            newPhoto = downloadURL;

            photo = downloadURL;

        } catch (error) {

            console.log("ERR ===", error);

        }

        return newPhoto;

    }



</script>

@endsection