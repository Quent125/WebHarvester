<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByName(string $name): ?User
    {
        return $this->model->where('name', $name)->first();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function updateAvatar(int $userId, string $avatarUrl): bool
    {
        return DB::table('users')
            ->where('id', $userId)
            ->update(['avatar' => $avatarUrl]);
    }

    public function updateProfile(int $userId, array $data): bool
    {
        return DB::table('users')
            ->where('id', $userId)
            ->update($data);
    }

    public function updateApiKey(int $userId, string $apiKey): bool
    {
        return DB::table('users')
            ->where('id', $userId)
            ->update(['FirescrawlApiKey' => $apiKey]);
    }

    public function getApiKey(int $userId): ?string
    {
        return DB::table('users')
            ->where('id', $userId)
            ->value('FirescrawlApiKey');
    }

    public function checkEmailExists(string $email): bool
    {
        return $this->model->where('email', $email)->exists();
    }

    public function checkUsernameExists(string $name): bool
    {
        return $this->model->where('name', $name)->exists();
    }
}
