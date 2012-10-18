<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

/**
 * A general purpose library base class,
 * so chile classes can enjoy some easy work without any problems
 * e.g.  Factory Pattern, Error Handling etc.
 * @author shyam
 *
 */
class Rb_Lib extends JObject
{
	// class variable startinf from _ are not part of DB columns
	// they cannot be updated via bind

	// trigger tells if we need to trigger onBeforeSave/onAfterSave events
	protected	$_trigger   	= true;

	// name of component without com_
	protected	$_component		= '';
   	protected	$_name			= '';
   	
	public function __construct($config = array())
	{
		//return $this to chain the functions
		return $this->reset($config);
	}

	/**
	 * @return : Rb_Lib
	 */
	static public function getInstance($comName, $name, $id=0, $type=null, $bindData=null)
	{
		static $instance=array();

		//clean cache if required
		if(Rb_Factory::cleanStaticCache()){
			$instance=array();
		}

		//generate class name
		$className	= $comName.$name;

		// special case handling for App
		if('app' === strtolower($name)){

			//try to calculate type of app from ID if given
			if($id){
				if($bindData !== null){
					$item = $bindData;
				}else{
					$item = Rb_Factory::getInstance('app','model', $this->_component)
								->loadRecords(array('id' => $id));
					$item = array_shift($item);
				}
				
				$type = $item->type;
			}

			Rb_Error::assert($type!==null, Rb_Text::_('PLG_SYSTEM_RBSL_ERROR_INVALID_TYPE_OF_APPLICATION'));

			//IMP autoload apps
			PayplansHelperApp::getApps();
			$className	= 'PayplansApp'.$type;
		}

		// in classname does not exists then return false
		if(class_exists($className, true) === FALSE){
			return false;
		}	
		
		// can not cache object of it 0
		if(!$id){
			return new $className();
		}

		//if already there is an object and check for static cache clean up
		if(isset($instance[$name][$id]) && $instance[$name][$id]->getId()==$id)
			return $instance[$name][$id];

		//create new object, class must be autoloaded
		$instance[$name][$id] = new $className();

		//if bind data exist then bind with it, else load new data
		return  $bindData 	? $instance[$name][$id]->bind($bindData)
					: $instance[$name][$id]->load($id);

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

	public function getPrefix()
	{
		return $this->_component;
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
		return Rb_Factory::getInstance($this->getName(), 'Model', $this->_component);
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
	
	public function setParam($key, $value)
	{
		Rb_Error::assert($this);
		$this->params->set($key,$value);
		return $this;
	}
	
	public function toArray($strict=false, $forReadOnly=false)
	{
		Rb_Error::assert($this);

		$arr = get_object_vars($this);
		$ret = array();
		foreach($arr as $key => $value)
		{
			if($strict === false && is_object($this->$key) && method_exists($this->$key, 'toString') && is_a($this->$key, 'Rb_Parameter')){
				$ret[$key] = $this->$key->toString('Rb_INI');
				continue;
			}

			if($value instanceof Rb_Date && $forReadOnly == true){
				$ret[$key] = PayplansHelperFormat::date($value);
				continue;
			}


			if(is_object($this->$key) && method_exists($this->$key, 'toArray')){
				$ret[$key] = $this->$key->toArray();
				continue;
			}

			$ret[$key] = $arr[$key];
		}

		return $ret;
	}


	public function getParamsHtml($name = 'params', $key= null)
	{
		$name = strtolower($name);

		Rb_Error::assert(is_object($this->$name), Rb_Text::_('PLG_SYSTEM_RBSL_ERROR_PARAMETER_MUST_BE_AN_OBJECT'));
		Rb_Error::assert(method_exists($this->$name,'render'), Rb_Text::_('PLG_SYSTEM_RBSL_ERROR_INVALID_PARAMETER_NAME_TO_RENDER'));

		$key = ($key === null) ? $name : $key;
		return $this->$name->render($key);
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
		foreach ($this->toArray() as $k => $v)
		{
			//need to ignore, also variable starting from underscore
			if(in_array($k, $ignore) || preg_match('/^_/',$k))
				continue;

			// if value not set in datas
			if(isset($data[$k])==false)
				continue;

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

		return $this->afterBind($this->getId());
	}

	public function afterBind($id)
	{
		return $this;
	}

	public function load($id)
	{
		Rb_Error::assert($this);
		Rb_Error::assert($id);

		//if we are working on a single element then we need to clear the limit from query
		$item = Rb_Factory::getInstance($this->getName(), 'Model', $this->_component)
						->loadRecords(array('id' => $id), array('limit'));

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
		$class = get_class($this);

		if($this->_trigger === true){
			$previousObject = null;
			if($this->getId()){
				$previousObject = $this->getClone();
				$previousObject->load($this->getId());
			}

			// trigger on before save
			$args  = array($previousObject, $this, $class);
			$event = 'onRbItemBeforeSave';
			$result = Rb_HelperPlugin::trigger($event, $args, '', $this);
		}


		// save to data to table
		$id = $this->getModel()->save($this->toArray(), $this->getId());

		//if save was not complete, then id will be null, do not trigger after save
		if(!$id){
			return false;
		}

		// correct the id, for new records required
		$this->setId($id);

		// trigger on after save
		if($this->_trigger === true){		
			$event = 'onRbItemAfterSave';
			$args  = array($previousObject, $this, $class);
			Rb_HelperPlugin::trigger($event, $args, '', $this);
		}

		return $this;
	}
	
	public function delete()
	{
		$class = get_class($this);
		// getName must be there
		if($this->_trigger === true){
			// trigger on before delete
			$event = 'onRbItemBeforeDelete';
			$args  = array($this, $class);
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
			$event = 'onRbItemAfterDelete';
			$args  = array($id, $class);
			Rb_HelperPlugin::trigger($event, $args, '', $this);
		}
			
		return $this;
	}
}
