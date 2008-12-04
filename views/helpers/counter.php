<?php
/**
 * Small wrapper for using the Flash Counter created by http://www.brandspankingnew.net
 * 
 * @author Alexander Morland
 * @version 1.0.2
 * @modified 4. des 2008
 *
 */
class CounterHelper extends AppHelper {	
	public $helpers = array('Flash');
	
	// location of the flash file relative to the webroot
	public $swf = 'flash/fl_countdown_v3_3.swf';
	
	// default options for the render method
	private $defaults = array(
		'width' => 250,
		'height' => 30,
		'bgcolor' => 'FFFFFF',
		'color' => '565656',
		'sound' => false,
		'div' => false	
	);
	
	/**
	 * The helpers only public function. Call it do use a counter.
	 * It takes in a date and style options for the flash. If the
	 * third parameter is set to true, it will also render the div
	 * the flash is put into, if not, create a div with the specified
	 * dom id (#counter by default). You can NOT style this div, as it
	 * is REPLACED by the flashembed, not put inside it. 
	 *
	 * @example echo $counter->render(array('month'=>12,'day'=>24',array('div'=>'replace_me','bgcolor'=>'FF0000', 'color'=>'33DD33')));
	 * @example echo $counter->render('2009-01-01 00:00:01');
	 * @param mixed $date Either a dateformated string or an array with
	 * 		these valid keys : 'year','month','day','hour','minute'
	 * @param array $options valid keys : 'div','width','height','bgcolor','color','sound'
	 * @param boolean $div TRUE to make helper render div tag
	 * @return string
	 */
	public function render($date, $options = array()) {	
		$options = am($this->defaults, $options);
		
		if (is_array($date)) {
			$date_array = $date;
		} else {
			$date_array = $this->toArray($date);
		}
		
		$urlparams = $this->toParams($date_array);
		$urlparams .= ($options['sound']) ? '&snd=on' : '&snd=off';
		$urlparams .= '&co='.$options['color'];
		
		$this->Flash->init();
		return $this->Flash->renderSwf($this->swf.$urlparams, $options['width'], $options['height'], $options['div'], array(
			'params' => array('wmode'=>'opaque','bgcolor'=>'#'.$options['bgcolor'],'movie'=>$urlparams)
		));
	}
	
	/**
	 * Takes in a php dateformatted string and creates a date array that is used to
	 * create the proper url params for the swf to read. If sent in an array, it
	 * will simply return it.
	 *
	 * @param string $date php date formated string
	 * @return array
	 */
	private function toArray($date) {
		$datetimeObject = new DateTime($date);
 		$ret = array(
			'month'=> $datetimeObject->format('n'),
			'day'  => $datetimeObject->format('j'),
			'hour' => $datetimeObject->format('G'),
 			'min'  => $datetimeObject->format('i')
		);
		$year = $datetimeObject->format('Y');
		if ($year !== false) {
			$ret['year'] = $year;
		}
		return $ret;
	}
	
	/**
	 * Takes a date array and converts it to the url parameters that
	 * the swf reads for its configuration.
	 *
	 * @param array $date_array 
	 * @return string
	 */
	private function toParams($date_array) {
		if (sizeof($date_array) == 0) {
			return '?mo=12&da=24';
		} 
		$ret = '?';
		$datetimeObject = new DateTime();
				
		if (!isset($date_array['month'])) {
			$date_array['month'] =$datetimeObject->format('n');
		}
		if (!isset($date_array['day'])) {
			$date_array['day'] = $datetimeObject->format('j');		
		}
		foreach (array('yr'=>'year','mo'=>'month','da'=>'day','ho'=>'hour','mi'=>'min') as $key => $type) {
			if (isset($date_array[$type]) && is_numeric($date_array[$type])) {
				$ret .= $key.'='.$date_array[$type].'&';
			}
		}
		$ret = substr($ret,0,-1);
		return $ret;
	}
}
	
	

		
?>
