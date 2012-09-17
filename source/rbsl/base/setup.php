<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


abstract class XiSetup
{

	public $_location = __FILE__;
	public $_name;
	public $_required =true;

	public $_message = 'COM_PAYPLANS_SETUP_NO_MESSAGE';
	public $_type = 'ERROR'; // WARNING , INFORMATION
	public $_returl = null;

	public function __construct()
	{
		$r = null;
		if (!preg_match('/Setup(.*)/i', get_class($this), $r)) {
			JError::raiseError (500, "XiView::getName() : Can't get or parse class name.");
		}
		$this->_name = JString::strtolower( $r[1] );

		//setup the action URL
		$parts 	= array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query');
		$uri 	= JURI::getInstance()->toString($parts);


		$this->_returl = base64_encode($uri);
	}


	static public function getInstance($name)
	{
		static $instance=array();

		//clean cache if we need to
		if(XiFactory::cleanStaticCache()){
			$instance = array();
		}

		//generate class name
		$className	= 'PayplansSetup'.JString::ucfirst(JString::strtolower($name));

		//if already there is an object and check for static cache clean up
		if(isset($instance[$name]))
			return $instance[$name];

		//create new object, class must be autoloaded
		if(JDEBUG) XiError::assert(class_exists($className, true));
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
		return XiText::_($this->_message);
	}

	public function getTooltip()
	{
		return XiText::_('COM_PAYPLANS_SETUP_'.JString::strtoupper($this->_name).'_TOOLTIP');
		//return $this->_tooltip;
	}

	public function getType()
	{
		return JString::strtolower($this->_type);
	}
}