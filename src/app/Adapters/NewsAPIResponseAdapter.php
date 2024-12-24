<?php

namespace App\Adapters;

use App\Contracts\NewsApiResponseAdapterInterface;
use App\DTO\NewsDTO;
use Carbon\Carbon;
use Mews\Purifier\Facades\Purifier;

class NewsAPIResponseAdapter implements NewsApiResponseAdapterInterface
{
    public function adapt(array $response): array
    {
        return collect($response['articles'] ?? [])->map(function ($item) {
            return new NewsDTO(
                title: $item['title'] ?? 'No Title',
                content: substr(Purifier::clean($item['content'] ?? ''), 0, 500) ?? null,
                source: 'NewsAPI',
                source_url: $item['url'] ?? '',
                author: $item['author'] ?? null,
                published_at: $item['publishedAt']
                    ? Carbon::parse($item['publishedAt'])->format('Y-m-d H:i:s')
                    : null,
                metadata: $this->validateMetadata($item['source']['name'] ?? null)

            );
        })->toArray();
    }

    private function validateMetadata($metadata): string
    {
        return $metadata !== null ? json_encode(['name' => $metadata]) : json_encode([]);
    }
}
