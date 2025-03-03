<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class NoStudentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return ($user->role_id === Role::ROLE_ADMIN && $user->is_verify) || $user->role_id === Role::ROLE_TEACHER;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, $model): bool
    {
        return ($user->role_id === Role::ROLE_ADMIN && $user->is_verify) || $user->role_id === Role::ROLE_TEACHER;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ($user->role_id === Role::ROLE_ADMIN && $user->is_verify) || $user->role_id === Role::ROLE_TEACHER;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, $model): bool
    {
        return ($user->role_id === Role::ROLE_ADMIN && $user->is_verify) || ($user->role_id === Role::ROLE_TEACHER && $model->user_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, $model): bool
    {
        return ($user->role_id === Role::ROLE_ADMIN && $user->is_verify) || ($user->role_id === Role::ROLE_TEACHER && $model->user_id === $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, $model): bool
    {
        return ($user->role_id === Role::ROLE_ADMIN && $user->is_verify) || ($user->role_id === Role::ROLE_TEACHER && $model->user_id === $user->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, $model): bool
    {
        return ($user->role_id === Role::ROLE_ADMIN && $user->is_verify) || ($user->role_id === Role::ROLE_TEACHER && $model->user_id === $user->id);
    }
}
