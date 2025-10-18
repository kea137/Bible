<?php

namespace App\Policies;

use App\Models\Bible;
use App\Models\User;

class BiblePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All roles can view any Bibles
        return $user->roles()->whereIn('role_id', [1, 2, 3])->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Bible $bible): bool
    {
        // All roles can view a Bible
        return $user->roles()->whereIn('role_id', [1, 2, 3])->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin (role 1) can create
        return $user->roles()->where('role_id', 1)->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bible $bible): bool
    {
        // Admin (1) and editor (2) can update
        return $user->roles()->whereIn('role_id', [1, 2])->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bible $bible): bool
    {
        // Only admin (role 1) can delete
        return $user->roles()->where('role_id', 1)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Bible $bible): bool
    {
        // Only admin (role 1) can restore
        return $user->roles()->where('role_id', 1)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Bible $bible): bool
    {
        // Only admin (role 1) can force delete
        return $user->roles()->where('role_id', 1)->exists();
    }
}
