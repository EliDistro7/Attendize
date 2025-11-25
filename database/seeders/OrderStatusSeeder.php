<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * 
     */
    public function run()
    {
        // Delete existing order statuses
        DB::table('order_statuses')->delete();
        $this->command->info('Existing order statuses deleted.');
        
        $order_statuses = [
            [
                'id' => 1,
                'name' => 'Completed',
            ],
            [
                'id' => 2,
                'name' => 'Pending',
            ],
            [
                'id' => 3,
                'name' => 'Refunded',
            ],
            [
                'id' => 4,
                'name' => 'Partially Refunded',
            ],
            [
                'id' => 5,
                'name' => 'Cancelled',
            ],
        ];

        DB::table('order_statuses')->insert($order_statuses);
        
        $this->command->info('Order statuses seeded successfully!');
    }
}