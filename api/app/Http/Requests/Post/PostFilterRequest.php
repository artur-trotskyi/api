<?php

namespace App\Http\Requests\Post;

use App\Dto\PostFilterDto;
use App\Enums\PostEnum;
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
            'itemsPerPage' => ['required', 'integer', 'between:' . PostEnum::itemsPerPage()['min'] . ',' . PostEnum::itemsPerPage()['max']],
            'page' => ['required', 'integer', 'min:1'],
            'title' => ['sometimes', 'nullable', 'string'],
            'content' => ['sometimes', 'nullable', 'string'],
            'tags' => ['sometimes', 'nullable', 'string', Rule::in(PostEnum::tags())],
            'sortBy' => ['sometimes', 'nullable', 'string', Rule::in(PostEnum::sortableFields())],
            'orderBy' => ['sometimes', 'nullable', 'string', Rule::in(PostEnum::sortOrderOptions())],
        ];
    }

    /**
     * Get a DTO (Data Transfer Object) from the validated request data.
     *
     * @return PostFilterDto A DTO with the validated post filter data.
     */
    public function getDto(): PostFilterDto
    {
        return new PostFilterDto($this->validated());
    }
}
