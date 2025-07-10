<?php

namespace App\Filament\Widgets;

use App\Models\Car;
use App\Models\User;
use App\Models\Driver;
use App\Models\Rental;
use App\Models\PaymentMethod;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalMobil = Car::count();
        $totalMobilTersedia = Car::where('status','tersedia')->count();
        $totalMobilRental = Car::where('status','dirental')->count();
        $totalUser = User::count();
        $totalDriver = Driver::count();
        $totalDriverTersedia = Driver::where('status','tersedia')->count();
        $totalDriverDisewa = Driver::where('status','tidak tersedia')->count();
        $totalRental = Rental::where('status','Selesai')->count();
        $totalPembayaran = PaymentMethod::count();
        return [
            Stat::make('Total Mobil', $totalMobil ),
            Stat::make('Total Mobil Tersedia', $totalMobilTersedia ),
            Stat::make('Total Mobil Di Rental', $totalMobilRental ),
            Stat::make('Total User', $totalUser ),
            Stat::make('Total Driver', $totalDriver ),
            Stat::make('Driver Disewa ', $totalDriverDisewa ),
            Stat::make('Driver Tersedia ', $totalDriverTersedia ),
            Stat::make('Transaksi Selesai', $totalRental ),
            Stat::make('Metode Pembayaran', $totalPembayaran ),
        ];
    }
}
