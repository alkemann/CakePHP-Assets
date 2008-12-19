<?php
class RevRevisionCommentFixture extends CakeTestFixture {
	var $name = 'RevRevisionComment';
	var $fields = array(	
			'version_id' => array('type' => 'integer','null' => false,'default' => NULL,'key' => 'primary'), 
			'version_created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
			'id' => array(
					'type' => 'integer', 
					'null' => false, 
					'default' => NULL), 
			'title' => array('type' => 'string', 'null' => false, 'default' => NULL), 
			'content' => array('type' => 'text', 'null' => false, 'default' => NULL));
	var $records = array(	
		array(
			'version_id' => 1,
			'version_created' => '2008-12-08 11:38:53',
			'id' => 2, 
			'title' => 'Stuff', 
			'content' => 'Lorem ipsum dolor sit.', 
		),
	);
}
?>