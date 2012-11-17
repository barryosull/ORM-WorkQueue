<?php

namespace Fuel\Migrations;

class Create_workqueues
{
	public function up()
	{
		\DBUtil::create_table('workqueues', array(
			'id' => array('constraint' => 11, 'type' => 'int', 'auto_increment' => true),
			'retry_count' => array('constraint' => 11, 'type' => 'int'),
			'status' => array('constraint' => 32, 'type' => 'varchar'),
			'model_name' => array('constraint' => 128, 'type' => 'varchar'),
			'model_id' => array('constraint' => 11, 'type' => 'int'),
			'function_to_run' => array('constraint' => 128, 'type' => 'varchar'),
			'next_visible_at' => array('constraint' => 11, 'type' => 'int'),
			'created_at' => array('constraint' => 11, 'type' => 'int'),
			'updated_at' => array('constraint' => 11, 'type' => 'int'),
		), array('id'));
	}

	public function down()
	{
		\DBUtil::drop_table('workqueues');
	}
}