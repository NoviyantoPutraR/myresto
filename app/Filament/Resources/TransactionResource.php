<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionItemsResource\Pages\CreateTransactionItems;
use App\Filament\Resources\TransactionItemsResource\Pages\EditTransactionItems;
use App\Filament\Resources\TransactionItemsResource\Pages\ListTransactionItems;

use App\Models\TransactionItems;
use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Route;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Transaksi';

    public static function getModelLabel(): string
    {
        return 'Transaksi';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Transaksi';
    }

    public static function canAccessPanel(\Filament\Panel $panel): bool
    {
        return in_array($panel->getId(), ['admin', 'kasir']);
    }



    public static function getRecordTitle(?Model $record): string|null|Htmlable
    {
        return $record->name;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('external_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('checkout_link')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('barcodes_id')
                    ->label('QR Code')
                    ->image() // Hanya menerima file gambar
                    ->directory('qr_code') // Direktori penyimpanan
                    ->disk('public') // Disk penyimpanan
                    ->default(function ($record) {
                        return $record->barcodes->image ?? null;
                    }),
                Forms\Components\TextInput::make('payment_method')
                    ->required(),
                Forms\Components\TextInput::make('payment_status')
                    ->required(),
                Forms\Components\TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('ppn')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Transaction::query()->orderByDesc('created_at'))
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode Transaksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name') // Menampilkan nama kasir
                    ->label('Kasir/Admin')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Nomor Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('barcode.table_number') // Menampilkan nomor meja dari relasi Barcode
                    ->label('No Meja')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->colors([
                        'success' => fn($state): bool => in_array($state, ['SUCCESS', 'PAID', 'SETTLED']),
                        'warning' => fn($state): bool => $state === 'PENDING',
                        'danger' => fn($state): bool => in_array($state, ['FAILED', 'EXPIRED']),
                    ]),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->numeric()
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('ppn')
                    ->label('PPN')
                    ->numeric()
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->numeric()
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir/Admin')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Transaksi')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'PAID' => 'Sudah Terbayar',
                        'PENDING' => 'Menunggu Pembayaran',
                        'FAILED' => 'Gagal',
                        'EXPIRED' => 'Kedaluwarsa',
                    ]),


            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('Lihat Detail Transaksi')
                    ->label('Lihat Detail')
                    ->color('success')
                    ->url(
                        fn(Transaction $record): string => static::getUrl('transaction-items.index', [
                            'parent' => $record->id,
                        ])
                    ),
                Action::make('Cetak Bukti Pembayaran')
                    ->label('Cetak Bukti')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->url(fn(Transaction $record): string => route('transactions.print', $record->id))
                    ->openUrlInNewTab(),

            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),

            'transaction-items.index' => ListTransactionItems::route('/{parent}/transaction'),

        ];
    }
}
