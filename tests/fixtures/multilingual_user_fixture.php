<?php
class MultilingualUserFixture extends CakeTestFixture {
    var $name = 'MultilingualUser';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'name' => array('type' => 'string', 'length' => 255, 'null' => false),
        'profile_id' => array('type' => 'integer', 'null' => false)        
    );
    var $records = array(
		array('id' => 1,  'name'=>'Superman', 'profile_id' => 1 ),
		array('id' => 2,  'name'=>'Batman', 'profile_id' => 2 )
    );
}
?>