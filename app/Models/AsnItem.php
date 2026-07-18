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

        static::created(function ($asnItem) {
            if ($asnItem->status === 'RECEIVED') {
                $asnItem->histories()->create([
                    'action' => 'RECEIVED',
                    'description' => 'Barang masuk diterima',
                    'new_value' => 'RECEIVED'
                ]);
            }
        });

        static::updated(function ($asnItem) {
            if ($asnItem->isDirty('status')) {
                $old = $asnItem->getOriginal('status');
                $new = $asnItem->status;
                $asnItem->histories()->create([
                    'action' => 'STATUS_CHANGED',
                    'description' => "Status diubah dari {$old} menjadi {$new}",
                    'old_value' => $old,
                    'new_value' => $new
                ]);
            }

            if ($asnItem->isDirty('block_location')) {
                $old = $asnItem->getOriginal('block_location');
                $new = $asnItem->block_location;
                $asnItem->histories()->create([
                    'action' => 'POSITION_CHANGED',
                    'description' => "Posisi diubah dari " . ($old ?: 'Belum ditentukan') . " menjadi {$new}",
                    'old_value' => $old,
                    'new_value' => $new
                ]);
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

    public function histories()
    {
        return $this->hasMany(ItemHistory::class, 'asn_item_id')->orderBy('created_at', 'desc');
    }
}
