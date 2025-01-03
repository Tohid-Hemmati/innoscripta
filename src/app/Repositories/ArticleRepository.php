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
        $keyListKey = $userID . '_UserPreference_Keys';

        $cachedKeys = Cache::get($keyListKey, []);
        foreach ($cachedKeys as $key) {
            Cache::forget($key);
        }

        Cache::forget($keyListKey);
        Cache::forget($userID. 'UserPreference');

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
        $preferences = $this->getPreferredNews($userID);
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $cacheKey = $userID . 'UserPreference_Page_' . $page . '_PerPage_' . $perPage;

        $keyListKey = $userID . '_UserPreference_Keys';

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($preferences, $perPage, $keyListKey, $cacheKey) {
            $cachedKeys = Cache::get($keyListKey, []);
            if (!in_array($cacheKey, $cachedKeys)) {
                $cachedKeys[] = $cacheKey;
                Cache::put($keyListKey, $cachedKeys, now()->addHours(6));
            }

            $query = Article::query();

            if (!empty($preferences['preferred_sources'])) {
                $query->where(function ($subQuery) use ($preferences) {
                    foreach ($preferences['preferred_sources'] as $source) {
                        $subQuery->orWhereRaw(
                            "MATCH(content) AGAINST(? IN NATURAL LANGUAGE MODE)",
                            [$source]
                        );
                    }
                });
            }

            if (!empty($preferences['preferred_categories'])) {
                $query->where(function ($subQuery) use ($preferences) {
                    foreach ($preferences['preferred_categories'] as $category) {
                        $subQuery->orWhereRaw(
                            "MATCH(content) AGAINST(? IN NATURAL LANGUAGE MODE)",
                            [$category]
                        );
                    }
                });
            }

            if (!empty($preferences['preferred_authors'])) {
                $query->where(function ($subQuery) use ($preferences) {
                    foreach ($preferences['preferred_authors'] as $author) {
                        $subQuery->orWhere('author', 'LIKE', "%{$author}%");
                    }
                });
            }

            return $query->select(['id', 'title', 'author', 'content'])
                ->paginate($perPage);
        });
    }

}
