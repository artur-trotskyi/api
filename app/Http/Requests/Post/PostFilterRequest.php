<?php

namespace App\Http\Requests\Post;

use App\Constants\AppConstants;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostFilterRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string'],
            'itemsPerPage' => ['required', 'integer', 'min:' . AppConstants::ALL],
            'page' => ['required', 'integer', 'min:1'],
            'title' => ['sometimes', 'nullable', 'string'],
            'content' => ['sometimes', 'nullable', 'string'],
            'sortBy' => ['sometimes', 'nullable', 'string'],
            'orderBy' => ['sometimes', 'nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }
}
