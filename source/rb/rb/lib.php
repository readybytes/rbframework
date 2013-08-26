<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

/**
 * A general purpose library base class,
 * so chile classes can enjoy some easy work without any problems
 * e.g.  Factory Pattern, Error Handling etc.
 * @author shyam
 *
 */
abstract class Rb_Lib extends JObject
{
	// class variable startinf from _ are not part of DB columns
	// they cannot be updated via bind

	// trigger tells if we need to trigger onBeforeSave/onAfterSave events
	protected	$_trigger   	= true;

	/** 
	 * @var Rb_Extension
	 */
	protected	$_component		= '';
   	protected	$_name			= '';
   	
   	/**
   	 * 
   	 * @var Rb_ModelForm
   	 */
   	protected	$_modelform			= null;
   	
	public function __construct($config = array())
	{
		$this->_component = Rb_Extension::getInstance($this->_component);

		parent::__construct();
		
		//return $this to chain the functions
		$this->reset($config);
	}

	/**
	 * @return : Rb_Lib
	 */
	static public function getInstance($comName, $name, $id=0, $bindData=null)
	{
		static $instance=array();

		//clean cache if required
		if(Rb_Factory::cleanStaticCache()){
			$instance=array();
		}

		//generate class name
		$className	= $comName.$name;
		
		// in classname does not exists then return false
		if(class_exists($className, true) === FALSE){
			return false;
		}	
		
		// can not cache object of it 0
		if(!$id){
			$class_instance = new $className();
			return $bindData ? $class_instance->bind($bindData)
							 : $class_instance;
		}

		//if already there is an object and check for static cache clean up
		if(isset($instance[$className][$id]) && $instance[$className][$id]->getId()==$id)
			return $instance[$className][$id];

		//create new object, class must be autoloaded
		$instance[$className][$id] = new $className();

		//if bind data exist then bind with it, else load new data
		return  $bindData 	? $instance[$className][$id]->bind($bindData)
							: $instance[$className][$id]->load($id);

	}
		
	public function getName()
	{
		if(empty($this->_name))
		{
			$r = null;
			if (!preg_match('/'.$this->getPrefix().'(.*)/i', get_class($this), $r)) {
				JError::raiseError (500, get_class($this)."::getName() : Can't get or parse class name.");
			}
			$this->_name = strtolower( $r[1] );
		}

		return $this->_name;
	}

	/*
	 * Collect prefix
	 * @return String : lowercase name
	 */
	public function getPrefix()
	{		
		return $this->_component->getPrefixClass();
	}
	
	// Over-ride so we can send $this as return
	public function set($property, $value = null, $prev=false)
	{
		$previous = parent::set($property, $value);
		return $prev ? $previous : $this;
	}
	
	/**
	 * @return : Rb_Model
	 */
	public function getModel()
	{
		return Rb_Factory::getInstance($this->getName(), 'Model', $this->_component->getPrefixClass());
	}
	
	/**
	 * 
	 * @return : Rb_Modelform
	 */
	public function getModelform()
	{
		if(isset($this->_modelform)){
			return $this->_modelform;
		}
		
		// setup modelform
		$this->_modelform = Rb_Factory::getInstance($this->getName(), 'Modelform' , $this->_component->getPrefixClass());
		
		// set model form to pick data from this object
		$this->_modelform->setLibData($this);
		
		return $this->_modelform ;
	}
	
	public function getId()
	{
		Rb_Error::assert($this);
		$varName = $this->getName().'_id';
		return $this->$varName;
	}
	
	public function setId($id)
	{
		Rb_Error::assert($this);
		$varName = $this->getName().'_id';
		$this->$varName = $id;
		return $this;
	}
	
	public function getClone()
	{
		return unserialize(serialize($this));
	}
	
	
	public function toDatabase()
	{
		Rb_Error::assert($this);

		$arr = get_object_vars($this);
		$ret = array();
		foreach($arr as $key => $value)
		{
			// ignore extra variables
			if(preg_match('/^_/',$key)){
				continue;
			}
			
			// if object, then bind it properly
			if(is_object($this->$key)){
				if(is_a($this->$key, 'Rb_Registry')){
					$ret[$key] = (string) $this->$key;
					continue;
				}
				
				if(is_a($this->$key, 'Rb_Date')){
					$ret[$key] = $this->$key->toSql();
					continue;
				}
			
				if(method_exists($this->$key, 'toString')){
					$ret[$key] = $this->$key->toString();
					continue;
				}
				
				if(method_exists($this->$key, 'toArray')){
					$ret[$key] = $this->$key->toArray();
					continue;
				}
			}			

			// normal scalar, just assign
			$ret[$key] = $arr[$key];
		}

		return $ret;
	}
	
	public function toArray()
	{
		Rb_Error::assert($this);

		$arr = get_object_vars($this);
		$ret = array();
		foreach($arr as $key => $value)
		{
			// ignore extra variables
			if(preg_match('/^_/',$key)){
				continue;
			}
			
			// if object, then bind it properly
			if(is_object($this->$key)){			
				if(method_exists($this->$key, 'toArray')){
					$ret[$key] = $this->$key->toArray();
					continue;
				}
				
				if(method_exists($this->$key, 'toString')){
					$ret[$key] = $this->$key->toString();
					continue;
				}
			}			

			// normal scalar, just assign
			$ret[$key] = $arr[$key];
		}

		return $ret;
	}



	/**
	 * @param Object/Array $data
	 * @param Array $ignore
	 *
	 * @return Rb_Lib
	 */
	public function bind($data, $ignore=array())
	{
		Rb_Error::assert($this);
		
		if(empty($data) || $data == null){
			return $this;
		}

		if(is_object($data)){
			$data = (array) ($data);
		}

		// $data must be now an array
		Rb_Error::assert(is_array($data), 'GIVEN DATA IS NOT AN ARRAY : '.var_export($data,true));

		// also accept strings
		if(!is_array($ignore)) {
			$ignore = explode(',', $ignore);
		}

		// bind information to object
		$arr = get_object_vars($this);
		foreach ($arr as $k => $v)
		{
			//need to ignore, also variable starting from underscore
			if(in_array($k, $ignore) || preg_match('/^_/',$k))
				continue;

			// if value not set in datas
			if(isset($data[$k]) === false)
				continue;

			// if its a rb_date object
			if(is_a($this->$k, 'Rb_Date')){
				$this->$k = new Rb_Date($data[$k]);				
				continue;
			}
			
			// its an object and supports bind function data, bind it
			if(is_object($this->$k) && method_exists($this->$k,'bind')){
				$this->$k->bind($data[$k]);
				continue;
			}

			// simply copy
			$this->$k = $data[$k];
		}

		// if id is set in data than set id
		if(array_key_exists('id', $data))
			$this->setId($data['id']);

		return $this->afterBind($this->getId(), $data);
	}

	public function afterBind($id, $data)
	{
		return $this;
	}

	public function load($id)
	{
		Rb_Error::assert($this);
		Rb_Error::assert($id);
		
		//if we are working on a single element then we need to clear the limit and where from query
		$item = Rb_Factory::getInstance($this->getName(), 'Model', $this->_component->getPrefixClass())
						->loadRecords(array('id' => $id), array('limit', 'where'));

		// if no items found
		if(count($item) === 0){
			return false;
			// raise exception
		}

		return $this->reset()->bind(array_shift($item));
	}

	/**
	 * @return Rb_Lib
	 */
	public function save()
	{
		$entity = $this->getName();

		//$previous object should be defined
		$previousObject = null;
		if ($this->getId()) {
			$previousObject = $this->getClone();
			$previousObject->load($this->getId());
		}
	
		// trigger on before save    
	    	if ($this->_trigger === true) {
			$args  = array($previousObject, $this, $entity);
			$event = 'on'.$this->getPrefix().'BeforeSave';
			$result = Rb_HelperPlugin::trigger($event, $args, '', $this);
		}

		// save to data to table
		$id = $this->_save($previousObject);

		//if save was not complete, then id will be null, do not trigger after save
		if(!$id){
			return false;
		}

		// correct the id, for new records required
		$this->setId($id);

		// trigger on after save
		if($this->_trigger === true){		
			$event = 'on'.$this->getPrefix().'AfterSave';
			$args  = array($previousObject, $this, $entity);
			Rb_HelperPlugin::trigger($event, $args, '', $this);
		}

		return $this;
	}
	
	/**
	 * 
	 * We can override it
	 * @param Lib_Object $previousObject
	 */
	protected function _save($previousObject)
	{
		// save to data to table
		return $this->getModel()->save($this->toDatabase(), $this->getId());
		
	}
	
	public function delete()
	{
		$entity = $this->getName();
		// getName must be there
		if($this->_trigger === true){
			// trigger on before delete
			$event = 'on'.$this->getPrefix().'BeforeDelete';
			$args  = array($this, $entity);
			$result = Rb_HelperPlugin::trigger($event, $args, '', $this);
		}

		// delete data from table
		$id  = $this->getId();
		$result = $this->getModel()->delete($id);
		$this->reset();
		
		// if above delete was not complete, then result will be null
		// then return false and do not trigger after delete
		if(!$result){
			return false;
		}

		// trigger on after delete
		if($this->_trigger === true){
			$event = 'on'.$this->getPrefix().'AfterDelete';
			$args  = array($id, $entity);
			Rb_HelperPlugin::trigger($event, $args, '', $this);
		}
			
		return $this;
	}
	
	/**
	 * We use this function in various entities, so it must be defined in parent.
	 */	
	public function reset()
	{
		return $this;
	}
	
	public function setParam($key, $value, $property='params')
	{
		$this->$property->set($key, $value);
		return $this;
	}
	
	public function setParams($value, $key='params')
	{
		$this->$key->bind($value);
		return $this;
	}
	
	public function getParam($key, $default = null, $property='params')
	{
		return $this->$property->get($key, $default);
	}
	
	public function getParams($object = true, $property='params')
	{
		if($object){
			return $this->$property->toObject();
		}
		
		return $this->$property->toArray();
	}
}
