<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TruckingTarif extends Model
{
    use HasFactory;

    protected $fillable = [
        'trucking_company_id', 'nama_tarif', 'origin', 'destination',
        'vehicle_type', 'rate', 'rate_unit', 'minimum_charge', 'is_active'
    ];

    protected $casts = [
        'rate' => 'float',
        'minimum_charge' => 'float',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(TruckingCompany::class, 'trucking_company_id');
    }
}