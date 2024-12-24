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

    public function getPreferredNews($userID)
    {
        return $this->articleRepository->getPreferredNews($userID);
    }
    public function setPreferredNews($request, $userID)
    {
        return $this->articleRepository->setPreferredNews($request, $userID);
    }

    public function fetchNewsFeed($request, $userID)
    {
        return $this->articleRepository->fetchNewsFeed($request, $userID);
    }
}
