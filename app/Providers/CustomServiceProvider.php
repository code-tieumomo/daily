<?php

namespace App\Providers;

use App\Http\Services\AuthService;
use App\Http\Services\ClassService;
use App\Http\Services\TeacherService;
use Illuminate\Support\ServiceProvider;

class CustomServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('auth-service', function () {
            return new AuthService();
        });

        $this->app->singleton('teacher-service', function () {
            return new TeacherService();
        });

        $this->app->singleton('class-service', function () {
            return new ClassService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
