<?php
class MergePost extends CakeTestModel {
	public $alias = 'Post';
	public $name = 'MergePost';
	public $actsAs = array('Merge','Containable');	
	public $belongsTo = array('Topic'=> array('className'=>'MergeTopic','foreignKey'=>'topic_id'));
	public $hasAndBelongsToMany = array('Tag'=>array(
                'className'=>'MergeTag',
                'foreignKey'=>'post_id',
                'associationForeignKey'=>'tag_id'
    ));
	public $useDbConfig = 'test';
	
}

class MergeTopic extends CakeTestModel {
	public $alias = 'Topic';
	public $name = 'MergeTopic';
	public $actsAs = array('Merge');
	public $hasMany = array('Post'=>array('className'=>'MergePost','foreignKey'=>'topic_id','dependent' => true));
	public $useDbConfig = 'test';
}

class MergeTag extends CakeTestModel {
	public $alias = 'Tag';
	public $name = 'MergeTag';
	public $useDbConfig = 'test';	
}

class MergePostsMergeTag extends CakeTestModel {
	public $name = 'MergePostsMergeTag';
	public $useDbConfig = 'test';
}

class MergeCase extends CakeTestCase {
	public $fixtures = array('app.merge_post','app.merge_topic','app.merge_tag','app.merge_posts_merge_tag');

	function start() {
		parent::start();
		$this->Post = new MergePost(); 
		$this->Topic = new MergeTopic(); 
	}	


 	function testDefaultMerge() {
		$this->Post->merge(1,2);
		$result = $this->Post->find('first',array('conditions'=>array('id'=>1),'recursive'=>-1));
		$expected = array(
			'Post' => array(
				'id' => 1,
				'title' => 'Rock and Roll',
				'body' => 'I love rock and roll!',
				'topic_id' => 1
			)
		);
		$this->assertEqual($result, $expected);
		$result = $this->Post->find('first',array('conditions'=>array('id'=>2),'recursive'=>-1));
		$this->assertFalse($result);		
	}
	
	function testInputMerge() {
		$data = array(
			'Post' => array(
				'body' => 'Rock and roll is both cool and you love it.'
		));
		$this->Post->merge(1,2,array('body'=>'input'),array(),$data);
		$result = $this->Post->find('first',array('conditions'=>array('id'=>1),'recursive'=>-1));
		$expected = array(
			'Post' => array(
				'id' => 1,
				'title' => 'Rock and Roll',
				'body' => 'Rock and roll is both cool and you love it.',
				'topic_id' => 1
			)
		);
		$this->assertEqual($result, $expected);
		$this->assertFalse($this->Post->find(array('id'=>2)));		
	}
	
	function testSourceMerge() {
		$this->Post->merge(1,2,array('title'=>'source', 'body' => 'source', 'topic_id' => 'source'));
		$result = $this->Post->find('first',array('conditions'=>array('id'=>1),'recursive'=>-1));
		$expected = array(
			'Post' => array(
				'id' => 1,
				'title' => 'Music',
				'body' => 'Rock and roll is cool',
				'topic_id' => 1
			)
		);
		$this->assertEqual($result, $expected);
		$this->assertFalse($this->Post->find(array('id'=>2)));		
	}		
	
	function testTargetMerge() {
		$this->Post->merge(1,2,array('title'=>'target', 'body' => 'target', 'topic_id' => 'target'));
		$result = $this->Post->find('first',array('conditions'=>array('id'=>1),'recursive'=>-1));
		$expected = array(
			'Post' => array(
				'id' => 1,
				'title' => 'Rock and Roll',
				'body' => 'I love rock and roll!',
				'topic_id' => 1
			)
		);
		$this->assertEqual($result, $expected);
		$this->assertFalse($this->Post->find('first',array('conditions'=>array('id'=>2),'recursive'=>-1)));		
	}		
	
	function testSourceTargetMerge() {
		$this->Post->merge(1,2,array('body' => 'source_target'));
		$result = $this->Post->find('first',array('conditions'=>array('id'=>1),'recursive'=>-1));
		$expected = array(
			'Post' => array(
				'id' => 1,
				'title' => 'Rock and Roll',
				'body' => "Rock and roll is cool\n I love rock and roll!",
				'topic_id' => 1
			)
		);
		$this->assertEqual($result, $expected);
		$this->assertFalse($this->Post->find('first',array('conditions'=>array('id'=>2),'recursive'=>-1)));		
	}

	function testBelongsTo() {
	/** stupid containable */
		$result = $this->Post->find('first', array('conditions'=>array('Post.id'=>1),'contain'=>'Topic'));
		$expected = array(
		    'Post' => array(
		            'id' => 1,
		            'title' => 'Rock and Roll',
		            'body' => 'I love rock and roll!',
		            'topic_id' => 1
		     ),		
		    'Topic' => array(
		            'id' => 1,
		            'title' => 'Personal'
		    )		
		);
		$this->assertEqual($result, $expected, 'BelongsTo fixture test : %s');
		
		$this->Post->merge(1,3,array('topic_id' => 'source'));
		$result = $this->Post->find('first', array('conditions'=>array('Post.id'=>1),'contain'=>'Topic'));
		$expected = array(
		    'Post' => array(
		            'id' => 1,
		            'title' => 'Rock and Roll',
		            'body' => 'I love rock and roll!',
		            'topic_id' => 3
		     ),		
		    'Topic' => array(
		            'id' => 3,
		            'title' => 'Work'
		    )			
		);
		$this->assertEqual($result, $expected, 'BelongsTo test : %s');		
	}

	function testHasManyDefault() {		
		$result = $this->Topic->find('first', array('conditions'=>array('Topic.id'=>2),'recursive'=>1));
		$this->assertEqual($result['Post'], array());
		
		$this->Topic->merge(2,3);		
		$result = $this->Topic->find('first', array('conditions'=>array('Topic.id'=>2),'recursive'=>1));		
		$expected = array(
			0 => array(
				'id' => 3,
				'title' => 'Food',
				'body' => 'Apples are good',
				'topic_id' => 2
			),
		);
		$this->assertEqual($result['Post'], $expected, 'HasMany test 1 : %s');
	
		$this->Topic->merge(1,2);		
		$result = $this->Topic->find('first', array('conditions'=>array('Topic.id'=>1),'recursive'=>1));
		$expected = array(
			0 => array(
				'id' => 1,
				'title' => 'Rock and Roll',
				'body' => 'I love rock and roll!',
				'topic_id' => 1
			),
			1 => array(
				'id' => 2,
				'title' => 'Music',
				'body' => 'Rock and roll is cool',
				'topic_id' => 1
			),
			2 => array(
				'id' => 3,
				'title' => 'Food',
				'body' => 'Apples are good',
				'topic_id' => 1
			),
		);
		$this->assertEqual($result['Post'], $expected, 'HasMany test 2 : %s');	
	}

	function testHasManyBoth() {
		$this->Topic->merge(2,3,array(),array('Post'=>'both'));		
		$result = $this->Topic->find('first', array('conditions'=>array('Topic.id'=>2),'recursive'=>1));		
		$expected = array(
			0 => array(
				'id' => 3,
				'title' => 'Food',
				'body' => 'Apples are good',
				'topic_id' => 2
			),
		);
		$this->assertEqual($result['Post'], $expected, 'HasMany test 3 : both : %s');
	
		$this->Topic->merge(1,2,array(), array('hasMany'=>array('Post'=>'both')));		
		$result = $this->Topic->find('first', array('conditions'=>array('Topic.id'=>1),'recursive'=>1));
		$expected = array(
			0 => array(
				'id' => 1,
				'title' => 'Rock and Roll',
				'body' => 'I love rock and roll!',
				'topic_id' => 1
			),
			1 => array(
				'id' => 2,
				'title' => 'Music',
				'body' => 'Rock and roll is cool',
				'topic_id' => 1
			),
			2 => array(
				'id' => 3,
				'title' => 'Food',
				'body' => 'Apples are good',
				'topic_id' => 1
			),
		);
		$this->assertEqual($result['Post'], $expected, 'HasMany test 4 : both : %s');			
	}
	
	function testHasManyNeither() {
		$this->Topic->merge(2,3,array(),array('Post'=>'neither'));		
		$this->assertEqual($this->Post->find('all',array('conditions'=>array('id'=>3))), array(), 'HasMany test 5 : "neither" : %s');
		
		$this->Topic->merge(1,2,array(), array('hasMany'=>array('Post'=>'neither')));		
		$this->assertEqual($this->Post->find('all'), array(), 'HasMany test 6 : "neither" : %s');				
	}
		
	function testHasManyTarget1() {
		$this->Topic->merge(2,3,array(),array('Post'=>'target'));	
		$this->assertEqual($this->Post->find('all',array('conditions'=>array('id'=>3))), array(), 'HasMany test 7 : "target" : %s');
		$result = $this->Topic->find('first', array('conditions'=>array('Topic.id'=>2),'recursive'=>1));		
		$this->assertEqual($result['Post'], array(), 'HasMany test 8 : "target" : %s');
	}
	
	function testHasManyTarget2() {
		$this->Topic->merge(1,3,array(), array('hasMany'=>array('Post'=>'target')));		
		$result = $this->Topic->find('first', array('conditions'=>array('Topic.id'=>1),'recursive'=>1));
		$expected = array(
			0 => array(
				'id' => 1,
				'title' => 'Rock and Roll',
				'body' => 'I love rock and roll!',
				'topic_id' => 1
			),
			1 => array(
				'id' => 2,
				'title' => 'Music',
				'body' => 'Rock and roll is cool',
				'topic_id' => 1
			)
		);
		$this->assertEqual($result['Post'], $expected, 'HasMany test 9 : "target" : %s');	
		$this->assertEqual($this->Post->find('all',array('conditions'=>array('id'=>3))), array(), 'HasMany test 10 : "target" : %s');	
	}
	
	function testHasManySource1() {
		$this->Topic->merge(2,3,array(),array('Post'=>'source'));	
		$result = $this->Topic->find('first', array('conditions'=>array('Topic.id'=>2),'recursive'=>1));
		$expected = array(
			0 => array(
				'id' => 3,
				'title' => 'Food',
				'body' => 'Apples are good',
				'topic_id' => 2
			),
		);		
		$this->assertEqual($result['Post'],$expected, 'HasMany test 11 : "target" : %s');
	}
	
	function testHasManySourcet2() {
		$this->Topic->merge(1,3,array(), array('hasMany'=>array('Post'=>'source')));		
		$result = $this->Topic->find('first', array('conditions'=>array('Topic.id'=>1),'recursive'=>1));
		$expected = array(
			0 => array(
				'id' => 3,
				'title' => 'Food',
				'body' => 'Apples are good',
				'topic_id' => 1
			),
		);	
		$this->assertEqual($result['Post'], $expected, 'HasMany test 12 : "target" : %s');	
		$this->assertEqual($this->Post->find('all',array('conditions'=>array('id'=>array(1,2)))), array(), 'HasMany test 13 : "source" : %s');	
	}
	
	function testHasOneDefault() {
		// Setup with hasOne
		$this->Post->id = 2;
		$this->Post->saveField('topic_id',2); //so there isnt multiple records;		
		$this->Topic->unbindModel(array('hasMany'=>array('Post')),false);
		$this->Topic->bindModel(array('hasOne'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>TRUE))),false);
		
		
		$this->Topic->merge(2,3);
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>2)));
		$expected = array(
			'id' => 2,
			'title' => 'Music',
			'body' => 'Rock and roll is cool',
			'topic_id' => 2,
		);
		$this->assertEqual($result['Post'], $expected, 'HasOne test 1 : %s');
		$this->Topic->merge(2,1);
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>2)));
		$this->assertEqual($result['Post'], $expected, 'HasOne test 2 : %s');
		$this->assertFalse($this->Post->find(array('id'=>1)), 'HasOne test 3 : %s');
		$this->assertFalse($this->Post->find(array('id'=>3)), 'HasOne test 4 : %s');
		
		$this->Topic->unbindModel(array('hasOne'=>array('Post')),false);
		$this->Topic->bindModel(array('hasMany'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>TRUE))),false);
	}

	function testHasOneTarget() {
		// Setup with hasOne
		$this->Post->id = 2;
		$this->Post->saveField('topic_id',2); //so there isnt multiple records;		
		$this->Topic->unbindModel(array('hasMany'=>array('Post')),false);
		$this->Topic->bindModel(array('hasOne'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>TRUE))),false);
		
		$this->Topic->merge(2,3, array(), array('Post'=>'target'));
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>2)));
		$expected = array(
			'id' => 2,
			'title' => 'Music',
			'body' => 'Rock and roll is cool',
			'topic_id' => 2,
		);
		$this->assertEqual($result['Post'], $expected, 'HasOne test 5 : %s');
		$this->assertFalse($this->Post->find(array('id'=>3)), 'HasOne test 6 : %s');		
		
		$this->Topic->unbindModel(array('hasOne'=>array('Post')),false);
		$this->Topic->bindModel(array('hasMany'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>TRUE))),false);
		
	}
	
	function testHasOneSource() {
		// Setup with hasOne
		$this->Post->id = 2;
		$this->Post->saveField('topic_id',2); //so there isnt multiple records;		
		$this->Topic->unbindModel(array('hasMany'=>array('Post')),false);
		$this->Topic->bindModel(array('hasOne'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>TRUE))),false);
		
		$this->Topic->merge(2,3, array(), array('Post'=>'source'));
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>2)));
		$expected = array(
			'id' => 3,
			'title' => 'Food',
			'body' => 'Apples are good',
			'topic_id' => 2,
		);
		$this->assertEqual($result['Post'], $expected, 'HasOne test 7 : source : %s');
		$this->assertFalse($this->Post->find(array('id'=>2)), 'HasOne test 8 : source : %s');	
			
		$this->Topic->merge(1,2, array(), array('Post'=>'source'));
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>1)));
		$expected['topic_id'] = 1;
		$this->assertEqual($result['Post'], $expected, 'HasOne test 9 : source : %s');
		$this->assertFalse($this->Post->find(array('id'=>1)), 'HasOne test 10 : source : %s');		
		
		$this->Topic->unbindModel(array('hasOne'=>array('Post')),false);
		$this->Topic->bindModel(array('hasMany'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>TRUE))),false);
	}
	
	function testHasOneNeither() {
		// Setup with hasOne
		$this->Post->id = 2;
		$this->Post->saveField('topic_id',2); //so there isnt multiple records;		
		$this->Topic->unbindModel(array('hasMany'=>array('Post')),false);
		$this->Topic->bindModel(array('hasOne'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>TRUE))),false);
	
		$this->Topic->merge(2,3, array(), array('Post'=>'neither'));
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>2)));
		$this->assertFalse($this->Post->find(array('topic_id'=>2)), 'HasOne test 11 : neither : %s');	
		$this->assertFalse($this->Post->find(array('id'=>2)), 'HasOne test 12 : neither : %s');	
			
		$this->Topic->merge(1,2, array(), array('Post'=>'neither'));
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>1)));
		$this->assertFalse($this->Post->find(array('topic_id'=>2)), 'HasOne test 13 : neither : %s');
		$this->assertFalse($this->Post->find(array('id'=>1)), 'HasOne test 14 : neither : %s');
				
		$this->Topic->unbindModel(array('hasOne'=>array('Post')),false);
		$this->Topic->bindModel(array('hasMany'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>TRUE))),false);
	}
	
	function testHasManyNotDependentNeither() {
		$this->Topic->bindModel(array('hasMany' => array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent' => false))), false);
		$this->Topic->merge(2, 3, array(), array('Post' => 'neither'));
		$this->Topic->merge(1, 2, array(), array('hasMany' => array('Post' => 'neither')));
		
		$expected = array(
			0 => array('Post'=>array(
				'id' => 1,
				'title' => 'Rock and Roll',
				'body' => 'I love rock and roll!',
				'topic_id' => NULL
			)),
			1 => array('Post'=>array(
				'id' => 2,
				'title' => 'Music',
				'body' => 'Rock and roll is cool',
				'topic_id' => NULL
			)),
			2 => array('Post'=>array(
				'id' => 3,
				'title' => 'Food',
				'body' => 'Apples are good',
				'topic_id' => NULL
			)),
		);
		
		$result = $this->Post->find('all',array('recursive'=>-1));
		$this->assertEqual($result, $expected, 'HasManyNotDependent : "neither" : %s');
		$this->Topic->bindModel(array('hasMany' => array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent' => true))), false);
	}
	
	function testHasManyNotDependentSourcet2() {
		$this->Topic->bindModel(array('hasMany' => array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent' => false))), false);
		
		$this->Topic->merge(1, 3, array(), array('hasMany' => array('Post' => 'source')));
		$result = $this->Topic->find('first', array('conditions' => array('Topic.id' => 1), 'recursive' => 1));

		$expected = array(
				0 => array(
						'id' => 3, 
						'title' => 'Food', 
						'body' => 'Apples are good', 
						'topic_id' => 1));
				
		$this->assertEqual($result['Post'], $expected, 'HasManyNotDependent test 12 : "target" : %s');
		
		$expected = array(
			0 => array('Post'=>array(
				'id' => 1,
				'title' => 'Rock and Roll',
				'body' => 'I love rock and roll!',
				'topic_id' => NULL
			)),
			1 => array('Post'=>array(
				'id' => 2,
				'title' => 'Music',
				'body' => 'Rock and roll is cool',
				'topic_id' => NULL
			))
		);
		
		$this->assertEqual($this->Post->find('all', array('conditions' => array('id' => array(1, 2)),'recursive'=>-1)), $expected, 'HasManyNotDependent test 13 : "source" : %s');
		$this->Topic->bindModel(array('hasMany' => array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent' => true))), false);
	}

	function testHasManyNotDependentTarget2() {
		$this->Topic->bindModel(array('hasMany' => array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent' => false))), false);
		$this->Topic->merge(1, 3, array(), array('hasMany' => array('Post' => 'target')));
		$result = $this->Topic->find('first', array('conditions' => array('Topic.id' => 1), 'recursive' => 1));
		$expected = array(
				0 => array(
						'id' => 1, 
						'title' => 'Rock and Roll', 
						'body' => 'I love rock and roll!', 
						'topic_id' => 1), 
				1 => array(
						'id' => 2, 
						'title' => 'Music', 
						'body' => 'Rock and roll is cool', 
						'topic_id' => 1));
		$this->assertEqual($result['Post'], $expected, 'HasManyNotDependent test 9 : "target" : %s');
		
		$expected = array(
			0 => array('Post'=>array(
				'id' => 3,
				'title' => 'Food',
				'body' => 'Apples are good',
				'topic_id' => NULL
			)),
		);
				
		$this->assertEqual($this->Post->find('all', array('conditions' => array('id' => 3),'recursive'=>-1)), $expected, 'HasManyNotDependent test 10 : "target" : %s');
		$this->Topic->bindModel(array('hasMany' => array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent' => true))), false);
	}
	
	function testHasManyNotDependentTarget1() {
		$this->Topic->bindModel(array('hasMany' => array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent' => false))), false);
		$this->Topic->merge(2, 3, array(), array('Post' => 'target'));
		$expected = array(
			0 => array('Post'=>array(
				'id' => 3,
				'title' => 'Food',
				'body' => 'Apples are good',
				'topic_id' => NULL
			)),
		);
		$this->assertEqual($this->Post->find('all', array('conditions' => array('id' => 3),'recursive'=>-1)), $expected, 'HasManyNotDependent test 7 : "target" : %s');

		$result = $this->Topic->find('first', array(
				'conditions' => array('Topic.id' => 2), 
				'recursive' => 1));
		$this->assertEqual($result['Post'], array(), 'HasManyNotDependent test 8 : "target" : %s');
		$this->Topic->bindModel(array('hasMany' => array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent' => true))), false);
	}

	function testHabtmDefault() {
		$this->Post->merge(1,3);
		$result = $this->Post->find('first', array('conditions'=>array('Post.id'=>1), 'recursive' => 1));
		$expected = array(
			0 => array(
				'id' 	=> 1,
				'title' => 'Fun'
			),
			1 => array(
				'id' 	=> 2,
				'title' => 'Lame'
			),
			2 => array(
				'id' 	=>3,
				'title' => 'Blue'
			)
		);
		$this->assertEqual($result['Tag'], $expected, 'HABTM test 1 : default : %s');
	}
		
	function testHabtmBoth() {
		$this->Post->merge(1,3, array('habtm'=>array('Tag'=>'both')));
		$result = $this->Post->find('first', array('conditions'=>array('Post.id'=>1), 'recursive' => 1));
		$expected = array(
			0 => array(
				'id' 	=> 1,
				'title' => 'Fun'
			),
			1 => array(
				'id' 	=> 2,
				'title' => 'Lame'
			),
			2 => array(
				'id' 	=>3,
				'title' => 'Blue'
			)
		);
		$this->assertEqual($result['Tag'], $expected, 'HABTM test 2 : both : %s');
	}
	
	function testHabtmTarget() {
		$this->Post->merge(2,1,array(),array('Tag'=>'target'));
		$result = $this->Post->find('first', array('conditions'=>array('Post.id'=>2), 'recursive' => 1));
		$expected = array(
			0 => array(
				'id' 	=> 1,
				'title' => 'Fun'
			)
		);
		$this->assertEqual($result['Tag'], $expected, 'HABTM test 3 : target : %s');
		
	}
	
	function testHabtmSource() {
		$this->Post->merge(2,1,array(),array('Tag'=>'source'));
		$result = $this->Post->find('first', array('conditions'=>array('Post.id'=>2), 'recursive' => 1));
		$expected = array(
			0 => array(
				'id' 	=> 1,
				'title' => 'Fun'
			),
			1 => array(
				'id' 	=> 2,
				'title' => 'Lame'
			)
		);
		$this->assertEqual($result['Tag'], $expected, 'HABTM test 4 : source : %s');		
	}
	
	function testHabtmNeither() {
		$this->Post->merge(2,1,array(),array('Tag'=>'neither'));
		$result = $this->Post->find('first', array('conditions'=>array('Post.id'=>2), 'recursive' => 1));
		$this->assertEqual($result['Tag'], array(), 'HABTM test 5 : neither : %s');		
	}
	
	function testHabtmJoinModelCleanUp() {
		$this->Post->merge(1,3,array(),array('Tag'=>'both'));
		$result = $this->Post->MergePostsMergeTag->find('all', array('conditions'=>array('post_id'=>1)));
		$expected = array(
			0 => array('TagTest'=> array(
					'id' 	=> 1,
					'title' => 'Fun'
				)			
			),
			1 => array('TagTest'=> array(
					'id' 	=> 2,
					'title' => 'Lame'
				)			
			),
			2 => array('TagTest'=> array(
					'id' 	=>3,
					'title' => 'Blue'
				)			
			),
		);
	//	debug($result);
		$this->assertEqual($result, $expected, "HABTM cleanup : Not to worry. Cleanup er ikke implementert.");
	}
	
	function testHasOneNotDependentDefault() {
		// Setup with hasOne
		$this->Post->id = 2;
		$this->Post->saveField('topic_id',2); //so there isnt multiple records;		
		$this->Topic->unbindModel(array('hasMany'=>array('Post')),false);
		$this->Topic->bindModel(array('hasOne'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>FALSE))),false);
		
		
		$this->Topic->merge(2,3);
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>2)));
		$expected = array(
			'id' => 2,
			'title' => 'Music',
			'body' => 'Rock and roll is cool',
			'topic_id' => 2,
		);
		$this->assertEqual($result['Post'], $expected, 'HasOneNotDependent test 1 : %s');
		$this->Topic->merge(2,1);
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>2)));
		$this->assertEqual($result['Post'], $expected, 'HasOneNotDependent test 2 : %s');
		$expected = array('Post'=>array(
				'id' => 1,
				'title' => 'Rock and Roll',
				'body' => 'I love rock and roll!',
				'topic_id' => NULL
			)
		);
		$this->assertEqual($this->Post->find('first',array('conditions'=>array('id'=>1),'recursive'=>-1)), $expected,'HasOneNotDependent test 3 : %s');
		$expected =  array('Post'=>array(
				'id' => 3,
				'title' => 'Food',
				'body' => 'Apples are good',
				'topic_id' => NULL
			)
		);
		$this->assertEqual($this->Post->find('first',array('conditions'=>array('id'=>3),'recursive'=>-1)), $expected,'HasOneNotDependent test 4 : %s');
		
		$this->Topic->unbindModel(array('hasOne'=>array('Post')),false);
		$this->Topic->bindModel(array('hasMany'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>TRUE))),false);
	}

	function testHasOneNotDependentTarget() {
		// Setup with hasOne
		$this->Post->id = 2;
		$this->Post->saveField('topic_id',2); //so there isnt multiple records;		
		$this->Topic->unbindModel(array('hasMany'=>array('Post')),false);
		$this->Topic->bindModel(array('hasOne'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>FALSE))),false);
		
		$this->Topic->merge(2,3, array(), array('Post'=>'target'));
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>2)));
		$expected = array(
			'id' => 2,
			'title' => 'Music',
			'body' => 'Rock and roll is cool',
			'topic_id' => 2,
		);
		$this->assertEqual($result['Post'], $expected, 'HasOneNotDependent test 5 : %s');
		
		$expected =  array('Post'=>array(
				'id' => 3,
				'title' => 'Food',
				'body' => 'Apples are good',
				'topic_id' => NULL
			)
		);
		$this->assertEqual($this->Post->find('first',array('conditions'=>array('id'=>3),'recursive'=>-1)), $expected, 'HasOneNotDependent test 6 : %s');		
		
		$this->Topic->unbindModel(array('hasOne'=>array('Post')),false);
		$this->Topic->bindModel(array('hasMany'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>TRUE))),false);
		
	}
	
	function testHasOneNotDependentSource() {
		// Setup with hasOne
		$this->Post->id = 2;
		$this->Post->saveField('topic_id',2); //so there isnt multiple records;		
		$this->Topic->unbindModel(array('hasMany'=>array('Post')),false);
		$this->Topic->bindModel(array('hasOne'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>FALSE))),false);
		
		$this->Topic->merge(2,3, array(), array('Post'=>'source'));
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>2)));
		$expected = array(
			'id' => 3,
			'title' => 'Food',
			'body' => 'Apples are good',
			'topic_id' => 2,
		);
		$this->assertEqual($result['Post'], $expected, 'HasOneNotDependent test 7 : source : %s');
		
		$expected = array('Post' => array(
			'id' => 2,
			'title' => 'Music',
			'body' => 'Rock and roll is cool',
			'topic_id' => NULL,
		)
		);
		$this->assertEqual($this->Post->find('first',array('conditions'=>array('id'=>2),'recursive'=>-1)), $expected, 'HasOneNotDependent test 8 : source : %s');	
			
		$this->Topic->merge(1,2, array(), array('Post'=>'source'));
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>1)));
		$expected = array(
			'id' => 3,
			'title' => 'Food',
			'body' => 'Apples are good',
			'topic_id' => 1,
		);
		$this->assertEqual($result['Post'], $expected, 'HasOneNotDependent test 9 : source : %s');
		$expected = array('Post'=>array(
				'id' => 1,
				'title' => 'Rock and Roll',
				'body' => 'I love rock and roll!',
				'topic_id' => NULL
			)
		);
		$this->assertEqual($this->Post->find('first',array('conditions'=>array('id'=>1),'recursive'=>-1)), $expected, 'HasOneNotDependent test 10 : source : %s');		
		
		$this->Topic->unbindModel(array('hasOne'=>array('Post')),false);
		$this->Topic->bindModel(array('hasMany'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>TRUE))),false);
	}
	
	function testHasOneNotDependentNeither() {
		// Setup with hasOne
		$this->Post->id = 2;
		$this->Post->saveField('topic_id',2); //so there isnt multiple records;		
		$this->Topic->unbindModel(array('hasMany'=>array('Post')),false);
		$this->Topic->bindModel(array('hasOne'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>FALSE))),false);
	
		$this->Topic->merge(2,3, array(), array('Post'=>'neither'));
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>2)));
		$this->assertFalse($this->Post->find(array('topic_id'=>2)), 'HasOneNotDependent test 11 : neither : %s');
		$expected = array('Post' => array(
			'id' => 2,
			'title' => 'Music',
			'body' => 'Rock and roll is cool',
			'topic_id' => NULL,
		)
		);
		$this->assertEqual($this->Post->find('first',array('conditions'=>array('id'=>2),'recursive'=>-1)), $expected, 'HasOneNotDependent test 12 : neither : %s');	
			
		$this->Topic->merge(1,2, array(), array('Post'=>'neither'));
		$result = $this->Topic->find('first',array('conditions'=>array('Topic.id'=>1)));
		$this->assertFalse($this->Post->find(array('topic_id'=>2)), 'HasOneNotDependent test 13 : neither : %s');
		$expected = array('Post'=>array(
				'id' => 1,
				'title' => 'Rock and Roll',
				'body' => 'I love rock and roll!',
				'topic_id' => NULL
			)
		);
		$this->assertEqual($this->Post->find('first',array('conditions'=>array('id'=>1),'recursive'=>-1)), $expected, 'HasOneNotDependent test 14 : neither : %s');
				
		$this->Topic->unbindModel(array('hasOne'=>array('Post')),false);
		$this->Topic->bindModel(array('hasMany'=>array('Post' => array('className'=>'MergePost','foreignKey'=>'topic_id','dependent'=>TRUE))),false);
	}
	
}

?>
