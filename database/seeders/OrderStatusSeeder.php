<?php





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
        // Skip if order statuses already exist
        if (DB::table('order_statuses')->count() > 0) {
            $this->command->info('Order statuses already exist, skipping...');
            return;
        }
        
        $order_statuses = [
            [
                'id' => 1,
                'name' => 'Completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Refunded',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Partially Refunded',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Cancelled',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('order_statuses')->insert($order_statuses);
        
        $this->command->info('Order statuses seeded successfully!');
    }
}