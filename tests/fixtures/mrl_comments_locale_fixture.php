<?php
class MrlCommentsLocaleFixture extends CakeTestFixture {
    var $name = 'MrlCommentsLocale';
  

    var $fields = array(
        'trans_id' => array('type' => 'integer', 'key' => 'primary'),
        'id' => array('type' => 'integer'),
        'content' => array('type' => 'string', 'length' => 255, 'null' => false),
        'locale' => array('type' => 'string', 'length' => 5, 'null' => false),
    );
    var $records = array(
		array('trans_id' => 1,'id' => 3, 'content'=>'italian', 'locale' => 'it-it' ),
		array('trans_id' => 2,'id' => 3, 'content'=>'spanish', 'locale' => 'es-es' ),
		array('trans_id' => 3,'id' => 1, 'content'=>'spanish comment 1', 'locale' => 'es-es' ),
		array('trans_id' => 4,'id' => 2, 'content'=>'spanish comment 2', 'locale' => 'es-es' )
	);
}
?>