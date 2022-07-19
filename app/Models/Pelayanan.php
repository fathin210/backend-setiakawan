<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelayanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_pelayanan',
        'jenis_pelayanan'
    ];

    protected $primaryKey = 'id_pelayanan';
    protected $table = "pelayanan";
}
