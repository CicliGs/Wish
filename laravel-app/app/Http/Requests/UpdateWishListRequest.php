<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\WishList;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWishListRequest extends FormRequest
{
    private const MAX_TITLE_LENGTH = 255;
    private const MAX_DESCRIPTION_LENGTH = 1000;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:' . self::MAX_TITLE_LENGTH],
            'description' => ['sometimes', 'nullable', 'string', 'max:' . self::MAX_DESCRIPTION_LENGTH],
            'is_public' => ['sometimes', 'boolean'],
            'currency' => ['sometimes', 'required', 'string', 'in:' . implode(',', WishList::getSupportedCurrencies())],
        ];
    }
}
