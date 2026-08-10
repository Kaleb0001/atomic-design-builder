<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeSuperAdmin extends Command
{
    protected $signature = 'app:make-super-admin {email}';

    protected $description = "Attribue le rôle Super Admin à un utilisateur existant, à partir de son email";

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("Aucun utilisateur trouvé avec l'email : {$this->argument('email')}");

            return self::FAILURE;
        }

        $user->assignRole('super_admin');

        $this->info("{$user->name} ({$user->email}) est maintenant Super Admin.");

        return self::SUCCESS;
    }
}
