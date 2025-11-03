<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\WishList;
use App\Support\MoneyService;
use Exception;
use Illuminate\Validation\Rule;

trait MoneyValidationTrait
{
    /**
     * Get money validation rules.
     */
    protected function getMoneyRules(): array
    {
        return [
            'currency' => [
                'nullable',
                'string',
                Rule::in(array_keys(MoneyService::getSupportedCurrencies()))
            ],
        ];
    }

    /**
     * Get money validation error messages.
     */
    protected function getMoneyMessages(): array
    {
        return [
            'currency.in' => __('validation.currency.invalid'),
        ];
    }

    /**
     * Validate and normalize price input.
     */
    protected function validatePrice(?string $price): ?float
    {
        if ($price === null || $price === '') {
            return null;
        }

        $cleaned = preg_replace('/[^0-9.-]/', '', $price);

        if (!is_numeric($cleaned)) {
            return null;
        }

        $floatValue = (float) $cleaned;

        return $floatValue >= 0 ? $floatValue : null;
    }

    /**
     * Get currency from request, route, or user preferences.
     */
    protected function getCurrency(): ?string
    {
        $currency = $this->input('currency');
        if ($this->has('currency') && $currency) {
            return $currency;
        }

        $wishList = $this->route('wishList');
        if ($wishList instanceof WishList) {
            return $wishList->currency;
        }

        $user = $this->user();
        if ($user) {
            return $user->currency;
        }

        return null;
    }

    /**
     * Validate money price with currency.
     */
    protected function validateMoneyPrice(?float $price, ?string $currency): bool
    {
        if ($price === null) {
            return true;
        }

        if ($price <= 0) {
            return false;
        }

        if ($currency && MoneyService::isValidCurrency($currency)) {
            try {
                MoneyService::create($price, $currency);
                return true;
            } catch (Exception) {
                return false;
            }
        }

        return true;
    }
}
