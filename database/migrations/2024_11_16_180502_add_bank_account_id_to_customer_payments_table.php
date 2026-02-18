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
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->string('iban')->nullable()->default(null);
            $table->foreign('iban')->references('iban')->on('bank_accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            if (! app()->runningUnitTests()) {
                $table->dropForeign('customer_payments_iban_fk');
            }
            $table->dropColumn('iban');
        });
    }
};
