<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWishesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wishes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('wishlistid');
            $table->string('title', 255);
            $table->string('details', 255);
            $table->string('wishimageurl');
            $table->string('alternatives');
            $table->timestamp('due_date');
            $table->timestamp('date_created');
            $table->tinyInteger('granted');
            $table->bigInteger('granterid');
            $table->string('granteddetails', 500);
            $table->string('grantedimageurl');
            $table->timestamp('date_granted');
            $table->tinyInteger('flagged');
            $table->tinyInteger('status');
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
        Schema::drop('wishes');
    }
}
