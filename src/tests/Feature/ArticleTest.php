<?php

namespace Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_paginated_articles()
    {
        Article::factory()->count(2)->create();

        $response = $this->getJson('/api/articles?page=1');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'content', 'source', 'published_at']
            ],
        ]);
    }
}
