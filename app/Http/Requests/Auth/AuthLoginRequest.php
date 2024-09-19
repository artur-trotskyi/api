<?php

namespace App\Http\Requests\Auth;

use App\Http\Resources\ErrorResource;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class AuthLoginRequest extends FormRequest
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
            'email' => ['required', 'max:255', 'email', 'exists:users,email'],
            'password' => ['required', 'max:255'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     *
     */
    protected function failedValidation(Validator $validator): void
    {
        $errorData = ['errors' => $validator->errors()->toArray()];
        $resource = new ErrorResource(
            $errorData,
            'Validation error',
            Response::HTTP_UNPROCESSABLE_ENTITY
        );

        throw new HttpResponseException($resource->toResponse(request()));
    }
}
