<?php

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use Illuminate\Support\Facades\DB;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function getArticles($request)
    {
        $perPage = $request->get('per_page', 15);

        $articles = Article::paginate($perPage);

        return [
            'data' => collect($articles->items())->map(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'content' => $article->content,
                    'source' => $article->source,
                    'source_url' => $article->source_url,
                    'author' => $article->author,
                    'published_at' => $article->published_at,
                ];
            }),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'total_pages' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total_items' => $articles->total(),
            ],
            'links' => [
                'first' => $articles->url(1),
                'last' => $articles->url($articles->lastPage()),
                'prev' => $articles->previousPageUrl(),
                'next' => $articles->nextPageUrl(),
            ]
        ];
    }


    /**
     * @param $id
     * @return array[]
     */
    public function getArticle($id)
    {
        return Article::find($id);
    }

    public function createArticle($data)
    {
        DB::table('articles')->insert($data);
    }
}
