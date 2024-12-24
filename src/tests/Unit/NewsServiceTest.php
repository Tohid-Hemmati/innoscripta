<?php

namespace Tests\Unit;

use App\Adapters\GuardianResponseAdapter;
use App\Adapters\NewsAPIResponseAdapter;
use App\Adapters\NewYorkTimesResponseAdapter;
use App\Services\GuardianService;
use App\Services\NewsAPIService;
use App\Services\NewYorkTimesService;
use App\Services\NewsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsServiceTest extends TestCase
{
    public function test_fetch_news_using_newsapi_strategy()
    {
        Http::fake([
            'https://newsapi.org/v2/everything*' => Http::response([
                'articles' => [
                    ['title' => 'Article 1', 'content' => 'Content 1', 'url' => 'https://example.com/article1', 'author' => 'author', 'publishedAt' => Carbon::parse(now())->format('Y-m-d H:i:s')],
                ],
            ]),
        ]);

        $adapter = new NewsAPIResponseAdapter();
        $newsService = new NewsService();
        $newsService->setStrategy(new NewsAPIService($adapter));
        $news = $newsService->fetchNews(['q' => 'laravel']);

        $this->assertCount(1, $news);
        $this->assertEquals('Article 1', $news[0]->title);
    }

    public function test_fetch_news_using_guardian_strategy()
    {
        Http::fake([
            'https://content.guardianapis.com/search*' => Http::response([
                'response' => [
                    'results' => [
                        [
                            'webTitle' => 'Guardian Article 1',
                            'webUrl' => 'https://example.com/article1',
                            'author' => 'author1',
                            'webPublicationDate' => Carbon::parse(now())->format('Y-m-d H:i:s')
                        ],
                    ],
                ],
            ]),
        ]);

        $adapter = new GuardianResponseAdapter();
        $newsService = new NewsService();
        $newsService->setStrategy(new GuardianService($adapter));
        $news = $newsService->fetchNews(['q' => 'technology']);

        $this->assertCount(1, $news);
        $this->assertEquals('Guardian Article 1', $news[0]->title);
        $this->assertEquals('https://example.com/article1', $news[0]->source_url);
    }

    public function test_fetch_news_using_new_york_times_strategy()
    {
        Http::fake([
            'https://api.nytimes.com/svc/search/v2/articlesearch.json*' => Http::response([
                'response' => [
                    'docs' => [
                        [
                            'headline' => ['main' => 'NYT Article 1'],
                            'web_url' => 'https://example.com/article1',
                            'abstract' => 'abstract 1',
                            'source' => 'New York Times',
                            'pub_date' => Carbon::parse(now())->format('Y-m-d H:i:s'),
                        ],
                        [
                            'headline' => ['main' => 'NYT Article 2'],
                            'web_url' => 'https://example.com/article2',
                            'abstract' => 'abstract 2',
                            'source' => 'New York Times',
                            'pub_date' => Carbon::parse(now())->format('Y-m-d H:i:s'),
                        ],
                    ],
                ],
            ]),
        ]);

        $adapter = new NewYorkTimesResponseAdapter();
        $newsService = new NewsService();
        $newsService->setStrategy(new NewYorkTimesService($adapter));
        $news = $newsService->fetchNews(['q' => 'technology']);

        $this->assertCount(2, $news);

        $this->assertEquals('NYT Article 1', $news[0]->title);
        $this->assertEquals('https://example.com/article1', $news[0]->source_url);
        $this->assertEquals('<p>abstract 1</p>', $news[0]->content);
        $this->assertEquals(Carbon::parse(now())->format('Y-m-d H:i:s'), $news[0]->published_at);

        $this->assertEquals('NYT Article 2', $news[1]->title);
        $this->assertEquals('https://example.com/article2', $news[1]->source_url);
        $this->assertEquals('<p>abstract 2</p>', $news[1]->content);
        $this->assertEquals(Carbon::parse(now())->format('Y-m-d H:i:s'), $news[1]->published_at);
    }
}
