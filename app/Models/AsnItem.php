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
        'block_location',
        'status'
    ];

    protected static function booted()
    {
        static::creating(function ($asnItem) {
            if (empty($asnItem->qr_id)) {
                $asnItem->qr_id = 'ITM-' . strtoupper(\Illuminate\Support\Str::random(8));
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

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'asn_item_id');
    }

    public function deliveryRequest()
    {
        return $this->hasOne(DeliveryRequest::class, 'asn_item_id');
    }

    public function photos()
    {
        return $this->hasMany(AsnItemPhoto::class, 'asn_item_id');
    }
}
