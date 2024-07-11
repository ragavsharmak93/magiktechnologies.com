<!--footer section start-->
<footer class="tt-footer bg-dark py-3 mt-auto bg-image-header">
    <div class="container">
        <div class="row g-3 align-items-center">
            <div class="col-md-4 order-last order-md-first">
                <div class="copyright text-center text-md-start">
                    <p class="fs-md mb-0">
                        {!! systemSettingsLocalization('copyright_text') !!}
                    </p>
                </div>
            </div>
            <div class="col-md-5">
                <div class="d-flex justify-content-center">
                    @php
                        $quick_links = getSetting('quick_links') != null ? json_decode(getSetting('quick_links')) : [];
                        $pages = \App\Models\Page::whereIn('id', $quick_links)->get();
                    @endphp
                    @foreach ($pages as $page)
                        <a href="{{ route('home.pages.show', $page->slug) }}"
                            class="fs-md">{{ $page->collectLocalization('title') }}</a>
                    @endforeach
                </div>
            </div>
            <div class="col-md-3">
                <form action="{{ route('subscribe.store') }}" method="POST">
                    @csrf
                    <div class="input-group text-end">
                        <input class="form-control" placeholder="{{ localize('Enter Email Address') }}" type="email"
                            name="email" required>
                        <button type="submit" class="btn btn-primary py-2">{{ localize('Subscribe') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</footer>
<!--footer section end-->
<script></script>