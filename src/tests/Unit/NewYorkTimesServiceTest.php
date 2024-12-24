<?php
namespace Tests\Unit;

use App\Adapters\NewYorkTimesResponseAdapter;
use App\Services\NewYorkTimesService;
use Carbon\Carbon;
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
                            'abstract' => 'This is the snippet for NYT Article 1',
                            'author'=> 'author 1',
                            'pub_date' => Carbon::parse(now())->format('Y-m-d H:i:s'),
                        ],
                        [
                            'headline' => ['main' => 'NYT Article 2'],
                            'web_url' => 'https://example.com/article2',
                            'abstract' => 'This is the snippet for NYT Article 2',
                            'author'=> 'author 2',
                            'pub_date' => Carbon::parse(now())->format('Y-m-d H:i:s'),
                        ],
                    ],
                ],
            ]),
        ]);

        $adapter = new NewYorkTimesResponseAdapter();

        $service = new NewYorkTimesService($adapter);

        $news = $service->fetchNews(['q' => 'technology']);

        $this->assertCount(2, $news);

        $this->assertEquals('NYT Article 1', $news[0]->title);
        $this->assertEquals('https://example.com/article1', $news[0]->source_url);
        $this->assertEquals('<p>This is the snippet for NYT Article 1</p>', $news[0]->content);
        $this->assertEquals(Carbon::parse(now())->format('Y-m-d H:i:s'), $news[0]->published_at);

        $this->assertEquals('NYT Article 2', $news[1]->title);
        $this->assertEquals('https://example.com/article2', $news[1]->source_url);
        $this->assertEquals('<p>This is the snippet for NYT Article 2</p>', $news[1]->content);
        $this->assertEquals(Carbon::parse(now())->format('Y-m-d H:i:s'), $news[1]->published_at);
    }
}
