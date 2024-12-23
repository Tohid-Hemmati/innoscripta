<?php
namespace App\Services;

use App\Contracts\NewsApiInterface;

class NewsService
{
    protected NewsApiInterface $newsApi;

    public function setStrategy(NewsApiInterface $newsApi)
    {
        $this->newsApi = $newsApi;
    }

    public function fetchNews(array $filters = []): array
    {
        return $this->newsApi->fetchNews($filters);
    }
}
