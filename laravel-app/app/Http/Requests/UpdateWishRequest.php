<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateWishRequest extends FormRequest
{
    use MoneyValidationTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return array_merge([
            'title' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'string', 'max:500'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ], $this->getMoneyRules());
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return array_merge([
            'title.required' => __('validation.wish.title.required'),
            'title.max' => __('validation.wish.title.max'),
            'url.url' => __('validation.wish.url.url'),
            'url.max' => __('validation.wish.url.max'),
            'image.max' => __('validation.wish.image.max'),
            'price.numeric' => __('validation.wish.price.numeric'),
            'price.min' => __('validation.wish.price.min'),
            'price.max' => __('validation.wish.price.max'),
        ], $this->getMoneyMessages());
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $price = $this->input('price');
        $normalizedPrice = $this->validatePrice($price);
        $currency = $this->getCurrencyForValidation();

        $title = $this->input('title');
        $url = $this->input('url');
        $image = $this->input('image');

        $this->merge([
            'title' => $title ? trim($title) : null,
            'url' => $url ? trim($url) : null,
            'image' => $image ? trim($image) : null,
            'price' => $normalizedPrice,
            'currency' => $currency,
        ]);
    }

    /**
     * Get validated and processed data for the wish.
     *
     * @return array{title: string, url: string|null, image: string|null, price: float|null}
     */
    public function getWishData(): array
    {
        return [
            'title' => $this->validated('title'),
            'url' => $this->validated('url'),
            'image' => $this->validated('image'),
            'price' => $this->validated('price'),
        ];
    }

    /**
     * Get the currency for this wish.
     *
     * @return string|null
     */
    public function getWishCurrency(): ?string
    {
        return $this->validated('currency');
    }

    /**
     * Check if request has valid Money data.
     *
     * @return bool
     */
    public function hasValidMoneyData(): bool
    {
        $price = $this->validated('price');
        $currency = $this->validated('currency');

        return $this->validateMoneyPrice($price, $currency);
    }
}
