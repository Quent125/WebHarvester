<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrawlService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function scrape(int $userId, array $requestData): array
    {
        $apiKey = $this->userRepository->getApiKey($userId);

        if (empty($apiKey)) {
            throw new \Exception('找不到有效的 FireCrawl API 金鑰');
        }

        // 準備請求資料
        $data = [
            'url' => $requestData['url'],
        ];

        // 加入格式選項
        if (!empty($requestData['formats'])) {
            $data['formats'] = $requestData['formats'];
        } else {
            $data['formats'] = ['markdown'];
        }

        // 加入主要內容選項
        if (isset($requestData['onlyMainContent'])) {
            $data['onlyMainContent'] = $requestData['onlyMainContent'];
        }

        Log::info('準備發送 API 請求', [
            'api_key_length' => strlen($apiKey),
            'request_data' => $data
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://api.firecrawl.dev/v1/scrape', $data);

            if ($response->successful()) {
                return $response->json();
            }

            $errorInfo = [];
            try {
                $errorInfo = $response->json();
            } catch (\Exception $e) {
                $errorInfo['raw_response'] = substr($response->body(), 0, 1000);
            }

            throw new \Exception('外部爬蟲服務返回錯誤: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('爬蟲請求失敗', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
