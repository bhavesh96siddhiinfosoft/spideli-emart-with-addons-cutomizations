document.addEventListener('DOMContentLoaded', function () {
    
    const modalTitle = document.getElementById('modalTitle');
    const mainContent = document.getElementById('mainAiContent');
    const uploadContent = document.getElementById('uploadImageContent');
    const titleContent = document.getElementById('giveTitleContent');
    const imageUpload = document.getElementById('aiImageUpload');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    function showMainContent() {
        document.querySelectorAll('.ai-modal-content').forEach(content => {
            content.style.display = 'none';
        });
        mainContent.style.display = 'block';
        modalTitle.textContent = 'AI Assistant';
    }

    $('#aiAssistantModal').on('show.bs.modal', function () {
        showMainContent();
    });

    document.querySelectorAll('.ai-action-btn').forEach(button => {
        button.addEventListener('click', function () {
            const action = this.getAttribute('data-action');

            document.querySelectorAll('.ai-modal-content').forEach(content => {
                content.style.display = 'none';
            });

            if (action === 'upload') {
                modalTitle.textContent = 'Upload Image for Analysis';
                uploadContent.style.display = 'block';
            } else if (action === 'title') {
                modalTitle.textContent = 'Generate Product Title';
                titleContent.style.display = 'block';
            }
        });
    });


    imageUpload.addEventListener('change', function (e) {
        $('#chooseImageBtn').find('.text-box').addClass('d-none');
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('removeImageBtn').addEventListener('click', function () {
        imageUpload.value = '';
        imagePreview.style.display = 'none';
        $('#chooseImageBtn').find('.text-box').removeClass('d-none');
    });

    $('#generateTitleBtn').on('click', function () {
        const $button = $(this);
        const keywords = $('#productKeywords').val();
        const route = $button.data('route');
        if (!keywords.trim()) {
            toastr.error('Please enter some keywords.');
            return;
        }

        const $spinner = $button.find('.ai-loader-animation');
        const $titlesList = $('#titlesList');

        $spinner.removeClass('d-none');
        $button.prop('disabled', true);
        $('.giveTitleContent_text').addClass('d-none');
        $('#generatedTitles').show();
        $('.show_generating_text').removeClass('d-none');

        $.ajax({
            url: route,
            method: 'POST',
            data: {
                keywords: keywords,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (response) {
                $titlesList.empty();


                if (!response.data.titles || response.data.titles.length === 0) {
                    $titlesList.html('<div class="text-center py-3">No titles generated.</div>');
                    return;
                }

                response.data.titles.forEach(function (title) {
                    const $item = $(`
                    <div class="list-group-item list-group-item-action title-option">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span class="overflow-wrap-anywhere">${title}</span>
                            <button class="btn btn-outline-primary px-4 py-1 use-title-btn" data-title="${title}">
                                <i class="fi fi-rr-checkbox"></i>Use
                            </button>
                        </div>
                    </div>
                `);
                $titlesList.append($item);
            });

                $titlesList.before($('.titlesList_title').removeClass('d-none'));
                $('#generatedTitles').show();

                $titleActionButton = $('#title-' + 'en' + '-action-btn');
                $('.use-title-btn').off('click').on('click', function (e) {
                    e.preventDefault();

                    const title = $(this).data('title');
                    const $productNameInput = $('#item_name');

                    if ($productNameInput.length) {
                        $productNameInput.val(title);
                        $productNameInput.trigger("focus");
                        $productNameInput[0].scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        $titleActionButton.find('.btn-text').text('Re-generate');
                    }
                });

                replaceSVGs();

            },
            error: function (xhr, status, error) {
                console.error(error);
                toastr.error('Failed to generate titles. Please try again.');
                $titlesList.empty();
            },
            complete: function () {

                $spinner.addClass('d-none');
                $button.prop('disabled', false);
                $('.show_generating_text').addClass('d-none');
            }
        });
    });
});



$(document).on('click', '#analyzeImageBtn', function () {
    const $button = $(this);
    const $imageRemoveButton = $("#removeImageBtn")
    const $chooseImageBtn = $("#chooseImageBtn")
    const route = $button.data('url') || $button.data('route');
    const imageInput = document.getElementById('aiImageUpload');
    const originalimageInput = document.getElementById('aiImageUploadOriginal');
    const lang = $button.data('lang');
    
    if (!imageInput || !imageInput.files[0]) {
        toastr.error('Please select an image first');
        return;
    } else {
        $chooseImageBtn.addClass('disabled');
    }

    const $titleField = $('#item_name');
    if ($titleField.length > 0) {
        $('html, body').animate({
            scrollTop: $titleField.offset().top - 100
        }, 800);
    }

    $button.prop('disabled', true);
    $button.find('.btn-text').text('Generating');
    $button.find('.ai-btn-animation').removeClass('d-none');
    $button.find('i').addClass('d-none');

    const formData = new FormData();
    formData.append('image', imageInput.files[0]);
    
    $.ajax({
        url: route,
        type: 'POST',
        dataType: 'json',
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        success: function (response) {

            $('#item_name').val(response.data.title);

            const aiFile = originalimageInput.files[0];
            if (aiFile) {
                const dt1 = new DataTransfer();
                dt1.items.add(aiFile);
                document.getElementById('product_image').files = dt1.files;
                $("#product_image").trigger("change");
            }

            const $nameField = $('#item_name');
            if ($nameField.length > 0) {
                $('html, body').animate({
                    scrollTop: $nameField.offset().top - 100
                }, 800);
            }

            setTimeout(function () {
                const $card = $('.card-body:has(.auto_fill_description)');
                $('html, body').animate({
                    scrollTop: $card.offset().top - 100
                }, 800);

                $('.auto_fill_description').first().trigger('click');

                waitForDescriptionAndContinue(lang, $button, $imageRemoveButton, $chooseImageBtn);
            }, 200);

            $chooseImageBtn.removeClass('disabled');
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
             $('#request_type').val(requestType);
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = xhr.responseJSON.errors;
                Object.keys(errors).forEach(key => {
                    errors[key].forEach(message => {
                        toastr.error(message);
                    });
                });
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error('An unexpected error occurred during image analysis.');
            }

            $imageRemoveButton.prop('disabled', false);
            $chooseImageBtn.removeClass('disabled');
            $button.prop('disabled', false);
            $button.find('.btn-text').text('Generate Product Data');
            $button.find('.ai-btn-animation').addClass('d-none');
            $button.find('i').removeClass('d-none');

        },
        complete: function () {
            
        }
    });
});

function waitForDescriptionAndContinue(lang, $button, $imageRemoveButton, $chooseImageBtn) {
    const descriptionField = $('#item_description');
    let checkCount = 0;
    const maxChecks = 15;

    const checkDescription = setInterval(function () {
        checkCount++;
        let hasContent = false;
        if (descriptionField.length > 0) {
            const content = descriptionField.val() || descriptionField.text();
            hasContent = content && content.trim().length > 0;
        }

        if (hasContent || checkCount >= maxChecks) {
            clearInterval(checkDescription);

            const remainingSteps = [
                { selector: '.variation_setup_auto_fill', delay: 3000 },
                { selector: '.ingredients_auto_fill', delay: 4000 },
                { selector: '.addons_auto_fill', delay: 5000 },
                { selector: '.specification_auto_fill', delay: 6000 },
            ];

            remainingSteps.forEach(step => {
                setTimeout(function () {
                    const $card = $('.card-body:has(' + step.selector + ')');
                    if ($card.length > 0) {
                        $('html, body').animate({
                            scrollTop: $card.offset().top - 100
                        }, 800);
                    }
                    $(step.selector + '[data-lang="' + lang + '"]').trigger('click');
                }, step.delay);
            });

            const totalDelay = remainingSteps[remainingSteps.length - 1].delay + 2500;
            setTimeout(() => {
                $imageRemoveButton.prop('disabled', false);
                $imageRemoveButton.trigger('click');
                $chooseImageBtn.prop('disabled', false);
                $button.prop('disabled', false);
                $button.find('.btn-text').text('Generate Product Data');
                $button.find('.ai-btn-animation').addClass('d-none');
                $button.find('i').removeClass('d-none');
            }, totalDelay);
        }
    }, 600);
}


document.querySelectorAll('.outline-wrapper').forEach(wrapper => {
    const child = wrapper.firstElementChild;
    if (child) {
        const radius = getComputedStyle(child).borderRadius;
        wrapper.style.borderRadius = radius;
    }
});


function replaceSVGs() {
    $("img.svg").each(function () {
        var $img = $(this);
        var imgID = $img.attr("id");
        var imgClass = $img.attr("class");
        var imgURL = $img.attr("src");

        $.get(
            imgURL,
            function (data) {
                var $svg = $(data).find("svg");

                if (typeof imgID !== "undefined") {
                    $svg = $svg.attr("id", imgID);
                }
                if (typeof imgClass !== "undefined") {
                    $svg = $svg.attr("class", imgClass + " replaced-svg");
                }

                $svg = $svg.removeAttr("xmlns:a");

                if (
                    !$svg.attr("viewBox") &&
                    $svg.attr("height") &&
                    $svg.attr("width")
                ) {
                    $svg.attr(
                        "viewBox",
                        "0 0 " + $svg.attr("height") + " " + $svg.attr("width")
                    );
                }

                $img.replaceWith($svg);
            },
            "xml"
        );
    });
}
