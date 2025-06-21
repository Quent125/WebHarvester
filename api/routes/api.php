<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScrawlController;
use App\Http\Controllers\UserController;

// 測試
Route::get('/test-connection', [TestController::class, 'test']);

// 獲取用戶資訊
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// verify-access-token
Route::middleware('auth:sanctum')->post('/verify-access-token', [AuthController::class, 'verifyAccessToken']);

// 登入
Route::post('/login', [AuthController::class, 'login']);
// 註冊
Route::post('/register', [AuthController::class, 'register']);
// verify-registration-token
Route::post('/verify-registration-token', [AuthController::class, 'verifyRegistrationToken']);

// 登出
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// 檢查帳號、電子郵件和密碼
Route::post('/checkUsername', [AuthController::class, 'checkUsername']);
Route::post('/checkEmail', [AuthController::class, 'checkEmail']);
Route::post('/checkPassword', [AuthController::class, 'checkPassword']);

Route::middleware('auth:sanctum')->group(function () {

    // Firescrawl
    Route::post('/scrape', [ScrawlController::class, 'scrape']);

    // setting
    Route::prefix('user')->group(function () {
        // 頭像
        Route::post('/avatar', [UserController::class, 'uploadAvatar']);

        // 個人資料
        Route::put('/profile', [UserController::class, 'updateProfile']);

        // 密碼變更
        Route::put('/change-password', [UserController::class, 'changePassword']);

        // Firescrawl API 設定
        Route::put('/crawl-settings', [UserController::class, 'crawlSetting']);
    });
});
