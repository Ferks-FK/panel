<?php

namespace App\Http\Requests\Api\Application\Servers;

use App\Helpers\ApiAcl;
use App\Http\Requests\Api\Application\BaseApplicationApiRequest;

class GetServersRequest extends BaseApplicationApiRequest
{
    protected ?string $resource = 'servers';

    protected string $ability = ApiAcl::READ;
}
