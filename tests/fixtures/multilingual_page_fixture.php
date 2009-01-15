<?php
class MultilingualPageFixture extends CakeTestFixture {
    var $name = 'MultilingualPage';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'content' => array('type' => 'string', 'length' => 255, 'null' => false),
        'book_id' => array('type' => 'integer', 'null' => false)        
    );
    var $records = array(
		array('id' => 1,  'content'=>'Page 1', 'book_id' => 1 ),
		array('id' => 2,  'content'=>'Page 2', 'book_id' => 1 ),
		array('id' => 3,  'content'=>'Page 3', 'book_id' => 1 ),
		array('id' => 4,  'content'=>'Page 4', 'book_id' => 1 ),
		array('id' => 5,  'content'=>'Page 5', 'book_id' => 1 ),
		array('id' => 6,  'content'=>'Page 1', 'book_id' => 2 ),
		array('id' => 7,  'content'=>'Page 2', 'book_id' => 2 ),
    );
}
?>