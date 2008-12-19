<?php 
class BehaviorsGroupTest extends GroupTest { 
  var $label = 'Behaviors'; 
  
  function behaviorsGroupTest() {    
    TestManager::addTestCasesFromDirectory($this, APP_TEST_CASES . DS . 'behaviors' );   
  } 
} 
?>