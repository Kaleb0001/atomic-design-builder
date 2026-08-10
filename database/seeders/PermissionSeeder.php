<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'super_admin']);

        // Les permissions des modules suivants viendront s'ajouter ici
        // (une ligne par permission, pas de migration nécessaire).
        collect([
            'components.write_code',  // accès à l'éditeur de code (Module 3)
            'components.assemble',    // accès à l'assemblage WYSIWYG (Module 4)
        ])->each(fn (string $name) => Permission::firstOrCreate(['name' => $name]));
    }
}
