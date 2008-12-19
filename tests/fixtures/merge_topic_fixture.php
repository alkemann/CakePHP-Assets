<?php
class MergeTopicFixture extends CakeTestFixture {
    var $name = 'MergeTopic';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'title' => array('type' => 'string', 'length' => 255, 'null' => false)
    );
    var $records = array(
		array('id' => 1, 'title' => 'Personal'),
		array('id' => 2, 'title' => 'Proffesional' ),
		array('id' => 3, 'title' => 'Work'),
    );
}
?>