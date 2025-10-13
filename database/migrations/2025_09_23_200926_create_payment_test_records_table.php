<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTestRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_test_records', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('reference_id')->nullable();
            $table->unsignedBigInteger('payment_gateway_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            
            // Payment details
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('TZS');
            $table->string('payment_method');
            $table->string('phone_number')->nullable();
            $table->string('account_reference')->nullable();
            
            // Status tracking
            $table->enum('status', [
                'pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'timeout'
            ])->default('pending');
            
            $table->text('provider_response')->nullable();
            $table->json('metadata')->nullable();
            
            // Test-specific fields
            $table->boolean('is_test')->default(true);
            $table->string('test_scenario')->nullable();
            $table->text('test_notes')->nullable();
            
            // Tanzania-specific fields
            $table->string('network_provider')->nullable();
            $table->string('operator_reference')->nullable();
            $table->decimal('network_fee', 8, 2)->nullable();
            
            // Timing fields
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->integer('processing_duration_seconds')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'is_test']);
            $table->index(['payment_method', 'status']);
            $table->index(['network_provider', 'status']);
            $table->index('transaction_id');
            $table->index('phone_number');
            $table->index(['created_at', 'payment_method']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_test_records');
    }
}