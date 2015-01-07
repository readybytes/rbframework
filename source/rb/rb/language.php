<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );


class Rb_Language extends JLanguage
{
	public static function getStrings(JLanguage $instance)
	{
		// for 1.6 +
		return $instance->strings;
	}
}