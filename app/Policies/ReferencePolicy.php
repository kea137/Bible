<?php

namespace App\Policies;

use App\Models\Reference;
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
    public function view(User $user, Reference $reference): bool
    {
        // All authenticated users can view references
        return $user->roles()->whereIn('role_number', [1, 2, 3])->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin (role number 1) and editor (role number 2) can create references
        return $user->roles()->whereIn('role_number', [1, 2])->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reference $reference): bool
    {
        // Admin (role 1) and editor (role 2) can update references
        return $user->roles()->whereIn('role_number', [1, 2])->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reference $reference): bool
    {
        // Only admin (role number 1) can delete
        return $user->roles()->where('role_number', 1)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Reference $reference): bool
    {
        // Only admin (role number 1) can restore
        return $user->roles()->where('role_number', 1)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reference $reference): bool
    {
        // Only admin (role number 1) can force delete
        return $user->roles()->where('role_number', 1)->exists();
    }
}
