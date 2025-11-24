<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    // Testing mode - orders complete immediately
    const COMPLETED = 1;
    const PENDING = 2;
    const REFUNDED = 3;
    const PARTIALLY_REFUNDED = 4;
    const CANCELLED = 5;
    
    // Helper method for testing vs production
    public static function getDefaultStatus()
    {
        // When testing without payment gateway
        if (config('app.testing_mode', true)) {
            return self::COMPLETED;
        }
        // When payment gateway is added
        return self::PENDING;
    }
}