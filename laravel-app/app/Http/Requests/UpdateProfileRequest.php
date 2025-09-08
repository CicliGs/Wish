<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    private const MAX_NAME_LENGTH = 255;
    private const MAX_AVATAR_SIZE = 2048; // 2MB in KB

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
            'name' => 'required|string|max:' . self::MAX_NAME_LENGTH,
            'avatar' => 'nullable|image|max:' . self::MAX_AVATAR_SIZE,
        ];
    }
}
