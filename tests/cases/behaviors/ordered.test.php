<?php
//App::import('behavior', 'Ordered');
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
    public $OrderedPage = NULL;
    public $OrderedMark = NULL;
    public $OrderedBook = NULL;
	public $fixtures = array('app.ordered_page', 'app.ordered_book', 'app.ordered_mark');

	function start() {
		parent::start();
		$this->OrderedPage = ClassRegistry::init('OrderedPage');
		$this->OrderedMark = ClassRegistry::init('OrderedMark');
		$this->OrderedBook = ClassRegistry::init('OrderedBook');
	}	
	private function findPagesByBook($book_id) {
		return $this->OrderedPage->find('all', array(
				'conditions' => array('book_id' => $book_id), 
				'fields' => array('title', 'book_id', 'weight')));
	}
	private function findMarksByOrder($order_id) {
		return $this->OrderedMark->find('all', array(
				'conditions' => array('order_id' => $order_id), 
				'fields' => array('title', 'order_id', 'nose')));
	}

	function testFind() {	
        $result = $this->OrderedPage->find('first', array('conditions'=>array('book_id'=>1), 'fields' => array('title','weight')));
        $expected = array('OrderedPage' => array('title' => 'First Page', 'weight' => 1));        
		$this->assertEqual($result, $expected);  
		
        $result = $this->OrderedPage->find('first', array('conditions'=>array('book_id'=>2), 'fields' => array('title','weight')));
        $expected = array('OrderedPage' => array('title' => 'Front Page', 'weight' => 1));        
		$this->assertEqual($result, $expected);  
		
		 $this->assertTrue($this->OrderedPage->isFirst(2));  
		$this->assertFalse($this->OrderedPage->isFirst(1));  
		$this->assertFalse($this->OrderedPage->isFirst(4));  
		$this->assertFalse($this->OrderedPage->isFirst(5));  
	 	 $this->assertTrue($this->OrderedPage->isFirst(3));  
		$this->assertFalse($this->OrderedPage->isFirst(6)); 
		
		$this->assertFalse($this->OrderedPage->isLast(2));  
		$this->assertFalse($this->OrderedPage->isLast(1));  
		$this->assertFalse($this->OrderedPage->isLast(4));  
		 $this->assertTrue($this->OrderedPage->isLast(5));  
		$this->assertFalse($this->OrderedPage->isLast(3));  
		 $this->assertTrue($this->OrderedPage->isLast(6));  
		 
		 // No params illegal if not set in id or data
		 $this->OrderedPage->id = NULL; $this->OrderedPage->data = NULL;
		 $this->assertFalse($this->OrderedPage->isFirst());  
		 $this->OrderedPage->id = NULL; $this->OrderedPage->data = NULL;
		 $this->assertFalse($this->OrderedPage->isLast());  
		 
		 // No params legal if set in properties Id or Data
		 $this->OrderedPage->id = 2; $this->OrderedPage->data = NULL;
		 $this->assertTrue($this->OrderedPage->isFirst()); 
		 $this->OrderedPage->id = NULL; $this->OrderedPage->data = array('OrderedPage' => array('id' => 2));
		 $this->assertTrue($this->OrderedPage->isFirst());  
		 $this->OrderedPage->id = 5; $this->OrderedPage->data = NULL;
		 $this->assertTrue($this->OrderedPage->isLast()); 
		 $this->OrderedPage->id = NULL; $this->OrderedPage->data = array('OrderedPage' => array('id' => 5));
		 $this->assertTrue($this->OrderedPage->isLast());  
		 
	}

	function testAddPageBook1() {
		$this->OrderedPage->create(array('OrderedPage'=> array('title'=>'New Page','book_id'=>1)));
		$this->OrderedPage->save(NULL, FALSE);
		$result = $this->OrderedPage->find('first', array('conditions' => array('id' => $this->OrderedPage->id)));
		$expected = array('OrderedPage' => array('id' => $this->OrderedPage->id, 'title'=>'New Page','book_id'=>1, 'weight' => 5));
		$this->assertEqual($result, $expected);  
	}

	function testAddPageBook2() {
		$this->OrderedPage->create(array('OrderedPage'=> array('title'=>'Last Page','book_id'=>2)));
		$this->OrderedPage->save(NULL,FALSE);
		$id = $this->OrderedPage->getLastInsertID();
		$result = $this->OrderedPage->find('first', array('conditions' => array('id' => $id)));
		$expected = array('OrderedPage' => array('id' => $id, 'title'=>'Last Page','book_id'=>2, 'weight' => 3));
		$this->assertEqual($result, $expected);  
	}

	function testMoveThirdPageUp() {
		$this->OrderedPage->moveUp(4);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'First Page',  'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'Third Page',  'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 4 ))
		);
		
		$this->assertEqual($result, $expected);

		$this->OrderedPage->moveDown(4);
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
		$this->OrderedPage->moveUp(5,TRUE);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'First Page',  'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Third Page',  'book_id' => 1, 'weight' => 4 ))
		);
		$this->assertEqual($result, $expected);

		$this->OrderedPage->moveDown(5,TRUE);
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
		$this->OrderedPage->moveUp(4,TRUE);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'Third Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'First Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 4 ))
		);
		$this->assertEqual($result, $expected);	

		$this->OrderedPage->moveDown(4,2);
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
		$this->OrderedPage->moveDown(1,TRUE);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'First Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'Third Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 4 ))
		);
		$this->assertEqual($result, $expected);	

		$this->OrderedPage->moveUp(1,2);
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
		$this->OrderedPage->create(array('OrderedPage'=> array('title'=>'New Page 1','book_id'=>2)));
		$this->OrderedPage->save(NULL, FALSE);
		$this->OrderedPage->create(array('OrderedPage'=> array('title'=>'New Page 2','book_id'=>2)));
		$this->OrderedPage->save(NULL, FALSE);
		$this->OrderedPage->create(array('OrderedPage'=> array('title'=>'New Page 3','book_id'=>2)));
		$this->OrderedPage->save(NULL, FALSE);
		$id = $this->OrderedPage->getInsertID();
		$this->OrderedPage->moveUp($id, 3);
		$result = $this->findPagesByBook(2);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'Front Page', 'book_id' => 2, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'New Page 3', 'book_id' => 2, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Intro Page', 'book_id' => 2, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'New Page 1', 'book_id' => 2, 'weight' => 4 )),		
			4 => Array('OrderedPage' => Array( 'title' => 'New Page 2', 'book_id' => 2, 'weight' => 5 ))		
		);
		$this->assertEqual($result, $expected);		
		
		$this->OrderedPage->deleteAll(array(
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
		$this->OrderedPage->sortBy('title ASC', 1);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array( 'title' => 'First Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array( 'title' => 'Fourth Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array( 'title' => 'Second Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array( 'title' => 'Third Page', 'book_id' => 1, 'weight' => 4 ))
		);
		$this->assertEqual($result, $expected);
		
		$this->OrderedPage->sortBy('title DESC', 1);
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
        $result = $this->OrderedMark->find('first', array('conditions'=>array('order_id'=>1), 'fields' => array('title','nose')));
        $expected = array('OrderedMark' => array('title' => 'First Mark', 'nose' => 1));        
		$this->assertEqual($result, $expected);  
		
		$this->assertFalse($this->OrderedMark->isLast(1));  
		$this->assertFalse($this->OrderedMark->isLast(2));  
		$this->assertFalse($this->OrderedMark->isLast(3));  
	 	 $this->assertTrue($this->OrderedMark->isLast(4));  
		$this->assertFalse($this->OrderedMark->isLast(5));  
	  	 $this->assertTrue($this->OrderedMark->isLast(6));  
		
		 $this->assertTrue($this->OrderedMark->isFirst(1));  
		$this->assertFalse($this->OrderedMark->isFirst(2));  
		$this->assertFalse($this->OrderedMark->isFirst(3));  
		$this->assertFalse($this->OrderedMark->isFirst(4));  
		 $this->assertTrue($this->OrderedMark->isFirst(5));  
		$this->assertFalse($this->OrderedMark->isFirst(6));  
	}

	function testMoveDownWithWeirdField() {
		$this->OrderedMark->moveDown(1);
		$result = $this->findMarksByOrder(1);
		$expected = array(
			0 => Array('OrderedMark' => Array( 'title' => 'Second Mark', 'order_id' => 1, 'nose' => 1 )),
			1 => Array('OrderedMark' => Array( 'title' => 'First Mark',  'order_id' => 1, 'nose' => 2 )),
			2 => Array('OrderedMark' => Array( 'title' => 'Third Mark',  'order_id' => 1, 'nose' => 3 )),
			3 => Array('OrderedMark' => Array( 'title' => 'Fourth Mark', 'order_id' => 1, 'nose' => 4 )),
		);
		$this->assertEqual($result, $expected);	
		
		$this->OrderedMark->moveDown(1,2);
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
		$this->OrderedMark->moveUp(4);
		$result = $this->findMarksByOrder(1);
		$expected = array(
			0 => Array('OrderedMark' => Array( 'title' => 'First Mark',  'order_id' => 1, 'nose' => 1 )),
			1 => Array('OrderedMark' => Array( 'title' => 'Second Mark', 'order_id' => 1, 'nose' => 2 )),
			2 => Array('OrderedMark' => Array( 'title' => 'Fourth Mark', 'order_id' => 1, 'nose' => 3 )),
			3 => Array('OrderedMark' => Array( 'title' => 'Third Mark',  'order_id' => 1, 'nose' => 4 )),
		);
		$this->assertEqual($result, $expected);	
		
		$this->OrderedMark->moveUp(4,2);
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
		
		$this->OrderedMark->sortBy('title ASC', 1);
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
		$this->OrderedPage->id = NULL; $this->OrderedPage->data = NULL;
		$this->assertFalse($this->OrderedPage->moveTo());
		$this->assertFalse($this->OrderedPage->moveTo(1));
		$this->assertFalse($this->OrderedPage->moveUp());
		$this->assertFalse($this->OrderedPage->moveDown());
	
		// Missing param, but should use ID or Data property
		$this->OrderedPage->id = 6; $this->OrderedPage->data = NULL;
		$this->assertTrue($this->OrderedPage->moveUp());
		$this->OrderedPage->id = 6; $this->OrderedPage->data = NULL;
		$this->assertTrue($this->OrderedPage->moveDown());
		$this->OrderedPage->id = NULL; $this->OrderedPage->data = array('OrderedPage'=> array('id' => 6));
		$this->assertTrue($this->OrderedPage->moveUp());
		$this->OrderedPage->id = NULL; $this->OrderedPage->data = array('OrderedPage'=> array('id' => 6));
		$this->assertTrue($this->OrderedPage->moveDown());
		
		// illegal moves
		$this->assertFalse($this->OrderedPage->moveUp(2));
		$this->assertFalse($this->OrderedPage->moveUp(3));
		$this->assertFalse($this->OrderedPage->moveDown(5));
		$this->assertFalse($this->OrderedPage->moveDown(6));
		
		$this->assertFalse($this->OrderedPage->moveUp(2,TRUE));
		$this->assertFalse($this->OrderedPage->moveUp(2,2));
		$this->assertFalse($this->OrderedPage->moveUp(4,3));
		$this->assertFalse($this->OrderedPage->moveUp(5,5));
		
		$this->assertFalse($this->OrderedPage->moveDown(5,TRUE));
		$this->assertFalse($this->OrderedPage->moveDown(5,2));
		$this->assertFalse($this->OrderedPage->moveDown(3,3));
		$this->assertFalse($this->OrderedPage->moveDown(4,5));
		
		
		$this->assertFalse($this->OrderedPage->moveUp('aa'));
		$this->assertFalse($this->OrderedPage->moveUp(array(1=>'aa')));
		
		$this->assertFalse($this->OrderedPage->moveDown('aa'));
		$this->assertFalse($this->OrderedPage->moveDown(array(1=>'aa')));	
		
		$this->assertFalse($this->OrderedPage->moveTo('aa'));
		$this->assertFalse($this->OrderedPage->moveTo('aa','dw'));
		$this->assertFalse($this->OrderedPage->moveTo(array(1=>'aa'),'a'));		
		
		// non-existing IDs
		$this->assertFalse($this->OrderedPage->moveUp(11,1));
		$this->assertFalse($this->OrderedPage->moveDown(11,1));		
		$this->assertFalse($this->OrderedPage->moveTo(11,1));
		
		// move to same position
		$this->assertFalse($this->OrderedPage->moveTo(2,1));
		// move to too high weight
		$this->assertFalse($this->OrderedPage->moveTo(2,5));
		$this->assertFalse($this->OrderedPage->moveTo(2,1000));
		// move to 0 or negative weigt
		$this->assertFalse($this->OrderedPage->moveTo(2,0));
		$this->assertFalse($this->OrderedPage->moveTo(2,-1));		
	}
	
	function testMoveTo() {
		
		// Move Second Page Last
		$this->OrderedPage->moveTo(1,4);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array('title' => 'First Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array('title' => 'Third Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array('title' => 'Fourth Page','book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array('title' => 'Second Page','book_id' => 1, 'weight' => 4 )),
		);
		$this->assertEqual($result, $expected);
		
		// Move Second Page Back
		$this->OrderedPage->moveTo(1,2);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array('title' => 'First Page', 'book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array('title' => 'Second Page','book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array('title' => 'Third Page', 'book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array('title' => 'Fourth Page','book_id' => 1, 'weight' => 4 )),
		);
		$this->assertEqual($result, $expected);
		
		// Move First Page Last
		$this->OrderedPage->moveTo(2,4);
		$result = $this->findPagesByBook(1);
		$expected = array(
			0 => Array('OrderedPage' => Array('title' => 'Second Page','book_id' => 1, 'weight' => 1 )),
			1 => Array('OrderedPage' => Array('title' => 'Third Page', 'book_id' => 1, 'weight' => 2 )),
			2 => Array('OrderedPage' => Array('title' => 'Fourth Page','book_id' => 1, 'weight' => 3 )),
			3 => Array('OrderedPage' => Array('title' => 'First Page', 'book_id' => 1, 'weight' => 4 )),
		);
		$this->assertEqual($result, $expected);
		
		// Weird field move
			$this->OrderedMark->moveTo(1,2);
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
		$result = $this->OrderedBook->find('first', array('conditions'=>array(1=>1),'fields'=>array('title','weight')));
        $expected = array('OrderedBook' => array('title' => 'First Book', 'weight' => 1));        
		$this->assertEqual($result, $expected);  
		
		
		$this->assertTrue($this->OrderedBook->isFirst(2));
		$this->assertFalse($this->OrderedBook->isFirst(1));
		$this->assertFalse($this->OrderedBook->isFirst(3));
		
		$this->assertTrue($this->OrderedBook->isLast(3));
		$this->assertFalse($this->OrderedBook->isLast(1));
		$this->assertFalse($this->OrderedBook->isLast(4));
		
		$this->OrderedBook->create(array('OrderedBook' => array('title' => 'New Book')));
		$this->OrderedBook->save(NULL, FALSE);
		$result = $this->OrderedBook->find('all');
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
		$this->OrderedBook->id = NULL; $this->OrderedBook->data = NULL;
		$this->assertFalse($this->OrderedBook->moveTo());
		$this->assertFalse($this->OrderedBook->moveUp());
		$this->assertFalse($this->OrderedBook->moveDown());
	
		$this->assertFalse($this->OrderedBook->moveUp(2));
		$this->assertFalse($this->OrderedBook->moveDown(3));
		$this->assertFalse($this->OrderedBook->moveTo(1));
			
		$this->assertFalse($this->OrderedBook->moveUp(2,TRUE));
		$this->assertFalse($this->OrderedBook->moveUp(2,2));
		$this->assertFalse($this->OrderedBook->moveUp(4,3));
		$this->assertFalse($this->OrderedBook->moveUp(5,5));
		
		$this->assertFalse($this->OrderedBook->moveDown(3,TRUE)); 
		$this->assertFalse($this->OrderedBook->moveDown(3,2));
		$this->assertFalse($this->OrderedBook->moveDown(3,3));
		$this->assertFalse($this->OrderedBook->moveDown(4,5));
			
	}
	
	function testLegalMoves() {		
		// legal moves
		$this->assertTrue($this->OrderedBook->moveUp(3));
		$this->assertTrue($this->OrderedBook->moveUp(3,2));
		$this->assertTrue($this->OrderedBook->moveUp(3,TRUE));
		$this->assertTrue($this->OrderedBook->moveDown(2));
		$this->assertTrue($this->OrderedBook->moveDown(2,2));
		$this->assertTrue($this->OrderedBook->moveDown(2,TRUE));
		$this->assertTrue($this->OrderedBook->moveTo(2,1));
		$this->assertTrue($this->OrderedBook->moveTo(3,6));
		
	}
	
	function testSortBy1() {		
		$this->OrderedBook->sortBy('title ASC');
		$result = $this->OrderedBook->find('all');
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
		$this->OrderedPage->sortBy('title',1);
		$result = $this->OrderedPage->find('all', array('conditions'=>array('book_id'=>1)));
		$expected = array(
			0 => Array('OrderedPage' => Array('id' => 2, 'title' => 'First Page', 	'weight' => 1, 'book_id' => 1 )),
			1 => Array('OrderedPage' => Array('id' => 5, 'title' => 'Fourth Page', 'weight' => 2, 'book_id' => 1  )),
			2 => Array('OrderedPage' => Array('id' => 1, 'title' => 'Second Page',	'weight' => 3, 'book_id' => 1  )),
			3 => Array('OrderedPage' => Array('id' => 4, 'title' => 'Third Page',	'weight' => 4, 'book_id' => 1  )),
		);     
		$this->assertEqual($result, $expected, 'Sort by title for book 1 : %s');  		
	}	
	
	function testSortBy3() {		
		$this->OrderedPage->create(array('OrderedPage'=>array('title'=>'All the men', 'book_id'=> 2)));
		$this->OrderedPage->save(null, false);
		$this->OrderedPage->sortBy('title',2);
		$result = $this->OrderedPage->find('all', array('conditions'=>array('book_id'=>2)));
		$expected = array(
			0 => Array('OrderedPage' => Array('id' => 7, 'title' => 'All the men', 'weight' => 1, 'book_id' => 2 )),
			1 => Array('OrderedPage' => Array('id' => 3, 'title' => 'Front Page',  'weight' => 2, 'book_id' => 2 )),
			2 => Array('OrderedPage' => Array('id' => 6, 'title' => 'Intro Page',  'weight' => 3, 'book_id' => 2 ))
		);     
		$this->assertEqual($result, $expected, 'Sort by title for book 2 : %s');  		
	}	
		
	function testSortBy4() {		
		$this->OrderedPage->create(array('OrderedPage'=>array('title'=>'All the men', 'book_id'=> 2)));
		$this->OrderedPage->save(null, false);
		$this->assertFalse($this->OrderedPage->sortBy('title')); // need to specify book	
	}	
		
	function testDeleteAll() {		
		$this->OrderedBook->deleteAll(array('weight >'=>1,'weight <'=>6),true,array('beforeDelete','afterDelete'));
		$result = $this->OrderedBook->find('all',array('conditions'=>array(1=>1),'fields'=>array('id','weight')));
		$expected = array(
			0 => array('OrderedBook' => array('id' => 2, 'weight' => 1)),
			1 => array('OrderedBook' => array('id' => 3, 'weight' => 2)),
		);
		$this->assertEqual($result, $expected);  	
	}
	
	function testResetWeights() {
		$this->OrderedPage->updateAll(array('weight'=>0));
		$this->OrderedPage->resetweights();
		$result = $this->OrderedPage->find('all', array('order'=>array('book_id','weight')));
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
		$this->OrderedMark->Behaviors->attach('SoftDeletable');
		$result = $this->OrderedMark->find('all', array('conditions'=>array('order_id'=>1), 'fields' => array('id','nose','deleted')));
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
		
		$this->OrderedMark->del(3);
		
        $this->OrderedMark->enableSoftDeletable('find', false); 
		$this->OrderedMark->removeFromList(3);
        $this->OrderedMark->enableSoftDeletable('find', true); 
		
		$result = $this->OrderedMark->find('first', array('conditions'=>array('id'=>3,'deleted'=>1), 'fields' => array('id','nose','deleted')));
		$expected = array(
			'OrderedMark' => array(
				'id' => 3,
				'nose' => 0,
				'deleted' => 1
			),
		);
		$this->assertEqual($result, $expected, 'Softdeleted model test : %s');
	
		$result = $this->OrderedMark->find('all', array('conditions'=>array('order_id'=>1), 'fields' => array('id','nose','deleted')));
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
		
		if ($this->OrderedMark->undelete(3)) {
			$this->assertTrue($this->OrderedMark->moveTo(3,true), 'Move successfull : %s');
		}

		$result = $this->OrderedMark->find('all', array('conditions'=>array('order_id'=>1,'deleted'=>array(0,1)), 'fields' => array('id','nose','deleted')));
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
		
		
		$this->OrderedMark->Behaviors->detach('SoftDeletable');
	}
}





























?>
