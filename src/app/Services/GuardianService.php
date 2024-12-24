<?php

namespace App\Services;

use App\Contracts\NewsApiInterface;
use App\Contracts\NewsApiResponseAdapterInterface;
use App\Enums\NewsServiceEnum;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $defaultFilters = [
            'api-key' => $this->apiKey,
            'show-fields' => 'body',
            'page-size' => 10,
            'order-by' => 'newest',
            'from-date' => now()->subYear()->toDateString(),
            'to-date' => now()->toDateString(),
        ];

    $allResults = [];
    $currentPage = 1;
    $maxPages = 10;

        do {
            $response = Http::get($this->baseUrl . 'search', array_merge($defaultFilters, $filters, ['page' => $currentPage]));

            if ($response->successful()) {
                $data = $response->json();

                $results = $data['response']['results'] ?? [];
                if (empty($results)) {
                    break;
                }

                $adaptedResults = $this->adapter->adapt($data);
                $allResults = array_merge($allResults, $adaptedResults);

            $currentPage++;
            usleep(500000);
        } elseif ($response->status() === 429) {
            $retryAfter = $response->header('Retry-After');
            Log::warning("Rate limit exceeded. Retrying after {$retryAfter} seconds...");
            sleep((int)$retryAfter);
        } else {
            Log::error('Error fetching news', ['body' => $response->body()]);
            throw new \Exception('Error fetching news from the Guardian API');
        }
    } while ($currentPage <= $maxPages);

    return $allResults;
}


}
