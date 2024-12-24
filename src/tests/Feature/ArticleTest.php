<?php

namespace Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

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


}
