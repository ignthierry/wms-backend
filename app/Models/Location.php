<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'zone',
        'aisle',
        'rack_row',
        'tier',
        'barcode_loc',
        'is_empty',
        'capacity'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
