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

        collect([
            'components.write_code',  // accès à l'éditeur de code (Module 3)
            'components.assemble',    // accès à l'assemblage WYSIWYG (Module 4)
            'pages.manage',           // gestion des pages / back-office contenu (Module 5)
        ])->each(fn (string $name) => Permission::firstOrCreate(['name' => $name]));
    }
}
