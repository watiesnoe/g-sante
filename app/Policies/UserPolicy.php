<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->role === 'superadmin') {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'secretaire']);
    }

    public function view(User $user, User $model)
    {
        return $user->id === $model->id
            || in_array($user->role, ['admin', 'secretaire']);
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'superadmin']);
    }

    public function update(User $user, User $model)
    {
        if ($user->role === 'admin' && $model->role !== 'superadmin') {
            return true;
        }

        if (in_array($user->role, ['medecin', 'secretaire'])) {
            return $user->id === $model->id;
        }

        return false;
    }

    public function delete(User $user, User $model)
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        if ($user->role === 'admin' && $model->role !== 'superadmin') {
            return true;
        }

        return false;
    }

    public function restore(User $user, User $model)
    {
        return $user->role === 'superadmin';
    }

    public function forceDelete(User $user, User $model)
    {
        return $user->role === 'superadmin';
    }
}
