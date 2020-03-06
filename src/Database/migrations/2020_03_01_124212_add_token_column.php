<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTokenColumn extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table(
			$this->getTable(),
			function(Blueprint $table) {
				if (!Schema::hasColumn($this->getTable(), 'token')) {
					$table->string('token', 255)->after('code');
				}
			}
		);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table(
			$this->getTable(),
			function(Blueprint $table) {
				if (Schema::hasColumn($this->getTable(), 'token')) {
					$table->dropColumn('token');
				}
			}
		);
	}

	/**
	 * @return string
	 */
	private function getTable()
	{
		return Config::get('sms.table.verifications', 'puzzley_sms_verifications');
	}
}
