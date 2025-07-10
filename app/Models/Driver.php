<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama', 'no_hp', 'alamat', 'foto_sim', 'status',
    ];

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }
}
