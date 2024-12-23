<?php

namespace App\Adapters;

use App\Contracts\NewsApiResponseAdapterInterface;

class NewsAPIResponseAdapter implements NewsApiResponseAdapterInterface
{
    public function adapt(array $response): array
    {
        return collect($response['articles'] ?? [])->map(function ($item) {
            return [
                'title' => $item['title'] ?? 'No Title',
                'url' => $item['url'] ?? '',
                'snippet' => $item['description'] ?? '',
                'published_date' => $item['publishedAt'] ?? null,
            ];
        })->toArray();
    }
}
