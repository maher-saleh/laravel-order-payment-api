<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function(Blueprint $table){
            $table->id();
            $table->foreignId('order_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('payment_method');
            $table->enum('status', ['pending', 'successful', 'failed'])
                  ->default('pending')
                  ->index();
            $table->decimal('amount', 10, 2);
            $table->json('gateway_response')
                  ->nullable();
            $table->string('transaction_id')
                  ->nullable()
                  ->unique();
            $table->timestamps();

            // Indexes
            $table->index('order_id');
            $table->index(['status', 'created_at']);
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};