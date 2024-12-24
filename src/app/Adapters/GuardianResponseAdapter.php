<?php

namespace App\Adapters;

use App\Contracts\NewsApiResponseAdapterInterface;
use App\DTO\NewsDTO;
use Carbon\Carbon;
use Mews\Purifier\Facades\Purifier;

class GuardianResponseAdapter implements NewsApiResponseAdapterInterface
{
    public function adapt(array $response): array
    {
        return collect($response['response']['results'] ?? [])->map(fn($item) => new NewsDTO(
            title: $item['webTitle'],
            content: substr(Purifier::clean($item['fields']['body'] ?? ''), 0, 500) ?? null,
            source: 'Guardian',
            source_url: $item['webUrl'] ?? null,
            author: $item['author'] ?? null,
            published_at: $item['webPublicationDate']
                ? Carbon::parse($item['webPublicationDate'])->format('Y-m-d H:i:s')
                : null,
            metadata: $item['metadata'] ?? null,
        ))->toArray();
    }
}
