<?php

namespace App\Http\Requests\Post;

use App\Constants\AppConstants;
use App\Dto\PostFilterDto;
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
            'itemsPerPage' => ['required', 'integer', 'between:' . AppConstants::ITEMS_PER_PAGE['min'] . ',' . AppConstants::ITEMS_PER_PAGE['max']],
            'page' => ['required', 'integer', 'min:1'],
            'title' => ['sometimes', 'nullable', 'string'],
            'content' => ['sometimes', 'nullable', 'string'],
            'tags' => ['sometimes', 'nullable', 'string', Rule::in(AppConstants::TAGS)],
            'sortBy' => ['sometimes', 'nullable', 'string', Rule::in(AppConstants::SORTABLE_FIELDS)],
            'orderBy' => ['sometimes', 'nullable', 'string', Rule::in(AppConstants::SORT_ORDER_OPTIONS)],
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
