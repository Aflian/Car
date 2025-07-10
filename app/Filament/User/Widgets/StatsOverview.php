<?php

namespace App\Filament\User\Widgets;

use App\Models\Car;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalMobilTersedia = Car::where('status','tersedia')->count();
        return [
            Stat::make('Total Mobil Tersedia',$totalMobilTersedia),
        ];
    }
}
