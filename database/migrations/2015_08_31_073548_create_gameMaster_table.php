<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameMasterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('gamemaster', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('game_name')->default('');
            $table->string('title')->default('');
            $table->string('link')->default('');
            $table->tinyInteger('active')->default(0);

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
        Schema::drop('gamemaster');
	}

}
