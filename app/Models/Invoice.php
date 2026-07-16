<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'asn_id',
        'asn_item_id',
        'invoice_number',
        'storage_fee',
        'handling_fee',
        'total_amount',
        'status',
        'tgl_invoice'
    ];

    public function asn()
    {
        return $this->belongsTo(Asn::class);
    }

    public function asnItem()
    {
        return $this->belongsTo(AsnItem::class);
    }
}
