<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class XiAbstractJ16Factory extends XiAbstractFactoryBase
{
	/**
	 * @return XiSession
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

class XiAbstractFactory extends XiAbstractJ16Factory
{}