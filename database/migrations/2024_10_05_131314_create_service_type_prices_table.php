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
        Schema::create('service_type_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id');
            $table->foreign('service_type_id')->references('id')->on('service_types')->cascadeOnDelete();
            $table->smallInteger('service_count_id')->nullable()->default(null);
            $table->foreign('service_count_id')->references('count_value')->on('service_type_counts')->nullOnDelete();
            $table->decimal('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_type_prices');
    }
};
