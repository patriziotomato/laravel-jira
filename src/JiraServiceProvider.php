<?php

namespace LaravelJira;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class JiraServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/jira.php' => config_path('jira.php'),
        ]);

        $this->app->singleton(Jira::class, function (Application $app) {
            $configuration = config('jira');

            if (!$configuration['host']) {
                throw new RuntimeException('No Jira host specified');
            }

            if (!$configuration['user']) {
                throw new RuntimeException('No Jira user specified');
            }

            if (!$configuration['accesstoken']) {
                throw new RuntimeException('No Jira Access token specified');
            }

            return new Jira($configuration['host'], $configuration['user'], $configuration['accesstoken']);
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/jira.php', 'jira');
    }
}
