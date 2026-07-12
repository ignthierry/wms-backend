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
        'pos_number',
        'expiry_date',
        'actual_weight',
        'actual_volume',
        'host_bl',
        'consignee_id',
        'packaging',
        'item_condition',
        'remarks',
        'photo_proof',
        'qr_id',
        'block_location'
    ];

    protected static function booted()
    {
        static::creating(function ($asnItem) {
            if (empty($asnItem->qr_id)) {
                $asnItem->qr_id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function asn()
    {
        return $this->belongsTo(Asn::class);
    }

    public function consignee()
    {
        return $this->belongsTo(Consignee::class);
    }
}
