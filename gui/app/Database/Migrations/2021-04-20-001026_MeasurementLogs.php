<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MeasurementLogs extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'			=> ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
			'instrument_id'	=> ['type' => 'INT', 'default' => '0'],
			'parameter_id'	=> ['type' => 'INT', 'default' => '0'],
			'value'			=> ['type' => 'DOUBLE', 'default' => '0'],
			'unit_id'		=> ['type' => 'INT', 'default' => '0'],
			'is_averaged'	=> ['type' => 'SMALLINT', 'default' => '0'],
			'xtimestamp timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()'
		]);
		$this->forge->addKey('id', TRUE);
		$this->forge->addKey('instrument_id');
		$this->forge->addKey('parameter_id');
		$this->forge->addKey('is_averaged');
		$this->forge->createTable('measurement_logs', TRUE);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('measurement_logs');
	}
}
