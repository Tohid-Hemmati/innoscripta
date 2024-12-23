<?php

namespace App\Contracts;

interface NewsApiInterface
{
    public function fetchNews(array $filters = []): array;
}
