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
	var $actsAs = array('Containable','Revision');
	
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
	var $hasAndBelongsToMany = array(
		'Comment' => array(
			'className' => 'RevisionComment'
		)
	);
}

class CommentsTag extends CakeTestModel {
    var $name = 'CommentsTag';
    var $useTable = 'revision_comments_revision_tags';
	var $actsAs = array('Revision');
}

class RevisionTestCase extends CakeTestCase {
	
	var $autoFixtures = false;
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
			'app.revision_comments_revision_tag',
			'app.revision_comments_revision_tags_rev',
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
        $this->Post->createRevision();
	
		$this->Post->id = 1;
		$count = $this->Post->shadow('count', array('conditions'=>array('id'=>1)));
		$this->assertEqual($count,2);
		
		$this->Post->id = 1;
		$data = $this->Post->read();
		$this->Post->save($data);
		
		$this->Post->id = 1;
		$count = $this->Post->shadow('count', array('conditions'=>array('id'=>1)));
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
	            'version_id' => 7,
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
		
	function testUndo() {				
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
		
	function testRevertTo() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 1'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 2'));
		$this->Post->save($data);
		$data = array('Post' => array('id'=>1, 'title' => 'Edited Post 3'));
		$this->Post->save($data);
		
		$this->Post->id = 1;
		$success = $this->Post->RevertTo(5);
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
		
		$this->Article->create();
		$data = array('Article' => array('id'=>3, 'content' => 'Re-edited Article'));
		$this->Article->save($data);
		
		$this->Article->moveUp(3);
		
		$this->Article->id = 3;
		
		$result = $this->Article->newest(array('fields' => array('id', 'title', 'content', 'version_id')));
		$expected = array(
			'Article' => array(
				'id' => 3, 
				'title' => 'Lorem ipsum', 
				'content' => 'Re-edited Article', 
				'version_id' => 1
			)
		);
		$this->assertEqual($expected, $result);
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
		$this->assertNoErrors();
		$success = $this->User->save($data);
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

	function testUndelete() {
		$this->loadFixtures('RevisionPost','RevisionPostsRev');
		
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
					'title' => 'Post 3', 
					'content' => 'Lorem ipsum dolor sit.'
			)
		);
		$this->assertEqual($expected, $result);
		
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
		
		$result = $this->Post->find('first',array('conditions'=>array('id'=>3)));
		
		$expected = array(
			'Post' => array(
					'id' => 3,
					'title' => 'Post 3', 
					'content' => 'Lorem ipsum dolor sit.',
			)
		);
		
		$this->assertEqual($expected, $result);
		
	}

	function testInitializeRevisions() {		
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

	function testRevertAll() {
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
		
		$result = $this->Comment->find('first', array('recursive'=>1));   //'contain' => array('Tag' => array('id','title'))));
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
	
	function testRevertToDeletedTag() {
		$this->loadFixtures(
			'RevisionComment','RevisionCommentsRev',
			'RevisionCommentsRevisionTag','RevisionCommentsRevisionTagsRev',
			'RevisionTag','RevisionTagsRev'
		);
		
		$this->Comment->bindModel(array('hasAndBelongsToMany' => array(
				'Tag' => array('className' => 'RevisionTag','with'=>'CommentsTag'))),false);
		
		$this->Comment->Tag->delete(1);
		
		$result = $this->Comment->shadow('all', array('conditions'=>array('version_id'=>array(4,5))));
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

}
?>
