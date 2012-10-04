<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_AdaptFactory extends JFactory
{
	/**
	 * @return Rb_Session
	 */
	static function getSession($reset=false)
	{
		return parent::_getSession();
	}

	/**
	 * @return stdClass
	 */
	static function getConfig()
	{
		return parent::_getConfig();
	}
}


class Rb_AbstractFactory extends Rb_AdaptFactory
{
	//Returns a MVCT object
	static function getInstance($name, $type='', $prefix='Payplans', $refresh=false)
	{
		static $instance=array();

		
		//generate class name
		$className	= $prefix.$type.$name;

		// Clean the name
		$className	= preg_replace( '/[^A-Z0-9_]/i', '', $className );

		//if already there is an object
		if(isset($instance[$className]) && !$refresh)
			return $instance[$className];

		//class_exists function checks if class exist,
		// and also try auto-load class if it can
		if(class_exists($className, true)===false)
		{
			self::getErrorObject()->setError("Class $className not found");
			return false;
		}

		//create new object, class must be autoloaded
		$instance[$className]= new $className();

		return $instance[$className];
	}

	/**
	 * @return JObject
	 */
	static function getErrorObject($reset=false)
	{
		static $instance=null;

		if($instance !== null && $reset===false)
			return $instance;

		$instance	= new JObject();

		return $instance;
	}

	/**
	 * @return Rb_Session
	 */
	static function _getSession($reset=false)
	{
		static $instance=null;

		if($instance !== null && $reset===false)
			return $instance;

		$instance	= new Rb_Session();

		return $instance;
	}

	/**
	 * @return Rb_AjaxResponse
	 */
	static public function getAjaxResponse()
  	{
  		//We want to send our DB object instead of Joomla Object
  		//so that we can check our sql performance on the fly.
 		static $response = null;

 		if ($response === null)
 			$response = Rb_AjaxResponse::getInstance();

  		return $response;
  	}
  	
  	/**
	 * get all configuration parameter available
	 * @return stdClass object of configuration params
	 */
	static $config = null;
  	static public function _getConfig()
  	{
		//RBFW_TODO : Implement reset logic for whole component
		if(self::$config && Rb_Factory::cleanStaticCache() != true)
  			return self::$config;

  		$records 	= self::getInstance('config', 'model')->loadRecords();

		// load parent global joomla configuration first
		$arr = JFactory::getConfig()->toArray();

		// load all configurations of Rb_, and merge them
		foreach($records as $record){
			//IMP : by sending record we can reduce one query on each loop iteration
			$obj = PayplansConfig::getInstance($record->config_id, null, $record);
			$arr = array_merge($arr, $obj->getConfig()->toArray());
		}

		// Convert single value to array, so we can ensure variable is always an array
		if(array_key_exists('blockLogging', $arr) && !is_array($arr['blockLogging'])){
			$arr['blockLogging'] = array($arr['blockLogging']);
		}

		// Let plugin modify config
		$args = array(&$arr);
		Rb_HelperPlugin::trigger('onRbConfigLoad', $args);

		// convert array of config to object
		return self::$config = (object)$arr;
  	}

	static public function cleanStaticCache($set = null)
	{
		static $reset = false;

		if($set !== null)
			$reset = $set;

		return $reset;
	}

	/**
	 * @return PayplansRewriter
	 */
	public static function getRewriter($reset=false)
	{
		static $instance=null;

		if($instance !== null && $reset===false)
			return $instance;

		$instance	= new PayplansRewriter();

		return $instance;
	}
	
	/**
	 * @return Rb_Encryptor
	 */
	public static function getEncryptor($reset=false)
	{
		static $instance=null;

		if($instance !== null && $reset===false)
			return $instance;

		// RBFW_TODO : raise error if key is not defined
		$key = JString::strtoupper(self::_getConfig()->expert_encryption_key);
		$instance	= new Rb_Encryptor($key);

		return $instance;
	}
	
	/**
	 * @return Rb_Logger
	 */
	static protected $_logger = array();
	public static function getLogger($name='')
	{
		$className = 'Rb_Logger'.$name;
		if(isset(self::$_logger[$className])===false){
			self::$_logger[$className] = new $className();
		}
		
		return self::$_logger[$className];
	}
}

class Rb_Factory extends Rb_AbstractFactory
{
	static protected $_dashboardMessage = array();
	public static function getDashboardMessage()
	{
		
		return self::$_dashboardMessage;
	}
	
	public static function setDashboardMessage($message, $type='MESSAGE')
	{
	
	}
	
	
	
	//  src='https://www.google.com/jsapi?autoload={"modules":[{"name":"visualization","version":"1"}]}'>	
	static protected $_chartScript = "jsapi?autoload={'modules':[{'name':'visualization','version':'1'}]}";

	// Load script for drawing charts
	static private $_chartInitilized = false;
	static function chartInit()
	{
		// add chart javascript
		if(self::$_chartInitilized  == false){
			Rb_Html::script(self::$_chartScript,'https://www.google.com/');
			self::$_chartInitilized =true;
		}
		 
		return true;
	}

	// Create a new chart object specific
	public static function getChart($type='annotatedtimeline' , $refresh=TRUE)
	{
		// classname will be Rb_ + Chart + $type 
		return Rb_Factory::getInstance($type, 'Chart', 'Rb_', $refresh);
	}
	
	static $currency = null; 
	public static function getCurrency($isocode = null)
	{	
		// if currency loaded loaded
		if(self::$currency === null){
			self::$currency = Rb_Factory::getInstance('currency', 'model')
										->loadRecords();
		
		}
		
		// if isocode is null then return all currency
		if($isocode == null){
			return self::$currency;
		}
		
		if(isset(self::$currency[$isocode])){
			return self::$currency[$isocode];
		}
		
		return false;	
	}
	
    static $country = null; 
	public static function getCountry($country_code = null)
	{	
		// if country loaded 
		if(self::$country === null){
			self::$country = Rb_Factory::getInstance('country', 'model')
										->loadRecords();
		
		}
		
		// if country code is null then return all countries
		if($country_code == null){
			return self::$country;
		}
		
		if(isset(self::$country[$country_code])){
			return self::$country[$country_code];
		}
		
		return false;	
	}
	
}