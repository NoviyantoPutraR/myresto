<?php

namespace App\Filament\Resources\BarcodeResource\Pages;

use App\Filament\Resources\BarcodeResource;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Resources\Pages\Page;
use App\Models\Barcode;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CreateQr extends Page
{
    protected static string $resource = BarcodeResource::class;
    protected static string $view = 'filament.resources.barcode-resource.pages.create-qr';

    public $table_number;

    public function mount(): void
    {
        $this->form->fill();
        $this->table_number = 1; // Default to table number 1
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('table_number')
                    ->label('Nomor Meja')
                    ->required()
                    ->options([
                        1 => 'Meja 1',
                        2 => 'Meja 2',
                        3 => 'Meja 3',
                        4 => 'Meja 4',
                        5 => 'Meja 5',
                        6 => 'Meja 6',
                        7 => 'Meja 7',
                        8 => 'Meja 8',
                        9 => 'Meja 9',
                        10 => 'Meja 10',
                    ])
                    ->default(fn() => $this->table_number),
            ]);
    }

    public function save(): void
    {
        $host = $_SERVER['HTTP_HOST'] . '/' . $this->table_number;

        // Generate the QR code as an SVG image
        $svgContent = QrCode::margin(1)->size(200)->generate($host);

        // Define the file path for the SVG
        $svgFilePath = 'qr_codes/' . $this->table_number . '.svg';

        // Save the SVG content to storage
        Storage::disk('public')->put($svgFilePath, $svgContent);

        // Save the form data, including the SVG QR code image path
        Barcode::create([
            'table_number' => $this->table_number,
            'image' => $svgFilePath,
            'qr_value' => $host // Save SVG file path
        ]);


        // Send success notification
        Notification::make()
            ->title('QR Code Terbuat')
            ->success()
            ->icon('heroicon-o-check-circle')
            ->send();

        // Redirect to the barcode list
        $this->redirect(url('admin/barcodes'));
    }
}
