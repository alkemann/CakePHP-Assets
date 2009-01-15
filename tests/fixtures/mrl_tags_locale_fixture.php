<?php
class MrlTagsLocaleFixture extends CakeTestFixture {
    var $name = 'MrlTagsLocale';
  

    var $fields = array(
        'trans_id' => array('type' => 'integer', 'key' => 'primary'),
        'id' => array('type' => 'integer'),
        'title' => array('type' => 'string', 'length' => 255, 'null' => false),
        'locale' => array('type' => 'string', 'length' => 5, 'null' => false),
    );
    var $records = array(
		array(
			'trans_id' => 1,
			'id' => 1, 
			'title' => 'Morro', 
			'locale' => 'no-nb'
		),
		array(
			'trans_id' => 2,
			'id' => 2, 
			'title' => 'Vanskelig', 
			'locale' => 'no-nb'
		),
		array(
			'trans_id' => 3,
			'id' => 3, 
			'title' => 'Triks', 
			'locale' => 'no-nb'
		),
		array(
			'trans_id' => 4,
			'id' => 4, 
			'title' => 'Nyheter', 
			'locale' => 'es-es'
		),
		array(
			'trans_id' => 5,
			'id' => 1, 
			'title' => 'Diversión', 
			'locale' => 'es-es'
		),
		array(
			'trans_id' => 6,
			'id' => 2, 
			'title' => 'Duro', 
			'locale' => 'es-es'
		),
		array(
			'trans_id' => 7,
			'id' => 3, 
			'title' => 'Truco', 
			'locale' => 'es-es'
		),
		array(
			'trans_id' => 8,
			'id' => 4, 
			'title' => 'Noticias', 
			'locale' => 'es-es'
		),
	);
}
?>