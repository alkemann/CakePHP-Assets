<?php
class MergePostsMergeTagFixture extends CakeTestFixture {
    var $name = 'MergePostsMergeTag';
    
    var $fields = array(
        'post_id' => array('type' => 'integer', 'null' => false),
        'tag_id' => array('type' => 'integer', 'null' => false)
    );
    var $records = array(
		array('post_id' => 1, 'tag_id' => 1),
		array('post_id' => 1, 'tag_id' => 2),
		
		array('post_id' => 2, 'tag_id' => 1),
		
		array('post_id' => 3, 'tag_id' => 2),
		array('post_id' => 3, 'tag_id' => 3),
    );
}
?>