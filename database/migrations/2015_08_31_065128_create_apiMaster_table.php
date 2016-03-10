<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiMasterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('apimaster', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('base_url')->default('');
            $table->string('auth')->default('');
            $table->string('name')->default('');
            $table->string('icon')->default('');
            $table->string('logo')->default('');
            $table->string('no_bets')->default('');
            $table->string('free_bet')->default('');
            $table->string('sign_up')->default('');
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
        Schema::drop('apimaster');
	}

}
