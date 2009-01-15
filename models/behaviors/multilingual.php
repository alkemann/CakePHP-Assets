<?php
/**
 * Translation behavior. 
 * 
 * @license MIT
 * @url http://code.google.com/p/alkemann
 * @author Alexander Morland aka alkemann
 * @author Ronny Vindenes 
 * @modified 2. january 2009
 * @version 1.0
 */
class MultilingualBehavior extends ModelBehavior {
	/**
	 * Behavior settings
	 * 
	 * @access public
	 * @var array
	 */
	public $settings = array();
	/**
	 * Shadow table prefix
	 * Only change this value if it causes table name crashes
	 *
	 * @access private
	 * @var string
	 */
	private $suffix = '_locales';
	/**
	 * Defaul setting values
	 *
	 * @access private
	 * @var array
	 */
    private $defaults = array(
        'default' => 'en-us',
    	'fields' => null,
    	'useDbConfig' => null,
    	'model' => false
    );

    /**
     * Configure the behavior through the Model::actsAs property
     *
     * @param object $Model
     * @param array $config
     */
	public function setup(&$Model, $config = null) {	
		if (is_array($config)) {
			$this->settings[$Model->alias] = array_merge($this->defaults, $config);			
		} else {
			$this->settings[$Model->alias] = $this->defaults;
		}		
		if (is_null($this->settings[$Model->alias]['fields'])) {
			$this->settings[$Model->alias]['fields'] = array();
			foreach($Model->_schema as $field => $arr) {
				if ($arr['type'] == 'string' || $arr['type'] == 'text') {
					$this->settings[$Model->alias]['fields'][] = $field;
				}
			}
			if (empty($this->settings[$Model->alias]['fields'])) {
				$this->settings[$Model->alias]['fields'] = null;
			}
		}
		$Model->locale = null; 
		$Model->LocaleModel = false;
		$this->createLocaleModel($Model);
		if (!$Model->LocaleModel) {
			#$Model->Behaviors->detach('Multilingual');
			#if this dont work. cant add behavior to appmodel
		}
	}
		
	/**
	 * Get a list of locales that the currently Model->id is translated to.
	 *
	 * @param object $Model 
	 * @return array list of locales that this Model->id is translated to
	 */
	public function locales(&$Model) {
        if (empty($Model->id)) {
            return null;
        }
        $Model->LocaleModel->displayField = 'locale';
        $list = $Model->LocaleModel->find('list',array('conditions'=>array($Model->primaryKey=>$Model->id)));
        return $list;
	}
	
    /**
     * Sets the given locale to the mode. Uses the default if no param given.
     *
     * @param object $Model
     * @param string $locale
     * @return string the locale set. ie can use this to get default
     */
	public function setLocale(&$Model, $locale = null) {
		if (!is_string($locale)) {
			$locale = $this->settings[$Model->alias]['default'];
		}
		$Model->locale = $locale;
		return $locale;
	}

		
	/**
     * When a model row is deleted, this will delete locales for that Id
     *
     * @param object $Model
     */
	public function afterDelete(&$Model) {
		if ($Model->LocaleModel) {
        	$Model->LocaleModel->deleteAll(array($Model->primaryKey=>$Model->id));
		}
	}
	
	/**
	 * If locale is set, assumes the result have joined locale data and will merge the results 
	 *
	 * @param object $Model
	 * @param array $result
	 * @return array modified result
	 */
    public function afterFind(&$Model, $result, $primary = false) {
        if (  empty($result) || $this->type == 'count') {           // !$Model->LocaleModel ||
            return $result;
        }
        if (is_string($Model->locale) && $Model->locale != $this->settings[$Model->alias]['default']) { 
			$localeAlias = 'I18n__'.$Model->alias.'__'.$Model->locale;
        	foreach ($result as $key => $data) {
        		if (empty($data[$localeAlias]['locale'])) {
        			$result[$key][$Model->alias]['locale'] = $this->settings[$Model->alias]['default'];
        			unset($result[$key][$localeAlias]);
        		} else {       			
        			foreach ($data[$localeAlias] as $field => $value) {
        				$result[$key][$Model->alias][$field] = $value;
        			}
        			unset($result[$key][$localeAlias]);
        		}
        		if (isset($this->related['belongsTo'])) {
	        		foreach ($this->related['belongsTo'] as $assocAlias => $assoc) {
	        			$Model->$assocAlias->locale = $Model->locale;
	        			$Model->$assocAlias->recursive = $Model->recursive - 1;
	        			// could join if "end of the line", must find if this model also has associated models. can check recursive
	        			$find_options = array(
	        				'conditions' => array(
	        				$Model->$assocAlias->alias.'.'.$Model->$assocAlias->primaryKey => $result[$key][$Model->alias][$assoc['foreignKey']]
	        			));
	        			if (isset($assoc['contain'])) {
	        				$find_options['contain'] = $assoc['contain'];
	        			}
	        			$subdata = $Model->$assocAlias->find('first',$find_options);
	        			
						$result[$key][$assocAlias] = $subdata[$assocAlias];  
						unset($subdata[$assocAlias]);
						if (!empty($subdata)) {
							$result[$key][$assocAlias] = am($result[$key][$assocAlias],$subdata);
						}
	        		}
        		}
        		if (isset($this->related['hasOne'])) {
	        		foreach ($this->related['hasOne'] as $assocAlias => $assoc) {
	        			$Model->$assocAlias->locale = $Model->locale;
	        			$Model->$assocAlias->recursive = $Model->recursive - 1;
	        			// could join if "end of the line", must find if this model also has associated models. can check recursive
	        			$find_options = array(
	        				'conditions' => array(
	        				$assoc['foreignKey'] => $result[$key][$Model->alias][$Model->primaryKey]
	        			));
	        			if (isset($assoc['contain'])) {
	        				$find_options['contain'] = $assoc['contain'];
	        			}
	        			$subdata = $Model->$assocAlias->find('first',$find_options);
						if (!empty($subdata)) {
							$result[$key][$assocAlias] = $subdata[$assocAlias];
						}
	        		}
        		}
        		if (isset($this->related['hasMany'])) {
	        		foreach ($this->related['hasMany'] as $assocAlias => $assoc) {
	        			$Model->$assocAlias->locale = $Model->locale;
	        			$Model->$assocAlias->recursive = $Model->recursive - 1;
	        			$find_options = array(
	        				'conditions' => array(
	        				$assoc['foreignKey'] => $result[$key][$Model->alias][$Model->primaryKey]
	        			));
	        			if (isset($assoc['contain'])) {
	        				$find_options['contain'] = $assoc['contain'];
	        			}
	        			$subdata = $Model->$assocAlias->find('all',$find_options);
	        			foreach($subdata as $a_key => $a_record) {
						    unset($subdata[$a_key][$assocAlias]);
						    $subdata[$a_key] = am($a_record[$assocAlias], $subdata[$a_key]);
						}						 
						$result[$key][$assocAlias] =  $subdata;
	        		}
        		}
        	}
        } else {       
            foreach ($result as $key => $data) {
                $result[$key][$Model->alias]['locale'] = $this->settings[$Model->alias]['default'];
            } 
         
        }      
        return $result;
    }
   
    /**
     * Will save localeData if it is set in beforeSave
     *
     * @param object $Model
     * @param boolean $created true if an add and false on edit
     */
    public function afterSave(&$Model, $created) {
        if (!$Model->LocaleModel || $created) {
            return true;
        }
        if (isset($Model->localeData) && !empty($Model->localeData)) {
            $exist = $Model->LocaleModel->find('first', array('conditions'=>array(
                    'locale' => $Model->locale,
                    $Model->primaryKey => $Model->id
                ),
                'fields' => array(
                	$Model->LocaleModel->primaryKey,
                	'locale',
                	$Model->primaryKey
                )
            ));
            $data = array($Model->alias => $Model->localeData);
            $Model->LocaleModel->create($data);
            $Model->LocaleModel->set('locale', $Model->locale);
			$Model->logableAction['Multilingual'] = 'translation added';
            if (!empty($exist)) {
				$Model->logableAction['Multilingual'] = 'translation edited';
            	$id = $exist[$Model->alias][$Model->LocaleModel->primaryKey];
                $Model->LocaleModel->set($Model->LocaleModel->primaryKey, $id);
				$Model->LocaleModel->id = $id;              
            }
            $Model->LocaleModel->save();
            unset($Model->localeData);
        }
    }
       
    /**
     * Deletes locale and not live data if Model::locale is set. 
     * Always return true when deleting locales.
     *
     * @param unknown_type $Model
     * @return boolean false if deleting a locale
     */
	public function beforeDelete(&$Model) {
		if (is_string($Model->locale) && $Model->locale != $this->settings[$Model->alias]['default'] ) {	
			$localeData = $Model->LocaleModel->find('first', array(
				'fields' => array('trans_id',$Model->primaryKey),
				'conditions' => array(
					'locale' => $Model->locale,
					$Model->primaryKey => $Model->id
				),
				'recursive' => -1
			));			
			if (!empty($localeData)) {
				$success = $Model->LocaleModel->del($localeData[$Model->LocaleModel->alias]['trans_id']);
				if ($success && $Model->Behaviors->attached('Logable')) {
					$Model->customLog('translation deleted',$Model->id,array(
						
					));
				}
			}	
			return false;
		}
		return true;
	}
	
    /**
     * If locale is set and find type is first all or list, will join in the locales table
     *
     * @param object $Model
     * @param array $query
     * @return array $query
     */
	function beforeFind(&$Model, $query) {
		$this->type = $Model->findQueryType;
		if (is_string($Model->locale) && $Model->locale != $this->settings[$Model->alias]['default'] && $this->type == 'first' && is_string($query['fields']) && in_array($query['fields'],$this->settings[$Model->alias]['fields'])) {			
            if (!isset($Model->LocaleModel) || !$Model->LocaleModel) {
				$this->createLocaleModel($Model);				
			}
			$localeAlias = 'I18n__'.$Model->alias.'__'.$Model->locale;
			$db = ConnectionManager::getDataSource($Model->LocaleModel->useDbConfig);
			$tablePrefix = $db->config['prefix'];
			$field = $query['fields'];
			$query['fields'] = array($Model->alias.'.'.$query['fields']);
			$query['fields'][] = $localeAlias.'.'.$field;
			$query['fields'][] = $localeAlias.'.locale';
			$query['fields'][] = $localeAlias.'.trans_id';
			$query['joins'][] = array(
				'type' => 'LEFT',
				'alias' => $localeAlias,
				'table' => '`'.$tablePrefix.$Model->LocaleModel->table.'`',  
				'conditions' => array(
					$Model->alias . '.' . $Model->primaryKey => $db->identifier($localeAlias.'.'.$Model->primaryKey),
					$localeAlias.'.locale' => $Model->locale
				)
			);
		} elseif (is_string($Model->locale) && $Model->locale != $this->settings[$Model->alias]['default'] && in_array($this->type,array('first','all','list')) ) {
			if (!isset($Model->LocaleModel)) { // || !$Model->LocaleModel
				debug($Model->alias);
				$this->createLocaleModel($Model);				
			}
			if ($Model->LocaleModel) {
				$localeAlias = 'I18n__'.$Model->alias.'__'.$Model->locale;
				$db = ConnectionManager::getDataSource($Model->useDbConfig);
				$tablePrefix = $db->config['prefix'];
				if (empty($query['fields'])) {
					$query['fields'] = array($Model->alias.'.*');
				} elseif (is_string($query['fields'])) {
					$query['fields'] = array($query['fields']);
				}
				foreach ($this->settings[$Model->alias]['fields'] as $key => $field) {
					$query['fields'][] = $localeAlias.'.'.$field;
				}
				$query['fields'][] = $localeAlias.'.locale';
				$query['fields'][] = $localeAlias.'.trans_id';
				$query['joins'][] = array(
					'type' => 'LEFT',
					'alias' => $localeAlias,
					'table' => '`'.$tablePrefix.$Model->LocaleModel->table.'`',  
					'conditions' => array(
						$Model->alias . '.' . $Model->primaryKey => $db->identifier($localeAlias.'.'.$Model->primaryKey),
						$localeAlias.'.locale' => $Model->locale
					)
				);
			}
			$this->related = array();					
			if (!empty($query['recursive']) || !empty($Model->recursive)) {
				$recursive = empty($query['recursive']) ? $Model->recursive : $query['recursive'];
				$Model->recursive = $recursive;
				if ($recursive > -1) {
					foreach ($Model->belongsTo as $assocAlias => $assocArray) {
						if (!isset($query['contain']) || (isset($query['contain']) && isset($query['contain'][$assocAlias])))
							if (isset($Model->{$assocAlias}->Behaviors->Multilingual)) {
								$Model->unbindModel(array('belongsTo'=>array($assocAlias)),true);
								if (isset($query['contain'][$assocAlias])) {
									$assocArray['contain'] = $query['contain'][$assocAlias];
								}								
								$this->related['belongsTo'][$assocAlias] = $assocArray;
							} else {
								if (empty($assocArray['fields'])) {
									$query['fields'][] = $assocAlias.'.*';
								} else {
									foreach ($assocArray['fields'] as $field) {
										$query['fields'][] = $assocAlias.'.'.$field;
									}
								}
								
							}
					}
				}
				if ($recursive > 0) {
					foreach ($Model->hasOne as $assocAlias => $assocArray) {
					if (isset($Model->{$assocAlias}->Behaviors->Multilingual)) {
							$Model->unbindModel(array('hasOne'=>array($assocAlias)),true);
							$this->related['hasOne'][$assocAlias] = $assocArray;
						} else {
							if (empty($assocArray['fields'])) {
								$query['fields'][] = $assocAlias.'.*';
							} else {
								foreach ($assocArray['fields'] as $field) {
									$query['fields'][] = $assocAlias.'.'.$field;
								}
							}
						}
					}
					foreach ($Model->hasMany as $assocAlias => $assocArray) {						
						
						
						if (isset($Model->{$assocAlias}->Behaviors->Multilingual)) {
							$Model->unbindModel(array('hasMany'=>array($assocAlias)),true);
							$this->related['hasMany'][$assocAlias] = $assocArray;
							if (isset($query['contain']) && isset($query['contain'][$assocAlias])) {
								$this->related['hasMany'][$assocAlias]['contain'] = $query['contain'][$assocAlias];
							}
						} 
					}
				}
			}
		}
        return $query;
	}

    /**
     * If LocaleModel and locale is set, will save these fields 
     * and allow any other fields to continue on with the save process.
     *
     * @param object $Model
     * @param array $options 
     * @return boolean true to continue save process
     */
    public function beforeSave(&$Model, $options = array()) {
        if ( !$Model->LocaleModel || !is_string($Model->locale) || $Model->locale == $this->settings[$Model->alias]['default']) {
            return true;
        }
		$Model->LocaleModel->create();
        $Model->localeData = array();
        foreach ($this->settings[$Model->alias]['fields'] as $field) {
        	if (isset($Model->data[$Model->alias][$field])) {
	            $Model->localeData[$field] = $Model->data[$Model->alias][$field];
	            unset($Model->data[$Model->alias][$field]); 
	        }
        }
        if (!empty($Model->localeData)) {
             $Model->localeData[$Model->primaryKey] = 
                $Model->data[$Model->alias][$Model->primaryKey];
        }
        return true;
    }
	
    /**
     * creates a model to access the locale models
     *
     * @param object $Model 
     * @return object Model object for the translation table
     */
    private function createLocaleModel(&$Model) {
        if ($this->settings[$Model->alias]['useDbConfig']) {
            $dbConfig = $this->settings[$Model->alias]['useDbConfig'];
        } else {
            $dbConfig = $Model->useDbConfig;
        }
        $table = $Model->useTable .$this->suffix;	
        $db = & ConnectionManager::getDataSource($dbConfig);
        $prefix = $Model->tablePrefix ? $Model->tablePrefix : $db->config['prefix'];
        $tables = $db->listSources();
        $full_table_name = $prefix.$table;
        if ($prefix && empty($db->config['prefix'])) {
            $table = $full_table_name;
        }
        if (!in_array($full_table_name, $tables)) {
        	$Model->LocaleModel = false;
            return false;
        } 	
       	
		if (is_string($this->settings[$Model->alias]['model'])) {
			if (App::import('model',$this->settings[$Model->alias]['model'])) {
				$Model->LocaleModel = new $this->settings[$Model->alias]['model'](false, $table, $dbConfig);
			} else {
				$Model->LocaleModel = new Model(false, $table, $dbConfig);
			}			
		} else {
			$Model->LocaleModel = new Model(false, $table, $dbConfig);
		}	
        $Model->LocaleModel->alias = $Model->alias;   
        $Model->LocaleModel->primaryKey = 'trans_id';
        return true;
    }
}
?>
