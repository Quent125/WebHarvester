<?php

namespace App\Http\Controllers;

use App\Services\ScrawlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScrawlController extends Controller
{
    protected $scrawlService;

    public function __construct(ScrawlService $scrawlService)
    {
        $this->scrawlService = $scrawlService;
    }

    public function scrape(Request $request)
    {
        try {
            $validated = $request->validate([
                'url' => 'required|url',
                'formats' => 'nullable|array',
                'onlyMainContent' => 'nullable|boolean',
            ]);

            $result = $this->scrawlService->scrape(
                $request->user()->id,
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => '爬蟲完成',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('爬蟲請求失敗', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
