<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_AdaptJ16Factory extends JFactory
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

class Rb_AdaptFactory extends Rb_AdaptJ16Factory
{}