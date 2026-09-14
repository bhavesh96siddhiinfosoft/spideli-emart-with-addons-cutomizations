@extends('layouts.app')
@section('content')
<?php
$countries = file_get_contents(public_path('countriesdata.json'));
$countries = json_decode($countries);
$countries = (array) $countries;
$newcountries = [];
$newcountriesjs = [];
foreach ($countries as $keycountry => $valuecountry) {
    $newcountries[$valuecountry->phoneCode] = $valuecountry;
    $newcountriesjs[$valuecountry->phoneCode] = $valuecountry->code;
}
?>
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{ trans('lang.create_deliveryman') }}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{!! route('deliveryman') !!}">{{ trans('lang.deliveryman') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ trans('lang.create_deliveryman') }}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="resttab-sec">
                    <div class="error_top"></div>
                    <div class="row vendor_payout_create">
                        <div class="vendor_payout_create-inner">
                            <fieldset>
                                <legend>{{ trans('lang.basic_details') }}</legend>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.first_name') }}</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control user_first_name" required>
                                        <div class="form-text text-muted">
                                            {{ trans('lang.user_first_name_help') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.last_name') }}</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control user_last_name">
                                        <div class="form-text text-muted">
                                            {{ trans('lang.user_last_name_help') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.email') }}</label>
                                    <div class="col-7">
                                        <input type="email" class="form-control user_email" required>
                                        <div class="form-text text-muted">
                                            {{ trans('lang.user_email_help') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{ trans('lang.password') }}</label>
                                    <div class="col-7">
                                        <input type="password" class="form-control password res_password" required>
                                        <div class="form-text text-muted">
                                            {{ trans('lang.user_password_help') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group form-material">
                                    <label class="col-3 control-label">{{ trans('lang.user_phone') }}</label>
                                    <div class="col-12">
                                        <div class="phone-box position-relative" id="phone-box">
                                            <select name="country" id="country_selector">
                                                <?php foreach ($newcountries as $keycy => $valuecy) { ?>
                                                    <?php $selected = ''; ?>
                                                    <option <?php echo $selected; ?> code="<?php echo $valuecy->code; ?>" value="<?php echo $keycy; ?>">
                                                        +<?php echo $valuecy->phoneCode; ?> {{ $valuecy->countryName }}</option>
                                                <?php } ?>
                                            </select>
                                            <input class="form-control user_phone" placeholder="Phone" id="phone" type="phone" name="phone" value="{{ old('phone') }}" required autocomplete="phone" autofocus>
                                            <div id="error2" class="err"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                if ($id == '') { ?>
                                    <div class="form-group row width-50">
                                        <label class="col-3 control-label">{{ trans('lang.store') }}</label>
                                        <div class="col-7">
                                            <select class="form-control store" id="store">
                                                <option value="" disabled selected>{{ trans('lang.select_vendor') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="form-group row">
                                    <label class="col-3 control-label">{{ trans('lang.profile_pic') }}</label>
                                    <div class="col-9">
                                        <input type="file" onChange="handleFileSelectProfile(event,'vendor')">
                                        <div id="uploding_image_owner"></div>
                                        <div class="uploaded_image_owner mt-3" style="display:none;"><img id="uploaded_image_owner" src="" width="150px" height="150px;">
                                        </div>
                                        <div class="form-text text-muted">
                                            {{ trans('lang.vendor_image_help') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="form-group row width-50">
                                        <div class="form-check width-100">
                                            <input type="checkbox" id="is_active">
                                            <label class="col-3 control-label" for="is_active">{{ trans('lang.active') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary  save_from_btn"><i class="fa fa-save"></i> {{ trans('lang.save') }}</button>
            <?php if ($id != '') { ?>
                <a href="{!! route('deliveryman',$id) !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>

            <?php } else { ?>
                <a href="{!! route('deliveryman') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
            <?php } ?>

        </div>
    </div>
</div>
</div>
@endsection
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.1.1/compressor.min.js" integrity="sha512-VaRptAfSxXFAv+vx33XixtIVT9A/9unb1Q8fp63y1ljF+Sbka+eMJWoDAArdm7jOYuLQHVx5v60TQ+t3EA8weA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.26.0/moment.min.js"></script>

<script>
    
    var section_id = getCookie('section_id') || '';
    
    var database = firebase.firestore();
    var geoFirestore = new GeoFirestore(database);
    var refVendor = database.collection('vendors');
    var storageRef = firebase.storage().ref('images');
    var storage = firebase.storage();
    var photo = "";
    var profilephoto = '';
    var profileFileName = '';
    var placeholderImage = '';
    
    var createdAtman = firebase.firestore.Timestamp.fromDate(new Date());
    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function(snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })

    var vendorId = "{{ $id }}";
    var vendorData ='';

    regionScoped(refVendor.where('section_id', '==', section_id)).orderBy('title', 'asc').get().then(async function(snapshots) {
        snapshots.docs.forEach((listval) => {
            var data = listval.data();
            $('#store').append($("<option></option>")
            .attr("value", data.id)
            .attr("section_id", data.section_id)
            .attr("zoneId", data.zoneId)
            .attr("data-region", data.regionId ? data.regionId : '')
            .text(data.title));
        })
    });

    $(document).ready(async function() {

        if(vendorId){
            let vendorRef = await database.collection('vendors').doc(vendorId).get();
            vendorData = vendorRef.data();
        }

        $("#country_selector").select2({
            templateResult: formatState,
            templateSelection: formatState2,
            placeholder: "Select Country",
            allowClear: true
        });

        // --- ADD THIS BLOCK TO SET DEFAULT COUNTRY CODE ---
        var globalSettingsRef = database.collection('settings').doc('globalSettings');
        globalSettingsRef.get().then(async function(snapshot) {
            var globalSettings = snapshot.data();
            if (globalSettings && globalSettings.defaultCountryCode) {
                var defaultPhoneCode = globalSettings.defaultCountryCode.replace('+', '').trim();

                // Find the option with matching phoneCode
                var $option = $("#country_selector option").filter(function() {
                    return $(this).val() === defaultPhoneCode;
                });

                if ($option.length > 0) {
                    $("#country_selector").val(defaultPhoneCode).trigger('change');
                } else {
                    console.warn("Default country code not found in list:", defaultPhoneCode);
                }
            }
        }).catch(function(error) {
            console.error("Error fetching global settings: ", error);
        });
        // --- END OF DEFAULT COUNTRY LOGIC ---
    });

    $(".save_from_btn").click(async function() {

        var userFirstName = $(".user_first_name").val();
        var userLastName = $(".user_last_name").val();
        var email = $(".user_email").val();
        var password = $(".password").val();
        var countryCode = '+' + jQuery("#country_selector").val();
        var userPhone = $(".user_phone").val();
        var isActive = $("#is_active").is(':checked') ? true : false;
        var zoneId = vendorId ? vendorData.zoneId : $('#store option:selected').attr('zoneid');
        var vendorID = vendorId ? vendorId : $('#store option:selected').val();
        var storeSection = vendorId ? vendorData.section_id : $('#store option:selected').attr('section_id');
        /* A deliveryman belongs to a store, so they inherit that store's
         * region rather than being placed independently. */
        var storeRegion = vendorId ? vendorData.regionId : $('#store option:selected').data('region');
        var driverRegionId = storeRegion ? storeRegion : getActiveRegionId();
                
        if (userFirstName == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{ trans('lang.user_first_name_help') }}</p>");
            window.scrollTo(0, 0);
        } else if (email == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{ trans('lang.user_email_help') }}</p>");
            window.scrollTo(0, 0);
        } else if (password == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{ trans('lang.user_password_help') }}</p>");
            window.scrollTo(0, 0);
        } else if (userPhone == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{ trans('lang.user_phone_help') }}</p>");
            window.scrollTo(0, 0);
        } else if (jQuery("#country_selector").val() == '' || jQuery("#country_selector").val() == null) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{ trans('lang.select_country_code') }}</p>");
            window.scrollTo(0, 0);
        } else {
            
            jQuery("#data-table_processing").show();
            var id = database.collection('tmp').doc().id;
            await storeImageData().then(async (IMG) => {

                firebase.auth().createUserWithEmailAndPassword(email, password)
                    .then(async function(firebaseUser) {
                        user_id = firebaseUser.user.uid;
                        database.collection('users').doc(user_id).set({
                            'firstName': userFirstName,
                            'lastName': userLastName,
                            'email': email,
                            'countryCode': countryCode,
                            'phoneNumber': userPhone,
                            'profilePictureURL': IMG && IMG.profileImage ? IMG.profileImage : "",
                            'role': 'driver',
                            'id': user_id,
                            'createdAt': createdAtman,
                            'provider': "email",
                            'appIdentifier': "web",
                            'vendorID': vendorID,
                            'active': isActive,
                            'isDocumentVerify': true,
                            'zoneId': zoneId,
                            'regionId': driverRegionId,
                            'isActive': false,
                            'sectionId': storeSection,
                            'sectionIds': [storeSection]
                        }).then(function(result) {
                            if (vendorId) {
                                window.location.href = "{{ route('restaurants.deliveryman', '') }}/" + "{{ $id }}";
                            } else {
                                window.location.href = '{{ route('deliveryman') }}';
                            }
                        });
                    }).catch(err => {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + err + "</p>");
                        window.scrollTo(0, 0);
                    });
            }).catch(function(error) {
                jQuery("#data-table_processing").hide();
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>" + error + "</p>");
                window.scrollTo(0, 0);
            });
        }
    })
    async function storeImageData() {
        var newPhoto = [];
        newPhoto['profileImage'] = profilephoto;
        try {
            if (profilephoto != '' && profilephoto != null) {
                profilephoto = profilephoto.replace(/^data:image\/[a-z]+;base64,/, "")
                var uploadTask = await storageRef.child(profileFileName).putString(profilephoto, 'base64', {
                    contentType: 'image/jpg'
                });
                var downloadURL = await uploadTask.ref.getDownloadURL();
                newPhoto['profileImage'] = downloadURL;
                profilephoto = downloadURL;
            }

        } catch (error) {
            console.log("ERR ===", error);
        }
        return newPhoto;
    }

    function handleFileSelectProfile(evt) {
        var f = evt.target.files[0];
        var reader = new FileReader();
        new Compressor(f, {
            quality: <?php echo env('IMAGE_COMPRESSOR_QUALITY', 0.8); ?>,
            success(result) {
                f = result;
                reader.onload = (function(theFile) {
                    return function(e) {
                        var filePayload = e.target.result;
                        var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                        var val = f.name;
                        var ext = val.split('.')[1];
                        var docName = val.split('fakepath')[1];
                        var filename = (f.name).replace(/C:\\fakepath\\/i, '');
                        var timestamp = Number(new Date());
                        var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                        profilephoto = filePayload;
                        profileFileName = filename;
                        $("#uploaded_image_owner").attr('src', profilephoto);
                        $(".uploaded_image_owner").show();
                    };
                })(f);
                reader.readAsDataURL(f);
            },
            error(err) {
                console.log(err.message);
            },
        });
    }


    function formatState(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/'); ?>/scss/icons/flag-icon-css/flags";
        var $state = $(
            '<span><img src="' + baseUrl + '/' + newcountriesjs[state.element.value].toLowerCase() + '.svg" class="img-flag" /> ' + state.text + '</span>'
        );
        return $state;
    }

    function formatState2(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/'); ?>/scss/icons/flag-icon-css/flags"
        var $state = $(
            '<span><img class="img-flag" /> <span></span></span>'
        );
        $state.find("span").text(state.text);
        $state.find("img").attr("src", baseUrl + "/" + newcountriesjs[state.element.value].toLowerCase() + ".svg");
        return $state;
    }

    var newcountriesjs = '<?php echo json_encode($newcountriesjs); ?>';
    var newcountriesjs = JSON.parse(newcountriesjs);
</script>
@endsection