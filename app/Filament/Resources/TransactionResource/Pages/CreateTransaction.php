<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;




class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    // app/Filament/Resources/TransactionResource/Pages/CreateTransaction.php

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth('kasir')->user() ?? auth('admin')->user();
        $data['user_id'] = $user?->id;

        return $data;
    }
}
