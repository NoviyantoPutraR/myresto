<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use App\Models\User;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }


    public function boot(): void
    {
        Filament::serving(function () {
            $user = filament()->auth()->user();


            $navigationItems = [
                NavigationItem::make('Dashboard')
                    ->url('/admin')
                    ->icon('heroicon-o-home')
                    ->sort(1),

                NavigationItem::make('Transaksi')
                    ->url('/admin/transactions')
                    ->icon('heroicon-o-currency-dollar')
                    ->sort(2),

                NavigationItem::make('Kategori Makanan')
                    ->url('/admin/categories')
                    ->icon('heroicon-o-rectangle-stack')
                    ->sort(3),

                NavigationItem::make('Data Makanan')
                    ->url('/admin/foods')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->sort(4),
            ];

            // Menambahkan navigasi khusus untuk admin
            if ($user && $user instanceof User && $user->hasRole('admin')) {
                $navigationItems[] = NavigationItem::make('QR Meja')
                    ->url('/admin/barcodes')
                    ->icon('heroicon-o-qr-code')
                    ->sort(5);

                $navigationItems[] = NavigationItem::make('Hak Akses Pengguna')
                    ->url('/admin/users')
                    ->icon('heroicon-o-user')
                    ->sort(6);
            }

            // Daftarkan semua item navigasi
            Filament::registerNavigationItems($navigationItems);
        });
    }
}
