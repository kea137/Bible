<?php

namespace App\Policies;

use App\Models\User;

class ReferencePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view references
        return $user->roles()->whereIn('role_number', [1, 2, 3])->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        // All authenticated users can view references
        return $user->roles()->whereIn('role_number', [1, 2, 3])->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin (role number 1) can create references
        return $user->roles()->whereIn('role_number', [1])->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        // Admin (role 1) can update references
        return $user->roles()->whereIn('role_number', [1])->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        // Only admin (role number 1) can delete
        return $user->roles()->where('role_number', 1)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user): bool
    {
        // Only admin (role number 1) can restore
        return $user->roles()->where('role_number', 1)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user): bool
    {
        // Only admin (role number 1) can force delete
        return $user->roles()->where('role_number', 1)->exists();
    }
}
