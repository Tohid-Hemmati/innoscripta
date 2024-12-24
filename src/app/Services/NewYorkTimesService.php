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
    /**
     * Fetch news from the New York Times API.
     *
     * @param array $filters
     * @return array
     * @throws \Exception
     */
    public function fetchNews(array $filters = []): array
    {
        $allowedFilters = [
            'q',
            'fq',
            'begin_date',
            'end_date',
            'sort',
            'page',
        ];

        $query = array_merge(
            array_filter($filters, fn($key) => in_array($key, $allowedFilters), ARRAY_FILTER_USE_KEY),
            ['api-key' => $this->apiKey]
        );

        $articles = [];
        $maxPages = 100;

        for ($page = 0; $page < $maxPages; $page++) {
            $query['page'] = $page;
            $response = Http::get($this->baseUrl . 'articlesearch.json', $query);

            if ($response->successful()) {
                $data = $response->json();
                $articles = array_merge($articles, $this->adapter->adapt($data));

                if (count($data['response']['docs']) < 10) {
                    break;
                }
            } else {
                throw new \Exception('Error fetching news from the New York Times API');
            }
        }

        return $articles;
    }

}
