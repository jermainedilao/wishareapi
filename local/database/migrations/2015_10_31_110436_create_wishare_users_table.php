<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWishareUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wishare_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('imageurl');
            $table->string('firstname', 255);
            $table->string('lastname', 255);
            $table->string('username', 255)->unique();
            $table->string('city', 255);
            $table->date('birthdate');
            $table->string('facebook', 255);
            $table->tinyInteger('privacy');
            $table->tinyInteger('type');
            $table->tinyInteger('status');
            $table->string('forgot_password_token');
            $table->string('remember_token')->nullable();
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
        Schema::drop('wishare_users');
    }
}
