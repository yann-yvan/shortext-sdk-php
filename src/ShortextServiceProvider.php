<?php

namespace NyCorp\Shortext;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use NyCorp\Shortext\Webhooks\WebhookVerifier;

/**
 * Laravel integration: config/shortext.php, Shortext and WebhookVerifier in the container, Shortext facade.
 */
class ShortextServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shortext.php', 'shortext');

        $this->app->singleton(Shortext::class, static function (Application $app): Shortext {
            $config = $app['config']->get('shortext', []);

            return new Shortext(
                (string) ($config['api_key'] ?? ''),
                $config['base_url'] ?? null,
                array_filter([
                    'timeout' => $config['timeout'] ?? null,
                    'connect_timeout' => $config['connect_timeout'] ?? null,
                ], static fn ($value) => $value !== null),
            );
        });

        $this->app->singleton(WebhookVerifier::class, static function (Application $app): WebhookVerifier {
            return new WebhookVerifier(
                (string) $app['config']->get('shortext.webhooks.secret', ''),
                (int) $app['config']->get('shortext.webhooks.tolerance', WebhookVerifier::DEFAULT_TOLERANCE),
            );
        });

        $this->app->alias(Shortext::class, 'shortext');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/shortext.php' => $this->app->configPath('shortext.php'),
            ], 'shortext-config');
        }
    }
}
