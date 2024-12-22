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
    public function index(ArticleRequest $request)
    {
        $articles = $this->articleService->fetchArticles($request);

        return response()->json($articles);
    }

}
