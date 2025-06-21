<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->validated());

            return response()->json([
                'user' => $result['user'],
                'access_token' => $result['access_token'],
                'message' => '登入成功',
                'expires_at' => $result['expires_at'],
                'hasFirescrawlApiKey' => $result['hasFirescrawlApiKey'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function register(RegisterRequest $request)
    {
        try {
            $result = $this->authService->register($request->validated());

            return response()->json([
                'message' => '註冊成功',
                'user' => $result['user'],
                'registration_token' => $result['registration_token']
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());
            return response()->json(['message' => '已成功登出']);
        } catch (\Exception $e) {
            Log::error('登出錯誤: ' . $e->getMessage());
            return response()->json(['message' => '登出過程發生錯誤'], 500);
        }
    }

    public function verifyRegistrationToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $valid = $this->authService->verifyRegistrationToken($request->token);

        return response()->json([
            'valid' => $valid,
            'message' => $valid ? '註冊令牌有效' : '無效的註冊令牌或已過期'
        ], $valid ? 200 : 403);
    }

    public function verifyAccessToken(Request $request)
    {
        return response()->json([
            'valid' => true,
            'user' => $request->user()
        ]);
    }

    public function checkUsername(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $exists = $this->authService->checkUsername($request->name);

        return response()->json(['exists' => $exists]);
    }

    public function checkEmail(Request $request)
    {
        $request->validate(['email' => 'required|string|email|max:255']);

        $exists = $this->authService->checkEmail($request->email);

        return response()->json(['exists' => $exists]);
    }

    public function checkPassword(Request $request)
    {
        $request->validate(['password' => 'required|min:8|max:16']);

        return response()->json(['message' => '密碼格式有效']);
    }
}
