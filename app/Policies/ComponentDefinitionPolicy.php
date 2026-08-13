<?php

namespace App\Policies;

use App\Models\ComponentDefinition;
use App\Models\User;

class ComponentDefinitionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('components.write_code') || $user->can('components.assemble');
    }

    public function view(User $user, ComponentDefinition $model): bool
    {
        return $user->can('components.write_code') || $user->can('components.assemble');
    }

    public function create(User $user): bool
    {
        return $user->can('components.write_code') || $user->can('components.assemble');
    }

    public function update(User $user, ComponentDefinition $model): bool
    {
        return $user->can('components.write_code') || $user->can('components.assemble');
    }

    // Volontairement plus restrictif : la suppression reste réservée aux profils
    // techniques (write_code) ou au Super Admin. À élargir si besoin.
    public function delete(User $user, ComponentDefinition $model): bool
    {
        return $user->can('components.write_code');
    }
}
