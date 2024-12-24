<?php

namespace App\Http\Controllers;

use App\Contracts\ArticleServiceInterface;
use App\Http\Requests\ArticleRequest;

class ArticleController extends Controller
{
    public function __construct(protected ArticleServiceInterface $articleService)
    {
    }


    /**
     * Display a listing of the resource.
     */
    public function getArticles(ArticleRequest $request)
    {
        $articles = $this->articleService->getArticles($request);

        return response()->json($articles);
    }

    public function getArticle($id)
    {
        $article = $this->articleService->getArticle($id);

        if (!$article) {
            return response()->json([
                'error' => 'Article not found',
            ], 404);
        }

        return response()->json([
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

}
