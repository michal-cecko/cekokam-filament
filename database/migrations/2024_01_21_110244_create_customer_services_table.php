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
        Schema::create('customer_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->smallInteger('service_count_id')->nullable()->default(null);
            $table->foreign('service_count_id')->references('count_value')->on('service_type_counts')->nullOnDelete();
            $table->foreignId('service_type_id')->nullable()->default(null);
            $table->foreign('service_type_id')->references('id')->on('service_types')->nullOnDelete();
            $table->foreignId('archive_account_id')->nullable()->default(null);
            $table->foreign('archive_account_id')->references('id')->on('account_subscriptions')->nullOnDelete();
            $table->double('price')->nullable()->default(null);
            $table->date('subscription_start');
            $table->date('subscription_end')->nullable()->default(null);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_services');
    }
};
