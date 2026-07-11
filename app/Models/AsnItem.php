<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'asn_id',
        'item_code',
        'item_name',
        'qty_expected',
        'lot_number',
        'expiry_date',
        'actual_weight',
        'actual_volume'
    ];

    public function asn()
    {
        return $this->belongsTo(Asn::class);
    }
}
