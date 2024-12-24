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

        return response()->json([
            'data' => $articles->items(),
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
            ],
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getArticle($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'error' => 'Article not found',
            ], 404);
        }

        return response()->json([
            'data' => $article,
        ]);
    }

    public function createArticle($data)
    {
        DB::table('articles')->insert($data);
    }
}
