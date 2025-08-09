<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Виш-лист</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="/">
            <img src="{{ asset('images/logo2.png') }}" alt="Виш-лист">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('wish-lists.*') ? 'active' : '' }}" href="{{ route('wish-lists.index') }}">
                            <i class="bi bi-heart-fill me-1"></i>
                            {{ __('messages.wish_lists') }}
                        </a>
                    </li>
                    <li class="nav-item position-relative">
                        <a class="nav-link {{ request()->routeIs('friends.*') ? 'active' : '' }}" href="{{ route('friends.index') }}">
                            <i class="bi bi-people-fill me-1"></i>
                            {{ __('messages.friends') }}
                            @if(isset($incomingRequestsCount) && $incomingRequestsCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle notification-badge rounded-circle" style="font-size:0.7em; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center;">
                                    <span class="visually-hidden">{{ __('messages.incoming_requests') }}</span>
                                    {{ $incomingRequestsCount > 9 ? '9+' : $incomingRequestsCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}" href="{{ route('profile') }}">
                            <i class="bi bi-person-circle me-1"></i>
                            {{ __('messages.profile') }}
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="currencyDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-currency-exchange me-1"></i>
                            {{ Auth::user()->currency }}
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="currencyDropdown">
                            @foreach(App\Models\User::getSupportedCurrencies() as $currency)
                                <li>
                                    <form method="POST" action="{{ route('settings.update') }}" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="currency" value="{{ $currency }}">
                                        <button type="submit" class="dropdown-item {{ Auth::user()->currency === $currency ? 'active' : '' }}">
                                            @switch($currency)
                                                @case('BYN')
                                                    <i class="bi bi-currency-dollar me-2"></i>
                                                    @break
                                                @case('USD')
                                                    <i class="bi bi-currency-dollar me-2"></i>
                                                    @break
                                                @case('EUR')
                                                    <i class="bi bi-currency-euro me-2"></i>
                                                    @break
                                                @case('RUB')
                                                    <i class="bi bi-currency-ruble me-2"></i>
                                                    @break
                                                @default
                                                    <i class="bi bi-currency-exchange me-2"></i>
                                            @endswitch
                                            {{ $currency }}
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-globe language-icon"></i>
                            {{ __('messages.language') }}
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('language.switch', 'ru') }}">
                                    <i class="bi bi-flag me-2"></i>{{ __('messages.russian') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('language.switch', 'en') }}">
                                    <i class="bi bi-flag me-2"></i>{{ __('messages.english') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                            @csrf
                            <button class="btn btn-link nav-link" type="submit">
                                <i class="bi bi-box-arrow-right me-1"></i>
                                {{ __('messages.logout') }}
                            </button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            {{ __('messages.login') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">
                            <i class="bi bi-person-plus me-1"></i>
                            {{ __('messages.register') }}
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-globe language-icon"></i>
                            {{ __('messages.language') }}
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('language.switch', 'ru') }}">
                                    <i class="bi bi-flag me-2"></i>{{ __('messages.russian') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('language.switch', 'en') }}">
                                    <i class="bi bi-flag me-2"></i>{{ __('messages.english') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="alert-message">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="alert-message">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>

<main>
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/modal-fix.js') }}"></script>
<script src="{{ asset('js/currency-selector.js') }}"></script>
@stack('scripts')
</body>
</html>
