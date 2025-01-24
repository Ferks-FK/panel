<?php

namespace App\Http\Requests\Api\Application;

use App\Helpers\ApiAcl;
use Illuminate\Foundation\Http\FormRequest;

class BaseApplicationApiRequest extends FormRequest
{
    /**
     * The resource that the request is for.
     *
     * @var string|null
     */
    protected ?string $resource = null;

    /**
     * The ability that the request is for.
     *
     * @var string
     */
    protected string $ability = ApiAcl::READ;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (is_null($this->resource)) {
            throw new \Exception('Resource not defined for request.');
        }

        $token = $this->user()->currentAccessToken();

        return ApiAcl::check($token, $this->resource, $this->ability);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
