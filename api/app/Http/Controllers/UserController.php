<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function uploadAvatar(Request $request)
    {
        try {
            $request->validate(['avatar' => 'required|image|max:2048']);

            $url = $this->userService->uploadAvatar(
                $request->user()->id,
                $request->file('avatar')
            );

            return response()->json([
                'success' => true,
                'avatarUrl' => $url
            ]);
        } catch (\Exception $e) {
            Log::error('頭像上傳失敗', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => '頭像上傳失敗: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $request->validate(['name' => 'required|string|max:255']);

            $user = $this->userService->updateProfile(
                $request->user()->id,
                $request->only(['name'])
            );

            return response()->json([
                'success' => true,
                'message' => '個人資料已更新',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('更新用戶資料失敗', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => '更新用戶資料失敗: ' . $e->getMessage()
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'currentPassword' => 'required',
                'newPassword' => 'required|min:8|max:16|confirmed'
            ]);

            $this->userService->changePassword(
                $request->user()->id,
                $request->currentPassword,
                $request->newPassword
            );

            return response()->json([
                'success' => true,
                'message' => '密碼已更新'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function crawlSetting(Request $request)
    {
        try {
            $request->validate(['apiKey' => 'required|string|max:255']);

            $this->userService->updateApiKey(
                $request->user()->id,
                $request->apiKey
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('apiKey設置失敗', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'apiKey設置失敗: ' . $e->getMessage()
            ], 500);
        }
    }
}
