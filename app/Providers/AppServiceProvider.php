<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Problem;
use App\Policies\UserPolicy;
use Filament\Support\Assets\Js;
use App\Observers\ProblemObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentAsset;

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
        Problem::observe(ProblemObserver::class);
        Gate::policy(User::class, UserPolicy::class);

    //     FilamentAsset::register([
    //     Js::make('chart-js-plugins', Vite::asset('resources/js/filament-chart-js-plugins.js'))->module(),
    // ]);
    }
}
