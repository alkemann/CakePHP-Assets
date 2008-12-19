<?php
/**
 * Merge behavior v.1.1
 * 
 * This behavior is an allpurpose behavior for merging two models into one, for example on a communal blog, a moderator
 * may want to merge two posts into one, joining the content body manually, keeping the title of the first and using
 * the comments from both. The behavior lets you set up a model, specifying how each field and association should be
 * handled, or you can do it when calling the merge (or you can use the defaults).
 * 
 * 
 * @author Alexander Morland (alexander#maritimecolours.no)
 * @co-author Ronny Vindenes
 * @co-author Eskil Mjelva Saatvedt
 * @co-author Carl Erik Fyllingen
 * @company Maritime Colours
 * @category Behavior
 * @version 1.1
 * @modified 18. des. 2008 by alexander morland 
 */
class MergeBehavior extends ModelBehavior  {
		
	/**
	 * Cake called intializer
	 * Config options are list of field names and instructions on how to merge:
	 * @field options : 'source', 'target_source', 'source_target', 'input' or 'target' (default is target) 
	 * @association options : 
	 * 		hasOne  : 'target', 'source', 'neither' (default is target)
	 * 		hasMany : 'target', 'source', 'neither', 'both' (default is both)
	 *      habtm	: 'target', 'source', 'neither', 'both' (default is both)
	 *
	 * @example var $actsAs = array('Merge'=>array('title'=>'target','ingress'=>'both','author_id'=>'target','hasMany'=>array('Comment'=>'both')));
	 * @param Object $Model
	 * @param array $config
	 */
	function setup(&$Model, $config = array()) {	
		if (!is_array($config)) {
			$config = array();
		}	
        $defaults = array(
            $Model->primaryKey => FALSE,
            'created' => FALSE,
            'modified' => FALSE
		);
		$this->settings[$Model->alias] = array_merge($defaults, $config);
	}
	
	function settings(&$Model) {
		return $this->settings[$Model->alias];
	}

	/**
	 * Merge two models into one, by effectivly joining them. By default target fields and associations will be used,
	 * but sources will be used where there the target isnt available or both where that is possible. You can override
	 * this on a field and association(alias) basis should you need to.
	 * 
	 * NB when merging with associations that are binded realtime, reset (2nd parameter of Model::bindModel) needs to be false
	 *
	 * @example $this->Post->merge(1,2,array('title'=>'source','author_id'=>'input'),array('Comment'=>'source'),$data);
	 * @param Object $Model
	 * @param int $target_id
	 * @param int $source_id
	 * @param array $field_options
	 * @param array $data
	 * @return boolean success
	 */
	function merge(&$Model, $target_id, $source_id, $field_options = array(), $assoc_options = array(), $data = array() ) {
		$fields = am($this->settings[$Model->alias], $field_options);
		
		$target = $Model->find('first', array('conditions'=>array($Model->primaryKey => $target_id),'recursive' => -1));
		$source = $Model->find('first', array('conditions'=>array($Model->primaryKey => $source_id),'recursive' => -1));
		
		$new_values = array();
		foreach ($fields as $key => $value) {
			switch ($value) {
				case 'source':
					$new_values[$key] = $source[$Model->alias][$key];
				break;	
				case 'target_source':
					$new_values[$key] = $target[$Model->alias][$key]."\n ".$source[$Model->alias][$key];
				break;	
				case 'source_target':
					$new_values[$key] = $source[$Model->alias][$key]."\n ".$target[$Model->alias][$key];
				break;				
				case 'input': 
					$new_values[$key] = $data[$Model->alias][$key];
				break;
				
				case 'target':
				case FALSE: 
				default:
				break;
			}
		}
		// belongsTo is taken care of above with fields
		// hasOne - default is target, options are target, source, neither
		foreach ($Model->hasOne as $assoc_model => $assoc_setup) {
			$className = $assoc_setup['className'];
			$foreignKey = $assoc_setup['foreignKey'];
			$dependent = $assoc_setup['dependent'];
			
			if ( (!isset($assoc_options[$assoc_model]) && !isset($assoc_options['hasOne'][$assoc_model])) ) {
				$use = 'target'; // default behaviour
			} else {
				if (isset($assoc_options['hasOne']) && isset($assoc_options['hasOne'][$assoc_model])) {
					$use = $assoc_options['hasOne'][$assoc_model];
				} else {
					$use = $assoc_options[$assoc_model];
				} 				 
			}
			switch ($use) {
				case 'neither': // delete or reset both
					if ($dependent) {
						$Model->{$assoc_model}->deleteAll(array($foreignKey=>array($source_id,$target_id)));
					} else {
						$Model->{$assoc_model}->updateAll(array($foreignKey=>NULL),array($foreignKey=>array($source_id,$target_id)));
					}
				break;
				case 'target': // keep old target, delete or reset assoc for source
					if ($dependent) {
						$Model->{$assoc_model}->deleteAll(array($foreignKey=>array($source_id)));
					} else {
						$Model->{$assoc_model}->updateAll(array($foreignKey=>NULL),array($foreignKey=>$source_id));
					}
					
				break;
				case 'source': // delete or reset target, use source
					if ($dependent) {
						$Model->{$assoc_model}->deleteAll(array($foreignKey=>array($target_id)));
					} else {
						$Model->{$assoc_model}->updateAll(array($foreignKey=>NULL),array($foreignKey=>$target_id));
					}
					$Model->{$assoc_model}->updateAll(array($foreignKey=>$target_id),array($foreignKey=>$source_id));					
				break;
				default:
					return FALSE;				
			}
		}
		
		// hasMany - default is both, options are : target,source,neither,both
		foreach ($Model->hasMany as $assoc_model => $assoc_setup) {
			$className = $assoc_setup['className'];
			$foreignKey = $assoc_setup['foreignKey'];
			$dependent = $assoc_setup['dependent'];
			
			if ( (!isset($assoc_options[$assoc_model]) && !isset($assoc_options['hasMany'][$assoc_model])) ) {
				$use = 'both'; // default behaviour
			} else {
				if (isset($assoc_options['hasMany']) && isset($assoc_options['hasMany'][$assoc_model])) {
					$use = $assoc_options['hasMany'][$assoc_model];
				} else {
					$use = $assoc_options[$assoc_model];
				} 				 
			}
			switch ($use) {
				case 'both':
					$Model->{$assoc_model}->updateAll(array($foreignKey=>$target_id),array($foreignKey=>$source_id));
				break;
				case 'neither':
					if ($dependent) {
						$Model->{$assoc_model}->deleteAll(array($foreignKey=>array($source_id,$target_id)));
					} else {
						$Model->{$assoc_model}->updateAll(array($foreignKey=>NULL),array($foreignKey=>array($source_id,$target_id)));						
					}					
				break;
				case 'target':
					if ($dependent) {
						$Model->{$assoc_model}->deleteAll(array($foreignKey=>array($source_id)));
					} else {
						$Model->{$assoc_model}->updateAll(array($foreignKey=>NULL),array($foreignKey=>$source_id));						
					}						
				break;
				case 'source':
					if ($dependent) {
						$Model->{$assoc_model}->deleteAll(array($foreignKey=>array($target_id)));
					} else {
						$Model->{$assoc_model}->updateAll(array($foreignKey=>NULL),array($foreignKey=>$target_id));						
					}	
					$Model->{$assoc_model}->updateAll(array($foreignKey=>$target_id),array($foreignKey=>$source_id));
				break;
				default:
					return FALSE;
			}
		}

		// HABTM - default is both, options are : target,source,neither,both
		foreach ($Model->hasAndBelongsToMany as $assoc_model => $assoc_setup) {
			$className = $assoc_setup['className'];
			$foreignKey = $assoc_setup['foreignKey'];
			//$assocForeignKey = $assoc_setup['associationForeignKey'];
			
			$joinModel = $Model->name . 's'  . $className;
			
			if ( (!isset($assoc_options[$assoc_model]) && !isset($assoc_options['habtm'][$assoc_model])) ) {
				$use = 'both'; // default behaviour
			} else {
				if (isset($assoc_options['habtm']) && isset($assoc_options['habtm'][$assoc_model])) {
					$use = $assoc_options['habtm'][$assoc_model];
				} else {
					$use = $assoc_options[$assoc_model];
				} 				 
			}
			switch ($use) {
				case 'both':
					$Model->{$joinModel}->updateAll(array($foreignKey=>$target_id),array($foreignKey=>$source_id));
				break;
				case 'neither':
					$Model->{$joinModel}->deleteAll(array($foreignKey=>array($source_id,$target_id)));						
				break;
				case 'target':
					$Model->{$joinModel}->deleteAll(array($foreignKey=>array($source_id)));					
				break;
				case 'source':
					$Model->{$joinModel}->deleteAll(array($foreignKey=>array($target_id)));
					$Model->{$joinModel}->updateAll(array($foreignKey=>$target_id),array($foreignKey=>$source_id));
				break;
				default:
					return FALSE;
			}
		}
		
		$data = $target;
		$data[$Model->alias] = array_merge($data[$Model->alias], $new_values);
		if ($Model->save($data,false)) {
			$Model->delete($source_id, false);			
		}
		return true;
	}
}
?>