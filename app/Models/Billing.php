<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    function orders()
    {
        return $this->hasMany(BillingOrder::class);
    }
}