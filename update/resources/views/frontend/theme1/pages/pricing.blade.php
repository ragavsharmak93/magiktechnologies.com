@extends('frontend.theme1.layouts.master')

@section('title')
    {{ localize('Home') }} {{ getSetting('title_separator') }} {{ getSetting('system_title') }}
@endsection

@section('content')
    @php
        $yearlyCounter = \App\Models\Subscriptionpackage::isActive()->where('package_type', 'yearly')->count();
        $lifetimeCounter = \App\Models\Subscriptionpackage::isActive()->where('package_type', 'lifetime')->count();
        $prepaidCounter = \App\Models\Subscriptionpackage::isActive()->where('package_type', 'prepaid')->count();
    @endphp
    <section class="breadcrumb-section">
        <div class="breadcrumb-section-inner">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xxl-7 col-xl-8">
                        <div class="text-center">
                            <div class="d-inline-flex align-items-center py-2 px-4 bg-info-10 bg-opacity-3 rounded-1">
                                <span class="fs-12 clr-white">{{ getSetting('system_title') }}</span>
                            </div>
                            <h2 class="h3 fw-bold clr-neutral-90 mt-4">{{ localize('Our Subscription Packages') }} </h2>
                        </div>
                    </div>
                </div>
                <div class="pricing- mt-10 d-flex justify-content-center overflow-hidden">
                    <ul class="pricing-5-list nav list list-sm-row align-items-center gap-3 fadeIn_bottom">

                        <li>
                            <button type="button"
                                class="link nav-link active d-inline-flex py-2 px-3 fs-12 fw-bold clr-neutral-70 border-0 bg-transparent"
                                data-bs-toggle="tab" data-bs-target="#monthlyPricing"
                                aria-selected="true">{{ localize('Monthly') }}</button>
                        </li>


                        @if ($yearlyCounter > 0)
                            <li>
                                <button type="button"
                                    class="link nav-link d-inline-flex py-2 px-3 fs-12 fw-bold clr-neutral-70 border-0 bg-transparent"
                                    data-bs-toggle="tab" data-bs-target="#yearlyPricing"
                                    aria-selected="false">{{ localize('Yearly') }}</button>
                            </li>
                        @endif

                        @if ($lifetimeCounter > 0)
                            <li>
                                <button type="button"
                                    class="link nav-link d-inline-flex py-2 px-3 fs-12 fw-bold clr-neutral-70 border-0 bg-transparent"
                                    data-bs-toggle="tab" data-bs-target="#lifetimePricing"
                                    aria-selected="false">{{ localize('Lifetime') }}</button>
                            </li>
                            <li>
                                <button type="button"
                                    class="link nav-link d-inline-flex py-2 px-3 fs-12 fw-bold clr-neutral-70 border-0 bg-transparent"
                                    data-bs-toggle="tab" data-bs-target="#prepaidPricing"
                                    aria-selected="false">{{ localize('Prepaid') }}</button>
                            </li>
                        @endif

                    </ul>
                </div>
                <div class="tab-content mt-10">
                    <div class="tab-pane fade show active" id="monthlyPricing">
                        <div class="row gy-4">
                            @foreach ($packages as $package)
                                @if ($package->package_type == 'starter' || $package->package_type == 'monthly')
                                    <div class="col-sm-6 col-lg-4 col-xxl-3">
                                        @include('frontend.theme1.pages.partials.home.pricing-card', [
                                            'package' => $package,
                                        ])
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="tab-pane fade" id="yearlyPricing">
                        <div class="row gy-4">
                            @foreach ($packages as $package)
                                @if ($package->package_type == 'yearly')
                                    <div class="col-sm-6 col-lg-4 col-xxl-3">
                                        @include('frontend.theme1.pages.partials.home.pricing-card', [
                                            'package' => $package,
                                        ])
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="tab-pane fade" id="lifetimePricing">
                        <div class="row gy-4">
                            @foreach ($packages as $package)
                                @if ($package->package_type == 'lifetime')
                                    <div class="col-sm-6 col-lg-4 col-xxl-3">
                                        @include('frontend.theme1.pages.partials.home.pricing-card', [
                                            'package' => $package,
                                        ])
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="tab-pane fade" id="prepaidPricing">
                        <div class="row gy-4">
                            @foreach ($packages as $package)
                                @if ($package->package_type == 'prepaid')
                                    <div class="col-sm-6 col-lg-4 col-xxl-3">
                                        @include('frontend.theme1.pages.partials.home.pricing-card', [
                                            'package' => $package,
                                        ])
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <img src="{{ asset('public/frontend/theme1/') }}/assets/img/breadcrumb-shape-top.png" alt="image"
            class="img-fluid breadcrumb-shape breadcrumb-shape-top">
        <img src="{{ asset('public/frontend/theme1/') }}/assets/img/breadcrumb-shape-left.png" alt="image"
            class="img-fluid breadcrumb-shape breadcrumb-shape-left">
        <img src="{{ asset('public/frontend/theme1/') }}/assets/img/breadcrumb-shape-right.png" alt="image"
            class="img-fluid breadcrumb-shape breadcrumb-shape-right">
        <img src="{{ asset('public/frontend/theme1/') }}/assets/img/breadcrumb-shape-line-left.png" alt="image"
            class="img-fluid breadcrumb-shape breadcrumb-shape-line-left">
        <img src="{{ asset('public/frontend/theme1/') }}/assets/img/breadcrumb-shape-line-right.png" alt="image"
            class="img-fluid breadcrumb-shape breadcrumb-shape-line-right">
    </section>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample" data-bs-scroll="true">
        <div class="offcanvas-header border-bottom">
            <h6 class="mb-0">{{ localize('All Templates') }}</h6>
            <button type="button" class="btn btn-sm bg-primary-90 :bg-primary-50 clr-primary-30 :clr-primary-95 rounded"
                data-bs-dismiss="offcanvas" aria-label="Close"> {{ localize('Close') }} </button>
        </div>
        <div class="offcanvas-body package-template-contents" id="package-template-contents">

        </div>
    </div>
 
    <div class="pricing-faq-section section-space-top section-space-bottom">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <h3 class="clr-neutral-90 fw-extrabold animate-line-3d"> {{localize('Frequently Asked Questions')}} 👍 </h3>
                    <p class="mb-6 fw-semibold clr-neutral-80 max-text-32 animate-text-from-right"> {{localize('Have a question that is
                        not answered? You can contact us at')}} </p>
                    <a href="{{ route('home.pages.contactUs') }}"
                        class="link d-inline-block py-3 px-6 rounded-pill bg-neutral-10 :bg-primary-key clr-white fs-14 fw-semibold fadeIn_bottom">
                        {{ localize('Have a question? Submit a Ticket') }} </a>
                </div>
                <div class="col-lg-7">
                    <div class="accordion custom-accordion custom-accordion--faq mb-8 fadeIn_bottom" id="faqAccordionOne">
                       
                        @if (!empty($faqs))
                            @foreach ($faqs as $faq)
                                <div class="accordion-item top-shadow rounded-2 gradient-card mb-4">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button fs-16 fw-bold collapsed rounded-2" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#faqAccordion2{{$faq->id}}" aria-expanded="false">
                                            {{ $faq->collectLocalization('question') }}
                                        </button>
                                    </h2>
                                    <div id="faqAccordion2{{$faq->id}}" class="accordion-collapse collapse {{$loop->first ? 'show':''}}"
                                        data-bs-parent="#faqAccordionOne">
                                        <div class="accordion-body">
                                            {{ $faq->collectLocalization('answer') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                       
                    </div>
                </div>
            </div>

        </div>
        <img src="{{ asset('public/frontend/theme1/') }}/assets/img/pricing-faq-top-left.png" alt="image"
            class="img-fluid pricing-fag-shape pricing-fag-shape-top-left">
        <img src="{{ asset('public/frontend/theme1/') }}/assets/img/pricing-faq-top-right.png" alt="image"
            class="img-fluid pricing-fag-shape pricing-fag-shape-top-right">
        <img src="{{ asset('public/frontend/theme1/') }}/assets/img/pricing-faq-bottom-right.png" alt="image"
            class="img-fluid pricing-fag-shape pricing-fag-shape-bottom-right">
    </div>
@endsection
