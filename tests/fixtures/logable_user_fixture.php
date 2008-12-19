<?php
class LogableUserFixture extends CakeTestFixture {
    var $name = 'LogableUser';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'name' => array('type' => 'string', 'length' => 255, 'null' => false),
    );
    var $records = array(
		array('id' => 66, 'name' => 'Alexander'),
		array('id' => 301, 'name' => 'Steven'),
    );
}
?>