<?php 
class MrlUserFixture extends CakeTestFixture {
	var $name = 'MrlUser';
	var $fields = array(
			'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
			'name' => array('type'=>'string', 'null' => false, 'default' => NULL),
			'username' => array('type'=>'string', 'null' => false, 'default' => NULL),
			'indexes' => array('PRIMARY' => array('column' => 'id'))
			);
	var $records = array(
    array(
			'id'  => 1,
			'name'  => 'Johnny Cash',
			'username'  => 'jc'
		),
		array(
			'id'  => 2,
			'name'  => 'Alexander',
			'username'  => 'alke'
		)
	);
}
?>