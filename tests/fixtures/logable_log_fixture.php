<?php
class LogableLogFixture extends CakeTestFixture {
    var $name = 'LogableLog';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'title' => array('type' => 'string', 'length' => 255, 'null' => false),
    	'description' => array('type' =>  'string', 'length' => 255, 'null' => false),
    	'model' => array('type' =>  'string', 'length' => 255, 'null' => false),
        'model_id' => array('type' => 'integer', 'null' => false),       
    	'action' => array('type' =>  'string', 'length' => 25, 'null' => false),    
        'user_id' => array('type' => 'integer', 'null' => false),       
    	'change' => array('type' =>  'string', 'length' => 255, 'null' => false),
    );
    var $records = array(
		array(
				'id' => 1,
	    		'title' => 'Fifth Book',
	    		'description' =>  'LogableBook "Fifth Book" (6) created by LogableUser "Alexander" (66).',
	    		'model' => 'LogableBook',
	    		'model_id' => 6,
	    		'action' => 'add',
	    		'user_id' => 66,
	    		'change' => 'title' 
	    ), 	 
    	array(
				'id' => 2,
	    		'title' => 'Fifth Book',
	    		'description' =>  'LogableBook "Fifth Book" (6) updated by LogableUser "Alexander" (66).',
	    		'model' => 'LogableBook',
	    		'model_id' => 6,
	    		'action' => 'edit',
	    		'user_id' => 66,
	    		'change' => 'title' 	
	    ),  
    	array(
				'id' => 3,
	    		'title' => 'Steven',
	    		'description' =>  'User "Steven" (301) updated by LogableUser "Steven" (301).',
	    		'model' => 'LogableUser',
	    		'model_id' => 301,
	    		'action' => 'edit',
	    		'user_id' => 301,
	    		'change' => 'name' 
	    ),    
    	array(
				'id' => 4,
	    		'title' => 'Fifth Book',
	    		'description' =>  'LogableBook "Fifth Book" (6) deleted by LogableUser "Alexander" (66).',
	    		'model' => 'LogableBook',
	    		'model_id' => 6,
	    		'action' => 'delete',
	    		'user_id' => 66,
	    		'change' => '' 	
	    ),   
		array(
				'id' => 5,
	    		'title' => 'New Book',
	    		'description' =>  'LogableBook "New Book" (7) added by LogableUser "Steven" (301).',
	    		'model' => 'LogableBook',
	    		'model_id' => 7,
	    		'action' => 'add',
	    		'user_id' => 301,
	    		'change' => 'title' 	
	    ),		
    );
}
?>