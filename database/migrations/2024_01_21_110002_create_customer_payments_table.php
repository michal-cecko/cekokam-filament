<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->foreignId('customer_id')->nullable()->default(null);
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->enum('status', ['NOT_SUFFICIENT', 'OK', 'REDUNDANT', 'TOO_MUCH'])->default('OK');
            $table->timestamp('customer_deleted_at')->nullable()->default(null);
            $table->double('amount_paid');
            $table->double('amount_expected')->nullable()->default(null);
            $table->text('note')->nullable()->default(null);
            $table->timestamp('received_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
