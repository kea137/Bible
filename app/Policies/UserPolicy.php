<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only admin (role number 1) can view users
        return $user->roles()->where('role_number', 1)->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function updateRoles(User $user, User $model): bool
    {
        // Only admin (role 1) can update roles
        return $user->roles()->where('role_number', 1)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Only admin (role number 1) can delete users
        // Cannot delete yourself
        return $user->roles()->where('role_number', 1)->exists() && $user->id !== $model->id;
    }
}
