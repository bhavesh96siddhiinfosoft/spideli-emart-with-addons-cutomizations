<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spideli Finance - the panel's own records (spec 5.3 and 8: four-eyes
 * approvals and the panel's settings live in the panel database, never in a
 * customer-readable Firestore document).
 */
class CreateFinancePanelTables extends Migration
{
    public function up()
    {
        // A sensitive money action above the threshold waits here for a second admin.
        Schema::create('finance_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('action', 40);                 // reversal | manual_adjustment
            $table->string('region_id', 64)->nullable();  // Firestore regions/{id}; null = decided by currency
            $table->json('payload');                      // what to execute
            $table->string('currency', 3);
            $table->unsignedBigInteger('amount');         // minor units
            $table->text('reason');
            $table->string('status', 20)->default('pending'); // pending | executing | executed | rejected | failed
            $table->unsignedBigInteger('requested_by');
            $table->string('requested_by_email')->default('');
            $table->unsignedBigInteger('decided_by')->nullable();
            $table->string('decided_by_email')->default('');
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->json('result')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('region_id');
        });

        // Per-region panel settings, e.g. the second-approver threshold.
        Schema::create('finance_panel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('region_id', 64);
            $table->string('key', 64);
            $table->json('value');
            $table->timestamps();

            $table->unique(['region_id', 'key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('finance_approvals');
        Schema::dropIfExists('finance_panel_settings');
    }
}
