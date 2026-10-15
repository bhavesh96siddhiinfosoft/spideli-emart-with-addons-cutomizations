<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spideli Finance - what the daily jobs already did (a reminder sent for a
 * cycle, a target-reached notice for a goal...), so a job that runs again
 * never sends the same notice twice.
 */
class CreateFinanceJobMarksTable extends Migration
{
    public function up()
    {
        Schema::create('finance_job_marks', function (Blueprint $table) {
            $table->string('mark', 191)->primary();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('finance_job_marks');
    }
}
