<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'dr_id',
        'dispatcher_by',
        'surat_jalan_number',
        'manifest_number',
        'expedition_name',
        'driver_name',
        'driver_phone',
        'dispatched_at',
        'status'
    ];

    public function deliveryRequest()
    {
        return $this->belongsTo(DeliveryRequest::class, 'dr_id');
    }

    public function dispatcherBy()
    {
        return $this->belongsTo(User::class, 'dispatcher_by');
    }
}
