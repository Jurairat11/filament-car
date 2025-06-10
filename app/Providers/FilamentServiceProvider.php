<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    // Filament::registerRenderHook('panels::auth.login', function (){
    //     $user = Auth::user();
    //     if($user->hasRole('User')){
    //     return redirect()->to('filament.pages.general-user-dashboard');
    //     }
    //     return redirect()->to('/dashboard');
    // });
    }
}
