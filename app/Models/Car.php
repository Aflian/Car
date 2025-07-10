<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'merk', 'no_plat', 'warna', 'tahun',
        'harga_dalam_kota', 'harga_luar_kota', 'denda_per_hari', 'gambar','status',
    ];

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }
    // File: app/Models/Car.php


    protected static function booted()
    {
        static::deleting(function ($car) {
            if ($car->gambar && Storage::disk('public')->exists($car->gambar)) {
                Storage::disk('public')->delete($car->gambar);
            }
        });
    }

}
