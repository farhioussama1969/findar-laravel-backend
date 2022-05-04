<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisementLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisement_location', function (Blueprint $table) {
            $table->string('latitude');
            $table->string('longitude');
            $table->string('address')->nullable();
            $table->foreignId('advertisement_id')->constrained()->onDelete('cascade');
            $table->foreignId('province_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertisement_location');
    }
}
