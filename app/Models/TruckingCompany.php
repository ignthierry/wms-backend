<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TruckingCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'company_code', 'address', 'phone', 'email',
        'pic_name', 'pic_phone', 'npwp', 'is_ours', 'is_active'
    ];

    protected $casts = [
        'is_ours' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tarifs()
    {
        return $this->hasMany(TruckingTarif::class, 'trucking_company_id');
    }

    public function invoices()
    {
        return $this->hasMany(TruckingInvoice::class, 'trucking_company_id');
    }

    public function asns()
    {
        return $this->hasMany(Asn::class, 'trucking_company_id');
    }
}