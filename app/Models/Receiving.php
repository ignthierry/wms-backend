<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receiving extends Model
{
    use HasFactory;

    protected $fillable = [
        'asn_id',
        'received_by',
        'gate_number',
        'start_time',
        'end_time',
        'status'
    ];

    public function asn()
    {
        return $this->belongsTo(Asn::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
