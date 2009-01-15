<?php
class MrlArticleFixture extends CakeTestFixture {
	var $name = 'MrlArticle';
	var $fields = array(
			'id' => array('type' => 'integer','null' => false, 'key' => 'primary'), 
			'user_id' => array('type' => 'integer', 'null' => false, 'default' => NULL), 
			'title' => array('type' => 'string', 'null' => false, 'default' => NULL), 
			'content' => array('type' => 'text', 'null' => false, 'default' => NULL), 
			'indexes' => array('PRIMARY' => array('column' => 'id')));
	var $records = array(
		array(
			'id' => 1, 
			'user_id' => 2, 
			'title' => 'Nimrod Expedition', 
			'content' => 'The British Antarctic Expedition 1907–09, otherwise known as the Nimrod Expedition,
			 was the first of three expeditions to the Antarctic led by Ernest Shackleton. 
			 It was financed without governmental or institutional support and relied on private
			  loans and individual contributions. Its ship, Nimrod, was a 40-year-old small 
			  wooden sealer of 334 gross register tons, and the expedition\'s members generally 
			  lacked relevant experience.'
		),
		array(
			'id' => 2, 
			'user_id' => 2, 
			'title' => 'Lorem ipsum', 
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.'
		),
		array(
			'id' => 3, 
			'user_id' => 1, 
			'title' => 'Lorem ipsum', 
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.'
		),
		array(
			'id' => 4, 
			'user_id' => 2, 
			'title' => 'To be deleted', 
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.'
		),
		array(
			'id' => 5, 
			'user_id' => 2, 
			'title' => 'To get tags', 
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.'
		),
	);
}
?>