<?php

namespace App\Http\Controllers;

use App\Contracts\ArticleServiceInterface;
use App\Http\Requests\ArticleRequest;
use App\Http\Requests\UserPreferenceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function getPreferredNews()
    {
        $preference = $this->articleService->getPreferredNews(Auth::id());
        if (!$preference) {
            return response()->json(['message' => 'Preferences not found'], 404);
        }
        return response()->json($preference, 200);
    }
    public function setPreferredNews(UserPreferenceRequest $request): JsonResponse
    {
        $preference = $this->articleService->setPreferredNews($request, Auth::id());
        return response()->json(['message' => 'Preferences saved successfully', 'data' => $preference], 200);
    }
    public function fetchNewsFeed(Request $request)
    {
        $newsFeed = $this->articleService->fetchNewsFeed($request, Auth::id());
        return $newsFeed
            ? response()->json(['data' => $newsFeed])
            : response()->json(['message' => 'Preferences not found'], 404);
    }

}
