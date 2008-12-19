<?php
class MergePostFixture extends CakeTestFixture {
    var $name = 'MergePost';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'title' => array('type' => 'string', 'length' => 255, 'null' => false),
        'body' => array('type' => 'string', 'length' => 1255, 'null' => false),        
        'topic_id' => array('type' => 'integer', 'null' => true)        
    );
    var $records = array(
		array('id' => 1, 'title' => 'Rock and Roll', 'body' => 'I love rock and roll!',  'topic_id' => 1 ),
		array('id' => 2, 'title' => 'Music', 'body' => 'Rock and roll is cool',  'topic_id' => 1 ),
		array('id' => 3, 'title' => 'Food', 'body' => 'Apples are good',  'topic_id' => 3 ),
    );
}
?>