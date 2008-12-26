<?php
class RevisionPost extends CakeTestModel {
    var $name = 'RevisionPost';
    var $alias = 'Post';
	var $actsAs = array('Revision' => array('limit'=>5));
	
	function beforeUndelete() {
		return true;
	}
	
	function afterUndelete() {
		return true;
	}
}

class RevisionArticle extends CakeTestModel {
    var $name = 'RevisionArticle';
    var $alias = 'Article';
	var $actsAs = array('Revision' => array('ignore'=>array('title')),'Tree');
}

class RevisionUser extends CakeTestModel {
    var $name = 'RevisionUser';
    var $alias = 'User';
	var $actsAs = array('Revision');
}

class RevisionComment extends CakeTestModel {
    var $name = 'RevisionComment';
    var $alias = 'Comment';
	var $actsAs = array('Revision');
	
	var $hasMany = array('Vote'=>array('className' => 'RevisionVote',
								'foreignKey' => 'revision_comment_id',
								'dependent' => true));
}

class RevisionVote extends CakeTestModel {
    var $name = 'RevisionVote';
    var $alias = 'Vote';
	var $actsAs = array('Revision');
}

class RevisionTag extends CakeTestModel {
    var $name = 'RevisionTag';
    var $alias = 'Tag';
	var $actsAs = array('Revision');
}

class RevisionTestCase extends CakeTestCase {
	var $fixtures = array(
			'app.revision_article', 
			'app.revision_articles_rev', 
			'app.revision_post', 
			'app.revision_posts_rev', 
			'app.revision_user',
			'app.revision_comment',
			'app.revision_comments_rev',
			'app.revision_vote',
			'app.revision_votes_rev',
			'app.revision_tag',
			'app.revision_tags_rev');
	
	var $Post;
	var $Article;
	var $User;
	var $Comment;
	var $Vote;
	var $Tag;
	
	function startTest() {
		$this->Post = & new RevisionPost();
        $this->Article = & new RevisionArticle();
		$this->User = & new RevisionUser();
		$this->Comment = & new RevisionComment();
		$this->Tag = & new RevisionTag();				
	}
	
	function endTest() {
		unset($this->Post);
		unset($this->Article);
		unset($this->User);
		unset($this->Comment);
		unset($this->Tag);
		ClassRegistry::flush();
	}
	
	function testSavePost() {
		$data = array('Post' => array('title' => 'New Post', 'content' => 'First post!'));
		$this->Post->save($data);
		$this->Post->id = 4;
		$result = $this->Post->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'Post' => array(
				'id' => 4, 
				'title' => 'New Post', 
				'content' => 'First post!', 
				'version_id' => 2
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testSaveWithoutChange() {	
		$this->Post->id = 1;
        $this->Post->createRevision();
	
		$this->Post->id = 1;
		$count = $this->Post->shadow('count', array('conditions'=>array('id'=>1)));
		$this->assertEqual($count,1);
		
		$this->Post->id = 1;
		$data = $this->Post->read();
		$this->Post->save($data);
		
		$this->Post->id = 1;
		$count = $this->Post->shadow('count', array('conditions'=>array('id'=>1)));
		$this->assertEqual($count,1);
	}
	
	function testEditPost() {
		$data = array('Post' => array( 'title' => 'New Post'));
		$this->Post->create();
		$this->Post->save($data);
		$this->Post->create();
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post'));
		$this->Post->save($data);
		
		$this->Post->id = 1;				
		$result = $this->Post->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'Post' => array(
				'id' => 1, 
				'title' => 'Edited Post', 
				'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
				'version_id' => 3
			)
		);
		$this->assertEqual($expected, $result);
	}

	function testShadow() {
		 
		$this->Post->create();
		$data = array('Post' => array('title' => 'Non Used Post' , 'content' => 'Whatever'));
		$this->Post->save($data);
		
		$this->Post->create();
		$data = array('Post' => array('title' => 'New Post 1' , 'content' => 'nada'));
		$this->Post->save($data);
		
		$data = array('Post' => array('id'=>5, 'title' => 'Edit Post 2'));
		$this->Post->save($data);
		
		$data = array('Post' => array( 'id'=>5,'title' => 'Edit Post 3'));
		$this->Post->save($data);
		
		$result = $this->Post->shadow('first',array('fields' => array('version_id','id','title','content')));
		$expected = array( 
			'Post' => array(
	            'version_id' => 5,
	            'id' => 5,
	            'title' => 'Edit Post 3',
	            'content' => 'nada'
	        )
		);
		$this->assertEqual($expected, $result);
		
		$result = $this->Post->shadow('first',array(
			'conditions' => array('id'=>4),
			'fields' => array('version_id','id','title','content')));
		
		$expected = array( 
			'Post' => array(
	            'version_id' => 2,
	            'id' => 4,
	            'title' => 'Non Used Post',
	            'content' => 'Whatever'
	        )
		);
		$this->assertEqual($expected, $result);
	}
	
	function testCurrentPost() {
		$this->Post->create();
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post'));
		$this->Post->save($data);
		
		$this->Post->create();
		$data = array('Post' => array('id'=>1, 'title' => 'Re-edited Post'));
		$this->Post->save($data);
		
		$this->Post->id = 1;				
		$result = $this->Post->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'Post' => array(
				'id' => 1, 
				'title' => 'Re-edited Post', 
				'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
				'version_id' => 3
			)
		);
		$this->assertEqual($expected, $result);
	}

	function testRevisionsPost() {
		$this->Post->create();
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post'));
		$this->Post->save($data);
		
		$this->Post->create();
		$data = array('Post' => array('id'=>1, 'title' => 'Re-edited Post'));
		$this->Post->save($data);
		
		$this->Post->id = 1;				
		$result = $this->Post->revisions(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array( 
			0 => array(
				'Post' => array(
					'id' => 1, 
					'title' => 'Re-edited Post', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
					'version_id' => 3
				)
			),
			1 => array (
				'Post' => array(
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
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 1'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 2'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 3'));
		$this->Post->save($data);
		
		$this->Post->id = 1;
		$result = $this->Post->diff(null,null,array('fields'=>array('version_id','id', 'title', 'content')));
		$expected = array(
			'Post' => array(
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
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 1'));
		$this->Post->save($data);
		
		$this->Post->id = 1;
		$this->assertNull($this->Post->previous());
				
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 2'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 3'));
		$this->Post->save($data);
		
		$this->Post->id = 1;		
		$result = $this->Post->previous(array('fields'=>array('version_id','id','title')));
		$expected = array(
			'Post' => array(
				'version_id' => 3,
				'id' => 1,
				'title' => 'Edited Post 2'		
			)
		); 
		$this->assertEqual($expected, $result);
	}	
		
	function testUndo() {		
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 1'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 2'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 3'));
		$this->Post->save($data);
		
		$this->Post->id = 1;
		$success = $this->Post->undo();
		$this->assertTrue($success);
		
		$result = $this->Post->find('first', array('fields'=>array('id', 'title', 'content')));
		$expected = array(
			'Post' => array(
				'id' => 1,
				'title' =>'Edited Post 2',
				'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.'
			)
		);
		$this->assertEqual($expected, $result);
	}
		
	function testRevertTo() {
		
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 1'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 2'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 3'));
		$this->Post->save($data);
		
		$this->Post->id = 1;
		$success = $this->Post->RevertTo(3);
		$this->assertTrue($success);
		
		$result = $this->Post->find('first', array('fields'=>array('id', 'title', 'content')));
		
		$expected = array(
			'Post' => array(
				'id' => 1,
				'title' => 'Edited Post 2',
				'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.'
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testLimit() {
		
		$data = array('Post' => array('id'=>2, 'title' => 'Edited Post 1'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>2, 'title' => 'Edited Post 2'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>2, 'title' => 'Edited Post 3'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>2, 'title' => 'Edited Post 4'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>2, 'title' => 'Edited Post 5'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>2, 'title' => 'Edited Post 6'));
		$this->Post->save($data);
		
		
		$data = array('Post' => array('id'=>2, 'title' => 'Edited Post 6'));
		$this->Post->save($data);
		
		$this->Post->id = 2;
		
		$result = $this->Post->revisions(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array( 
			0 => array(
				'Post' => array(
					'id' => 2, 
					'title' => 'Edited Post 6', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 7
				)
			),
			1 => array (
				'Post' => array(
					'id' => 2, 
					'title' => 'Edited Post 5', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 6
				),
			),
			2 => array(
				'Post' => array(
					'id' => 2, 
					'title' => 'Edited Post 4', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 5
				)
			),
			3 => array (
				'Post' => array(
					'id' => 2, 
					'title' => 'Edited Post 3', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 4
				),
			),
			4 => array(
				'Post' => array(
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
		
		$this->Article->create();
		$data = array('Article' => array('id'=>3, 'content' => 'Re-edited Post'));
		$this->Article->save($data);
		
		$this->Article->moveUp(3);
		
		$this->Article->id = 3;
		
		$result = $this->Article->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'Article' => array(
				'id' => 3, 
				'title' => 'Lorem ipsum', 
				'content' => 'Re-edited Post', 
				'version_id' => 1
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testIgnore() {
		
		$data = array('Article' => array('id'=>3, 'title' =>'New title', 'content' => 'Edited'));
		$this->Article->save($data);
		$data = array('Article' => array('id'=>3, 'title' => 'Re-edited title'));
		$this->Article->save($data);
				
		$this->Article->id = 3;		
		$result = $this->Article->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'Article' => array(
				'id' => 3, 
				'title' => 'New title', 
				'content' => 'Edited', 
				'version_id' => 1
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testWithoutShadowTable() {
		$data = array('User' => array('id'=>1, 'name' =>'New name'));
		$this->assertNoErrors();
		$success = $this->User->save($data);
		$this->assertTrue($success);
	}
	
	function testRevertToDate() {
		$data = array('Post' => array('id'=>3, 'title' => 'Edited Post 6'));
		$this->Post->save($data);
		
		$this->assertTrue($this->Post->revertToDate('2008-12-08'));
		$result = $this->Post->newest(array('fields' => array('id', 'title', 'content', 'version_id')));

		$expected = array(
			'Post' => array(
					'id' => 3, 
					'title' => 'Lorem ipsum dolor sit amet', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.',
					'version_id' => 3
			)
		);
		
		$this->assertEqual($expected, $result);
	}
	
	function testCascade() {
		
		$original_comments = $this->Comment->find('all');
		
		$data = array('Vote' => array('id'=>3, 'title' => 'Edited Vote','revision_comment_id'=>1));
		$this->Comment->Vote->save($data);
		
		$this->assertTrue($this->Comment->Vote->revertToDate('2008-12-08'));
		$this->Comment->Vote->id = 3;
		$result = $this->Comment->Vote->newest(array('fields' => array('id', 'title', 'content', 'version_id')));

		$expected = array(
			'Vote' => array(
					'id' => 3, 
					'title' => 'Stuff', 
					'content' => 'Lorem ipsum dolor sit.',
					'version_id' => 5
			)
		);
		
		$this->assertEqual($expected, $result);
		
		$data = array('Comment' => array('id'=>2, 'title' => 'Edited Comment'));
		$this->Comment->save($data);
		
		$this->assertTrue($this->Comment->revertToDate('2008-12-08'));
		
		$reverted_comments = $this->Comment->find('all');
		
		$this->assertEqual($original_comments, $reverted_comments);
	}
	
	function testUndelete() {
		
		$this->Post->id = 3;
		$result = $this->Post->undelete();		
		$this->assertFalse($result);
		
		$this->Post->delete(3);
		
		$result = $this->Post->find('first',array('conditions'=>array('id'=>3)));			
		$this->assertFalse($result);
		
		$this->Post->id = 3;
		$this->Post->undelete();
		$result = $this->Post->find('first',array('conditions'=>array('id'=>3),'fields' => array('id', 'title', 'content')));
		
		$expected = array(
			'Post' => array(
					'id' => 3,
					'title' => 'Lorem ipsum dolor sit amet', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.'
			)
		);
		$this->assertEqual($expected, $result);
		
	}
	
	function testCreateRevision() {
		
		$data = array('Article' => array('id'=>3, 'title' =>'New title', 'content' => 'Edited'));
		$this->Article->save($data);
		$data = array('Article' => array('id'=>3, 'title' => 'Re-edited title'));
		$this->Article->save($data);
				
		$this->Article->id = 3;		
		$result = $this->Article->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'Article' => array(
				'id' => 3, 
				'title' => 'New title', 
				'content' => 'Edited', 
				'version_id' => 1
			)
		);
		$this->assertEqual($expected, $result);
		
		$this->Article->id = 3;	
		$this->assertTrue($this->Article->createRevision());
		$result = $this->Article->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'Article' => array(
				'id' => 3, 
				'title' => 'Re-edited title', 
				'content' => 'Edited', 
				'version_id' => 2
			)
		);
		$this->assertEqual($expected, $result);
		
	}
	
	function testUndeleteCallbacks() {
		
		$this->Post->id = 3;
		$result = $this->Post->undelete();		
		$this->assertFalse($result);
		
		$this->Post->delete(3);
		
		$result = $this->Post->find('first',array('conditions'=>array('id'=>3)));			
		$this->assertFalse($result);
		
		$this->Post->id = 3;
		$this->assertTrue($this->Post->undelete());						
		
		$result = $this->Post->find('first',array('conditions'=>array('id'=>3)));
		
		$expected = array(
			'Post' => array(
					'id' => 3,
					'title' => 'Lorem ipsum dolor sit amet', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.',
			)
		);
		
		$this->assertEqual($expected, $result);
		
	}
	
	function testInitializeRevisions() {
		
		$this->assertTrue($this->Article->initializeRevisions());
		$this->assertFalse($this->Comment->initializeRevisions());
		$this->assertFalse($this->Post->initializeRevisions());
		$this->assertFalse($this->Comment->Vote->initializeRevisions());
		$this->assertTrue($this->Tag->initializeRevisions());
	}
	
}
?>
