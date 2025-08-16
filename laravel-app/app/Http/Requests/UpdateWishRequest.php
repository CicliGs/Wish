<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateWishRequest extends FormRequest
{
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'string', 'max:500'], // changed from 'url' to 'string'
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Название желания обязательно для заполнения.',
            'title.max' => 'Название не может быть длиннее 255 символов.',
            'url.url' => 'Ссылка должна быть валидным URL.',
            'url.max' => 'Ссылка не может быть длиннее 500 символов.',
            'image.max' => 'Ссылка на изображение не может быть длиннее 500 символов.',
            'price.numeric' => 'Цена должна быть числом.',
            'price.min' => 'Цена не может быть отрицательной.',
            'price.max' => 'Цена не может быть больше 999999.99.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim($this->title),
            'url' => $this->url ? trim($this->url) : null,
            'image' => $this->image ? trim($this->image) : null,
            'price' => $this->price ? (float) $this->price : null,
        ]);
    }
}
