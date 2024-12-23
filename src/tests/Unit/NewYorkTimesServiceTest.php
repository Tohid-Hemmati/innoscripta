<?php
namespace Tests\Unit;

use App\Adapters\NewYorkTimesResponseAdapter;
use App\Services\NewYorkTimesService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewYorkTimesServiceTest extends TestCase
{
    public function test_fetch_news_from_new_york_times_api()
    {
        // Mock API response
        Http::fake([
            'https://api.nytimes.com/svc/search/v2/articlesearch.json*' => Http::response([
                'response' => [
                    'docs' => [
                        [
                            'headline' => ['main' => 'NYT Article 1'],
                            'web_url' => 'https://example.com/article1',
                            'snippet' => 'This is the snippet for NYT Article 1',
                            'pub_date' => '2024-01-01T00:00:00Z',
                        ],
                        [
                            'headline' => ['main' => 'NYT Article 2'],
                            'web_url' => 'https://example.com/article2',
                            'snippet' => 'This is the snippet for NYT Article 2',
                            'pub_date' => '2024-01-02T00:00:00Z',
                        ],
                    ],
                ],
            ]),
        ]);

        $adapter = new NewYorkTimesResponseAdapter();

        $service = new NewYorkTimesService($adapter);

        $news = $service->fetchNews(['q' => 'technology']);

        $this->assertCount(2, $news);

        $this->assertEquals('NYT Article 1', $news[0]['title']);
        $this->assertEquals('https://example.com/article1', $news[0]['url']);
        $this->assertEquals('This is the snippet for NYT Article 1', $news[0]['snippet']);
        $this->assertEquals('2024-01-01T00:00:00Z', $news[0]['published_date']);

        $this->assertEquals('NYT Article 2', $news[1]['title']);
        $this->assertEquals('https://example.com/article2', $news[1]['url']);
        $this->assertEquals('This is the snippet for NYT Article 2', $news[1]['snippet']);
        $this->assertEquals('2024-01-02T00:00:00Z', $news[1]['published_date']);
    }
}
