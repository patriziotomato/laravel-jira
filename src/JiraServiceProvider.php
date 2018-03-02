<?php

namespace LaravelJira;

use Illuminate\Support\ServiceProvider;

class JiraServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes( [
            __DIR__ . '/../config/jira.php' => config_path( 'jira.php' ),
        ] );

        $this->app->singleton(JiraService::class, function ($app) {
            return new JiraService(...config('jira'));
        });
    }

    public function register()
    {
        $this->mergeConfigFrom( __DIR__ . '/../config/jira.php', 'jira' );
    }
}
