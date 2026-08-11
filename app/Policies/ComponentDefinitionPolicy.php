<?php

namespace App\Policies;

use App\Models\ComponentDefinition;
use App\Models\User;

class ComponentDefinitionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('components.write_code');
    }

    public function view(User $user, ComponentDefinition $model): bool
    {
        return $user->can('components.write_code');
    }

    public function create(User $user): bool
    {
        return $user->can('components.write_code');
    }

    public function update(User $user, ComponentDefinition $model): bool
    {
        return $user->can('components.write_code');
    }

    public function delete(User $user, ComponentDefinition $model): bool
    {
        return $user->can('components.write_code');
    }
}
