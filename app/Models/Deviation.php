<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deviation extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_id',
        'item_code',
        'qty_diff',
        'damage_condition',
        'photo_url'
    ];

    public function receiving()
    {
        return $this->belongsTo(Receiving::class);
    }
}
