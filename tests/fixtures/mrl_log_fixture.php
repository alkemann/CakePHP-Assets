<?php
class MrlLogFixture extends CakeTestFixture {
    var $name = 'MrlLog';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'title' => array('type' => 'string', 'length' => 255, 'null' => false),
        'action' => array('type' => 'string', 'length' => 255, 'null' => false),
        'model' => array('type' => 'string', 'length' => 255, 'null' => false),
        'model_id' => array('type' => 'integer','null' => true),
        'version_id' => array('type' => 'integer','null' => true),
        'created' => array('type' => 'datetime', 'null' => false),
    );
    var $records = array(
    );
}
?>