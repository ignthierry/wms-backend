<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'asn_item_id',
        'action',
        'description',
        'old_value',
        'new_value',
    ];

    public function asnItem()
    {
        return $this->belongsTo(AsnItem::class);
    }
}
