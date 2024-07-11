@foreach ($packages as $package)
    <div class="col-12 col-lg-4">
        <input type="hidden" value="{{ $package->id }}" class="package_id">

        <div class="card h-100 package-card">
            <div class="card-body">
                <div class="tt-pricing-plan">
                    {{-- name & desc --}}

                    <div class="tt-plan-name">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 tt_update_text" data-name="package-name-{{ $package->id }}">
                                {!! html_entity_decode($package->title) !!}
                            </h5>
                            <span class="tt-edit-icon ms-2 text-muted"><i class="tt_editable cursor-pointer icon-14"
                                    data-name="package-name-{{ $package->id }}" data-feather="edit"></i></span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted tt_update_text"
                                data-name="package-description-{{ $package->id }}">{!! html_entity_decode($package->description) !!}</span>
                            <span class="tt-edit-icon ms-2 text-muted"><i class="tt_editable cursor-pointer icon-14"
                                    data-name="package-description-{{ $package->id }}" data-feather="edit"></i></span>
                        </div>
                    </div>

                    {{-- price --}}
                    <div class="tt-price-wrap d-flex align-items-center justify-content-between mt-4 mb-3">
                        @if ($package->package_type == 'starter')
                            <div class="monthly-price fs-1 fw-bold">
                                {{ localize('Free') }}
                            </div>
                        @else
                            <div class="monthly-price fs-1 fw-bold">

                                <input type="hidden" name="package_main_price"
                                    class="package-main-price-{{ $package->id }}" value="{{ $package->price }}">

                                $<span class="tt_update_text package-price-{{ $package->id }}"
                                    onkeypress="nonNumericFilter()"
                                    data-name="package-price-{{ $package->id }}">{{ $package->discount_status && $package->discount_price ? $package->discount_price : $package->price }}</span>

                                <span class="tt_update_text ">
                                    <del
                                        class="package-discount-price-{{ $package->id }}">{{ $package->discount_status && $package->discount_price ? '$' . $package->price : '' }}</del></span>
                                <sup><span class="cursor-pointer" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="{{ localize('Set $0 to make it free') }}"><i
                                            data-feather="help-circle" class="icon-14"></i></span></sup>


                            </div>
                            <span
                                class="tt-edit-icon ms-2 text-muted package-price-edit-{{ $package->id }} {{ $package->discount_status && $package->discount_price ? 'd-none' : '' }}"><i
                                    class="tt_editable cursor-pointer icon-14"
                                    data-name="package-price-{{ $package->id }}" data-feather="edit"></i></span>
                        @endif
                    </div>

                </div>


                <div class="tt-pricing-feature">
                    <ul class="tt-pricing-feature list-unstyled rounded mb-0">

                        <!-- show hide info icon start -->
                        <li class="pt-0">
                            <div class="d-flex align-items-center justify-content-end">
                                <div class="d-flex align-items-center tt-info-icons">

                                    <span class="text-muted px-1" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="{{ localize('If this is enabled, user will be able to use unlimited balance.') }}"><i
                                            data-feather="activity"></i></span>

                                    <span class="text-muted px-1 ms-2" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="{{ localize('If this is checkd, it will be shown in the subscription plan list') }}"><i
                                            data-feather="help-circle"></i></span>

                                    <span class="text-muted px-1 ms-2" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="{{ localize('If this is enabled, user will be able to use the feature.') }}"><i
                                            data-feather="power"></i></span>


                                </div>
                            </div>
                        </li>
                        <!-- show hide info icon end -->

                        @if ($package->package_type != 'starter')
                            <li>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <span><i data-feather="check-circle"
                                                class="icon-14 me-2 text-success"></i><strong class=""
                                                data-name="package-words-{{ $package->id }}">
                                                {{ localize('Discount') }}</strong>
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <div class="form-check form-switch">
                                            <input type="checkbox"
                                                class="form-check-input cursor-pointer allow_discount tt_editable"
                                                data-id="{{ $package->id }}" id="allow_discount-{{ $package->id }}"
                                                data-name="allow_discount-{{ $package->id }}"
                                                @if ($package->discount_status == 1) checked @endif>
                                        </div>

                                    </div>
                                </div>

                            </li>
                            <li class="discount_option {{ $package->discount_status != 1 ? 'd-none' : '' }}"
                                id="discount_option_{{ $package->id }}">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <select
                                            class="form-select py-1 discount_type cursor-pointer discount_type_{{ $package->id }}"
                                            name="discount_type" onchange="handleDiscountTypeChange(this)">
                                            <option value="1"
                                                {{ $package->discount_type == 1 ? 'selected' : '' }}>
                                                {{ localize('Fixed') }}</option>
                                            <option value="2"
                                                {{ $package->discount_type == 2 ? 'selected' : '' }}>
                                                {{ localize('Percentage') }}</option>

                                        </select>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <input
                                            class="form-control py-1 discount_amount package-discount-{{ $package->id }}"
                                            type="text" onkeypress="nonNumericFilter()" name="discount"
                                            placeholder="{{ localize('discount') }}"
                                            value="{{ $package->discount }}" />
                                    </div>
                                </div>

                            </li>
                        @endif
                        @if (getSetting('enable_built_in_templates') != '0' ||
                                getSetting('enable_ai_chat') != '0' ||
                                getSetting('enable_ai_code') != '0' ||
                                getSetting('enable_custom_templates') != '0')
                            <li>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <span><i data-feather="check-circle"
                                                class="icon-14 me-2 text-success"></i>
                                                <strong class="tt_update_text" id="allow_word_text_{{ $package->id }}"
                                                data-name="package-words-{{ $package->id }}"
                                                onkeypress="nonNumericFilter()">{{ $package->allow_unlimited_word == 1 ? localize('Unlimited') : $package->total_words_per_month }}</strong>
                                            {{ $package->package_type != 'prepaid' && $package->package_type != 'starter' ? localize('Words per month') : localize('Words') }} </span>
                                        <span class="tt-edit-icon ms-2 text-muted {{$package->allow_unlimited_word == 1 ? 'd-none' : ''}}" id="allow_word_edit_{{ $package->id }}"><i
                                                class="tt_editable cursor-pointer icon-14"
                                                data-name="package-words-{{ $package->id }}"
                                                data-feather="edit"></i></span>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <div class="form-check tt-checkbox">
                                            <input class="form-check-input cursor-pointer  unlimited_balance unlimited_word" type="checkbox"
                                                id="allow_unlimited_word-{{ $package->id }}"
                                                data-name="allow_unlimited_word-{{ $package->id }}"
                                                @if ($package->allow_unlimited_word == 1) checked @endif>
                                        </div>
                                        <div class="form-check tt-checkbox">
                                            <input class="form-check-input cursor-pointer tt_editable" type="checkbox"
                                                id="show_word_tools-{{ $package->id }}"
                                                data-name="show_word_tools-{{ $package->id }}"
                                                @if ($package->show_word_tools == 1) checked @endif>
                                        </div>

                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input cursor-pointer tt_editable"
                                                id="allow_word_tools-{{ $package->id }}"
                                                data-name="allow_word_tools-{{ $package->id }}"
                                                @if ($package->allow_word_tools == 1) checked @endif>
                                        </div>
                                    </div>
                                </div>

                                <ul class="list-unstyled ms-4 my-2">
                                    @if (getSetting('enable_built_in_templates') != '0')
                                        <li class="p-0 d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                @php
                                                    $packageTemplatesCounter = $package->subscription_package_templates()->count();
                                                @endphp
                                                <span>- <strong>{{ $packageTemplatesCounter }}</strong>
                                                    {{ localize('AI Templates') }} </span>
                                                <span class="tt-edit-icon ms-2 text-muted"><i
                                                        class="cursor-pointer icon-14"
                                                        data-name="package-template-{{ $package->id }}"
                                                        data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
                                                        data-feather="edit"
                                                        onclick="getPackageTemplates(this)"></i></span>
                                            </div>

                                            <div class="d-flex align-items-center">
                                                <div class="form-check tt-checkbox">
                                                    <input class="form-check-input cursor-pointer tt_editable"
                                                        type="checkbox"
                                                        id="show_built_in_templates-{{ $package->id }}"
                                                        data-name="show_built_in_templates-{{ $package->id }}"
                                                        @if ($package->show_built_in_templates == 1) checked @endif>
                                                </div>

                                                <div class="form-check form-switch">
                                                    <input type="checkbox"
                                                        class="form-check-input cursor-pointer tt_editable"
                                                        id="allow_built_in_templates-{{ $package->id }}"
                                                        data-name="allow_built_in_templates-{{ $package->id }}"
                                                        @if ($package->allow_built_in_templates == 1) checked @endif>
                                                </div>
                                            </div>
                                        </li>
                                    @endif


                                    @if (getSetting('enable_ai_chat') != '0')
                                        <li class="p-0 d-flex justify-content-between align-items-center">
                                            <span>- <label for="allow_ai_chat-{{ $package->id }}"
                                                    class="cursor-pointer">{{ localize('AI Chat') }}</label></span>
                                            <div class="d-flex align-items-center">
                                                <div class="form-check tt-checkbox">
                                                    <input class="form-check-input cursor-pointer tt_editable"
                                                        type="checkbox" id="show_ai_chat-{{ $package->id }}"
                                                        data-name="show_ai_chat-{{ $package->id }}"
                                                        @if ($package->show_ai_chat == 1) checked @endif>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox"
                                                        class="form-check-input cursor-pointer tt_editable"
                                                        id="allow_ai_chat-{{ $package->id }}"
                                                        data-name="allow_ai_chat-{{ $package->id }}"
                                                        @if ($package->allow_ai_chat == 1) checked @endif>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                    @if (getSetting('enable_ai_rewriter') != '0')
                                        <li class="p-0 d-flex justify-content-between align-items-center">
                                            <span>- <label for="allow_ai_rewriter-{{ $package->id }}"
                                                    class="cursor-pointer">{{ localize('AI ReWriter') }}</label></span>
                                            <div class="d-flex align-items-center">
                                                <div class="form-check tt-checkbox">
                                                    <input class="form-check-input cursor-pointer tt_editable"
                                                        type="checkbox" id="show_ai_rewriter-{{ $package->id }}"
                                                        data-name="show_ai_rewriter-{{ $package->id }}"
                                                        @if ($package->show_ai_rewriter == 1) checked @endif>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox"
                                                        class="form-check-input cursor-pointer tt_editable"
                                                        id="allow_ai_rewriter-{{ $package->id }}"
                                                        data-name="allow_ai_rewriter-{{ $package->id }}"
                                                        @if ($package->allow_ai_rewriter == 1) checked @endif>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                    @if (getSetting('enable_ai_vision') != '0')
                                        <li class="p-0 d-flex justify-content-between align-items-center">
                                            <span>- <label for="allow_ai_vision-{{ $package->id }}"
                                                    class="cursor-pointer">{{ localize('AI Vision') }}</label></span>
                                            <div class="d-flex align-items-center">
                                                <div class="form-check tt-checkbox">
                                                    <input class="form-check-input cursor-pointer tt_editable"
                                                        type="checkbox" id="show_ai_vision-{{ $package->id }}"
                                                        data-name="show_ai_vision-{{ $package->id }}"
                                                        @if ($package->show_ai_vision == 1) checked @endif>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox"
                                                        class="form-check-input cursor-pointer tt_editable"
                                                        id="allow_ai_vision-{{ $package->id }}"
                                                        data-name="allow_ai_vision-{{ $package->id }}"
                                                        @if ($package->allow_ai_vision == 1) checked @endif>
                                                </div>
                                            </div>
                                        </li>
                                    @endif

                                    @if (getSetting('enable_ai_pdf_chat') != '0')
                                        <li class="p-0 d-flex justify-content-between align-items-center">
                                        <span>- <label for="allow_ai_vision-{{ $package->id }}"
                                                       class="cursor-pointer">{{ localize('AI PDF Chat') }}</label></span>
                                            <div class="d-flex align-items-center">
                                                <div class="form-check tt-checkbox">
                                                    <input class="form-check-input cursor-pointer tt_editable"
                                                           type="checkbox" id="show_ai_pdf_chat-{{ $package->id }}"
                                                           data-name="show_ai_pdf_chat-{{ $package->id }}"
                                                           @if ($package->show_ai_pdf_chat == 1) checked @endif>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox"
                                                           class="form-check-input cursor-pointer tt_editable"
                                                           id="allow_ai_pdf_chat-{{ $package->id }}"
                                                           data-name="allow_ai_pdf_chat-{{ $package->id }}"
                                                           @if ($package->allow_ai_pdf_chat == 1) checked @endif>
                                                </div>
                                            </div>
                                        </li>
                                    @endif

                                    @if (getSetting('enable_ai_code') != '0')
                                        <li class="p-0 d-flex justify-content-between align-items-center">
                                            <span>- <label for="allow_ai_code-{{ $package->id }}"
                                                    class="cursor-pointer">{{ localize('AI Code') }}</label></span>
                                            <div class="d-flex align-items-center">
                                                <div class="form-check tt-checkbox">
                                                    <input class="form-check-input cursor-pointer tt_editable"
                                                        type="checkbox" id="show_ai_code-{{ $package->id }}"
                                                        data-name="show_ai_code-{{ $package->id }}"
                                                        @if ($package->show_ai_code == 1) checked @endif>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox"
                                                        class="form-check-input cursor-pointer tt_editable"
                                                        id="allow_ai_code-{{ $package->id }}"
                                                        data-name="allow_ai_code-{{ $package->id }}"
                                                        @if ($package->allow_ai_code == 1) checked @endif>
                                                </div>
                                            </div>
                                        </li>
                                    @endif

                                    @if (getSetting('enable_text_to_speech') != '0')
                                        <li class="p-0 d-flex justify-content-between align-items-center">
                                            <span>- <label for="allow_text_to_speech-{{ $package->id }}"
                                                    class="cursor-pointer">{{ localize('Text to Speech') }}</label></span>
                                            <div class="d-flex align-items-center">
                                                <div class="form-check tt-checkbox">
                                                    <input class="form-check-input cursor-pointer tt_editable"
                                                        type="checkbox" id="show_text_to_speech-{{ $package->id }}"
                                                        data-name="show_text_to_speech-{{ $package->id }}"
                                                        @if ($package->show_text_to_speech == 1) checked @endif>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox"
                                                        class="form-check-input cursor-pointer tt_editable"
                                                        data-name="allow_text_to_speech-{{ $package->id }}"
                                                        id="allow_text_to_speech-{{ $package->id }}"
                                                        @if ($package->allow_text_to_speech == 1) checked @endif>
                                                </div>
                                            </div>
                                        </li>
                                    @endif

                                    @if (getSetting('enable_custom_templates') != '0')
                                        <li class="p-0 d-flex justify-content-between align-items-center">
                                            <span>- <label for="allow_custom_templates-{{ $package->id }}"
                                                    class="cursor-pointer">{{ localize('Custom Templates') }}</label></span>
                                            <div class="d-flex align-items-center">
                                                <div class="form-check tt-checkbox">
                                                    <input class="form-check-input cursor-pointer tt_editable"
                                                        type="checkbox"
                                                        id="show_custom_templates-{{ $package->id }}"
                                                        data-name="show_custom_templates-{{ $package->id }}"
                                                        @if ($package->show_custom_templates == 1) checked @endif>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox"
                                                        class="form-check-input cursor-pointer tt_editable"
                                                        data-name="allow_custom_templates-{{ $package->id }}"
                                                        id="allow_custom_templates-{{ $package->id }}"
                                                        @if ($package->allow_custom_templates == 1) checked @endif>
                                                </div>
                                            </div>
                                        </li>
                                    @endif

                                    {{-- blog wizard --}}
                                    @if (getSetting('enable_blog_wizard') != '0')
                                        <li class="p-0 d-flex justify-content-between align-items-center">
                                            <span>- <label for="allow_blog_wizard-{{ $package->id }}"
                                                    class="cursor-pointer">{{ localize('AI Blog Wizard') }}</label></span>
                                            <div class="d-flex align-items-center">
                                                <div class="form-check tt-checkbox">
                                                    <input class="form-check-input cursor-pointer tt_editable"
                                                        type="checkbox" id="show_blog_wizard-{{ $package->id }}"
                                                        data-name="show_blog_wizard-{{ $package->id }}"
                                                        @if ($package->show_blog_wizard == 1) checked @endif>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox"
                                                        class="form-check-input cursor-pointer tt_editable"
                                                        data-name="allow_blog_wizard-{{ $package->id }}"
                                                        id="allow_blog_wizard-{{ $package->id }}"
                                                        @if ($package->allow_blog_wizard == 1) checked @endif>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                    {{-- blog wizard --}}

                                    {{-- blog wizard --}}
                                    @if (getSetting('enable_eleven_labs') != '0')
                                        <li class="p-0 d-flex justify-content-between align-items-center">
                                            <span>- <label for="allow_eleven_labs-{{ $package->id }}"
                                                    class="cursor-pointer">{{ localize('ElevenLabs') }}</label></span>
                                            <div class="d-flex align-items-center">
                                                <div class="form-check tt-checkbox">
                                                    <input class="form-check-input cursor-pointer tt_editable"
                                                        type="checkbox" id="show_eleven_labs-{{ $package->id }}"
                                                        data-name="show_eleven_labs-{{ $package->id }}"
                                                        @if ($package->show_eleven_labs == 1) checked @endif>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox"
                                                        class="form-check-input cursor-pointer tt_editable"
                                                        data-name="allow_eleven_labs-{{ $package->id }}"
                                                        id="allow_eleven_labs-{{ $package->id }}"
                                                        @if ($package->allow_eleven_labs == 1) checked @endif>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                    {{-- blog wizard --}}
                                </ul>
                            </li>
                        @endif

                        @if (getSetting('enable_ai_images') != '0')
                            <li>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <span><i data-feather="check-circle"
                                                class="icon-14 me-2 text-success"></i><strong class="tt_update_text"
                                                data-name="package-images-{{ $package->id }}" id="allow_image_text_{{ $package->id }}"
                                                onkeypress="nonNumericFilter()">{{ $package->allow_unlimited_image == 1 ? localize('Unlimited') : $package->total_images_per_month }}</strong>
                                            {{ $package->package_type != 'prepaid' ? localize('Images per month') : localize('Images') }}</span>
                                        <span class="tt-edit-icon ms-2 text-muted {{$package->allow_unlimited_image == 1 ? 'd-none' : ''}}" id="allow_image_edit_{{ $package->id }}"><i
                                                class="tt_editable cursor-pointer icon-14"
                                                data-name="package-images-{{ $package->id }}"
                                                data-feather="edit"></i></span>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <div class="form-check tt-checkbox">
                                            <input type="checkbox" class="form-check-input cursor-pointer  unlimited_balance"
                                                data-name="allow_unlimited_image-{{ $package->id }}"
                                                id="allow_unlimited_image-{{ $package->id }}"
                                                @if ($package->allow_unlimited_image == 1) checked @endif>
                                        </div>
                                        <div class="form-check tt-checkbox">
                                            <input class="form-check-input cursor-pointer tt_editable" type="checkbox"
                                                id="show_image_tools-{{ $package->id }}"
                                                data-name="show_image_tools-{{ $package->id }}"
                                                @if ($package->show_image_tools == 1) checked @endif>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input cursor-pointer tt_editable"
                                                data-name="allow_image_tools-{{ $package->id }}"
                                                id="allow_image_tools-{{ $package->id }}"
                                                @if ($package->allow_image_tools == 1) checked @endif>
                                        </div>
                                    </div>
                                </div>

                                <ul class="list-unstyled ms-4 my-2">
                                    
                                    <li class="p-0 d-flex justify-content-between align-items-center">
                                        <span>- <label for="allow_dall_e_2_image-{{ $package->id }}"
                                                class="cursor-pointer">{{ localize('Dall E 2') }}</label></span>
                                        <div class="d-flex align-items-center">

                                            <div class="form-check tt-checkbox">
                                                <input class="form-check-input cursor-pointer tt_editable"
                                                    type="checkbox" id="show_dall_e_2_image-{{ $package->id }}"
                                                    data-name="show_dall_e_2_image-{{ $package->id }}"
                                                    @if ($package->show_dall_e_2_image == 1) checked @endif>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input type="checkbox"
                                                    class="form-check-input cursor-pointer tt_editable"
                                                    id="allow_dall_e_2_image-{{ $package->id }}"
                                                    data-name="allow_dall_e_2_image-{{ $package->id }}"
                                                    @if ($package->allow_dall_e_2_image == 1) checked @endif>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="p-0 d-flex justify-content-between align-items-center">
                                        <span>- <label for="allow_dall_e_3_image-{{ $package->id }}"
                                                class="cursor-pointer">{{ localize('Dall E 3') }}</label></span>
                                        <div class="d-flex align-items-center">

                                            <div class="form-check tt-checkbox">
                                                <input class="form-check-input cursor-pointer tt_editable"
                                                    type="checkbox" id="show_dall_e_3_image-{{ $package->id }}"
                                                    data-name="show_dall_e_3_image-{{ $package->id }}"
                                                    @if ($package->show_dall_e_3_image == 1) checked @endif>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input type="checkbox"
                                                    class="form-check-input cursor-pointer tt_editable"
                                                    id="allow_dall_e_3_image-{{ $package->id }}"
                                                    data-name="allow_dall_e_3_image-{{ $package->id }}"
                                                    @if ($package->allow_dall_e_3_image == 1) checked @endif>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="p-0 d-flex justify-content-between align-items-center">
                                        <span>- <label for="allow_sd_images-{{ $package->id }}"
                                                class="cursor-pointer">{{ localize('Stable Diffusion') }}</label></span>
                                        <div class="d-flex align-items-center">
                                            <div class="form-check tt-checkbox">
                                                <input class="form-check-input cursor-pointer tt_editable"
                                                    type="checkbox" id="show_sd_images-{{ $package->id }}"
                                                    data-name="show_sd_images-{{ $package->id }}"
                                                    @if ($package->show_sd_images == 1) checked @endif>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input type="checkbox"
                                                    class="form-check-input cursor-pointer tt_editable"
                                                    id="allow_sd_images-{{ $package->id }}"
                                                    data-name="allow_sd_images-{{ $package->id }}"
                                                    @if ($package->allow_sd_images == 1) checked @endif>
                                            </div>
                                        </div>
                                    </li>
                                    @if (getSetting('enable_ai_image_chat') != '0')
                                    <li class="p-0 d-flex justify-content-between align-items-center">
                                        <span>- <label for="allow_ai_image_chat-{{ $package->id }}"
                                                class="cursor-pointer">{{ localize('Chat Image') }}</label></span>
                                        <div class="d-flex align-items-center">
                                            <div class="form-check tt-checkbox">
                                                <input class="form-check-input cursor-pointer tt_editable"
                                                    type="checkbox" id="show_ai_image_chat-{{ $package->id }}"
                                                    data-name="show_ai_image_chat-{{ $package->id }}"
                                                    @if ($package->show_ai_image_chat == 1) checked @endif>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input type="checkbox"
                                                    class="form-check-input cursor-pointer tt_editable"
                                                    id="allow_ai_image_chat-{{ $package->id }}"
                                                    data-name="allow_ai_image_chat-{{ $package->id }}"
                                                    @if ($package->allow_ai_image_chat == 1) checked @endif>
                                            </div>
                                        </div>
                                    </li>
                                    @endif
                                </ul>
                            </li>
                        @endif


                        @if (getSetting('enable_speech_to_text') != '0')
                            <li>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <span><i data-feather="check-circle"
                                                class="icon-14 me-2 text-success"></i>
                                                <strong class="tt_update_text"
                                                data-name="package-speech-to-text-{{ $package->id }}" id="allow_speech_to_text_text_{{ $package->id }}"
                                                onkeypress="nonNumericFilter()">{{ $package->allow_unlimited_speech_to_text == 1 ? localize('Unlimited') : $package->total_speech_to_text_per_month }}</strong>
                                            {{ $package->package_type != 'prepaid' ? localize('Speech to Text per month') : localize('Speech to Texts') }}</span>
                                        <span class="tt-edit-icon ms-2 text-muted {{$package->allow_unlimited_speech_to_text == 1 ? 'd-none' : ''}}"><i
                                                class="tt_editable cursor-pointer icon-14" id="allow_speech_to_text_edit_{{ $package->id }}"
                                                data-name="package-speech-to-text-{{ $package->id }}"
                                                data-feather="edit"></i></span>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <div class="form-check tt-checkbox">
                                            <input type="checkbox" class="form-check-input cursor-pointer unlimited_balance"
                                                data-name="allow_unlimited_speech_to_text-{{ $package->id }}"
                                                id="allow_unlimited_speech_to_text-{{ $package->id }}"
                                                @if ($package->allow_unlimited_speech_to_text == 1) checked @endif>
                                        </div>
                                        <div class="form-check tt-checkbox">
                                            <input class="form-check-input cursor-pointer tt_editable" type="checkbox"
                                                id="show_speech_to_text_tools-{{ $package->id }}"
                                                data-name="show_speech_to_text_tools-{{ $package->id }}"
                                                @if ($package->show_speech_to_text_tools == 1) checked @endif>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input cursor-pointer tt_editable"
                                                data-name="allow_speech_to_text-{{ $package->id }}"
                                                id="allow_speech_to_text-{{ $package->id }}"
                                                @if ($package->allow_speech_to_text == 1) checked @endif>
                                        </div>
                                    </div>
                                </div>

                                <ul class="list-unstyled ms-4 my-2">
                                    <li class="p-0 d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <span>- </i><strong class="tt_update_text"
                                                    data-name="package-audio-size-{{ $package->id }}"
                                                    onkeypress="nonNumericFilter()">{{ $package->speech_to_text_filesize_limit }}</strong>
                                                MB {{ localize('Audio file size limit') }}</span>
                                            <span class="tt-edit-icon ms-2 text-muted"><i
                                                    class="tt_editable cursor-pointer icon-14"
                                                    data-name="package-audio-size-{{ $package->id }}"
                                                    data-feather="edit"></i></span>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        <li class="d-flex justify-content-between align-items-center">
                            <span><i data-feather="check-circle" class="icon-14 me-2 text-success"></i><label
                                    for="is_featured-{{ $package->id }}"
                                    class="cursor-pointer">{{ localize('Is Featured?') }}</label></span>
                            <div class="form-check form-switch ms-2">
                                <input type="checkbox" class="form-check-input cursor-pointer tt_editable"
                                    id="is_featured-{{ $package->id }}"
                                    data-name="is_featured-{{ $package->id }}"
                                    @if ($package->is_featured == 1) checked @endif data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-title="{{ localize('If this is enabled, a recommend badge will be shown in the subscription plan.') }}">
                            </div>
                        </li>


                        <li class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span><i data-feather="check-circle" class="icon-14 me-2 text-success"></i><label
                                        for="has_live_support-{{ $package->id }}"
                                        class="cursor-pointer">{{ localize('Live Support') }}</label></span>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="form-check tt-checkbox">
                                    <input class="form-check-input cursor-pointer tt_editable" type="checkbox"
                                        id="show_live_support-{{ $package->id }}"
                                        data-name="show_live_support-{{ $package->id }}"
                                        @if ($package->show_live_support == 1) checked @endif>
                                </div>

                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input cursor-pointer tt_editable"
                                        data-name="has_live_support-{{ $package->id }}"
                                        id="has_live_support-{{ $package->id }}"
                                        @if ($package->has_live_support == 1) checked @endif data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-title="{{ localize('If this is enabled, you have to provide live support to the users.') }}">
                                </div>
                            </div>
                        </li>



                        <li class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span><i data-feather="check-circle" class="icon-14 me-2 text-success"></i><label
                                        for="has_free_support-{{ $package->id }}"
                                        class="cursor-pointer">{{ localize('Free Support') }}</label></span>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="form-check tt-checkbox">
                                    <input class="form-check-input cursor-pointer tt_editable" type="checkbox"
                                        id="show_free_support-{{ $package->id }}"
                                        data-name="show_free_support-{{ $package->id }}"
                                        @if ($package->show_free_support == 1) checked @endif>
                                </div>

                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input cursor-pointer tt_editable"
                                        data-name="has_free_support-{{ $package->id }}"
                                        id="has_free_support-{{ $package->id }}"
                                        @if ($package->has_free_support == 1) checked @endif data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-title="{{ localize('If this is enabled, you have to provide free support to the users.') }}">
                                </div>
                            </div>
                        </li>

                        <li class="d-flex justify-content-between align-items-center w-100">
                            <div class="d-flex align-items-center flex-grow-1">
                                <i data-feather="check-circle" class="icon-14 me-2 text-success"></i>
                                <select class="form-select py-1 package_open_ai_model" name="openai_model_id"
                                    onchange="handleModelChange(this)">
                                    <option value="" disabled>{{ localize('Select Open AI Model') }}</option>
                                    @foreach ($openAiModels as $openAiModel)
                                        <option value="{{ $openAiModel->id }}"
                                            @if ($package->openai_model_id == $openAiModel->id) selected @endif>
                                            {{ $openAiModel->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="ms-3 d-flex align-items-center justify-content-end">
                                <div class="form-check tt-checkbox" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-title="{{ localize('If this is checkd, it will be shown in the subscription plan list') }}">
                                    <input class="form-check-input cursor-pointer tt_editable" type="checkbox"
                                        id="show_open_ai_model-{{ $package->id }}"
                                        data-name="show_open_ai_model-{{ $package->id }}"
                                        @if ($package->show_open_ai_model == 1) checked @endif>
                                </div>
                            </div>
                        </li>

                        <li class="d-flex flex-column align-items-start">
                            <div class="w-100 d-flex align-items-center">
                                <i data-feather="check-circle" class="icon-14 me-2 text-success"></i>
                                <input class="form-control py-1 other_features" type="text"
                                    placeholder="{{ localize('Type additional features') }}"
                                    value="{{ $package->other_features }}" />
                            </div>
                            <small class="text-muted ps-4">*
                                {{ localize('Comma separated: Feature A,Feature B') }}</small>
                        </li>
                        {{-- duration add for starter pacakge --}}
                        @if ($package->package_type == 'starter')
                            <li class="d-flex flex-column align-items-start">
                                <div class="w-100 d-flex align-items-center">
                                    <i data-feather="check-circle" class="icon-14 me-2 text-success"></i>
                                    <input class="form-control py-1 duration" type="text"
                                        onkeypress="nonNumericFilter()"
                                        placeholder="{{ localize('30') }}"
                                        value="{{ $package->duration }}" />
                                </div>
                                <small class="text-muted ps-4">*
                                    {{ localize('Expire in number in days for Starter Package') }}</small>
                            </li>
                        @endif
                        {{-- end --}}

                    </ul>
                </div>
            </div>
            <div class="card-footer">
                <div>
                    <div class="d-flex justify-content-between">
                        <div class="d-flex align-items-center">
                            <span class="ms-1"><label for="is_active-{{ $package->id }}"
                                    class="cursor-pointer">{{ localize('Is Active?') }}</label></span>
                            <div class="form-check form-switch ms-2">
                                <input type="checkbox" class="form-check-input cursor-pointer tt_editable"
                                    id="is_active-{{ $package->id }}" data-name="is_active-{{ $package->id }}"
                                    @if ($package->is_active == 1) checked @endif>
                            </div>
                        </div>

                        @if ($package->package_type != 'starter')
                            <div>
                                <i class="text-danger cursor-pointer icon-16" data-feather="trash"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-title="{{ localize('Delete this package') }}"
                                    onclick="confirmDelete(this)"
                                    data-href="{{ route('subscriptions.delete', $package->id) }}"></i>
                            </div>
                        @endif

                    </div>
                    @if ($package->package_type == 'starter')
                        <small class="text-muted">*
                            {{ localize('If active, this will be applied to new user\'s registration.') }}
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach

<div class="col-12 col-lg-4 min-h-400">
    <div class="card h-100 tt-add-more-card justify-content-center">
        <div class="card-body text-center">
            <button type="button" class="btn btn-primary rounded-circle btn-icon" onclick="showNewModal(this)"><i
                    data-feather="plus"></i></button>
        </div>
    </div>
</div>
