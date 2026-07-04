<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'location_id',
        'item_code',
        'item_name',
        'lot_number',
        'qty',
        'min_stock_alert',
        'expiry_date',
        'received_date'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
