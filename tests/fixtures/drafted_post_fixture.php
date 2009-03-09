<?php
class DraftedPostFixture extends CakeTestFixture {
    var $name = 'DraftedPost';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'title' => array('type' => 'string', 'length' => 255, 'null' => true),
        'body' => array('type' => 'string', 'length' => 255, 'null' => true),
    	'published' => array('type' => 'integer', 'length' => 1, 'default' => 0)
    );
    var $records = array(
		array('id' => 1, 'title' => 'Rock and Roll', 'body' => 'I love rock and roll!', 'published' => 1),
		array('id' => 2, 'title' => 'Music', 'body' => 'Rock and roll is cool', 'published' => 1),
		array('id' => 3, 'title' => 'Food', 'body' => 'Apples are good', 'published' => 1 ),
    );
}
?>