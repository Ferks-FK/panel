<?php

namespace App\Http\Requests\Api\Application\Users;

use App\Helpers\ApiAcl;
use App\Http\Requests\Api\Application\BaseApplicationApiRequest;

class GetUsersRequest extends BaseApplicationApiRequest
{
    protected ?string $resource = 'users';

    protected string $ability = ApiAcl::READ;
}
