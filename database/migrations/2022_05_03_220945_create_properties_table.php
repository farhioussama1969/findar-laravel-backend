<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->integer('number_of_rooms')->nullable();
            $table->integer('floor_number')->nullable();
            $table->integer('number_of_floor')->nullable();
            $table->integer('number_of_bathrooms')->nullable();
            $table->integer('total_area')->nullable();
            $table->integer('built_area')->nullable();
            $table->integer('number_of_kitchen')->nullable();
            $table->integer('number_of_garages')->nullable();
            $table->integer('number_of_balcony')->nullable();
            $table->boolean('is_furnished')->nullable();
            $table->foreignId('advertisement_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('properties');
    }
}
