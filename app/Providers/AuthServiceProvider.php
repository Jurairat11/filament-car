<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    protected $policies = [
        //'App\Model' => 'App\Policies\ModelPolicy',
        User::class => UserPolicy::class,
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //$this->registerPolicies();
        //ประกาศ Admin ดูแลระบบทั้งหมด
        Gate::before(function ($user, $ability) {
        return $user->hasRole('Admin') ? true : null;
    });
    }

}
