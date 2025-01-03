<?php

namespace App\Providers;

use App\Adapters\GuardianResponseAdapter;
use App\Adapters\NewsAPIResponseAdapter;
use App\Adapters\NewYorkTimesResponseAdapter;
use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\ArticleServiceInterface;
use App\Contracts\NewsApiResponseAdapterInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\UserServiceInterface;
use App\Enums\NewsServiceEnum;
use App\Repositories\ArticleRepository;
use App\Repositories\UserRepository;
use App\Services\ArticleService;
use App\Services\UserService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Exceptions\Handler;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ExceptionHandler::class, Handler::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
        $this->app->bind(ArticleServiceInterface::class, ArticleService::class);

        $this->app->bind(NewsApiResponseAdapterInterface::class, function () {
            $source = NewsServiceEnum::tryFrom(config('services.news.default_source'));

            if (!$source) {
                throw new \InvalidArgumentException("Invalid news source: " . config('news.default_source'));
            }

            return match ($source) {
                NewsServiceEnum::NEWS_API => new NewsAPIResponseAdapter(),
                NewsServiceEnum::NEW_YORK_TIMES => new NewYorkTimesResponseAdapter(),
                NewsServiceEnum::GUARDIAN => new GuardianResponseAdapter(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('allowedRequestNumber', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });
    }
}
