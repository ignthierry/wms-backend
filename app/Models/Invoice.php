<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'asn_id',
        'invoice_number',
        'storage_fee',
        'handling_fee',
        'total_amount',
        'status'
    ];

    public function asn()
    {
        return $this->belongsTo(Asn::class);
    }
}
