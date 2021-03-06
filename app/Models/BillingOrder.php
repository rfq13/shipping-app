<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingOrder extends Model
{
    use HasFactory;

    function orders()
    {
        return $this->hasOne(Order::class,'id','order_id');
    }
}
