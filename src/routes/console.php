<?php

use App\Contracts\ArticleRepositoryInterface;
use App\Jobs\FetchNewsJob;
use App\Services\NewsService;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new FetchNewsJob(app(NewsService::class), app(ArticleRepositoryInterface::class)))->hourly();
