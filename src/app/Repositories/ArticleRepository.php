<?php

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function getArticles($request)
    {
        $perPage = $request->get('per_page', 15);

        $articles = Article::paginate($perPage);

        return [
            'data' => collect($articles->items())->map(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'content' => $article->content,
                    'source' => $article->source,
                    'source_url' => $article->source_url,
                    'author' => $article->author,
                    'published_at' => $article->published_at,
                ];
            }),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'total_pages' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total_items' => $articles->total(),
            ],
            'links' => [
                'first' => $articles->url(1),
                'last' => $articles->url($articles->lastPage()),
                'prev' => $articles->previousPageUrl(),
                'next' => $articles->nextPageUrl(),
            ]
        ];
    }


    /**
     * @param $id
     * @return array[]
     */
    public function getArticle($id)
    {
        return Article::find($id);
    }

    public function createArticle($data)
    {
        DB::table('articles')->insert($data);
    }

    /**
     * @param $userID
     * @return mixed
     */
    public function getPreferredNews($userID)
    {
        return Cache::rememberForever($userID. 'UserPreference', function () use ($userID) {
            $preference = UserPreference::where('user_id', $userID)->first();
            if ($preference) {
                return [
                    'preferred_sources' => json_decode($preference->preferred_sources, true),
                    'preferred_categories' => json_decode($preference->preferred_categories, true),
                    'preferred_authors' => json_decode($preference->preferred_authors, true),
                ];
            }
            return null;
        });

    }

    /**
     * @param $request
     * @return mixed
     */
    public function setPreferredNews($request, $userID)
    {
        Cache::forget($userID . 'UserPreference');
        return UserPreference::updateOrCreate(
            ['user_id' => $userID],
            [
                'preferred_sources' => json_encode($request->get('preferred_sources'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'preferred_categories' => json_encode($request->get('preferred_categories'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'preferred_authors' => json_encode($request->get('preferred_authors'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]
        );
    }

    public function fetchNewsFeed($request, $userID)
    {
        $preference = $this->getPreferredNews($userID);

        return Cache::rememberForever($userID . 'UserPreference', function () use ($preference) {
            return Article::query()
                ->when(!empty($preference['preferred_sources']), function ($query) use ($preference) {
                    $query->whereJsonContains('preferred_sources', $preference['preferred_sources']);
                })
                ->when(!empty($preference['preferred_categories']), function ($query) use ($preference) {
                    $query->whereJsonContains('preferred_categories', $preference['preferred_categories']);
                })
                ->when(!empty($preference['preferred_authors']), function ($query) use ($preference) {
                    $query->whereJsonContains('preferred_authors', $preference['preferred_authors']);
                })
                ->get();
        });
    }
}
