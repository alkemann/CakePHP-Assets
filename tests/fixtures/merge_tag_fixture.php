<?php
class MergeTagFixture extends CakeTestFixture {
    var $name = 'MergeTag';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'title' => array('type' => 'string', 'length' => 255, 'null' => false)
    );
    var $records = array(
		array('id' => 1, 'title' => 'Fun'),
		array('id' => 2, 'title' => 'Lame' ),
		array('id' => 3, 'title' => 'Blue'),
    );
}
?>