
//Generate product title
$(document).on('click', '.auto_fill_title', function (event) {

    const $button = $(this);
    const lang = $button.data('lang');
    const route = $button.data('route');

    let $input = $('#item_name');
    let name = $input.val();
    if (!name) {
        toastr.error($button.data('error'));
        return;
    }

    $button.prop('disabled', true);
    $button.find('.btn-text').text('');
    const $aiText = $button.find('.ai-text-animation');
    $aiText.removeClass('d-none').addClass('ai-text-animation-visible');
    let $wrapper = $input.closest('.outline-wrapper');
    $wrapper.addClass('outline-animating');

     $.ajax({
        url: route,
        type: 'GET',
        dataType: 'json',
        data: {
            name: name,
            lang: lang
        },
        success: function (response) {
            if (response.data !== null){
                $input.val(response.data.title);
            }
            replaceSVGs();
        },
        error: function (xhr, status, error) {
            if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error('An unexpected error occurred.');
            }
        },
        complete: function () {
            setTimeout(function () {
                $wrapper.removeClass('outline-animating');
            }, 500);

            $button.prop('disabled', false);
            $button.find('.btn-text').text('Re-generate');
            $aiText.addClass('d-none').removeClass('ai-text-animation-visible');
        }
    });
});

//Generate product description
$(document).on('click', '.auto_fill_description', function () {
    
    const $button = $(this);
    const lang = $button.data('lang');
    const route = $button.data('route');
    
    let $input = $('#item_name');
    let name = $input.val();
    if (!name) {
        toastr.error($button.data('error'));
        return;
    }

    $button.prop('disabled', true);
    $button.find('.btn-text').text('');
    const $aiText = $button.find('.ai-text-animation');
    $aiText.removeClass('d-none').addClass('ai-text-animation-visible');
    let $wrapper = $(this).closest('.desciption-wrapper').find('.outline-wrapper');
    $wrapper.addClass('outline-animating');

    $.ajax({
        url: route,
        type: 'GET',
        dataType: 'json',
        data: {
            name: name,
            lang: lang,
        },
        success: function (response) {
            if (response.data !== null){
                $('#item_description').val(response.data.description);
            }
            replaceSVGs();
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error('An unexpected error occurred.');
            }
        },
        complete: function () {
            setTimeout(function () {
                $wrapper.removeClass('outline-animating');
            }, 500);
            $button.prop('disabled', false);
            $button.find('.btn-text').text('Re-generate');
            $aiText.addClass('d-none').removeClass('ai-text-animation-visible');
        }
    });
});

//Generate product ingredients
$(document).on('click', '.ingredients_auto_fill', function () {
    const $button = $(this);
    const lang = $button.data('lang');
    const route = $button.data('route');
    
    const name = $('#item_name').val();
    const description = $('#item_description').val();
    if (!name && !description) {
        toastr.error($button.data('error'));
        return;
    }
    
    $button.prop('disabled', true);
    $button.find('.btn-text').text('');
    const $aiText = $button.find('.ai-text-animation');
    $aiText.removeClass('d-none').addClass('ai-text-animation-visible');

    let $wrapper = $(this).closest('.ingredients-wrapper').find('.outline-wrapper');;
    $wrapper.addClass('outline-animating');
    $wrapper.find('.bg-animate').addClass('active');
    
    $.ajax({
        url: route,
        type: 'GET',
        dataType: 'json',
        data: {
            name: name,
            description: description,
            lang: lang,
        },
        success: function (response) {
            if (response.data !== null){
                $('.item_calories').val(response.data.calories);
                $('.item_grams').val(response.data.grams);
                $('.item_fats').val(response.data.fats);
                $('.item_proteins').val(response.data.proteins);
            }
            replaceSVGs();
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error('An unexpected error occurred.');
            }
        },
        complete: function () {
            setTimeout(function () {
                $wrapper.removeClass('outline-animating');
                $wrapper.find('.bg-animate').removeClass('active');
            }, 500);

            $button.prop('disabled', false);
            $button.find('.btn-text').text('Re-generate');
            $aiText.addClass('d-none').removeClass('ai-text-animation-visible');
        }
    });
});

//Generate product addons
$(document).on('click', '.addons_auto_fill', function () {
    const $button = $(this);
    const lang = $button.data('lang');
    const route = $button.data('route');
    
    const name = $('#item_name').val();
    const description = $('#item_description').val();
    if (!name && !description) {
        toastr.error($button.data('error'));
        return;
    }
    
    $button.prop('disabled', true);
    $button.find('.btn-text').text('');
    const $aiText = $button.find('.ai-text-animation');
    $aiText.removeClass('d-none').addClass('ai-text-animation-visible');

    let $wrapper = $(this).closest('.addons-wrapper').find('.outline-wrapper');;
    $wrapper.addClass('outline-animating');
    $wrapper.find('.bg-animate').addClass('active');
    
    $.ajax({
        url: route,
        type: 'GET',
        dataType: 'json',
        data: {
            name: name,
            description: description,
            lang: lang,
        },
        success: function (response) {
             if (response.data !== null && response.data.hasOwnProperty('addOnsTitle') && response.data.hasOwnProperty('addOnsPrice')){
                $(".add_ons_list").empty();
                response.data.addOnsTitle.forEach((element, index) => {
                    const price = response.data.addOnsPrice[index] ?? ''; 
                    const rowHtml = `
                        <div class="row mt-1" id="add_ones_list_iteam_${index}">
                        <div class="col-5">
                            <input class="form-control" type="text" value="${element}" disabled>
                        </div>
                        <div class="col-5">
                            <input class="form-control" type="text" value="${price}" disabled>
                        </div>
                        <div class="col-2">
                            <button class="btn" type="button" onclick="deleteAddOnesSingle(${index})">
                            <span class="mdi mdi-delete"></span>
                            </button>
                        </div>
                        </div>
                    `;
                    $(".add_ons_list").append(rowHtml);
                });
                addOnesTitle = response.data.addOnsTitle;
                addOnesPrice = response.data.addOnsPrice;
            }
            replaceSVGs();
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error('An unexpected error occurred.');
            }
        },
        complete: function () {
            setTimeout(function () {
                $wrapper.removeClass('outline-animating');
                $wrapper.find('.bg-animate').removeClass('active');
            }, 500);

            $button.prop('disabled', false);
            $button.find('.btn-text').text('Re-generate');
            $aiText.addClass('d-none').removeClass('ai-text-animation-visible');
        }
    });
});

//Generate product specification
$(document).on('click', '.specification_auto_fill', function () {
    const $button = $(this);
    const lang = $button.data('lang');
    const route = $button.data('route');
    
    const name = $('#item_name').val();
    const description = $('#item_description').val();
    if (!name && !description) {
        toastr.error($button.data('error'));
        return;
    }
    
    $button.prop('disabled', true);
    $button.find('.btn-text').text('');
    const $aiText = $button.find('.ai-text-animation');
    $aiText.removeClass('d-none').addClass('ai-text-animation-visible');

    let $wrapper = $(this).closest('.specification-wrapper').find('.outline-wrapper');;
    $wrapper.addClass('outline-animating');
    $wrapper.find('.bg-animate').addClass('active');
    
    $.ajax({
        url: route,
        type: 'GET',
        dataType: 'json',
        data: {
            name: name,
            description: description,
            lang: lang,
        },
        success: function (response) {
            if (response.data !== null && response.data.hasOwnProperty('product_specification')) {
                $(".product_specification").empty();
                product_specification = response.data.product_specification;
                for (var key in product_specification) {
                    $('#product_specification_heading').show();
                    $(".product_specification").append('<div class="row" style="margin-top:5px;" id="add_product_specification_iteam_' + key + '">' +
                        '<div class="col-5"><input class="form-control" type="text" value="' + key + '" disabled ></div>' +
                        '<div class="col-5"><input class="form-control" type="text" value="' + product_specification[key] + '" disabled ></div>' +
                        '<div class="col-2"><button class="btn" type="button" onclick=deleteProductSpecificationSingle("' + key + '")><span class="mdi mdi-delete"></span></button></div></div>');
                }
            }
            replaceSVGs();
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error('An unexpected error occurred.');
            }
        },
        complete: function () {
            setTimeout(function () {
                $wrapper.removeClass('outline-animating');
                $wrapper.find('.bg-animate').removeClass('active');
            }, 500);

            $button.prop('disabled', false);
            $button.find('.btn-text').text('Re-generate');
            $aiText.addClass('d-none').removeClass('ai-text-animation-visible');
        }
    });
});