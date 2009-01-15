<?php
class MrlArticlesMrlTagFixture extends CakeTestFixture {
	var $name = 'MrlArticlesMrlTag';
	var $fields = array(
			'mrl_article_id' => array('type' => 'integer', 'null' => false),  
			'mrl_tag_id' => array('type' => 'integer', 'null' => false));
	
	var $records = array(
		array( 
			'mrl_article_id' => 1, 
			'mrl_tag_id' => 1
		),
		array(
			'mrl_article_id' => 1, 
			'mrl_tag_id' => 2
		),
		array(
			'mrl_article_id' => 1, 
			'mrl_tag_id' => 3
		),
		array(
			'mrl_article_id' => 2, 
			'mrl_tag_id' => 1
		),
		array(
			'mrl_article_id' => 2, 
			'mrl_tag_id' => 3
		),
		array(
			'mrl_article_id' => 3, 
			'mrl_tag_id' => 2
		),
		array(
			'mrl_article_id' => 3, 
			'mrl_tag_id' => 4
		),
	);
}
?>