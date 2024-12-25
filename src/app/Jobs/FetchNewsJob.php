<?php

namespace App\Jobs;

use App\Adapters\GuardianResponseAdapter;
use App\Adapters\NewsAPIResponseAdapter;
use App\Adapters\NewYorkTimesResponseAdapter;
use App\Contracts\ArticleRepositoryInterface;
use App\Enums\NewsServiceEnum;
use App\Services\GuardianService;
use App\Services\NewsAPIService;
use App\Services\NewsService;
use App\Services\NewYorkTimesService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FetchNewsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private NewsService                $newsService,
        private ArticleRepositoryInterface $articleRepository
    )
    {
    }


    public function handle()
    {
        foreach (NewsServiceEnum::cases() as $case) {
            try {
                $this->setNewsSource($case->value);

                $filters = $this->getFilters();
                $articles = $this->fetchAndFilterArticles($filters);

                if (!empty($articles)) {
                    $this->articleRepository->createArticle($articles);
                }
            } catch (\Exception $e) {
                Log::error('Error fetching news: ' . $e->getMessage());
            }
        }
    }

    /**
     * Set the news source strategy for the NewsService.
     */
    protected function setNewsSource(string $source): void
    {
        $apiService = match (strtolower($source)) {
            NewsServiceEnum::GUARDIAN->value => new GuardianService(new GuardianResponseAdapter()),
            NewsServiceEnum::NEWS_API->value => new NewsAPIService(new NewsAPIResponseAdapter()),
            NewsServiceEnum::NEW_YORK_TIMES->value => new NewYorkTimesService(new NewYorkTimesResponseAdapter()),
            default => throw new \InvalidArgumentException("Invalid source: $source"),
        };

        $this->newsService->setStrategy($apiService);
    }

    /**
     * Get filters for fetching news.
     */
    private function getFilters(): array
    {
        return [
            'q' => 'science OR technology OR health OR business OR world news OR Travel OR Adventure Sports OR Entertainment OR Books OR Jobs',
        ];
    }

    /**
     * Fetch news articles and filter them.
     */
    private function fetchAndFilterArticles(array $filters): array
    {
        $rawArticles = $this->newsService->fetchNews($filters);
        $articles = $this->mapArticles($rawArticles);
        $existingUrls = $this->getExistingUrls($articles);

        return array_filter($articles, fn($article) => !in_array($article['source_url'], $existingUrls));
    }

    /**
     * Map raw articles to a structured format.
     */
    private function mapArticles(array $rawArticles): array
    {
        return array_map(function ($article) {
            if (empty($article->title) || empty($article->source_url)) {
                return null;
            }

            return [
                'title' => $article->title,
                'source_url' => $article->source_url,
                'content' => $article->content ?? null,
                'source' => $article->source,
                'author' => $article->author,
                'published_at' => $article->published_at,
                'metadata' => $article->metadata,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $rawArticles);
    }

    /**
     * Get existing article URLs from the database.
     */
    private function getExistingUrls(array $articles): array
    {
        return DB::table('articles')
            ->whereIn('source_url', array_column($articles, 'source_url'))
            ->pluck('source_url')
            ->toArray();
    }
}
