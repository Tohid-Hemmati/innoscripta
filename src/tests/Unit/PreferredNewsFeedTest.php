<?php

namespace Tests\Unit;

use App\Contracts\ArticleServiceInterface;
use App\Models\Article;
use App\Models\User;
use App\Models\UserPreference;
use App\Repositories\ArticleRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferredNewsFeedTest extends TestCase
{
    use RefreshDatabase;

    public function testFetchNewsFeedWithUserPreferences()
    {
        $user = User::factory()->create(['id' => 1]);

        $mockService = $this->createMock(ArticleServiceInterface::class);
        $mockService->method('fetchNewsFeed')->willReturn([
            [
                'preferred_sources' => ['source1'],
                'preferred_categories' => ['category1'],
                'preferred_authors' => ['author1'],
            ],
        ]);

        $this->app->instance(ArticleServiceInterface::class, $mockService);

        $mockQuery = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['when', 'get'])
            ->getMock();

        $mockQuery->method('when')->willReturnSelf();
        $mockQuery->method('get')->willReturn(collect([
            [
                'preferred_sources' => ['source1'],
                'preferred_categories' => ['category1'],
                'preferred_authors' => ['author1'],
            ],
        ]));

        $this->partialMock(Article::class, function ($mock) use ($mockQuery) {
            $mock->shouldReceive('query')->andReturn($mockQuery);
        });

        $response = $this->actingAs($user)
            ->getJson('/api/news-feed');
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'preferred_sources' => ['source1'],
                        'preferred_categories' => ['category1'],
                        'preferred_authors' => ['author1'],
                    ],
                ],
            ]);
    }



    public function testGetPreferredNewsWhenPreferenceDoesNotExist()
    {
        $mockService = $this->createMock(ArticleServiceInterface::class);
        $mockService->method('getPreferredNews')->willReturn(null);

        $this->app->instance(ArticleServiceInterface::class, $mockService);

        $response = $this->actingAs(User::factory()->create())
            ->getJson('/api/news-feed');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Preferences not found']);
    }

    /**
     * @dataProvider preferredNewsDataProvider
     */
    public function testSetPreferredNews($inputData, $expectedResponse)
    {
        $mockService = $this->createMock(ArticleServiceInterface::class);
        $mockService->method('setPreferredNews')->willReturn($expectedResponse);

        $this->app->instance(ArticleServiceInterface::class, $mockService);

        $response = $this->actingAs(User::factory()->create())
            ->postJson('/api/preferences', $inputData);
        $response->assertStatus(200)->assertJson([
            'message' => 'Preferences saved successfully',
            'data' => $expectedResponse,
        ]);
    }

    public static function preferredNewsDataProvider()
    {
        return [
            [
                ['preferred_sources' => ['source1'], 'preferred_categories' => ['category1'], 'preferred_authors' => ['author1']],
                ['preferred_sources' => ['source1'], 'preferred_categories' => ['category1'], 'preferred_authors' => ['author1']],
            ],
        ];
    }
    public function testFetchNewsFeed()
    {
        $mockService = $this->createMock(ArticleServiceInterface::class);
        $mockService->method('fetchNewsFeed')->willReturn([
            ['title' => 'Article 1', 'content' => 'Content 1'],
            ['title' => 'Article 2', 'content' => 'Content 2'],
        ]);

        $this->app->instance(ArticleServiceInterface::class, $mockService);

        $response = $this->actingAs(User::factory()->create())
            ->getJson('/api/news-feed');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['title' => 'Article 1', 'content' => 'Content 1'],
                    ['title' => 'Article 2', 'content' => 'Content 2'],
                ],
            ]);
    }

    public function testGetPreferredNewsFromCache()
    {
        $user = User::factory()->create(['id' => 1]);
        UserPreference::factory()->create([
            'user_id' => $user->id,
            'preferred_sources' => json_encode(['source1']),
            'preferred_categories' => json_encode(['category1']),
            'preferred_authors' => json_encode(['author1']),
        ]);

        $repo = new ArticleRepository();

        cache()->put($user->id . 'UserPreference', [
            'preferred_sources' => ['source1'],
            'preferred_categories' => ['category1'],
            'preferred_authors' => ['author1'],
        ]);

        $result = $repo->getPreferredNews($user->id);

        $this->assertEquals([
            'preferred_sources' => ['source1'],
            'preferred_categories' => ['category1'],
            'preferred_authors' => ['author1'],
        ], $result);

        $this->assertTrue(cache()->has($user->id . 'UserPreference'));
    }
    public function testSetPreferredNewsUpdatesCacheAndDatabase()
    {
        $user = User::factory()->create();

        $repo = new ArticleRepository();

        $request = new class {
            public function get($key)
            {
                $data = [
                    'preferred_sources' => ['source1', 'source2'],
                    'preferred_categories' => ['category1', 'category2'],
                    'preferred_authors' => ['author1', 'author2'],
                ];
                return $data[$key] ?? null;
            }
        };

        $repo->setPreferredNews($request, $user->id);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'preferred_sources' => json_encode(['source1', 'source2']),
            'preferred_categories' => json_encode(['category1', 'category2']),
            'preferred_authors' => json_encode(['author1', 'author2']),
        ]);

        $this->assertNull(cache()->get($user->id . 'UserPreference'));
    }

}
