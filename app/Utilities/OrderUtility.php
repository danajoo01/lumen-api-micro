<?php

namespace App\Utilities;

use App\Models\ApiOrder;
use Illuminate\Support\Str;

class OrderUtility
{
    const CREATED = 0;
    const WAITING_PAYMENT = 1;
    const PAYMENT_REJECTED = 2;
    const EXPIRED = 3;
    const CANCELED = 4;
    const PAYMENT_SUCCESS = 5;
    const WAITING_SHIPPING = 6;
    const ON_SHIPPING = 7;
    const SHIPPED = 8;
    public static function generate_code()
    {
        $orders = (ApiOrder::count()+1);
        return "ORD/".$orders."/".Str::random(5)."/".time();
    }
}
