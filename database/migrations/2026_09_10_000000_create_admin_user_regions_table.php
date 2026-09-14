<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminUserRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_user_regions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // Firestore document ids are 20 characters. The length is capped so
            // the composite unique index stays inside the server key limit.
            $table->string('region_id', 64);
            $table->timestamps();

            $table->unique(['user_id', 'region_id']);
            $table->index('region_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_user_regions');
    }
}
