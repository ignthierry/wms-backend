<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'dr_id',
        'item_code',
        'qty_requested',
        'lot_number'
    ];

    public function deliveryRequest()
    {
        return $this->belongsTo(DeliveryRequest::class, 'dr_id');
    }
}
