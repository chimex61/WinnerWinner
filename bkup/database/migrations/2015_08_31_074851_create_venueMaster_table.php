<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVenueMasterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('venueMaster', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('venue_name');
            $table->string('venue_id')->default('');
            $table->string('master_venue')->default('');
            $table->string('g_g_id')->default('');
            $table->integer('game_id')->default(0);
            $table->integer('date_stamp')->default(0);
            $table->integer('api_id')->default(0);

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
        Schema::drop('venueMaster');
	}

}
