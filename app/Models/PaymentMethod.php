<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_metode', 'kode_static', 'no_rekening', 'atas_nama',
    ];

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }
}
