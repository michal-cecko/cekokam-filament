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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->default(null);
            $table->jsonb('phone')->default('[]');
            $table->index('phone', 'customers_phones_idx', 'gin');
            $table->integer('year_added');
            $table->foreignId('server_id')->nullable()->default(null);
            $table->foreign('server_id')->references('id')->on('servers')->nullOnDelete();
            $table->jsonb('ip_addresses')->default('[]');
            $table->index('ip_addresses', 'customers_ips_idx', 'gin');
            $table->boolean('has_different_prices')->default(false);
            $table->enum('status', ['FREE', 'TURNED_OFF', 'PAID', 'UNPAID'])->default('UNPAID');
            $table->foreignId('city_id')->nullable()->default(null);
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
            $table->string('iban')->nullable()->default(null);
            $table->foreign('iban')->references('iban')->on('bank_accounts')->nullOnDelete();
            $table->text('note')->nullable()->default(null);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
