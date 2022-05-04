<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('features', function (Blueprint $table) {
            $table->boolean('conditioner')->nullable();
            $table->boolean('heating')->nullable();
            $table->boolean('electricity')->nullable();
            $table->boolean('gas')->nullable();
            $table->boolean('water')->nullable();
            $table->boolean('tv_cable')->nullable();
            $table->boolean('fixed_telephone_cable')->nullable();
            $table->boolean('fiber_internet_cable')->nullable();
            $table->boolean('refrigerator')->nullable();
            $table->boolean('washer')->nullable();
            $table->boolean('water_tank')->nullable();
            $table->boolean('pool')->nullable();
            $table->boolean('garden')->nullable();
            $table->boolean('elevator')->nullable();
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
        Schema::dropIfExists('features');
    }
}
