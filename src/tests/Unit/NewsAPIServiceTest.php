<?php

namespace Tests\Unit;

use App\Services\NewsAPIService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsAPIServiceTest extends TestCase
{
    public function test_fetch_news_from_newsapi()
    {
        Http::fake([
            'https://newsapi.org/v2/everything*' => Http::response([
                'articles' => [
                    ['title' => 'Article 1', 'content' => 'Content 1'],
                    ['title' => 'Article 2', 'content' => 'Content 2'],
                ],
            ]),
        ]);

        $service = new NewsAPIService();
        $news = $service->fetchNews(['q' => 'laravel']);

        $this->assertCount(2, $news);
        $this->assertEquals('Article 1', $news[0]['title']);
    }
}

