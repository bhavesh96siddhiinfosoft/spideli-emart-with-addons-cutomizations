<script type="text/javascript">
    /* Shared load and save for the carrier form. `carrierId` is defined by the
     * host page - a fresh id on create, the existing one on edit. */

    /* Flag templates for the dialling-code picker, matching the store and
     * driver forms. carrierCountryFlags is emitted by the form partial. */
    function formatState(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/'); ?>/flags/120/";
        return $('<span><img src="' + baseUrl + '/' + carrierCountryFlags[state.element.value].toLowerCase() +
                 '.png" class="img-flag" /> ' + state.text + '</span>');
    }

    function formatState2(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/'); ?>/flags/120/";
        var $state = $('<span><img class="img-flag" /> <span></span></span>');
        $state.find("span").text(state.text);
        $state.find("img").attr("src", baseUrl + "/" + carrierCountryFlags[state.element.value].toLowerCase() + ".png");
        return $state;
    }

    /* Digits only. The equivalent helper on the store and driver forms is
     * declared per page, so it is not available here. */
    $(document).on('keypress', '#phone', function (e) {
        var chr = String.fromCharCode(e.which);
        if (e.which !== 0 && e.which !== 8 && !/[0-9]/.test(chr)) {
            e.preventDefault();
        }
    });

    /* The carrier logo. Holds a data URL while a new file is being previewed,
     * or the stored https URL when editing an existing carrier. */
    var carrierLogo = '';
    var carrierLogoFilename = '';
    var carrierStorageRef = firebase.storage().ref();

    function handleCarrierLogoSelect(evt) {
        var file = evt.target.files[0];
        if (!file) {
            return;
        }

        var reader = new FileReader();
        reader.onload = (function (theFile) {
            return function (e) {
                carrierLogo = e.target.result;
                var parts = theFile.name.split('.');
                var ext = parts.length > 1 ? parts.pop() : 'png';
                carrierLogoFilename = 'carrier_logos/' + parts.join('.') + '_' + Number(new Date()) + '.' + ext;
                renderCarrierLogo();
            };
        })(file);
        reader.readAsDataURL(file);
    }

    function renderCarrierLogo() {
        var $thumb = $('.carrier_logo_thumb');
        $thumb.empty();
        if (!carrierLogo) {
            return;
        }
        $thumb.append(
            '<span class="image-item" id="carrier_logo_item">' +
            '<span class="remove-btn" data-id="carrier-logo-remove"><i class="fa fa-remove"></i></span>' +
            '<img class="rounded" style="width:60px" src="' + carrierLogo + '" alt="logo"></span>'
        );
    }

    $(document).on('click', '[data-id="carrier-logo-remove"]', function () {
        carrierLogo = '';
        carrierLogoFilename = '';
        $('#carrierLogo').val('');
        renderCarrierLogo();
    });

    /* Uploads the logo if a new one was picked. An unchanged logo is already an
     * https URL and is returned as-is; no logo returns an empty string, since
     * the logo is optional. */
    async function uploadCarrierLogo() {
        if (!carrierLogo) {
            return '';
        }
        if (carrierLogo.indexOf('https://') === 0) {
            return carrierLogo;
        }

        var mime = carrierLogo.match(/^data:(image\/[a-zA-Z0-9+.-]+);base64,/);
        var contentType = mime ? mime[1] : 'image/jpeg';
        var allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (allowed.indexOf(contentType) === -1) {
            throw "{{ trans('lang.carrier_logo_type_error') }}";
        }

        $('#uploading_logo').text("{{ trans('lang.carrier_logo_uploading') }}");
        var base64 = carrierLogo.replace(/^data:image\/[a-zA-Z0-9+.-]+;base64,/, '');
        var task = await carrierStorageRef.child(carrierLogoFilename).putString(base64, 'base64', {contentType: contentType});
        var url = await task.ref.getDownloadURL();
        $('#uploading_logo').text('');
        return url;
    }

    /* Uploaded document URLs, keyed by the field they belong to. Held here
     * rather than on the form because a file input cannot be given a value. */
    var carrierDocs = {
        operatingLicenceFile: '',
        commercialRegisterFile: '',
        uniqueIdNumberFile: ''
    };

    /* Uploads one identification document to Firebase Storage and keeps the
     * download URL for the save. Mirrors the credentials upload on Global
     * Settings. */
    function handleCarrierDocUpload(evt, key) {
        var file = evt.target.files[0];
        if (!file) {
            return;
        }

        var reader = new FileReader();
        reader.onload = (function (theFile) {
            return function () {
                /* File.name is already the bare filename - the C:\fakepath\
                 * prefix only ever appears on an input's value, not here. */
                var parts = theFile.name.split('.');
                var ext = parts.length > 1 ? parts.pop() : '';
                var filename = 'carrier_documents/' + parts.join('.') + '_' + Number(new Date()) + (ext ? '.' + ext : '');

                var uploadTask = firebase.storage().ref('/').child(filename).put(theFile);
                uploadTask.on('state_changed', function () {
                    $('#uploading_' + key).text("{{ trans('lang.carrier_document_uploading') }}");
                }, function (error) {
                    console.error('Document upload failed', error);
                    $('#uploading_' + key).text("{{ trans('lang.carrier_document_upload_failed') }}");
                }, function () {
                    uploadTask.snapshot.ref.getDownloadURL().then(function (downloadURL) {
                        carrierDocs[key] = downloadURL;
                        $('#uploading_' + key).text("{{ trans('lang.carrier_document_uploaded') }}");
                        renderCarrierDocLink(key);
                        setTimeout(function () { $('#uploading_' + key).text(''); }, 3000);
                    });
                });
            };
        })(file);
        reader.readAsDataURL(file);
    }

    /* Shows a link to whatever is currently stored, so an admin can check the
     * document without re-uploading it. */
    function renderCarrierDocLink(key) {
        var url = carrierDocs[key];
        if (!url) {
            $('#uploaded_' + key).html('<span class="text-muted">' + "{{ trans('lang.carrier_no_document') }}" + '</span>');
            return;
        }
        $('#uploaded_' + key).html(
            '<a href="' + url + '" target="_blank" rel="noopener"><i class="mdi mdi-file-document mr-1"></i>' +
            "{{ trans('lang.carrier_view_document') }}" + '</a>'
        );
    }

    /* Fills the form. Pass null on create, the carrier document on edit. */
    async function initCarrierForm(carrier) {
        var regions = await getPublishedRegions();
        var $regions = $('#region_ids');
        $regions.empty();
        regions.forEach(function (region) {
            $regions.append($('<option></option>').attr('value', region.id).text(region.name));
        });

        if (carrier) {
            $('#name').val(carrier.name || '');
            $('#code').val(carrier.code || '');
            $('#phone').val(carrier.phone || '');
            $('#email').val(carrier.email || '');
            $('#operating_licence').val(carrier.operatingLicence || '');
            $('#commercial_register').val(carrier.commercialRegister || '');
            $('#unique_id_number').val(carrier.uniqueIdNumber || '');
            $('#is_verified').prop('checked', carrier.isVerified === true);
            carrierLogo = carrier.photo || '';
            carrierDocs.operatingLicenceFile = carrier.operatingLicenceFile || '';
            carrierDocs.commercialRegisterFile = carrier.commercialRegisterFile || '';
            carrierDocs.uniqueIdNumberFile = carrier.uniqueIdNumberFile || '';
            $('#base_charge').val(carrier.baseCharge);
            $('#per_km_charge').val(carrier.perKmCharge);
            $('#per_kg_charge').val(carrier.perKgCharge);
            $('#minimum_charge').val(carrier.minimumCharge);
            $('#min_delivery_time').val(carrier.minDeliveryTime);
            $('#max_delivery_time').val(carrier.maxDeliveryTime);
            $('#delivery_time_unit').val(carrier.deliveryTimeUnit || 'hours');
            $('#max_weight').val(carrier.maxWeight);
            $('#conditions').val(carrier.conditions || '');
            $('#publish').prop('checked', carrier.publish === true);
            $regions.val(carrier.regionIds || []);
        } else if (getActiveRegionId()) {
            /* Created while working in a region: default to that region rather
             * than silently making the carrier available everywhere. */
            $regions.val([getActiveRegionId()]);
        }

        jQuery("#country_selector").select2({
            templateResult: formatState,
            templateSelection: formatState2,
            placeholder: "{{ trans('lang.select_country_code') }}",
            allowClear: true
        });

        if (carrier && carrier.countryCode) {
            $('#country_selector').val(String(carrier.countryCode).replace('+', '').trim()).trigger('change');
        } else {
            /* Fall back to the dialling code set in Global Settings. */
            var globalSettings = await database.collection('settings').doc('globalSettings').get();
            var data = globalSettings.exists ? globalSettings.data() : null;
            if (data && data.defaultCountryCode) {
                $('#country_selector').val(String(data.defaultCountryCode).replace('+', '').trim()).trigger('change');
            }
        }

        $regions.show().chosen({"placeholder_text": "{{ trans('lang.carrier_regions') }}"});
        $regions.trigger('chosen:updated');

        renderCarrierLogo();
        renderCarrierDocLink('operatingLicenceFile');
        renderCarrierDocLink('commercialRegisterFile');
        renderCarrierDocLink('uniqueIdNumberFile');
    }

    function numberOrNull(selector) {
        var raw = $(selector).val();
        if (raw === '' || raw === null || raw === undefined) {
            return null;
        }
        return parseFloat(raw);
    }

    async function saveCarrier(id, isEdit) {
        $('.err').html('');

        var name = $('#name').val().trim();
        var code = $('#code').val().trim().toUpperCase();

        if (name === '') {
            $('#error_name').html("{{ trans('lang.carrier_name_error') }}");
            return;
        }
        if (code === '') {
            $('#error_code').html("{{ trans('lang.carrier_code_error') }}");
            return;
        }

        var minTime = numberOrNull('#min_delivery_time');
        var maxTime = numberOrNull('#max_delivery_time');
        if (minTime !== null && maxTime !== null && minTime > maxTime) {
            /* Reported next to the field it concerns, and scrolled to - this
             * section sits well below the fold, so an error shown elsewhere
             * reads as the Save button doing nothing. */
            $('#error_delivery_time').html("{{ trans('lang.carrier_delivery_time_error') }}");
            document.getElementById('error_delivery_time').scrollIntoView({behavior: 'smooth', block: 'center'});
            return;
        }

        jQuery("#overlay").show();

        /* The code identifies the carrier to the apps, so it has to stay
         * unique across the whole collection. */
        var duplicate = await database.collection('delivery_carriers').where('code', '==', code).get();
        var clash = duplicate.docs.some(function (doc) {
            return !isEdit || doc.id !== id;
        });
        if (clash) {
            jQuery("#overlay").hide();
            $('#error_code').html("{{ trans('lang.carrier_code_duplicate_error') }}");
            return;
        }

        var logoUrl = '';
        try {
            logoUrl = await uploadCarrierLogo();
        } catch (err) {
            jQuery("#overlay").hide();
            $('.error_top').show().html('<p>' + err + '</p>');
            window.scrollTo(0, 0);
            return;
        }

        var payload = {
            'photo': logoUrl,
            'name': name,
            'code': code,
            /* Held apart rather than concatenated, so the edit screen can
             * preselect the dialling code without parsing it back out. */
            'countryCode': $('#country_selector').val() ? '+' + $('#country_selector').val() : '',
            'phone': $('#phone').val().trim(),
            'email': $('#email').val().trim(),
            'regionIds': $('#region_ids').val() || [],
            'operatingLicence': $('#operating_licence').val().trim(),
            'commercialRegister': $('#commercial_register').val().trim(),
            'uniqueIdNumber': $('#unique_id_number').val().trim(),
            'isVerified': $('#is_verified').is(':checked'),
            'operatingLicenceFile': carrierDocs.operatingLicenceFile,
            'commercialRegisterFile': carrierDocs.commercialRegisterFile,
            'uniqueIdNumberFile': carrierDocs.uniqueIdNumberFile,
            'baseCharge': numberOrNull('#base_charge'),
            'perKmCharge': numberOrNull('#per_km_charge'),
            'perKgCharge': numberOrNull('#per_kg_charge'),
            'minimumCharge': numberOrNull('#minimum_charge'),
            'minDeliveryTime': minTime,
            'maxDeliveryTime': maxTime,
            'deliveryTimeUnit': $('#delivery_time_unit').val(),
            'maxWeight': numberOrNull('#max_weight'),
            'conditions': $('#conditions').val().trim(),
            'publish': $('#publish').is(':checked')
        };

        if (isEdit) {
            payload.updatedAt = firebase.firestore.FieldValue.serverTimestamp();
            await database.collection('delivery_carriers').doc(id).update(payload);
        } else {
            payload.id = id;
            payload.createdAt = firebase.firestore.FieldValue.serverTimestamp();
            await database.collection('delivery_carriers').doc(id).set(payload);
        }

        window.location.href = '{{ route("carriers") }}';
    }
</script>
