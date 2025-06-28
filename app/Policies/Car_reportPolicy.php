<?php

namespace App\Policies;

use App\Models\Car_report;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class Car_reportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if($user->hasPermissionTo('View Any Car Report')){
            return true;
        }
        return false;

         // ปิด access สำหรับ role 'user'
        //return $user->role === ['Safety','Admin'];
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Car_report $carReport): bool
    {
        if($user->hasPermissionTo('View Car Report')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if($user->hasPermissionTo('Create Car Report')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Car_report $carReport): bool
    {
        if($user->hasPermissionTo('Update Car Report')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Car_report $carReport): bool
    {
        if($user->hasPermissionTo('Delete Car Report')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Car_report $carReport): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Car_report $carReport): bool
    {
        return false;
    }
}
