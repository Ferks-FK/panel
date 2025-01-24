<?php

namespace App\Packages\Laravel\Sanctum;

use App\Models\ApplicationApi;
use Laravel\Sanctum\NewAccessToken as SanctumAccessToken;

/**
 * @property \App\Models\ApplicationApi $accessToken
 */
class NewAccessToken extends SanctumAccessToken
{
    /**
     * NewAccessToken constructor.
     *
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(ApplicationApi $accessToken, string $plainTextToken)
    {
        $this->accessToken = $accessToken;
        $this->plainTextToken = $plainTextToken;
    }
}
