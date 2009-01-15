<?php
class MultilingualBookFixture extends CakeTestFixture {
    var $name = 'MultilingualBook';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'user_id' => array('type' => 'integer'),
        'title' => array('type' => 'string', 'length' => 255, 'null' => false),
        'description' => array('type' => 'string', 'length' => 255, 'null' => false),
        'kid' => array('type' => 'integer', 'null' => false)        
    );
    var $records = array(
		array('id' => 1, 'user_id'=>1, 'title' => 'Sixth Book', 'description'=>'Contents of sixth book', 'kid' => 11 ),
		array('id' => 2, 'user_id'=>1, 'title' => 'Fifth Book', 'description'=>'Contents of fifth book', 'kid' => 22 ),
		array('id' => 3, 'user_id'=>2, 'title' => 'First Book', 'description'=>'Contents of first book', 'kid' => 33 ),
		array('id' => 4, 'user_id'=>1, 'title' => 'Second Book','description'=>'Contents of second book','kid' => 44 ),
		array('id' => 5, 'user_id'=>2, 'title' => 'Third Book', 'description'=>'Contents of third book','kid' => 55 ),
		array('id' => 6, 'user_id'=>1, 'title' => 'Fourth Book','description'=>'Contents of fourth book','kid' => 66 )
    );
}
?>