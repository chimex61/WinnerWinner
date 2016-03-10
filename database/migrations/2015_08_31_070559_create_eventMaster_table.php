<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventMasterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('eventmaster', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('event_name')->unique();
            $table->dateTime('start_date')->unique();
            $table->dateTime('bet_till_date')->nullable();
            $table->string('state')->default('');
            $table->string('venue_id')->unique();
            $table->integer('event_id')->nullable();
            $table->text('other')->default('');
            $table->integer('api_id')->nullable();
            $table->integer('date_stamp')->nullable();
            $table->string('g_g_id')->default('');
            $table->integer('game_id')->nullable();
            $table->string('type_id')->default('');
            $table->integer('master_event')->nullable();
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
        Schema::drop('eventmaster');
	}

}
