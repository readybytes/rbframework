<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );


class Rb_Error extends JError
{
	const ERROR   = 1;
	const WARNING = 2;
	const MESSAGE = 3;

	//RBFW_TODO : add assertError. assertWarn, assertMessage function
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
		Rb_Factory::getApplication()->enqueueMessage('XI-WARNING : '.$msg);
	}

	static public function assertValue($value)
	{
		assert($value);
	}
}