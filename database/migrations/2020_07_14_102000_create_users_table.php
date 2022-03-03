<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id')->nullable();
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('login');
            $table->string('phone')->unique();
            $table->string('image')->nullable();
            $table->integer('rating')->default(0);
            $table->bigInteger('scores')->default(1000);
            $table->string('access_token')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('workplace', 50)->nullable();
            $table->string('organization', 50)->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
