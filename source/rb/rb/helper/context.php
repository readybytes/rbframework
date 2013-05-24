<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_HelperContext
{
	static function getObjectContext($object)
	{
		Rb_Error::assertValue($object);
		return strtolower($object->getPrefix().'_'.$object->getName());
	}
}