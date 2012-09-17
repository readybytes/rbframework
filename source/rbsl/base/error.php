<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


class XiError extends JError
{
	const ERROR   = 1;
	const WARNING = 2;
	const MESSAGE = 3;

	//XITODO : add assertError. assertWarn, assertMessage function
	static function assert($condition, $msg = '', $type = self::ERROR)
	{
		// assert only if in debug mode
		if($condition || !(JDEBUG)){
			return true;
		}

		//raise error
		if($type == self::ERROR){
			self::raiseError('XI-ERROR', $msg);
		}

		//raise warning
		if($type == self::WARNING){
			self::raiseWarning('XI-WARNING', $msg);
		}
		
		// enqueue message
		XiFactory::getApplication()->enqueueMessage('XI-WARNING : '.$msg);
	}

	static public function assertValue($value)
	{
		assert($value);
	}
}