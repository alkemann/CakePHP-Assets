<?php
class MultilingualBook extends CakeTestModel {
	public $name = 'MultilingualBook';
	public $alias = 'Book';
	public $actsAs = array('Multilingual' => array(
        'fields' => array('title','description')
	));
	public $hasMany = array(
		'Page' => array(
			'className'  => 'MultilingualPage',
			'foreignKey' => 'book_id'
		)
	);

}

class MultilingualPage extends CakeTestModel {
	public $name = 'MultilingualPage';
	public $alias = 'Page';
	public $actsAs = array('Multilingual' => array(
        'fields' => array('content')
	));
	public $belongsTo = array('Book' => array(
		'className' => 'MultilingualBook',
		'foreignKey'=> 'book_id'
	));
}

class MultilingualProfile extends CakeTestModel {
	public $name = 'MultilingualProfile';
	public $alias = 'Profile';
	public $actsAs = array('Multilingual' => array(
        'fields' => array('description')
	));
	public $hasOne = array('User' => array(
		'className' => 'MultilingualUser',
		'foreignKey'=> 'profile_id'
	));
}

class MultilingualUser extends CakeTestModel {
	public $name = 'MultilingualUser';
	public $alias = 'User';
	public $belongsTo = array('Profile' => array(
		'className' => 'MultilingualProfile',
		'foreignKey'=> 'profile_id'
	));
}

class MultilingualCase extends CakeTestCase {
    public $Book;
    public $Page;
	public $fixtures = array(
		'app.multilingual_book',
		'app.multilingual_books_locale',
		'app.multilingual_page',
		'app.multilingual_pages_locale',
		'app.multilingual_profile',
		'app.multilingual_profiles_locale',
		'app.multilingual_user'
	);

	function startTest() {
		$this->Book = ClassRegistry::init('MultilingualBook');
		$this->Book->recursive = -1;
	}	
	
	function endTest() {
        unset($this->Book);
        ClassRegistry::flush();
	}
	
	function testFindFirst() {
        $result = $this->Book->find(array('id'=>1));
        $expected = array(
            'Book' => array(
                'id' => 1,
        		'user_id' => 1,
                'title' => 'Sixth Book',
                'description' => 'Contents of sixth book',
                'kid' => 11,
                'locale' => 'en-us'
            )
        );
        $this->assertEqual($result,$expected,'1. Test on find first : %s');
	}
	
	function testFindFirstNob() {
        $this->Book->locale = 'no-nb';
        $result = $this->Book->find('first',array('conditions'=>array('Book.id'=>1)));
        $expected = array(
            'Book' => array(
                'id' => 1,
        		'user_id' => 1,
                'title' => 'Sjette Bok',
                'description' => 'Innhold av sjette bok',
                'kid' => 11,
                'trans_id' => 2,
                'locale' => 'no-nb'
            )
        );
        $this->assertEqual($result,$expected,'2. Test on find first with locale : %s');        
        
	}
	
	function testFindList() {
        $result = $this->Book->find('list');
        $expected = array( 
            1 => 'Sixth Book',
            2 => 'Fifth Book', 
            3 => 'First Book',
            4 => 'Second Book',
            5 => 'Third Book', 
            6 => 'Fourth Book'
        );
        $this->assertEqual($result,$expected,'3. Test find list : %s');
            
	}
	function testFindListNob() {
        $this->Book->locale = 'no-nb';
        $result = $this->Book->find('list');
        $expected = array( 
            1 => 'Sjette Bok',
            2 => 'Femte Bok', 
            3 => 'First Book',
            4 => 'Second Book',
            5 => 'Third Book', 
            6 => 'Fourth Book'
        );
        $this->assertEqual($result,$expected,'4. Test find list with locale : %s');
	
	}
	
	function testFindAll() {
        $result = $this->Book->find('all', array('order'=>'kid'));
        $expected = array(
            array('Book' => array(
                'id' => 1,
        		'user_id' => 1,
                'title' => 'Sixth Book',
                'description' => 'Contents of sixth book',
                'kid' => 11,
                'locale' => 'en-us'
            )),
            array('Book' => array(
                'id' => 2,
        		'user_id' => 1,
                'title' => 'Fifth Book',
                'description' => 'Contents of fifth book',
                'kid' => 22,
                'locale' => 'en-us'
            )),
            array('Book' => array(
                'id' => 3,
        		'user_id' => 2,
                'title' => 'First Book',
                'description' => 'Contents of first book',
                'kid' => 33,
                'locale' => 'en-us'
            )),
            array('Book' => array(
                'id' => 4,
        		'user_id' => 1,
                'title' => 'Second Book',
                'description' => 'Contents of second book',
                'kid' => 44,
                'locale' => 'en-us'
            )),
            array('Book' => array(
                'id' => 5,
        		'user_id' => 2,
                'title' => 'Third Book',
                'description' => 'Contents of third book',
                'kid' => 55,
                'locale' => 'en-us'
            )),
            array('Book' => array(
                'id' => 6,
        		'user_id' => 1,
                'title' => 'Fourth Book',
                'description' => 'Contents of fourth book',
                'kid' => 66,
                'locale' => 'en-us'
            )),
        );
        $this->assertEqual($result,$expected,'5. Test on find all with locale : %s');
	
	}
	
	function testFindAllNob() {
        $this->Book->locale = 'no-nb';
        $result = $this->Book->find('all', array('order'=>'kid'));
        $expected = array(
            array('Book' => array(
                'id' => 1,
        		'user_id' => 1,
                'title' => 'Sjette Bok',
                'description' => 'Innhold av sjette bok',
                'kid' => 11,
                'trans_id' => 2,
                'locale' => 'no-nb'
            )),
            array('Book' => array(
                'id' => 2,
        		'user_id' => 1,
                'title' => 'Femte Bok',
                'description' => 'Innhold av femte bok',
                'kid' => 22,
                'trans_id' => 1,
                'locale' => 'no-nb'
            )),
            array('Book' => array(
                'id' => 3,
        		'user_id' => 2,
                'title' => 'First Book',
                'description' => 'Contents of first book',
                'kid' => 33,
                'locale' => 'en-us'
            )),
            array('Book' => array(
                'id' => 4,
        		'user_id' => 1,
                'title' => 'Second Book',
                'description' => 'Contents of second book',
                'kid' => 44,
                'locale' => 'en-us'
            )),
            array('Book' => array(
                'id' => 5,
        		'user_id' => 2,
                'title' => 'Third Book',
                'description' => 'Contents of third book',
                'kid' => 55,
                'locale' => 'en-us'
            )),
            array('Book' => array(
                'id' => 6,
        		'user_id' => 1,
                'title' => 'Fourth Book',
                'description' => 'Contents of fourth book',
                'kid' => 66,
                'locale' => 'en-us'
            ))
        );
        $this->assertEqual($result,$expected,'6. Test on find all with locale : %s');
	
	}
	
	function testFindOnEmptyLocale() {
        $this->Book->locale = 'de-de';
        $result = $this->Book->find(array('Book.id'=>1));
        $expected = array(
            'Book' => array(
                'id' => 1,
        		'user_id' => 1,
                'title' => 'Sixth Book',
                'description' => 'Contents of sixth book',
                'kid' => 11,
                'locale' => 'en-us'
            )
        );
        $this->assertEqual($result,$expected,'7. Test on find first : %s');
        
	}
	
	function testListLocales() {
        $this->Book->id = 1;
        $result = $this->Book->locales();
        $expected = array(2=>'no-nb',3=>'es-es');
        $this->assertEqual($result,$expected, '8. Test on list locales : %s');
	}
	
	function testSave() {
        $data = array(
            'Book' => array(
                'title' => 'New book',
                'description' => 'New content',
                'kid' => 77,
        		'user_id' => 1
            )
        );
        $this->Book->create($data);
        $this->assertNoErrors('9. Test no errors on create');
        $result = $this->Book->save();
        $this->assertNoErrors('10. Test no errors on save');
        $this->assertTrue($result,'11. Test save successful');
        
        $result = $this->Book->find('first',array('conditions'=>array('kid'=>77)));
        $expected = array(
            'Book' => array(
                'id' => 7,
        		'user_id' => 1,
                'title' => 'New book',
                'description' => 'New content',
                'kid' => 77,
                'locale' => 'en-us'
            )
        );
        $this->assertEqual($result,$expected, '12. Test find on the saved : %s');
	}
	
	function testSaveNob() {
        $data = array(
            'Book' => array(
                'id' => 6,
        		'user_id' => 1,
                'title' => 'Fjerde Bok',
                'description' => 'Innholdet av fjerde bok',
            )
        );
        $this->Book->locale = 'no-nb';
        $result = $this->Book->save($data);
        $this->assertNoErrors('13. Test no errors on save');
        $this->assertTrue($result,'14. Test save successful');
        
        $this->Book->locale = null;
        $result = $this->Book->find('first',array('conditions'=>array('kid'=>66)));
        $expected = array(
            'Book' => array(
                'id' => 6,
        		'user_id' => 1,
                'title' => 'Fourth Book',
                'description' => 'Contents of fourth book',
                'kid' => 66,
                'locale' => 'en-us'
            )
        );
        $this->assertEqual($result,$expected, '15. Test find on the saved no locale : %s');
        
        $this->Book->locale = 'no-nb';
        $result = $this->Book->find('first',array('conditions'=>array('kid'=>66)));
        $expected = array(
            'Book' => array(
                'id' => 6,
        		'user_id' => 1,
                'title' => 'Fjerde Bok',
                'description' => 'Innholdet av fjerde bok',
                'kid' => 66,
                'trans_id' => 4,
                'locale' => 'no-nb'
            )
        );
        $this->assertEqual($result,$expected, '16. Test find on the saved with locale : %s');
	}	
	
	function testSaveAsEdit() {
        $this->Book->locale = 'no-nb';
        $this->Book->save(array('id'=>1,'title'=>'Edit test','description'=>'xx'));
        $result = $this->Book->LocaleModel->Find(array('trans_id'=>2));
        $expected = array(
            'Book' => array(
                'trans_id' => 2,
                'id' => 1,
                'title' => 'Edit test',
                'description' => 'xx',
                'locale' => 'no-nb'
                
            )
        );
        $this->assertEqual($result,$expected, '17. Test that it edits locale : %s');
	}

	function testDelete() {
        $this->assertNoErrors('18. Test delete no errors : %s');
        $this->Book->del(2);
        $this->assertEqual(array(),$this->Book->findById(2),'19. Tests delete removed correctly : %s');    
        $this->Book->id = 2;
        $this->assertEqual(array(),$this->Book->locales(), '20. Tests delete removed all locales : %s');
	}
	
	function testDeleteALocale() {
        $this->Book->locale = 'no-nb';
        $this->assertFalse($this->Book->delete(1),'18.5. Test delete success : %s');
        $this->Book->read();
        $this->assertEqual(sizeof($this->Book->data),1,'19.5. Tests delete with locale doesnt delete main : %s');    
        $this->Book->id = 1;
        $expected = array(3=>'es-es');
        $this->assertEqual($expected,$this->Book->locales(), '20.5. Tests delete removed only correct : %s');
        $this->assertNoErrors();
	}
	
	function testFindCount() {
        $this->assertEqual(6,$this->Book->find('count'),'22. Test find count');
        $this->assertNoErrors('21. Test no errors on find count : %s');
        $this->Book->locale = 'es';
        $this->assertEqual(6,$this->Book->find('count'),'24. Test find count with locale : %s');
        $this->assertNoErrors('23. Test no errors on find count with locale : %s');
	}
	
	function testAssoc() {
		$this->Book->recursive = 1;
		$result = $this->Book->findById(1);
		$expected = array(
		   'Book' => array(
	            'id' => 1,
        		'user_id' => 1,
	            'title' => 'Sixth Book',
	            'description' => 'Contents of sixth book',
	            'kid' => 11,
	            'locale' => 'en-us'
	        ),
			'Page' => array(
	            array(
	                    'id' => 1,
	                    'content' => 'Page 1',
	                    'book_id' => 1
	                ),
	            array(
	                    'id' => 2,
	                    'content' => 'Page 2',
	                    'book_id' => 1
	                ),
	            array(
	                    'id' => 3,
	                    'content' => 'Page 3',
	                    'book_id' => 1
	                ),
	            array(
	                    'id' => 4,
	                    'content' => 'Page 4',
	                    'book_id' => 1
	                ),
	            array(
	                    'id' => 5,
	                    'content' => 'Page 5',
	                    'book_id' => 1
	                )
			)
		);
		
        $this->assertEqual($result,$expected, '25. Test find assoc without locale : %s');
	}
	
	function testAssocNob() {
		$this->Book->recursive = 1;
		$this->Book->locale = $this->Book->Page->locale = 'no-nb';
		$result = $this->Book->findById(1);
		$expected = array(
		   'Book' => array(
	            'id' => 1,
        		'user_id' => 1,
	            'title' => 'Sjette Bok',
	            'description' => 'Innhold av sjette bok',
	            'kid' => 11,
	            'locale' => 'no-nb',
	            'trans_id' => 2
	        ),
			'Page' => array(
	            array(
	                    'id' => 1,
	                    'content' => 'Side 1',
	                    'book_id' => 1,
	           			'locale' => 'no-nb',
	          		   'trans_id' => 1
	                ),
	            array(
	                    'id' => 2,
	                    'content' => 'Side 2',
	                    'book_id' => 1,
	           			'locale' => 'no-nb',
	          		   'trans_id' => 2
	                ),
	            array(
	                    'id' => 3,
	                    'content' => 'Side 3',
	                    'book_id' => 1,
	           			'locale' => 'no-nb',
	          		   'trans_id' => 4
	                ),
	            array(
	                    'id' => 4,
	                    'content' => 'Side 4',
	                    'book_id' => 1,
	           			'locale' => 'no-nb',
	          		   'trans_id' => 5
	                ),
	            array(
	                    'id' => 5,
	                    'content' => 'Side 5',
	                    'book_id' => 1,
	           			'locale' => 'no-nb',
	          		   'trans_id' => 6
	                )
			)
		);
        $this->assertEqual($result,$expected, '25. Test find assoc without locale : %s');
	}
	
	function testAssocBelongsTo() {
		$result = $this->Book->Page->find('first',array('conditions'=>array('Page.id'=>1),'recursive'=>0));	
		$expected = array(
			'Page' => array(
			    'id' => 1,
			    'content' => 'Page 1',
			    'book_id' => 1,
	            'locale' => 'en-us'
			),
		   'Book' => array(
	            'id' => 1,
        		'user_id' => 1,
	            'title' => 'Sixth Book',
	            'description' => 'Contents of sixth book',
	            'kid' => 11,
	   //       'locale' => 'en-us'
	       )
		);
        $this->assertEqual($result,$expected, '26. Test find belongsTo without locale : %s');		
	}
/*	*/
	function testAssocBelongsToNob() {
		$this->Book->Page->locale = 'no-nb';
		$result = $this->Book->Page->find('first',array('conditions'=>array('Page.id'=>1),'recursive'=>0));	
		$expected = array(
			'Page' => array(
			    'id' => 1,
			    'content' => 'Side 1',
			    'book_id' => 1,
	           	'locale' => 'no-nb',
	          	'trans_id' => 1
			),
		   'Book' => array(
	            'id' => 1,
        		'user_id' => 1,
	            'title' => 'Sjette Bok',
	            'description' => 'Innhold av sjette bok',
	            'kid' => 11,
	           	'locale' => 'no-nb',
	          	'trans_id' => 2
	       )
		
		);
        $this->assertEqual($result,$expected, '27. Test find belongsTo with locale : %s');		
	}
	
	function testBelongsToOnNonML() {
		$User = ClassRegistry::init('MultilingualProfile');
		
		$result = $User->find('first', array('conditions'=>array('User.id'=>1),'recursive'=>1));
		$expected = array(
			'User' => array(
				'id' => 1,
	            'name' => 'Superman',
	            'profile_id' => 1
			),
			'Profile' => array(
	            'id' => 1,
	            'description' => 'Strongest man alive.',
	            'locale' => 'en-us'
			)
		);
        $this->assertEqual($result,$expected, '28. Test find belongsTo without behavior : %s');	
		
        unset($User);
        ClassRegistry::flush();
	}
	
	function testBelongsToOnNonMLspanishProfile() {
		$User = ClassRegistry::init('MultilingualUser');
		
		$user = $User->find('first', array('conditions'=>array('User.name'=>'Superman'),'recursive'=>-1));
		
		$User->Profile->locale = 'es-es';
		$profile = $User->Profile->findById($user[$User->alias]['profile_id']);
		$result = Set::merge($user,$profile);
		
		$expected = array(
			'User' => array(
				'id' => 1,
	            'name' => 'Superman',
	            'profile_id' => 1
			),
			'Profile' => array(
	            'id' => 1,
	            'description' => 'Viva el hombre más fuerte.',
	            'locale' => 'es-es',
				'trans_id' => 1
			)
		);
        $this->assertEqual($result,$expected, '29. Test find belongsTo without behavior translated : %s');	
		
        unset($User);
        ClassRegistry::flush();
	}

	function testHasOne() {		
		$Profile = ClassRegistry::init('MultilingualProfile');
		
		$result = $Profile->find('first', array('conditions'=>array('Profile.id'=>1),'recursive'=>1));
		$expected = array(
			'Profile' => array(
	            'id' => 1,
	            'description' => 'Strongest man alive.',
	            'locale' => 'en-us'
			),
			'User' => array(
				'id' => 1,
	            'name' => 'Superman',
	            'profile_id' => 1
			)
		);
        $this->assertEqual($result,$expected, '30. Test find hasOne without locale : %s');	
        
        unset($Profile);
        ClassRegistry::flush();
	}

	function testHasOneWithoutBehavior() {		
		$Profile = ClassRegistry::init('MultilingualProfile');
		$Profile->Behaviors->detach('Multilingual');
		$result = $Profile->find('first', array('conditions'=>array('Profile.id'=>2),'recursive'=>1));
		$expected = array(
			'Profile' => array(
	            'id' => 2,
	            'description' => 'Tall, dark and handsome.',
			),
			'User' => array(
				'id' => 2,
	            'name' => 'Batman',
	            'profile_id' => 2
			)
		);
        $this->assertEqual($result,$expected, '31. Test find hasOne with locale : %s');	
        
        unset($Profile);
        ClassRegistry::flush();
	}
	
	function testHasOneEs() {		
		$Profile = ClassRegistry::init('MultilingualProfile');
		$Profile->locale = 'es-es';
		$result = $Profile->find('first', array('conditions'=>array('Profile.id'=>2),'recursive'=>1));
		$expected = array(
			'Profile' => array(
	            'id' => 2,
	            'description' => 'Alto, guapo y oscuro.',
	            'locale' => 'es-es',
				'trans_id' => 2
			),
			'User' => array(
				'id' => 2,
	            'name' => 'Batman',
	            'profile_id' => 2
			)
		);
        $this->assertEqual($result,$expected, '32. Test find hasOne with locale : %s');	
        
        unset($Profile);
        ClassRegistry::flush();
	}

	function testHasOneTranslated() {
		$this->Book->unbindModel(array('hasMany'=>array('Page')));
		$this->Book->bindModel(array('hasOne'=>array('Page'=>array('className' => 'MultilingualPage', 'foreignKey'=>'book_id'))));
		
		$this->Book->locale = $this->Book->Page->locale = 'no-nb';
		$this->Book->recursive = 1;
		$result = $this->Book->find('first');
		$this->assertEqual($result['Book']['title'],'Sjette Bok');
		$this->assertEqual($result['Page']['content'],'Side 1');
		$this->assertEqual($result['Book']['locale'],'no-nb');
		$this->assertEqual($result['Page']['locale'],'no-nb');
	}
	
	function testHasOneTranslated2() {
		$this->Book->unbindModel(array('hasMany'=>array('Page')));
		$this->Book->bindModel(array('hasOne'=>array('Page'=>array('className' => 'MultilingualPage', 'foreignKey'=>'book_id'))));
		
		$this->Book->locale = $this->Book->Page->locale = 'no-nb';
		$this->Book->recursive = 1;
		$result = $this->Book->find('all', array('conditions'=>array('Book.id'=>array(1,2))));
		$this->assertEqual($result[0]['Book']['title'],'Sjette Bok');
		$this->assertEqual($result[0]['Page']['content'],'Side 1');
		$this->assertEqual($result[0]['Book']['locale'],'no-nb');
		$this->assertEqual($result[0]['Page']['locale'],'no-nb');
		$this->assertEqual($result[1]['Book']['title'],'Femte Bok');
		$this->assertEqual($result[1]['Page']['content'],'Side 6');
		$this->assertEqual($result[1]['Book']['locale'],'no-nb');
		$this->assertEqual($result[1]['Page']['locale'],'no-nb');
	}
	
	function testModelField() {
        $this->Book->id = 1;
        $this->assertEqual($this->Book->field('title'),'Sixth Book');
	}
	
	function testModelFieldEs() {
        $this->Book->id = 1;
		$this->Book->setLocale('no-nb');
        $this->assertEqual($this->Book->field('title'),'Sjette Bok');
	}
	function testModelFieldNob() {
        $this->Book->id = 1;
		$this->Book->setLocale('es-es');
        $this->assertEqual($this->Book->field('description'),'Sumario del sexto libro');
	}
/***/
}
?>
