<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function uploadAvatar(int $userId, UploadedFile $file): string
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new \Exception('用戶不存在');
        }

        // 生成檔案名稱
        $filename = 'user_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();
        $directoryPath = storage_path('app/public/avatars');

        // 確保目錄存在
        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0775, true);
        }

        // 移動檔案
        $file->move($directoryPath, $filename);
        $url = '/storage/avatars/' . $filename;

        // 更新資料庫
        $this->userRepository->updateAvatar($userId, $url);

        Log::debug('頭像上傳成功', ['url' => $url]);

        return $url;
    }

    public function updateProfile(int $userId, array $data): array
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new \Exception('用戶不存在');
        }

        $this->userRepository->updateProfile($userId, $data);
        $updatedUser = $this->userRepository->findById($userId);

        return [
            'id' => $updatedUser->id,
            'name' => $updatedUser->name,
            'email' => $updatedUser->email,
            'avatar' => $updatedUser->avatar,
        ];
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userRepository->findById($userId);

        if (!$user || !Hash::check($currentPassword, $user->password)) {
            throw new \Exception('當前密碼不正確');
        }

        return $this->userRepository->updateProfile($userId, [
            'password' => Hash::make($newPassword)
        ]);
    }

    public function updateApiKey(int $userId, string $apiKey): bool
    {
        return $this->userRepository->updateApiKey($userId, $apiKey);
    }

    public function getUser(int $userId): ?array
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
        ];
    }
}
