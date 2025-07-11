<?php

namespace App\Filament\Resources\FoodsResource\Pages;

use App\Filament\Resources\FoodsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFoods extends EditRecord
{
    protected static string $resource = FoodsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('cancel')
                ->label('Batal')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
                ->outlined(),

            Actions\Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save'),
        ];
    }
}
