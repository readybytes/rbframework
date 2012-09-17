<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class XiAbstractJ15Factory extends XiAbstractFactoryBase
{
	/**
	 * @return XiSession
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

class XiAbstractFactory extends XiAbstractJ15Factory
{}