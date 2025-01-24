<?php

namespace App\Models;

use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApplicationApi extends Model
{
    use HasFactory;

    const KEY_LENGTH = 32;

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
        $token = static::where('token', $token)->first();

        if (!is_null($token) && $token->token) {
            return $token;
        }

        return null;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (ApplicationApi $applicationApi) {
            $applicationApi->token = Str::random(self::KEY_LENGTH);
        });
    }

    public function updateLastUsed()
    {
        $this->update(['last_used' => now()]);
    }
}
