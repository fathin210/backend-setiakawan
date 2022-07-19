<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TarifGigi extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id_tarif',
        'tarif_gigi'
    ];
    

    protected $primaryKey = 'id_tarif';
    protected $table = "tarif_gigi";

}
