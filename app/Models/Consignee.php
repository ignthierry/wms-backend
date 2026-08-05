<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consignee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'status'
    ];

    public function forwarding()
    {
        return $this->belongsTo(Forwarding::class);
    }

    public function items()
    {
        return $this->hasMany(AsnItem::class);
    }
}
