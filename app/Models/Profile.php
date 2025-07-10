<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','username', 'nama', 'jenis_kelamin', 'alamat',
        'no_hp', 'foto_ktp', 'foto_sim',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
