<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected bool $isSuperAdmin = false;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->isSuperAdmin = (bool) ($data['is_super_admin'] ?? false);
        unset($data['is_super_admin']);

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->isSuperAdmin) {
            $this->record->assignRole('super_admin');
        } else {
            $this->record->removeRole('super_admin');
        }
    }
}
