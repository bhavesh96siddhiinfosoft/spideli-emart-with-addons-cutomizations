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
            <h3 class="text-themecolor">{{trans('lang.createe_vendor')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('vendors') !!}">{{trans('lang.vendors')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.createe_vendor')}}</li>
            </ol>
        </div>
        </div>
        
            <div class="card-body">
                <div class="error_top"></div>
                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">
                        <fieldset>
                            <legend>{{trans('lang.admin_area')}}</legend>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.first_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_first_name" required>
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
                                    <input type="email" class="form-control user_email" required onkeypress="return chkAlphabetsLower(event,'error1')">
                                    <div id="error1" class="err"></div>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_email_help") }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.password')}}</label>
                                <div class="col-7">
                                    <input type="password" class="form-control user_password" required>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_password_help") }}
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
                                                onkeypress="return chkAlphabets2(event,'error1')">
                                            <div id="error1" class="err"></div>
                                            <div class="form-text text-muted w-50">
                                                {{ trans("lang.user_phone_help") }}
                                            </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{trans('lang.user_profile_picture')}}</label>
                                <input type="file" onChange="handleFileSelectowner(event)" class="col-7">
                                <div id="uploding_image_owner"></div>
                                <div class="uploaded_image_owner" style="display:none;"><img id="uploaded_image_owner"
                                        src="" width="150px" height="150px;"></div>
                            </div>

                        </fieldset>

                        <fieldset class="subscription-plans-wrapper d-none">
                            <legend>{{ trans('lang.subscription_details') }}</legend>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.select_subscription_plan') }}</label>
                                <div class="col-7">
                                    <select class="form-control" id="subscription_plan">
                                        <option value="" selected> {{ trans('lang.select_subscription_plan') }}</option>
                                    </select>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>{{trans('vendor')}} {{trans('lang.active_deactive')}}</legend>
                            <div class="form-group row">

                                <div class="form-group row width-50">
                                    <div class="form-check width-100">
                                        <input type="checkbox" id="is_active">
                                        <label class="col-3 control-label"
                                            for="is_active">{{trans('lang.active')}}</label>
                                    </div>
                                </div>

                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>{{trans('lang.bankdetails')}}</legend>
                            <div class="form-group row width-100" style="display: none;" id="companyDriverShowDiv">
                                <div class="col-12">
                                    <h6><a href="#">{{ trans("lang.driver_add_by_company_info") }}</a>
                                    </h6>
                                </div>
                            </div>
                            <div class="form-group row" id="companyDriverHideDiv">

                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.bank_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="bank_name" class="form-control" id="bankName">
                                    </div>
                                </div>

                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.branch_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="branch_name" class="form-control" id="branchName">
                                    </div>
                                </div>


                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.holer_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="holer_name" class="form-control" id="holderName">
                                    </div>
                                </div>

                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.account_number')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="account_number" class="form-control"
                                            id="accountNumber">
                                    </div>
                                </div>

                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.other_information')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="other_information" class="form-control"
                                            id="otherDetails">
                                    </div>
                                </div>

                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>
        

        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary  save-form-btn"><i class="fa fa-save"></i>
                {{trans('lang.save')}}
            </button>
            <a href="{!! route('vendors') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        </div>
    
</div>


@endsection

@section('scripts')

<script type="text/javascript">
    var database = firebase.firestore();
   
    var section_id = getCookie('section_id') || '';
    var vendorOwnerId = "";
    var vendorOwnerOnline = false;
    var ownerphoto = '';
    var ownerFileName = '';
    var storageRef = firebase.storage().ref('images'); 

    var createdAt = firebase.firestore.FieldValue.serverTimestamp();
    var vendor_id = database.collection("tmp").doc().id;
    var email_templates=database.collection('email_templates').where('type','==','new_vendor_signup');
    var emailTemplatesData=null;
    var adminEmail='';
    var emailSetting=database.collection('settings').doc('emailSetting');
    let businessModelData = '';

    let isAutoVerify = false;
    $(document).ready(async function() {

        let businessModelRef = await database.collection('settings').doc("vendor").get();
        businessModelData = businessModelRef.data();
        if(businessModelData.subscription_model){
            $(".subscription-plans-wrapper").removeClass('d-none');
            database.collection('subscription_plans').where('isEnable','==',true).where('sectionId','==',section_id).get().then(async function(snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data=listval.data();
                    $('#subscription_plan').append($("<option></option>")
                        .attr("value",data.id)
                        .text(data.name));
                });
            });
        }

        let documentVerify = await database.collection('settings').doc('document_verification_settings').get();
        let documentSettings = documentVerify.data();
        if(documentSettings.isStoreVerification === false){
            isAutoVerify = true;
        }
        
        jQuery("#country_selector").select2({
            templateResult: formatState,
            templateSelection: formatState2,
            placeholder: "Select Country",
            allowClear: true
        });

        // --- ADD THIS BLOCK TO SET DEFAULT COUNTRY CODE ---
        var globalSettingsRef = database.collection('settings').doc('globalSettings');
        globalSettingsRef.get().then(async function (snapshot) {
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
        }).catch(function (error) {
            console.error("Error fetching global settings: ", error);
        });
        // --- END OF DEFAULT COUNTRY LOGIC ---

        await email_templates.get().then(async function(snapshots) {
            emailTemplatesData=snapshots.docs[0].data();
        });

        await emailSetting.get().then(async function(snapshots) {
            var emailSettingData=snapshots.data();

            adminEmail=emailSettingData.userName;
        });
    });


    $(".save-form-btn").click(async function () {
        
        $(".error_top").hide();
    
        var userFirstName = $(".user_first_name").val();
        var userLastName = $(".user_last_name").val();
        var email = $(".user_email").val();
        var password = $(".user_password").val();
        var userPhone = $(".user_phone").val();
        var country_code = '+' + jQuery("#country_selector").val();
        var ccode = jQuery("#country_selector").val();

        var vendor_active = false;
        if ($("#is_active").is(':checked')) {
            vendor_active = true;
        }

        var user_name = userFirstName + " " + userLastName;
        var user_id = "<?php echo uniqid(); ?>";
        var name = userFirstName + " " + userLastName;
        var subscriptionPlanId=$('#subscription_plan').val();

        if (userFirstName == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.enter_owners_name_error')}}</p>");
            window.scrollTo(0, 0);
        }else if (userLastName == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.enter_owners_lastname_error')}}</p>");
            window.scrollTo(0, 0);
        } else if (email == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.enter_owners_email')}}</p>");
            window.scrollTo(0, 0);
        } else if (password == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.enter_owners_password_error')}}</p>");
            window.scrollTo(0, 0);
        } else if(!ccode) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.select_country_code')}}</p>");
            window.scrollTo(0,0); 
        } else if (userPhone == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.enter_owners_phone')}}</p>");
            window.scrollTo(0, 0);
        } else if (subscriptionPlanId == '' && businessModelData.subscription_model) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.select_subscription_plan')}}</p>");
            window.scrollTo(0, 0);
        } else {

            jQuery("#data-table_processing").show();
            
            if(subscriptionPlanId && subscriptionPlanId !='') {
                var subscriptionData=await getSubscriptionDetails(subscriptionPlanId);
            } else {
                var subscriptionData=null;
            }

            var bankName = $("#bankName").val();
            var branchName = $("#branchName").val();
            var holderName = $("#holderName").val();
            var accountNumber = $("#accountNumber").val();
            var otherDetails = $("#otherDetails").val();
            var userBankDetails = {
                'bankName': bankName,
                'branchName': branchName,
                'holderName': holderName,
                'accountNumber': accountNumber,
                'accountNumber': accountNumber,
                'otherDetails': otherDetails,
            };

           
            firebase.auth().createUserWithEmailAndPassword(email, password)
                .then(async function (firebaseUser) {
                    user_id = firebaseUser.user.uid;
                    await storeImageData().then(async (IMG) => {
                        database.collection('users').doc(user_id).set(withRegion({
                            'firstName': userFirstName,
                            'lastName': userLastName,
                            'email': email,
                            'phoneNumber': country_code+userPhone,
                            'profilePictureURL': IMG.ownerImage,
                            'role': 'vendor',
                            'id': user_id,
                            'active': vendor_active,
                            'vendorID': null,
                            'createdAt': createdAt,
                            'userBankDetails': userBankDetails,
                            'isDocumentVerify': false,
                            'isAutoVerify':isAutoVerify,
                            'sectionId' : section_id,
                            'subscription_plan': subscriptionData!=null? subscriptionData:null,
                            'subscriptionPlanId': subscriptionData!=null? subscriptionData.id:null,
                            'subscriptionExpiryDate': subscriptionData!=null? subscriptionData.expiryDate:null
                        })).then(async function (result) {
                            if(subscriptionData!=null) {
                                historyData={'subscriptionData': subscriptionData,'userId': user_id,'expire_date': subscriptionData.expiryDate}
                                await addSubscriptionHistory(historyData);
                            }
                            var isSendMail = await sendRegistrationEmail(user_id, name, email, userPhone);
                            if (isSendMail) {
                                window.location.href = '{{ route("vendors")}}';
                            }
                        });

                    }).catch(err => {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + err + "</p>");
                        window.scrollTo(0, 0);
                    });
                });
        }

    });

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
        var baseUrl = "<?php echo URL::to('/');?>/scss/icons/flag-icon-css/flags";
        var $state = $(
            '<span><img class="img-flag" /> <span></span></span>'
        );
        $state.find("span").text(state.text);
        $state.find("img").attr("src", baseUrl + "/" + newcountriesjs[state.element.value].toLowerCase() + ".svg");
        return $state;
    }
    var newcountriesjs = '<?php echo json_encode($newcountriesjs); ?>';
    var newcountriesjs = JSON.parse(newcountriesjs);


    async function sendRegistrationEmail(user_id, name, email, userPhone) {

        var flag = true;
        var formattedDate = new Date();
        var month = formattedDate.getMonth() + 1;
        var day = formattedDate.getDate();
        var year = formattedDate.getFullYear();

        month = month < 10 ? '0' + month : month;
        day = day < 10 ? '0' + day : day;

        formattedDate = day + '-' + month + '-' + year;

        var message = emailTemplatesData.message;
        message = message.replace(/{userid}/g, user_id);
        message = message.replace(/{username}/g, name);
        message = message.replace(/{useremail}/g, email);
        message = message.replace(/{userphone}/g, userPhone);

        message = message.replace(/{date}/g, formattedDate);

        emailTemplatesData.message = message;

        var url = "{{url('send-email')}}";

        var sendEmailStatus = await sendEmail(url, emailTemplatesData.subject, emailTemplatesData.message, [adminEmail]);

        if (sendEmailStatus) {
            flag = true;
        }

        return flag;
    }

    function chkAlphabetsLower(event, msg) {
        let char = event.which || event.keyCode;
        if (!(char >= 97 && char <= 122) && !(char >= 48 && char <= 57) && !(char == 46) && !(char == 64)){
            document.getElementById(msg).innerHTML = "Not Accept Upper case letters";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }
    
    function chkAlphabets2(event, msg) {
        if (!(event.which >= 48 && event.which <= 57)
        ) {
            document.getElementById(msg).innerHTML = "Accept only Number";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

    function handleFileSelectowner(evt) {
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
                ownerphoto = filePayload;
                ownerFileName = filename;
                $("#uploaded_image_owner").attr('src', ownerphoto);
                $(".uploaded_image_owner").show();
            };
        })(f);
        reader.readAsDataURL(f);
    }


    async function storeImageData() {
        var newPhoto = [];
        newPhoto['ownerImage'] = '';
        try {
            if (ownerphoto != '') {
                ownerphoto = ownerphoto.replace(/^data:image\/[a-z]+;base64,/, "")
                var uploadTask = await storageRef.child(ownerFileName).putString(ownerphoto, 'base64', { contentType: 'image/jpg' });
                var downloadURL = await uploadTask.ref.getDownloadURL();
                newPhoto['ownerImage'] = downloadURL;
                ownerphoto = downloadURL;
            }
        } catch (error) {
            console.log("ERR ===", error);
        }
        return newPhoto;
    }

    async function getSubscriptionDetails(subscriptionId) {
        var data='';
        await database.collection('subscription_plans').where('id','==',subscriptionId).get().then(async function(
            snapshot) {
            data=snapshot.docs[0].data();
            var currentDate=new Date();
            if(data.expiryDay!='-1') {
                currentDate.setDate(currentDate.getDate()+parseInt(data.expiryDay));
                data.expiryDate=firebase.firestore.Timestamp.fromDate(currentDate);
            } else {
                data.expiryDate=null;
            }

        })
        return data;
    }
    async function addSubscriptionHistory(historyData) {
        var id_order=database.collection('tmp').doc().id;
        var createdAt=firebase.firestore.FieldValue.serverTimestamp();

        var userId=historyData.userId;
        await database.collection('subscription_history').doc(id_order).set({
            'id': id_order,
            'user_id': historyData.userId,
            'expiry_date': historyData.expire_date,
            'createdAt': createdAt,
            'subscription_plan': historyData.subscriptionData,
            'payment_type': 'cod'
        })
    }

</script>
@endsection
