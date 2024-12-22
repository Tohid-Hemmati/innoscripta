<?php

namespace App\Contracts;

interface ArticleRepositoryInterface
{
    public function fetchArticles($request);
}
