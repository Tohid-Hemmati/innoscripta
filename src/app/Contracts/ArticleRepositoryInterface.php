<?php

namespace App\Contracts;

interface ArticleRepositoryInterface
{
    public function getArticles($request);
    public function getArticle($id);
    public function createArticle($data);
}
