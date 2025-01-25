<?php

namespace App\Models;

use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ApplicationApi extends Model
{
    use HasFactory;

    const KEY_PREFIX = 'cpgg_';
    const KEY_LENGTH = 48;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'description',
        'last_used_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'last_used_at' => 'datetime',
        'allowed_ips' => 'array',
        'abilities' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tokenable()
    {
        return $this->user();
    }

    /**
     * Finds the model matching the provided token.
     */
    public static function findToken(string $token): ?self
    {
        $apiKeys = static::all();

        foreach ($apiKeys as $apiKey) {
            try {
                if (decrypt($apiKey->token) === $token) {
                    return $apiKey;
                }
            } catch (\Exception $e) {
                logger()->error($e->getMessage());
                continue;
            }
        }

        return null;
    }

    public function updateLastUsed()
    {
        $this->update(['last_used' => now()]);
    }
}
