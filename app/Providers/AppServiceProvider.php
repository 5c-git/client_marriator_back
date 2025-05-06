<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use App\Models\User;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);

        Passport::ignoreRoutes();
        Passport::hashClientSecrets();
        Passport::tokensExpireIn(now()->addDays(7));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addDays(7));

        Passport::tokensCan([
            'register' => 'Регистрация',
            'personalArea' => 'Полный доступ',
            'checkPin' => 'Проверка пина',
            'restorePin' => 'Восстановление пина',
        ]);
    }
}
