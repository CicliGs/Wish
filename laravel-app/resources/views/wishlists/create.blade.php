@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center wishlist-container py-5" style="min-height: 90vh;">
    <div class="wishlist-card">
        <h1 class="wishlist-title">{{ __('messages.create_list') }}</h1>
        <form action="{{ route('wish-lists.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="title" class="form-label wishlist-form-label">{{ __('messages.list_title') }}</label>
                <input type="text" name="title" id="title" class="form-control wishlist-form-control" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label wishlist-form-label">{{ __('messages.list_description') }} ({{ __('messages.optional') }})</label>
                <textarea name="description" id="description" class="form-control wishlist-form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="currency" class="form-label wishlist-form-label">{{ __('messages.wishlist_currency') }}</label>
                <select name="currency" id="currency" class="form-control wishlist-form-control" required>
                    @foreach(App\Models\WishList::getSupportedCurrencies() as $currency)
                        <option value="{{ $currency }}" {{ $currency === 'BYN' ? 'selected' : '' }}>
                            @switch($currency)
                                @case('BYN')
                                    🇧🇾 {{ __('messages.currency_byn') }}
                                    @break
                                @case('USD')
                                    🇺🇸 {{ __('messages.currency_usd') }}
                                    @break
                                @case('EUR')
                                    🇪🇺 {{ __('messages.currency_eur') }}
                                    @break
                                @case('RUB')
                                    🇷🇺 {{ __('messages.currency_rub') }}
                                    @break
                                @default
                                    {{ $currency }}
                            @endswitch
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">{{ __('messages.wishlist_currency_description') }}</small>
            </div>
            <button type="submit" class="btn btn-dark w-100 wishlist-btn">{{ __('messages.create') }}</button>
            <div class="mt-3 text-center">
                <a href="{{ route('wish-lists.index') }}" class="wishlist-link">{{ __('messages.back_to_lists') }}</a>
            </div>
        </form>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="{{ asset('css/wishlist.css') }}">
@endpush
@endsection
