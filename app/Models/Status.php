<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_status',
        'jenis_status'
    ];

    protected $primaryKey = 'id_status';
    protected $table = "status";
}
