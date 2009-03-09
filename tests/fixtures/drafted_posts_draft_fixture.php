<?php
class DraftedPostsDraftFixture extends CakeTestFixture {
    var $name = 'DraftedPostsDraft';
    
    var $fields = array(
        'draft_id' => array('type' => 'integer', 'key' => 'primary'),
        'id' => array('type' => 'integer', 'null' => false),
        'title' => array('type' => 'string', 'length' => 255, 'null' => false),
        'body' => array('type' => 'string', 'length' => 1255, 'null' => false)
    );
    var $records = array(
		array('draft_id' => 1, 'id' => 2, 'title' => 'Musical', 'body' => 'Rock and roll is awesome!'),
    );
}
?>