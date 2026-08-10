<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, User $model): bool
    {
        // Un Super Admin ne peut pas se supprimer lui-même par erreur.
        return $user->hasRole('super_admin') && $user->isNot($model);
    }
}
