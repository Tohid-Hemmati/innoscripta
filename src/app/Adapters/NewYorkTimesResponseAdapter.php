<?php

namespace App\Adapters;

use App\Contracts\NewsApiResponseAdapterInterface;
use App\DTO\NewsDTO;
use Carbon\Carbon;
use Mews\Purifier\Facades\Purifier;

class NewYorkTimesResponseAdapter implements NewsApiResponseAdapterInterface
{
    public function adapt(array $response): array
    {
        return collect($response['response']['docs'] ?? [])->map(fn($item) => new NewsDTO(
            title: $item['headline']['main'] ?? '',
            content: substr(Purifier::clean(htmlspecialchars($item['abstract'] ?? '') ?? ''), 0, 500) ?? null, // the content could be extracted using the DOM parser but this is a simple example
            source: $item['source'] ?? null,
            source_url: $item['web_url'] ?? null,
            author: $item['byline']['original'] ?? null,
            published_at: isset($item['pub_date'])
                ? Carbon::parse($item['pub_date'])->format('Y-m-d H:i:s')
                : null,
            metadata: isset($item['keywords']) ? json_encode($item['keywords']) : null
        )
        )->toArray();
    }
}
