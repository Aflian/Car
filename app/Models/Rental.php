<?php
namespace App\Models;

use App\Models\Car;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'car_id', 'driver_id', 'tanggal_rental', 'tanggal_kembali',
        'jenis_pemakaian', 'payment_method_id', 'bukti_pembayaran',
        'jumlah_hari', 'total_biaya', 'status', 'denda',
    ];

    protected static function booted()
    {
        static::creating(function ($rental) {
            self::hitungBiaya($rental);
        });

        static::updating(function ($rental) {
            self::hitungBiaya($rental);
        });
    }

    protected static function hitungBiaya($rental)
    {
        // Hitung jumlah hari sewa
        $tanggalRental = Carbon::parse($rental->tanggal_rental);
        $tanggalKembali = Carbon::parse($rental->tanggal_kembali);
        $jumlahHari = $tanggalRental->diffInDays($tanggalKembali);

        // Ambil data mobil
        $car = Car::find($rental->car_id);

        // Tentukan harga per hari
        if ($rental->jenis_pemakaian === 'Dalam Kota') {
            $hargaPerHari = $car->harga_dalam_kota;
        } else {
            $hargaPerHari = $car->harga_luar_kota;
        }

        // Hitung total biaya
        $totalBiaya = $jumlahHari * $hargaPerHari;

        // Set nilai ke rental
        $rental->jumlah_hari = $jumlahHari;
        $rental->total_biaya = $totalBiaya;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function penaltyNotification()
    {
        return $this->hasOne(PenaltyNotification::class);
    }
}
