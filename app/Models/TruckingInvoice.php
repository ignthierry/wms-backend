<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TruckingInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'trucking_company_id', 'asn_id', 'invoice_number', 'invoice_type',
        'trucking_fee', 'warehouse_fee', 'total_amount', 'status', 'tgl_invoice', 'details'
    ];

    protected $casts = [
        'trucking_fee' => 'float',
        'warehouse_fee' => 'float',
        'total_amount' => 'float',
        'details' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(TruckingCompany::class, 'trucking_company_id');
    }

    public function asn()
    {
        return $this->belongsTo(Asn::class, 'asn_id');
    }
}