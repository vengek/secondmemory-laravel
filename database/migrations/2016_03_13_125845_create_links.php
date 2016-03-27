<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->increments('_id');
            $table->integer('id');
            $table->integer('right');
            $table->integer('type_id')->default(0);
            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            $table->unique(['id', 'right', 'type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('links');
    }
}
