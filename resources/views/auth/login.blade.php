 <!doctype html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo-light-icon.png') }}">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    @yield('style')
</head>

<body>

    <style type="text/css">
        .form-group.default-admin {
            padding: 10px;
            font-size: 14px;
            color: #000;
            font-weight: 600;
            border-radius: 10px;
            box-shadow: 0 0px 6px 0px rgba(0, 0, 0, 0.5);
            margin: 20px 10px 10px 10px;
        }

        .form-group.default-admin .crediantials-field {
            position: relative;
            padding-right: 15px;
            text-align: left;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .form-group.default-admin .crediantials-field>a {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            margin: auto;
            height: 20px;
        }

        .login-register {
            background-color: #000000;
        }

        .btn-primary,
        .btn-primary.disabled,
        .btn-primary:hover,
        .btn-primary.disabled:hover {
            background: #000000;
            border: 1px solid #000000;
        }
    </style>

    @php
        $admin_panel_color = @$_COOKIE['admin_panel_color'];
    @endphp

    @if ($admin_panel_color)
        <style type="text/css">
            a,
            a:hover,
            a:focus {
                color: {{ $admin_panel_color }};
            }

            .btn-primary,
            .btn-primary.disabled,
            .btn-primary:hover,
            .btn-primary.disabled:hover {
                background: {{ $admin_panel_color }};
                border: 1px solid {{ $admin_panel_color }};
            }

            [type="checkbox"]:checked+label::before {
                border-right: 2px solid {{ $admin_panel_color }};
                border-bottom: 2px solid {{ $admin_panel_color }};
            }

            .form-material .form-control,
            .form-material .form-control.focus,
            .form-material .form-control:focus {
                background-image: linear-gradient({{ $admin_panel_color }}, {{ $admin_panel_color }}), linear-gradient(rgba(120, 130, 140, 0.13), rgba(120, 130, 140, 0.13));
            }

            .btn-primary.active,
            .btn-primary:active,
            .btn-primary:focus,
            .btn-primary.disabled.active,
            .btn-primary.disabled:active,
            .btn-primary.disabled:focus,
            .btn-primary.active.focus,
            .btn-primary.active:focus,
            .btn-primary.active:hover,
            .btn-primary.focus:active,
            .btn-primary:active:focus,
            .btn-primary:active:hover,
            .open>.dropdown-toggle.btn-primary.focus,
            .open>.dropdown-toggle.btn-primary:focus,
            .open>.dropdown-toggle.btn-primary:hover,
            .btn-primary.focus,
            .btn-primary:focus,
            .btn-primary:not(:disabled):not(.disabled).active:focus,
            .btn-primary:not(:disabled):not(.disabled):active:focus,
            .show>.btn-primary.dropdown-toggle:focus {
                background: {{ $admin_panel_color }};
                border-color: {{ $admin_panel_color }};
                box-shadow: 0 0 0 0.2rem {{ $admin_panel_color }};
            }

            .login-register {
                background-color: {{ $admin_panel_color }};
            }

            .text-primary {
                color: {{ $admin_panel_color }};
            }
        </style>
    @endif

    <section id="wrapper">

        <div class="auth-wrapper">

            <div class="auth-wrapper-left">

            </div>

            <div class="auth-wrapper-right">

                <div class="auth-wrapper-form">
                    <div class="admin-logo mb-5">
                        <img src="{{ asset('images/logo_web.png') }}"
                            onerror="this.onerror=null; this.src='{{ asset('images/logo_web.png') }}';">
                    </div>

                    @if (count($errors) > 0)
                        @foreach ($errors->all() as $message)
                            <div class="alert alert-danger display-hide">
                                <button class="close" data-close="alert"></button>
                                <span>{{ $message }}</span>
                            </div>
                        @endforeach
                    @endif

                    <form class="form-horizontal form-material" method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="auth-header">
                            <div class="mb-4">
                                <h2 class="title text-dark mb-3">{{ __('Admin Login') }}</h2>
                                <p class="text-muted">{{ trans('lang.login_text') }}</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="input-label text-capitalize">{{ __('Email Address') }}</label>
                            <input class="form-control" placeholder="{{ __('Email Address') }}" id="email"
                                type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                                value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="input-label text-capitalize">{{ __('Password') }}</label>
                            <div class="input-group">
                            <input id="password" placeholder="{{ __('Password') }}" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password" required
                                autocomplete="current-password">
                            <span class="password-toggle-icon"
                                style="position: absolute; right: 15px; top: 10px; cursor: pointer; z-index: 5;">
                                <i class="mdi mdi-eye" id="togglePasswordIcon"></i>
                            </span>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div>

                        <div class="form-group text-center mb-0">
                            <button type="submit"
                                class="btn btn-dark btn-lg btn-block text-uppercase waves-effect waves-light btn btn-primary">{{ __('Login') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </section>

    <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-storage-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>
    <script src="{{ asset('js/crypto-js.js') }}"></script>
    <script src="{{ asset('js/jquery.cookie.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.js') }}"></script>

    <script type="text/javascript">
        var database = firebase.firestore();

        $(document).ready(function() {

            database.collection('settings').doc("globalSettings").get().then(async function(snapshots) {
                var globalSettings = snapshots.data();
                admin_panel_color = globalSettings.admin_panel_color;
                setCookie('admin_panel_color', admin_panel_color, 365);
                $('.login-register').css({
                    'background-color': admin_panel_color
                });
            })

            database.collection('sections').where('isActive', '==', true).orderBy('order').get().then(async function(snapshots) {
                const firstSection = snapshots.docs[0].data();
                const firstSectionId = snapshots.docs[0].id;
                const firstServiceType = firstSection.serviceTypeFlag;
                setCookie('section_id', firstSectionId, 1);
                setCookie('service_type', firstServiceType, 1);
            });
        });

        function setCookie(cname, cvalue, exdays) {
            const d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            let expires = "expires=" + d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }
        $(document).ready(function() {
            const icon = $('#togglePasswordIcon');
            icon.removeClass('mdi-eye').addClass('mdi-eye-off');
            $('.password-toggle-icon').on('click', function() {
                const passwordField = $('#password');


                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('mdi-eye-off').addClass('mdi-eye');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('mdi-eye').addClass('mdi-eye-off');
                }
            });
        });
    </script>

</body>

</html>
