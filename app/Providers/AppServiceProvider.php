<?php

namespace App\Providers;

use App\Jobs\UpdatePermStatus;
use App\Models\User;
use App\Models\Problem;
use App\Policies\UserPolicy;
use App\Models\Car_responses;
use Filament\Facades\Filament;
use App\Observers\ProblemObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Observers\Car_responsesObserver;
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

        // $this->app->bindMethod([UpdatePermStatus::class, 'handle'], function (UpdatePermStatus $job) {
        //     return $job->handle();
        // });

    //     Filament::registerRenderHook('panels::auth.login', function (){
    //     $user = Auth::user();
    //     if($user->hasRole('User')){
    //     return redirect()->to('filament.pages.general-user-dashboard');
    //     }
    //     return redirect()->to('/dashboard');
    // });
    }
}
