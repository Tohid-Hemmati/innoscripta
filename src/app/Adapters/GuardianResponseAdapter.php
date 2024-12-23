<?php

namespace App\Adapters;

use App\Contracts\NewsApiResponseAdapterInterface;

class GuardianResponseAdapter implements NewsApiResponseAdapterInterface
{
    public function adapt(array $response): array
    {
        return collect($response['response']['results'] ?? [])->map(function ($item) {
            return [
                'title' => $item['webTitle'],
                'url' => $item['webUrl'],
            ];
        })->toArray();
    }
}
