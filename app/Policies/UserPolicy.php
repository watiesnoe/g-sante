<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return $user->hasRole(['admin', 'secretaire']);
    }

    public function view(User $user, User $model)
    {
        return $user->id === $model->id
            || $user->hasRole(['admin', 'secretaire']);
    }

    public function create(User $user)
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    public function update(User $user, User $model)
    {
        if ($user->hasRole('admin') && !$model->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole(['medecin', 'secretaire'])) {
            return $user->id === $model->id;
        }

        return false;
    }

    public function delete(User $user, User $model)
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('admin') && !$model->hasRole('super_admin')) {
            return true;
        }

        return false;
    }

    public function restore(User $user, User $model)
    {
        return $user->hasRole('super_admin');
    }

    public function forceDelete(User $user, User $model)
    {
        return $user->hasRole('super_admin');
    }
}
