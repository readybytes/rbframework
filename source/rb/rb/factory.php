<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_AbstractFactory  extends JFactory
{
}


class Rb_Factory extends Rb_AbstractFactory 
{
	//Returns a Model/View/Controller/Table/Lib object
	static function getInstance($name, $type='', $prefix='Rb_', $refresh=false)
	{
		static $instance=array();

		//generate class name
		$className	= $prefix.$type.$name;

		// Clean the name
		$className	= preg_replace( '/[^A-Z0-9_]/i', '', $className );

		//even thought class name are not case-senstivite but array index are
		//so convert the case so that if instance is available isset does not results false due to case 
		$className = strtolower($className);

		//if already there is an object
		if(isset($instance[$className]) && !$refresh){
			return $instance[$className];
		}

		//class_exists function checks if class exist,
		// and also try auto-load class if it can
		if(class_exists($className, true)===false){
			throw new Exception("RB Factory::getInstance = Class $className not found");
		}

		//create new object, class must be autoloaded
		$instance[$className]= new $className();

		return $instance[$className];
	}

	/**
	 * @return Rb_AjaxResponse
	 */
	static public function getAjaxResponse()
  	{
  		//We want to send our DB object instead of Joomla Object
  		//so that we can check our sql performance on the fly.
 		static $response = null;

 		if ($response === null){
 			$response = Rb_AjaxResponse::getInstance();
 		}

  		return $response;
  	}

	static public function cleanStaticCache($set = null)
	{
		static $reset = false;

		if($set !== null)
			$reset = $set;

		return $reset;
	}
}
