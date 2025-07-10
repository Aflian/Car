<?php

namespace App\Notifications;

use App\Models\Rental;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DendaNotification extends Notification
{
    use Queueable;

    protected Rental $rental;

    public function __construct(Rental $rental)
    {
        $this->rental = $rental;
    }

    public function via($notifiable): array
    {
        return ['database']; // Panel Filament akan otomatis baca dari DB
    }

    public function toDatabase($notifiable): array
    {
        $car = $this->rental->car;
        $hariTerlambat = now()->diffInDays($this->rental->tanggal_kembali);
        $denda = $car->denda_per_hari * $hariTerlambat;

        return [
            'title' => 'Denda Keterlambatan Mobil',
            'body' => "Mobil {$car->merk} dikembalikan terlambat {$hariTerlambat} hari. Denda dikenakan: Rp " . number_format($denda, 0, ',', '.'),
            'rental_id' => $this->rental->id,
        ];
    }
}
