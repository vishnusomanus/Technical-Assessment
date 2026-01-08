<?php

namespace App\Providers;

use App\Repositories\TaskRepository;
use App\Services\TaskService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Task Repository
        $this->app->singleton(TaskRepository::class, function ($app) {
            return new TaskRepository($app->make(\App\Models\Task::class));
        });

        // Register Task Service
        $this->app->singleton(TaskService::class, function ($app) {
            return new TaskService($app->make(TaskRepository::class));
        });
        
        // Fix for Laravel bug: Register connection resolver early
        // This ensures our custom connection is used for all MySQL connections
        \Illuminate\Support\Facades\DB::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            // Ensure prefix is always a string, never an array
            if (is_array($prefix)) {
                $prefix = '';
            } elseif (!is_string($prefix)) {
                $prefix = (string) ($prefix ?? '');
            }
            
            return new \App\Database\MySqlConnection($connection, $database, $prefix, $config);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for Laravel bug: getTablePrefix() returning array
        // Override the connection factory to use our custom connection class
        \Illuminate\Support\Facades\DB::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            // Ensure prefix is always a string, never an array
            if (is_array($prefix)) {
                $prefix = '';
            } elseif (!is_string($prefix)) {
                $prefix = (string) ($prefix ?? '');
            }
            
            return new \App\Database\MySqlConnection($connection, $database, $prefix, $config);
        });
    }
}

