<?php
class OrderedMarkFixture extends CakeTestFixture {
    var $name = 'OrderedMark';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'title' => array('type' => 'string', 'length' => 255, 'null' => false),
        'nose' => array('type' => 'integer', 'null' => false),
        'order_id' => array('type' => 'integer', 'null' => false),
    	'deleted' => array('type' => 'boolean')
    );
    var $records = array(
		array('id' => 1, 'title' => 'First Mark', 'order_id' => 1, 'nose' => 1, 'deleted' => false ),
		array('id' => 2, 'title' => 'Second Mark','order_id' => 1, 'nose' => 2, 'deleted' => false ),
		array('id' => 3, 'title' => 'Third Mark', 'order_id' => 1, 'nose' => 3, 'deleted' => false ),
		array('id' => 4, 'title' => 'Fourth Mark','order_id' => 1, 'nose' => 4, 'deleted' => false ),
		array('id' => 5, 'title' => 'Front Mark', 'order_id' => 2, 'nose' => 1, 'deleted' => false ),
		array('id' => 6, 'title' => 'Intro Mark', 'order_id' => 2, 'nose' => 2, 'deleted' => false ),
    );
}
?>