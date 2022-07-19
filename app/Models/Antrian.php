<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Antrian extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_pendaftaran',
        'id_pasien',
        'id_teknisi',
        'id_admin',
        'id_pelayanan',
        'jumlah_gigi',
        'id_status',
        'id_tarif',
        'total_biaya',
        'tanggal_pelaksanaan'
    ];

    protected $primaryKey = 'id_antrian';
    protected $table = "antrian";

    protected $attributes = [
     'id_status' => 1,
     'total_biaya' => 0
    ];

    public function teknisi()
    {
        return $this->belongsTo(Teknisi::class, 'id_teknisi');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'id_status');
    }

    public function pelayanan()
    {
        return $this->belongsTo(Pelayanan::class, 'id_pelayanan');
    }

    public function tarif()
    {
        return $this->belongsTo(TarifGigi::class, 'id_tarif');
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'id_pasien');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin');
    }

}
