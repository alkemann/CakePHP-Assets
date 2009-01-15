<?php
class MrlArticlesLocaleFixture extends CakeTestFixture {
    var $name = 'MrlArticlesLocale';
  

    var $fields = array(
        'trans_id' => array('type' => 'integer', 'key' => 'primary'),
        'id' => array('type' => 'integer'),
        'title' => array('type' => 'string', 'length' => 255, 'null' => false),
        'content' => array('type' => 'text', 'null' => false),
        'locale' => array('type' => 'string', 'length' => 5, 'null' => false),
    );
    var $records = array(
    	array(
    		'trans_id' => 1,
    		'id' => 1,
    		'title' => 'Nimrod Expedición', 
			'content' => 'La Expedición Antártica Británica 1907-09, también conocido como la Expedición Nimrod,  <br> fue la primera de tres expediciones a la Antártida encabezada por Ernest Shackleton.  <br> Fue financiado gubernamentales o sin apoyo institucional y privado basado en  <br> Préstamos y las contribuciones individuales. Su buque, Nimrod, era de 40 años de edad, los pequeños  <br> sellador de madera de 334 toneladas brutas de registro, y la expedición \ &#39;s en general  <br> Carecen de experiencia pertinente.',
			'locale' => 'es-es'
    	),
		array('trans_id' => 2,'id' => 3,  'title' => 'Introduzione alla Terra', 'content'=>'italian', 'locale' => 'it-it' ),
		array('trans_id' => 3,'id' => 3,  'title' => 'Introduccin a la Tierra', 'content'=>'spanish', 'locale' => 'es-es' )
	);
}
?>