@extends('backend.layouts.master')

@section('title')
    {{ localize('Email Template Settings') }} {{ getSetting('title_separator') }} {{ getSetting('system_title') }}
@endsection

@section('contents')
    <section class="tt-section pt-4">
        <div class="container">


            <div class="row mb-4">
                <div class="col-12">
                    <div class="tt-page-header">
                        <div class="d-lg-flex align-items-center justify-content-lg-between">
                            <div class="tt-page-title mb-3 mb-lg-0">
                                <h1 class="h4 mb-lg-1">{{ localize('Email Template Settings') }}</h1>
                                <ol class="breadcrumb breadcrumb-angle text-muted">
                                    <li class="breadcrumb-item"><a
                                            href="{{ route('writebot.dashboard') }}">{{ localize('Dashboard') }}</a>
                                    </li>
                                    <li class="breadcrumb-item">{{ localize('Email Template Settings') }}</li>
                                </ol>
                            </div>
                            <div class="tt-action">

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-between mb-5">

                <div class="col-xl-9">
                    <div class="tab-content" id="myTabContent">
                        @foreach ($templates as $template)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                id="{{ $template->slug }}-pane" role="tabpanel" aria-labelledby="{{ $template->slug }}"
                                tabindex="0">

                                <form action="{{ route('admin.email-template.update') }}" method="POST">
                                    @csrf
                                    <div id="editor">
                                        <h4>{{ $template->name }}</h4>
                                        <input type="hidden" name="template[id]" value="{{ $template->id }}">
                                        <div class="mb-4">
                                            <label for="Variables">{{ localize('Variables') }}</label>
                                            <span><strong>{{ $template->variables }}</strong></span>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-lg-8">
                                                <div class="mb-4">
                                                    <label for="subject" class="form-label">{{ localize('Subject') }}
                                                        <x-required-star /></label>
                                                    <input class="form-control" type="text" id="subject"
                                                        name="template[subject]"
                                                        placeholder="{{ localize('Type Subject') }}"
                                                        value="{{ $template->subject }}" required>
                                                    <x-error :name="'subject'" />

                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <label for="subject" class="form-label">
                                                    <input type="checkbox" name="template[is_active]"
                                                        class="form-check-input me-2"
                                                        @if ($template->is_active) checked @endif
                                                        value="{{ $template->id }}"> {{ localize('Is Active ?') }}</label>

                                            </div>
                                        </div>

                                        <textarea name="template[code]" id="content" class="editor form-control" cols="30" rows="10">
                                        {{ $template->code }}
                                        </textarea>
                                    </div>
                                    <div class="d-flex justify-content-center mt-4">
                                        <button class="btn btn-primary" onclick="showSelectedFilePreview()"
                                            data-bs-dismiss="offcanvas">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endforeach

                    </div>

                </div>
                <div class="col-xl-3">
                    <div class="card tt-sticky-sidebar">
                        <div class="card-body">
                            <h5 class="mb-4">{{ localize('Template Information') }}</h5>
                            <div class="tt-vertical-step-two">
                                <ul class="list-unstyled" id="myTab" role="tablist">
                                    @foreach ($templates as $template)
                                        <li>
                                            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                id="{{ $template->slug }}" data-bs-toggle="tab"
                                                data-bs-target="#{{ $template->slug }}-pane" type="button" role="tab"
                                                aria-controls="{{ $template->slug }}-pane"
                                                aria-selected="true">{{ $template->name }}</a>
                                        </li>
                                    @endforeach


                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        "use strict";

        $(document).ready(function() {
            $('div.note-editable').height(500);

        });

    </script>
@endsection
