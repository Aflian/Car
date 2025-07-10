<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenaltyNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_id', 'pesan', 'dibaca',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }
}
