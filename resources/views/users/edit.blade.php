@extends('layouts.app')

@section('content')

<?php
$countries = file_get_contents(public_path('countriesdata.json'));
$countries = json_decode($countries);
$countries = (array)$countries;
$newcountries = array();
$newcountriesjs = array();
foreach ($countries as $keycountry => $valuecountry) {
    $newcountries[$valuecountry->phoneCode] = $valuecountry;
    $newcountriesjs[$valuecountry->phoneCode] = $valuecountry->code;
}
?>

<div class="page-wrapper">

    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.user_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('users') !!}">{{trans('lang.user_plural')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.user_edit')}}</li>
            </ol>
        </div>

    </div>

    <div>
        <div class="card-body">
           
            <div class="row daes-top-sec mb-3">

                <div class="col-lg-6 col-md-6">

                    <a href="{{ route('orders', ['userId' => $id]) }}">

                        <div class="card">

                            <div class="flex-row">

                                <div class="p-10 bg-info col-md-12 text-center">

                                    <h3 class="text-white box m-b-0"><i class="mdi mdi-cart"></i></h3>
                                </div>

                                <div class="align-self-center pt-3 col-md-12 text-center">

                                    <h3 class="m-b-0 text-info" id="total_orders">0</h3>

                                    <h5 class="text-muted m-b-0">{{trans('lang.dashboard_total_orders')}}</h5>

                                </div>

                            </div>

                        </div>
                    </a>
                </div>

                <div class="col-lg-6 col-md-6">
                    <a href="{{route('users.walletstransaction',$id)}}">
                        <div class="card">

                            <div class="flex-row">

                                <div class="p-10 bg-info col-md-12 text-center">

                                    <h3 class="text-white box m-b-0"><i class="mdi mdi-bank"></i></h3>
                                </div>

                                <div class="align-self-center pt-3 col-md-12 text-center">

                                    <h3 class="m-b-0 text-info" id="wallet_amount">0</h3>

                                    <h5 class="text-muted m-b-0">{{trans('lang.wallet_Balance')}}</h5>

                                </div>

                            </div>

                        </div>
                    </a>
                </div>

            </div>


            <div class="error_top"></div>
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">

                    <fieldset>
                        <legend>{{trans('lang.user_edit')}}</legend>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{trans('lang.first_name')}}</label>
                            <div class="col-7">
                                <input type="text" class="form-control user_first_name">
                                <div class="form-text text-muted">
                                    {{ trans("lang.user_first_name_help") }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{trans('lang.last_name')}}</label>
                            <div class="col-7">
                                <input type="text" class="form-control user_last_name">
                                <div class="form-text text-muted">
                                    {{ trans("lang.user_last_name_help") }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{trans('lang.email')}}</label>
                            <div class="col-7">
                                <input type="text" class="form-control user_email" >
                                <div class="form-text text-muted">
                                    {{ trans("lang.user_email_help") }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group row width-50">
                            <label class="col-3 control-label">{{trans('lang.user_phone')}}</label>
                            <div class="col-7">
                                <div class="phone-box position-relative" id="phone-box"> 
                                            <select name="country" id="country_selector">
                                                <?php foreach ($newcountries as $keycy => $valuecy) { ?>
                                                <?php $selected = ""; ?>
                                                <option <?php echo $selected; ?> code="<?php echo $valuecy->code; ?>"
                                                        value="<?php echo $keycy; ?>">
                                                    +<?php echo $valuecy->phoneCode; ?> {{$valuecy->countryName}}</option>
                                                <?php } ?>
                                            </select>
                                    <input type="text" class="form-control user_phone"
                                        onkeypress="return chkAlphabets2(event,'error2')">
                                    <div id="error2" class="err"></div>
                                </div>
                            </div>

                            <div class="form-text text-muted w-50">
                                {{ trans("lang.user_phone_help") }}
                            </div>

                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.vendor_image')}}</label>
                            <input type="file" onChange="handleFileSelect(event)" class="col-7" id="userImage">
                            <div class="placeholder_img_thumb user_image"></div>
                            <div id="uploding_image"></div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>{{trans('lang.user_active_deactive')}}</legend>
                        <div class="form-group row width-100">
                            <div class="form-check">
                                <input type="checkbox" class="user_active" id="user_active">
                                <label class="col-3 control-label" for="user_active">{{trans('lang.active')}}</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="reset_password">
                                <label class="col-3 control-label">{{trans('lang.reset_password')}}</label>
                                <div class="form-text text-muted w-100">
                                    {{ trans("lang.note_reset_password_email") }}
                                </div>
                            </div>
                            <div class="form-button" style="margin-top: 16px;margin-left: 20px;">
                                <button type="button" class="btn btn-primary"
                                    id="send_mail">{{trans('lang.send_mail')}}</button>
                            </div>
                        </div>
                    </fieldset>


                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary  edit-form-btn"><i class="fa fa-save"></i> {{
                trans('lang.save')}}
            </button>
            <a href="{!! route('users') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
                trans('lang.cancel')}}</a>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script type="text/javascript">

    var section_id = getCookie('section_id') || null;
    var id = "<?php echo $id; ?>";
    var database = firebase.firestore();
    var ref = database.collection('users').where("id", "==", id);
    var storageRef = firebase.storage().ref('images');
    var storage = firebase.storage();

    var photo = "";
    var fileName = "";
    var oldImageFile = '';
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');

    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })
    var currency = database.collection('settings');

    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;

    var refCurrency = regionCurrencyRef();
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;

        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });

    $(document).ready(function () {

        jQuery("#country_selector").select2({
            templateResult: formatState,
            templateSelection: formatState2,
            placeholder: "Select Country",
            allowClear: true
        });

        currency.where("isActive", "==", true).get().then((snapshot) => {

        });
        jQuery("#data-table_processing").show();

        ref.get().then(async function (snapshots) {
            jQuery("#data-table_processing").hide();
            if (!snapshots.empty) {
                var user = snapshots.docs[0].data();
                $(".user_first_name").val(user.firstName);
                $(".user_last_name").val(user.lastName);
                $(".user_email").val(shortEmail(user.email)).prop('disabled',true);
                $("#country_selector").val(user.countryCode.replace('+', '')).trigger('change');
                if(user.phoneNumber != ""){
                    $(".user_phone").val(user.phoneNumber);
                }
                else{
                    $(".user_phone").val("");
                }

                $(".user_phone").attr('disabled',true);

                photo = user.profilePictureURL;
                if (photo != '') {
                    photo = user.profilePictureURL;
                    oldImageFile = user.profilePictureURL;
                    $(".user_image").append('<span class="image-item"><span class="remove-btn"><i class="fa fa-remove"></i></span><img class="rounded" style="width:50px" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image"></span>');
                } else {
                    $(".user_image").append('<img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">');
                }

                if (user.active) {
                    $(".user_active").prop('checked', true);
                }

                var wallet = 0;

                if (user.wallet_amount) {
                    wallet = user.wallet_amount;
                }
                if (currencyAtRight) {
                    wallet = parseFloat(wallet).toFixed(decimal_degits) + "" + currentCurrency;
                } else {
                    wallet = currentCurrency + "" + parseFloat(wallet).toFixed(decimal_degits);
                }

                $("#wallet_amount").text(wallet);

                var orderRef = database.collection('vendor_orders').where("authorID", "==", id).where('section_id', '==', section_id);
                orderRef.get().then(async function (snapshotsorder) {
                    var orders = snapshotsorder.size;
                    $("#total_orders").text(orders);
                });

                jQuery("#data-table_processing").hide();
            }
        })

        $("#send_mail").click(function () {
            if ($("#reset_password").is(":checked")) {
                var email = $(".user_email").val();
                firebase.auth().sendPasswordResetEmail(email)
                    .then((res) => {
                        alert('{{trans("lang.mail_sent")}}');
                    })
                    .catch((error) => {
                        console.log('Error password reset: ', error);
                    });
            }
        });

        $(".edit-form-btn").click(function () {

            var userFirstName = $(".user_first_name").val();
            var userLastName = $(".user_last_name").val();
            var email = $(".user_email").val();
            var countryCode = '+' + jQuery("#country_selector").val();
            var userPhone = $(".user_phone").val();
            var active = $(".user_active").is(":checked");
            var user_name = userFirstName + " " + userLastName;

            if (userFirstName == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.user_firstname_error')}}</p>");
                window.scrollTo(0, 0);
            }else if (userLastName == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.user_lastname_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (userPhone == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.user_phone_error')}}</p>");
                window.scrollTo(0, 0);
            } else {

                storeImageData().then(IMG => {

                    database.collection('users').doc(id).update({
                        'firstName': userFirstName,
                        'lastName': userLastName,
                        'email': email,
                        'countryCode': countryCode,
                        'phoneNumber': userPhone,
                        'isActive': active,
                        'profilePictureURL': IMG,
                        'role': 'customer',
                        'active': active,
                    }).then(function (result) {
                        window.location.href = '{{ route("users")}}';
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
                photo = filePayload;
                fileName = filename;
                $(".user_image").empty();
                $(".user_image").append('<span class="image-item" id="photo_user"><span class="remove-btn" data-id="user-remove" data-img="' + photo + '"><i class="fa fa-remove"></i></span><img class="rounded" style="width:50px" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image"></span>');

            };
        })(f);
        reader.readAsDataURL(f);
    }
    async function storeImageData() {
        var newPhoto = '';
        try {
            if (oldImageFile != "" && photo != oldImageFile) {
                var oldImageUrl = await storage.refFromURL(oldImageFile);
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
            if (photo != oldImageFile) {
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

    function formatState(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/');?>/scss/icons/flag-icon-css/flags";
        var $state = $(
            '<span><img src="' + baseUrl + '/' + newcountriesjs[state.element.value].toLowerCase() + '.svg" class="img-flag" /> ' + state.text + '</span>'
        );
        return $state;
    }
    function formatState2(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/');?>/scss/icons/flag-icon-css/flags"
        var $state = $(
            '<span><img class="img-flag" /> <span></span></span>'
        );
        $state.find("span").text(state.text);
        $state.find("img").attr("src", baseUrl + "/" + newcountriesjs[state.element.value].toLowerCase() + ".svg");
        return $state;
    }
    var newcountriesjs = '<?php echo json_encode($newcountriesjs); ?>';
    var newcountriesjs = JSON.parse(newcountriesjs);

    $(document).on("click", ".remove-btn", function () {
        $(".image-item").remove();
        $('#userImage').val('');
    });

    function chkAlphabets(event, msg) {
        if (!(event.which >= 97 && event.which <= 122) && !(event.which >= 65 && event.which <= 90)) {
            document.getElementById(msg).innerHTML = "Accept only Alphabets";
            return false;
        }
        else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

    function chkAlphabets2(event, msg) {
        if (!(event.which >= 48 && event.which <= 57)
        ) {
            document.getElementById(msg).innerHTML = "Accept only Number";
            return false;
        }
        else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }
    function chkAlphabets3(event, msg) {
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
            document.getElementById(msg).innerHTML = "Accept only Number and Dot(.)";
            return false;
        }
        else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }
</script>
@endsection