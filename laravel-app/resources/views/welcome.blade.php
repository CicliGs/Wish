@extends('layouts.app')

@section('content')
<div class="welcome-hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="welcome-title">{{ __('messages.welcome_title') }}</h1>
                <p class="welcome-subtitle">{{ __('messages.welcome_subtitle') }}</p>
                <div class="mt-4">
                    <a href="{{ route('register') }}" class="btn btn-light btn-lg me-2 welcome-btn">{{ __('messages.register') }}</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg welcome-btn">{{ __('messages.login') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h4 class="welcome-section-title">{{ __('messages.how_it_works') }}</h4>
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item welcome-step">
                            <span class="fw-semibold"><i class="bi bi-person-plus welcome-step-icon"></i> {{ __('messages.step_register') }}</span>
                        </li>
                        <li class="list-group-item welcome-step">
                            <span class="fw-semibold"><i class="bi bi-gift welcome-step-icon gift"></i> {{ __('messages.step_add_wishes') }}</span>
                        </li>
                        <li class="list-group-item welcome-step">
                            <span class="fw-semibold"><i class="bi bi-link-45deg welcome-step-icon link"></i> {{ __('messages.step_share_link') }}</span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item welcome-step">
                            <span class="fw-semibold"><i class="bi bi-bookmark-heart welcome-step-icon bookmark"></i> {{ __('messages.step_friends_reserve') }}</span>
                        </li>
                        <li class="list-group-item welcome-step">
                            <span class="fw-semibold"><i class="bi bi-emoji-smile welcome-step-icon smile"></i> {{ __('messages.step_get_gifts') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            @auth
                <div class="text-center mt-4">
                    <a href="{{ route('wish-lists.index') }}" class="btn btn-dark btn-lg welcome-btn">{{ __('messages.go_to_my_lists') }}</a>
                </div>
            @endauth
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="welcome-section-title">Примеры желаний</h4>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card welcome-card">
                <img src="{{ asset('images/69502621.jpg') }}" class="card-img-top welcome-card-img" alt="Книга">
                <div class="card-body">
                    <h5 class="welcome-card-title">{{ __('messages.example_book') }}</h5>
                    <p class="welcome-card-text">{!! __('messages.example_price', ['price' => '35 BYN']) !!}</p>
                    <a href="https://www.labirint.ru/books/123456/" target="_blank" class="btn btn-outline-dark btn-sm welcome-card-btn">{{ __('messages.wish_url') }}</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card welcome-card">
                <img src="{{ asset('images/1478273.jpg') }}" class="card-img-top welcome-card-img" alt="Наушники">
                <div class="card-body">
                    <h5 class="welcome-card-title">{{ __('messages.example_headphones') }}</h5>
                    <p class="welcome-card-text">{!! __('messages.example_price', ['price' => '200 BYN']) !!}</p>
                    <a href="https://www.ozon.ru/product/besprovodnye-naushniki-654321/" target="_blank" class="btn btn-outline-dark btn-sm welcome-card-btn">{{ __('messages.wish_url') }}</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card welcome-card">
                <img src="{{ asset('images/catan-00-1024x1024-wm.jpg') }}" class="card-img-top welcome-card-img" alt="Настольная игра">
                <div class="card-body">
                    <h5 class="welcome-card-title">{{ __('messages.example_boardgame') }}</h5>
                    <p class="welcome-card-text">{!! __('messages.example_price', ['price' => '100 BYN']) !!}</p>
                    <a href="https://www.mosigra.ru/games/kolonizatory/" target="_blank" class="btn btn-outline-dark btn-sm welcome-card-btn">{{ __('messages.wish_url') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
@endpush
@endsection
