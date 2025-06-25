<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Problem;
use App\Policies\UserPolicy;
use App\Models\Car_responses;
use Filament\Facades\Filament;
use App\Observers\ProblemObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Observers\Car_responsesObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Foundation\Application;

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
        Car_responses::observe(Car_responsesObserver::class);
        Gate::policy(User::class, UserPolicy::class);
        URL::forceScheme('https');

        ResetPassword::createUrlUsing(function (User $user, string $token) {
        return 'https://jp.edi-vcst.in.th/reset-password/'.$token;
    });

    }
}
