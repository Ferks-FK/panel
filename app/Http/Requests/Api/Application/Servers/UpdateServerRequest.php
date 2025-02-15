<?php

namespace App\Http\Requests\Api\Application\Servers;

use App\Helpers\ApiAcl;
use App\Http\Requests\Api\Application\BaseApplicationApiRequest;

class UpdateServerRequest extends BaseApplicationApiRequest
{
    protected ?string $resource = 'servers';

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
            'description' => 'sometimes|string|nullable|min:4|max:191',
            'suspended' => 'sometimes|boolean',
            'external_id' => 'sometimes|string|nullable|min:4|max:191',
            'user_id' => 'required|numeric|exists:users,id',
        ];
    }
}
