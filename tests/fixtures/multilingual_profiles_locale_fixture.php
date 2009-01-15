<?php
class MultilingualProfilesLocaleFixture extends CakeTestFixture {
    var $name = 'MultilingualProfilesLocale';
    
    var $fields = array(
        'trans_id' => array('type' => 'integer', 'key' => 'primary'),
        'id' => array('type' => 'integer'),
        'description' => array('type' => 'string', 'length' => 255, 'null' => false),
        'locale' => array('type' => 'string', 'length' => 15, 'null' => false)        
    );
    var $records = array(
		array('trans_id' => 1,  'id' => 1, 'description'=>'Viva el hombre más fuerte.', 'locale' => 'es-es' ),
		array('trans_id' => 2,  'id' => 2, 'description'=>'Alto, guapo y oscuro.',  'locale' => 'es-es' )
	);
}
?>