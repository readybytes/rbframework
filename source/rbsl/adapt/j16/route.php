<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_AdaptJ16Route extends JRoute
{
	static public function _($url, $xhtml = false, $ssl = null)
	{
		return parent::_route($url, $xhtml, $ssl);
	}
}

class Rb_AdaptRoute extends Rb_AdaptJ16Route
{}