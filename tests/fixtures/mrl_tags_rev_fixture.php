<?php

class MrlTagsRevFixture extends CakeTestFixture {
	var $name = 'MrlTagsRev';
	var $fields = array(	
			'version_id' => array('type' => 'integer','null' => false,'default' => NULL,'key' => 'primary'), 
			'version_created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
			'id' => array('type' => 'integer', 'null' => false,'default' => NULL), 
			'title' => array('type' => 'string', 'null' => false, 'default' => NULL),
			'locale' => array('type' => 'string', 'null' => false, 'default' => 'en-us')
	);
	
	var $records = array(
		array(
			'version_id' => 1, 
			'version_created' => '2008-01-01', 
			'id' => 1, 
			'title' => 'Fun', 
			'locale' => 'en-us'
		),
		array(
			'version_id' => 2, 
			'version_created' => '2008-01-02',
			'id' => 2, 
			'title' => 'Hard', 
			'locale' => 'en-us'
		),
		array(
			'version_id' => 3, 
			'version_created' => '2008-01-03',
			'id' => 3, 
			'title' => 'Trick', 
			'locale' => 'en-us'
		),
		array(
			'version_id' => 4, 
			'version_created' => '2008-01-04',
			'id' => 4, 
			'title' => 'News', 
			'locale' => 'en-us'
		),		
		array(
			'version_id' => 5,
			'version_created' => '2008-02-02',
			'id' => 1, 
			'title' => 'Morro', 
			'locale' => 'no-nb'
		),
		array(
			'version_id' => 6,
			'version_created' => '2008-02-02',
			'id' => 2, 
			'title' => 'Vanskelig', 
			'locale' => 'no-nb'
		),
		array(
			'version_id' => 7,
			'version_created' => '2008-02-02',
			'id' => 3, 
			'title' => 'Triks', 
			'locale' => 'no-nb'
		),
		array(
			'version_id' => 8,
			'version_created' => '2008-02-02',
			'id' => 4, 
			'title' => 'Nyheter', 
			'locale' => 'es-es'
		),
		array(
			'version_id' => 9,
			'version_created' => '2008-02-02',
			'id' => 1, 
			'title' => 'Diversión', 
			'locale' => 'es-es'
		),
		array(
			'version_id' =>10,
			'version_created' => '2008-02-02',
			'id' => 2, 
			'title' => 'Duro', 
			'locale' => 'es-es'
		),
		array(
			'version_id' =>11,
			'version_created' => '2008-02-02',
			'id' => 3, 
			'title' => 'Truco', 
			'locale' => 'es-es'
		),
		array(
			'version_id' =>12,
			'version_created' => '2008-02-02',
			'id' => 4, 
			'title' => 'Noticias', 
			'locale' => 'es-es'
		),
	);
}

?>