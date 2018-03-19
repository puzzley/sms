<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerificationsTable extends Migration 
{
    
    private function getTable()
	{
		return Config::get('sms.table.verifications', 'puzzley_sms_verifications');
	}

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(
			$this->getTable(),
			function(Blueprint $table)
			{
				$table->increments('id');
				$table->string('code');
                $table->nullableTimestamps();
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
		Schema::drop($this->getTable());
	}

}
