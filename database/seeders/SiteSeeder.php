<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        Site::firstOrCreate(
            ['nom' => 'Site principal'],
            [
                'domaine' => null,
                'statut' => 'actif',
            ]
        );
    }
}
