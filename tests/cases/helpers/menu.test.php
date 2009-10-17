<?php
App::import('Core', array('Helper', 'ClassRegistry', 'AppHelper', 'Controller'));
App::import('Helper', array('Html', 'Menu'));
class TheMenuTestController extends Controller {
	var $name = 'TheMenuTest';
	var $uses = null;
}

class MenuHelperTest extends CakeTestCase {

	function setup() {
		$this->Menu = new MenuHelper();
		$this->Menu->Html = new HtmlHelper();
		$view =& new View(new TheMenuTestController());
		ClassRegistry::addObject('view', $view);
	}

    function testNestedWithOne() {	
    	$this->Menu->add('main', array('Home','/',array('title' => 'Go Home')));
    	$result = $this->Menu->generate('main');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    	'<li', 
	    	array('a' => array('href' => '/', 'title' => 'Go Home')) , 'Home', '/a',
	    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }
    
    function testNestedWithTwo() {
    	$this->Menu->add('main', array('Home','/'));
    	$this->Menu->add('main', array('About','/about'));
    	$result = $this->Menu->generate('main');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    	'<li', array('a' => array('href'=>'/', 'title' => 'Home'))		 , 'Home' , '/a', '/li',
	    	'<li', array('a' => array('href'=>'/about', 'title' => 'About')) , 'About' , '/a', '/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }   
    
    function testUlAttributes() {
    	$this->Menu->add('main', array('Home','/'));
    	$result = $this->Menu->generate('main', array(
    		'id' 	=> 'menu',
    		'class'	=> 'nese'    		
    	));
    	$expected = array(
	    	array('ul' => array('id'=>'menu', 'class'=>'nese')),
	    	'<li', 
	    	array('a' => array('href'=>'/', 'title' => 'Home')) , 'Home' , '/a', 
	    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }   

    function testAattributes() {
    	$this->Menu->add(
    		'main', 
    		array(
    			'Home',
    			'/',
    			array('id' => 'home', 'class' => 'homeclass', 'style' => 'text-decoration:none;')
    		)
    	);
    	$result = $this->Menu->generate('main');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
    			'<li',
	    			array('a' => array('href'=>'/', 'title' => 'Home','id'=>'home', 'class'=>'homeclass', 'style' => 'text-decoration:none;')), 
	    				'Home', 
	    			'/a', 
	    		'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    	
    }
    
    function testLiAttributes() {
    	$this->Menu->add('main', array('Home','/'),
    		array('li' => array('id' => 'homeli', 'class' => 'homeliclass', 'style' => 'width:100%'))
    	);
    	$result = $this->Menu->generate('main');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    	array('li' => array('id'=>'homeli', 'class'=>'homeliclass', 'style' => 'width:100%')),
	    	array('a' => array('href'=>'/', 'title' => 'Home')) , 'Home' , '/a', 
	    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testADiv() {
    	$this->Menu->add('main', array('Home','/'),array('div' => array('id'=>'divven','class'=>'divs','style'=>'width:50%;')));
    	$result = $this->Menu->generate('main');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    	'<li',
    		array('div' => array('id'=>'divven','class'=>'divs','style'=>'width:50%;')),
	    	array('a' => array('href'=>'/', 'title' => 'Home')) , 'Home' , '/a', 
	    	'/div',
	    	'/li',
	    	'/ul',
    	);
    	$this->assertTags($result, $expected);
    	
    }
    
    function testUlDiv() {
    	$this->Menu->add('main', array('Home','/'));
    	$result = $this->Menu->generate('main',array('div' => TRUE));
    	$expected = array(
    		'<div',
	    	array('ul' => array('class' => 'menu_main')),
	    	'<li',
	    	array('a' => array('href'=>'/', 'title' => 'Home')) , 'Home' , '/a', 
	    	'/li',
	    	'/ul',
	    	'/div'
    	);
    	$this->assertTags($result, $expected);
    	$result = $this->Menu->generate('main',array('div' => array('id'=>'divven','class'=>'divs','style'=>'width:50%;')));
    	$expected = array(
    		array('div' => array('id'=>'divven','class'=>'divs','style'=>'width:50%;')),
	    	array('ul' => array('class' => 'menu_main')),
	    	'<li',
	    	array('a' => array('href'=>'/', 'title' => 'Home')) , 'Home' , '/a', 
	    	'/li',
	    	'/ul',
	    	'/div'
    	);
    	$this->assertTags($result, $expected);    	
    }
    
    function testAllAttributes() {    	
    	$this->Menu->add('main', array('Home', '/', array('class' => 'link')),
    		array(
    			'div' => array('id'=>'diven','class'=>'divs','style'=>'width:50%;'),
    			'li' => array('id'=>'mainli','class'=>'lis','style'=>'width:100%;')
    		));
    	$this->Menu->add('main', array('About', '/about', array('title'=>'About us', 'style' => 'display:block;')),
    		array(
    			'div' => array('id'=>'divto','class'=>'divs','style'=>'width:50%;'),
    			'li' => array('class'=>'lis','style'=>'width:100%;')
    		));
    	$this->Menu->add('main', array('Words',	array('controller'=>'words','action'=>'index'), array('class'=>'link')),
    		array(
    			'div' => array('id'=>'divtre','class'=>'divs','style'=>'width:50%;'),
    			'li' => array('class'=>'lis','style'=>'width:100%;')
    		));
    	$result = $this->Menu->generate('main', array(
    		'id'    => 'menu',
    		'class' => 'uls', 
    		'ul'    => array('class' => 'uls'),
    		'div'   => array('id' => 'menudiv', 'style' => 'width:800px')
    	));
    	$expected = array(
    		array('div' => array('id' => 'menudiv', 'style' => 'width:800px')),
    			array('ul' => array('class' => 'uls','id' => 'menu')),	
    			
    				array('li' => array('id' => 'mainli', 'class'=>'lis','style'=>'width:100%;')),
    					array('div' => array('id' => 'diven', 'class'=>'divs','style'=>'width:50%;')),    	
	   	 					array('a' => array('href'=>'/', 'class'=> 'link', 'title' => 'Home')) , 'Home' , '/a', 
	    				'/div',
	    			'/li',
	   	 					
    				array('li' => array('class'=>'lis','style'=>'width:100%;')),
    					array('div' => array('id' => 'divto', 'class'=>'divs','style'=>'width:50%;')),    	
	   	 					array('a' => array('href'=>'/about', 'title' => 'About us', 'style' => 'display:block;')) , 'About' , '/a', 
	    				'/div',
	    			'/li',
	   	 					
    				array('li' => array('class'=>'lis','style'=>'width:100%;')),
    					array('div' => array('id' => 'divtre', 'class'=>'divs','style'=>'width:50%;')),    	
	   	 					array('a' => array('href'=>'/words/', 'class'=> 'link', 'title' => 'Words')) , 'Words' , '/a', 
	    				'/div',
	    			'/li',
	   	 					
	    		'/ul',
	   	 	'/div',
    	);
    	$this->assertTags($result, $expected);
    	
    }

    function testFunkyURL() {
    	$this->Menu->add('main', array('About us','/pages/about/me',array('title' => 'About')));
    	$this->Menu->add('main', array('Example','http://example.org'));
    	$this->Menu->add('main', array('delete',array('controller'=>'users','action'=>'delete',4,'admin'=>true)));
    	$result = $this->Menu->generate();
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    	'<li', 
	    	array('a' => array('href' => '/pages/about/me', 'title' => 'About')) , 'About us', '/a',
	    	'/li',
	    	'<li',
	    	array('a' => array('href' => 'http://example.org', 'title' => 'Example')) , 'Example', '/a',
	    	'/li',
	    	'<li',
	    	array('a' => array('href' => '/admin/users/delete/4', 'title' => 'delete')) , 'delete', '/a',
	    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }
      
    function testAddElement() {
    	$this->Menu->addElement('main','<p>Stuff</p>');
    	$this->Menu->addElement('main','<div><p>stuff</p></div>');
    	$result = $this->Menu->generate();
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    		'<li',
		    		'<p',
		    			'Stuff',
		    		'/p',
	    		'/li',
	    		'<li',
	    			'<div',
		    			'<p',
	    					'stuff',
		    			'/p',
	    			'/div',
	    		'/li',
    		'/ul'
    	);    	
    	$this->assertTags($result, $expected);     	
    }
           
    function testAddElementToSubMenu() {
    	$this->Menu->addElement(array('main','sub','subsub'),'<p>Stuff</p>');
    	$this->Menu->addElement(array('main','sub'),'<div><p>stuff</p></div>');
    	$result = $this->Menu->generate();
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    		'<li',
			    	array('ul' => array('class' => 'menu_sub')),
			    		'<li',
					    	array('ul' => array('class' => 'menu_subsub')),
					    		'<li',
						    		'<p',
						    			'Stuff',
						    		'/p',
					    		'/li',
					    	'/ul',
			    		'/li',
			    		'<li',
			    			'<div',
				    			'<p',
			    					'stuff',
				    			'/p',
			    			'/div',
			    		'/li',
			    	'/ul',
	    		'/li',
    		'/ul'
    	);    	
    	$this->assertTags($result, $expected);     	
    }
       
    function testOneLevelTarget() {
    	$this->Menu->add('sub', array('Home','/',array('title' => 'Go Home')));
    	$result = $this->Menu->generate('sub');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_sub')),
	    	'<li', 
	    	array('a' => array('href' => '/', 'title' => 'Go Home')) , 'Home', '/a',
	    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    	$result = $this->Menu->generate('main');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);    	
    }

    function testTwoLevelTarget() {
    	$this->Menu->add('main', array('Home','/'));
    	$this->Menu->add(array('main', 'sub'), array('About','/about'));
    	$result = $this->Menu->generate();
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
		    	'<li', 
		    		array('a' => array('href' => '/', 'title' => 'Home')) , 'Home', '/a',
		    	'/li',
		    	'<li',
		    		array('ul' => array('class' => 'menu_sub')), 
		    			'<li',
		    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
		    			'/li',
		    		'/ul',
		    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected); 	
    }
    
    function testTwoLevelMultiTarget() {
    	$this->Menu->add('main', array('Home','/'));
    	$this->Menu->add(array('main', 'sub'), array('About','/about'));
    	$this->Menu->add(array('main', 'side'), array('Contact','/contact'));
    	$result = $this->Menu->generate();
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
		    	'<li', 
		    		array('a' => array('href' => '/', 'title' => 'Home')) , 'Home', '/a',
		    	'/li',
		    	'<li',
		    		array('ul' => array('class' => 'menu_sub')), 
		    			'<li',
		    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
		    			'/li',
		    		'/ul',
		    	'/li',
		    	'<li',
		    		array('ul' => array('class' => 'menu_side')), 
		    			'<li',
		    				array('a' => array('href' => '/contact', 'title' => 'Contact')) , 'Contact', '/a',
		    			'/li',
		    		'/ul',
		    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected); 	
    }
    
    function testFourLevelMultiTarget() {
    	$this->Menu->add('main', array('Home','/'));
    	$this->Menu->add(array('main', 'sub'), array('About','/about'));
    	$this->Menu->add(array('main', 'sub','subsub'), array('About','/about'));
    	$this->Menu->add(array('main', 'side'), array('Home','/'));
    	$this->Menu->add(array('main', 'side', 'sub'), array('About','/about'));
    	$this->Menu->add(array('main', 'side', 'sub', 'sidesubsub'), array('About','/about'));
    	$result = $this->Menu->generate();
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
		    	'<li', 
		    		array('a' => array('href' => '/', 'title' => 'Home')) , 'Home', '/a',
		    	'/li',
		    	'<li',
		    		array('ul' => array('class' => 'menu_sub')), 
		    			'<li',
		    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
		    			'/li',
				    	'<li',
				    		array('ul' => array('class' => 'menu_subsub')), 
				    			'<li',
				    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
				    			'/li',
				    		'/ul',
				    	'/li',
		    		'/ul',
		    	'/li',
		    	'<li',
		    		array('ul' => array('class' => 'menu_side')), 
		    			'<li',
		    				array('a' => array('href' => '/', 'title' => 'Home')) , 'Home', '/a',
		    			'/li',
				    	'<li',
				    		array('ul' => array('class' => 'menu_sub')), 
				    			'<li',
				    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
				    			'/li',
						    	'<li',
						    		array('ul' => array('class' => 'menu_sidesubsub')), 
						    			'<li',
						    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
						    			'/li',
						    		'/ul',
						    	'/li',
				    		'/ul',
				    	'/li',
		    		'/ul',
		    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected); 	
    }

    function testTwoLevelId() {
    	$this->Menu->add('main', array('Home','/'));
    	$this->Menu->add(array('main', 'sub'), array('About','/about'));
    	$result = $this->Menu->generate('main',array('id' => 'main_menu_id'));
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main','id' => 'main_menu_id')),
		    	'<li', 
		    		array('a' => array('href' => '/', 'title' => 'Home')) , 'Home', '/a',
		    	'/li',
		    	'<li',
		    		array('ul' => array('class' => 'menu_sub')), 
		    			'<li',
		    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
		    			'/li',
		    		'/ul',
		    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected); 	
    }  

    function testForcedErrors() {
    	$this->assertFalse($this->Menu->generate('sub'));
    	$this->assertFalse($this->Menu->generate('side'));
    	$this->assertFalse($this->Menu->generate(array('side')));
    	$this->assertFalse($this->Menu->generate(array('side','sub')));
    	$this->assertFalse($this->Menu->generate(array('side','sub','subs','sub')));
    	$result = $this->Menu->generate('main',array('id' => 'main_menu_id'));
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main','id' => 'main_menu_id')),
	    	'/ul'
    	);
    	$this->assertTags($result, $expected); 	
    }
    
    function testFunkyParams() {
    	$this->assertFalse($this->Menu->add(34,FALSE));
    	$this->assertFalse($this->Menu->add(34,array(FALSE)));
    	$this->assertFalse($this->Menu->addElement(array(FALSE,Array('null'),array(FALSE))));
    	$res = $this->Menu->generate(34);
    	$this->assertNoErrors();
    }
}





















