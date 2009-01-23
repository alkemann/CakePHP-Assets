<?php
class RevisionPost extends CakeTestModel {
    public $name = 'RevisionPost';
    public $alias = 'Post';
	public $actsAs = array('Revision' => array('limit'=>5));
	
	function beforeUndelete() {
		$this->beforeUndelete = true;
		return true;
	}
	
	function afterUndelete() {
		$this->afterUndelete = true;
		return true;
	}
}

class RevisionArticle extends CakeTestModel {
    public $name = 'RevisionArticle';
    public $alias = 'Article';
	public $actsAs = array(
		'Tree',
		'Revision' => array('ignore'=>array('title')));
	
	/**
	 * Example of using this callback to undelete children
	 * of a deleted node.
	 */
	function afterUndelete() {
		$former_children = $this->ShadowModel->find('list', array(
			'conditions' => array(
				'parent_id' => $this->id
			),
			'distinct' => true,
			'order' => 'version_created DESC, version_id DESC' 
		));
		foreach (array_keys($former_children) as $cid) {
			$this->id = $cid;
			$this->undelete();
		}
	}
}

class RevisionUser extends CakeTestModel {
    public $name = 'RevisionUser';
    public $alias = 'User';
	public $actsAs = array('Revision');
}

class RevisionComment extends CakeTestModel {
    public $name = 'RevisionComment';
    public $alias = 'Comment';
	public $actsAs = array('Containable','Revision');
	
	public $hasMany = array('Vote'=>array('className' => 'RevisionVote',
								'foreignKey' => 'revision_comment_id',
								'dependent' => true));
}

class RevisionVote extends CakeTestModel {
    public $name = 'RevisionVote';
    public $alias = 'Vote';
	public $actsAs = array('Revision');
}

class RevisionTag extends CakeTestModel {
    public $name = 'RevisionTag';
    public $alias = 'Tag';
	public $actsAs = array('Revision');
	public $hasAndBelongsToMany = array(
		'Comment' => array(
			'className' => 'RevisionComment'
		)
	);
}

class CommentsTag extends CakeTestModel {
    public $name = 'CommentsTag';
    public $useTable = 'revision_comments_revision_tags';
	public $actsAs = array('Revision');
}

class RevisionTestCase extends CakeTestCase {
	
	public $autoFixtures = false;
	public $fixtures = array(
		'app.revision_article', 
		'app.revision_articles_rev', 
		'app.revision_post', 
		'app.revision_posts_rev', 
		'app.revision_user',
		'app.revision_comment',
		'app.revision_comments_rev',
		'app.revision_vote',
		'app.revision_votes_rev',
		'app.revision_comments_revision_tag',
		'app.revision_comments_revision_tags_rev',
		'app.revision_tag',
		'app.revision_tags_rev'
	);
	
	public $Post;
	public $Article;
	public $User;
	public $Comment;
	public $Vote;
	public $Tag;
	
	function startTest() {		
		$this->Post = new RevisionPost();
		$this->Article = new RevisionArticle();
		$this->User = new RevisionUser();
		$this->Comment = new RevisionComment();				
	}
	
	function endTest() {
		unset($this->Post);
		unset($this->Article);
		unset($this->User);
		unset($this->Comment);
		ClassRegistry::flush();
	}

	function testSavePost() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
			
		$data = array('Post' => array('title' => 'New Post', 'content' => 'First post!'));
		$this->Post->save($data);
		$this->Post->id = 4;
		$result = $this->Post->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'Post' => array(
				'id' => 4, 
				'title' => 'New Post', 
				'content' => 'First post!', 
				'version_id' => 4
			)
		);
		$this->assertEqual($expected, $result);
	}

	function testSaveWithoutChange() {	
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
		$this->Post->id = 1;
        $this->assertTrue($this->Post->createRevision());
	
		$this->Post->id = 1;
		$count = $this->Post->ShadowModel->find('count', array('conditions'=>array('id'=>1)));
		$this->assertEqual($count,2);
		
		$this->Post->id = 1;
		$data = $this->Post->read();
		$this->Post->save($data);
		
		$this->Post->id = 1;
		$count = $this->Post->ShadowModel->find('count', array('conditions'=>array('id'=>1)));
		$this->assertEqual($count,2);
	}
	
	function testEditPost() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
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
				'version_id' => 5
			)
		);
		$this->assertEqual($expected, $result);
	}

	function testShadow() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		 
		$this->Post->create(array('Post' => array('title' => 'Non Used Post' , 'content' => 'Whatever')));
		$this->Post->save();
		$post_id = $this->Post->id;
		
		$this->Post->create(array('Post' => array('title' => 'New Post 1' , 'content' => 'nada')));
		$this->Post->save();
		
		$this->Post->save(array('Post' => array('id'=>5, 'title' => 'Edit Post 2')));
		
		$this->Post->save(array('Post' => array('id'=>5, 'title' => 'Edit Post 3')));
		
		$result = $this->Post->ShadowModel->find('first',array('fields' => array('version_id','id','title','content')));
		$expected = array( 
			'Post' => array(
	            'version_id' => 7,
	            'id' => 5,
	            'title' => 'Edit Post 3',
	            'content' => 'nada'
	        )
		);
		$this->assertEqual($expected, $result);
		
		$this->Post->id = $post_id;
		$result = $this->Post->newest();
		$this->assertEqual($result['Post']['title'],'Non Used Post');
		$this->assertEqual($result['Post']['version_id'],4);
		
		$result = $this->Post->ShadowModel->find('first',array(
			'conditions' => array('version_id'=>4),
			'fields' => array('version_id','id','title','content')));
		
		$expected = array( 
			'Post' => array(
	            'version_id' => 4,
	            'id' => 4,
	            'title' => 'Non Used Post',
	            'content' => 'Whatever'
	        )
		);
		$this->assertEqual($expected, $result);
	}

	function testCurrentPost() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
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
				'version_id' => 5
			)
		);
		$this->assertEqual($expected, $result);
	}

	function testRevisionsPost() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
		$this->Post->create();
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post'));
		$this->Post->save($data);
		
		$this->Post->create();
		$data = array('Post' => array('id'=>1, 'title' => 'Re-edited Post'));
		$this->Post->save($data);
		$this->Post->create();
		$data = array('Post' => array('id'=>1, 'title' => 'Newest edited Post'));
		$this->Post->save($data);
		
		$this->Post->id = 1;				
		$result = $this->Post->revisions(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array( 
			0 => array(
				'Post' => array(
					'id' => 1, 
					'title' => 'Re-edited Post', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
					'version_id' => 5
				)
			),
			1 => array (
				'Post' => array(
					'id' => 1, 
					'title' => 'Edited Post', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
					'version_id' => 4
				),
			),
			2 => array (
				'Post' => array(
					'id' => 1, 
					'title' => 'Lorem ipsum dolor sit amet', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
					'version_id' => 1
				),
			)
		);
		$this->assertEqual($expected, $result);
		
		$this->Post->id = 1;				
		$result = $this->Post->revisions(array('fields' => array('id', 'title', 'content', 'version_id')),true);
		$expected = array( 
			0 => array(
				'Post' => array(
					'id' => 1, 
					'title' => 'Newest edited Post', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
					'version_id' => 6
				)
			),
			1 => array(
				'Post' => array(
					'id' => 1, 
					'title' => 'Re-edited Post', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
					'version_id' => 5
				)
			),
			2 => array (
				'Post' => array(
					'id' => 1, 
					'title' => 'Edited Post', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
					'version_id' => 4
				),
			),
			3 => array (
				'Post' => array(
					'id' => 1, 
					'title' => 'Lorem ipsum dolor sit amet', 
					'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.', 
					'version_id' => 1
				),
			)
		);
		$this->assertEqual($expected, $result);
	}
	
	function testDiff() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
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
				'version_id' => array(6,5,4,1),
				'id' => 1,
				'title' => array(
					'Edited Post 3',
					'Edited Post 2',
					'Edited Post 1',
					'Lorem ipsum dolor sit amet'
				),
				'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.'
			)
		);
		$this->assertEqual($expected, $result);
	}

	function testPrevious() {		
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
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
				'version_id' => 4,
				'id' => 1,
				'title' => 'Edited Post 2'		
			)
		); 
		$this->assertEqual($expected, $result);
	}	
		
	function testUndoEdit() {				
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
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

	function testUndoCreate() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
		$this->Post->create(array('Post' => array('title' => 'New post','content' => 'asd')));
		$this->Post->save();
		
		$result = $this->Post->read();
		$this->assertEqual($result['Post']['title'],'New post');
		$id = $this->Post->id;
		
		$this->Post->undo();
		
		$this->Post->id = $id;
		$this->assertFalse($this->Post->read());
		
		$this->Post->undelete();
		$result = $this->Post->read();
		$this->assertEqual($result['Post']['title'],'New post');		
		
	}
	
	function testRevertTo() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
		$this->Post->save(array('Post' => array('id'=>1, 'title' => 'Edited Post 1')));
		$this->Post->save(array('Post' => array('id'=>1, 'title' => 'Edited Post 2')));
		$this->Post->save(array('Post' => array('id'=>1, 'title' => 'Edited Post 3')));
		
		$this->Post->id = 1;
		$result = $this->Post->previous();
		$this->assertEqual($result['Post']['title'],'Edited Post 2');
		
		$version_id = $result['Post']['version_id'];
		
		$this->assertTrue(
			$this->Post->RevertTo($version_id)
		);
		
		$result = $this->Post->find('first', array('fields'=>array('id', 'title', 'content')));
		$this->assertEqual($result['Post']['title'],'Edited Post 2');
	}
	
	function testLimit() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
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
		
		$result = $this->Post->revisions(array('fields' => array('id', 'title', 'content', 'version_id')),true);
		$expected = array( 
			0 => array(
				'Post' => array(
					'id' => 2, 
					'title' => 'Edited Post 6', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 9
				)
			),
			1 => array (
				'Post' => array(
					'id' => 2, 
					'title' => 'Edited Post 5', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 8
				),
			),
			2 => array(
				'Post' => array(
					'id' => 2, 
					'title' => 'Edited Post 4', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 7
				)
			),
			3 => array (
				'Post' => array(
					'id' => 2, 
					'title' => 'Edited Post 3', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 6
				),
			),
			4 => array(
				'Post' => array(
					'id' => 2, 
					'title' => 'Edited Post 2', 
					'content' => 'Lorem ipsum dolor sit.', 
					'version_id' => 5
				)
			)
		);
		$this->assertEqual($expected, $result);
	}

	function testTree() {
		$this->loadFixtures('RevisionArticle','RevisionArticlesRev');
		$this->Article->initializeRevisions();
		
		$this->Article->save(array('Article' => array('id'=>3, 'content' => 'Re-edited Article')));
		$this->assertNoErrors('Save() with tree problem : %s');
		
		$this->Article->moveUp(3);
		$this->assertNoErrors('moveUp() with tree problem : %s');
		
		$this->Article->id = 3;		
		$result = $this->Article->newest(array('fields' => array('id', 'version_id')));
		$this->assertEqual($result['Article']['version_id'], 4);
		
		$this->Article->create(array('title'=>'midten','content'=>'stuff','parent_id'=>2));
		$this->Article->save();
		$this->assertNoErrors('Save() with tree problem : %s');
		
		$result = $this->Article->find('all', array('fields'=>array('id','lft','rght','parent_id')));
		$expected = array('id'=>1, 'lft'=>1, 'rght'=>8, 'parent_id'=>null);
		$this->assertEqual($result[0]['Article'],$expected);
		$expected = array('id'=>2, 'lft'=>4, 'rght'=>7, 'parent_id'=>1);
		$this->assertEqual($result[1]['Article'],$expected);
		$expected = array('id'=>3, 'lft'=>2, 'rght'=>3, 'parent_id'=>1);
		$this->assertEqual($result[2]['Article'],$expected);
		$expected = array('id'=>4, 'lft'=>5, 'rght'=>6, 'parent_id'=>2);
		$this->assertEqual($result[3]['Article'],$expected);		
	}	
	
	function testIgnore() {
		$this->loadFixtures('RevisionArticle','RevisionArticlesRev');
		
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
		$this->loadFixtures('RevisionUser');
		$data = array('User' => array('id'=>1, 'name' =>'New name'));
		$success = $this->User->save($data);
		$this->assertNoErrors();
		$this->assertTrue($success);
	}
	
	
	function testRevertToDate() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
		$data = array('Post' => array('id'=>3, 'title' => 'Edited Post 6'));
		$this->Post->save($data);
		
		$this->assertTrue($this->Post->revertToDate(date('Y-m-d H:i:s',strtotime('yesterday'))));
		$result = $this->Post->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'Post' => array(
					'id' => 3, 
					'title' => 'Post 3', 
					'content' => 'Lorem ipsum dolor sit.',
					'version_id' => 5
			)
		);
		$this->assertEqual($expected, $result);
	}

	function testCascade() {
		$this->loadFixtures('RevisionComment','RevisionCommentsRev','RevisionVote','RevisionVotesRev');
		
		$original_comments = $this->Comment->find('all');
		
		$data = array('Vote' => array('id'=>3, 'title' => 'Edited Vote','revision_comment_id'=>1));
		$this->Comment->Vote->save($data);
		
		$this->assertTrue($this->Comment->Vote->revertToDate('2008-12-09'));
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
		
		$this->assertTrue($this->Comment->revertToDate('2008-12-09'));
		
		$reverted_comments = $this->Comment->find('all');
		
		$this->assertEqual($original_comments, $reverted_comments);
	}

	function testCreateRevision() {
		$this->loadFixtures('RevisionArticle','RevisionArticlesRev');
		
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

	function testUndelete() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
		$this->Post->id = 3;
		$result = $this->Post->undelete();		
		$this->assertFalse($result);
		
		$this->Post->delete(3);
		
		$result = $this->Post->find('count',array('conditions'=>array('id'=>3)));	
		$this->assertEqual($result,0);
		
		$this->Post->id = 3;
		$this->Post->undelete();

		$result = $this->Post->find('first',array('conditions'=>array('id'=>3),'fields' => array('id', 'title', 'content')));
		
		$expected = array(
			'Post' => array(
					'id' => 3,
					'title' => 'Post 3', 
					'content' => 'Lorem ipsum dolor sit.'
			)
		);
		$this->assertEqual($expected, $result);
		
	}

	function testUndeleteCallbacks() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
		$this->Post->id = 3;
		$result = $this->Post->undelete();		
		$this->assertFalse($result);
		
		$this->Post->delete(3);
		
		$result = $this->Post->find('first',array('conditions'=>array('id'=>3)));			
		$this->assertFalse($result);
		
		$this->Post->id = 3;
		$this->assertTrue($this->Post->undelete());				
		$this->assertTrue($this->Post->beforeUndelete);
		$this->assertTrue($this->Post->afterUndelete);	
		
		$result = $this->Post->find('first',array('conditions'=>array('id'=>3)));
		
		$expected = array(
			'Post' => array(
					'id' => 3,
					'title' => 'Post 3', 
					'content' => 'Lorem ipsum dolor sit.',
			)
		);		
		$this->assertEqual($expected, $result);		
		$this->assertNoErrors();			
	}
	
	function testUndeleteTree1() {
		$this->loadFixtures(
			'RevisionArticle','RevisionArticlesRev'
		);
		$this->Article->initializeRevisions();
				
		$this->Article->delete(3);
		
		$this->Article->id = 3;
		$this->Article->undelete();
		
		$result = $this->Article->find('all');
		
		$this->assertEqual(sizeof($result),3);
		$this->assertEqual($result[0]['Article']['lft'], 1);
		$this->assertEqual($result[0]['Article']['rght'],6);
		
		$this->assertEqual($result[1]['Article']['lft'], 2);
		$this->assertEqual($result[1]['Article']['rght'],3);
		
		$this->assertEqual($result[2]['Article']['id'],  3);
		$this->assertEqual($result[2]['Article']['lft'], 4);
		$this->assertEqual($result[2]['Article']['rght'],5);
	}
	
	function testUndeleteTree2() {
		$this->loadFixtures(
			'RevisionArticle','RevisionArticlesRev'
		);
		$this->Article->initializeRevisions();
		
		$this->Article->create(array('title'=>'første barn','content'=>'stuff','parent_id'=>3,'user_id'=>1));
		$this->Article->save();
		$this->Article->create(array('title'=>'andre barn','content'=>'stuff','parent_id'=>4,'user_id'=>1));
		$this->Article->save();
		
		$this->Article->delete(3);
		
		$this->Article->id = 3;
		$this->Article->undelete();
		
		$result = $this->Article->find('all');
		// Test that children are also "returned" to their undeleted father
		$this->assertEqual(sizeof($result),5);
		$this->assertEqual($result[0]['Article']['lft'], 1);
		$this->assertEqual($result[0]['Article']['rght'],10);
		
		$this->assertEqual($result[1]['Article']['lft'], 2);
		$this->assertEqual($result[1]['Article']['rght'],3);
		
		$this->assertEqual($result[2]['Article']['id'],  3);
		$this->assertEqual($result[2]['Article']['lft'], 4);
		$this->assertEqual($result[2]['Article']['rght'],9);
		
		$this->assertEqual($result[3]['Article']['id'],  4);
		$this->assertEqual($result[3]['Article']['lft'], 5);
		$this->assertEqual($result[3]['Article']['rght'],8);
		
		$this->assertEqual($result[4]['Article']['id'],  5);
		$this->assertEqual($result[4]['Article']['lft'], 6);
		$this->assertEqual($result[4]['Article']['rght'],7);
	}

	function testInitializeRevisionsWithLimit() {		
		$this->loadFixtures(
			'RevisionPost','RevisionPostsRev',
			'RevisionArticle','RevisionArticlesRev',
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag',
			'RevisionVote','RevisionVotesRev',
			'RevisionTag','RevisionTagsRev'
		);
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array('className' => 'RevisionTag','with'=>'CommentsTag'))),false);
		
		$this->assertFalse($this->Post->initializeRevisions());
		$this->assertTrue($this->Article->initializeRevisions());
		$this->assertFalse($this->Comment->initializeRevisions());
		$this->assertFalse($this->Comment->Vote->initializeRevisions());
		$this->assertFalse($this->Comment->Tag->initializeRevisions());
	}

	function testInitializeRevisions() {		
		$this->loadFixtures('RevisionPost');
		
		$this->assertTrue($this->Post->initializeRevisions(2));
		
		$result = $this->Post->ShadowModel->find('all');
		
		$this->assertEqual(sizeof($result),3);
	}
	
	function testRevertAll() {
		$this->loadFixtures(
			'RevisionPost','RevisionPostsRev'
		);
		
		$this->Post->save(array('id'=>1,'title' => 'tullball1'));
		$this->Post->save(array('id'=>3,'title' => 'tullball3'));
		$this->Post->create(array('title' => 'new post','content'=>'stuff'));
		$this->Post->save();

		$result = $this->Post->find('all');
		$this->assertEqual($result[0]['Post']['title'],'tullball1');
		$this->assertEqual($result[1]['Post']['title'],'Post 2');
		$this->assertEqual($result[2]['Post']['title'],'tullball3');
		$this->assertEqual($result[3]['Post']['title'],'new post');
		
		$this->assertTrue( $this->Post->revertAll(array(
				'date' => date('Y-m-d H:i:s', strtotime('yesterday'))
			))
		);
		
		$result = $this->Post->find('all');
		$this->assertEqual($result[0]['Post']['title'],'Lorem ipsum dolor sit amet');
		$this->assertEqual($result[1]['Post']['title'],'Post 2');
		$this->assertEqual($result[2]['Post']['title'],'Post 3');
		$this->assertEqual(sizeof($result),3);
	}	
	
	function testRevertAllConditions() {
		$this->loadFixtures(
			'RevisionPost','RevisionPostsRev'
		);
		
		$this->Post->save(array('id'=>1,'title' => 'tullball1'));
		$this->Post->save(array('id'=>3,'title' => 'tullball3'));
		$this->Post->create();
		$this->Post->save(array('title' => 'new post','content'=>'stuff'));

		$result = $this->Post->find('all');
		$this->assertEqual($result[0]['Post']['title'],'tullball1');
		$this->assertEqual($result[1]['Post']['title'],'Post 2');
		$this->assertEqual($result[2]['Post']['title'],'tullball3');
		$this->assertEqual($result[3]['Post']['title'],'new post');
		
		$this->assertTrue( $this->Post->revertAll(array(
				'conditions' => array('Post.id' =>array(1,2,4)),
				'date' => date('Y-m-d H:i:s', strtotime('yesterday'))
			))
		);
		
		$result = $this->Post->find('all');
		$this->assertEqual($result[0]['Post']['title'],'Lorem ipsum dolor sit amet');
		$this->assertEqual($result[1]['Post']['title'],'Post 2');
		$this->assertEqual($result[2]['Post']['title'],'tullball3');
		$this->assertEqual(sizeof($result),3);
	}	
	
	function testOnWithModel() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array(
					'className' => 'RevisionTag',
					'with' => 'CommentsTag'		
				)
			)
		),false);
		$result = $this->Comment->find('first', array('contain' => array('Tag' => array('id','title'))));
		$this->assertEqual(sizeof($result['Tag']),3);
		$this->assertEqual($result['Tag'][0]['title'],'Fun');
		$this->assertEqual($result['Tag'][1]['title'],'Hard');
		$this->assertEqual($result['Tag'][2]['title'],'Trick');
	}	

	function testHABTMRelatedUndoed() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array(
					'className' => 'RevisionTag',
					'with' => 'CommentsTag'		
				)
			)
		),false);
		$this->Comment->Tag->id = 3;
		$this->Comment->Tag->undo();
		$result = $this->Comment->find('first', array('contain' => array('Tag' => array('id','title'))));
		$this->assertEqual($result['Tag'][2]['title'],'Tricks');
	}

	function testOnWithModelUndoed() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array(
					'className' => 'RevisionTag',
					'with' => 'CommentsTag'		
				)
			)
		),false);
		$this->Comment->CommentsTag->delete(3);
		$result = $this->Comment->find('first', array('contain' => array('Tag' => array('id','title'))));
		$this->assertEqual(sizeof($result['Tag']),2);
		$this->assertEqual($result['Tag'][0]['title'],'Fun');
		$this->assertEqual($result['Tag'][1]['title'],'Hard');
		
		$this->Comment->CommentsTag->id = 3;
		$this->assertTrue($this->Comment->CommentsTag->undelete(), 'Undelete unsuccessful');
		
		$result = $this->Comment->find('first', array('contain' => array('Tag' => array('id','title'))));
		$this->assertEqual(sizeof($result['Tag']),3);
		$this->assertEqual($result['Tag'][0]['title'],'Fun');
		$this->assertEqual($result['Tag'][1]['title'],'Hard');
		$this->assertEqual($result['Tag'][2]['title'],'Trick');
		$this->assertNoErrors('Third Tag not back : %s');
	}	

	function testHabtmRevSave() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array(
					'className' => 'RevisionTag'
				)
			)
		),false);
		
		$result = $this->Comment->find('first', array('contain' => array('Tag' => array('id','title'))));
		$this->assertEqual(sizeof($result['Tag']),3);
		$this->assertEqual($result['Tag'][0]['title'],'Fun');
		$this->assertEqual($result['Tag'][1]['title'],'Hard');
		$this->assertEqual($result['Tag'][2]['title'],'Trick');

		$currentIds = Set::extract($result,'Tag.{n}.id');
		$expected = implode(',',$currentIds);
		$this->Comment->id = 1;
		$result = $this->Comment->newest();
		$this->assertEqual($expected,$result['Comment']['Tag']);

		$this->Comment->save(
			array(
				'Comment' => array('id' => 1),
				'Tag' => array(
					'Tag' => array(2,4)
				)
			)
		);
		
		$result = $this->Comment->find('first', array('contain' => array('Tag' => array('id','title'))));
		$this->assertEqual(sizeof($result['Tag']),2);
		$this->assertEqual($result['Tag'][0]['title'],'Hard');
		$this->assertEqual($result['Tag'][1]['title'],'News');

		$currentIds = Set::extract($result,'Tag.{n}.id');
		$expected = implode(',',$currentIds);
		$this->Comment->id = 1;
		$result = $this->Comment->newest();
		$this->assertEqual(4,$result['Comment']['version_id']);
		$this->assertEqual($expected,$result['Comment']['Tag']);
	}
	
	function testHabtmRevCreate() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array(
					'className' => 'RevisionTag'
				)
			)
		),false);
		
		$result = $this->Comment->find('first', array('contain' => array('Tag' => array('id','title'))));
		$this->assertEqual(sizeof($result['Tag']),3);
		$this->assertEqual($result['Tag'][0]['title'],'Fun');
		$this->assertEqual($result['Tag'][1]['title'],'Hard');
		$this->assertEqual($result['Tag'][2]['title'],'Trick');
		
		$this->Comment->create(
			array(
				'Comment' => array('title' => 'Comment 4'),
				'Tag' => array(
					'Tag' => array(2,4)
				)
			)
		);
		
		$this->Comment->save();		
		
		$result = $this->Comment->newest();
		$this->assertEqual('2,4',$result['Comment']['Tag']);
	}
	
	function testHabtmRevIgnore() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		
		$this->Comment->Behaviors->detach('Revision');
		$this->Comment->Behaviors->attach('Revision', array('ignore'=>array('Tag')));
		
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array(
					'className' => 'RevisionTag'
				)
			)
		),false);
		
		$this->Comment->id = 1;
		$original_result = $this->Comment->newest();

		$this->Comment->save(
			array(
				'Comment' => array('id' => 1),
				'Tag' => array(
					'Tag' => array(2,4)
				)
			)
		);
		
		$result = $this->Comment->newest();
		$this->assertEqual($original_result, $result);
	}
	
	
	function testHabtmRevUndo() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array(
					'className' => 'RevisionTag'
				)
			)
		),false);
		
		$this->Comment->save(
			array(
				'Comment' => array('id' => 1,'title'=>'edit'),
				'Tag' => array(
					'Tag' => array(2,4)
				)
			)
		);
		
		$this->Comment->id = 1;
		$this->Comment->undo();
		$result = $this->Comment->find('first', array('recursive'=>1));   //'contain' => array('Tag' => array('id','title'))));
		$this->assertEqual(sizeof($result['Tag']),3);
		$this->assertEqual($result['Tag'][0]['title'],'Fun');
		$this->assertEqual($result['Tag'][1]['title'],'Hard');
		$this->assertEqual($result['Tag'][2]['title'],'Trick');	
		$this->assertNoErrors('3 tags : %s');
	}
	
	function testHabtmRevUndoJustHabtmChanges() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array(
					'className' => 'RevisionTag'
				)
			)
		),false);
		
		$this->Comment->save(
			array(
				'Comment' => array('id' => 1),
				'Tag' => array(
					'Tag' => array(2,4)
				)
			)
		);
		
		$this->Comment->id = 1;
		$this->Comment->undo();
		$result = $this->Comment->find('first', array('recursive'=>1));   //'contain' => array('Tag' => array('id','title'))));
		$this->assertEqual(sizeof($result['Tag']),3);
		$this->assertEqual($result['Tag'][0]['title'],'Fun');
		$this->assertEqual($result['Tag'][1]['title'],'Hard');
		$this->assertEqual($result['Tag'][2]['title'],'Trick');	
		$this->assertNoErrors('3 tags : %s');
	}

	function testHabtmRevRevert() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array(
					'className' => 'RevisionTag'
				)
			)
		),false);
		
		$this->Comment->save(
			array(
				'Comment' => array('id' => 1),
				'Tag' => array(
					'Tag' => array(2,4)
				)
			)
		);
		
		$this->Comment->id = 1;
		$this->Comment->revertTo(1);
		
		$result = $this->Comment->find('first', array('recursive'=>1));   //'contain' => array('Tag' => array('id','title'))));
		$this->assertEqual(sizeof($result['Tag']),3);
		$this->assertEqual($result['Tag'][0]['title'],'Fun');
		$this->assertEqual($result['Tag'][1]['title'],'Hard');
		$this->assertEqual($result['Tag'][2]['title'],'Trick');	
		$this->assertNoErrors('3 tags : %s');
	}	

	function testHabtmRevRevertToDate() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array(
					'className' => 'RevisionTag'
				)
			)
		),false);
		
		$this->Comment->save(
			array(
				'Comment' => array('id' => 1),
				'Tag' => array(
					'Tag' => array(2,4)
				)
			)
		);
		
		$this->Comment->id = 1;
		$this->Comment->revertToDate(date('Y-m-d H:i:s',strtotime('yesterday')));
		
		$result = $this->Comment->find('first', array('recursive'=>1));  
		$this->assertEqual(sizeof($result['Tag']),3);
		$this->assertEqual($result['Tag'][0]['title'],'Fun');
		$this->assertEqual($result['Tag'][1]['title'],'Hard');
		$this->assertEqual($result['Tag'][2]['title'],'Trick');	
		$this->assertNoErrors('3 tags : %s');
	}

	function testRevertToTheTagsCommentHadBefore() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array('className' => 'RevisionTag'))),false);

		$result = $this->Comment->find('first', array(
			'conditions' => array('Comment.id' => 2),
			'contain' => array('Tag' => array('id','title'))));
		$this->assertEqual(sizeof($result['Tag']),2);
		$this->assertEqual($result['Tag'][0]['title'],'Fun');
		$this->assertEqual($result['Tag'][1]['title'],'Trick');		
				
		$this->Comment->save(
			array(
				'Comment' => array('id' => 2),
				'Tag' => array(
					'Tag' => array(2,3,4)
				)
			)
		);
		
		$result = $this->Comment->find('first', array(
			'conditions' => array('Comment.id' => 2),
			'contain' => array('Tag' => array('id','title'))));
		$this->assertEqual(sizeof($result['Tag']),3);
		$this->assertEqual($result['Tag'][0]['title'],'Hard');
		$this->assertEqual($result['Tag'][1]['title'],'Trick');
		$this->assertEqual($result['Tag'][2]['title'],'News');

		// revert Tags on comment logic
		$this->Comment->id = 2;
		$this->assertTrue(
			$this->Comment->revertToDate(date('Y-m-d H:i:s',strtotime('yesterday')))
		,'revertHabtmToDate unsuccessful : %s');

		$result = $this->Comment->find('first', array(
			'conditions' => array('Comment.id' => 2),
			'contain' => array('Tag' => array('id','title'))));
		$this->assertEqual(sizeof($result['Tag']),2);
		$this->assertEqual($result['Tag'][0]['title'],'Fun');
		$this->assertEqual($result['Tag'][1]['title'],'Trick');	

	}
	
	function testSaveWithOutTags() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array('className' => 'RevisionTag'))),false);
    
	    $this->Comment->id = 1;
	    $newest = $this->Comment->newest();
		
	    $this->Comment->save(array(
	      'Comment' => array('id'=>1,'title'=>'spam')
	    ));
	    
	    $result = $this->Comment->newest();
	    $this->assertEqual($newest['Comment']['Tag'], $result['Comment']['Tag']);
	}
	
	function testRevertToDeletedTag() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array('className' => 'RevisionTag','with'=>'CommentsTag'))),false);
		
		$this->Comment->Tag->delete(1);
		
		$result = $this->Comment->ShadowModel->find('all', array('conditions'=>array('version_id'=>array(4,5))));
		$this->assertEqual($result[0]['Comment']['Tag'],'3');
		$this->assertEqual($result[1]['Comment']['Tag'],'2,3');
	}

	function testBadKittyForgotId() {
		
		$this->assertNull($this->Comment->createRevision(),'createRevision() : %s');
		$this->assertError(true);
		$this->assertNull($this->Comment->diff(),'diff() : %s');
		$this->assertError(true);
		$this->assertNull($this->Comment->undelete(),'undelete() : %s');
		$this->assertError(true);
		$this->assertNull($this->Comment->undo(),'undo() : %s');
		$this->assertError(true);
		$this->assertNull($this->Comment->newest(),'newest() : %s');
		$this->assertError(true);
		$this->assertNull($this->Comment->oldest(),'oldest() : %s');
		$this->assertError(true);
		$this->assertNull($this->Comment->previous(),'previous() : %s');
		$this->assertError(true);		
		$this->assertNull($this->Comment->revertTo(10),'revertTo() : %s');	
		$this->assertError(true);	
		$this->assertNull($this->Comment->revertToDate(date('Y-m-d H:i:s',strtotime('yesterday')),'revertTo() : %s'));
		$this->assertError(true);		
		$this->assertNull($this->Comment->revisions(),'revisions() : %s');			
		$this->assertError(true);
	}
	
	function testBadKittyMakesUpStuff() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		
		$this->Comment->id = 1;
		$this->assertFalse($this->Comment->revertTo(10),'revertTo() : %s');
		$this->assertFalse($this->Comment->diff(1,4),'diff() between existing and non-existing : %s');
		$this->assertFalse($this->Comment->diff(10,4),'diff() between two non existing : %s');
	}

	function testMethodsOnNonRevisedModel() {
		$this->User->id = 1;
		$this->assertFalse($this->User->createRevision());
		$this->assertError();
		$this->assertNull($this->User->diff());
		$this->assertError();
		$this->assertFalse($this->User->initializeRevisions());
		$this->assertError();
		$this->assertNull($this->User->newest());
		$this->assertError();
		$this->assertNull($this->User->oldest());
		$this->assertError();
		$this->assertFalse($this->User->previous());
		$this->assertError();
		$this->assertFalse($this->User->revertAll(array('date'=>'1970-01-01')));
		$this->assertError();
		$this->assertFalse($this->User->revertTo(2));
		$this->assertError();
		$this->assertTrue($this->User->revertToDate('1970-01-01'));
		$this->assertNoErrors();
		$this->assertFalse($this->User->revisions());
		$this->assertError();
		$this->assertFalse($this->User->undo());
		$this->assertError();
		$this->assertFalse($this->User->undelete());
		$this->assertError();
		$this->assertFalse($this->User->updateRevisions());
		$this->assertError();
	}
/* */
}
?>
