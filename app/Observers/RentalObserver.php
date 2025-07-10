<?php

namespace App\Observers;

use App\Models\Rental;
use App\Models\PenaltyNotification;
use Carbon\Carbon;

class RentalObserver
{
    public function updated(Rental $rental): void
    {
        // Jika sudah selesai dan belum pernah kena denda
        if (
            $rental->status === 'Selesai' &&
            !$rental->denda
        ) {
            $today = Carbon::now()->startOfDay();
            $returnDate = Carbon::parse($rental->tanggal_kembali);

            if ($today->gt($returnDate)) {
                $daysLate = $today->diffInDays($returnDate);
                $dendaPerHari = $rental->car->denda_per_hari;
                $totalDenda = $daysLate * $dendaPerHari;

                $rental->denda = true;
                $rental->total_biaya += $totalDenda;
                $rental->saveQuietly(); // Supaya tidak trigger observer terus-menerus

                PenaltyNotification::create([
                    'rental_id' => $rental->id,
                    'pesan' => "Anda mengembalikan mobil {$rental->car->merk} terlambat {$daysLate} hari. Denda Rp " . number_format($totalDenda, 0, ',', '.'),
                    'dibaca' => false,
                ]);
            }
        }
    }
}
