<?php

namespace Recoded\LaravelSQS\Providers;

use Aws\Sqs\SqsClient;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Recoded\LaravelSQS\Console\Commands\WorkCommand;

final class SqsServiceProvider extends ServiceProvider
{
    /** @var list<class-string<\Illuminate\Console\Command>> */
    protected array $commands = [
        WorkCommand::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/sqs.php', 'sqs');

        $this->publishes([
            __DIR__ . '/../../config' => config_path(),
        ], 'laravel-sqs');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->commands($this->commands);

        $this->app->bindIf(SqsClient::class, function (Container $container) {
            /** @var \Illuminate\Contracts\Config\Repository $configRepository */
            $configRepository = $container->make('config');

            $config = $configRepository->get('sqs.client');

            if (!is_array($config) || array_is_list($config)) {
                throw new InvalidArgumentException(
                    '"sqs.client" configuration should be an associative array',
                );
            }

            return new SqsClient([
                ...$config,
                'version' => '2012-11-05',
            ]);
        });
    }
}
