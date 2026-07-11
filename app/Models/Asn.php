<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asn extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'warehouse_id',
        'asn_number',
        'eta',
        'driver_name',
        'vehicle_plate',
        'status',
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
        'qr_id'
    ];

    protected static function booted()
    {
        static::creating(function ($asn) {
            if (empty($asn->qr_id)) {
                $asn->qr_id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(AsnItem::class);
    }
}
