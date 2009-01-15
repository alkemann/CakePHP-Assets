<?php
class MultilingualProfileFixture extends CakeTestFixture {
    var $name = 'MultilingualProfile';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'description' => array('type' => 'string', 'length' => 255, 'null' => false)
    );
    var $records = array(
		array('id' => 1,  'description'=>'Strongest man alive.'),
		array('id' => 2,  'description'=>'Tall, dark and handsome.'),
    );
}
?>