<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutcomeMasterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('outcomeMaster', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('label')->default('');
            $table->string('bet_type')->default('');
            $table->double('odd',8,4)->default(0);
            $table->string('odd_fractional')->default('');
            $table->integer('event_id')->default(0);
            $table->string('add_date')->default('');
            $table->text('other')->default('');
            $table->integer('game_id')->default(0);
            $table->string('g_g_id')->default('');
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
        Schema::drop('outcomeMaster');
	}

}
