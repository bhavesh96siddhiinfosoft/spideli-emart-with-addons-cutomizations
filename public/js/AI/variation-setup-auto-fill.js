

$(document).on('click', '.variation_setup_auto_fill', function () {
    const $button = $(this);
    const lang = $button.data('lang');
    const route = $button.data('route');
    
    const name = $('#item_name').val();
    const description = $('#item_description').val();
    if (!name && !description) {
        toastr.error($button.data('error'));
        return;
    }
    
    var categories = $('#item_category option').map(function() {
        return { id: $(this).val(), name: $(this).text() };
    }).get().slice(1);
    var item_attribute = $('#item_attribute option').map(function() {
        return { id: $(this).val(), name: $(this).text() };
    }).get();

    $button.prop('disabled', true);
    $button.find('.btn-text').text('');
    const $aiText = $button.find('.ai-text-animation');
    $aiText.removeClass('d-none').addClass('ai-text-animation-visible');

    let $wrapper = $(this).closest('.variation_wrapper').find('.outline-wrapper');
    $wrapper.addClass('outline-animating');
    $wrapper.find('.bg-animate').addClass('active');
    
    $.ajax({
        url: route,
        type: 'GET',
        dataType: 'json',
        data: {
            name: name,
            description: description,
            categories: JSON.stringify(categories),
            item_attribute: JSON.stringify(item_attribute),
            lang: lang,
        },
        success: function (response) {
            render_variations_from_response(response.data, item_attribute);
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

function render_variations_from_response(data, item_attribute) {

     if (!data || Object.keys(data).length === 0) return;

    $("#item_category").val(data.category_id);
    $("#item_nonveg").prop('checked',data.is_nonveg);

    var selected_attributes = [];
    if (data.item_attribute && data.item_attribute.attributes) {
        $("#attributes_div").show();
        $.each(data.item_attribute.attributes, function(index, attribute) {
            selected_attributes.push(attribute.attribute_id);
        });
        $('#attributes').val(JSON.stringify(data.item_attribute.attributes));
        $('#variants').val(JSON.stringify(data.item_attribute.variants));
    }

    // Reset dropdown and reselect based on AI response
    $('#item_attribute option').each(function() {
        const attrId = $(this).val();
        if ($.inArray(attrId, selected_attributes) !== -1) {
            $(this).prop('selected', true);
        } else {
            $(this).prop('selected', false);
        }
    });

    $('#item_attribute').trigger("chosen:updated");
    $("#item_attribute").attr("onChange", "selectAttribute('" + btoa(JSON.stringify(data.item_attribute)) + "')");
    selectAttribute(btoa(JSON.stringify(data.item_attribute)));
}