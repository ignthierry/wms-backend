<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'stock_id',
        'qty_system',
        'qty_physical',
        'difference',
        'counted_by'
    ];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function countedBy()
    {
        return $this->belongsTo(User::class, 'counted_by');
    }
}
