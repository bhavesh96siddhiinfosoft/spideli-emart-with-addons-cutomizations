@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.parcel_weight')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>

                <li class="breadcrumb-item active">{{trans('lang.parcel_weight')}}</li>

            </ol>

        </div>

        <div>

        </div>

    </div>


    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-body">
                        <div class="resttab-sec">

                            <div class="error_top"></div>
                            <div class="row vendor_payout_create">
                                <div class="vendor_payout_create-inner">
                                    <fieldset>
                                        <legend>{{trans('lang.parcel_weight')}}</legend>

                                        <div class="form-group row">

                                            <div class="special_offer_div">

                                                <div class="form-group row">
                                                    <label class="col-12 control-label"
                                                           style="color:red;font-size:15px;">{{trans('lang.edit_button_save_note')}} </label>
                                                </div>
                                                <?php if (in_array('parcel.weight.create', json_decode(@session('user_permissions'),true))) { ?>
                                                    <div class="form-group row">

                                                        <div class="col-12">
                                                            <button type="button"
                                                                    class="btn btn-primary add_more_sunday"
                                                                    onclick="addMoreButton()">{{trans('lang.add_more')}}
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                <div class="form-group row">
                                                    <div class="col-12 form-text text-muted">{{ trans('lang.parcel_weight_range_help') }}</div>
                                                </div>
                                                <div id="parcel_weight_suggested_note" class="alert alert-warning" style="display:none;">
                                                    {{ trans('lang.parcel_weight_range_suggested') }}
                                                </div>
                                                <div class="parcel_weight" style="display:none">
                                                    <table class="booking-table" id="parcel_weight_table">
                                                        <tr>
                                                            <th style="width:30%"><label class="col-12 control-label">{{trans('lang.title')}}</label>
                                                            </th>
                                                            {{-- Point 41: a real weight range per band, so a
                                                                 chargeable weight can be matched to a band.
                                                                 Saved as minKg / maxKg - see
                                                                 docs/app-spec-parcel-pricing.md. --}}
                                                            <th style="width:15%"><label class="col-12 control-label">{{trans('lang.parcel_weight_min_kg')}}</label>
                                                            </th>
                                                            <th style="width:15%"><label class="col-12 control-label">{{trans('lang.parcel_weight_max_kg')}}</label>
                                                            </th>
                                                            <th style="width:25%"><label class="col-12 control-label">{{trans('lang.delivery_charge')}}</label>
                                                            </th>

                                                            <?php if (in_array('parcel.weight.edit', json_decode(@session('user_permissions'),true)) || in_array('parcel.weight.delete', json_decode(@session('user_permissions'),true))) { ?>

                                                                <th style="width:20%"><label
                                                                            class="col-3 control-label">{{trans('lang.actions')}}</label>
                                                                </th>
                                                            <?php } ?>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                    </fieldset>

                                    <div class="form-group col-12 text-center btm-btn">
                                        <button type="button" class="btn btn-primary  save-form-btn"><i
                                                    class="fa fa-save"></i> {{trans('lang.save')}}
                                        </button>
                                        <a href="{!! route('parcel_weight') !!}" class="btn btn-default"><i
                                                    class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @endsection

    @section('scripts')

    <script type="text/javascript">

        var database = firebase.firestore();
        var arrayParcelWeight = [];

        var parcel_weight = database.collection('parcel_weight');
        var editCount = 0;

        /* ---- Point 41: a weight range for each band -------------------------
         *
         * The bands were TEXT ONLY ("0.1-1KG", "5 KG", "+15 KG"), so nothing
         * could match a parcel's chargeable weight to one reliably. Each band
         * now carries minKg and maxKg (inclusive; maxKg empty = no upper
         * limit). The app reads exactly these keys.
         *
         * Bands saved before this have no range. They are shown with a range
         * SUGGESTED from their titles, marked as such, and nothing is written
         * until the admin presses Save. */

        function parcelKgOrNull(value) {
            if (value === null || value === undefined) {
                return null;
            }
            var raw = String(value).trim().replace(',', '.');
            if (raw === '') {
                return null;
            }
            var number = Number(raw);
            return isFinite(number) ? number : NaN;
        }

        function parcelRound2(value) {
            return Math.round(value * 100) / 100;
        }

        /* Reads a title the way people write them: "0.1-1KG", "1.1-2 KG",
         * "5 KG", "+15 KG". Returns {lo, hi, open} or null. */
        function parcelParseTitle(title) {
            var text = String(title || '').replace(/,/g, '.');
            var range = text.match(/(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)/);
            if (range) {
                return {lo: Number(range[1]), hi: Number(range[2]), open: false};
            }
            var plus = text.match(/^\s*\+\s*(\d+(?:\.\d+)?)/);
            if (plus) {
                return {lo: Number(plus[1]), hi: null, open: true};
            }
            var single = text.match(/(\d+(?:\.\d+)?)/);
            if (single) {
                return {lo: null, hi: Number(single[1]), open: false};
            }
            return null;
        }

        /* Suggested ranges for bands that have none, laid end to end with no
         * gaps: a band without an explicit start begins just after the band
         * below it. Returns {id: {minKg, maxKg}} for the bands it could read. */
        function parcelSuggestRanges(bands) {
            var parsed = bands.map(function (band) {
                return {id: band.id, p: parcelParseTitle(band.title)};
            }).filter(function (row) {
                return row.p !== null;
            });

            parsed.sort(function (a, b) {
                var aTop = a.p.open ? Infinity : a.p.hi;
                var bTop = b.p.open ? Infinity : b.p.hi;
                return aTop - bTop;
            });

            var out = {};
            var previousMax = null;
            parsed.forEach(function (row) {
                var min;
                if (row.p.open) {
                    /* "+15 KG" after a band ending at 7: starting at 15 would
                     * leave 7-15 kg in no band at all. Start just after the
                     * band below; the admin can still change it. */
                    min = previousMax !== null ? parcelRound2(previousMax + 0.01) : row.p.lo;
                } else if (row.p.lo !== null) {
                    min = row.p.lo;
                } else {
                    min = previousMax !== null ? parcelRound2(previousMax + 0.01) : 0;
                }
                out[row.id] = {minKg: min, maxKg: row.p.open ? null : row.p.hi};
                if (!row.p.open) {
                    previousMax = row.p.hi;
                }
            });
            return out;
        }

        /* Every band currently on screen, read from the inputs - so what is
         * saved is what the admin sees. `key` is the input suffix. */
        function parcelBandsOnScreen() {
            var bands = [];
            $('#parcel_weight_table tr[data-band-key]').each(function () {
                var key = $(this).attr('data-band-key');
                bands.push({
                    key: key,
                    id: $(this).attr('data-band-id') || '',
                    title: $('#title_' + key).val(),
                    delivery_charge: $('#price_' + key).val(),
                    minKg: parcelKgOrNull($('#min_' + key).val()),
                    maxKg: parcelKgOrNull($('#max_' + key).val())
                });
            });
            return bands;
        }

        /* The message for the first problem found, or ''. Checks the ranges
         * only - the title and charge keep their own existing checks. */
        function parcelRangeProblem(bands) {
            var ranged = [];

            for (var i = 0; i < bands.length; i++) {
                var band = bands[i];
                if (isNaN(band.minKg) || isNaN(band.maxKg) ||
                    (band.minKg !== null && band.minKg < 0) || (band.maxKg !== null && band.maxKg < 0)) {
                    return "{{ trans('lang.parcel_weight_range_number_error') }}".replace(':title', band.title);
                }
                if (band.minKg !== null && band.maxKg !== null && band.minKg > band.maxKg) {
                    return "{{ trans('lang.parcel_weight_range_order_error') }}".replace(':title', band.title);
                }
                if (band.minKg !== null || band.maxKg !== null) {
                    ranged.push(band);
                }
            }

            /* Overlaps make the band for a weight ambiguous. Compared pairwise
             * so an open-ended band ("no upper limit") is handled too. */
            for (var a = 0; a < ranged.length; a++) {
                for (var b = a + 1; b < ranged.length; b++) {
                    var lowA = ranged[a].minKg === null ? 0 : ranged[a].minKg;
                    var highA = ranged[a].maxKg === null ? Infinity : ranged[a].maxKg;
                    var lowB = ranged[b].minKg === null ? 0 : ranged[b].minKg;
                    var highB = ranged[b].maxKg === null ? Infinity : ranged[b].maxKg;
                    if (lowA <= highB && lowB <= highA) {
                        return "{{ trans('lang.parcel_weight_range_overlap_error') }}"
                            .replace(':first', ranged[a].title).replace(':second', ranged[b].title);
                    }
                }
            }

            return '';
        }

        function parcelShowError(message) {
            $(".error_top").show().html("").append($('<p>').text(message));
            window.scrollTo(0, 0);
        }

        function parcelKgCell(key, value, suggested) {
            var shown = (value === null || value === undefined) ? '' : value;
            return '<td style="width:15%"><input type="number" step="any" min="0" class="form-control' +
                (suggested ? ' border-warning' : '') + '" id="' + key + '" value="' + shown + '"' +
                ' onchange="replaceText(`' + key.replace(/^(min|max)_/, '') + '`)"></td>';
        }

        $(document).ready(function () {


            parcel_weight.get().then(async function (snapshots) {

                var loaded = snapshots.docs.map(function (doc) {
                    return doc.data();
                });

                /* Only bands with no range at all get a suggestion. */
                var suggestions = parcelSuggestRanges(loaded.filter(function (band) {
                    return (band.minKg === undefined || band.minKg === null) &&
                           (band.maxKg === undefined || band.maxKg === null);
                }));
                var anySuggested = false;

                loaded.forEach((parcel_weight_data) => {

                    var object = {
                        'id': parcel_weight_data.id,
                        'title': parcel_weight_data.title,
                        'delivery_charge': parcel_weight_data.delivery_charge,
                        'minKg': parcel_weight_data.minKg === undefined ? null : parcel_weight_data.minKg,
                        'maxKg': parcel_weight_data.maxKg === undefined ? null : parcel_weight_data.maxKg
                    };

                    arrayParcelWeight.push(object);

                    var suggestion = suggestions[parcel_weight_data.id];
                    var minShown = object.minKg, maxShown = object.maxKg;
                    if (suggestion) {
                        anySuggested = true;
                        minShown = suggestion.minKg;
                        maxShown = suggestion.maxKg;
                    }

                    $(".parcel_weight").show();

                    var html = '<tr data-band-key="' + parcel_weight_data.id + '" data-band-id="' + parcel_weight_data.id + '">' +
                        '<td style="width:30%"><input type="text" value="' + parcel_weight_data.title + '" class="form-control" id="title_' + parcel_weight_data.id + '" onchange="replaceText(`' + parcel_weight_data.id + '`)"></td>' +
                        parcelKgCell('min_' + parcel_weight_data.id, minShown, !!suggestion) +
                        parcelKgCell('max_' + parcel_weight_data.id, maxShown, !!suggestion) +
                        '<td style="width:25%"><input type="text" value="' + parcel_weight_data.delivery_charge + '" class="form-control" id="price_' + parcel_weight_data.id + '" onchange="replaceText(`' + parcel_weight_data.id + '`)"></td>';

                    <?php if (in_array('parcel.weight.edit', json_decode(@session('user_permissions'),true)) || in_array('parcel.weight.delete', json_decode(@session('user_permissions'),true))) { ?>

                    html += '<td class="action-btn" style="width:15%">';
                    <?php if (in_array('parcel.weight.edit', json_decode(@session('user_permissions'),true))){?>
                    html += '<span class="edit-form-btn"><button type="button" class="btn btn-primary edit_' + parcel_weight_data.id + '" onclick="editData(`' + editCount + '`,`' + parcel_weight_data.id + '`)"><i class="fa fa-edit"></i></button>&nbsp;&nbsp</span>';
                    <?php }
                    if (in_array('parcel.weight.delete', json_decode(@session('user_permissions'),true))){
                    ?>
                    html += '<span class="delete-btn"><button type="button" class="btn btn-primary delete_' + parcel_weight_data.id + '" onclick="deleteData(`' + parcel_weight_data.id + '`)"><i class="fa fa-trash"></i></button></span>';
                    <?php }?>
                    html += '</td>';
                    <?php }?>
                    html += '</tr>';

                    $('#parcel_weight_table tr:last').after(html);
                    editCount++;
                    $('#data-table_processing').hide();
                });

                $('#parcel_weight_suggested_note').toggle(anySuggested);
            });
        });


        function replaceText(id) {
            $('.edit_' + id).html("<i class='fa fa-save'></i>");
        }


        var countAddButton = 1;

        function addMoreButton() {
            count = countAddButton;
            $(".parcel_weight").show();

            $('#parcel_weight_table tr:last').after('<tr data-band-key="new' + count + '">' +
                '<td><input type="text" class="form-control" id="title_new' + count + '"></td>' +
                parcelKgCell('min_new' + count, null, false) +
                parcelKgCell('max_new' + count, null, false) +
                '<td><input type="text" class="form-control" id="price_new' + count + '"></td>' +
                '<td class="action-btn">' +
                '<button type="button" class="btn btn-primary save_new' + count + '" onclick="saveData(' + count + ')">Save</button>' +
                '</td></tr>');
            countAddButton++;
        }

        function saveData(count) {
            var key = 'new' + count;
            var title = $("#title_" + key).val();
            var price = $("#price_" + key).val();

            if (title == "") {
                parcelShowError("{{trans('lang.parcel_title_error')}}");
            } else if (price == "") {
                parcelShowError("{{trans('lang.parcel_price_error')}}");
            } else {
                var problem = parcelRangeProblem(parcelBandsOnScreen());
                if (problem !== '') {
                    parcelShowError(problem);
                    return;
                }

                $(".error_top").hide();
                $(".error_top").html("");
                var alovelaceDocumentRef = database.collection('vendor_orders').doc();

                if (alovelaceDocumentRef.id) {
                    id_rendom = alovelaceDocumentRef.id;
                }
                var object = {
                    'id': id_rendom,
                    'title': title,
                    'delivery_charge': price,
                    'minKg': parcelKgOrNull($('#min_' + key).val()),
                    'maxKg': parcelKgOrNull($('#max_' + key).val())
                };

                arrayParcelWeight.push(object);
                /* The row now belongs to this new band, so Save writes it under
                   its id rather than as a second new band. */
                $('#parcel_weight_table tr[data-band-key="' + key + '"]').attr('data-band-id', id_rendom);

                $(".save_" + key).hide();
                $("#title_" + key).attr('disabled', "true");
                $("#price_" + key).attr('disabled', "true");
                $("#min_" + key).attr('disabled', "true");
                $("#max_" + key).attr('disabled', "true");

            }
        }

        function editData(count, actionId) {
            /* 02-report point 32: "We were unable to modify the setting in
             * parcel weight".
             *
             * A demo-mode guard stood here and KILLED THIS FUNCTION ON EVERY
             * CLICK. It read three identifiers that are declared nowhere in
             * this panel - `is_disable_delete`, `doNotUpdateAlert` and
             * `doNotDeleteAlert` - and called `e.preventDefault()` where there
             * is no `e` to call it on. The first line threw a ReferenceError
             * and nothing below it ever ran.
             *
             * That is why ADDING a weight worked and EDITING one silently did
             * nothing: saveData() below has no such guard.
             *
             * Removed rather than repaired. It is dead theme code for a demo
             * mode this installation does not have, and leaving a guard that
             * blocks every administrator is worse than having none. */

            var title = $("#title_" + actionId).val();
            var price = $("#price_" + actionId).val();

            if (title == "") {
                parcelShowError("{{trans('lang.parcel_title_error')}}");
            } else if (price == "") {
                parcelShowError("{{trans('lang.parcel_price_error')}}");
            } else {
                var problem = parcelRangeProblem(parcelBandsOnScreen());
                if (problem !== '') {
                    parcelShowError(problem);
                    return;
                }

                $(".error_top").hide();
                $(".error_top").html("");
                var object = {
                    'id': actionId,
                    'title': title,
                    'delivery_charge': price,
                    'minKg': parcelKgOrNull($('#min_' + actionId).val()),
                    'maxKg': parcelKgOrNull($('#max_' + actionId).val())
                };

                database.collection('parcel_weight').doc(actionId).update(object);
                arrayParcelWeight[count] = object;
                $('#min_' + actionId + ', #max_' + actionId).removeClass('border-warning');

            }

        }

        function deleteData(actionId) {
            /* 02-report point 32 - the same dead guard as editData() above,
             * which is why Delete did nothing either. */
            if (!confirm("{{ trans('lang.parcel_weight_delete_confirm') }}")) {
                return false;
            }

            database.collection('parcel_weight').doc(actionId).delete().then(function (result) {
                window.location.href = '{{ url()->current() }}';
            });
        }

        /* Save writes WHAT IS ON SCREEN for every saved band, ranges included.
         *
         * It used to write the list as it was loaded, with only three fields
         * and set() - which would have wiped minKg / maxKg - and it reloaded
         * the page as soon as the FIRST band finished, which could cut the
         * rest off. It now waits for all of them. Rows added but not yet
         * confirmed with their own Save button are left out, as before. */
        $(document).on('click', '.save-form-btn', async function () {
            var bands = parcelBandsOnScreen().filter(function (band) {
                return band.id !== '';
            });

            var problem = parcelRangeProblem(bands);
            if (problem !== '') {
                parcelShowError(problem);
                return;
            }

            for (var i = 0; i < bands.length; i++) {
                if (bands[i].title === '' || bands[i].delivery_charge === '') {
                    parcelShowError(bands[i].title === ''
                        ? "{{trans('lang.parcel_title_error')}}"
                        : "{{trans('lang.parcel_price_error')}}");
                    return;
                }
            }

            jQuery("#data-table_processing").show();
            await Promise.all(bands.map(function (band) {
                return database.collection('parcel_weight').doc(band.id).set({
                    'id': band.id,
                    'title': band.title,
                    'delivery_charge': band.delivery_charge,
                    'minKg': band.minKg,
                    'maxKg': band.maxKg
                });
            }));
            window.location.href = '{{ route("parcel_weight")}}';
        });

    </script>

    @endsection
