<?php

namespace App\Http\Requests\Api\Application\Servers;

use App\Helpers\ApiAcl;
use App\Http\Requests\Api\Application\BaseApplicationApiRequest;

class DeleteServerRequest extends BaseApplicationApiRequest
{
    protected ?string $resource = 'servers';

    protected string $ability = ApiAcl::WRITE;
}
