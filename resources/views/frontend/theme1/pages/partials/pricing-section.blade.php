  <!-- Pricing Section -->
  @php
      $yearlyCounter = \App\Models\Subscriptionpackage::isActive()->where('package_type', 'yearly')->count();
      $lifetimeCounter = \App\Models\Subscriptionpackage::isActive()->where('package_type', 'lifetime')->count();
      $prepaidCounter = \App\Models\Subscriptionpackage::isActive()->where('package_type', 'prepaid')->count();
  @endphp
  <section class="pricing-section-5 section-space-top section-space-sm-bottom">
      <div class="container">
          <div class="row justify-content-center">
              <div class="col-lg-8">
                  <div class="text-center">
                      <span
                          class="rounded-1 bg-primary-key bg-opacity-2 clr-white fs-12 fw-bold px-4 py-2 d-inline-block mb-4 fadeIn_bottom">
                          {{ getSetting('system_title') }}
                      </span>
                      <h3 class="clr-neutral-90 fw-bold animate-line-3d">{{ localize('WriteBot Subscription Plans') }}
                      </h3>
                  </div>
              </div>
          </div>
          <div class="pricing- mt-10 d-flex justify-content-center overflow-hidden">
              <ul class="pricing-5-list nav list list-sm-row align-items-center gap-3 fadeIn_bottom" role="tablist">

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
      <img src="{{ asset('public/frontend/theme1/') }}/assets/img/pricing-section-5-shape-top.png" alt="image"
          class="img-fluid pricing-section-5-shape pricing-section-5-shape-top">
      <img src="{{ asset('public/frontend/theme1/') }}/assets/img/pricing-section-5-shape-left.png" alt="image"
          class="img-fluid pricing-section-5-shape pricing-section-5-shape-left">
      <img src="{{ asset('public/frontend/theme1/') }}/assets/img/pricing-section-5-shape-right.png" alt="image"
          class="img-fluid pricing-section-5-shape pricing-section-5-shape-right">
  </section>
  <!-- /Pricing Section -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample" data-bs-scroll="true">
    <div class="offcanvas-header border-bottom">
      <h6 class="mb-0">{{localize('All Templates')}}</h6>
      <button type="button" class="btn btn-sm bg-primary-90 :bg-primary-50 clr-primary-30 :clr-primary-95 rounded"
        data-bs-dismiss="offcanvas" aria-label="Close"> {{localize('Close')}} </button>
    </div>
    <div class="offcanvas-body package-template-contents" id="package-template-contents">
      
    </div>
  </div>