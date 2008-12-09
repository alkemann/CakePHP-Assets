<?php 
/**
 * A helper for embedding flash into your site using Javascript.
 * This helper is simply a wrapper for the javascript SwfObject vendor found here:
 * 
 *   http://code.google.com/p/swfobject/
 * 
 *  IMPORTANT : This helper requires you to have the line 
 *
 *    echo $scripts_for_layout;
 * 
 *  in your layout.
 * 
 *
 * It has simply two functions, one is optional. If you wish to embed several
 * flash files into your view, you can initialize the helper (include the javascript
 * library) once and also set default options for all your subsequent calls.
 *
 * Example 1 :
 * 
 * echo $flash->renderSwf('test.swf',400,200,'flashy');
 * echo '<div id="flashy"></div>';
 * 
 * Example 2 :
 * 
 * $flash->init(array('width'=>200,'height'=>100));
 * echo $flash->renderSwf('test1.swf');
 * echo $flash->renderSwf('test2swf');
 * 
 * Example 3 :
 * 
 * echo $flash->renderSwf('flashfiles/fl_countdown_v3_3.swf?mo=1&da=24&snd=off&co=AA3333',800,250,false,
 * 		array('params' => array('movie'=>'?mo=1&da=24&snd=off&co=AA3333')));
 * 
 * @author Alexander Morland
 * @license MIT
 * @version 1.3
 * @modified 28. nov. 2008
 */
class FlashHelper extends AppHelper {	
	public $helpers = array('Javascript');
	/**
	 * Used for remembering options from init() to each renderSwf
	 *
	 * @var array
	 */
	private $options = array(
		'width' => 100,
		'height' => 100
	);

	/**
	 * Used by renderSwf to set a flash version requirement
	 *
	 * @var string
	 */
	private $defaultVersionRequirement = '9.0.0';
	
	/**
	 * Used by renderSwf to only call init if it hasnt been done, either
	 * manually or automatically by a former renderSwf()
	 *
	 * @var boolean
	 */
	private $initialized = false;
	
	/**
	 * Optional initializing for setting default parameters and also includes the
	 * swf library. Should be called once, but if using several groups of flashes,
	 * MAY be called several times, once before each group.
	 *
	 * @example echo $flash->init();
	 * @example $flash->init(array('width'=>200,'height'=>100);
	 * @return mixed String if it was not able to add the script to the view, true if it was
	 */
	public function init($options = array()) {
		if (!empty($options)) {
			$this->options = am($this->options, $options);
		}
		$this->initialized = true;
        $view =& ClassRegistry::getObject('view'); 
        if (is_object($view)) { 
            $view->addScript($this->Javascript->link('swfobject')); 
            return true;
        } else {
        	return $this->Javascript->link('swfobject');
        }
	}
	
	/**
	 * Wrapper for the SwfObject::embedSWF method in the vendor. This method will write a javascript code
	 * block that calls that javascript method. If given a dom id as fourth parameter the flash will 
	 * replace that dom object. If false is given, a div will be placed at the point in the 
	 * page that this method is echo'ed. The last parameter is mainly used for sending in extra settings to
	 * the embedding code, like parameters and attributes. It may also send in flashvars to the flash. 
	 * 
	 * For doucumentation on what options can be sent, look here:
	 * http://code.google.com/p/swfobject/wiki/documentation
	 *
	 * @example echo $flash->renderSwf('counter.swf'); // size set with init();
	 * @example echo $flash->renderSwf('flash/ad.swf',100,20);
	 * @example echo $flash->renderSwf('swf/banner.swf',800,200,'banner_ad',array('params'=>array('wmode'=>'opaque')));
	 * @param string $swfFile Filename (with paths relative to webroot)
	 * @param int $width if null, will use width set by FlashHelper::init()
	 * @param int $height if null, will use height set by FlashHelper::init()
	 * @param mixed $divDomId false or string : dom id
	 * @param array $options array('flashvars'=>array(),'params'=>array('wmode'=>'opaque'),'attributes'=>array());
	 * 		See SwfObject documentation for valid options
	 * @return string
	 */
	public function renderSwf($swfFile, $width = null, $height = null, $divDomId = false, $options = array()) {
		$options = am ($this->options, $options);		
		if (is_null($width)) {
			$width = $options['width'];
		}
		if (is_null($height)) {
			$height = $options['height'];
		}
		$ret = '';
		if (!$this->initialized) {
			$init = $this->init($options);
			if (is_string($init)) {
				$ret = $init;
			}
			$this->initialized = TRUE;
		}		
		$flashvars = '{}';
		$params =  '{wmode : "opaque"}';
		$attributes = '{}';
		if (isset($options['flashvars'])) {
			$flashvars = $this->Javascript->object($options['flashvars']);
		}
		if (isset($options['params'])) {
			$params = $this->Javascript->object($options['params']);
		}
		if (isset($options['attributes'])) {
			$attributes = $this->Javascript->object($options['attributes']);
		}
	
		if ($divDomId === false) {
			$divDomId = uniqid('c_');
			$ret .= '<div id="'.$divDomId.'"></div>';
		}
		if (isset($options['version'])) {
			$version = $options['version'];
		} else {
			$version = $this->defaultVersionRequirement;			
		}
		if (isset($options['install'])) {
			$install = $options['install'];
		} else {
			$install =  '';			
		}
		
		$swfLocation = $this->webroot.$swfFile;
		$ret .= $this->Javascript->codeBlock(
			'swfobject.embedSWF("'.$swfLocation.'", "'.$divDomId.'", "'.$width.'", "'.$height.'", "'.$version.'","'.$install.'", '.$flashvars.', '.$params.', '.$attributes.');');
	
		return $ret;
	}
}
?>
