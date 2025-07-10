<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        $cars = [
            [
                'merk' => 'Toyota Avanza',
                'no_plat' => 'BM 1234 AA',
                'tahun' => 2020,
                'warna' => 'Hitam',
                'harga_dalam_kota' => 350000,
                'harga_luar_kota' => 450000,
                'status' => 'tersedia',
                'denda_per_hari'=>50000,
                'gambar' => 'mobil/avanza.jpg',
            ],
            [
                'merk' => 'Honda Brio',
                'no_plat' => 'BM 5678 BB',
                'tahun' => 2021,
                'warna' => 'Putih',
                'harga_dalam_kota' => 300000,
                'harga_luar_kota' => 400000,
                'status' => 'tersedia',
                'denda_per_hari'=>50000,
                'gambar' => 'mobil/brio.jpg',
            ],
            [
                'merk' => 'Daihatsu Xenia',
                'no_plat' => 'BM 9101 CC',
                'tahun' => 2019,
                'warna' => 'Silver',
                'harga_dalam_kota' => 320000,
                'harga_luar_kota' => 420000,
                'status' => 'tersedia',
                'denda_per_hari'=>50000,
                'gambar' => 'mobil/xenia.jpg',
            ],
        ];

        foreach ($cars as $car) {
            Car::create($car);
        }
    }
}
