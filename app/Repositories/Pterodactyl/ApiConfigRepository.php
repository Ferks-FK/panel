<?php

namespace App\Repositories\Pterodactyl;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ApiConfigRepository
{
    public function baseUrl(): string
    {
        $url = config('api.pterodactyl.url');

        if (!str_ends_with($url, '/')) {
            $url = $url . '/';
        }

        return $url;
    }

    public function clientToken(): string
    {
        return config('api.pterodactyl.user_token');
    }

    public function applicationToken(): string
    {
        return config('api.pterodactyl.admin_token');
    }

    public function client(): PendingRequest
    {
        return Http::withToken($this->clientToken())
            ->baseUrl($this->baseUrl() . 'client/');
    }

    public function application(): PendingRequest
    {
        return Http::withToken($this->applicationToken())
            ->baseUrl($this->baseUrl() . 'application/');
    }
}