<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\WishList;
use App\Support\MoneyHelper;
use Exception;
use Illuminate\Validation\Rule;

trait MoneyValidationTrait
{
    protected function getMoneyRules(): array
    {
        return [
            'currency' => [
                'nullable',
                'string',
                Rule::in(array_keys(MoneyHelper::getSupportedCurrencies()))
            ],
        ];
    }

    protected function getMoneyMessages(): array
    {
        return [
            'currency.in' => __('validation.currency.invalid'),
        ];
    }

    protected function validatePrice(?string $price): ?float
    {
        if (empty($price)) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9.-]/', '', $price);

        if (!is_numeric($cleaned)) {
            return null;
        }

        $floatValue = (float) $cleaned;

        return $floatValue >= 0 ? $floatValue : null;
    }

    protected function getCurrency(): ?string
    {
        if ($this->has('currency') && $currency = $this->input('currency')) {
            return $currency;
        }

        if ($wishList = $this->route('wishList')) {
            return $wishList instanceof WishList ? $wishList->currency : null;
        }

        return auth()->user()?->currency;
    }

    protected function validateMoneyPrice(?float $price, ?string $currency): bool
    {
        if ($price === null) {

            return true;
        }

        if ($price <= 0) {

            return false;
        }

        if (!$currency || !MoneyHelper::isValidCurrency($currency)) {

            return true;
        }

        try {
            MoneyHelper::create($price, $currency);

            return true;
        } catch (Exception) {

            return false;
        }
    }
}
