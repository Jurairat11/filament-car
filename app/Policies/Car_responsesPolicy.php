<?php

namespace App\Policies;

use App\Models\Car_responses;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class Car_responsesPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if($user->hasPermissionTo('View Car Responses')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Car_responses $carResponses): bool
    {
        if($user->hasPermissionTo('View Car Responses')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if($user->hasPermissionTo('Create Car Responses')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Car_responses $carResponses): bool
    {
        if($user->hasPermissionTo('Update Car Responses')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Car_responses $carResponses): bool
    {
        if($user->hasPermissionTo('Delete Car Responses')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Car_responses $carResponses): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Car_responses $carResponses): bool
    {
        return false;
    }
}
