<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RegistrationTokenRepository
{
    protected $table = 'registration_tokens';

    public function create(string $token, int $userId, Carbon $expiresAt): bool
    {
        return DB::table($this->table)->insert([
            'token' => $token,
            'user_id' => $userId,
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);
    }

    public function findValidToken(string $token): ?object
    {
        return DB::table($this->table)
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function deleteToken(string $token): bool
    {
        return DB::table($this->table)->where('token', $token)->delete();
    }

    public function deleteExpiredTokens(): int
    {
        return DB::table($this->table)
            ->where('expires_at', '<', now())
            ->delete();
    }
}
