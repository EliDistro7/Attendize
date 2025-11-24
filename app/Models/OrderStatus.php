<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $fillable = ['name'];
    
    // Status constants
    const COMPLETED = 1;
    const PENDING = 2;
    const REFUNDED = 3;
    const PARTIALLY_REFUNDED = 4;
    const CANCELLED = 5;
    
    /**
     * Get the default status based on testing mode
     * 
     * @return int
     */
    public static function getDefaultStatus()
    {
        // When testing without payment gateway, mark as completed
        if (config('app.testing_mode', true)) {
            return self::COMPLETED;
        }
        // When payment gateway is added, start as pending
        return self::PENDING;
    }
    
    /**
     * Check if payment should be marked as received
     * 
     * @return int
     */
    public static function isPaymentReceived()
    {
        return config('app.testing_mode', true) ? 1 : 0;
    }
}