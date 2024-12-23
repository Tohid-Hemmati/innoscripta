<?php

namespace App\Contracts;

interface NewsApiResponseAdapterInterface
{
    public function adapt(array $response): array;
}
