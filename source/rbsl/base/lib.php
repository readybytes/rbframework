<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
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
class XiLib extends JObject
{
	// class variable startinf from _ are not part of DB columns
	// they cannot be updated via bind

	// trigger tells if we need to trigger onBeforeSave/onAfterSave events
	public 		$_trigger   		= true;
	protected	$_component			= XI_COMPONENT_NAME;

	/*
	 * We want to make error handling to common objects
	 * So we override the functions and direct them to work
	 * on a global error object
	 */
	public function getError($i = null, $toString = true )
	{
		$errObj	=	XiFactory::getErrorObject();
		return $errObj->getError($i, $toString);
	}

	public function setError($errMsg)
	{
		$errObj	=	XiFactory::getErrorObject();
		return $errObj->setError($errMsg);
	}

	// Over-ride so we can send $this as return
	public function set($property, $value = null, $prev=false)
	{
		$previous = parent::set($property, $value);
		return $prev ? $previous : $this;
	}

	public function getName()
	{
		XiError::assert($this);

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

	/**
	 * @return : XiModel
	 */
	public function getModel()
	{
		XiError::assert($this);
		return XiFactory::getInstance($this->getName(), 'Model');
	}


	public function getPrefix()
	{
		XiError::assert($this);
		return $this->_component;
	}

	public function __construct($config = array())
	{
		//return $this to chain the functions
		return $this->reset($config);
	}

	static public function getInstance($name, $id=0, $type=null, $bindData=null)
	{
		static $instance=array();

		//clean cache if required
		if(XiFactory::cleanStaticCache()){
			$instance=array();
		}

		//generate class name
		$className	= 'Payplans'.$name;

		// special case handling for App
		if('app' === JString::strtolower($name)){

			//try to calculate type of app from ID if given
			if($id){
				if($bindData !== null){
					$item = $bindData;
				}else{
					$item = XiFactory::getInstance('app','model')
								->loadRecords(array('id' => $id));
					$item = array_shift($item);
				}
				
				$type = $item->type;
			}

			XiError::assert($type!==null, XiText::_('COM_PAYPLANS_ERROR_INVALID_TYPE_OF_APPLICATION'));

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

	public function toArray($strict=false, $forReadOnly=false)
	{
		XiError::assert($this);

		$arr = get_object_vars($this);
		$ret = array();
		foreach($arr as $key => $value)
		{
			if($strict === false && is_object($this->$key) && method_exists($this->$key, 'toString') && is_a($this->$key, 'XiParameter')){
				$ret[$key] = $this->$key->toString('XiINI');
				continue;
			}

			if($value instanceof XiDate && $forReadOnly == true){
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

	public function getClassname()
	{
		XiError::assert($this);
		return get_class($this);
	}

	public function getParamsHtml($name = 'params', $key= null)
	{
		$name = JString::strtolower($name);

		XiError::assert(is_object($this->$name), XiText::_('COM_PAYPLANS_ERROR_PARAMETER_MUST_BE_AN_OBJECT'));
		XiError::assert(method_exists($this->$name,'render'), XiText::_('COM_PAYPLANS_ERROR_INVALID_PARAMETER_NAME_TO_RENDER'));

		$key = ($key === null) ? $name : $key;
		return $this->$name->render($key);
	}

	/**
	 * @param Object/Array $data
	 * @param Array $ignore
	 *
	 * @return XiLib
	 */
	public function bind($data, $ignore=array())
	{
		XiError::assert($this);
		if(empty($data) || $data == null){
			return $this;
		}

		if(is_object($data)){
			$data = (array) ($data);
		}

		// $data must be now an array
		XiError::assert(is_array($data), 'GIVEN DATA IS NOT AN ARRAY : '.var_export($data,true));

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


//	public function getEntityId()
//	{
//		XiError::assert($this);
//		return PayplansHelperEntity::getIdFromName($this->getName());
//	}

	public function getId()
	{
		XiError::assert($this);
		$varName = $this->getName().'_id';
		return $this->$varName;
	}

	function getKey()
	{
		return XiHelperUtils::getKeyFromId($this->getId());
	}
	
	public function setId($id)
	{
		XiError::assert($this);
		$varName = $this->getName().'_id';
		$this->$varName = $id;
		return $this;
	}

	public function load($id)
	{
		XiError::assert($this);
		XiError::assert($id);

		//if we are working on a single element then we need to clear the limit from query
		$item = XiFactory::getInstance($this->getName(), 'model')->loadRecords(array('id' => $id), array('limit'));

		// if no items found
		if(count($item) === 0){
			return false;
		}

		return $this->reset()->bind(array_shift($item));
	}


	/**
	 * Load Previously existing record if match with given condition
	 * @param Array $filters
	 * @param Array $clean
	 */
	public function loadIf($filters=array(), $clean=array())
	{
		XiError::assert($this);
		$records = $this->getModel()->loadRecords($filters, $clean);
		//if records exist then load the first one.
		if(empty($records)===false){
			$this->bind(array_shift($records));
		}

		// return this.
		return $this;
	}

	/**
	 * @return XiLib
	 */
	public function save()
	{
		// all class must have _trigger variable set to true, if need to trigger them
		// getName must be there
		if($this->_trigger === true){
			$previousObject = null;
			if($this->getId()){
				$previousObject = $this->getClone();
				$previousObject->load($this->getId());
			}

			// trigger on before save
			$args  = array($previousObject, $this);
			
			$event = 'onPayplans'.JString::ucfirst($this->getName()).'BeforeSave';
			if($this instanceof PayplansApp){
				$event = 'onPayplansAppBeforeSave';
			}	

			$result = PayplansHelperEvent::trigger($event, $args, '', $this);
		}


		// save to data to table
		$id = $this->getModel()->save($this->toArray(), $this->getId());

		//if above save was not complete, then id will be null
		// then return false and do not trigger after save
		if(!$id){
			return false;
		}

		// correct the id, for new records required
		$this->setId($id);

		// trigger on after save
		if($this->_trigger === true){
			// 	trigger on before save
			$args  = array($previousObject, $this);
			
			$event = 'onPayplans'.JString::ucfirst($this->getName()).'AfterSave';
			if($this instanceof PayplansApp){
				$event = 'onPayplansAppAfterSave';
			}	

			PayplansHelperEvent::trigger($event, $args, '', $this);
		}

		return $this;
	}
	
	public function delete()
	{
		// all class must have _trigger variable set to true, if need to trigger them
		// getName must be there
		if($this->_trigger === true){
			// trigger on before delete
			$args  = array($this);
			
			$event = 'onPayplans'.JString::ucfirst($this->getName()).'BeforeDelete';
			if($this instanceof PayplansApp){
				$event = 'onPayplansAppBeforeDelete';
				//XITODO : add __clone function to lib, for better readibility
				XiFactory::getSession()->set('OBJECT_TO_BE_DELETED_'.$this->getId().'_APP', $this->getClone());
			}else{
				XiFactory::getSession()->set('OBJECT_TO_BE_DELETED_'.$this->getId().'_'.JString::strtoupper($this->getName()), $this->getClone());
			}

			$result = PayplansHelperEvent::trigger($event, $args, '', $this);
		}

		// delete data from table
		$id  = $this->getId();
		$result = $this->getModel()->delete($id);
		$this->reset();
		//if above delete was not complete, then result will be null
		// then return false and do not trigger after delete
		if(!$result){
			return false;
		}

		// trigger on after delete
		if($this->_trigger === true){
			// 	trigger on after delete
			$args  = array($id);
			
			$event = 'onPayplans'.JString::ucfirst($this->getName()).'AfterDelete';
			if($this instanceof PayplansApp){
				$event = 'onPayplansAppAfterDelete';
			}
			
			PayplansHelperEvent::trigger($event, $args, '', $this);
		}
			
		return $this;
	}
	
	public function getClone()
	{
		return unserialize(serialize($this));
	}
	
	public function setParam($key, $value)
	{
		XiError::assert($this);
		$this->params->set($key,$value);
		return $this;
	}
}
