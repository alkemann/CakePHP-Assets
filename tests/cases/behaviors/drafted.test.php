<?php
/*
class DraftedBook extends CakeTestModel {
	public $name = 'DraftedBook';
	public $alias = 'Book';
	public $actsAs = array( 'Drafted' );
	

}*/

class DraftedPost extends CakeTestModel {
	public $name = 'DraftedPost';
	public $alias = 'Post';
	public $actsAs = array('Drafted' => array('fields' => array('title', 'body')));
	
	public function afterDraft($callback, $id) {
		switch ($callback){
			case 'accept':
				$this->id = $id;
				return $this->saveField('published', 1);
			break;
			default:
			
			break;
		}
	}
}

class DraftedCase extends CakeTestCase {
	public $fixtures = array(#	'app.drafted_book',
	#	'app.drafted_books_draft',
	'app.drafted_post', 'app.drafted_posts_draft');
	
	function endTest() {
		ClassRegistry::flush();
	}
	
	function testFind() {
		$Post = ClassRegistry::init('DraftedPost', 'model');
		$this->loadFixtures('DraftedPost', 'DraftedPostsDraft');
		
		$result = $Post->findById(1);
		$expected = array(
				'id' => 1, 
				'title' => 'Rock and Roll', 
				'body' => 'I love rock and roll!', 
				'published' => 1);
		$this->assertEqual($result['Post'], $expected);
		
		$result = $Post->findById(2);
		$expected = array(
				'id' => 2, 
				'title' => 'Music', 
				'body' => 'Rock and roll is cool', 
				'published' => 1);
		$this->assertEqual($result['Post'], $expected);
	}
	
	function testFindDraft() {
		$Post = ClassRegistry::init('DraftedPost', 'model');
		$this->loadFixtures('DraftedPost', 'DraftedPostsDraft');
		
		$Post->showDraft = true;
		$result = $Post->findById(2);
		$expected = array(
				'id' => 2, 
				'title' => 'Musical', 
				'body' => 'Rock and roll is awesome!', 
				'published' => 1, 
				'draft_id' => 1);
		$this->assertEqual($result['Post'], $expected);
	}
	
	function testFindAll() {
		$Post = ClassRegistry::init('DraftedPost', 'model');
		$this->loadFixtures('DraftedPost', 'DraftedPostsDraft');
		
		$result = $Post->find('all', array('fields' => array('id', 'title')));
		$expected = array(
				array('Post' => array('id' => 1, 'title' => 'Rock and Roll')), 
				array('Post' => array('id' => 2, 'title' => 'Music')), 
				array('Post' => array('id' => 3, 'title' => 'Food')));
		$this->assertEqual($result, $expected);
	}
	
	function testFindAllWithDrafts() {
		$Post = ClassRegistry::init('DraftedPost', 'model');
		$this->loadFixtures('DraftedPost', 'DraftedPostsDraft');
		
		$Post->showDraft = true;
		$result = $Post->find('all', array('fields' => array('Post.id', 'Post.title')));
		$expected = array(
				array(
						'Post' => array(
								'id' => 1, 
								'title' => 'Rock and Roll', 
								'draft_id' => NULL)), 
				array('Post' => array('id' => 2, 'title' => 'Musical', 'draft_id' => 1)), 
				array('Post' => array('id' => 3, 'title' => 'Food', 'draft_id' => NULL)));
		$this->assertEqual($result, $expected);
	}
	
	function testdrafts() {
		$Post = ClassRegistry::init('DraftedPost', 'model');
		$this->loadFixtures('DraftedPost', 'DraftedPostsDraft');
		
		$result = $Post->draft('all');
		$this->assertNoErrors();
		$expected = array(
				0 => array(
						'Post' => array('id' => 2, 'title' => 'Musical', 'draft_id' => 1)));
		$this->assertEqual($result, $expected);
	}
	
	function testfindDraftsCheck() {
		$Post = ClassRegistry::init('DraftedPost', 'model');
		$this->loadFixtures('DraftedPost', 'DraftedPostsDraft');
		
		$this->assertFalse($Post->draft('check', 1));
		$this->assertTrue($Post->draft('check', 2));
		$this->assertFalse($Post->draft('check', 3));
	}
	/***/
	
	function testSaveDirect() {
		$Post = ClassRegistry::init('DraftedPost', 'model');
		$this->loadFixtures('DraftedPost');
		
		$findDrafts = $Post->draft('all');
		
		$Post->saveDraft = false;
		$Post->create(array('title' => 'new post', 'body' => 'lorem ipsum'));
		$this->assertTrue($Post->save());
		
		$post_save_findDrafts = $Post->draft('all');
		$this->assertEqual($findDrafts, $post_save_findDrafts);
		$this->assertEqual($Post->find('count'), 4);
	}
	
	function testSave() {
		$Post = ClassRegistry::init('DraftedPost', 'model');
		$this->loadFixtures('DraftedPost');
		
		$Post->create(array(
				'title' => 'new post', 
				'body' => 'lorem ipsum', 
				'published' => false));
		$this->assertTrue($Post->save());
		
		$result = $Post->draft('all');
		$expected = array(
				array('Post' => array('id' => 2, 'title' => 'Musical', 'draft_id' => 1)), 
				array('Post' => array('id' => 4, 'title' => 'new post', 'draft_id' => 2)));
		$this->assertEqual($result, $expected);
		$this->assertEqual($Post->find('count'), 4);
	}
	
	function testSaveEdit() {
		$Post = ClassRegistry::init('DraftedPost', 'model');
		$this->loadFixtures('DraftedPost');
		
		$Post->save(array('id' => 1, 'title' => 'edit', 'body' => 'edited lorem'));
		
		$result = $Post->draft('all');
		$expected = array(
				array('Post' => array('id' => 2, 'title' => 'Musical', 'draft_id' => 1)), 
				array('Post' => array('id' => 1, 'title' => 'edit', 'draft_id' => 2)));
		$this->assertEqual($result, $expected);
		$this->assertEqual($Post->find('count'), 3);
		$result = $Post->findById(1);
		$this->assertEqual($result['Post']['title'], 'Rock and Roll');
		
		$Post->save(array('id' => 1, 'title' => 'edit again', 'body' => 'edited lorem'));
		
		$result = $Post->draft('all');
		$expected = array(
				array('Post' => array('id' => 2, 'title' => 'Musical', 'draft_id' => 1)), 
				array('Post' => array('id' => 1, 'title' => 'edit again', 'draft_id' => 2)));
		$this->assertEqual($result, $expected);
		$this->assertEqual($Post->find('count'), 3);
		$result = $Post->findById(1);
		$this->assertEqual($result['Post']['title'], 'Rock and Roll');
	}
	
	function testSaveEditDirect() {
		$Post = ClassRegistry::init('DraftedPost', 'model');
		$this->loadFixtures('DraftedPost');
		
		$Post->saveDraft = false;
		$Post->save(array('id' => 1, 'title' => 'edit', 'body' => 'edited lorem'));
		
		$this->assertFalse($Post->draft('check', 1));
		
		$result = $Post->findById(1);
		$this->assertEqual($result['Post']['title'], 'edit');
	}
	/***/
	
	function testModerartionAcceptOnEdit() {
		$Post = ClassRegistry::init('DraftedPost', 'model');
		$this->loadFixtures('DraftedPost');
		
		$this->assertFalse($Post->draft('accept', 1));
		
		$this->assertTrue($Post->draft('accept', 2));
		$this->assertEqual($Post->DraftModel->find('count'), 0);
		$result = $Post->findById(2);
		$expected = array(
				'Post' => array(
						'id' => 2, 
						'title' => 'Musical', 
						'body' => 'Rock and roll is awesome!', 
						'published' => 1));
		$this->assertEqual($result, $expected);
	}
	
	function testModerationAcceptOnCreate() {
		$Post = ClassRegistry::init('DraftedPost', 'model');
		$this->loadFixtures('DraftedPost');
		
		$Post->create(array(
				'title' => 'new post', 
				'body' => 'lorem ipsum', 
				'published' => false));
		$this->assertTrue($Post->save());
		
		$result = $Post->draft('all');
		$expected = array(
				array('Post' => array('id' => 2, 'title' => 'Musical', 'draft_id' => 1)), 
				array('Post' => array('id' => 4, 'title' => 'new post', 'draft_id' => 2)));
		
		$result = $Post->findById(4);
		$expected = array(
				'Post' => array(
						'id' => 4, 
						'title' => null, 
						'body' => null, 
						'published' => 0));
		$this->assertEqual($expected, $result);
		
		$this->assertTrue($Post->draft('accept', 4));
		
		$result = $Post->findById(4);
		$expected = array(
				'Post' => array(
						'id' => 4, 
						'title' => 'new post', 
						'body' => 'lorem ipsum', 
						'published' => 1));
		$this->assertEqual($expected, $result);
	}

/***/
}
?>
