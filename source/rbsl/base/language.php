<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


class XiLanguage extends JLanguage
{
	public static function getStrings(JLanguage $instance)
	{
		if(PAYPLANS_JVERSION_FAMILY == '15'){
			return $instance->_strings;
		}
		
		// for 1.6 +
		return $instance->strings;
	}
}