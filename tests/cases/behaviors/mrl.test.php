<?php
class MrlUser extends MrlAppModel {
	public $name = 'MrlUser';
	public $alias = 'User';
	public $hasMany = array(
		'Article' => array('className' => 'MrlArticle','foreignKey'=>'user_id','order'=>'Article.id'),
		'Comment' => array('className' => 'MrlComment','foreignKey'=>'user_id','order'=>'Comment.id')
	);	
	public function lang($locale = null) {
		$this->Article->lang($locale);
		$this->Comment->lang($locale);
	}
}
class MrlArticle extends MrlAppModel {
	public $name = 'MrlArticle';
	public $alias = 'Article';
	public $hasMany = array(
		'Comment' => array(
			'className' => 'MrlComment',
			'foreignKey'=> 'article_id',
			'dependent' => true
		)
	);
	public $hasAndBelongsToMany = array('Tag' =>array('className' => 'MrlTag'));
	public function lang($locale = null) {
		parent::lang($locale);
		$this->Comment->lang($locale);
	}


}
class MrlComment extends MrlAppModel {
	public $name = 'MrlComment';
	public $displayField = 'content';
	public $alias = 'Comment';
}
class MrlVote extends MrlAppModel {
	public $name = 'MrlVote';
	public $displayField = 'content';
	public $alias = 'Vote';
	public $useTable = 'revision_votes';	
}
class MrlTag extends MrlAppModel {
	public $name = 'MrlTag';
	public $alias = 'Tag';
	public $hasAndBelongsToMany = array('Article' =>array('className' => 'MrlArticle'));	
}

class MrlAppModel  extends CakeTestModel {	
	public $actsAs = array(
		'Containable',
		'Multilingual',			
		'Revision' => array('model' => 'RevisionShadowModel'),
		'Logable'
	);	
	/**
	 * Overrides MultilingualBehavior::setLocale so that shadowmodel also knows locale
	 *
	 * @param string $locale
	 */
	public function lang($locale = null) {
		$locale = $this->Behaviors->Multilingual->setLocale($this,$locale);
		if (is_object($this->ShadowModel)) {
			$this->ShadowModel->locale = $locale;
		}
	}
}

/**
 * Overrides RevisionShadowModel defined by the revision behavior 
 *
 */
class RevisionShadowModel extends Model {
	public $name = 'RevisionShadowModel';
	public $order = 'version_created DESC, version_id DESC';
	
	/**
	 * Forces ShadowModel to take locale into consideration
	 *
	 * @param array $query
	 * @return array
	 */
	public function beforeFind($query) {
		if (isset($this->locale) && !isset($query['conditions']['locale'])) {
			$query['conditions']['locale'] = $this->locale;
		}
		return $query;
	}
}
class Log extends CakeTestModel  {
	public $name = 'Log';
	public $useTable = 'mrl_logs';
	public $order = 'Log.created DESC, Log.id DESC';
}

class MrlCase extends CakeTestCase {
    public $Post;
	public $autoFixtures = false;
	public $fixtures = array(
		'app.mrl_user',
		'app.mrl_log',
		'app.mrl_article',
		'app.mrl_articles_rev',
		'app.mrl_articles_locale',
		'app.mrl_articles_mrl_tag',
		'app.mrl_tag',
		'app.mrl_tags_rev',
		'app.mrl_tags_locale',
		'app.mrl_comment',
		'app.mrl_comments_rev',
		'app.mrl_comments_locale',
		'app.revision_vote',
		'app.revision_votes_rev',
	);

	function startTest() {
		$this->User = ClassRegistry::init('MrlUser');
	}	
	
	function endTest() {
        unset($this->User);
        ClassRegistry::flush();
	}

	function testRevertToDateLocalization() {
		// load fixtures
		$this->loadFixtures('MrlArticle','MrlComment');		
		// load revision fixtures
		$this->loadFixtures('MrlArticlesRev','MrlCommentsRev');		
		// load locale fixtures
		$this->loadFixtures('MrlArticlesLocale','MrlCommentsLocale');
        unset($this->User);
        ClassRegistry::flush();
        $Article = new MrlArticle();
		
		$Article->lang('es-es');
		
		$original = $Article->findById(1);
		
		$Article->save(array(
			'Article' => array(
				'id' => 1,
				'title' => 'edited spanish title',
				'content' => 'edited spanish content'
			),
		));
		$Article->Comment->save(array(
			'Comment' => array(
				'id' => 1,
				'content' => 'edited spanish comment content'
			)
		));		
		
		$Article->recursive = 1;
		$result = $Article->findById(1);
		$this->assertEqual($result['Article']['title'],'edited spanish title');	
		$this->assertEqual($result['Article']['content'],'edited spanish content');	
		$this->assertEqual($result['Comment'][0]['content'],'edited spanish comment content');	
		$this->assertEqual($result['Comment'][1]['content'],'spanish comment 2');
		
		$Article->revertToDate(date('Y-m-d H:i:s',strtotime('yesterday')),true,true);
		$Article->recursive = 1;
		$reverted = $Article->findById(1);
		$this->assertEqual($original, $reverted);
	}
	
	
	
	function testRevertToLocalization1() {
		// load fixtures
		$this->loadFixtures('MrlArticle');		
		// load revision fixtures
		$this->loadFixtures('MrlArticlesRev');		
		// load locale fixtures
		$this->loadFixtures('MrlArticlesLocale');
        unset($this->User);
        ClassRegistry::flush();
        $Article = new MrlArticle();
        
        $Article->id = 1;
		$this->assertEqual($Article->locales(),array(1=>'es-es'));
		
		$Article->lang('es-es');
		$Article->save(array(
			'Article' => array(
				'id' => 1,
				'title' => 'edited spanish title',
				'content' => 'edited spanish content'
			)
		));
		
		$this->assertEqual($Article->locales(),array(1=>'es-es'));
		
		$Article->read();
		$this->assertEqual($Article->data['Article']['title'],'edited spanish title');
		
		$result = $Article->previous();
		
		$Article->revertTo($result['Article']['version_id']);
		
		$this->assertEqual($Article->locales(),array(1=>'es-es'));
		
		$Article->read();
		$this->assertEqual($Article->data['Article']['title'],'Nimrod Expedición');		
		
		$Article->lang();
		$Article->read();
		$this->assertEqual($Article->data['Article']['title'],'Nimrod Expedition');		
	}
	
	function testUndoLocalization1() {
		// load fixtures
		$this->loadFixtures('MrlArticle');		
		// load revision fixtures
		$this->loadFixtures('MrlArticlesRev');		
		// load locale fixtures
		$this->loadFixtures('MrlArticlesLocale');
        unset($this->User);
        ClassRegistry::flush();
        $Article = new MrlArticle();
        
        $Article->id = 1;
		$this->assertEqual($Article->locales(),array(1=>'es-es'));
		
		$Article->lang('es-es');
		$Article->save(array(
			'Article' => array(
				'id' => 1,
				'title' => 'edited spanish title',
				'content' => 'edited spanish content'
			)
		));
		
		$this->assertEqual($Article->locales(),array(1=>'es-es'));
		
		$Article->read();
		$this->assertEqual($Article->data['Article']['title'],'edited spanish title');
		
		$Article->undo();
		
		$this->assertEqual($Article->locales(),array(1=>'es-es'));
		
		$Article->read();
		$this->assertEqual($Article->data['Article']['title'],'Nimrod Expedición');		
		
		$Article->lang();
		$Article->read();
		$this->assertEqual($Article->data['Article']['title'],'Nimrod Expedition');		
	}
	
	function testUndoLocalization2() {
		// load fixtures
		$this->loadFixtures('MrlArticle');		
		// load revision fixtures
		$this->loadFixtures('MrlArticlesRev');		
		// load locale fixtures
		$this->loadFixtures('MrlArticlesLocale');
        unset($this->User);
        ClassRegistry::flush();
        $Article = new MrlArticle();
        
        $Article->id = 1;
		$this->assertEqual($Article->locales(),array(1=>'es-es'));
		
		$Article->lang('no-nb');
		$Article->save(array(
			'Article' => array(
				'id' => 1,
				'title' => 'norsk title',
				'content' => 'norsk content'
			)
		));
		$this->assertEqual($Article->locales(),array(1=>'es-es',4=>'no-nb'));
		
		$Article->read();
		$this->assertEqual($Article->data['Article']['title'],'norsk title');
		
		$Article->undo();
		
		$this->assertEqual($Article->locales(),array(1=>'es-es'));
		
		unset($Article->ShadowModel->locale);
		$Article->lang();
		$Article->read();
		$this->assertEqual($Article->data['Article']['title'],'Nimrod Expedition');		
	}	
	
	function testLoggingOfLocale() {
		// load fixtures
		$this->loadFixtures('MrlArticle','MrlComment');		
		// load revision fixtures
		$this->loadFixtures('MrlArticlesRev','MrlCommentsRev');		
		// load locale fixtures
		$this->loadFixtures('MrlArticlesLocale','MrlCommentsLocale');
        unset($this->User);
        ClassRegistry::flush();
        $Article = new MrlArticle();
		$Log = new Log();
	
		$Article->lang('se-se');
		$Article->save(array(
			'Article' => array(
				'id' => 1,
				'title' => 'svensk tittel',
				'content' => 'svensk inhold'
			)
		));
		
		$result = $Article->find('first', array(
			'contain' => array('Comment')
		));
		$this->assertEqual($result['Article']['title'],'svensk tittel');
		$this->assertEqual($result['Article']['locale'],'se-se');
		
		$result = $Log->find('first');
		$this->assertEqual($result['Log']['action'],'translation added');
		
		$Article->save(array(
			'Article' => array(
				'id' => 1,
				'title' => 'svensk tittel edit',
				'content' => 'svensk inhold edit'
			)
		));
		
		$result = $Log->find('first');
		$this->assertEqual($result['Log']['action'],'translation edited');
		
		$this->assertFalse($Article->delete(1));
		$this->assertEqual($Article->locales(),array(1=>'es-es'));
		
		$Article->lang();
		$this->assertIsA($Article->read(null,1),'array');
		
		$result = $Log->find('first');
		$this->assertEqual($result['Log']['action'],'translation deleted');
		
		$this->assertTrue($Article->delete(1));
		$this->assertFalse($Article->read(null,1));
		
		$result = $Log->find('first');
		$this->assertEqual($result['Log']['action'],'delete');
	}	
	
	function testRevertCascade() {
		// load fixtures
		$this->loadFixtures('MrlUser','MrlArticle','MrlComment','MrlTag','MrlArticlesMrlTag','RevisionVote');		
		// load revision fixtures
		$this->loadFixtures('MrlArticlesRev','MrlCommentsRev','MrlTagsRev','RevisionVotesRev');		
		// load locale fixtures
		$this->loadFixtures('MrlArticlesLocale','MrlCommentsLocale','MrlTagsLocale');
					
		$this->User->unBindModel(array('hasMany' => array(
			'Comment' 
		)),false);
		
		$this->User->Article->Comment->bindModel(array('hasMany' => array(
			'Vote' => array(
				'className' => 'MrlVote',
				'foreignKey' => 'revision_comment_id'
			)
		)),false);
		
		$start = $this->User->find('first',array(
			'conditions' => array('User.id' => 2),
			'contain' => array(
				'Article' => array(
					'Tag' => array(),
					'Comment' => array(
						'Vote' => array()
					)
				)
			)		
		));
		
		$this->User->Article->create(array(
			'Article' => array(
				'title' => 'spam',
				'content' => 'spam',
				'user_id' => 2
			)
		));
		$this->User->Article->save();
		
		$this->User->Article->save(array(
			'Article' => array(
				'id' => 2,
				'title' => 'spam',
				'content' => 'spam',
				'user_id' => 1
			)
		));
		
		$this->User->Article->delete(4);
		
		$this->User->Article->Comment->create(array(
			'Comment' => array(
				'article_id' => 1,
				'content' => 'spam',
				'user_id' => 2
			),
		));		
		$this->User->Article->Comment->save();
		
		$this->User->Article->Comment->create(array(
			'Comment' => array(
				'article_id' => 1,
				'content' => 'spam',
				'user_id' => 3
			),
		));		
		$this->User->Article->Comment->save();
		
		$this->User->Article->Comment->save(array(
			'Comment' => array(
				'id' => 1,
				'content' => 'spam'
			),
		));
		
		$this->User->Article->Comment->delete(2);
		
		$this->User->Article->Comment->Vote->create(array(
			'Vote' => array(
				'title' => 'spam',
				'content' => 'spam',
				'revision_comment_id' => 1		
			)
		));
		$this->User->Article->Comment->Vote->save();
		$this->User->Article->Comment->Vote->save(array(
			'Vote' => array(
				'id' => 1,
				'title' => 'spam',
				'content' => 'spam',
				'revision_comment_id' => 1		
			)
		));
		
		$this->User->Article->Comment->Vote->delete(3);
		
		$this->User->id = 2;
		$this->User->revertToDate(date('Y-m-d H:i:s',strtotime('yesterday')),true,true);
		
		$end = $this->User->find('first',array(
			'conditions' => array('User.id' => 2),
			'contain' => array(
				'Article' => array(
					'Tag' => array(),
					'Comment' => array(
						'Vote' => array()
					)
				)
			)		
		));
		$this->assertEqual($start['User'],$end['User']);
		$this->assertEqual($start['Article'][1],$end['Article'][1]);
		$this->assertEqual($start['Article'][2],$end['Article'][2]);
		$this->assertEqual($start['Article'][3],$end['Article'][3]);
		
		$this->assertEqual($start['Article'][0]['title'],  $end['Article'][0]['title']);
		$this->assertEqual($start['Article'][0]['content'],$end['Article'][0]['content']);
		
		$this->assertEqual(
			sizeof($start['Article'][0]['Comment']),
			sizeof($end['Article'][0]['Comment']), 
			'Number of comments changed : %s');
		
		$this->assertEqual(
			sizeof($start['Article'][0]['Comment'][0]['Vote']) + 1,
			sizeof($end['Article'][0]['Comment'][0]['Vote']),
			'Votes restored : %s' );
			
		$this->assertEqual(
			sizeof($end['Article'][0]['Comment'][1]['Vote']), 0,
			'Deleted Vote is back : %s');
	
		$this->Log = new Log();
		$result = $this->Log->find('all');
		
		$this->assertEqual(sizeof($result),17);
		$this->assertNotNull($result[0]['Log']['version_id']);
		$this->assertNotNull($result[1]['Log']['version_id']);
		$this->assertNull($result[2]['Log']['version_id']);
		$this->assertNotNull($result[3]['Log']['version_id']);
		$this->assertNull($result[4]['Log']['version_id']);
		$this->assertNull($result[5]['Log']['version_id']);
		$this->assertNotNull($result[6]['Log']['version_id']);
	}

		
	function testUndoLogged() {
		// load fixtures
		$this->loadFixtures('MrlUser','MrlArticle','MrlComment','MrlTag','MrlArticlesMrlTag','RevisionVote');		
		// load revision fixtures
		$this->loadFixtures('MrlArticlesRev','MrlCommentsRev','MrlTagsRev','RevisionVotesRev');		
		// load locale fixtures
		$this->loadFixtures('MrlArticlesLocale','MrlCommentsLocale','MrlTagsLocale');
		
		$this->User->Article->save(array(
			'Article' => array(
				'id' => 1,
				'title' => 'spam'
			)
		));
		$this->User->Article->lang();
		$this->User->Article->id = 1;
		$this->User->Article->undo();
		
		
		$this->Log = new Log();
		$result = $this->Log->find('first');
		$this->assertEqual($result['Log']['action'], 'undo changes');		
	}	
		
	function testRevertToDateLogged() {
		// load fixtures
		$this->loadFixtures('MrlUser','MrlArticle','MrlComment','MrlTag','MrlArticlesMrlTag','RevisionVote');		
		// load revision fixtures
		$this->loadFixtures('MrlArticlesRev','MrlCommentsRev','MrlTagsRev','RevisionVotesRev');		
		// load locale fixtures
		$this->loadFixtures('MrlArticlesLocale','MrlCommentsLocale','MrlTagsLocale');
		
		$this->User->Article->save(array(
			'Article' => array(
				'id' => 1,
				'title' => 'spam'
			)
		));		
		$this->User->Article->Comment->save(array(
			'Comment' => array(
				'id' => 1,
				'content' => 'spam'
			),
		));
		$this->User->Article->lang();
		$this->User->Article->id = 1;
		$this->User->Article->revertToDate(date('Y-m-d H:i:s',strtotime('yesterday')),true,true);
		
		$this->Log = new Log();
	#	debug($this->Log->find('all'));
	#	debug($this->User->Article->ShadowModel->find('all'));
	#	debug($this->User->Article->Comment->ShadowModel->find('all'));
		
		
		$result = $this->Log->find('first');
		$this->assertPattern('/^revertToDate/',$result['Log']['action']);	
	}
	
	function testComplexRevision() {
		// load fixtures
		$this->loadFixtures('MrlUser','MrlArticle','MrlComment','MrlTag','MrlArticlesMrlTag');
		
		// load revision fixtures
		$this->loadFixtures('MrlArticlesRev','MrlCommentsRev','MrlTagsRev');
				
		// set user id to pretend REAL login
		$user_id = 2;
				
		// find the time created of the comment above; ie "last known action of real user"
		$this->User->Article->Comment->id = 4;
		$newest = $this->User->Article->Comment->newest();
		$version_created = $newest['Comment']['version_created']; // time to return to
				
		// inspect user and user data to set expected for last check; ie "are we back to this point"
		$result = $this->User->find('first', array('conditions'=>array('User.id'=>$user_id),
			'contain' => array(
				'Article'=>array(
					'Tag' => array(),
					'Comment' => array()
				),
				'Comment' => array()
			)
		));
		$this->assertTrue(Set::matches('/User',$result));		
		$this->assertTrue(Set::matches('/Article',$result));
		$this->assertEqual(sizeof($result['Article']),4);
		$this->assertTrue(Set::matches('/Article/Tag',$result));
		$this->assertEqual(sizeof($result['Article'][0]['Tag']),3);
		$this->assertEqual(sizeof($result['Article'][1]['Tag']),2);
		$this->assertTrue(Set::matches('/Article/Comment',$result));	
		$this->assertEqual(sizeof($result['Article'][0]['Comment']),3);		
		$this->assertEqual(sizeof($result['Article'][1]['Comment']),1);		
		$this->assertTrue(Set::matches('/Comment',$result));	
		$this->assertEqual(sizeof($result['Comment']),3);	
		$this->assertFalse(Set::matches('/Comment/Article',$result));
		$this->assertFalse(Set::matches('/Comment/User',$result));	
		$original_state = $result;
		
		$this->User->lang('es-es');		
		$original_spanish_state = $this->User->find('first', array('conditions'=>array('User.id'=>$user_id),
			'contain' => array(
				'Article'=>array(
					'Tag' => array(),
					'Comment' => array()
				),
				'Comment' => array()
			)
		));		
		$this->User->lang();
		
		// assume now logged in as hijacker 
		
		// delete an article
		$this->assertTrue($this->User->Article->delete(4));
		
		// edit  and change tags a 2nd article
		$this->assertTrue($this->User->Article->save(array(
			'Article' => array(
				'id' => 1,
				'title' => 'haha',
				'content' => 'you been had!'
			),
			'Tag' => array('Tag' => array(
				1,4
			))
		)));		
		
		// add tags
		$this->assertTrue($this->User->Article->save(array(
			'Article' => array('id' => 1),
			'Tag' => array('Tag' => array(1,2,3,4))			
		)));
		
		// create a 4th article
		$this->User->Article->create(array('title' => 'spam', 'content'=>'spam', 'user_id'=>$user_id));
		$this->assertTrue($this->User->Article->save());
			
		// translate an article 
		$this->User->lang('es-es');
				
		$this->User->Article->save(array(
			'Article' => array(
				'id' => 1,
				'title' => 'edited spanish title',
				'content' => 'edited spanish content'
			),
		));
		$this->User->lang();		
		
		// create many comments on 2nd, 3rd, 4th and a 5th article
		$spam_comment = array('user_id'=>$user_id,'article_id'=>2,'content'=>'spam'); 
		$this->User->Article->Comment->create($spam_comment);
		$this->assertTrue($this->User->Article->Comment->save());
		$this->User->Article->Comment->create($spam_comment);
		$this->assertTrue($this->User->Article->Comment->save());
		$spam_comment['article_id'] = 1;
		$this->User->Article->Comment->create($spam_comment);
		$this->assertTrue($this->User->Article->Comment->save());
		
		$this->User->lang('es-es');	
		// translate a comment 	
		$this->User->Article->Comment->save(array(
			'Comment' => array(
				'id' => 3,
				'content' => 'spanish comment content added'
			)
		));				
		// delete comments on a translated article
		$this->User->Article->Comment->delete(2);
		
		// edit a translation
		$this->User->Article->Comment->save(array(
			'Comment' => array(
				'id' => 1,
				'content' => 'edited spanish comment content'
			)
		));		
		$this->User->lang();		
		// create comments on an article not made by this user
		$spam_comment['article_id'] = 3;
		$this->User->Article->Comment->create($spam_comment);
		$this->assertTrue($this->User->Article->Comment->save());
		

		
		// add a tag and add it to all user's articles
		$this->User->Article->Tag->create(array('title'=>'SPAM'));
		$this->assertTrue($this->User->Article->Tag->save());
		$this->User->Article->Tag->save(array(
			'Tag' => array('id' => $this->User->Article->Tag->id),
			'Article' => array('Article' => array(1,2,3,5,6))
		));
		
		// pretend hijacker log out
		
		// inspect user and user data to verify that all illegal activity is in database; ie start != now
		$result = $this->User->find('first', array('conditions'=>array('User.id'=>$user_id),
			'contain' => array(
				'Article'=>array(
					'Tag' => array(),
					'Comment' => array()
				),
				'Comment' => array()
			)
		));
		$this->assertTrue(Set::matches('/User',$result));		
		$this->assertTrue(Set::matches('/Article',$result));
		$this->assertEqual(sizeof($result['Article']),4);
		$this->assertTrue(Set::matches('/Article/Tag',$result));
		$this->assertEqual(sizeof($result['Article'][0]['Tag']),5);
		$this->assertEqual($result['Article'][0]['Tag'][4]['title'],'SPAM');
		$this->assertEqual(sizeof($result['Article'][1]['Tag']),3);
		$this->assertEqual(sizeof($result['Article'][2]['Tag']),1);
		$this->assertEqual(sizeof($result['Article'][3]['Tag']),1);
		$this->assertEqual($result['Article'][3]['Tag'][0]['title'],'SPAM');
		$this->assertTrue(Set::matches('/Article/Comment',$result));	
		$this->assertEqual(sizeof($result['Article'][0]['Comment']),4);	
		$this->assertEqual($result['Article'][0]['Comment'][3]['content'],'spam');	
		$this->assertEqual(sizeof($result['Article'][1]['Comment']),3);		
		$this->assertTrue(Set::matches('/Comment',$result));	
		$this->assertEqual(sizeof($result['Comment']),7);	
		$this->assertFalse(Set::matches('/Comment/Article',$result));
		$this->assertFalse(Set::matches('/Comment/User',$result));
	
		// inspect log to verify that hijacked activity is all logged
	
		// identify the "last known action of real user" comment and 
		// revert to this datetime, cascading to all users articles and comments
		$this->User->id = $user_id;
		$this->assertTrue(
			$this->User->revertToDate($version_created,true,true)
		,'revertToDate returns false! : %s');

		// verify that start == now 
		$result = $this->User->find('first', array('conditions'=>array('User.id'=>$user_id),
			'contain' => array(
				'Article'=>array(
					'Tag' => array(),
					'Comment' => array()
				),
				'Comment' => array()
			)
		));	
        $this->assertEqual($original_state['User'], $result['User'], 'Revert of User failed : %s');
        $this->assertEqual($original_state['Article'], $result['Article'], 'Revert of Article failed : %s');
        $this->assertEqual(sizeof($original_state['Article']), sizeof($result['Article']),'Wrong number of articles : %s');
        $this->assertEqual($original_state['Article'][0]['Tag'], $result['Article'][0]['Tag'], 'Revert of Tag failed : %s');
        $this->assertEqual($original_state['Article'][0]['Comment'], $result['Article'][0]['Comment'], 'Revert of Tag failed : %s');
        $this->assertEqual($original_state['Comment'], $result['Comment'], 'Revert of Comment failed : %s');
        $this->assertEqual(sizeof($original_state['Comment']), sizeof($result['Comment']),'Wrong number of comments : %s');
        
        
		$this->User->lang('es-es');		
		$reverted_spanish_state = $this->User->find('first', array('conditions'=>array('User.id'=>$user_id),
			'contain' => array(
				'Article'=>array(
					'Tag' => array(),
					'Comment' => array()
				),
				'Comment' => array()
			)
		));		
		$this->assertEqual($original_spanish_state, $reverted_spanish_state);
	}	

}
?>
