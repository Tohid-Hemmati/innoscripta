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
        $articles = $this->articleService->getArticle($id);

        return response()->json($articles);
    }

}
