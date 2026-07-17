<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsnItemPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'asn_item_id',
        'photo_proof',
        'jenis_foto',
    ];

    public function item()
    {
        return $this->belongsTo(AsnItem::class, 'asn_item_id');
    }
}
