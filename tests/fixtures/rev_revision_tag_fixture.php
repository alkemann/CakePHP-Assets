<?php

class RevRevisionTagFixture extends CakeTestFixture {
	var $name = 'RevRevisionTag';
	var $fields = array(	
			'version_id' => array('type' => 'integer','null' => false,'default' => NULL,'key' => 'primary'), 
			'version_created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
			'id' => array(
					'type' => 'integer', 
					'null' => false, 
					'default' => NULL), 
			'title' => array('type' => 'string', 'null' => false, 'default' => NULL), 
			'content' => array('type' => 'text', 'null' => false, 'default' => NULL), 
			'revision_comment_id' => array('type'=>'integer','null'=>false));
	
	var $records = array(
	);
}

?>