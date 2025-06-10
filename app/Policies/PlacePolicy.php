<?php

namespace App\Policies;

use App\Models\Place;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlacePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if($user->hasPermissionTo('View Place')){
            return true;
        }
        return false;
    }

    // /**
    //  * Determine whether the user can view the model.
    //  */
    public function view(User $user, Place $place): bool
    {
        if($user->hasPermissionTo('View Place')){
            return true;
        }
        return false;
    }

    // /**
    //  * Determine whether the user can create models.
    //  */
    public function create(User $user): bool
    {
        if($user->hasPermissionTo('Create Place')){
            return true;
        }
        return false;
    }

    // /**
    //  * Determine whether the user can update the model.
    //  */
    public function update(User $user, Place $place): bool
    {
        if($user->hasPermissionTo('Update Place')){
            return true;
        }
        return false;
    }

    // /**
    //  * Determine whether the user can delete the model.
    //  */
    public function delete(User $user, Place $place): bool
    {
        if($user->hasPermissionTo('Delete Place')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Place $place): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Place $place): bool
    {
        return false;
    }
}
