<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;

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
        DB::extend('odbc', function ($config, $name) {
            $dsn = $config['dsn'];
            $username = $config['username'];
            $password = $config['password'];
            $options = $config['options'] ?? [];

            $pdo = new \PDO("odbc:$dsn", $username, $password, $options);

            return new Connection($pdo, $config['database'], $config['prefix'] ?? '', $config);
        });
    }
}
