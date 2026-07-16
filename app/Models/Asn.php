<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asn extends Model
{
    use HasFactory;

    protected $fillable = [
        'forwarding_id',
        'warehouse_id',
        'asn_number',
        'eta',
        'driver_name',
        'vehicle_plate',
        'no_master_bl',
        'tgl',
        'tanggal_tiba',
        'tanggal_stripping',
        'tgl_in_container',
        'out_container',
        'no_segel',
        'voyage',
        'jumlah_pos',
        'no_container',
        'size',
        'qr_id',
        'trucking_company'
    ];

    protected static function booted()
    {
        static::creating(function ($asn) {
            if (empty($asn->qr_id)) {
                $asn->qr_id = 'ASN-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }

    public function forwarding()
    {
        return $this->belongsTo(Forwarding::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(AsnItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
