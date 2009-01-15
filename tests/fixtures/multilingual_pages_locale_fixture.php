<?php
class MultilingualPagesLocaleFixture extends CakeTestFixture {
    var $name = 'MultilingualPagesLocale';
    
    var $fields = array(
        'trans_id' => array('type' => 'integer', 'key' => 'primary'),
        'id' => array('type' => 'integer'),
        'content' => array('type' => 'string', 'length' => 255, 'null' => false),
        'locale' => array('type' => 'string', 'length' => 15, 'null' => false)        
    );
    var $records = array(
		array('trans_id' => 1,  'id' => 1, 'content' => 'Side 1',  'locale' => 'no-nb' ),
		array('trans_id' => 2,  'id' => 2, 'content' => 'Side 2',  'locale' => 'no-nb' ),
		array('trans_id' => 3,  'id' => 2, 'content' => 'Página 2','locale' => 'es-es' ),
		array('trans_id' => 4,  'id' => 3, 'content' => 'Side 3',  'locale' => 'no-nb' ),
		array('trans_id' => 5,  'id' => 4, 'content' => 'Side 4',  'locale' => 'no-nb' ),
		array('trans_id' => 6,  'id' => 5, 'content' => 'Side 5',  'locale' => 'no-nb' ),
		array('trans_id' => 7,  'id' => 6, 'content' => 'Seite 1', 'locale' => 'de-de' ),
		array('trans_id' => 8,  'id' => 7, 'content' => 'Seite 2', 'locale' => 'de-de' ),
		array('trans_id' => 9,  'id' => 6, 'content' => 'Side 6', 'locale' => 'no-nb' ),
    );
}
?>