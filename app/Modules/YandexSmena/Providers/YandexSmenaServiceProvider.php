<?php

namespace Modules\YandexSmena\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\YandexSmena\Console\Commands\PollYandexSmenaEventsCommand;
use Modules\YandexSmena\Services\EventEnvelopeBuilder;
use Modules\YandexSmena\Services\Handlers\AdjustFactHandler;
use Modules\YandexSmena\Services\Handlers\EventResultHandler;
use Modules\YandexSmena\Services\Handlers\SignupWorkerHandler;
use Modules\YandexSmena\Services\Handlers\WithdrawWorkerHandler;
use Modules\YandexSmena\Services\SmenaEventProcessor;
use Modules\YandexSmena\Services\SmenaShiftLifecycleService;
use Modules\YandexSmena\Services\SmenaShiftPublisher;
use Modules\YandexSmena\Services\SmenaWorkerInteractionService;
use Modules\YandexSmena\Services\YandexSmenaApiClient;
use Modules\YandexSmena\Services\YandexSmenaApiClientInterface;
use Modules\YandexSmena\Services\YandexSmenaEventPublisher;

class YandexSmenaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('yandex-smena.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                PollYandexSmenaEventsCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'yandex-smena'
        );

        $this->app->singleton(YandexSmenaApiClientInterface::class, function ($app) {
            return new YandexSmenaApiClient(
                $app['config']->get('yandex-smena.host'),
                $app['config']->get('yandex-smena.token'),
            );
        });

        $this->app->singleton(EventEnvelopeBuilder::class);

        $this->app->singleton(YandexSmenaEventPublisher::class, function ($app) {
            return new YandexSmenaEventPublisher(
                $app['config']->get('yandex-smena.queue'),
                $app->make(EventEnvelopeBuilder::class)
            );
        });

        $this->app->singleton(SmenaShiftPublisher::class, function ($app) {
            return new SmenaShiftPublisher(
                $app->make(YandexSmenaEventPublisher::class),
                $app->make(\Modules\YandexSmena\Services\Mappers\ShiftMapper::class)
            );
        });

        $this->app->singleton(SmenaShiftLifecycleService::class, function ($app) {
            return new SmenaShiftLifecycleService(
                $app->make(YandexSmenaEventPublisher::class)
            );
        });

        $this->app->singleton(SmenaWorkerInteractionService::class, function ($app) {
            return new SmenaWorkerInteractionService(
                $app->make(YandexSmenaEventPublisher::class)
            );
        });

        $this->app->singleton(SmenaEventProcessor::class, function ($app) {
            return new SmenaEventProcessor(
                $app->make(YandexSmenaApiClientInterface::class),
                [
                    'smena.shift.signup_worker' => SignupWorkerHandler::class,
                    'smena.shift.withdraw_worker' => WithdrawWorkerHandler::class,
                    'smena.shift.adjust_fact' => AdjustFactHandler::class,
                    'smena.event.result' => EventResultHandler::class,
                ]
            );
        });
    }
}
