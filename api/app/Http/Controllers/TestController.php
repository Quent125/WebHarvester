<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test()
    {
        return response()->json([
            'message' => '連接成功！',
            'timestamp' => now()->setTimezone('Asia/Taipei')->format('Y年m月d日 H:i:s'),
            'laravel_version' => app()->version()
        ]);
    }
}
