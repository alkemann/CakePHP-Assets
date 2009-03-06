<?php
/** MenuHelper 1.2
 *
 * The purpose of this helper is to generate menus and other lists of links. The dynamic api
 * lets you build any amount of multi level "menus". Created for the purpose of main, sub and
 * context sensitive menues, this helper can also be used as an html UL generator.
 * 
 * Installation and requirements:
 * 
 * - Copy this file to app/views/helpers
 * - add 'Menu' to AppController's $helpers property
 *
 * Description :
 * 
 * To understand how this helper works there are two important concepts. Firstly, in a single
 * run of cakephp, only one instance of any helper is used. There for we can temporarily "store"
 * information in it (as a property) between views, elements and layouts. In the most common
 * use of this helper, links are created in the view and in elements and then the layout renders 
 * them. The reason why this works is the second important concept; the layout is rendered after
 * the view. Therefore we can add to the list of urls in when the layout is rendered, the menu
 * helper already know all that it is to render. 
 * 
 * Usage example 1: Creating a list and rendering it
 * 
 * //We have a list of links stored in the database and on this view we wish to list them out.
 * // A single link array looks like this : array(Link => array(url,title))
 * 
 * foreach ($links as $link) {
 * 		$menu->add('link_list',array($link['title'],$link['url']));
 * }
 * echo $menu->generate('link_list');
 * 
 * Usage example 2: Replacing the baked html links with a context sensitive menu
 * 
 * A typical use is compromised of several $menu->add( and a single echo $menu->generate(
 * 
 * For instance to add a context sensitive menu to replace the $html->link() baked by cake
 * in default follow these steps:
 * 
 * 1. In app/views/layouts/default.ctp just inside the #content div add this line :
 *      if (isset($menu)) { echo $menu->generate('context'); }
 * 2. Copy the files from cake/console/libs/templates/views to app/vendors/shells/templates/views
 * 3. Inside those 3 files replace 
 *      "echo $html->link("
 *         with
 *      "$menu->add('context', array("
 *         and
 *    add a ) to the end.
 * 4. Remove the div, ul and li parts that surrounded these links.
 * 5. Add this css rule ul.menu_context li { list-style: none; }
 * 
 * Now all these links that used to be echoed in the view, will be printed as a ul on top of the layout.
 *
 * Usage example 3: A multilevel list 
 * 
 * //Say we have an Article with hasMany Page, to render a list of links to both we could do :  
 * 
 * foreach ($data as $article) {
 * 		$menu->add('articles', array($article['Article']['title'], 
 * 				array('action'=>'view', $article['Article']['id'])));
 * 		foreach ($article['Page'] as $page) {
 * 			$menu->add(array('articles', $article['Article']['id']), array(
 * 				$page['Page']['title'], array('controller'=> 'pages',
 * 				'action' => 'view', $page['Page']['id'])));
 * 		}
 * }
 * echo $menu->generate('articles');
 * 
 * This will genreate this :
 * 
 * <ul>
 * <li><a href="/articles/view/1">Article 1</a></li>
 * <li><ul>
 * 	<li><a href="/pages/view/1">Page 1</a></li>
 * 	<li><a href="/pages/view/2">Page 2</a></li>
 * </ul></li>
 * <li><a href="/articles/view/2">Article 2</a></li>
 * <li><ul>
 * 	<li><a href="/pages/view/3">Page 3</a></li>
 * 	<li><a href="/pages/view/4">Page 4</a></li>
 * </ul></li>
 * </ul>
 * 
 * 
 * Customizations : 
 * 
 * If you wish to style the menus, take a look at the generated source code, each UL level
 * is given a unique class based on the target name. If you have need of more fine control,
 * you can use the $options paramter of the helpers methods to use image icons, class on
 * the A tags, id, class or style LI, UL and DIVs. See each method for specifics.
 *  
 * @author Ronny Vindenes
 * @author Alexander Morland
 * @license MIT
 * @modified 6.mar 2009
 * @version 1.2
 */
class MenuHelper extends AppHelper {
	
	var $helpers = array('Html');
	
	var $items = array('main' => array());
	
	/**
	 * Adds a menu item to a target location
	 *
	 * 
	 * @param mixed $target String or Array target notations
	 * @param array $link Array in same format as used by HtmlHelper::link()
	 * @param array $options
	 *  @options 'icon'  > $html->image() params
	 *  @options 'class' > <a class="?">
	 *  @options 'li'    > string:class || array('id','class','style')
	 *  @options 'div'	 > string:class || boolean:use || array('id','class','style') 
	 * 
	 * @return boolean successfully added
	 */
	function add($target = 'main', $link = array(), $options = array()) {
		
		if (!is_array($link) || !is_array($options) || !isset($link[0]) || !(is_array($link[0]) || is_string($link[0]))) {
			return false;
		}
		
		if (!isset($link[1])) {
			$link[1] = array();
		}
		
		if (!isset($link[2])) {
			$link[2] = array();
		}
		
		if (!isset($link[3])) {
			$link[3] = false;
		}
		
		if (!isset($link[4])) {
			$link[4] = true;
		}
		
		if (is_array($target)) {
			
			$depth = count($target);
			$menu = &$this->items;
			
			for ($i = 0; $i < $depth; $i++) {
				if (array_key_exists($target[$i], $menu)) {
					$menu = &$menu[$target[$i]];
				} else {
					$menu[$target[$i]] = array(true);
					$menu = &$menu[$target[$i]];
				}
			}
		
		} else {
			$menu = &$this->items[$target];
		}
		
		$menu[] = array($link, $options);
		
		return true;
	}
	
	/**
	 * Adds an element to a target item
	 *
	 * @param mixed $target String or Array target notations
	 * @param string $element Any string
	 * @param array $options
	 *  @options 'li'    > string:class || array('id','class','style')
	 *  @options 'div'	 > string:class || boolean:use || array('id','class','style') 
	 * 
	 * @return boolean successfully added
	 */
	function addElement($target = 'main', $element = false, $options = array()) {
		
		if ($element === false) {
			return false;
		}
		
		if (is_array($target)) {
			
			$depth = count($target);
			$menu = &$this->items;
			
			for ($i = 0; $i < $depth; $i++) {
				if (array_key_exists($target[$i], $menu)) {
					$menu = &$menu[$target[$i]];
				} else {
					$menu[$target[$i]] = array(true);
					$menu = &$menu[$target[$i]];
				}
			}
		
		} else {
			$menu = &$this->items[$target];
		}
		
		$menu[] = array(1 => $options, 2 => $element);
		
		return true;
	}
	
	/**
	 * Renders and returns the generated html for the targeted item and its element and children
	 *
	 * @param mixed $source String or Array target notations
	 * @param array $options
	 *  @options 'style' > string:predefined style name || boolean:use
	 *  @options 'class' > <ul class="?"><li><ul>..</li></ul>
	 *  @options 'id' 	 > <ul id="?"><li><ul>..</li></ul>
	 *  @options 'ul'    > string:class || array('class','style')
	 *  @options 'div'	 > string:class || boolean:use || array('id','class','style') 
	 *  @options 'active'> array('tag' => string(span,strong,etc), 'attributes' => array(htmlAttributes), 'strict' => boolean(true|false)))
	 *
	 * @example echo $menu->generate('context', array('active' => array('tag' => 'a','attributes' => array('style' => 'color:red;','id'=>'current'))));
	 * @return mixed string generated html or false if target doesnt exist
	 */
	function generate($source = 'main', $options = array()) {
		
		$out = '';
		$list = '';
		
		$ulAttributes = array();
		
		/* DOM class attribute for outer UL */
		if (isset($options['class'])) {
			$ulAttributes['class'] = $options['class'];
		} else {
			if (is_array($source)) {
				$ulAttributes['class'] = 'menu_' . $source[count($source) - 1];
			} else {
				$ulAttributes['class'] = 'menu_' . $source;
			}
		}
		
		/* DOM element id for outer UL */
		if (isset($options['id'])) {
			$ulAttributes['id'] = $options['id'];
		}
		
		/* Find source menu */
		if (is_array($source)) {
			
			$depth = count($source);
			$menu = &$this->items;
			
			for ($i = 0; $i < $depth; $i++) {
				if (array_key_exists($source[$i], $menu)) {
					$menu = &$menu[$source[$i]];
				} else {
					return false;
				}
			}
		
		} else {
			if (!isset($this->items[$source])) {
				return false;
			}
			$menu = &$this->items[$source];
		}
		if (isset($options['active'])) {
			$defaults = array( 'tag' => 'span', 'attributes' =>  array('class' => 'active'), 'strict' => true);
			$options['active'] = array_merge($defaults, $options['active']);
		}
		
		/* Generate menu items */
		foreach ($menu as $key => $item) {
			$liAttributes = array();
			$aAttributes = array();
			
			if (isset($item[1]['li'])) {
				$liAttributes = $item[1]['li'];
			}
			
			if (isset($item[0]) && $item[0] === true) {
				$menusource = $source;
				if (!is_array($menusource)) {
					$menusource = array($menusource);
				}
				$menusource[] = $key;
				/* Don't set DOM element id on sub menus */
				if (isset($options['id'])) {
					unset($options['id']);
				}
				$listitem = $this->generate($menusource, $options);
				if (empty($listitem)) {
					continue;
				}
			} elseif (isset($item[0])) {
				if (!isset($item[0][2]['title'])) {
					$item[0][2]['title'] = $item[0][0];
				}
				if (isset($options['active']['strict']) && !$options['active']['strict']) {
					$here = $this->url(array('controller' => $this->params['controller'], 'action' => $this->params['action']));
				} else {
					$here = $this->here;
				}

				$active = ($here == $this->url($item[0][1]));
				if ( $active && isset($options['active'])) {	
					$listitem = $this->Html->tag($options['active']['tag'], $item[0][0], $options['active']['attributes']);					
				} else {
					if ($active) {
						if (is_array($item[0][2])) {
							if (isset($item[0][2]['class'])) {
								$item[0][2]['class'] .= ' active';
							} else {
								$item[0][2]['class'] = 'active';
							}
						} else {
							$item[0][2] = array('class' => 'active');
						}
					}
					$listitem = $this->Html->link($item[0][0], $item[0][1], $item[0][2], $item[0][3], $item[0][4]);
				}
			} elseif (isset($item[2])) {
				$listitem = $item[2];
			} else {
				continue;
			}

			if (isset($item[1]['div']) && $item[1]['div'] !== false) {
				if (!is_array($item[1]['div'])) {
					$item[1]['div'] = array();
				}
				$listitem = $this->Html->tag('div', $listitem, $item[1]['div']);
			}
			
			$list .= $this->Html->tag('li', $listitem, $liAttributes);
		}
		
		/* Generate menu */
		$out .= $this->Html->tag('ul', $list, $ulAttributes);
		
		/* Add optional outer div */
		if (isset($options['div']) && $options['div'] !== false) {
			if (!is_array($options['div'])) {
				$options['div'] = array();
			}
			$out = $this->Html->tag('div', $out, $options['div']);
		}
		return $out;
	}

}
?>