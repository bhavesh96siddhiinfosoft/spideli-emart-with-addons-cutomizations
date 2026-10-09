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
    var currentCarrierOwnerId = '';
    var carrierListEnabled = {{ \App\Http\Controllers\CarrierController::LIST_ENABLED ? 'true' : 'false' }};

    function renderOwnerDetails(owner, ownerId) {
        if (!owner) {
            $('#owner_details_section').hide();
            return;
        }
        var fullName = ((owner.firstName || '') + ' ' + (owner.lastName || '')).trim();
        if (fullName === '') {
            fullName = owner.name || owner.companyName || '';
        }
        $('#owner_name').val(fullName);
        $('#owner_company_name').val(owner.companyName || '');

        var phone = owner.phoneNumber || owner.phone || '';
        if (phone && owner.countryCode && !phone.startsWith('+')) {
            phone = owner.countryCode + ' ' + phone;
        }
        $('#owner_phone').val(phone);
        $('#owner_email').val(owner.email || '');
        $('#owner_commercial_register').val(owner.commercialRegister || '');
        $('#owner_operating_licence').val(owner.operatingLicence || '');
        $('#owner_unique_id').val(owner.uniqueIdNumber || '');

        if (ownerId) {
            var ownerViewUrl = '{{ route("owners.view", ":id") }}'.replace(':id', encodeURIComponent(ownerId));
            $('#owner_view_link').attr('href', ownerViewUrl).show();
        } else {
            $('#owner_view_link').hide();
        }

        $('#owner_details_section').show();
    }

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

    /* ---- Report 03 point 44: a price per region -------------------------
     *
     * *"Allow carrier manager to set pricing according to each zone they
     * serve, because they can serve multiple zones."*
     *
     * The Pricing section is now drawn from "Regions Served": one block of
     * four charges per region, created when a region is chosen and removed
     * when it is taken away - which is the client's second requirement.
     *
     * CHOOSING NO REGION STILL MEANS "EVERYWHERE", so that case keeps a
     * single block. Every carrier created before today is in exactly that
     * state, and must go on working untouched.
     * ------------------------------------------------------------------ */

    /* Prices typed but not yet saved, kept here so that re-drawing the blocks
     * does not wipe what somebody is in the middle of entering.
     * Keyed by region id, or by ALL_REGIONS when none is chosen. */
    var CARRIER_PRICING_ALL = '__all__';
    var carrierPricingDraft = {};

    var CARRIER_CHARGE_FIELDS = [
        { key: 'baseCharge',    label: "{{ trans('lang.carrier_base_charge') }}",
          help: "{{ trans('lang.carrier_base_charge_help') }}" },
        { key: 'perKmCharge',   label: "{{ trans('lang.carrier_per_km_charge') }}", help: '' },
        { key: 'perKgCharge',   label: "{{ trans('lang.carrier_per_kg_charge') }}",
          help: "{{ trans('lang.carrier_per_kg_charge_help') }}" },
        { key: 'minimumCharge', label: "{{ trans('lang.carrier_minimum_charge') }}", help: '' }
    ];

    function carrierPricingEscape(value) {
        return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
    }

    /* Reads whatever is on screen back into the draft, so nothing is lost when
     * the blocks are rebuilt. */
    function captureCarrierPricing() {
        $('#carrier_pricing_blocks .carrier-charge').each(function () {
            var scope = $(this).attr('data-scope');
            var field = $(this).attr('data-field');

            if (!carrierPricingDraft[scope]) {
                carrierPricingDraft[scope] = {};
            }

            carrierPricingDraft[scope][field] = $(this).val();
        });
    }

    function carrierPricingBlock(scope, heading) {
        var saved = carrierPricingDraft[scope] || {};
        var html = '<div class="card border mb-3" data-pricing-scope="' + carrierPricingEscape(scope) + '">';

        html += '<div class="card-header py-2"><strong>' + carrierPricingEscape(heading) + '</strong></div>';
        html += '<div class="card-body pb-1">';

        CARRIER_CHARGE_FIELDS.forEach(function (field) {
            var value = saved[field.key];
            value = (value === null || value === undefined) ? '' : value;

            html += '<div class="form-group row width-50">';
            html += '<label class="col-3 control-label">' + carrierPricingEscape(field.label) + '</label>';
            html += '<div class="col-7">';
            html += '<input type="number" step="any" class="form-control carrier-charge"' +
                    ' data-scope="' + carrierPricingEscape(scope) + '"' +
                    ' data-field="' + carrierPricingEscape(field.key) + '"' +
                    ' value="' + carrierPricingEscape(value) + '">';

            if (field.help) {
                html += '<div class="form-text text-muted">' + carrierPricingEscape(field.help) + '</div>';
            }

            html += '</div></div>';
        });

        return html + '</div></div>';
    }

    /* Draws one block per selected region, or a single block when none is
     * selected. Safe to call as often as you like. */
    function renderCarrierPricing() {
        if (!$('#carrier_pricing_blocks').length) {
            return;
        }

        captureCarrierPricing();

        var selected = $('#region_ids').val() || [];
        var html = '';

        if (selected.length === 0) {
            $('#carrier_pricing_hint').text("{{ trans('lang.carrier_pricing_all_regions_hint') }}");
            html = carrierPricingBlock(CARRIER_PRICING_ALL,
                "{{ trans('lang.carrier_pricing_all_regions') }}");
        } else {
            $('#carrier_pricing_hint').text("{{ trans('lang.carrier_pricing_per_region_hint') }}");

            selected.forEach(function (regionId) {
                /* The name from the dropdown, so it reads as the admin chose
                 * it rather than as an id. */
                var name = $('#region_ids option[value="' + regionId + '"]').text() || regionId;
                html += carrierPricingBlock(regionId, name);
            });
        }

        $('#carrier_pricing_blocks').html(html);

        /* A region taken away keeps nothing behind: its prices go with it,
         * which is what the client asked for. Done AFTER drawing so a region
         * put back within the same session is not punished for it. */
        var keep = {};

        if (selected.length === 0) {
            keep[CARRIER_PRICING_ALL] = carrierPricingDraft[CARRIER_PRICING_ALL] || {};
        } else {
            selected.forEach(function (regionId) {
                keep[regionId] = carrierPricingDraft[regionId] || {};
            });
        }

        carrierPricingDraft = keep;
    }

    /* What gets saved. Returns both shapes:
     *   regionPricing  - the map, the source of truth from today
     *   the four flat fields - kept so anything already reading them still
     *                          gets a number. They carry the "everywhere"
     *                          price, or the FIRST region's when regions are
     *                          set. Documented for the app team. */
    function carrierPricingForSave() {
        captureCarrierPricing();

        var selected = $('#region_ids').val() || [];
        var out = { regionPricing: {}, flat: {} };

        function numbers(scope) {
            var saved = carrierPricingDraft[scope] || {};
            var row = {};

            CARRIER_CHARGE_FIELDS.forEach(function (field) {
                var raw = saved[field.key];

                if (raw === '' || raw === null || raw === undefined) {
                    row[field.key] = null;
                    return;
                }

                var value = Number(raw);
                row[field.key] = isFinite(value) ? value : null;
            });

            return row;
        }

        if (selected.length === 0) {
            out.flat = numbers(CARRIER_PRICING_ALL);
            return out;
        }

        selected.forEach(function (regionId) {
            out.regionPricing[regionId] = numbers(regionId);
        });

        out.flat = out.regionPricing[selected[0]];

        return out;
    }

    /* Puts a saved carrier back on screen. Older carriers have no
     * regionPricing at all - their flat fields become the price for every
     * region they serve, so nothing reads as blank after an upgrade. */
    function loadCarrierPricing(carrier) {
        carrierPricingDraft = {};

        var flat = {
            baseCharge: carrier ? carrier.baseCharge : '',
            perKmCharge: carrier ? carrier.perKmCharge : '',
            perKgCharge: carrier ? carrier.perKgCharge : '',
            minimumCharge: carrier ? carrier.minimumCharge : ''
        };

        carrierPricingDraft[CARRIER_PRICING_ALL] = flat;

        var saved = (carrier && carrier.regionPricing) ? carrier.regionPricing : null;
        var regionIds = (carrier && carrier.regionIds) ? carrier.regionIds : [];

        regionIds.forEach(function (regionId) {
            if (saved && saved[regionId]) {
                carrierPricingDraft[regionId] = saved[regionId];
            } else {
                /* An existing carrier priced before today: carry its single
                 * price into each region rather than show empty boxes. */
                carrierPricingDraft[regionId] = {
                    baseCharge: flat.baseCharge,
                    perKmCharge: flat.perKmCharge,
                    perKgCharge: flat.perKgCharge,
                    minimumCharge: flat.minimumCharge
                };
            }
        });
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
            if (carrier.ownerId) {
                currentCarrierOwnerId = carrier.ownerId;
            }
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

        /* 02#44: the price blocks follow the regions. chosen fires the native
         * change on the underlying select, so one handler covers both the
         * dropdown and anything that sets the value in code. */
        loadCarrierPricing(carrier);
        renderCarrierPricing();
        $regions.off('change.carrierPricing').on('change.carrierPricing', renderCarrierPricing);

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

    /* `linkCompanyId` is set only when the carrier is being created from a
     * registered company - bug report 02 point 19. */
    async function saveCarrier(id, isEdit, linkCompanyId) {
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

        /* 02#44: read the per-region blocks once, before the payload. */
        var carrierPricing = carrierPricingForSave();

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
            /* 02#44: the map is the source of truth; the four flat fields
             * are kept so anything already reading them still gets a number.
             * See carrierPricingForSave(). */
            'regionPricing': carrierPricing.regionPricing,
            'baseCharge': carrierPricing.flat.baseCharge,
            'perKmCharge': carrierPricing.flat.perKmCharge,
            'perKgCharge': carrierPricing.flat.perKgCharge,
            'minimumCharge': carrierPricing.flat.minimumCharge,
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

            /* THE TWO POINT AT EACH OTHER.
             *
             * `ownerId` on the carrier names the company that registered; the
             * company's own user record gets `carrierId` back. Either side can
             * then be found from the other, which is what
             * APP-SPEC-ADMIN.md section 19 has been asking for since September -
             * until a carrier is linked to its drivers, its orders are offered
             * to every driver.
             *
             * A company's own drivers already carry `ownerId` pointing at the
             * company, so once this link exists the carrier's drivers are
             * reachable in one step. */
            if (linkCompanyId) {
                payload.ownerId = linkCompanyId;
            }

            await database.collection('delivery_carriers').doc(id).set(payload);

            if (linkCompanyId) {
                try {
                    await database.collection('users').doc(linkCompanyId).update({ 'carrierId': id });
                } catch (err) {
                    /* The carrier exists and is the thing that was asked for.
                     * A failed back-link is worth reporting, not worth undoing
                     * the carrier for - the list shows unlinked companies, so
                     * this one simply appears again. */
                    console.error('carrier created, but the company could not be linked back to it', err);
                }
            }
        }

        var returnToOwners = (linkCompanyId || currentCarrierOwnerId || !carrierListEnabled);
        if (returnToOwners) {
            var ownersBackUrl = '{{ route("owners") }}';
            var backSource = (typeof backParam !== 'undefined') ? backParam : '';
            if (backSource === 'approved') {
                ownersBackUrl = '{{ route("owners.approved") }}';
            } else if (backSource === 'pending') {
                ownersBackUrl = '{{ route("owners.pending") }}';
            } else if (backSource === 'owner_view') {
                var oId = linkCompanyId || currentCarrierOwnerId;
                if (oId) {
                    ownersBackUrl = '{{ route("owners.view", ":id") }}'.replace(':id', oId) + '#carrier';
                }
            }
            window.location.href = ownersBackUrl;
        } else {
            window.location.href = '{{ route("carriers") }}';
        }
    }
</script>
