<?php

class OrderedBook extends CakeTestModel {
	public $name = 'OrderedBook';
	public $actsAs = array('Ordered' => array('foreign_key' => FALSE, 'field' => 'weight'));
	public $fixture = 'ordered_book';
}
class OrderedPage extends CakeTestModel {
	public $name = 'OrderedPage';
	public $actsAs = array('Ordered' => array('foreign_key' => 'book_id'));
}
class OrderedMark extends CakeTestModel {
	public $name = 'OrderedMark';
	public $actsAs = array('Ordered' => array('field' => 'nose'));	
}
class OrderedCase extends CakeTestCase {
    public $Page = NULL;
    public $OrderedMark = NULL;
    public $OrderedBook = NULL;
	public $fixtures = array('app.ordered_page', 'app.ordered_book', 'app.ordered_mark');

	function startTest() {		
		$this->Page = ClassRegistry::init('OrderedPage');
		$this->Mark = ClassRegistry::init('OrderedMark');
		$this->Book = ClassRegistry::init('OrderedBook');
	}	
	
	function endTest() {
		unset($this->Page);
		unset($this->Mark);
		unset($this->Book);
		ClassRegistry::flush();
	}
	
	private function findPagesByBook($book_id) {
		return $this->Page->find('all', array(
				'conditions' => array('book_id' => $book_id), 
				'fields' => array('title', 'book_id', 'weight')));
	}
	private function findMarksByOrder($order_id) {
		return $this->Mark->find('all', array(
				'conditions' => array('order_id' => $order_id), 
				'fields' => array('title', 'order_id', 'nose')));
	}

	function testFind() {	
		$result = $this->Page->find('first', array('conditions'=>array('book_id'=>1), 'fields' => array('title','weight')));
		$expected = array('OrderedPage' => array('title' => 'First Page', 'weight' => 1));        
		$this->assertEqual($result, $expected);  
		
		$result = $this->Page->find('first', array('conditions'=>array('book_id'=>2), 'fields' => array('title','weight')));
		$expected = array('OrderedPage' => array('title' => 'Front Page', 'weight' => 1));        
		$this->assertEqual($result, $expected);  

		$this->Page->create();
		$this->assertTrue( $this->Page->isFirst(2));  
		$this->Page->create();
		$this->assertFalse($this->Page->isFirst(1));  
		$this->Page->create();
		$this->assertFalse($this->Page->isFirst(4));  
		$this->Page->create();
		$this->assertFalse($this->Page->isFirst(5));  
		$this->Page->create();
		$this->assertTrue( $this->Page->isFirst(3));  
		$this->Page->create();
		$this->assertFalse($this->Page->isFirst(6)); 

		$this->Page->create();
		$this->assertFalse($this->Page->isLast(2));  
		$this->Page->create();
		$this->assertFalse($this->Page->isLast(1));  
		$this->Page->create();
		$this->assertFalse($this->Page->isLast(4));  
		$this->Page->create();
		$this->assertTrue( $this->Page->isLast(5));  
		$this->Page->create();
		$this->assertFalse($this->Page->isLast(3));  
		$this->Page->create();
		$this->assertTrue( $this->Page->isLast(6));  
 
		// No params illegal if not set in id or data
		$this->Page->id = NULL; $this->Page->data = NULL;
		$this->assertFalse($this->Page->isFirst());  
		$this->Page->id = NULL; $this->Page->data = NULL;
		$this->assertFalse($this->Page->isLast());  
		 
		// No params legal if set in properties Id or Data
		$this->Page->id = 2; $this->Page->data = NULL;
		$this->assertTrue($this->Page->isFirst()); 
		$this->Page->id = NULL; $this->Page->data = array('OrderedPage' => array('id' => 2));
		$this->assertTrue($this->Page->isFirst());  
		$this->Page->id = 5; $this->Page->data = NULL;
		$this->assertTrue($this->Page->isLast()); 
		$this->Page->id = NULL; $this->Page->data = array('OrderedPage' => array('id' => 5));
		$this->assertTrue($this->Page->isLast());  	 
	}

	function testAddPageBook1() {
		$this->Page->create(array('OrderedPage'=> array('title'=>'New Page','book_id'=>1)));
		$this->Page->save(NULL, FALSE);
		$result = $this->Page->find('first', array('conditions' => array('id' => $this->Page->id)));
		$expected = array('OrderedPage' => array('id' => $this->Page->id, 'title'=>'New Page','book_id'=>1, 'weight' => 5));
		$this->assertEqual($result, $expected);  
	}

	function testAddPageBook2() {
		$this->Page->create(array('OrderedPage'=> array('title'=>'Last Page','book_id'=>2)));
		$this->Page->save(NULL,FALSE);
		$id = $this->Page->getLastInsertID();
		$result = $this->Page->find('first', array('conditions' => array('id' => $id)));
		$expected = array('OrderedPage' => array('id' => $id, 'title'=>'Last Page','book_id'=>2, 'weight' => 3));
		$this->assertEqual($result, $expected);  
	}

	function testMoveThirdPageUp() {
		$this->Page->moveUp(4);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'First Page',  'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'Third Page',  'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 4 ))
		);
		
		$this->assertEqual($result, $expected);

		$this->Page->moveDown(4);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'First Page',  'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Third Page',  'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 4 ))
		);

		$this->assertEqual($result, $expected);
	}

	function testMoveLastPageFirst() {
		$this->Page->moveUp(5,TRUE);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'First Page',  'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Third Page',  'book_id' => 1, 'weight' => 4 ))
		);
		$this->assertEqual($result, $expected);

		$this->Page->moveDown(5,TRUE);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'First Page',  'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Third Page',  'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 4 ))
		);
		$this->assertEqual($result, $expected);
	}

	function testMoveThirdFirst() {
		$this->Page->moveUp(4,TRUE);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'Third Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'First Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 4 ))
		);
		$this->assertEqual($result, $expected);	

		$this->Page->moveDown(4,2);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'First Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Third Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 4 ))
		);
		$this->assertEqual($result, $expected);
	}

	function testMoveSecondLast() {
		$this->Page->moveDown(1,TRUE);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'First Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'Third Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 4 ))
		);
		$this->assertEqual($result, $expected);	

		$this->Page->moveUp(1,2);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'First Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Third Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 4 ))
		);
		$this->assertEqual($result, $expected);
	}	

	function testAddThreeToBookTwoAndMoveLastUpThree() {
		$this->Page->create(array('OrderedPage'=> array('title'=>'New Page 1','book_id'=>2)));
		$this->Page->save(NULL, FALSE);
		$this->Page->create(array('OrderedPage'=> array('title'=>'New Page 2','book_id'=>2)));
		$this->Page->save(NULL, FALSE);
		$this->Page->create(array('OrderedPage'=> array('title'=>'New Page 3','book_id'=>2)));
		$this->Page->save(NULL, FALSE);
		$id = $this->Page->getInsertID();
		$this->Page->moveUp($id, 3);
		$result = $this->findPagesByBook(2);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'Front Page', 'book_id' => 2, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'New Page 3', 'book_id' => 2, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Intro Page', 'book_id' => 2, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'New Page 1', 'book_id' => 2, 'weight' => 4 )),		
			4 => Array('OrderedPage' => Array( 'title' => 'New Page 2', 'book_id' => 2, 'weight' => 5 ))		
		);
		$this->assertEqual($result, $expected);		
		
		$this->Page->deleteAll(array(
				'book_id' => 2,
				'title LIKE' => 'NEW%'
			),
			FALSE,
			array('beforeDelete', 'afterDelete')
		);
		$result = $this->findPagesByBook(2);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'Front Page', 'book_id' => 2, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'Intro Page', 'book_id' => 2, 'weight' => 2 )),	
		);
		$this->assertEqual($result, $expected);			
	}

	function testSortyByTitle() {
		$this->Page->sortBy('title ASC', 1);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'First Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Third Page', 'book_id' => 1, 'weight' => 4 ))
		);
		$this->assertEqual($result, $expected);
		
		$this->Page->sortBy('title DESC', 1);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'Third Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'Second Page','book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Fourth Page','book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'First Page', 'book_id' => 1, 'weight' => 4 ))
		);
		$this->assertEqual($result, $expected);
	}	

	function testFindWithWeirdWeightFieldName() {
        $result = $this->Mark->find('first', array('conditions'=>array('order_id'=>1), 'fields' => array('title','nose')));
        $expected = array('OrderedMark' => array('title' => 'First Mark', 'nose' => 1));        
		$this->assertEqual($result, $expected);  
		
		$this->assertFalse($this->Mark->isLast(1));  
		$this->assertFalse($this->Mark->isLast(2));  
		$this->assertFalse($this->Mark->isLast(3));  
	 	 $this->assertTrue($this->Mark->isLast(4));  
		$this->assertFalse($this->Mark->isLast(5));  
	  	 $this->assertTrue($this->Mark->isLast(6));  
		
		 $this->assertTrue($this->Mark->isFirst(1));  
		$this->assertFalse($this->Mark->isFirst(2));  
		$this->assertFalse($this->Mark->isFirst(3));  
		$this->assertFalse($this->Mark->isFirst(4));  
		 $this->assertTrue($this->Mark->isFirst(5));  
		$this->assertFalse($this->Mark->isFirst(6));  
	}

	function testMoveDownWithWeirdField() {
		$this->Mark->moveDown(1);
		$result = $this->findMarksByOrder(1);
		$expected = array(
			0 => Array('OrderedMark' => Array( 'title' => 'Second Mark', 'order_id' => 1, 'nose' => 1 )),
			1 => Array('OrderedMark' => Array( 'title' => 'First Mark',  'order_id' => 1, 'nose' => 2 )),
			2 => Array('OrderedMark' => Array( 'title' => 'Third Mark',  'order_id' => 1, 'nose' => 3 )),
			3 => Array('OrderedMark' => Array( 'title' => 'Fourth Mark', 'order_id' => 1, 'nose' => 4 )),
		);
		$this->assertEqual($result, $expected);	
		
		$this->Mark->moveDown(1,2);
		$result = $this->findMarksByOrder(1);
		$expected = array(
			0 => Array('OrderedMark' => Array( 'title' => 'Second Mark', 'order_id' => 1, 'nose' => 1 )),
			1 => Array('OrderedMark' => Array( 'title' => 'Third Mark',  'order_id' => 1, 'nose' => 2 )),
			2 => Array('OrderedMark' => Array( 'title' => 'Fourth Mark', 'order_id' => 1, 'nose' => 3 )),
			3 => Array('OrderedMark' => Array( 'title' => 'First Mark',  'order_id' => 1, 'nose' => 4 )),
		);
		$this->assertEqual($result, $expected);				
	}
	
	function testMoveUpWithWeirdField() {
		$this->Mark->moveUp(4);
		$result = $this->findMarksByOrder(1);
		$expected = array(
			0 => Array('OrderedMark' => Array( 'title' => 'First Mark',  'order_id' => 1, 'nose' => 1 )),
			1 => Array('OrderedMark' => Array( 'title' => 'Second Mark', 'order_id' => 1, 'nose' => 2 )),
			2 => Array('OrderedMark' => Array( 'title' => 'Fourth Mark', 'order_id' => 1, 'nose' => 3 )),
			3 => Array('OrderedMark' => Array( 'title' => 'Third Mark',  'order_id' => 1, 'nose' => 4 )),
		);
		$this->assertEqual($result, $expected);	
		
		$this->Mark->moveUp(4,2);
		$result = $this->findMarksByOrder(1);
		$expected = array(
			0 => Array('OrderedMark' => Array( 'title' => 'Fourth Mark', 'order_id' => 1, 'nose' => 1 )),
			1 => Array('OrderedMark' => Array( 'title' => 'First Mark',  'order_id' => 1, 'nose' => 2 )),
			2 => Array('OrderedMark' => Array( 'title' => 'Second Mark', 'order_id' => 1, 'nose' => 3 )),
			3 => Array('OrderedMark' => Array( 'title' => 'Third Mark',  'order_id' => 1, 'nose' => 4 )),
		);
		$this->assertEqual($result, $expected);				
	}	
	
	function testSortByTitleWithWeirdField() {
		
		$this->Mark->sortBy('title ASC', 1);
		$result = $this->findMarksByOrder(1);
		$expected = array(
			0 => Array('OrderedMark' => Array( 'title' => 'First Mark', 'order_id' => 1,  'nose' => 1 )),
			1 => Array('OrderedMark' => Array( 'title' => 'Fourth Mark', 'order_id' => 1, 'nose' => 2 )),
			2 => Array('OrderedMark' => Array( 'title' => 'Second Mark', 'order_id' => 1, 'nose' => 3 )),
			3 => Array('OrderedMark' => Array( 'title' => 'Third Mark', 'order_id' => 1,  'nose' => 4 ))
		);
		$this->assertEqual($result, $expected);
	}

	function testIllegalMoves() {
		// missing parameters 
		$this->Page->id = NULL; $this->Page->data = NULL;
		$this->assertFalse($this->Page->moveTo());
		$this->assertFalse($this->Page->moveTo(1));
		$this->assertFalse($this->Page->moveUp());
		$this->assertFalse($this->Page->moveDown());
	
		// Missing param, but should use ID or Data property
		$this->Page->id = 6; $this->Page->data = NULL;
		$this->assertTrue($this->Page->moveUp());
		$this->Page->id = 6; $this->Page->data = NULL;
		$this->assertTrue($this->Page->moveDown());
		$this->Page->id = NULL; $this->Page->data = array('OrderedPage'=> array('id' => 6));
		$this->assertTrue($this->Page->moveUp());
		$this->Page->id = NULL; $this->Page->data = array('OrderedPage'=> array('id' => 6));
		$this->assertTrue($this->Page->moveDown());
		
		// illegal moves
		$this->assertFalse($this->Page->moveUp(2));
		$this->assertFalse($this->Page->moveUp(3));
		$this->assertFalse($this->Page->moveDown(5));
		$this->assertFalse($this->Page->moveDown(6));
		
		$this->assertFalse($this->Page->moveUp(2,TRUE));
		$this->assertFalse($this->Page->moveUp(2,2));
		$this->assertFalse($this->Page->moveUp(4,3));
		$this->assertFalse($this->Page->moveUp(5,5));
		
		$this->assertFalse($this->Page->moveDown(5,TRUE));
		$this->assertFalse($this->Page->moveDown(5,2));
		$this->assertFalse($this->Page->moveDown(3,3));
		$this->assertFalse($this->Page->moveDown(4,5));
		
		
		$this->assertFalse($this->Page->moveUp('aa'));
		$this->assertFalse($this->Page->moveUp(array(1=>'aa')));
		
		$this->assertFalse($this->Page->moveDown('aa'));
		$this->assertFalse($this->Page->moveDown(array(1=>'aa')));	
		
		$this->assertFalse($this->Page->moveTo('aa'));
		$this->assertFalse($this->Page->moveTo('aa','dw'));
		$this->assertFalse($this->Page->moveTo(array(1=>'aa'),'a'));		
		
		// non-existing IDs
		$this->assertFalse($this->Page->moveUp(11,1));
		$this->assertFalse($this->Page->moveDown(11,1));		
		$this->assertFalse($this->Page->moveTo(11,1));
		
		// move to same position
		$this->assertFalse($this->Page->moveTo(2,1));
		// move to too high weight
		$this->assertFalse($this->Page->moveTo(2,5));
		$this->assertFalse($this->Page->moveTo(2,1000));
		// move to 0 or negative weigt
		$this->assertFalse($this->Page->moveTo(2,0));
		$this->assertFalse($this->Page->moveTo(2,-1));		
	}
	
	function testMoveTo() {
		
		// Move Second Page Last
		$this->Page->moveTo(1,4);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array('title' => 'First Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array('title' => 'Third Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array('title' => 'Fourth Page','book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array('title' => 'Second Page','book_id' => 1, 'weight' => 4 )),
		);
		$this->assertEqual($result, $expected);
		
		// Move Second Page Back
		$this->Page->moveTo(1,2);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array('title' => 'First Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array('title' => 'Second Page','book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array('title' => 'Third Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array('title' => 'Fourth Page','book_id' => 1, 'weight' => 4 )),
		);
		$this->assertEqual($result, $expected);
		
		// Move First Page Last
		$this->Page->moveTo(2,4);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array('title' => 'Second Page','book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array('title' => 'Third Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array('title' => 'Fourth Page','book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array('title' => 'First Page', 'book_id' => 1, 'weight' => 4 )),
		);
		$this->assertEqual($result, $expected);
		
		// Weird field move
			$this->Mark->moveTo(1,2);
		$result = $this->findMarksByOrder(1);
		$expected = array(
			0 => Array('OrderedMark' => Array( 'title' => 'Second Mark', 'order_id' => 1, 'nose' => 1 )),
			1 => Array('OrderedMark' => Array( 'title' => 'First Mark',  'order_id' => 1, 'nose' => 2 )),
			2 => Array('OrderedMark' => Array( 'title' => 'Third Mark',  'order_id' => 1, 'nose' => 3 )),
			3 => Array('OrderedMark' => Array( 'title' => 'Fourth Mark', 'order_id' => 1, 'nose' => 4 )),
		);
		$this->assertEqual($result, $expected);	
			
	}

	function testNoForeignKey() {	
        // test that order by weight is added 
		$result = $this->Book->find('first', array('conditions'=>array(1=>1),'fields'=>array('title','weight')));
        $expected = array('OrderedBook' => array('title' => 'First Book', 'weight' => 1));        
		$this->assertEqual($result, $expected);  
		
		
		$this->assertTrue($this->Book->isFirst(2));
		$this->assertFalse($this->Book->isFirst(1));
		$this->assertFalse($this->Book->isFirst(3));
		
		$this->assertTrue($this->Book->isLast(3));
		$this->assertFalse($this->Book->isLast(1));
		$this->assertFalse($this->Book->isLast(4));
		
		$this->Book->create(array('OrderedBook' => array('title' => 'New Book')));
		$this->Book->save(NULL, FALSE);
		$result = $this->Book->find('all');
		$expected = array(
			0 => Array('OrderedBook' => Array('id' => 2, 'title' => 'First Book', 'weight' => 1 )),
			1 => Array('OrderedBook' => Array('id' => 1, 'title' => 'Second Book','weight' => 2 )),
			2 => Array('OrderedBook' => Array('id' => 4, 'title' => 'Third Book', 'weight' => 3 )),
			3 => Array('OrderedBook' => Array('id' => 5, 'title' => 'Fourth Book','weight' => 4 )),
			4 => Array('OrderedBook' => Array('id' => 6, 'title' => 'Fifth Book', 'weight' => 5 )),
			5 => Array('OrderedBook' => Array('id' => 3, 'title' => 'Sixth Book', 'weight' => 6 )),
			6 => Array('OrderedBook' => Array('id' => 7, 'title' => 'New Book',   'weight' => 7 )),
		);  
		$this->assertEqual($result, $expected);  
				
	}
	
	function testFalseMoves() {	
		// false moves 
		$this->Book->id = NULL; $this->Book->data = NULL;
		$this->assertFalse($this->Book->moveTo());
		$this->assertFalse($this->Book->moveUp());
		$this->assertFalse($this->Book->moveDown());
	
		$this->assertFalse($this->Book->moveUp(2));
		$this->assertFalse($this->Book->moveDown(3));
		$this->assertFalse($this->Book->moveTo(1));
			
		$this->assertFalse($this->Book->moveUp(2,TRUE));
		$this->assertFalse($this->Book->moveUp(2,2));
		$this->assertFalse($this->Book->moveUp(4,3));
		$this->assertFalse($this->Book->moveUp(5,5));
		
		$this->assertFalse($this->Book->moveDown(3,TRUE)); 
		$this->assertFalse($this->Book->moveDown(3,2));
		$this->assertFalse($this->Book->moveDown(3,3));
		$this->assertFalse($this->Book->moveDown(4,5));
			
	}
	
	function testLegalMoves() {		
		// legal moves
		$this->assertTrue($this->Book->moveUp(3));
		$this->assertTrue($this->Book->moveUp(3,2));
		$this->assertTrue($this->Book->moveUp(3,TRUE));
		$this->assertTrue($this->Book->moveDown(2));
		$this->assertTrue($this->Book->moveDown(2,2));
		$this->assertTrue($this->Book->moveDown(2,TRUE));
		$this->assertTrue($this->Book->moveTo(2,1));
		$this->assertTrue($this->Book->moveTo(3,6));
		
	}
	
	function testSortBy1() {		
		$this->Book->sortBy('title ASC');
		$result = $this->Book->find('all');
		$expected = array(
			0 => Array('OrderedBook' => Array('id' => 6, 'title' => 'Fifth Book', 'weight' => 1 )),
			1 => Array('OrderedBook' => Array('id' => 2, 'title' => 'First Book', 'weight' => 2 )),
			2 => Array('OrderedBook' => Array('id' => 5, 'title' => 'Fourth Book','weight' => 3 )),
			3 => Array('OrderedBook' => Array('id' => 1, 'title' => 'Second Book','weight' => 4 )),
			4 => Array('OrderedBook' => Array('id' => 3, 'title' => 'Sixth Book', 'weight' => 5 )),
			5 => Array('OrderedBook' => Array('id' => 4, 'title' => 'Third Book', 'weight' => 6 )),	
		);     
		$this->assertEqual($result, $expected, 'Sort all books by title : %s');  		
	}
	
	function testSortBy2() {		
		$this->Page->sortBy('title',1);
		$result = $this->Page->find('all', array('conditions'=>array('book_id'=>1)));
		$expected = array(
			0 => Array('OrderedPage' => Array('id' => 2, 'title' => 'First Page', 	'weight' => 1, 'book_id' => 1 )),
			1 => Array('OrderedPage' => Array('id' => 5, 'title' => 'Fourth Page', 'weight' => 2, 'book_id' => 1  )),
			2 => Array('OrderedPage' => Array('id' => 1, 'title' => 'Second Page',	'weight' => 3, 'book_id' => 1  )),
			3 => Array('OrderedPage' => Array('id' => 4, 'title' => 'Third Page',	'weight' => 4, 'book_id' => 1  )),
		);     
		$this->assertEqual($result, $expected, 'Sort by title for book 1 : %s');  		
	}	
	
	function testSortBy3() {		
		$this->Page->create(array('OrderedPage'=>array('title'=>'All the men', 'book_id'=> 2)));
		$this->Page->save(null, false);
		$this->Page->sortBy('title',2);
		$result = $this->Page->find('all', array('conditions'=>array('book_id'=>2)));
		$expected = array(
			0 => Array('OrderedPage' => Array('id' => 7, 'title' => 'All the men', 'weight' => 1, 'book_id' => 2 )),
			1 => Array('OrderedPage' => Array('id' => 3, 'title' => 'Front Page',  'weight' => 2, 'book_id' => 2 )),
			2 => Array('OrderedPage' => Array('id' => 6, 'title' => 'Intro Page',  'weight' => 3, 'book_id' => 2 ))
		);     
		$this->assertEqual($result, $expected, 'Sort by title for book 2 : %s');  		
	}	
		
	function testSortBy4() {		
		$this->Page->create(array('OrderedPage'=>array('title'=>'All the men', 'book_id'=> 2)));
		$this->Page->save(null, false);
		$this->assertFalse($this->Page->sortBy('title')); // need to specify book	
	}	
		
	function testDeleteAll() {		
		$this->Book->deleteAll(array('weight >'=>1,'weight <'=>6),true,array('beforeDelete','afterDelete'));
		$result = $this->Book->find('all',array('conditions'=>array(1=>1),'fields'=>array('id','weight')));
		$expected = array(
			0 => array('OrderedBook' => array('id' => 2, 'weight' => 1)),
			1 => array('OrderedBook' => array('id' => 3, 'weight' => 2)),
		);
		$this->assertEqual($result, $expected);  	
	}
	
	function testResetWeights() {
		$this->Page->updateAll(array('weight'=>0));
		$this->Page->resetweights();
		$result = $this->Page->find('all', array('order'=>array('book_id','weight')));
		$expected = array(
			0 => Array('OrderedPage' => Array('id' => 2, 'title' => 'First Page', 	'weight' => 1, 'book_id' => 1 )),
			1 => Array('OrderedPage' => Array('id' => 5, 'title' => 'Fourth Page', 'weight' => 2, 'book_id' => 1 )),
			2 => Array('OrderedPage' => Array('id' => 1, 'title' => 'Second Page',	'weight' => 3, 'book_id' => 1 )),
			3 => Array('OrderedPage' => Array('id' => 4, 'title' => 'Third Page',	'weight' => 4, 'book_id' => 1 )),
			4 => Array('OrderedPage' => Array('id' => 3, 'title' => 'Front Page',  'weight' => 1, 'book_id' => 2 )),
			5 => Array('OrderedPage' => Array('id' => 6, 'title' => 'Intro Page',  'weight' => 2, 'book_id' => 2 ))
		
		);
		$this->assertEqual($result, $expected, 'ResetWeights : %s'); 		
	}
	
	function testWithSoftDelete() { 
		$this->Mark->Behaviors->attach('SoftDeletable');
		$result = $this->Mark->find('all', array('conditions'=>array('order_id'=>1), 'fields' => array('id','nose','deleted')));
		$expected = array(
			0 => array('OrderedMark' => array(
				'id' => 1,
				'nose' => 1,
				'deleted' => 0
			)),
			1 => array('OrderedMark' => array(
				'id' => 2,
				'nose' => 2,
				'deleted' => 0
			)),
			2 => array('OrderedMark' => array(
				'id' => 3,
				'nose' => 3,
				'deleted' => 0
			)),
			3 => array('OrderedMark' => array(
				'id' => 4,
				'nose' => 4,
				'deleted' => 0
			)),
		);
		$this->assertEqual($result, $expected, 'Softdeletable start test : %s');
		
		$this->Mark->del(3);
		
        $this->Mark->enableSoftDeletable('find', false); 
		$this->Mark->removeFromList(3);
        $this->Mark->enableSoftDeletable('find', true); 
		
		$result = $this->Mark->find('first', array('conditions'=>array('id'=>3,'deleted'=>1), 'fields' => array('id','nose','deleted')));
		$expected = array(
			'OrderedMark' => array(
				'id' => 3,
				'nose' => 0,
				'deleted' => 1
			),
		);
		$this->assertEqual($result, $expected, 'Softdeleted model test : %s');
	
		$result = $this->Mark->find('all', array('conditions'=>array('order_id'=>1), 'fields' => array('id','nose','deleted')));
		$expected = array(
			0 => array('OrderedMark' => array(
				'id' => 1,
				'nose' => 1,
				'deleted' => 0
			)),
			1 => array('OrderedMark' => array(
				'id' => 2,
				'nose' => 2,
				'deleted' => 0
			)),
			2 => array('OrderedMark' => array(
				'id' => 4,
				'nose' => 3,
				'deleted' => 0
			)),
		);
		$this->assertEqual($result, $expected, 'Softdeleteable result test : %s');
		
		if ($this->Mark->undelete(3)) {
			$this->assertTrue($this->Mark->moveTo(3,true), 'Move successfull : %s');
		}

		$result = $this->Mark->find('all', array('conditions'=>array('order_id'=>1,'deleted'=>array(0,1)), 'fields' => array('id','nose','deleted')));
		$expected = array(
			0 => array('OrderedMark' => array(
				'id' => 1,
				'nose' => 1,
				'deleted' => 0
			)),
			1 => array('OrderedMark' => array(
				'id' => 2,
				'nose' => 2,
				'deleted' => 0
			)),
			2 => array('OrderedMark' => array(
				'id' => 4,
				'nose' => 3,
				'deleted' => 0
			)),
			3 => array('OrderedMark' => array(
				'id' => 3,
				'nose' => 4,
				'deleted' => 0
			)),
		);
		$this->assertEqual($result, $expected, 'Softdeletable reinserted test : %s');
		
		
		$this->Mark->Behaviors->detach('SoftDeletable');
	}
	
	function testBookDelete() {
		$result = $this->Book->find('list', array('fields'=>array('title','weight')));
		$expected = array(
			'First Book' => 1,
		    'Second Book' => 2,
		    'Third Book' => 3,
		    'Fourth Book' => 4,
		    'Fifth Book' => 5,
		    'Sixth Book' => 6
		);
		$this->assertEqual($expected,$result);
		
		$this->assertTrue($this->Book->delete(4));
		
		$result = $this->Book->find('list', array('fields'=>array('title','weight')));
		$expected = array(
			'First Book' => 1,
		    'Second Book' => 2,
		    'Fourth Book' => 3,
		    'Fifth Book' => 4,
		    'Sixth Book' => 5
		);
		$this->assertEqual($expected,$result);
	}	
	
	function testPageDelete() {
		$result = $this->Page->find('list', array(
      'conditions' => array('book_id' => 1),
      'fields'=>array('title','weight')));
		$expected = array(
			'First Page' => 1,
		    'Second Page' => 2,
		    'Third Page' => 3,
		    'Fourth Page' => 4,
		);
		$this->assertEqual($expected,$result);

		$this->assertTrue($this->Page->delete(4));

		$result = $this->Page->find('list', array(
      'conditions' => array('book_id' => 1),
      'fields'=>array('title','weight')));
		$expected = array(
			'First Page' => 1,
		    'Second Page' => 2,
		    'Fourth Page' => 3,
		);
		$this->assertEqual($expected,$result);
	}

	/**
	 * test changed foreign_key
	 *
	 * @author cyberlussi 2010-09
	 */
	function testPageChangeBook() {

	    $this->Page->read('id', 1);
        $this->Page->set('book_id', 2);
        $this->Page->save();

        $result = $this->Page->find('all', array('conditions'=>array('book_id'=>2)));
        $expected = array(
            0 => Array('OrderedPage' => Array('id' => 3, 'title' => 'Front Page',   'book_id' => 2, 'weight' => 1 )),
            1 => Array('OrderedPage' => Array('id' => 6, 'title' => 'Intro Page',   'book_id' => 2, 'weight' => 2 )),
            2 => Array('OrderedPage' => Array('id' => 1, 'title' => 'Second Page',  'book_id' => 2, 'weight' => 3 ))
        );
        $this->assertEqual($expected, $result);

        $result = $this->Page->find('all', array('conditions'=>array('book_id'=>1)));
        $expected = array(
            0 => Array('OrderedPage' => Array('id' => 2, 'title' => 'First Page',   'book_id' => 1, 'weight' => 1 )),
            1 => Array('OrderedPage' => Array('id' => 4, 'title' => 'Third Page',   'book_id' => 1, 'weight' => 2 )),
            2 => Array('OrderedPage' => Array('id' => 5, 'title' => 'Fourth Page',  'book_id' => 1, 'weight' => 3 ))
        );
        $this->assertEqual($expected, $result);
	}

}

?>
