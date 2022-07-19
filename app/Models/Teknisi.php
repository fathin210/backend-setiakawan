<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teknisi extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'nama',
    ];

    protected $table = 'teknisi';
    protected $primaryKey = 'id_teknisi';

    public function pendaftaran()
    {
        return $this->hasMany(Antrian::class);
    }
}
