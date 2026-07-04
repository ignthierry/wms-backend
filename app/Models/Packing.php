<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Packing extends Model
{
    use HasFactory;

    protected $fillable = [
        'dr_id',
        'packed_by',
        'start_time',
        'end_time',
        'barcode_scanned_count',
        'packing_photo'
    ];

    public function deliveryRequest()
    {
        return $this->belongsTo(DeliveryRequest::class, 'dr_id');
    }

    public function packedBy()
    {
        return $this->belongsTo(User::class, 'packed_by');
    }
}
