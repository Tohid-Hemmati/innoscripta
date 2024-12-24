<?php

namespace App\Contracts;

interface ArticleServiceInterface
{
    public function getArticles($request);
    public function getArticle($id);
    public function getPreferredNews($userID);
    public function setPreferredNews($request, $userID);
    public function fetchNewsFeed($request, $userID);
}
