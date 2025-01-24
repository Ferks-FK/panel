<?php

namespace App\Http\Requests\Api\Application\Users;

use App\Helpers\ApiAcl;
use App\Http\Requests\Api\Application\BaseApplicationApiRequest;

class CreateUserRequest extends BaseApplicationApiRequest
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
            'name' => 'required|string|max:30|min:4|alpha_num|unique:users',
            'email' => 'required|string|email|max:64|unique:users',
            'password' => 'required|string|min:8|max:191',
        ];
    }
}
