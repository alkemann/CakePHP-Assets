<?php
class OrderedPageFixture extends CakeTestFixture {
    var $name = 'OrderedPage';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'title' => array('type' => 'string', 'length' => 255, 'null' => false),
        'weight' => array('type' => 'integer', 'null' => false),
        'book_id' => array('type' => 'integer', 'null' => false)
    );
    var $records = array(
		array('id' => 2, 'title' => 'First Page', 'book_id' => 1, 'weight' => 1 ),
		array('id' => 1, 'title' => 'Second Page','book_id' => 1, 'weight' => 2 ),
		array('id' => 4, 'title' => 'Third Page', 'book_id' => 1, 'weight' => 3 ),
		array('id' => 5, 'title' => 'Fourth Page','book_id' => 1, 'weight' => 4 ),
		array('id' => 3, 'title' => 'Front Page', 'book_id' => 2, 'weight' => 1 ),
		array('id' => 6, 'title' => 'Intro Page', 'book_id' => 2, 'weight' => 2 ),
    );
}
?>