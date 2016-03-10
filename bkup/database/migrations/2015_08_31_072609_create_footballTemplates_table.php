<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFootballTemplatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('footballTemplates', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('temp_id');
            $table->string('name')->default('');
            $table->integer('sort')->default(0);
            $table->string('type')->default('');
            $table->string('status')->default('');
            $table->string('grouped')->default('');
            $table->date('update_dat');
            $table->integer('type_id')->nullable();
            $table->integer('api_id')->nullable();
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
        Schema::drop('footballTemplates');
	}

}
