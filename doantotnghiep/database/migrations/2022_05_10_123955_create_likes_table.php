<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('like')->default(0);
            $table->integer('user_id')->unsigned();
            $table->integer('dangtin_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('dangtin_id')->references('id')->on('dangtins');
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
        Schema::dropIfExists('likes');
    }
};
