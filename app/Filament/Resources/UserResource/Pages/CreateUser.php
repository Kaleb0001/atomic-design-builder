<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected bool $isSuperAdmin = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->isSuperAdmin = (bool) ($data['is_super_admin'] ?? false);
        unset($data['is_super_admin']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->isSuperAdmin) {
            $this->record->assignRole('super_admin');
        }
    }
}
