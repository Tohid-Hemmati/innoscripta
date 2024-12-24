<?php

namespace App\Contracts;

interface ArticleRepositoryInterface
{
    public function getArticles($request);
    public function getArticle($id);
    public function createArticle($data);
    public function setPreferredNews($request, $userID);
    public function getPreferredNews($userID);
    public function fetchNewsFeed($request, $userID);
}
