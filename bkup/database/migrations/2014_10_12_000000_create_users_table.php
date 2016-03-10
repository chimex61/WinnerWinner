<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('email')->unique();
            $table->string('username')->unique();
			$table->string('password', 60);
            $table->string('token', 32)->default('');
            $table->string('settings')->default('');
            $table->string('phone',15)->nullable();
            $table->string('photo')->nullable();
            $table->tinyInteger('role')->default(3);
            $table->tinyInteger('activated')->default(0);
			$table->rememberToken();
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
		Schema::drop('users');
	}

}
