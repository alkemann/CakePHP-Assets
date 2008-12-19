<?php
class RevisionPost extends CakeTestModel {
	var $actsAs = array('Revision' => array('limit'=>5));
	
	function beforeUndelete() {
		$this->data['RevisionPost']['created'] = '2010-01-01';
		return true;
	}
	
	function afterUndelete() {
		return true;
	}
}

class RevisionArticle extends CakeTestModel {
	var $actsAs = array('Revision' => array('ignore'=>array('title')),'Tree');
}

class RevisionUser extends CakeTestModel {
	var $actsAs = array('Revision');
}

class RevisionComment extends CakeTestModel {
	var $actsAs = array('Revision');
	
	var $hasMany = array('RevisionVote'=>array('className' => 'RevisionVote',
								'foreignKey' => 'revision_comment_id',
								'dependent' => false));
}

class RevisionVote extends CakeTestModel {
	var $actsAs = array('Revision');
}

class RevisionTag extends CakeTestModel {
	var $actsAs = array('Revision');
}

class RevisionTestCase extends CakeTestCase {
	var $fixtures = array(
			'app.revision_article', 
			'app.rev_revision_article', 
			'app.revision_post', 
			'app.rev_revision_post', 
			'app.revision_user',
			'app.revision_comment',
			'app.rev_revision_comment',
			'app.revision_vote',
			'app.rev_revision_vote',
			'app.revision_tag',
			'app.rev_revision_tag');
	
	var $RevisionPost;
	var $RevisionArticle;
	var $RevisionUser;
	var $RevisionComment;
	var $RevisionVote;
	var $RevisionTag;
	
	function startTest() {
		$this->RevisionPost = & new RevisionPost();
		$this->RevisionArticle = & new RevisionArticle();
		$this->RevisionUser = & new RevisionUser();
		$this->RevisionComment = & new RevisionComment();
		$this->RevisionVote = & new RevisionVote();
		$this->RevisionTag = & new RevisionTag();				
	}
	
	function endTest() {
		unset($this->RevisionPost);
		unset($this->RevisionArticle);
		unset($this->RevisionUser);
		unset($this->RevisionComment);
		unset($this->RevisionVote);
		unset($this->RevisionTag);
		ClassRegistry::flush();
	}
	
	function testSavePost() {
		$data = array('RevisionPost' => array('title' => 'New Post', 'content' => 'First post!'));
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->id = 4;
		$result = $this->RevisionPost->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'RevisionPost' => array(
				'id' => 4, 
				'title' => 'New Post', 
				'content' => 'First post!', 
				'version_id' => 2
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testEditPost() {
		$data = array('RevisionPost' => array( 'title' => 'New Post'));
		$this->RevisionPost->create();
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->create();
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post'));
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->id = 1;				
		$result = $this->RevisionPost->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'RevisionPost' => array(
				'id' => 1, 
				'title' => 'Edited Post', 
				'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
				'version_id' => 3
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testShadow() {
		 
		$this->RevisionPost->create();
		$data = array('RevisionPost' => array('title' => 'Non Used Post' , 'content' => 'Whatever'));
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->create();
		$data = array('RevisionPost' => array('title' => 'New Post 1' , 'content' => 'nada'));
		$this->RevisionPost->save($data);
		
		$data = array('RevisionPost' => array('id'=>5, 'title' => 'Edit Post 2'));
		$this->RevisionPost->save($data);
		
		$data = array('RevisionPost' => array( 'id'=>5,'title' => 'Edit Post 3'));
		$this->RevisionPost->save($data);
		
		$result = $this->RevisionPost->shadow('first',array('fields' => array('version_id','id','title','content')));
		$expected = array( 
			'RevisionPost' => array(
	            'version_id' => 5,
	            'id' => 5,
	            'title' => 'Edit Post 3',
	            'content' => 'nada'
	        )
		);
		$this->assertEqual($expected, $result);
		
		$result = $this->RevisionPost->shadow('first',array(
			'conditions' => array('id'=>4),
			'fields' => array('version_id','id','title','content')));
		
		$expected = array( 
			'RevisionPost' => array(
	            'version_id' => 2,
	            'id' => 4,
	            'title' => 'Non Used Post',
	            'content' => 'Whatever'
	        )
		);
		$this->assertEqual($expected, $result);
	}
	
	function testCurrentPost() {
		$this->RevisionPost->create();
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post'));
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->create();
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Re-edited Post'));
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->id = 1;				
		$result = $this->RevisionPost->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'RevisionPost' => array(
				'id' => 1, 
				'title' => 'Re-edited Post', 
				'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
				'version_id' => 3
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testRevisionsPost() {
		$this->RevisionPost->create();
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post'));
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->create();
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Re-edited Post'));
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->id = 1;				
		$result = $this->RevisionPost->revisions(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array( 
			0 => array(
				'RevisionPost' => array(
					'id' => 1, 
					'title' => 'Re-edited Post', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
					'version_id' => 3
				)
			),
			1 => array (
				'RevisionPost' => array(
					'id' => 1, 
					'title' => 'Edited Post', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
					'version_id' => 2
				),
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testDiff() {
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post 1'));
		$this->RevisionPost->save($data);
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post 2'));
		$this->RevisionPost->save($data);
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post 3'));
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->id = 1;
		$result = $this->RevisionPost->diff(null,null,array('fields'=>array('version_id','id', 'title', 'content')));
		$expected = array(
			'RevisionPost' => array(
				'version_id' => array(4,3,2),
				'id' => 1,
				'title' => array(
					'Edited Post 3',
					'Edited Post 2',
					'Edited Post 1',
				),
				'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.'
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testPrevious() {		
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post 1'));
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->id = 1;
		$this->assertNull($this->RevisionPost->previous());
				
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post 2'));
		$this->RevisionPost->save($data);
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post 3'));
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->id = 1;		
		$result = $this->RevisionPost->previous(array('fields'=>array('version_id','id','title')));
		$expected = array(
			'RevisionPost' => array(
				'version_id' => 3,
				'id' => 1,
				'title' => 'Edited Post 2'		
			)
		); 
		$this->assertEqual($expected, $result);
	}	
		
	function testUndo() {		
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post 1'));
		$this->RevisionPost->save($data);
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post 2'));
		$this->RevisionPost->save($data);
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post 3'));
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->id = 1;
		$success = $this->RevisionPost->undo();
		$this->assertTrue($success);
		
		$result = $this->RevisionPost->find('first', array('fields'=>array('id', 'title', 'content')));
		$expected = array(
			'RevisionPost' => array(
				'id' => 1,
				'title' =>'Edited Post 2',
				'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.'
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testRevertTo() {
		
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post 1'));
		$this->RevisionPost->save($data);
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post 2'));
		$this->RevisionPost->save($data);
		$data = array('RevisionPost' => array('id'=>1, 'title' => 'Edited Post 3'));
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->id = 1;
		$success = $this->RevisionPost->RevertTo(3);
		$this->assertTrue($success);
		
		$result = $this->RevisionPost->find('first', array('fields'=>array('id', 'title', 'content')));
		
		$expected = array(
			'RevisionPost' => array(
				'id' => 1,
				'title' => 'Edited Post 2',
				'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.'
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testLimit() {
		
		$data = array('RevisionPost' => array('id'=>2, 'title' => 'Edited Post 1'));
		$this->RevisionPost->save($data);
		$data = array('RevisionPost' => array('id'=>2, 'title' => 'Edited Post 2'));
		$this->RevisionPost->save($data);
		$data = array('RevisionPost' => array('id'=>2, 'title' => 'Edited Post 3'));
		$this->RevisionPost->save($data);
		$data = array('RevisionPost' => array('id'=>2, 'title' => 'Edited Post 4'));
		$this->RevisionPost->save($data);
		$data = array('RevisionPost' => array('id'=>2, 'title' => 'Edited Post 5'));
		$this->RevisionPost->save($data);
		$data = array('RevisionPost' => array('id'=>2, 'title' => 'Edited Post 6'));
		$this->RevisionPost->save($data);
		$data = array('RevisionPost' => array('id'=>3, 'title' => 'Edited Post 6'));
		$this->RevisionPost->save($data);
		
		$this->RevisionPost->id = 2;
		
		$result = $this->RevisionPost->revisions(array('fields' => array('id', 'title', 'content', 'version_id')));
		
		$expected = array( 
			0 => array(
				'RevisionPost' => array(
					'id' => 2, 
					'title' => 'Edited Post 6', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 7
				)
			),
			1 => array (
				'RevisionPost' => array(
					'id' => 2, 
					'title' => 'Edited Post 5', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 6
				),
			),
			2 => array(
				'RevisionPost' => array(
					'id' => 2, 
					'title' => 'Edited Post 4', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 5
				)
			),
			3 => array (
				'RevisionPost' => array(
					'id' => 2, 
					'title' => 'Edited Post 3', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 4
				),
			),
			4 => array(
				'RevisionPost' => array(
					'id' => 2, 
					'title' => 'Edited Post 2', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 3
				)
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testTree() {
		
		$this->RevisionArticle->create();
		$data = array('RevisionArticle' => array('id'=>3, 'content' => 'Re-edited Post'));
		$this->RevisionArticle->save($data);
		
		$this->RevisionArticle->moveUp(3);
		
		$this->RevisionArticle->id = 3;
		
		$result = $this->RevisionArticle->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'RevisionArticle' => array(
				'id' => 3, 
				'title' => 'Lorem ipsum', 
				'content' => 'Re-edited Post', 
				'version_id' => 1
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testIgnore() {
		
		$data = array('RevisionArticle' => array('id'=>3, 'title' =>'New title', 'content' => 'Edited'));
		$this->RevisionArticle->save($data);
		$data = array('RevisionArticle' => array('id'=>3, 'title' => 'Re-edited title'));
		$this->RevisionArticle->save($data);
				
		$this->RevisionArticle->id = 3;		
		$result = $this->RevisionArticle->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'RevisionArticle' => array(
				'id' => 3, 
				'title' => 'New title', 
				'content' => 'Edited', 
				'version_id' => 1
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testWithoutShadowTable() {
		$data = array('RevisionUser' => array('id'=>1, 'name' =>'New name'));
		$success = $this->RevisionUser->save($data);
		$this->assertTrue($success);
	}
	
	function testRevertToDate() {
		$data = array('RevisionPost' => array('id'=>3, 'title' => 'Edited Post 6'));
		$this->RevisionPost->save($data);
		
		$this->assertTrue($this->RevisionPost->revertToDate('2008-12-08'));
		$result = $this->RevisionPost->newest(array('fields' => array('id', 'title', 'content', 'version_id')));

		$expected = array(
			'RevisionPost' => array(
					'id' => 3, 
					'title' => 'Lorem ipsum dolor sit amet', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.',
					'version_id' => 3
			)
		);
		
		$this->assertEqual($expected, $result);
	}
	
	function testCascade() {
		
		$original_comments = $this->RevisionComment->find('all');
		
		$data = array('RevisionVote' => array('id'=>3, 'title' => 'Edited Vote'));
		$this->RevisionVote->save($data);
		
		$this->assertTrue($this->RevisionVote->revertToDate('2008-12-08'));
		$this->RevisionVote->id = 3;
		$result = $this->RevisionVote->newest(array('fields' => array('id', 'title', 'content', 'version_id')));

		$expected = array(
			'RevisionVote' => array(
					'id' => 3, 
					'title' => 'Stuff', 
					'content' => 'Lorem ipsum dolor sit.',
					'version_id' => 5
			)
		);
		
		$this->assertEqual($expected, $result);
		
		$data = array('RevisionComment' => array('id'=>2, 'title' => 'Edited Comment'));
		$this->RevisionComment->save($data);
		
		$this->assertTrue($this->RevisionComment->revertToDate('2008-12-08'));
		
		$reverted_comments = $this->RevisionComment->find('all');
		
		$this->assertEqual($original_comments, $reverted_comments);
	}
	
	function testUndelete() {
		
		$this->RevisionPost->id = 3;
		$result = $this->RevisionPost->undelete();		
		$this->assertFalse($result);
		
		$this->RevisionPost->delete(3);
		
		$result = $this->RevisionPost->find('first',array('conditions'=>array('id'=>3)));			
		$this->assertFalse($result);
		
		$this->RevisionPost->id = 3;
		$this->RevisionPost->undelete();
		$result = $this->RevisionPost->find('first',array('conditions'=>array('id'=>3),'fields' => array('id', 'title', 'content')));
		
		$expected = array(
			'RevisionPost' => array(
					'id' => 3,
					'title' => 'Lorem ipsum dolor sit amet', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.'
			)
		);
		$this->assertEqual($expected, $result);
		
	}
	
	function testCreateRevision() {
		
		$data = array('RevisionArticle' => array('id'=>3, 'title' =>'New title', 'content' => 'Edited'));
		$this->RevisionArticle->save($data);
		$data = array('RevisionArticle' => array('id'=>3, 'title' => 'Re-edited title'));
		$this->RevisionArticle->save($data);
				
		$this->RevisionArticle->id = 3;		
		$result = $this->RevisionArticle->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'RevisionArticle' => array(
				'id' => 3, 
				'title' => 'New title', 
				'content' => 'Edited', 
				'version_id' => 1
			)
		);
		$this->assertEqual($expected, $result);
		
		$this->RevisionArticle->id = 3;	
		$this->assertTrue($this->RevisionArticle->createRevision());
		$result = $this->RevisionArticle->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'RevisionArticle' => array(
				'id' => 3, 
				'title' => 'Re-edited title', 
				'content' => 'Edited', 
				'version_id' => 2
			)
		);
		$this->assertEqual($expected, $result);
		
	}
	
	function testUndeleteCallbacks() {
		
		$this->RevisionPost->id = 3;
		$result = $this->RevisionPost->undelete();		
		$this->assertFalse($result);
		
		$this->RevisionPost->delete(3);
		
		$result = $this->RevisionPost->find('first',array('conditions'=>array('id'=>3)));			
		$this->assertFalse($result);
		
		$this->RevisionPost->id = 3;
		$this->assertTrue($this->RevisionPost->undelete());						
		
		$result = $this->RevisionPost->find('first',array('conditions'=>array('id'=>3)));
		
		$expected = array(
			'RevisionPost' => array(
					'id' => 3,
					'title' => 'Lorem ipsum dolor sit amet', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
					'modified' => '2008-12-08 11:38:53', 
					'created' => '2010-01-01',
			)
		);
		
		$this->assertEqual($expected, $result);
		
	}
	
	function testInitializeRevisions() {
		
		$this->assertTrue($this->RevisionArticle->initializeRevisions());
		$this->assertFalse($this->RevisionComment->initializeRevisions());
		$this->assertFalse($this->RevisionPost->initializeRevisions());
		$this->assertFalse($this->RevisionVote->initializeRevisions());
		$this->assertTrue($this->RevisionTag->initializeRevisions());
	}
	
}
?>
