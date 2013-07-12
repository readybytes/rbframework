<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );


abstract class Rb_Checklist
{

	public $_location = __FILE__;
	public $_name;
	public $_required =true;

	public $_message = 'PLG_SYSTEM_RBSL_SETUP_NO_MESSAGE';
	public $_type = 'ERROR'; // WARNING , INFORMATION
	public $_returl = null;

	public function __construct()
	{
		$r = null;
		if (!preg_match('/Checklist(.*)/i', get_class($this), $r)) {
			JError::raiseError (500, "Rb_View::getName() : Can't get or parse class name.");
		}
		$this->_name = strtolower( $r[1] );

		//setup the action URL
		$parts 	= array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query');
		$uri 	= JURI::getInstance()->toString($parts);


		$this->_returl = base64_encode($uri);
	}


	static public function getInstance($componentName, $name)
	{
		static $instance=array();

		//clean cache if we need to
		if(Rb_Factory::cleanStaticCache()){
			$instance = array();
		}

		//generate class name
		$className	= $componentName.'Checklist'.ucfirst($name);

		//if already there is an object and check for static cache clean up
		if(isset($instance[$name]))
			return $instance[$name];

		//create new object, class must be autoloaded
		if(JDEBUG) Rb_Error::assert(class_exists($className, true));
		return $instance[$name] = new $className();
	}



	function isRequired()
	{
		return true;
	}

	function doApply()
	{
		return true;
	}

	function isApplicable()
	{
		return true;
	}

	function doRevert()
	{
		return true;
	}

	public function getMessage()
	{
		return Rb_Text::_($this->_message);
	}

	public function getTooltip()
	{
		return Rb_Text::_('PLG_SYSTEM_RBSL_SETUP_'.JString::strtoupper($this->_name).'_TOOLTIP');
		//return $this->_tooltip;
	}

	public function getType()
	{
		return strtolower($this->_type);
	}
}