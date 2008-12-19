<?php
class RevisionPostFixture extends CakeTestFixture {
	var $name = 'RevisionPost';
	var $fields = array(
			'id' => array(
					'type' => 'integer', 
					'null' => false, 
					'default' => NULL, 
					'key' => 'primary'), 
			'title' => array('type' => 'string', 'null' => false, 'default' => NULL), 
			'content' => array('type' => 'text', 'null' => false, 'default' => NULL), 
			'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL), 
			'created' => array('type' => 'date', 'null' => false, 'default' => NULL), 
			'indexes' => array('PRIMARY' => array('column' => 'id')));
	var $records = array(
		array(
			'id' => 1, 
			'title' => 'Lorem ipsum dolor sit amet', 
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
			'modified' => '2008-12-08 11:38:53', 
			'created' => '2008-12-08'
		),
		array(
			'id' => 2, 
			'title' => 'Stuff', 
			'content' => 'Lorem ipsum dolor sit.', 
			'modified' => '2008-12-09 13:48:01', 
			'created' => '2008-12-09'
		),
		array(
			'id' => 3, 
			'title' => 'Stuff', 
			'content' => 'Lorem ipsum dolor sit.', 
			'modified' => '2008-12-09 13:48:01', 
			'created' => '2008-12-09'
		),
	);
}
?>