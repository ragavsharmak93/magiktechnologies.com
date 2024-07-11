<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Modern AI Landing HTML Template">
    <meta name="keywords" content="bootstrap 5, saas, landing page">
    <meta name="author" content="Themetags">
    <title> @yield('title', getSetting('system_title'))</title>
    <link rel="stylesheet" href="{{ asset('public/frontend/theme1/') }}/assets/css/fonts/ff-1.css">
    <link rel="stylesheet" href="{{ asset('public/frontend/theme1/') }}/assets/css/fonts/ff-3.css">
    <link rel="stylesheet" href="{{ asset('public/frontend/theme1/') }}/assets/css/fonts/bootstrap-icons.css">
    <link rel="icon" href="{{ asset('public/frontend/theme1/') }}/assets/img/favicon.png" type="image/png">
    <link rel="stylesheet" href="{{ asset('public/frontend/theme1/') }}/assets/css/plugins.min.css">
    <link rel="stylesheet" href="{{ asset('public/frontend/theme1/') }}/assets/css/style.min.css">
</head>

<body class="bg-black body-clip">

    @if (getSetting('enable_cookie_consent') == '1')
        <div class="cookie-alert">
            <div class="p-3 bg-white rounded shadow-lg">
                <div class="mb-3">
                    {!! getSetting('cookie_consent_text') !!}
                </div>
                <button class="btn btn-primary cookie-accept">
                    {{ localize('I Understood') }}
                </button>
            </div>
        </div>
    @endif
    <!-- preloader -->

    @if (getSetting('enable_preloader') != '0')
        <!-- preloader -->
        <div class="preloader">
            <div class="preloader__img">
                <img src="{{ uploadedAsset(getSetting('navbar_logo_dark')) }}" alt="image">
            </div>
        </div>
    @endif
    <!-- preloader End -->
    @yield('auth-content')
    <!-- scrpts -->
    <script src="{{ asset('public/frontend/theme1/') }}/assets/js/plugins.js"></script>
    <script src="{{ asset('public/frontend/theme1/') }}/assets/js/app.js"></script>
    <script src="{{ asset('public/frontend/theme1/') }}/assets/js/animatedt-title.js"></script>
    <script src="{{ staticAsset('frontend/default/assets/js/vendors/jquery-3.7.0.min.js') }}"></script>
    @yield('scripts')
    <script>
        "use strict";

        // copyAdmin
        function copyAdmin() {
            $('#email').val('admin@themetags.com');
            $('#password').val('123456');
        }

        // copyCustomer
        function copyCustomer() {
            $('#email').val('customer@themetags.com');
            $('#password').val('123456');
        }

        // change input to phone
        function handleLoginWithPhone() {
            $('.login_with').val('phone');

            $('.login-email').addClass('d-none');
            $('.login-email input').prop('required', false);

            $('.login-phone').removeClass('d-none');
            $('.login-phone input').prop('required', true);
        }

        // change input to email
        function handleLoginWithEmail() {
            $('.login_with').val('email');
            $('.login-email').removeClass('d-none');
            $('.login-email input').prop('required', true);

            $('.login-phone').addClass('d-none');
            $('.login-phone input').prop('required', false);
        }


        // disable login button
        function handleSubmit() {
            $('#login-form').on('submit', function(e) {
                $('.sign-in-btn').prop('disabled', true);
            });
        }
    </script>
</body>

</html>
