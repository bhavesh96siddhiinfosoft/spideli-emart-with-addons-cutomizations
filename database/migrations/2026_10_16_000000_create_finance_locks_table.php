<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spideli Finance - while an admin works on a queue item (a withdrawal, a
 * transfer, a bill, a receipt, a KYC application) it is locked to them, in
 * the panel's own database (spec 8), so two admins never pay the same item.
 */
class CreateFinanceLocksTable extends Migration
{
    public function up()
    {
        Schema::create('finance_locks', function (Blueprint $table) {
            $table->string('resource', 191)->primary();   // e.g. wallet_operations/{id}
            $table->unsignedBigInteger('locked_by');
            $table->string('locked_by_email')->default('');
            $table->timestamp('locked_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('finance_locks');
    }
}
