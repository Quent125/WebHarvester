<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\RegistrationTokenRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthService
{
    protected $userRepository;
    protected $tokenRepository;

    public function __construct(
        UserRepository $userRepository,
        RegistrationTokenRepository $tokenRepository
    ) {
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
    }

    public function login(array $credentials): array
    {
        $login = $credentials['login'];
        $password = $credentials['password'];
        $remember = $credentials['remember'] ?? false;

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (!Auth::attempt([$fieldType => $login, 'password' => $password])) {
            throw new \Exception('電子郵件或密碼錯誤');
        }

        $user = Auth::user();
        $expiresAt = $remember ? now()->addDays(30) : now()->addDay();
        $token = $user->createToken('access_token', ['*'], $expiresAt)->plainTextToken;

        // 處理頭像路徑
        $avatar = $user->avatar;
        if ($avatar && strpos($avatar, '/avatars/') !== false && strpos($avatar, '/storage/') === false) {
            $avatar = str_replace('/avatars/', '/storage/avatars/', $avatar);
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $avatar,
            ],
            'access_token' => $token,
            'expires_at' => $expiresAt->toDateTimeString(),
            'hasFirescrawlApiKey' => !empty($user->FirescrawlApiKey),
        ];
    }

    public function register(array $userData): array
    {
        $userData['password'] = Hash::make($userData['password']);
        $user = $this->userRepository->create($userData);

        $token = Str::random(40);
        $expiresAt = now()->addMinutes(5);

        $this->tokenRepository->create($token, $user->id, $expiresAt);

        return [
            'user' => $user,
            'registration_token' => $token,
        ];
    }

    public function verifyRegistrationToken(string $token): bool
    {
        $tokenRecord = $this->tokenRepository->findValidToken($token);

        if (!$tokenRecord) {
            return false;
        }

        $this->tokenRepository->deleteToken($token);
        return true;
    }

    public function logout($user): void
    {
        if ($user) {
            $user->tokens()->delete();
        }
        Auth::logout();
    }

    public function checkUsername(string $name): bool
    {
        return $this->userRepository->checkUsernameExists($name);
    }

    public function checkEmail(string $email): bool
    {
        return $this->userRepository->checkEmailExists($email);
    }
}
