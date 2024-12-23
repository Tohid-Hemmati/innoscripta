<?php

namespace App\Services;

use App\Contracts\NewsApiInterface;
use App\Contracts\NewsApiResponseAdapterInterface;
use App\Enums\NewsServiceEnum;
use Illuminate\Support\Facades\Http;

class GuardianService implements NewsApiInterface
{
    protected string $baseUrl = 'https://content.guardianapis.com/';
    protected string $apiKey;

    public function __construct(protected NewsApiResponseAdapterInterface $adapter)
    {
        $source = NewsServiceEnum::GUARDIAN->value;
        $this->apiKey = config("services.news.sources.$source.key");
    }

    /**
     * Fetch news from the Guardian API.
     *
     * @param array $filters
     * @return array
     * @throws \Exception
     */
    public function fetchNews(array $filters = []): array
    {
        $response = Http::get($this->baseUrl . 'search', array_merge($filters, [
            'api-key' => $this->apiKey,
        ]));

        if ($response->successful()) {
            return $this->adapter->adapt($response->json());
        }

        throw new \Exception('Error fetching news from the Guardian API');
    }
}
