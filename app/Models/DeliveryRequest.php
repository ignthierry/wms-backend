<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'forwarding_id',
        'warehouse_id',
        'dr_number',
        'request_date',
        'recipient_name',
        'delivery_address',
        'status'
    ];

    public function forwarding()
    {
        return $this->belongsTo(Forwarding::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(DrItem::class, 'dr_id');
    }
}
