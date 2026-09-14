<!-- AI Assistant Modal -->
<div class="modal fade p-0" id="aiAssistantModal" tabindex="-1" aria-labelledby="aiAssistantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-slideInRight modal-dialog-scrollable modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2 aiAssistantModalLabel" id="aiAssistantModalLabel">
                    <span class="square-div">
                        <span class="ai-btn-animation">
                            <span class="gradientCirc"></span>
                        </span>
                        <img class="position-relative z-1" width="15" height="12" src="{{ asset('images/svg/blink-icon-white.svg') }}" alt="">
                    </span>
                    <span id="modalTitle">{{ trans('lang.ai_assistant') }}</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" ria-label="{{ trans('lang.ai_close') }}">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Main AI Assistant Content -->
                <div id="mainAiContent" class="ai-modal-content" style="display: none;">
                    <div class="text-center mb-4">
                        <div class="ai-avatar mb-3">
                            <div class="avatar-circle mx-auto">
                                <span class="ai-btn-animation">
                                    <span class="gradientCirc"></span>
                                </span>
                                <img class="position-relative z-1" width="40" height="34" src="{{ asset('images/svg/blink-icon-white.svg') }}" alt="">
                            </div>
                        </div>

                        <div class="ai-greeting mb-5">
                            <h4 class="text-title">{{ trans('lang.ai_hi_there') }}</h4>
                            <h2 class="mb-2">{{ trans('lang.ai_im_here_to_help') }}</h2>
                            <p class="text-muted">
                                {{ trans('lang.ai_im_help_text') }}
                            </p>
                        </div>

                        <div class="ai-actions d-grid gap-3">
                            <button type="button" class="btn btn-outline-primary bg-transparent btn-block d-flex gap-2 mb-3 ai-action-btn"
                                data-action="upload">
                                <i class="mdi mdi-image"></i>
                                <span class="text-title">{{ trans('lang.ai_upload_image' ) }}</span>
                            </button>
                            <button type="button" class="btn btn-outline-primary bg-transparent btn-block d-flex gap-2 ai-action-btn"
                                data-action="title">
                                <i class="mdi mdi-translate"></i>
                                <span class="text-title">{{ trans('lang.ai_generate_product_name' ) }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="uploadImageContent" class="ai-modal-content" style="display: none;">
                    <div class="mt-10">
                        <div class="mb-4">
                            <h5 class="mb-3 fs-16 font-bold">
                                {{ trans('lang.ai_give_product_name_or_upload_image') }}
                            </h5>
                            <p class="mb-3">{{ trans('lang.ai_proper_product_name' )}}</p>
                            <ul class="mb-5 pl-4">
                                <li>{{ trans('lang.ai_try_clean_image' )}}</li>
                                <li>{{ trans('lang.ai_use_close_your_product_image' ) }}</li>
                            </ul>
                        </div>
                        <div class="text-center mb-4">
                            <label class="upload-zone w-100 mx-auto" id="chooseImageBtn">
                                <input type="file" id="aiImageUpload" class="image-compressor"  hidden class="d-none" accept="image/*">
                                <input type="file" id="aiImageUploadOriginal" hidden accept="image/*">
                                <div class="text-box mx-auto">
                                    <div class="w-100 d-flex flex-column gap-2 justify-content-center align-items-center py-4">
                                        <img width="40" height="40" src="{{ asset('images/svg/image-upload.svg') }}" alt="">
                                        <div class="d-flex gap-2 align-items-center justify-content-center fs-14">
                                            <span type="button" class="text-primary font-semibold fs-12 text-underline">
                                                <i class="fi fi-rr-cloud-upload-alt"></i>
                                                {{ trans('lang.ai_browse_image' ) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                 <div id="imagePreview" class="mx-auto position-relative" style="display: none;">
                                     <img id="previewImg" src="" alt="{{ trans('lang.ai_preview' ) }}"
                                         class="upload-zone_img" style="max-height: 200px;">
                                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                                            <button type="button" class="btn btn-danger p-0 square-div z-2 remove_image_btn" id="removeImageBtn" data-toggle="tooltip" title="{{ trans('lang.ai_remove_image' ) }}">
                                                <i class="tio-clear"></i>
                                            </button>
                                        </div>
                                    </div>
                                </label>
                                <div class="mt-4 text-center analyzeImageBtn_wrapper">
                                    <button type="button" class="btn btn-primary mb-3 d-flex align-items-center gap-2 opacity-1 border-0 mx-auto"
                                        id="analyzeImageBtn" data-url="{{ route('ai.analyze-image-auto-fill') }}"
                                        data-lang="{{ App::getLocale() }}">
                                        <span class="ai-btn-animation d-none">
                                            <span class="gradientRect"></span>
                                        </span>
                                        <span class="position-relative z-1 d-flex gap-2 align-items-center">
                                            <span
                                                class="d-flex align-items-center btn-text">{{ trans('lang.ai_generate_product_data' ) }}</span>
                                                <img width="17" height="15" src="{{ asset('images/svg/blink-left.svg') }}" alt="">
                                        </span>
                                    </button>
                                </div>
                        </div>
    
                    </div>
                </div>

                <div id="giveTitleContent" class="ai-modal-content" style="display: none;">
                    <div class="mb-4">
                        <div class="giveTitleContent_text">
                            <h5 class="mb-3 fs-16 font-bold">
                                {{ trans('lang.ai_now_tell_me' ) }}
                            </h5>
                            <ul class="mb-3 pl-4">
                                <li>{{ trans('lang.ai_want_to_add_detail_for_food') }}</li>
                                <li>{{ trans('lang.ai_want_to_add_detail_for_men') }}</li>
                                <li>{{ trans('lang.ai_want_to_add_detail_for_women') }}</li>
                            </ul>
                            <p class="mb-4">{{ trans('lang.ai_feel_free_to_describe') }}</p>
                        </div>
                        <div class="generate-text-input-group">
                            <input type="text" class="form-control" id="productKeywords"
                                placeholder="{{ trans('lang.ai_tell_me_about_item') }}" data-role="tagsinput">
                                <button type="button" class="btn btn-primary border-0"
                                    id="generateTitleBtn" data-route="{{ route('ai.generate-title-suggestions') }}"
                                    data-lang="en">
                                    <span class="ai-loader-animation z-2 d-none">
                                        <span class="loader-circle"></span>
                                        <img width="15" height="15" class="position-relative h-100" src="{{ asset('images/svg/blink-left.svg') }}" alt="">
                                    </span>
                                    <span class="position-rtelative z-1"><i class="mdi mdi-arrow-right"></i></span>
                                </button>
                        </div>
                    </div>

                    <div id="generatedTitles" style="display: none;">
                        <div class="text-primary generate_btn_wrapper show_generating_text d-none mb-3">
                            <div class="btn-svg-wrapper">
                                <img width="18" height="18" class="" src="{{ asset('images/svg/blink-icon-orange.svg') }}"
                                alt="">
                            </div>
                            <span class="ai-text-animation ai-text-animation-visible">
                                {{ trans('lang.ai_just_asecond') }}
                            </span>
                        </div>
                        <h4 class="mb-2 titlesList_title d-none">{{ trans('lang.ai_suggest_product_name')}}</h4>
                        <div id="titlesList" class="list-group">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if (isset($openai_settings) && data_get($openai_settings, 'status') == true)
    <div class="floating-ai-button">
        <button type="button" class="btn btn-lg rounded-circle shadow-lg" data-toggle="modal"
        data-target="#aiAssistantModal" data-action="main" title="{{ trans('lang.ai_assistant') }}">
            <span class="ai-btn-animation">
                <span class="gradientCirc"></span>
            </span>
            <span class="position-relative z-1 text-white d-flex flex-column gap-1 align-items-center">
                <img width="16" height="17" src="{{ asset('images/svg/hexa-ai.svg') }}" alt="">
                <span class="fs-12 font-semibold">{{ trans('lang.ai_use_ai') }}</span>
            </span>
        </button>
        <div class="ai-tooltip">
            <span>{{ trans('lang.ai_assistant') }}</span>
        </div>
    </div>
@endif
