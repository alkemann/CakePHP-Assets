<?php

class MrlCommentFixture extends CakeTestFixture {
	var $name = 'MrlComment';
	var $fields = array(
			'id' => array('type' => 'integer','null' => false, 'key' => 'primary'), 
			'article_id' => array('type' => 'integer', 'null' => false),
			'user_id' => array('type' => 'integer', 'null' => false),
			'content' => array('type' => 'text', 'null' => false, 'default' => NULL), 
			'indexes' => array('PRIMARY' => array('column' => 'id')));
	var $records = array(
		array(
			'id' => 1, 
			'article_id' => 1,
			'user_id' => 1,
			'content' => 'Comment 1'
		),
		array(
			'id' => 2, 
			'article_id' => 1,
			'user_id' => 2,
			'content' => 'Comment 2'
		),
		array(
			'id' => 3, 
			'article_id' => 2,
			'user_id' => 2,
			'content' => 'Article 2 Comment 1'
		),
		array(
			'id' => 4, 
			'article_id' => 1,
			'user_id' => 2,
			'content' => 'Comment 3'
		),
	);
}
?>