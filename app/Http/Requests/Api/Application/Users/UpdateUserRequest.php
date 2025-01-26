<?php

namespace App\Http\Requests\Api\Application\Users;

use App\Helpers\ApiAcl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    protected ?string $resource = 'users';

    protected string $ability = ApiAcl::WRITE;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:4|max:30',
            'email' => 'required|string|email',
            'credits' => 'sometimes|numeric|min:0|max:1000000',
            'server_limit' => 'sometimes|numeric|min:0|max:1000000',
            'role' => 'sometimes|string|exists:roles,name',
            'password' => 'sometimes|string|min:8|max:191',
            'suspended' => 'sometimes|boolean',
        ];
    }
}
