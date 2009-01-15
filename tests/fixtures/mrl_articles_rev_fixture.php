<?php
class MrlArticlesRevFixture extends CakeTestFixture {
	var $name = 'MrlArticlesRev';
	var $fields = array(
			'version_id' => array('type' => 'integer','null' => false,'default' => NULL,'key' => 'primary'), 
			'version_created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
			'id' => array('type' => 'integer','null' => false,'default' => NULL), 
			'user_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
			'locale' => array('type' => 'string', 'null' => false, 'default' => 'en-us'), 
			'title' => array('type' => 'string', 'null' => false, 'default' => NULL), 
			'content' => array('type' => 'text', 'null' => false, 'default' => NULL),
			'Tag' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'indexes' => array('PRIMARY' => array('column' => 'version_id')));
			
	var $records = array(
		array(
			'version_id' => 1, 
			'version_created' => '2008-01-01', 
			'id' => 1, 
			'user_id' => 2, 
			'locale' => 'en-us',
			'title' => 'Nimrod Expedition', 
			'content' => 'The British Antarctic Expedition 1907–09, otherwise known as the Nimrod Expedition,
			 was the first of three expeditions to the Antarctic led by Ernest Shackleton. 
			 It was financed without governmental or institutional support and relied on private
			  loans and individual contributions. Its ship, Nimrod, was a 40-year-old small 
			  wooden sealer of 334 gross register tons, and the expedition\'s members generally 
			  lacked relevant experience.',
			'Tag' => '1,2,3'
		),
		array(
			'version_id' => 2, 
			'version_created' => '2008-02-02', 
			'id' => 2, 
			'user_id' => 2, 
			'locale' => 'en-us',
			'title' => 'Lorem ipsum', 
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.',
			'Tag' => '1,3'
		),
		array(
			'version_id' => 3, 
			'version_created' => '2008-03-03', 
			'id' => 3, 
			'user_id' => 1, 
			'locale' => 'en-us',
			'title' => 'Lorem ipsum', 
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.',
			'Tag' => '2,4'
		),	
		array(
			'version_id' => 4, 
			'version_created' => '2008-03-04', 
			'id' => 4, 
			'user_id' => 2, 
			'locale' => 'en-us',
			'title' => 'To be deleted', 
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.',
			'Tag' => null
		),
		array(
			'version_id' => 5, 
			'version_created' => '2008-03-05', 
			'id' => 5, 
			'user_id' => 2, 
			'locale' => 'en-us',
			'title' => 'To get tags', 
			'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.',
			'Tag' => null
		),
		array(
			'version_id' => 6, 
			'version_created' => '2008-01-02', 
			'id' => 1, 
			'user_id' => 2, 
			'locale' => 'es-es',
   			'title' => 'Nimrod Expedición', 
			'content' => 'La Expedición Antártica Británica 1907-09, también conocido como la Expedición Nimrod,  <br> fue la primera de tres expediciones a la Antártida encabezada por Ernest Shackleton.  <br> Fue financiado gubernamentales o sin apoyo institucional y privado basado en  <br> Préstamos y las contribuciones individuales. Su buque, Nimrod, era de 40 años de edad, los pequeños  <br> sellador de madera de 334 toneladas brutas de registro, y la expedición \ &#39;s en general  <br> Carecen de experiencia pertinente.',		
			'Tag' => '1,2,3'
		),
	);
}
?>