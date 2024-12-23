<?php

namespace App\Services;

use App\Contracts\NewsApiInterface;
use App\Contracts\NewsApiResponseAdapterInterface;
use App\Enums\NewsServiceEnum;
use Illuminate\Support\Facades\Http;

class NewYorkTimesService implements NewsApiInterface
{
    protected string $baseUrl = 'https://api.nytimes.com/svc/search/v2/';
    protected string $apiKey;

    public function __construct(protected NewsApiResponseAdapterInterface $adapter)
    {
        $source = NewsServiceEnum::NEW_YORK_TIMES->value;
        $this->apiKey = config("services.news.sources.$source.key");
    }

    /**
     * Fetch news from the New York Times API.
     *
     * @param array $filters
     * @return array
     * @throws \Exception
     */
    public function fetchNews(array $filters = []): array
    {
        $query = array_merge($filters, [
            'api-key' => $this->apiKey,
        ]);

        $response = Http::get($this->baseUrl . 'articlesearch.json', $query);

        if ($response->successful()) {
            return $this->adapter->adapt($response->json());
        }

        throw new \Exception('Error fetching news from the New York Times API');
    }
}
