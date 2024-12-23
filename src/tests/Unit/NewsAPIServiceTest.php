<?php
namespace Tests\Unit;

use App\Adapters\NewsAPIResponseAdapter;
use App\Services\NewsAPIService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsAPIServiceTest extends TestCase
{
    public function test_fetch_news_from_newsapi()
    {
        Http::fake([
            'https://newsapi.org/v2/everything*' => Http::response([
                'articles' => [
                    ['title' => 'Article 1', 'description' => 'Content 1', 'url' => 'https://example.com/article1','source' => 'NewsAPI', 'author' => 'author 1', 'publishedAt' => Carbon::parse(now())->format('Y-m-d H:i:s')],
                    ['title' => 'Article 2', 'description' => 'Content 2', 'url' => 'https://example.com/article2','source' => 'NewsAPI', 'author' => 'author 2', 'publishedAt' => Carbon::parse(now())->format('Y-m-d H:i:s')],
                ],
            ]),
        ]);

        $adapter = new NewsAPIResponseAdapter();
        $service = new NewsAPIService($adapter);
        $news = $service->fetchNews(['q' => 'laravel']);

        $this->assertCount(2, $news);
        $this->assertEquals('Article 1', $news[0]->title);
    }
}
