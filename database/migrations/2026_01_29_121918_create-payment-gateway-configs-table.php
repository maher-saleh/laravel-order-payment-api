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
        Schema::create('payment_gateway_configs', function(Blueprint $table){
            $table->id();
            $table->string('gateway_name')
                  ->unique();
            $table->text('config'); // Encrypted configuration
            $table->boolean('is_active')
                  ->default(true);
            $table->timestamps();

            // Indexes
            $table->index('is_active');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_configs');
    }
};