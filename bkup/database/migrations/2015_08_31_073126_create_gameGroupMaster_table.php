<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameGroupMasterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('gameGroupMaster', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('game_id')->default(0);
            $table->string('g_g_name')->default('');
            $table->string('g_g_id')->default('');
            $table->integer('active_flag')->default(0);
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
        Schema::drop('gameGroupMaster');
	}

}
