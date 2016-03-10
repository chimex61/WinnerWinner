<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutcomeParticipantMasterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('outcomeParticipantMaster', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('label')->unique();
            $table->string('real_id')->default('');
            $table->integer('api_id')->default(0);
            $table->integer('flag')->default(0);

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
        Schema::drop('outcomeParticipantMaster');
	}

}
