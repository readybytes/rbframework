<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_AbstractJ15Factory extends Rb_AbstractFactoryBase
{
	/**
	 * @return Rb_Session
	 */
	public function getSession($reset=false)
	{
		return parent::_getSession();
	}

	/**
	 * @return stdClass
	 */
	public function getConfig()
	{
		return parent::_getConfig();
	}
}

class Rb_AbstractFactory extends Rb_AbstractJ15Factory
{}