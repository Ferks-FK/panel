<?php

namespace App\Helpers;

use App\Models\ApplicationApi;

class ApiAcl
{
    public const READ = 'read';
    public const WRITE = 'write';

    /**
     * Determine if an API key has permission to perform a specific read/write operation.
     */
    public static function can(string $ability, string $action = self::READ): bool
    {
        if ($ability & $action) {
            return true;
        }

        return false;
    }

    /**
     * Determine if an API Key has permission to access a given resource
     * at a specific action level.
     */
    public static function check(ApplicationApi $key, string $resource, string $action = self::READ): bool
    {
        $ability = array_filter($key->abilities, function ($ability) use($resource, $action) {
            return $ability === ($resource . ':' . $action);
        });

        if (empty($ability)) {
            return false;
        }

        $ability = array_shift($ability);

        return self::can($ability, $action);
    }
}
