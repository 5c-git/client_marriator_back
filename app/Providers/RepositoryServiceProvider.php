<?php

namespace App\Providers;

use App\Services\Local\Repositories\Contracts\UserRepository;
use App\Services\Local\Repositories\User\CachingUserRepository;
use App\Services\Local\Repositories\User\EloquentUserRepository;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(
            abstract: UserRepository::class,
            concrete: fn($app) => new CachingUserRepository(
                new EloquentUserRepository(),
                $app->get(CacheManager::class),
            )
        );

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    public function provides()
    {
        return [
            UserRepository::class,
        ];
    }
}
