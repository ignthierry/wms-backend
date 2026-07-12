<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forwarding extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'forwarding_name',
        'company_name',
        'email',
        'phone',
        'address'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function consignees()
    {
        return $this->hasMany(Consignee::class);
    }
}
