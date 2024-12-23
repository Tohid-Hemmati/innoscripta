<?php

namespace App\Services;

use App\Contracts\NewsApiInterface;
use App\Contracts\NewsApiResponseAdapterInterface;
use App\Enums\NewsServiceEnum;
use Illuminate\Support\Facades\Http;

class NewsAPIService implements NewsApiInterface
{
    protected string $baseUrl = 'https://newsapi.org/v2/';
    protected string $apiKey;

    public function __construct(protected NewsApiResponseAdapterInterface $adapter)
    {
        $source = NewsServiceEnum::NEWS_API->value;
        $this->apiKey = config("services.news.sources.$source.key");
    }

    public function fetchNews(array $filters = []): array
    {
        $response = Http::get($this->baseUrl . 'everything', array_merge($filters, [
            'apiKey' => $this->apiKey,
        ]));

        if ($response->successful()) {
            return $this->adapter->adapt($response->json());
        }

        throw new \Exception('Error fetching news from NewsAPI');
    }
}
