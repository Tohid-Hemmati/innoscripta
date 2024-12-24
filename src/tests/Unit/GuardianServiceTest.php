<?php
namespace Tests\Unit;

use App\Adapters\GuardianResponseAdapter;
use App\Services\GuardianService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GuardianServiceTest extends TestCase
{
    public function test_fetch_news_from_guardian_api()
    {
        Http::fake([
            'https://content.guardianapis.com/*' => Http::response([
                'response' => [
                    'results' => [
                        ['webTitle' => 'Guardian Article 1', 'webUrl' => 'https://example.com/article1','author' => 'author1', 'webPublicationDate' => Carbon::parse(now())->format('Y-m-d H:i:s')],
                        ['webTitle' => 'Guardian Article 2', 'webUrl' => 'https://example.com/article2','author' => 'author2', 'webPublicationDate' => Carbon::parse(now())->format('Y-m-d H:i:s')],
                    ],
                ],
            ]),
        ]);

        $adapter = new GuardianResponseAdapter();
        $service = new GuardianService($adapter);
        $news = $service->fetchNews(['q' => 'laravel']);
        $this->assertEquals('Guardian Article 1', $news[0]->title);
        $this->assertEquals('https://example.com/article1', $news[0]->source_url);
        $this->assertEquals('author1', $news[0]->author);
        $this->assertEquals('Guardian', $news[0]->source);
    }
}
