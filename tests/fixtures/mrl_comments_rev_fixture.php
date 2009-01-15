<?php
class MrlCommentsRevFixture extends CakeTestFixture {
	var $name = 'MrlCommentsRev';
	var $fields = array(	
			'version_id' => array('type' => 'integer','null' => false,'default' => NULL,'key' => 'primary'), 
			'version_created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
			'id' => array('type' => 'integer', 'null' => false),  
			'article_id' => array('type' => 'integer', 'null' => false),  
			'user_id' => array('type' => 'integer', 'null' => false),  
			'locale' => array('type' => 'string', 'null' => false, 'default' => 'en-us'),
			'content' => array('type' => 'text', 'null' => false, 'default' => NULL)
	);
	var $records = array(	
		array(
			'version_id' => 1,
			'version_created' => '2008-02-02 11:38:53',
			'id' => 1, 
			'article_id' => 1, 
			'user_id' => 1, 
			'locale' => 'en-us',
			'content' => 'Comment 1'
		),
		array(
			'version_id' => 2,
			'version_created' => '2008-03-03 11:38:53',
			'id' => 2, 
			'article_id' => 1, 
			'user_id' => 2, 
			'locale' => 'en-us',
			'content' => 'Comment 2'
		),
		array(
			'version_id' => 3,
			'version_created' => '2008-04-04 11:38:53',
			'id' => 3, 
			'article_id' => 2, 
			'user_id' => 2, 
			'locale' => 'en-us',
			'content' => 'Article 2 Comment 1'
		),
		array(
			'version_id' => 4,
			'version_created' => '2008-05-05 11:55:53',
			'id' => 4, 
			'article_id' => 1, 
			'user_id' => 2, 
			'locale' => 'en-us',
			'content' => 'Comment 3'
		),
		array(
			'version_id' => 5,
			'version_created' => '2008-04-05 11:38:53',
			'id' => 3, 
			'article_id' => 2, 
			'user_id' => 2, 
			'locale' => 'it-it',
			'content'=>'italian'
		),
		array(
			'version_id' => 6,
			'version_created' => '2008-04-06 11:38:53',
			'id' => 3, 
			'article_id' => 2, 
			'user_id' => 1, 
			'locale' => 'es-es' ,
			'content'=>'spanish'
		),
		array(
			'version_id' => 7,
			'version_created' => '2008-02-03 11:38:53',
			'id' => 1, 
			'article_id' => 1, 
			'user_id' => 1, 
			'locale' => 'es-es',
			'content'=>'spanish comment 1'
		),
		array(
			'version_id' => 8,
			'version_created' => '2008-03-04 11:38:53',
			'id' => 2, 
			'article_id' => 1, 
			'user_id' => 2, 
			'locale' => 'es-es',
			'content'=>'spanish comment 2'
		)
	);
}
?>