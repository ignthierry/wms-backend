<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_tarif',
        'storage_masa_1',
        'storage_masa_2',
        'storage_masa_3',
        'storage_masa_4',
        'administrasi',
        'minimum_tarif',
        'mekanis',
        'service',
        'surveyor_fee',
        'behandle',
        'stiker'
    ];
}
