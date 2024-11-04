<?php

namespace App\Http\Requests\Post;

use App\Dto\PostUpdateDto;
use App\Enums\PostEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:65535'],
            'tags' => ['required', 'array'],
            'tags.*' => ['string', Rule::in(PostEnum::tags())],
        ];
    }

    /**
     * Get a DTO (Data Transfer Object) from the validated request data.
     *
     * @return PostUpdateDto A DTO with the validated post update data.
     */
    public function getDto(): PostUpdateDto
    {
        return PostUpdateDto::make($this->validated());
    }
}
