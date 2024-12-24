<?php

namespace App\Contracts;

interface ArticleServiceInterface
{
    public function getArticles($request);
    public function getArticle($id);
}
