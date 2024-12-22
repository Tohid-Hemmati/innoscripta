<?php

namespace App\Services;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\ArticleServiceInterface;

class ArticleService implements ArticleServiceInterface
{

    public function __construct(protected ArticleRepositoryInterface $articleRepository){}

    public function index($request)
    {
        return $this->articleRepository->index($request);
    }
}
