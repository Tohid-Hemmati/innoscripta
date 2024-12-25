<?php

namespace Tests\Unit;

use App\Contracts\ArticleServiceInterface;
use App\Models\User;
use App\Repositories\ArticleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PreferredNewsFeedTest extends TestCase
{
    use RefreshDatabase;

    public function testFetchNewsFeedWithUserPreferences()
    {
        $user = User::factory()->create(['id' => 1]);

        $mockService = $this->createMock(ArticleServiceInterface::class);

        $mockService->method('fetchNewsFeed')->willReturn([
            'data' => [
                [
                    'id' => 1,
                    'title' => 'Test Article',
                    'author' => 'Author Name',
                    'category' => 'Test Category',
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'total_pages' => 1,
                'per_page' => 10,
                'total_items' => 1,
            ],
            'links' => [
                'first' => '/api/news-feed?page=1',
                'last' => '/api/news-feed?page=1',
                'prev' => null,
                'next' => null,
            ],
        ]);

        $this->app->instance(ArticleServiceInterface::class, $mockService);

        $response = $this->actingAs($user)
            ->getJson('/api/news-feed?page=1&per_page=10');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'title', 'author', 'category'],
                    ],
                    'meta' => ['current_page', 'total_pages', 'per_page', 'total_items'],
                    'links' => ['first', 'last', 'prev', 'next'],
                ],
            ])
            ->assertJson([
                'data' => [
                    'data' => [
                        [
                            'id' => 1,
                            'title' => 'Test Article',
                            'author' => 'Author Name',
                            'category' => 'Test Category',
                        ],
                    ],
                    'meta' => [
                        'current_page' => 1,
                        'total_pages' => 1,
                        'per_page' => 10,
                        'total_items' => 1,
                    ],
                    'links' => [
                        'first' => '/api/news-feed?page=1',
                        'last' => '/api/news-feed?page=1',
                        'prev' => null,
                        'next' => null,
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

        $mockService = $this->createMock(ArticleServiceInterface::class);

        $cachedData = [
            'preferred_sources' => ['source1'],
            'preferred_categories' => ['category1'],
            'preferred_authors' => ['author1'],
        ];

        $mockService->expects($this->once())
            ->method('getPreferredNews')
            ->with($user->id)
            ->willReturn($cachedData);

        $this->app->instance(ArticleServiceInterface::class, $mockService);

        cache()->put($user->id . 'UserPreference', $cachedData);

        $result = $mockService->getPreferredNews($user->id);

        $this->assertEquals($cachedData, $result);

        $this->assertTrue(Cache::has($user->id . 'UserPreference'));
    }

    public function testSetPreferredNewsUpdatesCacheAndDatabase()
    {
        $user = User::factory()->create();

        $requestData = [
            'preferred_sources' => ['source1', 'source2'],
            'preferred_categories' => ['category1', 'category2'],
            'preferred_authors' => ['author1', 'author2'],
        ];

        $repository = app(ArticleRepository::class);

        $request = new class ($requestData) {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function get($key)
            {
                return $this->data[$key] ?? null;
            }
        };

        $repository->setPreferredNews($request, $user->id);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'preferred_sources' => json_encode($requestData['preferred_sources']),
            'preferred_categories' => json_encode($requestData['preferred_categories']),
            'preferred_authors' => json_encode($requestData['preferred_authors']),
        ]);

        $this->assertFalse(Cache::has($user->id . 'UserPreference_Page_1_PerPage_10'));
        $this->assertFalse(Cache::has($user->id . '_UserPreference_Keys'));
    }



}
