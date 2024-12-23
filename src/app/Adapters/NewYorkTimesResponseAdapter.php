<?php

namespace App\Adapters;

use App\Contracts\NewsApiResponseAdapterInterface;

class NewYorkTimesResponseAdapter implements NewsApiResponseAdapterInterface
{
    public function adapt(array $response): array
    {
        return collect($response['response']['docs'] ?? [])->map(function ($item) {
            return [
                'title' => $item['headline']['main'] ?? 'No Title',
                'url' => $item['web_url'] ?? '',
                'snippet' => $item['snippet'] ?? '',
                'published_date' => $item['pub_date'] ?? null,
            ];
        })->toArray();
    }
}
