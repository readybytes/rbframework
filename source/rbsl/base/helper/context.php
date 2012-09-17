<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class XiHelperContext
{
	static function getObjectContext($object)
	{
		XiError::assertValue($object);
		return JString::strtolower($object->getPrefix().'_'.$object->getName());
	}
}