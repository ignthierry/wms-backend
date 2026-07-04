<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asn extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'warehouse_id',
        'asn_number',
        'eta',
        'driver_name',
        'vehicle_plate',
        'status'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(AsnItem::class);
    }
}
