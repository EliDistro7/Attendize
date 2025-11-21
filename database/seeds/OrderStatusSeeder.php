<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Skip if order statuses already exist
        if (DB::table('order_statuses')->count() > 0) {
            $this->command->info('Order statuses already exist, skipping...');
            return;
        }
        
        $order_statuses = [
            [
                'id' => 1,
                'name' => 'Completed',
            ],
            [
                'id' => 2,
                'name' => 'Refunded',
            ],
            [
                'id' => 3,
                'name' => 'Partially Refunded',
            ],
            [
                'id' => 4,
                'name' => 'Cancelled',
            ],
        ];

        DB::table('order_statuses')->insert($order_statuses);
    }
}