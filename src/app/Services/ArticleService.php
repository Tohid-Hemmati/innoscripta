<?php

namespace App\Services;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\ArticleServiceInterface;

class ArticleService implements ArticleServiceInterface
{

    public function __construct(protected ArticleRepositoryInterface $articleRepository){}

    public function getArticles($request)
    {
        return $this->articleRepository->getArticles($request);
    }

    public function getArticle($id)
    {
        return $this->articleRepository->getArticle($id);
    }
}
