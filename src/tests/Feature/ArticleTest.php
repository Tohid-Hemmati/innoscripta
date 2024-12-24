<?php

namespace Feature;

use App\Models\Article;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'mysql_testing']);
        $this->seed(\Database\Seeders\UserPreferenceSeeder::class);
    }
    public function test_can_fetch_paginated_articles_with_authentication()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        Article::factory()->count(5)->create();

        $response = $this->getJson('/api/articles?page=1&per_page=2');
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'content', 'source', 'published_at'],
            ],
            'meta' => [
                'current_page',
                'total_pages',
                'per_page',
                'total_items',
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
        ]);
    }

    public function test_cannot_fetch_articles_without_authentication()
    {
        $response = $this->getJson('/api/articles?page=1&per_page=2');

        $response->assertStatus(401);

        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }


    public function test_can_fetch_single_article_with_authentication()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $article = Article::factory()->create();

        $response = $this->getJson("/api/article/{$article->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'content',
                'source',
                'source_url',
                'author',
                'metadata',
                'published_at',
            ],
        ]);
        $response->assertJsonFragment([
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'content' => $article->content,
                'source' => $article->source,
                'source_url' => $article->source_url,
                'author' => $article->author,
                'metadata' => json_decode($article->metadata, true),
                'published_at' => $article->published_at,
            ],
        ]);
    }

    public function test_cannot_fetch_single_article_without_authentication()
    {
        $response = $this->getJson('/api/article/1');
        $response->assertStatus(401);

        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function test_can_set_preferred_news_with_authentication()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $payload = [
            'preferred_sources' => ['The New York Times'],
            'preferred_categories' => ['technology', 'health'],
            'preferred_authors' => ['author1'],
        ];

        $response = $this->postJson('/api/preferences', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'preferred_sources' => json_encode($payload['preferred_sources'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'preferred_categories' => json_encode($payload['preferred_categories'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'preferred_authors' => json_encode($payload['preferred_authors'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function test_cannot_set_preferred_news_without_authentication()
    {
        $payload = [
            'preferred_sources' => ['The New York Times'],
            'preferred_categories' => ['technology', 'health'],
            'preferred_authors' => ['author1'],
        ];

        $response = $this->postJson('/api/preferences', $payload);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function test_can_get_preferred_news_with_authentication()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => json_encode(['The New York Times']),
            'preferred_categories' => json_encode(['science']),
            'preferred_authors' => json_encode(['author1']),
        ]);

        $response = $this->getJson('/api/preferences');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'preferred_sources',
            'preferred_categories',
            'preferred_authors',
        ]);
        $response->assertJsonFragment([
            'preferred_sources' => ['The New York Times'],
            'preferred_categories' => ['science'],
            'preferred_authors' => ['author1'],
        ]);
    }

    public function test_cannot_get_preferred_news_without_authentication()
    {
        $response = $this->getJson('/api/preferences');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function test_can_fetch_news_feed_with_authentication()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/news-feed');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'content', 'source', 'published_at'],
            ],
        ]);
    }

    public function test_cannot_fetch_news_feed_without_authentication()
    {
        $response = $this->getJson('/api/news-feed');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

}
