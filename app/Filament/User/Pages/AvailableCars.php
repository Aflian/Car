<?php
namespace App\Filament\User\Pages;

use App\Models\Car;
use Filament\Pages\Page;

class AvailableCars extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static string $view = 'filament.user.pages.available-cars';
    protected static ?string $title = 'Mobil Tersedia';
    protected static ?string $navigationGroup = 'Pemesanan';

    public $cars;

    public function mount(): void
    {
        $this->cars = Car::where('status', 'tersedia')->get();
    }
}
