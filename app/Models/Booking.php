<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id_pasien',
        'nomor_booking',
        'tanggal',
        'jam',
        'alasan_batal'
    ];

    protected $table = 'booking';
    protected $primaryKey = 'id_booking';

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'id_pasien');
    }

}
