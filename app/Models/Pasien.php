<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Pasien extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'nomor_pasien',
        'nama',
        'password',
        'jenis_kelamin',
        'alamat',
        'no_telepon'
    ];

    protected $hidden = [
        'password',
    ];

    protected $table = 'pasien';
    protected $primaryKey = 'id_pasien';

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function pendaftaran()
    {
        return $this->hasMany(Antrian::class, 'id_pasien');
    }
}
