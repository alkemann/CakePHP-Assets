<?php
class MultilingualBooksLocaleFixture extends CakeTestFixture {
    var $name = 'MultilingualBooksLocale';
    
    var $fields = array(
        'trans_id' => array('type' => 'integer', 'key' => 'primary'),
        'id' => array('type' => 'integer'),
        'title' => array('type' => 'string', 'length' => 255, 'null' => false),
        'description' => array('type' => 'string', 'length' => 255, 'null' => false),
        'locale' => array('type' => 'string', 'length' => 15, 'null' => false)        
    );
    var $records = array(
		array('trans_id' => 1, 'id' => 2, 'title' => 'Femte Bok',  'description' => 'Innhold av femte bok', 'locale' => 'no-nb' ),
		array('trans_id' => 2, 'id' => 1, 'title' => 'Sjette Bok', 'description' => 'Innhold av sjette bok','locale' => 'no-nb' ),
		array('trans_id' => 3, 'id' => 1, 'title' => 'Sexto Libro', 'description' => 'Sumario del sexto libro','locale' => 'es-es' ),
    );
}
?>