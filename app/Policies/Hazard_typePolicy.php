<?php

namespace App\Policies;

use App\Models\Hazard_type;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class Hazard_typePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if($user->hasPermissionTo('View Hazard Type')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Hazard_type $hazardType): bool
    {
        if($user->hasPermissionTo('View Hazard Type')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if($user->hasPermissionTo('Create Hazard Type')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Hazard_type $hazardType): bool
    {
    if($user->hasPermissionTo('Update Hazard Type')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Hazard_type $hazardType): bool
    {
        if($user->hasPermissionTo('Delete Hazard Type')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Hazard_type $hazardType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Hazard_type $hazardType): bool
    {
        return false;
    }
}
